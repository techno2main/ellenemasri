<?php
/**
 * Enregistrement page Accueil + assets.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre la page Accueil (masquée du menu latéral — WP Dashboard y pointe).
 */
function em_wp_admin_dashboard_register_page(): void
{
    add_menu_page(
        __('Accueil EM-WP', 'em-wp'),
        __('Accueil', 'em-wp'),
        'manage_options',
        em_wp_admin_dashboard_page_slug(),
        'em_wp_admin_render_dashboard_page',
        'dashicons-admin-home',
        3
    );
}
add_action('admin_menu', 'em_wp_admin_dashboard_register_page');

/**
 * Retire le sous-menu dupliqué WordPress.
 */
function em_wp_admin_dashboard_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_admin_dashboard_page_slug(), em_wp_admin_dashboard_page_slug());
}
add_action('admin_menu', 'em_wp_admin_dashboard_remove_duplicate_submenu', 999);

/**
 * Masque l'entrée Accueil du menu latéral (navigation via Dashboard WP).
 */
function em_wp_admin_dashboard_hide_menu_entry(): void
{
    remove_menu_page(em_wp_admin_dashboard_page_slug());
}
add_action('admin_menu', 'em_wp_admin_dashboard_hide_menu_entry', 10002);

/**
 * Assets page Accueil.
 */
function em_wp_admin_dashboard_enqueue(): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug !== em_wp_admin_dashboard_page_slug()) {
        return;
    }

    em_wp_admin_enqueue_shared_assets();

    wp_enqueue_style('dashicons');

    wp_enqueue_style(
        'em-wp-admin-dashboard',
        get_template_directory_uri() . '/assets/admin/css/pages/dashboard.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/pages/dashboard.css')
    );
}
add_action('admin_enqueue_scripts', 'em_wp_admin_dashboard_enqueue');
