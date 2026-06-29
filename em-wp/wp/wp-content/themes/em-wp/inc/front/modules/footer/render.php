<?php
/**
 * Rendu front du module Footer.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_footer_enqueue_front_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');
    $theme_uri = get_template_directory_uri();
    $css_path = 'assets/front/css/modules/footer/footer.css';

    wp_enqueue_style(
        'em-wp-landing-ui',
        $theme_uri . '/assets/front/css/landing-ui.css',
        ['em-wp-theme'],
        file_exists(get_template_directory() . '/assets/front/css/landing-ui.css')
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/assets/front/css/landing-ui.css')
            : $theme_version
    );

    wp_enqueue_style(
        'em-wp-footer',
        $theme_uri . '/' . $css_path,
        ['em-wp-landing-ui'],
        file_exists(get_template_directory() . '/' . $css_path)
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/' . $css_path)
            : $theme_version
    );
}

function em_wp_render_landing_footer(): void
{
    if (function_exists('em_wp_front_v4_render_module') && em_wp_front_v4_render_module('footer')) {
        return;
    }

    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('footer')) {
        return;
    }

    $options = function_exists('em_wp_footer_get_options_for_front')
        ? em_wp_footer_get_options_for_front()
        : em_wp_footer_get_options();

    // Aucun footer sélectionné et aucun item Default disponible : on n'affiche rien.
    if (empty($options['footer_slug'])) {
        return;
    }

    em_wp_footer_enqueue_front_assets();

    get_template_part('template-parts/sections/footer/footer', null, [
        'footer' => $options,
    ]);

    get_template_part('template-parts/sections/footer/sticky-bar', null, [
        'footer' => $options,
    ]);
}
