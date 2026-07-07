<?php
/**
 * Bootstrap wizard création template.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/onboarding-copy.php';
require_once __DIR__ . '/config-data.php';
require_once __DIR__ . '/draft-store.php';
require_once __DIR__ . '/create-wizard-handler.php';
require_once __DIR__ . '/wireframe-preview.php';
require_once __DIR__ . '/enqueue.php';
require_once __DIR__ . '/render-wizard-ui.php';
