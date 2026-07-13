<?php
/**
 * Menu, assets et hooks admin Stream.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre menu Stream.
 */
function em_site_stream_register_admin(): void
{
    add_menu_page(
        __('STREAM', 'em-site'),
        __('STREAM', 'em-site'),
        'manage_options',
        em_site_stream_page_slug(),
        'em_site_stream_render_admin_page',
        'dashicons-playlist-audio',
        em_site_admin_menu_position_for_site_module('stream')
    );
}
add_action('admin_menu', 'em_site_stream_register_admin');

/**
 * Retire le sous-menu dupliqué.
 */
function em_site_stream_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_site_stream_page_slug(), em_site_stream_page_slug());
}
add_action('admin_menu', 'em_site_stream_remove_duplicate_submenu', 999);

/**
 * Assets admin Stream.
 */
function em_site_stream_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug !== em_site_stream_page_slug()) {
        return;
    }

    em_site_admin_enqueue_shared_assets();
    em_site_admin_enqueue_catalog_slug_switch_assets();

    wp_enqueue_style(
        'em-site-header-admin',
        get_template_directory_uri() . '/assets/admin/css/modules/header/header.css',
        ['em-site-admin-module-common', 'em-site-admin-hub-cards'],
        em_site_admin_asset_version('assets/admin/css/modules/header/header.css')
    );

    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'em-site-top-bar-platform-list',
        $theme_uri . '/assets/admin/css/modules/top-bar/top-bar.css',
        ['em-site-admin-module-common'],
        em_site_admin_asset_version('assets/admin/css/modules/top-bar/top-bar.css')
    );

    wp_enqueue_style(
        'em-site-stream-module-admin',
        $theme_uri . '/assets/admin/css/modules/stream/stream.css',
        ['em-site-top-bar-platform-list'],
        em_site_admin_asset_version('assets/admin/css/modules/stream/stream.css')
    );

    wp_enqueue_script(
        'em-site-admin-slide-sortable',
        $theme_uri . '/assets/admin/shared/js/media/slide-sortable.js',
        [],
        em_site_admin_asset_version('assets/admin/shared/js/media/slide-sortable.js'),
        true
    );

    wp_enqueue_script(
        'em-site-stream-admin',
        $theme_uri . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-site-admin-color-picker', 'em-site-admin-accordion', 'em-site-admin-slide-sortable'],
        em_site_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_site_stream_admin_enqueue');
