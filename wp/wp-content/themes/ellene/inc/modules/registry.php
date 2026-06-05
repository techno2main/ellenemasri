<?php

/**
 * Ellene module registry.
 *
 * @package Mayami
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
            'label' => __('Stream', 'ellene'),
            'template' => 'template-parts/sections/stream',
            'default_enabled' => true,
            'supports_shared' => true,
        ),
        'social' => array(
            'label' => __('Social', 'ellene'),
            'template' => 'template-parts/sections/social',
            'default_enabled' => true,
            'supports_shared' => true,
        ),
        'video' => array(
            'label' => __('Video', 'ellene'),
            'template' => 'template-parts/sections/video',
            'default_enabled' => true,
            'supports_shared' => true,
        ),
        'release-info' => array(
            'label' => __('Release Info', 'ellene'),
            'template' => 'template-parts/sections/release-info',
            'default_enabled' => true,
            'supports_shared' => false,
        ),
        'visual-links' => array(
            'label' => __('Visual Links', 'ellene'),
            'template' => 'template-parts/sections/visual-links',
            'default_enabled' => true,
            'supports_shared' => false,
        ),
        'cta' => array(
            'label' => __('CTA', 'ellene'),
            'template' => 'template-parts/sections/cta',
            'default_enabled' => true,
            'supports_shared' => true,
        ),
    );

    /**
     * Filter module registry.
     */
    return apply_filters('ellene_module_registry', $registry);
}
