<?php
/**
 * Helpers page Accueil (données utilisateur, etc.).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prénom (ou repli) de l'admin connecté pour le bandeau d'accueil.
 */
function em_site_admin_dashboard_greeting_name(): string
{
    return em_site_admin_hub_greeting_name();
}
