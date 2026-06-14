<?php
/**
 * Bootstrap module HEADER (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/defaults.php';
require_once __DIR__ . '/get-options.php';
require_once __DIR__ . '/sanitize.php';
require_once __DIR__ . '/admin-page.php';
require_once __DIR__ . '/partials/style-panel.php';
require_once __DIR__ . '/partials/layout-switcher.php';
require_once __DIR__ . '/register.php';
