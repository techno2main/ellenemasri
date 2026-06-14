<?php
/**
 * Menu et assets admin HEADER.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le menu rubrique HEADER.
 */
function em_wp_header_register_admin(): void
{
    add_menu_page(
        __('HEADER', 'em-wp'),
        __('HEADER', 'em-wp'),
        'manage_options',
        em_wp_header_page_slug(),
        'em_wp_header_render_admin_page',
        'dashicons-align-wide',
        em_wp_admin_menu_position_for_site_module('header')
    );
}
add_action('admin_menu', 'em_wp_header_register_admin');

/**
 * Retire le sous-menu dupliqué.
 */
function em_wp_header_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_header_page_slug(), em_wp_header_page_slug());
}
add_action('admin_menu', 'em_wp_header_remove_duplicate_submenu', 999);

/**
 * Assets admin HEADER.
 */
function em_wp_header_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug !== em_wp_header_page_slug()) {
        return;
    }

    em_wp_admin_enqueue_shared_assets();

    wp_enqueue_style(
        'em-wp-header-admin',
        get_template_directory_uri() . '/assets/admin/css/modules/header/header.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/header/header.css')
    );

    wp_enqueue_style('wp-color-picker');

    wp_enqueue_script(
        'em-wp-header-admin',
        get_template_directory_uri() . '/assets/admin/js/modules/header/header.js',
        ['jquery', 'wp-color-picker', 'wp-util'],
        em_wp_admin_asset_version('assets/admin/js/modules/header/header.js'),
        true
    );

    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'em_wp_header_admin_enqueue');
