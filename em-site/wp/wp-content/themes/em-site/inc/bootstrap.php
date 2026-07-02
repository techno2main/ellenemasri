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
require_once __DIR__ . '/front/modules/header/render.php';
require_once __DIR__ . '/front/modules/stream/render.php';
require_once __DIR__ . '/front/modules/social/render.php';
require_once __DIR__ . '/front/modules/video/render.php';
require_once __DIR__ . '/front/modules/release/render.php';
require_once __DIR__ . '/front/modules/cta/render.php';
require_once __DIR__ . '/front/modules/contact/render.php';
require_once __DIR__ . '/front/modules/about/render.php';
require_once __DIR__ . '/front/modules/footer/render.php';
require_once __DIR__ . '/front/render-page.php';
