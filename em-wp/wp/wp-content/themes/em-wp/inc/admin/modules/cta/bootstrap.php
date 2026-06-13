<?php
/**
 * Bootstrap module CTA (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/defaults.php';
require_once __DIR__ . '/get-options.php';
require_once __DIR__ . '/sanitize.php';
require_once __DIR__ . '/migrate.php';
require_once __DIR__ . '/partials/texture-panel.php';
require_once __DIR__ . '/partials/content-panel.php';
require_once __DIR__ . '/partials/buttons-panel.php';
require_once __DIR__ . '/admin-page.php';
require_once __DIR__ . '/register.php';
