<?php
/**
 * Item « Default » mutualisé des catalogues.
 *
 * Le défaut n'est PAS un item virtuel : c'est l'item réel dont l'identifiant
 * vaut `{prefixe}-default` (ex. `top-bar-default`, `contact-default`). Il est
 * affiché en premier dans les sommaires et sert de repli front quand une
 * rubrique de template ne référence aucun item.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Indique si un slug d'item catalogue est l'item Default.
 *
 * Vrai pour `default` ou pour un slug se terminant par `-default`, en excluant
 * les seeds d'origine non renommés (`{x}-mayami-default`, `{x}-ellene-default`).
 */
function em_wp_catalog_entry_is_default(string $slug): bool
{
    $slug = sanitize_key($slug);

    if ($slug === '') {
        return false;
    }

    if ($slug === 'default') {
        return true;
    }

    if (!str_ends_with($slug, '-default')) {
        return false;
    }

    return !str_contains($slug, '-mayami-') && !str_contains($slug, '-ellene-');
}

/**
 * Détecte le slug de l'item Default dans une liste d'entrées (ou '' si absent).
 *
 * @param array<string, array<string, mixed>> $entries
 */
function em_wp_catalog_detect_default_slug(array $entries): string
{
    foreach (array_keys($entries) as $slug) {
        $slug = sanitize_key((string) $slug);

        if ($slug !== '' && em_wp_catalog_entry_is_default($slug)) {
            return $slug;
        }
    }

    return '';
}

/**
 * Alias front : slug de l'item Default si la liste en contient un, sinon ''.
 *
 * @param array<string, array<string, mixed>> $entries
 */
function em_wp_catalog_default_entry_slug_if_present(array $entries): string
{
    return em_wp_catalog_detect_default_slug($entries);
}

/**
 * Place l'item Default détecté en première position (sans créer d'item virtuel).
 *
 * @param array<string, array<string, mixed>> $entries
 * @return array<string, array<string, mixed>>
 */
function em_wp_catalog_apply_default_entry(array $entries): array
{
    $default_slug = em_wp_catalog_detect_default_slug($entries);

    if ($default_slug === '' || !isset($entries[$default_slug])) {
        return $entries;
    }

    $default_entry = $entries[$default_slug];
    unset($entries[$default_slug]);

    return [$default_slug => $default_entry] + $entries;
}

/**
 * Entrées d'un catalogue, en tolérant le slug rubrique singulier.
 *
 * `em_wp_catalog_hub_entries()` indexe les modules au pluriel (`streams`,
 * `top-bars`…) alors que les rubriques sont au singulier. On tente le slug
 * tel quel, puis sa forme plurielle, puis les modules custom.
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_catalog_module_entries_for_lookup(string $module_slug): array
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return [];
    }

    if (function_exists('em_wp_catalog_hub_entries')) {
        $entries = em_wp_catalog_hub_entries($module_slug);

        if ($entries === []) {
            $entries = em_wp_catalog_hub_entries($module_slug . 's');
        }

        if ($entries !== []) {
            return $entries;
        }
    }

    if (function_exists('em_wp_custom_catalog_entries')) {
        return em_wp_custom_catalog_entries($module_slug);
    }

    return [];
}

/**
 * Ensemble des slugs d'items catalogue actuellement actifs sur le template live.
 *
 * Parcourt toutes les rubriques liées à un catalogue (visibles sur le template
 * live), récupère l'item sélectionné (ou son repli Default), et ajoute aussi le
 * hero/slider du HEADER. Les slugs catalogue étant préfixés (`hero-…`,
 * `stream-…`), l'ensemble global suffit pour tester n'importe quel item.
 *
 * @return array<string, true>
 */
