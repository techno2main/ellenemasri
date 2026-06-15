<?php
/**
 * CRUD registre catalogue Hero.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug catalogue dérivé d'un libellé (identifiant).
 */
function em_wp_hero_catalog_slug_from_label(string $label): string
{
    $base = sanitize_title($label);

    if ($base === '') {
        $base = 'hero-item';
    }

    if (!str_starts_with($base, 'hero-')) {
        $base = 'hero-' . $base;
    }

    return sanitize_key($base);
}

/**
 * Slug unique dans le registre catalogue.
 */
function em_wp_hero_catalog_unique_slug(string $base_slug, string $except_slug = ''): string
{
    $base_slug = sanitize_key($base_slug);
    $except_slug = sanitize_key($except_slug);

    if ($base_slug === '') {
        $base_slug = 'hero-item';
    }

    $entries = em_wp_hero_catalog_entries();
    $slug = $base_slug;
    $suffix = 2;

    while (isset($entries[$slug]) && $slug !== $except_slug) {
        $slug = sanitize_key($base_slug . '-' . $suffix);
        $suffix++;
    }

    return $slug;
}

/**
 * Persiste le registre catalogue Hero.
 *
 * @param array<string, array{label:string,layout:string}> $entries
 */
function em_wp_hero_catalog_persist_entries(array $entries): bool
{
    if ($entries === []) {
        return false;
    }

    return (bool) update_option(em_wp_hero_catalog_option_name(), $entries, false);
}

/**
 * Met à jour les références hero_slug dans les HEADER de tous les templates.
 */
function em_wp_hero_catalog_sync_header_references(string $old_slug, string $new_slug = ''): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_header_get_options')) {
        return;
    }

    $old_slug = sanitize_key($old_slug);
    $new_slug = sanitize_key($new_slug);

    foreach (array_keys(em_wp_template_registry()) as $template_slug) {
        $template_slug = sanitize_key((string) $template_slug);

        if ($template_slug === '') {
            continue;
        }

        $options = function_exists('em_wp_header_get_saved_options')
            ? em_wp_header_get_saved_options($template_slug)
            : em_wp_header_get_options($template_slug);

        if (sanitize_key((string) ($options['hero_slug'] ?? '')) !== $old_slug) {
            continue;
        }

        $options['hero_slug'] = $new_slug;
        update_option(em_wp_header_option_name($template_slug), $options, false);
    }
}

/**
 * Migre les options de contenu d'un hero catalogue.
 */
function em_wp_hero_catalog_migrate_item_options(string $old_slug, string $new_slug): void
{
    $old_slug = sanitize_key($old_slug);
    $new_slug = sanitize_key($new_slug);

    if ($old_slug === '' || $new_slug === '' || $old_slug === $new_slug) {
        return;
    }

    $old_option = em_wp_hero_catalog_item_option_name($old_slug);
    $new_option = em_wp_hero_catalog_item_option_name($new_slug);
    $saved = get_option($old_option, null);

    if ($saved !== null) {
        update_option($new_option, $saved, false);
        delete_option($old_option);
    }
}

/**
 * Crée un hero catalogue.
 *
 * @return string|WP_Error Slug créé.
 */
function em_wp_hero_catalog_create(string $label)
{
    $label = sanitize_text_field($label);

    if ($label === '') {
        return new WP_Error('em_wp_hero_empty_label', __('Le nom du hero est obligatoire.', 'em-wp'));
    }

    $entries = em_wp_hero_catalog_entries();
    $slug = em_wp_hero_catalog_unique_slug(em_wp_hero_catalog_slug_from_label($label));
    $entries[$slug] = [
        'label'  => $label,
        'layout' => 'default',
    ];

    if (!em_wp_hero_catalog_persist_entries($entries)) {
        return new WP_Error('em_wp_hero_persist_failed', __('Impossible d\'enregistrer le catalogue hero.', 'em-wp'));
    }

    $defaults = function_exists('em_wp_hero_default_options') ? em_wp_hero_default_options() : [];
    update_option(em_wp_hero_catalog_item_option_name($slug), $defaults, false);

    return $slug;
}

/**
 * Renomme un hero catalogue (met à jour l'identifiant si le libellé le impose).
 *
 * @return string|WP_Error Nouveau slug.
 */
function em_wp_hero_catalog_rename(string $old_slug, string $new_label)
{
    $old_slug = sanitize_key($old_slug);
    $new_label = sanitize_text_field($new_label);

    if ($old_slug === '') {
        return new WP_Error('em_wp_hero_missing_slug', __('Hero introuvable.', 'em-wp'));
    }

    if ($new_label === '') {
        return new WP_Error('em_wp_hero_empty_label', __('Le nom du hero est obligatoire.', 'em-wp'));
    }

    $entries = em_wp_hero_catalog_entries();

    if (!isset($entries[$old_slug])) {
        return new WP_Error('em_wp_hero_not_found', __('Hero introuvable.', 'em-wp'));
    }

    $candidate = em_wp_hero_catalog_slug_from_label($new_label);
    $new_slug = em_wp_hero_catalog_unique_slug($candidate, $old_slug);

    if ($new_slug !== $old_slug && isset($entries[$new_slug])) {
        return new WP_Error('em_wp_hero_duplicate_slug', __('Cet identifiant est déjà utilisé.', 'em-wp'));
    }

    $layout = (string) ($entries[$old_slug]['layout'] ?? 'default');
    unset($entries[$old_slug]);
    $entries[$new_slug] = [
        'label'  => $new_label,
        'layout' => $layout !== '' ? $layout : 'default',
    ];

    if (!em_wp_hero_catalog_persist_entries($entries)) {
        return new WP_Error('em_wp_hero_persist_failed', __('Impossible d\'enregistrer le catalogue hero.', 'em-wp'));
    }

    if ($new_slug !== $old_slug) {
        em_wp_hero_catalog_migrate_item_options($old_slug, $new_slug);
        em_wp_hero_catalog_sync_header_references($old_slug, $new_slug);
    }

    return $new_slug;
}

/**
 * Supprime un hero catalogue.
 *
 * @return true|WP_Error
 */
function em_wp_hero_catalog_delete(string $slug)
{
    $slug = sanitize_key($slug);
    $entries = em_wp_hero_catalog_entries();

    if ($slug === '' || !isset($entries[$slug])) {
        return new WP_Error('em_wp_hero_not_found', __('Hero introuvable.', 'em-wp'));
    }

    if (count($entries) <= 1) {
        return new WP_Error('em_wp_hero_last_item', __('Impossible de supprimer le dernier hero du catalogue.', 'em-wp'));
    }

    unset($entries[$slug]);

    if (!em_wp_hero_catalog_persist_entries($entries)) {
        return new WP_Error('em_wp_hero_persist_failed', __('Impossible d\'enregistrer le catalogue hero.', 'em-wp'));
    }

    delete_option(em_wp_hero_catalog_item_option_name($slug));
    em_wp_hero_catalog_sync_header_references($slug, '');

    return true;
}
