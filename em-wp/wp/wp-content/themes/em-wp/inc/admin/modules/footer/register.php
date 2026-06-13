<?php
/**
 * Menu et assets admin Footer.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_footer_register_admin(): void
{
    add_menu_page(
        __('FOOTER', 'em-wp'),
        __('FOOTER', 'em-wp'),
        'manage_options',
        em_wp_footer_page_slug(),
        'em_wp_footer_render_admin_page',
        'dashicons-editor-insertmore',
        em_wp_admin_menu_position_for_site_module('footer')
    );
}
add_action('admin_menu', 'em_wp_footer_register_admin');

function em_wp_footer_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_footer_page_slug(), em_wp_footer_page_slug());
}
add_action('admin_menu', 'em_wp_footer_remove_duplicate_submenu', 999);

function em_wp_footer_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    if (sanitize_key((string) ($_GET['page'] ?? '')) !== em_wp_footer_page_slug()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }

    em_wp_admin_enqueue_shared_assets();
}
add_action('admin_enqueue_scripts', 'em_wp_footer_admin_enqueue');
