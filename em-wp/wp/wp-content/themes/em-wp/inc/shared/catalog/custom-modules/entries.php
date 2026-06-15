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
 * Index de tri d'une entrée catalogue selon l'ordre des templates (Mayami, Ellene…).
 */
function em_wp_custom_catalog_entry_template_sort_index(string $entry_slug, array $entry, array $template_slugs): int
{
    $entry_slug = sanitize_key($entry_slug);
    $haystack = strtolower($entry_slug . ' ' . sanitize_key((string) ($entry['label'] ?? '')));

    foreach ($template_slugs as $index => $template_slug) {
        $template_slug = sanitize_key((string) $template_slug);

        if ($template_slug !== '' && str_contains($haystack, $template_slug)) {
            return (int) $index;
        }
    }

    return count($template_slugs);
}

/**
 * Aligne l'ordre des entrées sur le registre template (Mayami avant Ellene).
 *
 * @param array<string, array{label:string,layout:string}> $entries
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_custom_catalog_sort_entries_by_template(array $entries): array
{
    if ($entries === [] || !function_exists('em_wp_template_registry')) {
        return $entries;
    }

    $template_slugs = array_keys(em_wp_template_registry());

    if ($template_slugs === []) {
        return $entries;
    }

    $is_template_scoped = false;

    foreach ($entries as $entry_slug => $entry) {
        if (!is_array($entry)) {
            continue;
        }

        if (em_wp_custom_catalog_entry_template_sort_index((string) $entry_slug, $entry, $template_slugs) < count($template_slugs)) {
            $is_template_scoped = true;
            break;
        }
    }

    if (!$is_template_scoped) {
        return $entries;
    }

    uksort(
        $entries,
        static function (string $slug_a, string $slug_b) use ($entries, $template_slugs): int {
            $index_a = em_wp_custom_catalog_entry_template_sort_index($slug_a, $entries[$slug_a] ?? [], $template_slugs);
            $index_b = em_wp_custom_catalog_entry_template_sort_index($slug_b, $entries[$slug_b] ?? [], $template_slugs);

            if ($index_a !== $index_b) {
                return $index_a <=> $index_b;
            }

            return strcmp($slug_a, $slug_b);
        }
    );

    return $entries;
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

    return em_wp_custom_catalog_sort_entries_by_template($entries);
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

function em_wp_custom_catalog_entry_slug_prefix(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);
    $module = em_wp_custom_catalog_module($module_slug);
    $label = trim(sanitize_text_field((string) ($module['label'] ?? '')));

    if ($label !== '') {
        $base = sanitize_title($label);

        if ($base !== '' && str_ends_with($base, 's')) {
            $base = substr($base, 0, -1);
        }

        if ($base !== '') {
            return sanitize_key($base);
        }
    }

    $fallback = preg_replace('/^custom-/', '', $module_slug);

    if (is_string($fallback) && $fallback !== '') {
        if (str_ends_with($fallback, 's')) {
            $fallback = substr($fallback, 0, -1);
        }

        return sanitize_key($fallback);
    }

    return sanitize_key($module_slug);
}

/**
 * @return string[]
 */
function em_wp_custom_catalog_legacy_entry_slug_variants(string $module_slug, string $entry_slug): array
{
    $module_slug = sanitize_key($module_slug);
    $entry_slug = sanitize_key($entry_slug);
    $variants = [];

    if ($entry_slug !== '') {
        $variants[] = $entry_slug;
    }

    $legacy_prefix = $module_slug . '-';

    while ($entry_slug !== '' && str_starts_with($entry_slug, $legacy_prefix)) {
        $entry_slug = sanitize_key(substr($entry_slug, strlen($legacy_prefix)));
        $variants[] = $entry_slug;
    }

    return array_values(array_unique(array_filter($variants)));
}

function em_wp_custom_catalog_resolve_entry_slug(string $module_slug, string $entry_slug): string
{
    $module_slug = sanitize_key($module_slug);
    $entry_slug = sanitize_key($entry_slug);

    if ($module_slug === '') {
        return '';
    }

    $entries = em_wp_custom_catalog_entries($module_slug);

    foreach (em_wp_custom_catalog_legacy_entry_slug_variants($module_slug, $entry_slug) as $candidate) {
        if (isset($entries[$candidate])) {
            return $candidate;
        }
    }

    return '';
}

function em_wp_custom_catalog_entry_slug_from_label(string $module_slug, string $label): string
{
    $module_slug = sanitize_key($module_slug);
    $prefix = em_wp_custom_catalog_entry_slug_prefix($module_slug);
    $base = sanitize_title($label);

    if ($base === '') {
        $base = 'item';
    }

    if ($prefix === '') {
        return sanitize_key($base);
    }

    $prefix_with_dash = $prefix . '-';

    if (str_starts_with($base, $prefix_with_dash) || $base === $prefix) {
        return sanitize_key($base);
    }

    return sanitize_key($prefix_with_dash . $base);
}

