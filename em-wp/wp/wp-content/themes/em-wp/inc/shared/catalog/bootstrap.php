<?php
/**
 * Bootstrap catalogues Hero / Slider (V2 Phase 4).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/hero/registry.php';
require_once __DIR__ . '/hero/option-names.php';
require_once __DIR__ . '/hero/crud.php';
require_once __DIR__ . '/slider/registry.php';
require_once __DIR__ . '/slider/option-names.php';
require_once __DIR__ . '/slider/crud.php';
require_once __DIR__ . '/migrate-v1.php';
require_once __DIR__ . '/resolve-style.php';

add_action('init', 'em_wp_catalog_maybe_migrate_v1', 5);
