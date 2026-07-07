<?php
/**
 * Options et sanitization du module Slider (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Valeurs par defaut du module Slider.
 */
function em_wp_slider_default_options(string $style_slug = 'mayami'): array
{
    $footer_titles = [
        'mayami' => __('MAYAMI, MY MIAMI', 'em-wp'),
        'ellene' => __('ELLENE', 'em-wp'),
    ];

    return [
        'enabled'             => true,
        'frame_bg_color'      => '#12338f',
        'footer_bg_color'     => '#f2ebd1',
        'footer_text'         => '#100421',
        'footer_title'        => $footer_titles[$style_slug] ?? $footer_titles['mayami'],
        'slider_title_hidden' => false,
        'slides'              => [em_wp_slider_default_slide()],
    ];
}

/**
 * Retourne les options Slider normalisees.
 */
function em_wp_slider_get_options(string $style_slug = 'slider-mayami-default'): array
{
    if (function_exists('em_wp_slider_normalize_catalog_slug')) {
        $style_slug = em_wp_slider_normalize_catalog_slug($style_slug);
    }

    $saved = get_option(em_wp_slider_option_name($style_slug), []);

    if ($style_slug === 'slider-mayami-default' && empty($saved)) {
        $saved = get_option('em_wp_slider_mayami_options', []);
    }

    if ($style_slug === 'slider-mayami-default' && empty($saved)) {
        $saved = get_option('em_wp_slider_options', []);
    }

    if (!is_array($saved)) {
        $saved = [];
    }

    $defaults = em_wp_slider_default_options($style_slug);
    unset($defaults['slides']);

    $merged = wp_parse_args($saved, $defaults);
    $merged = em_wp_slider_migrate_legacy_options($merged);
    $merged['slides'] = em_wp_slider_get_slides_list($merged);

    return $merged;
}

/**
 * Sanitize callback Settings API pour une variante Slider.
 *
 * @param mixed $input
 */
function em_wp_slider_sanitize_options_for_style($input, string $style_slug): array
{
    $existing = em_wp_slider_get_options($style_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $frame_bg_color = sanitize_hex_color($input['frame_bg_color'] ?? '');
    $footer_bg_color = sanitize_hex_color($input['footer_bg_color'] ?? '');
    $footer_text = sanitize_hex_color($input['footer_text'] ?? '');
    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        em_wp_admin_sync_rubrique_visibility_from_post('slider');
    }

    return [
        'enabled'             => $enabled,
        'frame_bg_color'      => $frame_bg_color !== null && $frame_bg_color !== false && $frame_bg_color !== ''
            ? $frame_bg_color
            : (string) ($existing['frame_bg_color'] ?? ''),
        'footer_bg_color'     => $footer_bg_color !== null && $footer_bg_color !== false && $footer_bg_color !== ''
            ? $footer_bg_color
            : (string) ($existing['footer_bg_color'] ?? ''),
        'footer_text'         => $footer_text !== null && $footer_text !== false && $footer_text !== ''
            ? $footer_text
            : (string) ($existing['footer_text'] ?? ''),
        'footer_title'        => sanitize_text_field($input['footer_title'] ?? ($existing['footer_title'] ?? '')),
        'slider_title_hidden' => !empty($input['slider_title_hidden']),
        'slides'              => isset($input['slides']) && is_array($input['slides'])
            ? em_wp_slider_sanitize_slides_from_input($input['slides'])
            : $existing['slides'],
    ];
}
