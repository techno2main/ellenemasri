<?php
/**
 * Entrées des modules catalogue personnalisés.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_custom_catalog_entries_option_name(string $module_slug): string
{
    return 'em_wp_custom_catalog_entries_' . sanitize_key($module_slug);
}

/**
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_custom_catalog_entries(string $module_slug): array
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '' || !em_wp_custom_catalog_is_module($module_slug)) {
        return [];
    }

    $saved = get_option(em_wp_custom_catalog_entries_option_name($module_slug), []);

    if (!is_array($saved)) {
        return [];
    }

    $entries = [];

    foreach ($saved as $slug => $entry) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || !is_array($entry)) {
            continue;
        }

        $entries[$slug] = [
            'label'  => sanitize_text_field((string) ($entry['label'] ?? $slug)),
            'layout' => sanitize_key((string) ($entry['layout'] ?? 'default')) ?: 'default',
        ];
    }

    return $entries;
}

function em_wp_custom_catalog_edit_page_slug(string $module_slug, string $entry_slug): string
{
    $module_slug = sanitize_key($module_slug);
    $entry_slug = sanitize_key($entry_slug);

    if ($module_slug === '' || $entry_slug === '') {
        return '';
    }

    return 'em-wp-cced-' . $module_slug . '-' . $entry_slug;
}

function em_wp_custom_catalog_entry_from_page(string $page_slug): array
{
    $page_slug = sanitize_key($page_slug);
    $prefix = 'em-wp-cced-';

    if (!str_starts_with($page_slug, $prefix)) {
        return [
            'module_slug' => '',
            'entry_slug'  => '',
        ];
    }

    $remainder = substr($page_slug, strlen($prefix));

    foreach (em_wp_custom_catalog_modules() as $module_slug => $module) {
        $module_slug = sanitize_key((string) $module_slug);
        $module_prefix = $module_slug . '-';

        if ($module_slug === '' || !str_starts_with($remainder, $module_prefix)) {
            continue;
        }

        return [
            'module_slug' => $module_slug,
            'entry_slug'  => sanitize_key(substr($remainder, strlen($module_prefix))),
        ];
    }

    return [
        'module_slug' => '',
        'entry_slug'  => '',
    ];
}

function em_wp_custom_catalog_entry_slug_from_label(string $module_slug, string $label): string
{
    $module_slug = sanitize_key($module_slug);
    $base = sanitize_title($label);

    if ($base === '') {
        $base = 'item';
    }

    $prefix = $module_slug . '-';

    if (!str_starts_with($base, $prefix)) {
        $base = $prefix . $base;
    }

    return sanitize_key($base);
}

function em_wp_custom_catalog_unique_entry_slug(string $module_slug, string $base_slug, string $except_slug = ''): string
{
    $module_slug = sanitize_key($module_slug);
    $base_slug = sanitize_key($base_slug);
    $except_slug = sanitize_key($except_slug);
    $entries = em_wp_custom_catalog_entries($module_slug);

    if ($base_slug === '') {
        $base_slug = $module_slug . '-item';
    }

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
function em_wp_custom_catalog_persist_entries(string $module_slug, array $entries): bool
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '' || !em_wp_custom_catalog_is_module($module_slug)) {
        return false;
    }

    return (bool) update_option(em_wp_custom_catalog_entries_option_name($module_slug), $entries, false);
}

/**
 * @return string|WP_Error
 */
