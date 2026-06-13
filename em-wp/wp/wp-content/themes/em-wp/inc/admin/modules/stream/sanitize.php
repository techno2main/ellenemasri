<?php
/**
 * Sanitize options Stream.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitize Settings API Stream.
 *
 * @param mixed $input
 */
function em_wp_stream_sanitize_options($input): array
{
    if (!is_array($input)) {
        return em_wp_stream_get_options(em_wp_stream_admin_template_slug());
    }

    $enabled = !empty($input['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('stream', $enabled);
    }

    return [
        'enabled'           => $enabled,
        'background_color'  => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'        => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'kicker'            => sanitize_text_field($input['kicker'] ?? ''),
        'title_prefix'      => sanitize_text_field($input['title_prefix'] ?? ''),
        'title_logo'        => esc_url_raw($input['title_logo'] ?? ''),
        'availability_text' => sanitize_text_field($input['availability_text'] ?? ''),
        'card_label'        => sanitize_text_field($input['card_label'] ?? ''),
        'platforms'         => em_wp_stream_sanitize_platforms_from_input($input['platforms'] ?? []),
    ];
}
