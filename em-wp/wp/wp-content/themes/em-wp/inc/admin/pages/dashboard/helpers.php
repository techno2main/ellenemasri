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
    return em_wp_admin_hub_greeting_name();
}
