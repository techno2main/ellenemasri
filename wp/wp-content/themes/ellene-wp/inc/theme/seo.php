<?php

/**
 * SEO-related theme hooks.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_force_landing_noindex($robots) {
    if (is_admin()) {
        return $robots;
    }

    if (is_front_page() || is_home()) {
        return array(
            'noindex' => true,
            'nofollow' => true,
            'noarchive' => true,
            'nosnippet' => true,
            'max-snippet' => 0,
            'max-image-preview' => 'none',
            'max-video-preview' => 0,
        );
    }

    return $robots;
}

add_filter('wp_robots', 'mayami_force_landing_noindex');