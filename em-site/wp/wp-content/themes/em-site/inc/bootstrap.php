<?php
/**
 * Bootstrap minimal du theme pour rendu front par rubrique.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/core/assets.php';
require_once __DIR__ . '/front/modules/top-bar/render.php';
require_once __DIR__ . '/front/render-page.php';
