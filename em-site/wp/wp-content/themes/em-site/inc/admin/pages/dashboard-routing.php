<?php
/**
 * Routage dashboard admin em-site.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

// Fichier legacy conservé pour historique. Le routage actif est dans pages/dashboard/redirects.php.
return;

function em_site_admin_login_redirect_to_dashboard($redirect_to, $requested_redirect_to, $user)
{
    if (!is_a($user, 'WP_User')) {
        return $redirect_to;
    }

    $user_login = strtolower((string) $user->user_login);
    $client_logins = function_exists('em_site_admin_client_user_logins')
        ? em_site_admin_client_user_logins()
        : ['admin-ellene'];

    if (
        function_exists('em_site_client_admin_gate_is_enabled')
        && em_site_client_admin_gate_is_enabled()
        && in_array($user_login, $client_logins, true)
    ) {
        return $redirect_to;
    }

    if (!in_array('administrator', (array) $user->roles, true)) {
        return $redirect_to;
    }

    if (!empty($requested_redirect_to) && admin_url() !== $requested_redirect_to && admin_url('index.php') !== $requested_redirect_to) {
        return $redirect_to;
    }

    return em_site_admin_dashboard_admin_url();
}
add_filter('login_redirect', 'em_site_admin_login_redirect_to_dashboard', 10, 3);

function em_site_admin_redirect_wp_dashboard_to_home(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ((string) $pagenow !== 'index.php') {
        return;
    }

    wp_safe_redirect(em_site_admin_dashboard_admin_url());
    exit;
}
add_action('admin_init', 'em_site_admin_redirect_wp_dashboard_to_home', 1);

function em_site_admin_filter_dashboard_url($url, $path)
{
    if (!is_user_logged_in()) {
        return $url;
    }

    if (!current_user_can('manage_options')) {
        return $url;
    }

    if ($path === '' || $path === null || $path === 'index.php') {
        return em_site_admin_dashboard_admin_url();
    }

    return $url;
}
add_filter('dashboard_url', 'em_site_admin_filter_dashboard_url', 10, 2);

function em_site_admin_filter_get_dashboard_url($url, $user_id, $path)
{
    return em_site_admin_filter_dashboard_url($url, $path);
}
add_filter('get_dashboard_url', 'em_site_admin_filter_get_dashboard_url', 10, 3);
