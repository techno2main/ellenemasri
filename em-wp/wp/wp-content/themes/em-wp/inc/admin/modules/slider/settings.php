<?php
/**
 * Point d'entree du module Slider (admin).
 *
 * Ce fichier reste volontairement court et charge les sous-composants
 * afin de respecter la limite de taille par fichier.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/core/context.php';
require_once __DIR__ . '/core/options.php';
require_once __DIR__ . '/admin/render.php';
require_once __DIR__ . '/admin/hooks.php';
