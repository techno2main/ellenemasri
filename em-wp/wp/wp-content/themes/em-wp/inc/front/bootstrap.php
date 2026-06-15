<?php
/**
 * Bootstrap de la couche front.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/modules/top-bar/render.php';
require_once __DIR__ . '/modules/header/render.php';
require_once __DIR__ . '/modules/hero/render.php';
require_once __DIR__ . '/modules/slider/render.php';
require_once __DIR__ . '/modules/stream/render.php';
require_once __DIR__ . '/modules/social/render.php';
require_once __DIR__ . '/modules/video/render.php';
require_once __DIR__ . '/modules/release/render.php';
require_once __DIR__ . '/modules/cta/render.php';
require_once __DIR__ . '/modules/contacts/render.php';
require_once __DIR__ . '/modules/footer/render.php';
require_once __DIR__ . '/template-context.php';
require_once __DIR__ . '/landing-render.php';

/**
 * Slugs modules gérés côté front.
 */
function em_wp_front_module_slugs(): array
{
    return [
        'top-bar',
        'header',
        'stream',
        'social',
        'video',
        'release',
        'cta',
        'contacts',
        'footer',
    ];
}
