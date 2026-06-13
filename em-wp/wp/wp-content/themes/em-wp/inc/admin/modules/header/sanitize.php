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
 * @return array{enabled:bool,hero_slug:string,slider_slug:string,layout:string}
 */
function em_wp_header_sanitize_options($input): array
{
    $existing = em_wp_header_get_options();

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);
    $hero_slug = sanitize_key((string) ($input['hero_slug'] ?? ($existing['hero_slug'] ?? '')));
    $slider_slug = sanitize_key((string) ($input['slider_slug'] ?? ($existing['slider_slug'] ?? '')));
    $layout = sanitize_key((string) ($input['layout'] ?? ($existing['layout'] ?? 'hero_left')));

    if ($hero_slug !== '' && function_exists('em_wp_hero_catalog_has') && !em_wp_hero_catalog_has($hero_slug)) {
        $hero_slug = (string) ($existing['hero_slug'] ?? '');
    }

    if ($slider_slug !== '' && function_exists('em_wp_slider_catalog_has') && !em_wp_slider_catalog_has($slider_slug)) {
        $slider_slug = (string) ($existing['slider_slug'] ?? '');
    }

    if (!in_array($layout, ['hero_left', 'slider_left'], true)) {
        $layout = 'hero_left';
    }

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        em_wp_admin_sync_rubrique_visibility_from_post('header');
    }

    return [
        'enabled'     => $enabled,
        'hero_slug'   => $hero_slug,
        'slider_slug' => $slider_slug,
        'layout'      => $layout,
    ];
}
