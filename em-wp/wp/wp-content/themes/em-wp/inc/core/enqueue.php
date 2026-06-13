<?php
/**
 * Enqueue des feuilles de style et scripts.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Charge les assets principaux du thème.
 */
function em_wp_enqueue_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'em-wp-archivo-black',
        'https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );

    wp_enqueue_style('em-wp-style', get_stylesheet_uri(), [], $theme_version);
    wp_enqueue_style('em-wp-theme', get_template_directory_uri() . '/assets/css/theme.css', ['em-wp-style'], $theme_version);
    wp_enqueue_style(
        'em-wp-top-bar',
        get_template_directory_uri() . '/assets/front/css/modules/top-bar/top-bar.css',
        ['em-wp-theme', 'em-wp-archivo-black'],
        is_readable(get_template_directory() . '/assets/front/css/modules/top-bar/top-bar.css')
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/assets/front/css/modules/top-bar/top-bar.css')
            : $theme_version
    );
    $hero_style_slug = function_exists('em_wp_hero_active_style_slug')
        ? em_wp_hero_active_style_slug()
        : 'mayami';

    wp_enqueue_style(
        'em-wp-hero',
        get_template_directory_uri() . '/assets/front/css/modules/hero/' . $hero_style_slug . '/hero.css',
        ['em-wp-theme'],
        $theme_version
    );
    $slider_style_slug = function_exists('em_wp_slider_active_style_slug')
        ? em_wp_slider_active_style_slug()
        : 'mayami';

    wp_enqueue_style(
        'em-wp-slider',
        get_template_directory_uri() . '/assets/front/css/modules/slider/' . $slider_style_slug . '/slider.css',
        ['em-wp-theme'],
        $theme_version
    );

    if (is_front_page()) {
        wp_enqueue_style(
            'em-wp-landing',
            get_template_directory_uri() . '/assets/front/css/landing.css',
            ['em-wp-hero', 'em-wp-slider'],
            $theme_version
        );
    }

    $theme_js_path = get_template_directory() . '/assets/js/theme.js';
    $theme_js_version = is_readable($theme_js_path)
        ? $theme_version . '.' . (string) filemtime($theme_js_path)
        : $theme_version;

    wp_enqueue_script('em-wp-theme', get_template_directory_uri() . '/assets/js/theme.js', [], $theme_js_version, true);
}
add_action('wp_enqueue_scripts', 'em_wp_enqueue_assets');
