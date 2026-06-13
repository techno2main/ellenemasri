<?php
/**
 * Menu et assets admin Release.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_register_admin(): void
{
    add_menu_page(
        __('RELEASES', 'em-wp'),
        __('RELEASES', 'em-wp'),
        'manage_options',
        em_wp_release_page_slug(),
        'em_wp_release_render_admin_page',
        'dashicons-album',
        em_wp_admin_menu_position_for_site_module('release')
    );
}
add_action('admin_menu', 'em_wp_release_register_admin');

function em_wp_release_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_release_page_slug(), em_wp_release_page_slug());
}
add_action('admin_menu', 'em_wp_release_remove_duplicate_submenu', 999);

function em_wp_release_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    if (sanitize_key((string) ($_GET['page'] ?? '')) !== em_wp_release_page_slug()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }

    em_wp_admin_enqueue_shared_assets();
    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'em-wp-release-admin',
        $theme_uri . '/assets/admin/css/modules/release/release.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/release/release.css')
    );

    wp_enqueue_script(
        'em-wp-admin-slide-sortable',
        $theme_uri . '/assets/admin/js/shared/slide-sortable.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/slide-sortable.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-release-admin',
        $theme_uri . '/assets/admin/js/modules/release/release.js',
        ['em-wp-admin-slide-sortable', 'em-wp-admin-accordion', 'em-wp-admin-confirm-modal'],
        em_wp_admin_asset_version('assets/admin/js/modules/release/release.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-stream-admin',
        $theme_uri . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_release_admin_enqueue');
