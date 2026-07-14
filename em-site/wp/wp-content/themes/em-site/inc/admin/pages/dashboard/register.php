<?php
/**
 * Enregistrement page Accueil + assets.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre la page Accueil (masquée du menu latéral — WP Dashboard y pointe).
 */
function em_site_admin_dashboard_register_page(): void
{
    $dashboard_icon = function_exists('em_site_site_icon') ? em_site_site_icon('dashboard', 'dashicons-dashboard') : 'dashicons-dashboard';

    add_menu_page(
        __('Accueil EM-SITE', 'em-site'),
        __('Accueil', 'em-site'),
        'manage_options',
        em_site_admin_dashboard_page_slug(),
        'em_site_admin_render_dashboard_page',
        $dashboard_icon,
        3
    );
}
add_action('admin_menu', 'em_site_admin_dashboard_register_page');

/**
 * Retire le sous-menu dupliqué WordPress.
 */
function em_site_admin_dashboard_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_site_admin_dashboard_page_slug(), em_site_admin_dashboard_page_slug());
}
add_action('admin_menu', 'em_site_admin_dashboard_remove_duplicate_submenu', 999);

/**
 * Masque l'entrée Accueil du menu latéral (navigation via Dashboard WP).
 */
function em_site_admin_dashboard_hide_menu_entry(): void
{
    remove_menu_page(em_site_admin_dashboard_page_slug());
}
add_action('admin_menu', 'em_site_admin_dashboard_hide_menu_entry', 10002);

/**
 * Assets page Accueil.
 */
function em_site_admin_dashboard_enqueue(): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug !== em_site_admin_dashboard_page_slug()) {
        return;
    }

    em_site_admin_hub_cards_enqueue_assets();

    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );

    if (function_exists('em_site_admin_template_enqueue_new_template_launcher')) {
        em_site_admin_template_enqueue_new_template_launcher();
    }
}
add_action('admin_enqueue_scripts', 'em_site_admin_dashboard_enqueue');
