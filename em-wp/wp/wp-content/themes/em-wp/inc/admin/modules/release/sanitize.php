<?php
/**
 * Sanitize options Release.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_sanitize_rubrique_options($input): array
{
    $template_slug = em_wp_release_resolve_template_slug();
    $existing = em_wp_release_get_saved_rubrique_options($template_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);
    $release_slug = sanitize_key((string) ($input['release_slug'] ?? ($existing['release_slug'] ?? '')));

    if ($release_slug !== '' && function_exists('em_wp_release_normalize_catalog_slug')) {
        $release_slug = em_wp_release_normalize_catalog_slug($release_slug);
    }

    if ($release_slug !== '' && function_exists('em_wp_release_catalog_has') && !em_wp_release_catalog_has($release_slug)) {
        $release_slug = sanitize_key((string) ($existing['release_slug'] ?? ''));
    }

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        em_wp_admin_sync_rubrique_visibility_from_post('release');
    }

    return [
        'enabled'          => $enabled,
        'release_slug'     => $release_slug,
        'background_color' => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'       => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
    ];
}

function em_wp_release_sanitize_catalog_options($input): array
{
    if (!is_array($input)) {
        return em_wp_release_catalog_default_options();
    }

    return [
        'kicker'          => sanitize_text_field($input['kicker'] ?? ''),
        'title_left'      => sanitize_text_field($input['title_left'] ?? ''),
        'title_highlight' => sanitize_text_field($input['title_highlight'] ?? ''),
        'cover_image'     => esc_url_raw($input['cover_image'] ?? ''),
        'rows'            => em_wp_release_normalize_rows($input['rows'] ?? []),
    ];
}

function em_wp_release_sanitize_options($input, bool $sync_rubrique = true): array
{
    if ($sync_rubrique) {
        return em_wp_release_sanitize_rubrique_options($input);
    }

    return em_wp_release_sanitize_catalog_options($input);
}
