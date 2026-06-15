<?php
/**
 * CRUD registre catalogue Release.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_catalog_slug_from_label(string $label): string
{
    $base = sanitize_title($label);

    if ($base === '') {
        $base = 'release-item';
    }

    if (!str_starts_with($base, 'release-')) {
        $base = 'release-' . $base;
    }

    return sanitize_key($base);
}

function em_wp_release_catalog_unique_slug(string $base_slug, string $except_slug = ''): string
{
    $base_slug = sanitize_key($base_slug);
    $except_slug = sanitize_key($except_slug);

    if ($base_slug === '') {
        $base_slug = 'release-item';
    }

    $entries = em_wp_release_catalog_entries();
    $slug = $base_slug;
    $suffix = 2;

    while (isset($entries[$slug]) && $slug !== $except_slug) {
        $slug = sanitize_key($base_slug . '-' . $suffix);
        $suffix++;
    }

    return $slug;
}

/**
 * @param array<string, array{label:string,layout:string}> $entries
 */
function em_wp_release_catalog_persist_entries(array $entries): bool
{
    if ($entries === []) {
        return false;
    }

    return (bool) update_option(em_wp_release_catalog_option_name(), $entries, false);
}

/**
 * Met à jour les références release_slug dans les rubriques RELEASES de tous les templates.
 */
function em_wp_release_catalog_sync_rubrique_references(string $old_slug, string $new_slug = ''): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_release_get_saved_rubrique_options')) {
        return;
    }

    $old_slug = sanitize_key($old_slug);
    $new_slug = sanitize_key($new_slug);

    foreach (array_keys(em_wp_template_registry()) as $template_slug) {
        $template_slug = sanitize_key((string) $template_slug);

        if ($template_slug === '') {
            continue;
        }

        $options = em_wp_release_get_saved_rubrique_options($template_slug);

        if (sanitize_key((string) ($options['release_slug'] ?? '')) !== $old_slug) {
            continue;
        }

        $options['release_slug'] = $new_slug;
        update_option(em_wp_release_option_name($template_slug), $options, false);
    }
}

function em_wp_release_catalog_migrate_item_options(string $old_slug, string $new_slug): void
{
    $old_slug = sanitize_key($old_slug);
    $new_slug = sanitize_key($new_slug);

    if ($old_slug === '' || $new_slug === '' || $old_slug === $new_slug) {
        return;
    }

    $old_option = em_wp_release_catalog_item_option_name($old_slug);
    $new_option = em_wp_release_catalog_item_option_name($new_slug);
    $saved = get_option($old_option, null);

    if ($saved !== null) {
        update_option($new_option, $saved, false);
        delete_option($old_option);
    }
}

/**
 * @return string|WP_Error
 */
function em_wp_release_catalog_create(string $label)
{
    $label = sanitize_text_field($label);

    if ($label === '') {
        return new WP_Error('em_wp_release_empty_label', __('Le nom de la release est obligatoire.', 'em-wp'));
    }

    $entries = em_wp_release_catalog_entries();
    $slug = em_wp_release_catalog_unique_slug(em_wp_release_catalog_slug_from_label($label));
    $entries[$slug] = [
        'label'  => $label,
        'layout' => 'default',
    ];

    if (!em_wp_release_catalog_persist_entries($entries)) {
        return new WP_Error('em_wp_release_persist_failed', __('Impossible d\'enregistrer le catalogue release.', 'em-wp'));
    }

    update_option(em_wp_release_catalog_item_option_name($slug), em_wp_release_catalog_default_options(), false);

    return $slug;
}

/**
 * @return string|WP_Error
 */
function em_wp_release_catalog_rename(string $old_slug, string $new_label)
{
    $old_slug = sanitize_key($old_slug);
    $new_label = sanitize_text_field($new_label);

    if ($old_slug === '') {
        return new WP_Error('em_wp_release_missing_slug', __('Release introuvable.', 'em-wp'));
    }

    if ($new_label === '') {
        return new WP_Error('em_wp_release_empty_label', __('Le nom de la release est obligatoire.', 'em-wp'));
    }

    $entries = em_wp_release_catalog_entries();

    if (!isset($entries[$old_slug])) {
        return new WP_Error('em_wp_release_not_found', __('Release introuvable.', 'em-wp'));
    }

    $candidate = em_wp_release_catalog_slug_from_label($new_label);
    $new_slug = em_wp_release_catalog_unique_slug($candidate, $old_slug);

    if ($new_slug !== $old_slug && isset($entries[$new_slug])) {
        return new WP_Error('em_wp_release_duplicate_slug', __('Cet identifiant est déjà utilisé.', 'em-wp'));
    }

    $layout = (string) ($entries[$old_slug]['layout'] ?? 'default');
    unset($entries[$old_slug]);
    $entries[$new_slug] = [
        'label'  => $new_label,
        'layout' => $layout !== '' ? $layout : 'default',
    ];

    if (!em_wp_release_catalog_persist_entries($entries)) {
        return new WP_Error('em_wp_release_persist_failed', __('Impossible d\'enregistrer le catalogue release.', 'em-wp'));
    }

    if ($new_slug !== $old_slug) {
        em_wp_release_catalog_migrate_item_options($old_slug, $new_slug);
        em_wp_release_catalog_sync_rubrique_references($old_slug, $new_slug);
    }

    return $new_slug;
}

/**
 * @return true|WP_Error
 */
function em_wp_release_catalog_delete(string $slug)
{
    $slug = sanitize_key($slug);
    $entries = em_wp_release_catalog_entries();

    if ($slug === '' || !isset($entries[$slug])) {
        return new WP_Error('em_wp_release_not_found', __('Release introuvable.', 'em-wp'));
    }

    if (count($entries) <= 1) {
        return new WP_Error('em_wp_release_last_item', __('Impossible de supprimer la dernière release du catalogue.', 'em-wp'));
    }

    unset($entries[$slug]);

    if (!em_wp_release_catalog_persist_entries($entries)) {
        return new WP_Error('em_wp_release_persist_failed', __('Impossible d\'enregistrer le catalogue release.', 'em-wp'));
    }

    delete_option(em_wp_release_catalog_item_option_name($slug));
    em_wp_release_catalog_sync_rubrique_references($slug, '');

    return true;
}

