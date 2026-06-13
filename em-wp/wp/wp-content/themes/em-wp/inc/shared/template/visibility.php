<?php
/**
 * Visibilité rubrique × template (stockage V2 — branché front Phase 5).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress : visibilité par template et rubrique.
 */
function em_wp_template_visibility_option_name(): string
{
    return 'em_wp_template_visibility';
}

/**
 * Visibilité enregistrée brute.
 *
 * @return array<string, array<string, bool>>
 */
function em_wp_template_visibility_store(): array
{
    $saved = get_option(em_wp_template_visibility_option_name(), []);

    return is_array($saved) ? $saved : [];
}

/**
 * Indique si une rubrique est visible pour un template.
 *
 * Phase 0 : stockage prêt ; le front continue d'utiliser em_wp_get_site_rubrique_visibility().
 */
function em_wp_is_template_rubrique_visible(string $template_slug, string $rubrique_slug): bool
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($template_slug === '' || $rubrique_slug === '') {
        return true;
    }

    $store = em_wp_template_visibility_store();

    if (!isset($store[$template_slug]) || !is_array($store[$template_slug])) {
        return true;
    }

    if (!array_key_exists($rubrique_slug, $store[$template_slug])) {
        return true;
    }

    return (bool) $store[$template_slug][$rubrique_slug];
}

/**
 * Met à jour la visibilité d'une rubrique pour un template.
 */
function em_wp_set_template_rubrique_visibility(string $template_slug, string $rubrique_slug, bool $visible): bool
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($template_slug === '' || $rubrique_slug === '') {
        return false;
    }

    $store = em_wp_template_visibility_store();

    if (!isset($store[$template_slug]) || !is_array($store[$template_slug])) {
        $store[$template_slug] = [];
    }

    $store[$template_slug][$rubrique_slug] = $visible;

    return update_option(em_wp_template_visibility_option_name(), $store, false);
}
