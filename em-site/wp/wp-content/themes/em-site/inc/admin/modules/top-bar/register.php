<?php
/**
 * Menu et assets admin Top Bar.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre la page Top Bar dans le menu principal.
 */
function em_wp_top_bar_add_admin_page(): void
{
    add_menu_page(
        __('TOP-BAR', 'em-wp'),
        __('TOP-BAR', 'em-wp'),
        'manage_options',
        em_wp_top_bar_page_slug(),
        'em_wp_top_bar_render_admin_page',
        'dashicons-align-wide',
        em_wp_admin_menu_position_top_bar()
    );
}
add_action('admin_menu', 'em_wp_top_bar_add_admin_page');

/**
 * Charge les assets admin du module Top Bar.
 */
function em_wp_top_bar_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($page_slug !== em_wp_top_bar_page_slug()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    em_wp_admin_enqueue_shared_assets();
    em_wp_admin_enqueue_catalog_slug_switch_assets();

    wp_enqueue_style(
        'em-wp-header-admin',
        $theme_uri . '/assets/admin/css/modules/header/admin-preview-render-header.css',
        ['em-wp-admin-module-common', 'em-wp-admin-hub-cards'],
        em_wp_admin_asset_version('assets/admin/css/modules/header/admin-preview-render-header.css')
    );

    wp_enqueue_style(
        'em-wp-top-bar-admin',
        $theme_uri . '/assets/admin/css/modules/top-bar/top-bar.css',
        ['em-wp-admin-color-picker', 'em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/top-bar/top-bar.css')
    );

    wp_enqueue_script(
        'em-wp-top-bar-admin',
        $theme_uri . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_top_bar_admin_enqueue');
