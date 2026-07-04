<?php
/**
 * Slug et URL page Accueil em-wp.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug page admin Accueil.
 */
function em_wp_admin_dashboard_page_slug(): string
{
    return 'em-wp-dashboard';
}

/**
 * URL page admin Accueil.
 */
function em_wp_admin_dashboard_admin_url(): string
{
    return admin_url('admin.php?page=' . em_wp_admin_dashboard_page_slug());
}

/**
 * Indique si l'écran admin courant est la page Accueil em-wp.
 */
function em_wp_admin_is_dashboard_admin_screen(): bool
{
    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return false;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return sanitize_key((string) ($_GET['page'] ?? '')) === em_wp_admin_dashboard_page_slug();
}
