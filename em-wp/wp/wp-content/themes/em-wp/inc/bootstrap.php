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
require_once __DIR__ . '/core/login.php';
require_once __DIR__ . '/shared/rubrique-order.php';
require_once __DIR__ . '/shared/template/bootstrap.php';
require_once __DIR__ . '/shared/social-platforms.php';
require_once __DIR__ . '/shared/stream-embed.php';
require_once __DIR__ . '/shared/stream-platform-items.php';
require_once __DIR__ . '/shared/stream-platforms.php';
require_once __DIR__ . '/admin/bootstrap.php';
require_once __DIR__ . '/front/bootstrap.php';
