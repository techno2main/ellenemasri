<?php
/**
 * Bootstrap page Rubriques Template (sommaire workspace).
 *
 * Où modifier quoi :
 *   slug.php                  → slug / URL page Rubriques
 *   definitions.php           → liste TOP-BAR, HEADER, STREAM… + URLs entrée
 *   register.php              → enregistrement WP + assets JS/CSS
 *   render-template-picker.php → écran « Choix du template » (sans contexte)
 *   render-list-item.php      → une ligne rubrique (liste + tri + visibilité)
 *   render-page.php           → page sommaire complète (liste + plan)
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

$rubriques_dir = __DIR__;

require_once $rubriques_dir . '/slug.php';
require_once $rubriques_dir . '/definitions.php';
require_once $rubriques_dir . '/register.php';
require_once $rubriques_dir . '/render-list-item.php';
require_once $rubriques_dir . '/instance-picker.php';
require_once $rubriques_dir . '/instance-picker-assets.php';
require_once $rubriques_dir . '/header-section.php';
require_once $rubriques_dir . '/header-section-assets.php';
require_once $rubriques_dir . '/skeleton-preview.php';
require_once $rubriques_dir . '/render-template-picker.php';
require_once $rubriques_dir . '/render-page.php';
