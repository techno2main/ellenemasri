<?php
/**
 * Bootstrap de la couche admin (BO).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slugs modules gérés côté admin.
 */
function em_wp_admin_module_slugs(): array
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
