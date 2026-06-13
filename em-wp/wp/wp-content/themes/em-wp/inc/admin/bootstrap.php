<?php
/**
 * Bootstrap de la couche admin (BO).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/shared/style-panel.php';
require_once __DIR__ . '/shared/assets.php';
require_once __DIR__ . '/modules/top-bar/stream-links.php';
require_once __DIR__ . '/modules/top-bar/settings.php';
require_once __DIR__ . '/modules/hero/settings.php';
require_once __DIR__ . '/modules/slider/slides.php';
require_once __DIR__ . '/modules/slider/settings.php';

/**
 * Slugs modules gérés côté admin.
 */
function em_wp_admin_module_slugs(): array
{
    return [
        'top-bar',
        'hero',
        'slider',
        'stream',
        'social',
        'video',
        'release',
        'cta',
        'footer',
    ];
}
