<?php
/**
 * Bootstrap de la couche admin em-site.
 *
 * Phase 1: point d'entree unique pour rebrancher l'admin lot par lot,
 * sans impacter le front valide.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/menu.php';
require_once __DIR__ . '/pages/dashboard-menus.php';
require_once __DIR__ . '/pages/dashboard-routing.php';
require_once __DIR__ . '/pages/dashboard-context.php';
require_once __DIR__ . '/pages/ellene-gate.php';
require_once __DIR__ . '/client-access.php';

/**
 * Slugs modules admin cibles (ordre source em-wp).
 */
function em_site_admin_module_slugs(): array
{
    return [
        'top-bar',
        'header',
        'stream',
        'social',
        'video',
        'release',
        'cta',
        'about',
        'contact',
        'footer',
    ];
}