function em_wp_custom_catalog_entry_create(string $module_slug, string $label)
{
    $module_slug = sanitize_key($module_slug);
    $label = sanitize_text_field($label);

    if (!em_wp_custom_catalog_is_module($module_slug)) {
        return new WP_Error('em_wp_custom_catalog_module_not_found', __('Catalogue introuvable.', 'em-wp'));
    }

    if ($label === '') {
        return new WP_Error('em_wp_custom_catalog_empty_label', __('Le nom est obligatoire.', 'em-wp'));
    }

    $entries = em_wp_custom_catalog_entries($module_slug);
    $slug = em_wp_custom_catalog_unique_entry_slug(
        $module_slug,
        em_wp_custom_catalog_entry_slug_from_label($module_slug, $label)
    );

    $entries[$slug] = [
        'label'  => $label,
        'layout' => 'default',
    ];

    if (!em_wp_custom_catalog_persist_entries($module_slug, $entries)) {
        return new WP_Error('em_wp_custom_catalog_persist_failed', __('Impossible d\'enregistrer l\'entrée.', 'em-wp'));
    }

    return $slug;
}

/**
 * @return string|WP_Error
 */
function em_wp_custom_catalog_entry_rename(string $module_slug, string $old_slug, string $new_label)
{
    $module_slug = sanitize_key($module_slug);
    $old_slug = sanitize_key($old_slug);
    $new_label = sanitize_text_field($new_label);

    if (!em_wp_custom_catalog_is_module($module_slug)) {
        return new WP_Error('em_wp_custom_catalog_module_not_found', __('Catalogue introuvable.', 'em-wp'));
    }

    if ($old_slug === '') {
        return new WP_Error('em_wp_custom_catalog_missing_slug', __('Entrée introuvable.', 'em-wp'));
    }

    if ($new_label === '') {
        return new WP_Error('em_wp_custom_catalog_empty_label', __('Le nom est obligatoire.', 'em-wp'));
    }

    $entries = em_wp_custom_catalog_entries($module_slug);

    if (!isset($entries[$old_slug])) {
        return new WP_Error('em_wp_custom_catalog_not_found', __('Entrée introuvable.', 'em-wp'));
    }

    $candidate = em_wp_custom_catalog_entry_slug_from_label($module_slug, $new_label);
    $new_slug = em_wp_custom_catalog_unique_entry_slug($module_slug, $candidate, $old_slug);

    if ($new_slug !== $old_slug && isset($entries[$new_slug])) {
        return new WP_Error('em_wp_custom_catalog_duplicate_slug', __('Cet identifiant est déjà utilisé.', 'em-wp'));
    }

    $layout = (string) ($entries[$old_slug]['layout'] ?? 'default');
    unset($entries[$old_slug]);
    $entries[$new_slug] = [
        'label'  => $new_label,
        'layout' => $layout !== '' ? $layout : 'default',
    ];

    if (!em_wp_custom_catalog_persist_entries($module_slug, $entries)) {
        return new WP_Error('em_wp_custom_catalog_persist_failed', __('Impossible d\'enregistrer l\'entrée.', 'em-wp'));
    }

    return $new_slug;
}

/**
 * @return true|WP_Error
 */
function em_wp_custom_catalog_entry_delete(string $module_slug, string $slug)
{
    $module_slug = sanitize_key($module_slug);
    $slug = sanitize_key($slug);
    $entries = em_wp_custom_catalog_entries($module_slug);

    if (!em_wp_custom_catalog_is_module($module_slug)) {
        return new WP_Error('em_wp_custom_catalog_module_not_found', __('Catalogue introuvable.', 'em-wp'));
    }

    if ($slug === '' || !isset($entries[$slug])) {
        return new WP_Error('em_wp_custom_catalog_not_found', __('Entrée introuvable.', 'em-wp'));
    }

    unset($entries[$slug]);

    if (!em_wp_custom_catalog_persist_entries($module_slug, $entries)) {
        return new WP_Error('em_wp_custom_catalog_persist_failed', __('Impossible d\'enregistrer l\'entrée.', 'em-wp'));
    }

    return true;
}

/**
 * Wrappers POST pour le handler générique (module courant via champ hidden).
 *
 * @return string|WP_Error
 */
