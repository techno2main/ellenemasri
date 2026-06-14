<?php
/**
 * Bootstrap menu admin em-wp (modules < 350 lignes chacun).
 *
 * Positions figées : menu/layout.php
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

$menu_dir = __DIR__;

require_once $menu_dir . '/capability.php';
require_once $menu_dir . '/helpers.php';
require_once $menu_dir . '/rubrique.php';
require_once $menu_dir . '/medias.php';
require_once $menu_dir . '/catalog-positions.php';
require_once $menu_dir . '/template-positions.php';
require_once $menu_dir . '/reserved-slugs.php';
require_once $menu_dir . '/intruders.php';
require_once $menu_dir . '/chrome.php';
require_once $menu_dir . '/body-class.php';
require_once $menu_dir . '/footer.php';
require_once $menu_dir . '/styles/register.php';
