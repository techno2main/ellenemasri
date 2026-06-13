<?php
/**
 * Sanitize options Footer.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_footer_sanitize_options($input): array
{
    if (!is_array($input)) {
        return em_wp_footer_get_options(em_wp_footer_admin_template_slug());
    }

    $enabled = !empty($input['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('footer', $enabled);
    }

    return [
        'enabled'             => $enabled,
        'background_color'    => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'          => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'line1'               => sanitize_text_field($input['line1'] ?? ''),
        'line2'               => sanitize_text_field($input['line2'] ?? ''),
        'sticky_stream_label' => sanitize_text_field($input['sticky_stream_label'] ?? ''),
        'sticky_video_label'  => sanitize_text_field($input['sticky_video_label'] ?? ''),
        'sticky_tiktok_label' => sanitize_text_field($input['sticky_tiktok_label'] ?? ''),
        'sticky_tiktok_link'  => esc_url_raw($input['sticky_tiktok_link'] ?? ''),
    ];
}
