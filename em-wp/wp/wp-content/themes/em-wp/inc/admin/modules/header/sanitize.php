<?php
/**
 * Sanitize options HEADER.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param mixed $input
 * @return array<string, mixed>
 */
function em_wp_header_sanitize_options($input): array
{
    $template_slug = em_wp_header_resolve_template_slug();
    $existing = em_wp_header_get_saved_options($template_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);
    $hero_slug = sanitize_key((string) ($input['hero_slug'] ?? ($existing['hero_slug'] ?? '')));
    $slider_slug = sanitize_key((string) ($input['slider_slug'] ?? ($existing['slider_slug'] ?? '')));
    $layout = sanitize_key((string) ($input['layout'] ?? ($existing['layout'] ?? 'hero_left')));

    if ($hero_slug !== '' && function_exists('em_wp_hero_normalize_catalog_slug')) {
        $hero_slug = em_wp_hero_normalize_catalog_slug($hero_slug);
    }

    if ($slider_slug !== '' && function_exists('em_wp_slider_normalize_catalog_slug')) {
        $slider_slug = em_wp_slider_normalize_catalog_slug($slider_slug);
    }

    if ($hero_slug !== '' && function_exists('em_wp_hero_catalog_has') && !em_wp_hero_catalog_has($hero_slug)) {
        $hero_slug = sanitize_key((string) ($existing['hero_slug'] ?? ''));
    }

    if ($slider_slug !== '' && function_exists('em_wp_slider_catalog_has') && !em_wp_slider_catalog_has($slider_slug)) {
        $slider_slug = sanitize_key((string) ($existing['slider_slug'] ?? ''));
    }

    if (!in_array($layout, ['hero_left', 'slider_left'], true)) {
        $layout = 'hero_left';
    }

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        em_wp_admin_sync_rubrique_visibility_from_post('header');
    }

    return [
        'enabled'                  => $enabled,
        'hero_slug'                => $hero_slug,
        'slider_slug'              => $slider_slug,
        'layout'                   => $layout,
        'background_color'         => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'               => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
        'background_image'         => esc_url_raw($input['background_image'] ?? ($existing['background_image'] ?? '')),
        'background_image_hidden'  => !empty($input['background_image_hidden']),
    ];
}
