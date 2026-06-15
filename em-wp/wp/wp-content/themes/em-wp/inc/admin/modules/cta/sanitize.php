<?php
/**
 * Sanitize options CTA.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_sanitize_rubrique_options($input): array
{
    $template_slug = em_wp_cta_resolve_template_slug();
    $existing = em_wp_cta_get_saved_rubrique_options($template_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);
    $cta_slug = sanitize_key((string) ($input['cta_slug'] ?? ($existing['cta_slug'] ?? '')));

    if ($cta_slug !== '' && function_exists('em_wp_cta_normalize_catalog_slug')) {
        $cta_slug = em_wp_cta_normalize_catalog_slug($cta_slug);
    }

    if ($cta_slug !== '' && function_exists('em_wp_cta_catalog_has') && !em_wp_cta_catalog_has($cta_slug)) {
        $cta_slug = sanitize_key((string) ($existing['cta_slug'] ?? ''));
    }

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        em_wp_admin_sync_rubrique_visibility_from_post('cta');
    }

    return [
        'enabled'          => $enabled,
        'cta_slug'     => $cta_slug,
        'background_color' => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'       => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
    ];
}

function em_wp_cta_sanitize_catalog_options($input): array
{
    if (!is_array($input)) {
        return em_wp_cta_catalog_default_options();
    }

    return [
        'kicker'           => sanitize_text_field($input['kicker'] ?? ''),
        'title_left'       => sanitize_text_field($input['title_left'] ?? ''),
        'title_right'      => sanitize_text_field($input['title_right'] ?? ''),
        'description'      => sanitize_textarea_field($input['description'] ?? ''),
        'hashtag'          => sanitize_text_field($input['hashtag'] ?? ''),
        'stream_label'     => sanitize_text_field($input['stream_label'] ?? ''),
        'stream_link'      => esc_url_raw($input['stream_link'] ?? ''),
        'video_label'      => sanitize_text_field($input['video_label'] ?? ''),
        'video_link'       => esc_url_raw($input['video_link'] ?? ''),
        'tiktok_label'     => sanitize_text_field($input['tiktok_label'] ?? ''),
        'tiktok_link'      => esc_url_raw($input['tiktok_link'] ?? ''),
        'instagram_label'  => sanitize_text_field($input['instagram_label'] ?? ''),
        'instagram_link'   => esc_url_raw($input['instagram_link'] ?? ''),
        'texture_image'    => esc_url_raw($input['texture_image'] ?? ''),
    ];
}

function em_wp_cta_sanitize_options($input, bool $sync_rubrique = true): array
{
    if ($sync_rubrique) {
        return em_wp_cta_sanitize_rubrique_options($input);
    }

    return em_wp_cta_sanitize_catalog_options($input);
}

