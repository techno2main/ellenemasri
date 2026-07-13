<?php
/**
 * Menu et assets admin Video.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_video_register_admin(): void
{
    add_menu_page(
        __('VIDEO', 'em-site'),
        __('VIDEO', 'em-site'),
        'manage_options',
        em_site_video_page_slug(),
        'em_site_video_render_admin_page',
        'dashicons-video-alt3',
        em_site_admin_menu_position_for_site_module('video')
    );
}
add_action('admin_menu', 'em_site_video_register_admin');

function em_site_video_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_site_video_page_slug(), em_site_video_page_slug());
}
add_action('admin_menu', 'em_site_video_remove_duplicate_submenu', 999);

function em_site_video_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    if (sanitize_key((string) ($_GET['page'] ?? '')) !== em_site_video_page_slug()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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

    wp_enqueue_style(
        'em-site-video-admin',
        get_template_directory_uri() . '/assets/admin/css/modules/video/video.css',
        ['em-site-admin-module-common'],
        em_site_admin_asset_version('assets/admin/css/modules/video/video.css')
    );

    wp_enqueue_script(
        'em-site-stream-admin',
        get_template_directory_uri() . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-site-admin-color-picker', 'em-site-admin-accordion'],
        em_site_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_site_video_admin_enqueue');
