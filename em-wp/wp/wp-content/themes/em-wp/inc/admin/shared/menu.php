<?php
/**
 * Menu admin partagé — chargeur (modules dans shared/menu/).
 *
 * Convention : tout nouveau module front/admin em-wp s'enregistre dans le bloc
 * « Rubriques du site » (entre le filet du haut et le filet du bas).
 *
 * Positions figées : inc/admin/shared/menu/layout.php
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/menu/bootstrap.php';
