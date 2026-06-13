<?php
/**
 * Sanitize options Social.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_social_sanitize_options($input): array
{
    if (!is_array($input)) {
        return em_wp_social_get_options(em_wp_social_admin_template_slug());
    }

    $enabled = !empty($input['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('social', $enabled);
    }

    return [
        'enabled'          => $enabled,
        'background_color' => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'       => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'kicker'           => sanitize_text_field($input['kicker'] ?? ''),
        'title_left'       => sanitize_text_field($input['title_left'] ?? ''),
        'title_right'      => sanitize_text_field($input['title_right'] ?? ''),
        'description'      => sanitize_textarea_field($input['description'] ?? ''),
        'platforms'        => em_wp_social_sanitize_platforms_from_input($input['platforms'] ?? []),
    ];
}
