<?php
/**
 * Rendu front du module Hero.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retourne les options hero pour le front.
 */
function em_wp_get_hero_options_for_front(string $style_slug = 'mayami'): array
{
    if (function_exists('em_wp_hero_get_options')) {
        return em_wp_hero_get_options($style_slug);
    }

    $defaults = [
        'enabled'                  => true,
        'badge_text'               => __('New Single · Available!', 'em-wp'),
        'badge_text_hidden'        => false,
        'subtitle'                 => __('Mayami, My Miami', 'em-wp'),
        'subtitle_hidden'          => false,
        'main_title'               => __('Mayami, My Miami', 'em-wp'),
        'background_image'         => '',
        'background_image_hidden'  => false,
        'logo_image'               => '',
        'logo_hidden'              => false,
        'logo_alt'                 => __('Mayami, My Miami', 'em-wp'),
        'description'              => '',
        'description_hidden'       => false,
        'stream_label'             => __('◉ Stream', 'em-wp'),
        'stream_hidden'            => false,
        'stream_href'              => '#stream',
        'watch_label'              => __('▶ Watch', 'em-wp'),
        'watch_hidden'             => false,
        'watch_href'               => '#video',
    ];

    $saved = get_option('em_wp_hero_options', []);
    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, $defaults);
}

/**
 * Affiche le module hero via son template part.
 *
 * @param array{embed_slider?:bool,layout?:string} $args
 */
function em_wp_render_hero(array $args = []): void
{
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('hero')) {
        return;
    }

    $embed_slider = array_key_exists('embed_slider', $args) ? (bool) $args['embed_slider'] : true;
    $layout = (string) ($args['layout'] ?? ($embed_slider ? 'default' : 'standalone'));

    $hero_style_slug = function_exists('em_wp_hero_active_style_slug')
        ? em_wp_hero_active_style_slug()
        : 'mayami';

    $hero = em_wp_get_hero_options_for_front($hero_style_slug);
    if (empty($hero['enabled'])) {
        return;
    }

    get_template_part('template-parts/sections/hero/' . $hero_style_slug . '/hero', null, [
        'hero'         => $hero,
        'embed_slider' => $embed_slider,
        'layout'       => $layout,
    ]);
}
