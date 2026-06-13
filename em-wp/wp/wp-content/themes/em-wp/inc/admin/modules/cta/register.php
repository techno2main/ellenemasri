<?php
/**
 * Menu et assets admin CTA.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_register_admin(): void
{
    add_menu_page(
        __('CTA', 'em-wp'),
        __('CTA', 'em-wp'),
        'manage_options',
        em_wp_cta_page_slug(),
        'em_wp_cta_render_admin_page',
        'dashicons-megaphone',
        em_wp_admin_menu_position_for_site_module('cta')
    );
}
add_action('admin_menu', 'em_wp_cta_register_admin');

function em_wp_cta_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_cta_page_slug(), em_wp_cta_page_slug());
}
add_action('admin_menu', 'em_wp_cta_remove_duplicate_submenu', 999);

function em_wp_cta_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    if (sanitize_key((string) ($_GET['page'] ?? '')) !== em_wp_cta_page_slug()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }

    em_wp_admin_enqueue_shared_assets();

    wp_enqueue_style(
        'em-wp-cta-admin',
        get_template_directory_uri() . '/assets/admin/css/modules/cta/cta.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/cta/cta.css')
    );

    wp_enqueue_script(
        'em-wp-cta-admin',
        get_template_directory_uri() . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion', 'em-wp-admin-module-style-preview'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_cta_admin_enqueue');
