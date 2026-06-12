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

    wp_enqueue_style('em-wp-style', get_stylesheet_uri(), [], $theme_version);
    wp_enqueue_style('em-wp-theme', get_template_directory_uri() . '/assets/css/theme.css', ['em-wp-style'], $theme_version);
    wp_enqueue_script('em-wp-theme', get_template_directory_uri() . '/assets/js/theme.js', [], $theme_version, true);
}
add_action('wp_enqueue_scripts', 'em_wp_enqueue_assets');
