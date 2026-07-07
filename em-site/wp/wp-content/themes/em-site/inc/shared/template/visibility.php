<?php
/**
 * Visibilité rubrique × template (stockage V2 — branché front Phase 5).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress : visibilité par template et rubrique.
 */
function em_site_template_visibility_option_name(): string
{
    return 'em_site_template_visibility';
}

/**
 * Visibilité enregistrée brute.
 *
 * @return array<string, array<string, bool>>
 */
function em_site_template_visibility_store(): array
{
    $saved = get_option(em_site_template_visibility_option_name(), []);

    return is_array($saved) ? $saved : [];
}

/**
 * Rubriques dont la visibilité est portée par les options template V2 (enabled).
 *
 * @return string[]
 */
function em_site_template_scoped_rubrique_slugs(): array
{
    return [
        'stream',
        'video',
        'release',
        'top-bar',
        'social',
        'cta',
        'footer',
        'contacts',
    ];
}

/**
 * Visibilité sommaire HEADER (store template, indépendante de `enabled` contenu).
 */
function em_site_get_header_rubrique_visibility(?string $template_slug = null): bool
{
    $template_slug = em_site_resolve_rubrique_visibility_template_slug($template_slug);

    if ($template_slug === '') {
        return true;
    }

    $store = em_site_template_visibility_store();

    if (isset($store[$template_slug]['header'])) {
        return (bool) $store[$template_slug]['header'];
    }

    // Migration : anciennes sauvegardes dans enabled × template HEADER.
    if (function_exists('em_site_get_template_rubrique_options')) {
        $raw = em_site_get_template_rubrique_options('header', $template_slug);

        if (array_key_exists('enabled', $raw)) {
            return (bool) $raw['enabled'];
        }
    }

    return true;
}

/**
 * Enregistre la visibilité sommaire HEADER pour un template.
 */
function em_site_set_header_rubrique_visibility(bool $visible, ?string $template_slug = null): bool
{
    $template_slug = em_site_resolve_rubrique_visibility_template_slug($template_slug);

    if ($template_slug === '') {
        return false;
    }

    return em_site_set_template_rubrique_visibility($template_slug, 'header', $visible);
}

/**
 * Indique si une rubrique utilise les options par template (visibilité indépendante).
 */
function em_site_rubrique_uses_template_scoped_options(string $module_slug): bool
{
    return in_array(sanitize_key($module_slug), em_site_template_scoped_rubrique_slugs(), true);
}

/**
 * Slug template pour résoudre la visibilité (admin = édition, front = live).
 */
function em_site_resolve_rubrique_visibility_template_slug(?string $template_slug = null): string
{
    if ($template_slug !== null && $template_slug !== '') {
        return em_site_template_sanitize_slug($template_slug);
    }

    if (is_admin() && function_exists('em_site_get_editing_template_slug')) {
        return em_site_get_editing_template_slug();
    }

    if (function_exists('em_site_front_get_live_template_slug')) {
        return em_site_front_get_live_template_slug();
    }

    return function_exists('em_site_get_active_template_slug') ? em_site_get_active_template_slug() : '';
}

/**
 * Visibilité (enabled) d'une rubrique pour un template via options V2.
 */
function em_site_get_rubrique_enabled_for_template(string $module_slug, ?string $template_slug = null): bool
{
    $module_slug = sanitize_key($module_slug);
    $template_slug = em_site_resolve_rubrique_visibility_template_slug($template_slug);

    if ($module_slug === '' || $template_slug === '') {
        return true;
    }

    $saved = em_site_get_template_rubrique_options($module_slug, $template_slug);

    if (array_key_exists('enabled', $saved)) {
        return (bool) $saved['enabled'];
    }

    return true;
}

/**
 * Met à jour enabled dans les options template d'une rubrique.
 */
function em_site_set_rubrique_enabled_for_template(string $module_slug, bool $enabled, ?string $template_slug = null): bool
{
    $module_slug = sanitize_key($module_slug);
    $template_slug = em_site_resolve_rubrique_visibility_template_slug($template_slug);

    if ($module_slug === '' || $template_slug === '') {
        return false;
    }

    $option_name = em_site_template_resolve_option_name($module_slug, $template_slug);
    $saved = get_option($option_name, []);

    if (!is_array($saved)) {
        $saved = [];
    }

    if ((bool) ($saved['enabled'] ?? true) === $enabled) {
        return true;
    }

    $saved['enabled'] = $enabled;

    return update_option($option_name, $saved, false);
}

/**
 * Indique si une rubrique est visible pour un template.
 *
 * Rubriques V2 (stream, video, release) : `enabled` dans options template.
 * Autres rubriques : store `em_site_template_visibility` puis défaut visible.
 */
function em_site_is_template_rubrique_visible(string $template_slug, string $rubrique_slug): bool
{
    $template_slug = em_site_template_sanitize_slug($template_slug);
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($template_slug === '' || $rubrique_slug === '') {
        return true;
    }

    if ($rubrique_slug === 'header' && function_exists('em_site_get_header_rubrique_visibility')) {
        return em_site_get_header_rubrique_visibility($template_slug);
    }

    if (em_site_rubrique_uses_template_scoped_options($rubrique_slug)) {
        return em_site_get_rubrique_enabled_for_template($rubrique_slug, $template_slug);
    }

    $store = em_site_template_visibility_store();

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
function em_site_set_template_rubrique_visibility(string $template_slug, string $rubrique_slug, bool $visible): bool
{
    $template_slug = em_site_template_sanitize_slug($template_slug);
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($template_slug === '' || $rubrique_slug === '') {
        return false;
    }

    $store = em_site_template_visibility_store();

    if (!isset($store[$template_slug]) || !is_array($store[$template_slug])) {
        $store[$template_slug] = [];
    }

    $store[$template_slug][$rubrique_slug] = $visible;

    return update_option(em_site_template_visibility_option_name(), $store, false);
}
