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
require_once __DIR__ . '/video/registry.php';
require_once __DIR__ . '/video/option-names.php';
require_once __DIR__ . '/video/crud.php';
require_once __DIR__ . '/stream/registry.php';
require_once __DIR__ . '/stream/option-names.php';
require_once __DIR__ . '/stream/crud.php';
require_once __DIR__ . '/social/registry.php';
require_once __DIR__ . '/social/option-names.php';
require_once __DIR__ . '/social/crud.php';
require_once __DIR__ . '/top-bar/registry.php';
require_once __DIR__ . '/top-bar/option-names.php';
require_once __DIR__ . '/top-bar/crud.php';
require_once __DIR__ . '/release/registry.php';
require_once __DIR__ . '/release/option-names.php';
require_once __DIR__ . '/release/crud.php';
require_once __DIR__ . '/cta/registry.php';
require_once __DIR__ . '/cta/option-names.php';
require_once __DIR__ . '/cta/crud.php';
require_once __DIR__ . '/footer/registry.php';
require_once __DIR__ . '/footer/option-names.php';
require_once __DIR__ . '/footer/crud.php';
require_once __DIR__ . '/custom-modules/registry.php';
require_once __DIR__ . '/custom-modules/crud.php';
require_once __DIR__ . '/custom-modules/entries.php';
require_once __DIR__ . '/module-overrides.php';
require_once __DIR__ . '/migrate-v1.php';
require_once __DIR__ . '/resolve-style.php';

add_action('init', 'em_wp_catalog_maybe_migrate_v1', 5);
