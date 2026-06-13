<?php
/**
 * Sanitize options Release.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_sanitize_options($input): array
{
    if (!is_array($input)) {
        return em_wp_release_get_options(em_wp_release_admin_template_slug());
    }

    $enabled = !empty($input['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('release', $enabled);
    }

    return [
        'enabled'          => $enabled,
        'background_color' => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'       => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'kicker'           => sanitize_text_field($input['kicker'] ?? ''),
        'title_left'       => sanitize_text_field($input['title_left'] ?? ''),
        'title_highlight'  => sanitize_text_field($input['title_highlight'] ?? ''),
        'cover_image'      => esc_url_raw($input['cover_image'] ?? ''),
        'rows'             => em_wp_release_normalize_rows($input['rows'] ?? []),
    ];
}
