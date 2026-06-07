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
            'label' => __('Release Info', 'ellene-wp'),
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
    );

    return apply_filters('ellene_module_registry', $registry);
}
