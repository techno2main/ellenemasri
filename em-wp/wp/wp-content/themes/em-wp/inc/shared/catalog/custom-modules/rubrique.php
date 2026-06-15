<?php
/**
 * Rubriques template liées aux catalogues personnalisés (CONTACTS, …).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug page admin rubrique template (ex. em-wp-contacts).
 */
function em_wp_custom_catalog_rubrique_page_slug(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);

    return $module_slug !== '' ? 'em-wp-' . $module_slug : '';
}

/**
 * Clé POST / registre sauvegarde (ex. em_wp_contacts_options).
 */
function em_wp_custom_catalog_rubrique_form_option_key(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);

    return $module_slug !== '' ? 'em_wp_' . str_replace('-', '_', $module_slug) . '_options' : '';
}

/**
 * Résout le module catalogue depuis le slug page admin rubrique.
 */
function em_wp_custom_catalog_rubrique_module_from_page_slug(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);

    if ($page_slug === '' || !function_exists('em_wp_custom_catalog_modules')) {
        return '';
    }

    foreach (array_keys(em_wp_custom_catalog_modules()) as $module_slug) {
        if (em_wp_custom_catalog_rubrique_page_slug((string) $module_slug) === $page_slug) {
            return sanitize_key((string) $module_slug);
        }
    }

    return '';
}

/**
 * @return array<string, mixed>
 */
function em_wp_custom_catalog_rubrique_default_options(string $module_slug): array
{
    $module_slug = sanitize_key($module_slug);
    $pointer_key = function_exists('em_wp_admin_rubrique_catalog_pointer_key')
        ? em_wp_admin_rubrique_catalog_pointer_key($module_slug)
        : $module_slug . '_slug';

    return [
        'enabled'          => true,
        'background_color' => '',
        'text_color'       => '',
        $pointer_key       => '',
    ];
}

function em_wp_custom_catalog_rubrique_option_name(string $module_slug, ?string $template_slug = null): string
{
    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_wp_custom_catalog_rubrique_resolve_template_slug();
    }

    return em_wp_template_resolve_option_name($module_slug, $template_slug);
}

function em_wp_custom_catalog_rubrique_resolve_template_slug(?string $preferred = null): string
{
    $preferred = sanitize_key((string) ($preferred ?? ''));

    if ($preferred !== '' && function_exists('em_wp_template_exists') && em_wp_template_exists($preferred)) {
        return $preferred;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $from_post = sanitize_key((string) ($_POST['em_wp_template_context'] ?? ''));

    if ($from_post !== '' && function_exists('em_wp_template_exists') && em_wp_template_exists($from_post)) {
        return $from_post;
    }

    if (is_admin() && function_exists('em_wp_get_editing_template_slug')) {
        return em_wp_get_editing_template_slug();
    }

    return function_exists('em_wp_front_get_live_template_slug')
        ? em_wp_front_get_live_template_slug()
        : em_wp_get_active_template_slug();
}

/**
 * @return array<string, mixed>
 */
function em_wp_custom_catalog_rubrique_get_saved_options(string $module_slug, ?string $template_slug = null): array
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return [];
    }

    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_wp_custom_catalog_rubrique_resolve_template_slug();
    }

    return wp_parse_args(
        em_wp_get_template_rubrique_options($module_slug, $template_slug),
        em_wp_custom_catalog_rubrique_default_options($module_slug)
    );
}

function em_wp_custom_catalog_rubrique_resolve_entry_slug(string $module_slug, ?string $template_slug, array $rubrique): string
{
    $module_slug = sanitize_key($module_slug);
    $pointer_key = function_exists('em_wp_admin_rubrique_catalog_pointer_key')
        ? em_wp_admin_rubrique_catalog_pointer_key($module_slug)
        : $module_slug . '_slug';
    $slug = sanitize_key((string) ($rubrique[$pointer_key] ?? ''));

    if ($slug !== '' && em_wp_custom_catalog_rubrique_catalog_has($module_slug, $slug)) {
        return $slug;
    }

    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_wp_custom_catalog_rubrique_resolve_template_slug();
    }

    if (function_exists('em_wp_admin_rubrique_guess_catalog_entry_for_template')) {
        return em_wp_admin_rubrique_guess_catalog_entry_for_template($module_slug, $template_slug);
    }

    return '';
}

/**
 * @return array<string, mixed>
 */
function em_wp_custom_catalog_rubrique_get_options(string $module_slug, ?string $template_slug = null): array
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return [];
    }

    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_wp_custom_catalog_rubrique_resolve_template_slug();
    }

    $options = em_wp_custom_catalog_rubrique_get_saved_options($module_slug, $template_slug);
    $pointer_key = function_exists('em_wp_admin_rubrique_catalog_pointer_key')
        ? em_wp_admin_rubrique_catalog_pointer_key($module_slug)
        : $module_slug . '_slug';
    $options[$pointer_key] = em_wp_custom_catalog_rubrique_resolve_entry_slug($module_slug, $template_slug, $options);

    return function_exists('em_wp_rubrique_sync_enabled_for_admin')
        ? em_wp_rubrique_sync_enabled_for_admin($module_slug, $options)
        : $options;
}

