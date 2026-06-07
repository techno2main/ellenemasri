<?php

/**
 * Theme setup helpers and shared utilities.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_get_landing_option($field_id, $default = '') {
    $primary_options = get_option('mayami_landing_options', array());

    if (is_array($primary_options) && array_key_exists($field_id, $primary_options)) {
        return $primary_options[$field_id];
    }

    $legacy_options = get_option('mayami_options', array());

    if (is_array($legacy_options) && array_key_exists($field_id, $legacy_options)) {
        return $legacy_options[$field_id];
    }

    return $default;
}

function mayami_upload_size_limit($size) {
    $desired_limit = 128 * MB_IN_BYTES;

    return max((int) $size, $desired_limit);
}

add_filter('upload_size_limit', 'mayami_upload_size_limit');

function mayami_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    add_filter('use_block_editor_for_post', '__return_false');

    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}

add_action('after_setup_theme', 'mayami_theme_setup');

function mayami_output_theme_favicon() {
    $favicon_svg_path = get_template_directory() . '/assets/favicon.svg';
    $favicon_png_32_path = get_template_directory() . '/assets/favicon-32.png';
    $favicon_png_180_path = get_template_directory() . '/assets/favicon-180.png';
    $favicon_svg_url = get_template_directory_uri() . '/assets/favicon.svg';
    $favicon_png_32_url = get_template_directory_uri() . '/assets/favicon-32.png';
    $favicon_png_180_url = get_template_directory_uri() . '/assets/favicon-180.png';

    if (file_exists($favicon_svg_path)) {
        $favicon_svg_url .= '?v=' . filemtime($favicon_svg_path);
    }

    if (file_exists($favicon_png_32_path)) {
        $favicon_png_32_url .= '?v=' . filemtime($favicon_png_32_path);
    }

    if (file_exists($favicon_png_180_path)) {
        $favicon_png_180_url .= '?v=' . filemtime($favicon_png_180_path);
    }

    echo '<link rel="icon" type="image/svg+xml" href="' . esc_url($favicon_svg_url) . '" />' . "\n";
    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url($favicon_png_32_url) . '" />' . "\n";
    echo '<link rel="shortcut icon" href="' . esc_url($favicon_png_32_url) . '" />' . "\n";
    echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url($favicon_png_180_url) . '" />' . "\n";
}

add_action('wp_head', 'mayami_output_theme_favicon', 1);
add_action('admin_head', 'mayami_output_theme_favicon', 1);
add_action('login_head', 'mayami_output_theme_favicon', 1);