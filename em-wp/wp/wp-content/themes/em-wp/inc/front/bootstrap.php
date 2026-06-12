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

/**
 * Slugs modules gérés côté front.
 */
function em_wp_front_module_slugs(): array
{
    return [
        'top-bar',
        'hero',
        'stream',
        'social',
        'video',
        'release',
        'cta',
        'footer',
    ];
}
