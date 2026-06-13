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
require_once __DIR__ . '/modules/hero/render.php';
require_once __DIR__ . '/modules/slider/render.php';
require_once __DIR__ . '/modules/stream/render.php';
require_once __DIR__ . '/landing-render.php';

/**
 * Slugs modules gérés côté front.
 */
function em_wp_front_module_slugs(): array
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
