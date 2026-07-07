<?php
/**
 * Redirections vers l'Accueil em-site (login, index.php, liens WP).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Redirige vers l'accueil après connexion admin.
 *
 * @param mixed $redirect_to
 * @param mixed $requested_redirect_to
 * @param mixed $user
 * @return mixed
 */
function em_site_admin_login_redirect_to_dashboard($redirect_to, $requested_redirect_to, $user)
{
    if (!($user instanceof WP_User) || !user_can($user, 'manage_options')) {
        return $redirect_to;
    }

    if (!empty($requested_redirect_to) && admin_url() !== $requested_redirect_to && admin_url('index.php') !== $requested_redirect_to) {
        return $redirect_to;
    }

    return em_site_admin_dashboard_admin_url();
}
add_filter('login_redirect', 'em_site_admin_login_redirect_to_dashboard', 10, 3);

/**
 * Redirige index.php vers l'accueil em-site.
 */
function em_site_admin_redirect_wp_dashboard_to_home(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'index.php') {
        return;
    }

    em_site_admin_safe_redirect(em_site_admin_dashboard_admin_url());
}
add_action('admin_init', 'em_site_admin_redirect_wp_dashboard_to_home', 1);

/**
 * Liens « Dashboard » générés par WordPress → accueil em-site.
 *
 * @param mixed $url
 * @param mixed $path
 * @return mixed
 */
function em_site_admin_filter_dashboard_url($url, $path)
{
    if (!current_user_can('manage_options')) {
        return $url;
    }

    $path = (string) $path;

    if ($path === '' || $path === 'index.php') {
        return em_site_admin_dashboard_admin_url();
    }

    return $url;
}
add_filter('dashboard_url', 'em_site_admin_filter_dashboard_url', 10, 2);

/**
 * @param mixed $url
 * @param mixed $user_id
 * @param mixed $path
 * @return mixed
 */
function em_site_admin_filter_get_dashboard_url($url, $user_id, $path)
{
    unset($user_id);

    return em_site_admin_filter_dashboard_url($url, $path);
}
add_filter('get_dashboard_url', 'em_site_admin_filter_get_dashboard_url', 10, 3);
