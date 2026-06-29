<?php
/**
 * Bootstrap page Accueil (Dashboard em-wp).
 *
 * Où modifier quoi :
 *   slug.php              → slug / URL page Accueil
 *   register.php          → enregistrement WP + CSS/JS
 *   redirects.php         → login, index.php, dashboard_url
 *   menu-sidebar.php      → entrée DASHBOARD + flèche menu latéral
 *   helpers.php           → prénom admin, détection écran Accueil
 *   components.php        → titres cartes, boutons, badges
 *   render-page.php       → assemblage page Accueil
 *   rows/rubriques.php    → rangée carte Rubriques (V4)
 *   rows/templates.php    → rangée cartes Templates
 *   rows/medias-settings.php → rangée Médias + Settings
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

$dashboard_dir = __DIR__;

require_once $dashboard_dir . '/slug.php';
require_once $dashboard_dir . '/register.php';
require_once $dashboard_dir . '/redirects.php';
require_once $dashboard_dir . '/menu-sidebar.php';
require_once $dashboard_dir . '/helpers.php';
require_once $dashboard_dir . '/components.php';
require_once $dashboard_dir . '/rows/rubriques.php';
require_once $dashboard_dir . '/rows/templates.php';
require_once $dashboard_dir . '/rows/medias-settings.php';
require_once $dashboard_dir . '/render-page.php';
