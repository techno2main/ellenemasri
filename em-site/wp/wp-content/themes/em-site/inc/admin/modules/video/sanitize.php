<?php
/**
 * Sanitize options Video.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitize options rubrique VIDEOS (template).
 *
 * @param mixed $input
 * @return array<string, mixed>
 */
function em_site_video_sanitize_rubrique_options($input): array
{
    $template_slug = em_site_video_resolve_template_slug();
    $existing = em_site_video_get_saved_rubrique_options($template_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);
    $video_slug = sanitize_key((string) ($input['video_slug'] ?? ($existing['video_slug'] ?? '')));

    if ($video_slug !== '' && function_exists('em_site_video_normalize_catalog_slug')) {
        $video_slug = em_site_video_normalize_catalog_slug($video_slug);
    }

    if ($video_slug !== '' && function_exists('em_site_video_catalog_has') && !em_site_video_catalog_has($video_slug)) {
        $video_slug = sanitize_key((string) ($existing['video_slug'] ?? ''));
    }

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');

    if (function_exists('em_site_admin_sync_rubrique_visibility_from_post')) {
        em_site_admin_sync_rubrique_visibility_from_post('video');
    }

    return [
        'enabled'          => $enabled,
        'video_slug'       => $video_slug,
        'background_color' => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'       => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
    ];
}

/**
 * Sanitize options contenu catalogue Video.
 *
 * @param mixed $input
 * @return array<string, mixed>
 */
function em_site_video_sanitize_catalog_options($input): array
{
    if (!is_array($input)) {
        return em_site_video_catalog_default_options();
    }

    return [
        'kicker'             => sanitize_text_field($input['kicker'] ?? ''),
        'title'              => sanitize_text_field($input['title'] ?? ''),
        'description'        => sanitize_textarea_field($input['description'] ?? ''),
        'watch_label'        => sanitize_text_field($input['watch_label'] ?? ''),
        'watch_href'         => esc_url_raw($input['watch_href'] ?? ''),
        'watch_disable_link' => !empty($input['watch_disable_link']),
        'cover_image'        => esc_url_raw($input['cover_image'] ?? ''),
    ];
}

/**
 * @param mixed $input
 */
function em_site_video_sanitize_options($input, bool $sync_rubrique = true): array
{
    if ($sync_rubrique) {
        return em_site_video_sanitize_rubrique_options($input);
    }

    return em_site_video_sanitize_catalog_options($input);
}
