<?php
/**
 * Résolution des options rubrique × template (fallback V1 Phase 0).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nom d'option effectif (V2 uniquement).
 */
function em_site_template_resolve_option_name(string $rubrique_slug, string $template_slug): string
{
    $rubrique_slug = sanitize_key($rubrique_slug);
    $template_slug = em_site_template_sanitize_slug($template_slug);
    return em_site_template_option_name($rubrique_slug, $template_slug);
}

/**
 * Options brutes pour une rubrique et un template.
 *
 * @return array<string, mixed>
 */
function em_site_get_template_rubrique_options(string $rubrique_slug, ?string $template_slug = null): array
{
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($rubrique_slug === '') {
        return [];
    }

    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_site_get_active_template_slug();
    } else {
        $template_slug = em_site_template_sanitize_slug($template_slug);
    }

    if ($template_slug === '') {
        return [];
    }

    $option_name = em_site_template_resolve_option_name($rubrique_slug, $template_slug);
    $saved = get_option($option_name, []);

    return is_array($saved) ? $saved : [];
}

/**
 * Clé du pointeur catalogue dans les options rubrique (ex. cta_slug, contacts_slug).
 */
function em_site_admin_rubrique_catalog_pointer_key(string $rubrique_slug): string
{
    return str_replace('-', '_', sanitize_key($rubrique_slug)) . '_slug';
}

/**
 * Entrée catalogue par défaut pour une rubrique × template.
 */
function em_site_admin_rubrique_guess_catalog_entry_for_template(string $rubrique_slug, string $template_slug): string
{
    $rubrique_slug = sanitize_key($rubrique_slug);
    $template_slug = em_site_template_sanitize_slug($template_slug);

    if ($rubrique_slug === '' || $template_slug === '') {
        return '';
    }

    $all = function_exists('em_site_admin_site_rubrique_all_definitions')
        ? em_site_admin_site_rubrique_all_definitions()
        : [];
    $catalog_module = sanitize_key((string) ($all[$rubrique_slug]['catalog_module'] ?? $rubrique_slug));

    if ($catalog_module === '') {
        return '';
    }

    $entries = function_exists('em_site_catalog_hub_entries')
        ? em_site_catalog_hub_entries($catalog_module)
        : [];

    if ($entries === [] && function_exists('em_site_custom_catalog_entries')) {
        $entries = em_site_custom_catalog_entries($catalog_module);
    }

    if ($entries === []) {
        return '';
    }

    $candidates = [
        sanitize_key('contact-' . $template_slug),
        sanitize_key($catalog_module . '-' . $template_slug),
        sanitize_key($template_slug),
    ];

    foreach ($candidates as $candidate) {
        if ($candidate !== '' && isset($entries[$candidate])) {
            return $candidate;
        }
    }

    foreach (array_keys($entries) as $entry_slug) {
        $entry_slug = sanitize_key((string) $entry_slug);

        if ($entry_slug !== '' && str_contains($entry_slug, $template_slug)) {
            return $entry_slug;
        }
    }

    $first = array_key_first($entries);

    return is_string($first) ? sanitize_key($first) : '';
}

/**
 * Options rubrique par défaut lors de l'ajout au squelette template.
 *
 * @return array<string, mixed>
 */
function em_site_admin_rubrique_default_template_options(string $rubrique_slug, ?string $template_slug = null): array
{
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($template_slug === null && function_exists('em_site_get_editing_template_slug')) {
        $template_slug = em_site_get_editing_template_slug();
    }

    $template_slug = em_site_template_sanitize_slug((string) $template_slug);
    $options = [
        'enabled'          => true,
        'background_color' => '',
        'text_color'       => '',
    ];

    if ($rubrique_slug !== ''
        && function_exists('em_site_admin_rubrique_is_catalog_linked')
        && em_site_admin_rubrique_is_catalog_linked($rubrique_slug)) {
        $pointer_key = em_site_admin_rubrique_catalog_pointer_key($rubrique_slug);
        $options[$pointer_key] = em_site_admin_rubrique_guess_catalog_entry_for_template($rubrique_slug, $template_slug);
    }

    return $options;
}

/**
 * Persiste les options rubrique pour un template (merge).
 *
 * @param array<string, mixed> $options
 */
function em_site_save_template_rubrique_options(string $template_slug, string $rubrique_slug, array $options): bool
{
    $template_slug = em_site_template_sanitize_slug($template_slug);
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($template_slug === '' || $rubrique_slug === '' || $options === []) {
        return false;
    }

    $option_name = em_site_template_resolve_option_name($rubrique_slug, $template_slug);
    $existing = get_option($option_name, []);

    if (!is_array($existing)) {
        $existing = [];
    }

    $merged = wp_parse_args($options, $existing);

    return (bool) update_option($option_name, $merged, false);
}

/**
 * Initialise les options rubrique (couleurs + pointeur catalogue) à l'insertion squelette.
 *
 * @param array{background_color?:string,text_color?:string} $style_colors
 */
function em_site_template_skeleton_init_rubrique_options(
    string $template_slug,
    string $rubrique_slug,
    array $style_colors = []
): bool {
    $template_slug = em_site_template_sanitize_slug($template_slug);
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($template_slug === '' || $rubrique_slug === '') {
        return false;
    }

    $defaults = function_exists('em_site_admin_module_default_style_colors')
        ? em_site_admin_module_default_style_colors($rubrique_slug)
        : ['background' => '#100421', 'text' => '#ffffff'];
    $background = sanitize_hex_color((string) ($style_colors['background_color'] ?? ''));
    $text = sanitize_hex_color((string) ($style_colors['text_color'] ?? ''));

    if ($background === null || $background === false || $background === '') {
        $background = sanitize_hex_color((string) ($defaults['background'] ?? '#100421')) ?: '#100421';
    }

    if ($text === null || $text === false || $text === '') {
        $text = sanitize_hex_color((string) ($defaults['text'] ?? '#ffffff')) ?: '#ffffff';
    }

    $options = em_site_admin_rubrique_default_template_options($rubrique_slug, $template_slug);
    $options['background_color'] = $background;
    $options['text_color'] = $text;

    if (function_exists('em_site_site_rubrique_is_visibility_toggle')
        && em_site_site_rubrique_is_visibility_toggle($rubrique_slug)) {
        $options['enabled'] = false;
    }

    return em_site_save_template_rubrique_options($template_slug, $rubrique_slug, $options);
}