/**
 * @return array<string, string>
 */
function em_wp_custom_catalog_rubrique_catalog_choices(string $module_slug): array
{
    $module_slug = sanitize_key($module_slug);
    $choices = [];

    if ($module_slug === '' || !function_exists('em_wp_custom_catalog_entries')) {
        return $choices;
    }

    foreach (em_wp_custom_catalog_entries($module_slug) as $slug => $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $choices[sanitize_key((string) $slug)] = (string) ($entry['label'] ?? $slug);
    }

    return $choices;
}

function em_wp_custom_catalog_rubrique_catalog_has(string $module_slug, string $entry_slug): bool
{
    $module_slug = sanitize_key($module_slug);
    $entry_slug = sanitize_key($entry_slug);

    if ($module_slug === '' || $entry_slug === '' || !function_exists('em_wp_custom_catalog_entries')) {
        return false;
    }

    return isset(em_wp_custom_catalog_entries($module_slug)[$entry_slug]);
}

/**
 * @return array<string, mixed>
 */
function em_wp_custom_catalog_rubrique_sanitize_options(string $module_slug, $input): array
{
    $module_slug = sanitize_key($module_slug);
    $template_slug = em_wp_custom_catalog_rubrique_resolve_template_slug();
    $existing = em_wp_custom_catalog_rubrique_get_saved_options($module_slug, $template_slug);
    $pointer_key = function_exists('em_wp_admin_rubrique_catalog_pointer_key')
        ? em_wp_admin_rubrique_catalog_pointer_key($module_slug)
        : $module_slug . '_slug';

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);
    $entry_slug = sanitize_key((string) ($input[$pointer_key] ?? ($existing[$pointer_key] ?? '')));

    if ($entry_slug !== '' && !em_wp_custom_catalog_rubrique_catalog_has($module_slug, $entry_slug)) {
        $entry_slug = sanitize_key((string) ($existing[$pointer_key] ?? ''));
    }

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        em_wp_admin_sync_rubrique_visibility_from_post($module_slug);
    }

    return [
        'enabled'          => $enabled,
        $pointer_key       => $entry_slug,
        'background_color' => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'       => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
    ];
}

/**
 * Options rubrique × entrée catalogue pour le front.
 *
 * @return array<string, mixed>
 */
function em_wp_custom_catalog_rubrique_merge_with_catalog_entry(string $module_slug, array $rubrique, ?string $template_slug = null): array
{
    $module_slug = sanitize_key($module_slug);
    $pointer_key = function_exists('em_wp_admin_rubrique_catalog_pointer_key')
        ? em_wp_admin_rubrique_catalog_pointer_key($module_slug)
        : $module_slug . '_slug';

    if ($template_slug === null || $template_slug === '') {
        $template_slug = function_exists('em_wp_front_get_live_template_slug')
            ? em_wp_front_get_live_template_slug()
            : em_wp_get_active_template_slug();
    }

    $entry_slug = sanitize_key((string) ($rubrique[$pointer_key] ?? ''));

    if ($entry_slug === '') {
        $entry_slug = em_wp_custom_catalog_rubrique_resolve_entry_slug($module_slug, $template_slug, $rubrique);
    }

    $catalog = $entry_slug !== ''
        ? em_wp_custom_catalog_get_entry_options($module_slug, $entry_slug)
        : em_wp_custom_catalog_entry_default_options($module_slug);

    $merged = wp_parse_args($catalog, em_wp_custom_catalog_entry_default_options($module_slug));
    $merged['enabled'] = !empty($rubrique['enabled']);
    $merged[$pointer_key] = $entry_slug;
    $merged['background_color'] = (string) ($rubrique['background_color'] ?? '');
    $merged['text_color'] = (string) ($rubrique['text_color'] ?? '');

    return $merged;
}

/**
 * @return array<string, mixed>
 */
function em_wp_custom_catalog_rubrique_get_options_for_front(string $module_slug): array
{
    $module_slug = sanitize_key($module_slug);
    $template_slug = function_exists('em_wp_front_get_live_template_slug')
        ? em_wp_front_get_live_template_slug()
        : em_wp_get_active_template_slug();

    return em_wp_custom_catalog_rubrique_merge_with_catalog_entry(
        $module_slug,
        em_wp_custom_catalog_rubrique_get_saved_options($module_slug, $template_slug),
        $template_slug
    );
}