function em_wp_custom_catalog_entry_create_from_request(string $label)
{
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $module_slug = sanitize_key((string) ($_POST['em_wp_custom_catalog_module'] ?? ''));

    return em_wp_custom_catalog_entry_create($module_slug, $label);
}

/**
 * @return string|WP_Error
 */
function em_wp_custom_catalog_entry_rename_from_request(string $old_slug, string $new_label)
{
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $module_slug = sanitize_key((string) ($_POST['em_wp_custom_catalog_module'] ?? ''));

    return em_wp_custom_catalog_entry_rename($module_slug, $old_slug, $new_label);
}

/**
 * @return true|WP_Error
 */
function em_wp_custom_catalog_entry_delete_from_request(string $slug)
{
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $module_slug = sanitize_key((string) ($_POST['em_wp_custom_catalog_module'] ?? ''));

    return em_wp_custom_catalog_entry_delete($module_slug, $slug);
}

function em_wp_custom_catalog_entry_edit_page_slug_from_request(string $entry_slug): string
{
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $module_slug = sanitize_key((string) ($_POST['em_wp_custom_catalog_module'] ?? ''));

    return em_wp_custom_catalog_edit_page_slug($module_slug, $entry_slug);
}

function em_wp_custom_catalog_entry_slug_from_label_for_request(string $label): string
{
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $module_slug = sanitize_key((string) ($_POST['em_wp_custom_catalog_module'] ?? ''));

    return em_wp_custom_catalog_entry_slug_from_label($module_slug, $label);
}

function em_wp_custom_catalog_unique_entry_slug_for_request(string $base_slug, string $except_slug = ''): string
{
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $module_slug = sanitize_key((string) ($_POST['em_wp_custom_catalog_module'] ?? ''));

    return em_wp_custom_catalog_unique_entry_slug($module_slug, $base_slug, $except_slug);
}

function em_wp_custom_catalog_edit_page_slug_for_module(string $entry_slug): string
{
    $module_slug = em_wp_custom_catalog_current_hub_module_slug();

    return em_wp_custom_catalog_edit_page_slug($module_slug, $entry_slug);
}

function em_wp_custom_catalog_entry_slug_from_label_for_hub(string $label): string
{
    $module_slug = em_wp_custom_catalog_current_hub_module_slug();

    return em_wp_custom_catalog_entry_slug_from_label($module_slug, $label);
}

function em_wp_custom_catalog_unique_entry_slug_for_hub(string $base_slug, string $except_slug = ''): string
{
    $module_slug = em_wp_custom_catalog_current_hub_module_slug();

    return em_wp_custom_catalog_unique_entry_slug($module_slug, $base_slug, $except_slug);
}

function em_wp_custom_catalog_current_hub_module_slug(): string
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    return em_wp_custom_catalog_module_slug_from_hub($page_slug);
}

function em_wp_custom_catalog_current_hub_menu_slug(): string
{
    $module_slug = em_wp_custom_catalog_current_hub_module_slug();

    return em_wp_custom_catalog_hub_menu_slug($module_slug);
}

/**
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_custom_catalog_current_hub_entries(): array
{
    $module_slug = em_wp_custom_catalog_current_hub_module_slug();

    return em_wp_custom_catalog_entries($module_slug);
}

function em_wp_custom_catalog_style_from_page_slug(string $page_slug): string
{
    $resolved = em_wp_custom_catalog_entry_from_page($page_slug);

    return (string) ($resolved['entry_slug'] ?? '');
}

function em_wp_custom_catalog_style_definitions(string $module_slug): array
{
    $definitions = [];

    foreach (em_wp_custom_catalog_entries($module_slug) as $entry_slug => $entry) {
        $label = (string) ($entry['label'] ?? $entry_slug);
        $definitions[$entry_slug] = [
            'label'      => $label,
            'menu_title' => $label,
            'page_slug'  => em_wp_custom_catalog_edit_page_slug($module_slug, $entry_slug),
        ];
    }

    return $definitions;
}
