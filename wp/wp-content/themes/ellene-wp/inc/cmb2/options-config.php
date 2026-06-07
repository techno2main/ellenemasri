<?php

/**
 * CMB2 Configuration - Page unique avec navigation sticky
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('ELLENE_WP_LANDING_OPTIONS_KEY')) {
    define('ELLENE_WP_LANDING_OPTIONS_KEY', 'ellene-wp_landing_options');
}

if (!defined('ELLENE_WP_LEGACY_LANDING_OPTIONS_KEY')) {
    define('ELLENE_WP_LEGACY_LANDING_OPTIONS_KEY', 'ma' . 'yami_landing_options');
}

/**
 * One-shot migration from legacy option key to the active theme option key.
 * No runtime fallback is kept after migration.
 */
function ellene_wp_migrate_legacy_landing_options_once() {
    $migration_flag_key = 'ellene_wp_landing_options_migrated_v1';

    if (get_option($migration_flag_key, '0') === '1') {
        return;
    }

    $current_options = get_option(ELLENE_WP_LANDING_OPTIONS_KEY, null);
    if (is_array($current_options) && !empty($current_options)) {
        update_option($migration_flag_key, '1', false);
        return;
    }

    $legacy_options = get_option(ELLENE_WP_LEGACY_LANDING_OPTIONS_KEY, null);
    if (is_array($legacy_options) && !empty($legacy_options)) {
        update_option(ELLENE_WP_LANDING_OPTIONS_KEY, $legacy_options, false);
    }

    update_option($migration_flag_key, '1', false);
}

add_action('init', 'ellene_wp_migrate_legacy_landing_options_once', 1);

require_once get_template_directory() . '/inc/cmb2/options-sections/hero.php';
require_once get_template_directory() . '/inc/cmb2/options-sections/slider.php';
require_once get_template_directory() . '/inc/cmb2/options-sections/stream.php';
require_once get_template_directory() . '/inc/cmb2/options-sections/social.php';
require_once get_template_directory() . '/inc/cmb2/options-sections/video.php';
require_once get_template_directory() . '/inc/cmb2/options-sections/release.php';
require_once get_template_directory() . '/inc/cmb2/options-sections/cta.php';
require_once get_template_directory() . '/inc/cmb2/options-sections/footer.php';
require_once get_template_directory() . '/inc/cmb2/options-sections/top-bar.php';
require_once get_template_directory() . '/inc/cmb2/options-sections/modules.php';

function ellene_wp_get_primary_stream_link(array $options) {
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

add_action('cmb2_admin_init', 'ellene_wp_register_options');

function ellene_wp_register_options() {
    $cmb = new_cmb2_box(array(
        'id'           => ELLENE_WP_LANDING_OPTIONS_KEY,
        'title'        => __('LANDING PAGE SETTINGS', 'ellene-wp'),
        'object_types' => array('options-page'),
        'option_key'   => ELLENE_WP_LANDING_OPTIONS_KEY,
        'menu_title'   => __('LANDING PAGE', 'ellene-wp'),
        'position'     => 2,
        'capability'   => 'manage_options',
    ));

    ellene_wp_register_cmb2_top_bar_section($cmb);
    ellene_wp_register_cmb2_hero_section($cmb);
    ellene_wp_register_cmb2_slider_section($cmb);
    ellene_wp_register_cmb2_stream_section($cmb);
    ellene_wp_register_cmb2_social_section($cmb);
    ellene_wp_register_cmb2_video_section($cmb);
    ellene_wp_register_cmb2_release_section($cmb);
    ellene_wp_register_cmb2_cta_section($cmb);
    ellene_wp_register_cmb2_footer_section($cmb);
    ellene_wp_register_cmb2_modules_section($cmb);
}
