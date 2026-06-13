<?php
/**
 * Rendu front du module Social.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_social_enqueue_front_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');
    $theme_uri = get_template_directory_uri();
    $css_path = 'assets/front/css/modules/social/social.css';

    wp_enqueue_style(
        'em-wp-landing-ui',
        $theme_uri . '/assets/front/css/landing-ui.css',
        ['em-wp-theme'],
        file_exists(get_template_directory() . '/assets/front/css/landing-ui.css')
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/assets/front/css/landing-ui.css')
            : $theme_version
    );

    wp_enqueue_style(
        'em-wp-social',
        $theme_uri . '/' . $css_path,
        ['em-wp-landing-ui', 'font-awesome-6'],
        file_exists(get_template_directory() . '/' . $css_path)
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/' . $css_path)
            : $theme_version
    );
}

function em_wp_render_social(): void
{
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('social')) {
        return;
    }

    $options = function_exists('em_wp_social_get_options_for_front')
        ? em_wp_social_get_options_for_front()
        : em_wp_social_get_options();

    if (empty($options['enabled'])) {
        return;
    }

    em_wp_social_enqueue_front_assets();

    get_template_part('template-parts/sections/social/social', null, [
        'social' => $options,
        'cards'  => em_wp_get_social_cards_for_front(),
    ]);
}
