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
    $hero_style_slug = 'mayami';
    $slider_style_slug = 'mayami';
    $header = function_exists('em_wp_header_get_options_for_front')
        ? em_wp_header_get_options_for_front()
        : [];

    if (!empty($header['hero_slug'])) {
        $hero_catalog = sanitize_key((string) $header['hero_slug']);
        if (function_exists('em_wp_catalog_resolve_style_slug')) {
            $hero_style_slug = em_wp_catalog_resolve_style_slug('hero', $hero_catalog);
        } elseif (preg_match('/^hero-([^-]+)-/', $hero_catalog, $matches)) {
            $hero_style_slug = $matches[1] === 'ellene' ? 'mayami' : $matches[1];
        }
    } elseif (function_exists('em_wp_hero_active_style_slug')) {
        $hero_style_slug = em_wp_hero_active_style_slug();
    }

    if (!empty($header['slider_slug'])) {
        $slider_catalog = sanitize_key((string) $header['slider_slug']);
        if (function_exists('em_wp_catalog_resolve_style_slug')) {
            $slider_style_slug = em_wp_catalog_resolve_style_slug('slider', $slider_catalog);
        } elseif (preg_match('/^slider-([^-]+)-/', $slider_catalog, $matches)) {
            $slider_style_slug = $matches[1];
        }
    } elseif (function_exists('em_wp_slider_active_style_slug')) {
        $slider_style_slug = em_wp_slider_active_style_slug();
    }

    $theme_dir = get_template_directory();
    $landing_ui_path = 'assets/front/css/landing-ui.css';
    $hero_css_style_slug = $hero_style_slug === 'ellene' ? 'mayami' : $hero_style_slug;
    $hero_css_path = 'assets/front/css/modules/hero/' . $hero_css_style_slug . '/hero.css';
    $slider_css_path = 'assets/front/css/modules/slider/' . $slider_style_slug . '/slider.css';
    $enqueue_hero = empty($header) || !empty($header['hero_slug']);
    $enqueue_slider = empty($header) || !empty($header['slider_slug']);
    $enqueue_header = empty($header) || !empty($header['hero_slug']) || !empty($header['slider_slug']);
    $header_css_path = 'assets/front/css/modules/header/header.css';

    wp_enqueue_style(
        'em-wp-landing-ui',
        get_template_directory_uri() . '/' . $landing_ui_path,
        ['em-wp-theme'],
        is_readable($theme_dir . '/' . $landing_ui_path)
            ? $theme_version . '.' . (string) filemtime($theme_dir . '/' . $landing_ui_path)
            : $theme_version
    );

    $style_deps = ['em-wp-theme', 'em-wp-landing-ui'];

    if ($enqueue_header && is_readable($theme_dir . '/' . $header_css_path)) {
        wp_enqueue_style(
            'em-wp-header',
            get_template_directory_uri() . '/' . $header_css_path,
            $style_deps,
            $theme_version . '.' . (string) filemtime($theme_dir . '/' . $header_css_path)
        );
        $style_deps[] = 'em-wp-header';
    }

    if ($enqueue_hero && is_readable($theme_dir . '/' . $hero_css_path)) {
        wp_enqueue_style(
            'em-wp-hero',
            get_template_directory_uri() . '/' . $hero_css_path,
            $style_deps,
            $theme_version . '.' . (string) filemtime($theme_dir . '/' . $hero_css_path)
        );
        $style_deps[] = 'em-wp-hero';
    }

    if ($enqueue_slider && is_readable($theme_dir . '/' . $slider_css_path)) {
        wp_enqueue_style(
            'em-wp-slider',
            get_template_directory_uri() . '/' . $slider_css_path,
            ['em-wp-theme'],
            $theme_version . '.' . (string) filemtime($theme_dir . '/' . $slider_css_path)
        );
    }

    if (is_front_page()) {
        $landing_css_path = 'assets/front/css/landing.css';
        $landing_deps = array_values(array_filter(['em-wp-header', 'em-wp-hero', 'em-wp-slider', 'em-wp-landing-ui']));
        wp_enqueue_style(
            'em-wp-landing',
            get_template_directory_uri() . '/' . $landing_css_path,
            $landing_deps,
            is_readable($theme_dir . '/' . $landing_css_path)
                ? $theme_version . '.' . (string) filemtime($theme_dir . '/' . $landing_css_path)
                : $theme_version
        );
    }

    $theme_js_path = get_template_directory() . '/assets/js/theme.js';
    $theme_js_version = is_readable($theme_js_path)
        ? $theme_version . '.' . (string) filemtime($theme_js_path)
        : $theme_version;

    wp_enqueue_script('em-wp-theme', get_template_directory_uri() . '/assets/js/theme.js', [], $theme_js_version, true);
}
add_action('wp_enqueue_scripts', 'em_wp_enqueue_assets');
