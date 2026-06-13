<?php
/**
 * Menu, assets et hooks admin Stream.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre menu Stream.
 */
function em_wp_stream_register_admin(): void
{
    add_menu_page(
        __('STREAM', 'em-wp'),
        __('STREAM', 'em-wp'),
        'manage_options',
        em_wp_stream_page_slug(),
        'em_wp_stream_render_admin_page',
        'dashicons-playlist-audio',
        em_wp_admin_menu_position_for_site_module('stream')
    );
}
add_action('admin_menu', 'em_wp_stream_register_admin');

/**
 * Retire le sous-menu dupliqué.
 */
function em_wp_stream_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_stream_page_slug(), em_wp_stream_page_slug());
}
add_action('admin_menu', 'em_wp_stream_remove_duplicate_submenu', 999);

/**
 * Assets admin Stream.
 */
function em_wp_stream_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug !== em_wp_stream_page_slug()) {
        return;
    }

    em_wp_admin_enqueue_shared_assets();

    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'em-wp-top-bar-platform-list',
        $theme_uri . '/assets/admin/css/modules/top-bar/top-bar.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/top-bar/top-bar.css')
    );

    wp_enqueue_script(
        'em-wp-admin-slide-sortable',
        $theme_uri . '/assets/admin/js/shared/slide-sortable.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/slide-sortable.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-stream-admin',
        $theme_uri . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion', 'em-wp-admin-slide-sortable'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_stream_admin_enqueue');
