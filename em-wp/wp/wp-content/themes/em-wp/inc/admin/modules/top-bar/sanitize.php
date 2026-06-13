<?php
/**
 * Sanitize options Top Bar.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitize callback de Settings API.
 *
 * @param mixed $input
 */
function em_wp_top_bar_sanitize_options($input): array
{
    $existing = em_wp_top_bar_get_options(em_wp_top_bar_admin_template_slug());

    if (!is_array($input)) {
        return $existing;
    }

    $defaults = em_wp_top_bar_default_options();
    $items = [];
    foreach (em_wp_top_bar_item_definitions() as $key => $title) {
        unset($title);
        $source = is_array($input['items'][$key] ?? null) ? $input['items'][$key] : [];
        $items[$key] = [
            'label'  => sanitize_text_field($source['label'] ?? $defaults['items'][$key]['label']),
            'href'   => esc_url_raw($source['href'] ?? $defaults['items'][$key]['href']),
            'hidden' => !empty($source['hidden']),
        ];
    }

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');
    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('top-bar', $enabled);
    }

    return [
        'enabled'                  => $enabled,
        'logo_url'                 => esc_url_raw($input['logo_url'] ?? ($existing['logo_url'] ?? '')),
        'logo_hidden'              => array_key_exists('logo_hidden', $input) ? !empty($input['logo_hidden']) : !empty($existing['logo_hidden']),
        'background_image_enabled' => array_key_exists('background_image_enabled', $input) ? !empty($input['background_image_enabled']) : !empty($existing['background_image_enabled']),
        'background_image_url'     => esc_url_raw($input['background_image_url'] ?? ($existing['background_image_url'] ?? '')),
        'background_image_hidden'  => array_key_exists('background_image_hidden', $input) ? !empty($input['background_image_hidden']) : !empty($existing['background_image_hidden']),
        'background_color'         => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'               => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
        'items'                    => $items,
        'stream_icons_hidden'      => array_key_exists('stream_icons_hidden', $input)
            ? !empty($input['stream_icons_hidden'])
            : !empty($existing['stream_icons_hidden']),
    ];
}
