<?php
/**
 * Setup des supports et menus du thème.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('em_wp_setup')) {
    /**
     * Déclare les supports WordPress du thème.
     */
    function em_wp_setup(): void
    {
        load_theme_textdomain('em-wp', get_template_directory() . '/languages');

        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support(
            'html5',
            ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']
        );
        add_theme_support('custom-logo', [
            'height'      => 120,
            'width'       => 240,
            'flex-height' => true,
            'flex-width'  => true,
        ]);

        register_nav_menus([
            'primary' => __('Menu principal', 'em-wp'),
        ]);
    }
}
add_action('after_setup_theme', 'em_wp_setup');
