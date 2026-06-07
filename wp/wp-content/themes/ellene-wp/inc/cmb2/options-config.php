<?php

/**
 * CMB2 Configuration - Page unique avec navigation sticky
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/cmb2/sections-config/hero.php';
require_once get_template_directory() . '/inc/cmb2/sections-config/slider.php';
require_once get_template_directory() . '/inc/cmb2/sections-config/stream.php';
require_once get_template_directory() . '/inc/cmb2/sections-config/social.php';
require_once get_template_directory() . '/inc/cmb2/sections-config/video.php';
require_once get_template_directory() . '/inc/cmb2/sections-config/release.php';
require_once get_template_directory() . '/inc/cmb2/sections-config/cta.php';
require_once get_template_directory() . '/inc/cmb2/sections-config/footer.php';
require_once get_template_directory() . '/inc/cmb2/sections-config/top-bar.php';
require_once get_template_directory() . '/inc/cmb2/modules.php';

function mayami_get_primary_stream_link(array $options) {
    if (empty($options['stream_platforms']) || !is_array($options['stream_platforms'])) {
        return '';
    }

    $fallback_link = '';

    foreach ($options['stream_platforms'] as $platform) {
        if (!is_array($platform) || empty($platform['is_active'])) {
            continue;
        }

        $label = isset($platform['label']) ? strtolower(trim((string) $platform['label'])) : '';
        $href = isset($platform['href']) ? trim((string) $platform['href']) : '';

        if ($href === '') {
            continue;
        }

        if (strpos($label, 'spotify') !== false) {
            return $href;
        }

        if ($fallback_link === '') {
            $fallback_link = $href;
        }
    }

    return $fallback_link;
}

add_action('cmb2_admin_init', 'mayami_register_options');

function mayami_register_options() {
    $cmb = new_cmb2_box(array(
        'id'           => 'mayami_landing_options',
        'title'        => __('ellene-wp Landing Settings', 'ellene-wp'),
        'object_types' => array('options-page'),
        'option_key'   => 'mayami_landing_options',
        'menu_title'   => __('ellene-wp Landing', 'ellene-wp'),
        'position'     => 2,
        'capability'   => 'manage_options',
    ));

    mayami_register_cmb2_top_bar_section($cmb);
    mayami_register_cmb2_hero_section($cmb);
    mayami_register_cmb2_slider_section($cmb);
    mayami_register_cmb2_stream_section($cmb);
    mayami_register_cmb2_social_section($cmb);
    mayami_register_cmb2_video_section($cmb);
    mayami_register_cmb2_release_section($cmb);
    mayami_register_cmb2_cta_section($cmb);
    mayami_register_cmb2_footer_section($cmb);
    mayami_register_cmb2_modules_section($cmb);
}
