<?php
/**
 * Sanitize options CTA.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_sanitize_options($input): array
{
    if (!is_array($input)) {
        return em_wp_cta_get_options(em_wp_cta_admin_template_slug());
    }

    $enabled = !empty($input['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('cta', $enabled);
    }

    return [
        'enabled'          => $enabled,
        'background_color' => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'       => sanitize_hex_color($input['text_color'] ?? '') ?: '',
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
