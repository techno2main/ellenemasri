<?php
/**
 * Sanitize options Stream.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitize options rubrique STREAM (template).
 *
 * @param mixed $input
 * @return array<string, mixed>
 */
function em_site_stream_sanitize_rubrique_options($input): array
{
    $template_slug = em_site_stream_resolve_template_slug();
    $existing = em_site_stream_get_saved_rubrique_options($template_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);
    $stream_slug = sanitize_key((string) ($input['stream_slug'] ?? ($existing['stream_slug'] ?? '')));

    if ($stream_slug !== '' && function_exists('em_site_stream_normalize_catalog_slug')) {
        $stream_slug = em_site_stream_normalize_catalog_slug($stream_slug);
    }

    if ($stream_slug !== '' && function_exists('em_site_stream_catalog_has') && !em_site_stream_catalog_has($stream_slug)) {
        $stream_slug = sanitize_key((string) ($existing['stream_slug'] ?? ''));
    }

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');

    if (function_exists('em_site_admin_sync_rubrique_visibility_from_post')) {
        em_site_admin_sync_rubrique_visibility_from_post('stream');
    }

    return [
        'enabled'          => $enabled,
        'stream_slug'      => $stream_slug,
        'background_color' => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'       => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
    ];
}

/**
 * Sanitize options contenu catalogue Stream.
 *
 * @param mixed $input
 * @return array<string, mixed>
 */
function em_site_stream_sanitize_catalog_options($input): array
{
    if (!is_array($input)) {
        return em_site_stream_catalog_default_options();
    }

    return [
        'kicker'            => sanitize_text_field($input['kicker'] ?? ''),
        'title_prefix'      => sanitize_text_field($input['title_prefix'] ?? ''),
        'title_logo'        => esc_url_raw($input['title_logo'] ?? ''),
        'availability_text' => sanitize_text_field($input['availability_text'] ?? ''),
        'card_label'        => sanitize_text_field($input['card_label'] ?? ''),
        'platforms'         => em_site_stream_sanitize_platforms_from_input($input['platforms'] ?? []),
    ];
}

/**
 * @param mixed $input
 */
function em_site_stream_sanitize_options($input, bool $sync_rubrique = true): array
{
    if ($sync_rubrique) {
        return em_site_stream_sanitize_rubrique_options($input);
    }

    return em_site_stream_sanitize_catalog_options($input);
}