function em_wp_catalog_live_active_entry_slugs(): array
{
    static $cache = null;

    if (is_array($cache)) {
        return $cache;
    }

    $cache = [];

    if (!function_exists('em_wp_get_active_template_slug')) {
        return $cache;
    }

    $template = em_wp_get_active_template_slug();

    if ($template === '') {
        return $cache;
    }

    $is_visible = static function (string $rubrique) use ($template): bool {
        return !function_exists('em_wp_is_template_rubrique_visible')
            || em_wp_is_template_rubrique_visible($template, $rubrique);
    };

    // Rubriques intégrées : on s'appuie sur le résolveur propre à chaque module
    // (gère le pointeur, la map V1 legacy et le repli Default).
    $builtin_resolvers = [
        'top-bar' => ['fn' => 'em_wp_top_bar_get_options', 'key' => 'top_bar_slug'],
        'stream'  => ['fn' => 'em_wp_stream_get_options', 'key' => 'stream_slug'],
        'video'   => ['fn' => 'em_wp_video_get_options', 'key' => 'video_slug'],
        'release' => ['fn' => 'em_wp_release_get_options', 'key' => 'release_slug'],
        'social'  => ['fn' => 'em_wp_social_get_options', 'key' => 'social_slug'],
        'cta'     => ['fn' => 'em_wp_cta_get_options', 'key' => 'cta_slug'],
        'footer'  => ['fn' => 'em_wp_footer_get_options', 'key' => 'footer_slug'],
    ];

    $definitions = function_exists('em_wp_admin_site_rubrique_all_definitions')
        ? em_wp_admin_site_rubrique_all_definitions()
        : [];

    foreach ($definitions as $rubrique_slug => $definition) {
        $rubrique_slug = sanitize_key((string) $rubrique_slug);

        // HEADER (hero + slider) est traité séparément plus bas.
        if ($rubrique_slug === '' || $rubrique_slug === 'header' || !$is_visible($rubrique_slug)) {
            continue;
        }

        $slug = '';

        if (isset($builtin_resolvers[$rubrique_slug])) {
            $fn = $builtin_resolvers[$rubrique_slug]['fn'];

            if (function_exists($fn)) {
                $options = call_user_func($fn, $template);
                $slug = sanitize_key((string) ($options[$builtin_resolvers[$rubrique_slug]['key']] ?? ''));
            }
        } else {
            // Modules custom (CONTACTS, …) : résolveur dédié + repli Default.
            $catalog_module = sanitize_key((string) ($definition['catalog_module'] ?? ''));

            if ($catalog_module === '') {
                continue;
            }

            $options = function_exists('em_wp_get_template_rubrique_options')
                ? em_wp_get_template_rubrique_options($rubrique_slug, $template)
                : [];

            if (function_exists('em_wp_custom_catalog_rubrique_resolve_entry_slug')) {
                $slug = sanitize_key(
                    em_wp_custom_catalog_rubrique_resolve_entry_slug($catalog_module, $template, $options)
                );
            }

            if ($slug === '') {
                $pointer_key = function_exists('em_wp_admin_rubrique_catalog_pointer_key')
                    ? em_wp_admin_rubrique_catalog_pointer_key($rubrique_slug)
                    : $rubrique_slug . '_slug';
                $slug = sanitize_key((string) ($options[$pointer_key] ?? ''));
            }

            if ($slug === '') {
                $slug = em_wp_catalog_default_entry_slug_if_present(
                    em_wp_catalog_module_entries_for_lookup($catalog_module)
                );
            }
        }

        if ($slug !== '') {
            $cache[$slug] = true;
        }
    }

    // HEADER : hero + slider (sous-rubriques spéciales avec repli Default intégré).
    if ($is_visible('header') && function_exists('em_wp_header_get_options')) {
        $header = em_wp_header_get_options($template);

        foreach (['hero_slug', 'slider_slug'] as $key) {
            $slug = sanitize_key((string) ($header[$key] ?? ''));

            if ($slug !== '') {
                $cache[$slug] = true;
            }
        }
    }

    return $cache;
}

/**
 * Libellé (majuscules) du template actuellement live, pour les indicateurs.
 */
function em_wp_catalog_live_template_label(): string
{
    if (!function_exists('em_wp_get_active_template_slug')) {
        return '';
    }

    $active = em_wp_get_active_template_slug();

    if ($active === '') {
        return '';
    }

    $registry = function_exists('em_wp_template_registry') ? em_wp_template_registry() : [];

    return mb_strtoupper((string) ($registry[$active]['label'] ?? $active));
}

/**
 * Couleur d'accent du template actuellement live (ex. pour le badge Live).
 */
function em_wp_catalog_live_template_color(): string
{
    if (!function_exists('em_wp_get_active_template_slug')) {
        return '';
    }

    $active = em_wp_get_active_template_slug();

    if ($active === '' || !function_exists('em_wp_get_template_color')) {
        return '';
    }

    return (string) em_wp_get_template_color($active);
}

/**
 * Indique si un item catalogue est actuellement actif sur le template live.
 */
function em_wp_catalog_entry_is_live(string $entry_slug): bool
{
    $entry_slug = sanitize_key($entry_slug);

    if ($entry_slug === '') {
        return false;
    }

    $active = em_wp_catalog_live_active_entry_slugs();

    return isset($active[$entry_slug]);
}
