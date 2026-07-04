<?php
/**
 * Sanitize options Footer.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_footer_sanitize_rubrique_options($input): array
{
    $template_slug = em_wp_footer_resolve_template_slug();
    $existing = em_wp_footer_get_saved_rubrique_options($template_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);
    $footer_slug = sanitize_key((string) ($input['footer_slug'] ?? ($existing['footer_slug'] ?? '')));

    if ($footer_slug !== '' && function_exists('em_wp_footer_normalize_catalog_slug')) {
        $footer_slug = em_wp_footer_normalize_catalog_slug($footer_slug);
    }

    if ($footer_slug !== '' && function_exists('em_wp_footer_catalog_has') && !em_wp_footer_catalog_has($footer_slug)) {
        $footer_slug = sanitize_key((string) ($existing['footer_slug'] ?? ''));
    }

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        em_wp_admin_sync_rubrique_visibility_from_post('footer');
    }

    return [
        'enabled'          => $enabled,
        'footer_slug'     => $footer_slug,
        'background_color' => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'       => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
    ];
}

function em_wp_footer_sanitize_catalog_options($input): array
{
    if (!is_array($input)) {
        return em_wp_footer_catalog_default_options();
    }

    return [
        'line1'               => sanitize_text_field($input['line1'] ?? ''),
        'line2'               => sanitize_text_field($input['line2'] ?? ''),
        'sticky_stream_label' => sanitize_text_field($input['sticky_stream_label'] ?? ''),
        'sticky_video_label'  => sanitize_text_field($input['sticky_video_label'] ?? ''),
        'sticky_tiktok_label' => sanitize_text_field($input['sticky_tiktok_label'] ?? ''),
        'sticky_tiktok_link'  => esc_url_raw($input['sticky_tiktok_link'] ?? ''),
    ];
}

function em_wp_footer_sanitize_options($input, bool $sync_rubrique = true): array
{
    if ($sync_rubrique) {
        return em_wp_footer_sanitize_rubrique_options($input);
    }

    return em_wp_footer_sanitize_catalog_options($input);
}