function em_wp_custom_catalog_entry_slug_prefix_with_dash(string $module_slug): string
{
    $prefix = em_wp_custom_catalog_entry_slug_prefix($module_slug);

    return $prefix !== '' ? $prefix . '-' : '';
}

function em_wp_custom_catalog_unique_entry_slug(string $module_slug, string $base_slug, string $except_slug = ''): string
{
    $module_slug = sanitize_key($module_slug);
    $base_slug = sanitize_key($base_slug);
    $except_slug = sanitize_key($except_slug);
    $entries = em_wp_custom_catalog_entries($module_slug);

    if ($base_slug === '') {
        $prefix = em_wp_custom_catalog_entry_slug_prefix($module_slug);
        $base_slug = $prefix !== '' ? $prefix . '-item' : $module_slug . '-item';
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

    if (function_exists('em_wp_custom_catalog_init_entry_options')) {
        em_wp_custom_catalog_init_entry_options($module_slug, $slug);
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

    $resolved_old_slug = em_wp_custom_catalog_resolve_entry_slug($module_slug, $old_slug);

    if ($resolved_old_slug !== '') {
        $old_slug = $resolved_old_slug;
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

    if (function_exists('em_wp_custom_catalog_migrate_entry_options')) {
        em_wp_custom_catalog_migrate_entry_options($module_slug, $old_slug, $new_slug);
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

    $resolved_slug = em_wp_custom_catalog_resolve_entry_slug($module_slug, $slug);

    if ($resolved_slug !== '') {
        $slug = $resolved_slug;
    }

    if ($slug === '' || !isset($entries[$slug])) {
        return new WP_Error('em_wp_custom_catalog_not_found', __('Entrée introuvable.', 'em-wp'));
    }

    unset($entries[$slug]);

    if (!em_wp_custom_catalog_persist_entries($module_slug, $entries)) {
        return new WP_Error('em_wp_custom_catalog_persist_failed', __('Impossible d\'enregistrer l\'entrée.', 'em-wp'));
    }

    if (function_exists('em_wp_custom_catalog_delete_entry_options')) {
        em_wp_custom_catalog_delete_entry_options($module_slug, $slug);
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

/**
 * Réaligne les slugs d'entrées sur le format catalogue (contact-mayami, hero-mayami…).
 */
function em_wp_custom_catalog_normalize_entry_slugs(string $module_slug): bool
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return false;
    }

    $option_name = em_wp_custom_catalog_entries_option_name($module_slug);
    $saved = get_option($option_name, []);

    if (!is_array($saved) || $saved === []) {
        return false;
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

    if ($entries === []) {
        return false;
    }

    $normalized = [];
    $changed = false;

    foreach ($entries as $slug => $entry) {
        $label = trim((string) ($entry['label'] ?? $slug));
        $target = em_wp_custom_catalog_entry_slug_from_label($module_slug, $label !== '' ? $label : $slug);
        $suffix = 2;

        while (isset($normalized[$target])) {
            $target = sanitize_key($target . '-' . $suffix);
            $suffix++;
        }

        if ($slug !== $target) {
            if (function_exists('em_wp_custom_catalog_migrate_entry_options')) {
                em_wp_custom_catalog_migrate_entry_options($module_slug, $slug, $target);
            }

            $changed = true;
        }

        $normalized[$target] = [
            'label'  => sanitize_text_field($label !== '' ? $label : (string) ($entry['label'] ?? $target)),
            'layout' => sanitize_key((string) ($entry['layout'] ?? 'default')) ?: 'default',
        ];
    }

    if (!$changed) {
        return false;
    }

    return (bool) update_option($option_name, $normalized, false);
}

/**
 * Normalise les slugs de tous les catalogues personnalisés (migration idempotente).
 */
function em_wp_custom_catalog_maybe_normalize_all_entry_slugs(): void
{
    $module_slugs = array_keys(em_wp_custom_catalog_modules());

    if (function_exists('em_wp_contacts_catalog_module_slug')) {
        $contacts_slug = em_wp_contacts_catalog_module_slug();

        if ($contacts_slug !== '' && !in_array($contacts_slug, $module_slugs, true)) {
            $module_slugs[] = $contacts_slug;
        }
    }

    foreach ($module_slugs as $module_slug) {
        em_wp_custom_catalog_normalize_entry_slugs((string) $module_slug);
    }
}
