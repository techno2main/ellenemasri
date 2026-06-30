<?php
/**
 * Bootstrap admin — catalogues Hero / Slider.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Active/désactive l'admin legacy Catalogues.
 *
 * Étape 3 purge V4: désactivé par défaut pour couper les entrées BO legacy,
 * tout en conservant le runtime/front tant que la purge n'est pas terminée.
 */
function em_wp_catalog_legacy_admin_enabled(): bool
{
    return (bool) apply_filters('em_wp_catalog_legacy_admin_enabled', false);
}

require_once __DIR__ . '/sommaire.php';
require_once __DIR__ . '/registry-crud.php';
require_once __DIR__ . '/hero-actions.php';
require_once __DIR__ . '/slider-actions.php';
require_once __DIR__ . '/video-actions.php';
require_once __DIR__ . '/stream-actions.php';
require_once __DIR__ . '/social-actions.php';
require_once __DIR__ . '/top-bar-actions.php';
require_once __DIR__ . '/release-actions.php';
require_once __DIR__ . '/cta-actions.php';
require_once __DIR__ . '/footer-actions.php';
require_once __DIR__ . '/custom-modules-admin.php';
require_once __DIR__ . '/custom-module-actions.php';
require_once __DIR__ . '/custom-module-fields.php';
