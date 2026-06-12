<?php
/**
 * Charge les composants cœur du thème.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/core/theme-setup.php';
require_once __DIR__ . '/core/enqueue.php';
require_once __DIR__ . '/admin/bootstrap.php';
require_once __DIR__ . '/front/bootstrap.php';
