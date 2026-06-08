<?php

/**
 * Module registry.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return the module registry.
 *
 * @return array<string, array<string, mixed>>
 */
function ellene_get_module_registry() {
    $registry = array(
        'top-bar' => array(
            'label' => __('Top-Bar', 'ellene-wp'),
            'template' => 'template-parts/sections/top-bar/index',
            'default_enabled' => true,
            'supports_shared' => false,
        ),
        'hero' => array(
            'label' => __('Hero', 'ellene-wp'),
            'template' => 'template-parts/sections/hero/index',
            'default_enabled' => true,
            'supports_shared' => false,
        ),
        'stream' => array(
            'label' => __('Stream', 'ellene-wp'),
            'template' => 'template-parts/sections/stream/index',
            'default_enabled' => true,
            'supports_shared' => true,
        ),
        'social' => array(
            'label' => __('Social', 'ellene-wp'),
            'template' => 'template-parts/sections/social/index',
            'default_enabled' => true,
            'supports_shared' => true,
        ),
        'video' => array(
            'label' => __('Video', 'ellene-wp'),
            'template' => 'template-parts/sections/video/index',
            'default_enabled' => true,
            'supports_shared' => true,
        ),
        'release-info' => array(
            'label' => __('Release', 'ellene-wp'),
            'template' => 'template-parts/sections/release/index',
            'default_enabled' => true,
            'supports_shared' => false,
        ),
        'cta' => array(
            'label' => __('CTA', 'ellene-wp'),
            'template' => 'template-parts/sections/cta/index',
            'default_enabled' => true,
            'supports_shared' => true,
        ),
        'footer' => array(
            'label' => __('Footer', 'ellene-wp'),
            'template' => 'template-parts/sections/footer/index',
            'default_enabled' => true,
            'supports_shared' => false,
        ),
    );

    return apply_filters('ellene_module_registry', $registry);
}
