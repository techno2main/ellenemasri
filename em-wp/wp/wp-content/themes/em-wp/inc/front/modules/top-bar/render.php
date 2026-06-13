<?php
/**
 * Rendu front du module Top Bar.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retourne les options top-bar pour le front.
 */
function em_wp_front_top_bar_options(): array
{
    if (function_exists('em_wp_top_bar_get_options_for_front')) {
        return em_wp_top_bar_get_options_for_front();
    }

    if (function_exists('em_wp_top_bar_get_options')) {
        return em_wp_top_bar_get_options();
    }

    return [
        'enabled'          => true,
        'logo_url'         => '',
        'logo_hidden'      => false,
        'background_color' => '#1d1b19',
        'text_color'       => '#ffffff',
        'items'            => [],
    ];
}

/**
 * Affiche le module top-bar via son template part.
 */
function em_wp_render_top_bar(): void
{
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('top-bar')) {
        return;
    }

    $options = em_wp_front_top_bar_options();

    if (empty($options['enabled'])) {
        return;
    }

    if (
        function_exists('em_wp_get_top_bar_stream_icons_for_front')
        && function_exists('em_wp_stream_enqueue_front_assets')
        && em_wp_get_top_bar_stream_icons_for_front() !== []
    ) {
        em_wp_stream_enqueue_front_assets();
    }

    get_template_part('template-parts/sections/top-bar/top-bar', null, [
        'top_bar' => $options,
    ]);
}
