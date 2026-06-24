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
