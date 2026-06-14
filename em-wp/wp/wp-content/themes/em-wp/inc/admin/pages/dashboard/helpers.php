<?php
/**
 * Helpers page Accueil (données utilisateur, etc.).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prénom (ou repli) de l'admin connecté pour le bandeau d'accueil.
 */
function em_wp_admin_dashboard_greeting_name(): string
{
    $user = wp_get_current_user();

    if (!$user instanceof WP_User || $user->ID <= 0) {
        return '';
    }

    $first_name = trim((string) $user->first_name);

    if ($first_name !== '') {
        return $first_name;
    }

    $display_name = trim((string) $user->display_name);

    if ($display_name !== '') {
        return $display_name;
    }

    return (string) $user->user_login;
}
