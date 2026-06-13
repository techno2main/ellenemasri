<?php
/**
 * Sanitize options Video.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_video_sanitize_options($input): array
{
    if (!is_array($input)) {
        return em_wp_video_get_options(em_wp_video_admin_template_slug());
    }

    $enabled = !empty($input['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('video', $enabled);
    }

    return [
        'enabled'            => $enabled,
        'background_color'   => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'         => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'kicker'             => sanitize_text_field($input['kicker'] ?? ''),
        'title'              => sanitize_text_field($input['title'] ?? ''),
        'description'        => sanitize_textarea_field($input['description'] ?? ''),
        'watch_label'        => sanitize_text_field($input['watch_label'] ?? ''),
        'watch_href'         => esc_url_raw($input['watch_href'] ?? ''),
        'watch_disable_link' => !empty($input['watch_disable_link']),
        'cover_image'        => esc_url_raw($input['cover_image'] ?? ''),
    ];
}
