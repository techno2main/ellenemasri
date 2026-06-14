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
function em_wp_get_hero_options_for_front(string $style_slug = ''): array
{
    if ($style_slug === '' && function_exists('em_wp_hero_active_style_slug')) {
        $style_slug = em_wp_hero_active_style_slug();
    }

    if (function_exists('em_wp_hero_normalize_catalog_slug') && $style_slug !== '') {
        $style_slug = em_wp_hero_normalize_catalog_slug($style_slug);
    }

    if (function_exists('em_wp_hero_get_options')) {
        return em_wp_hero_get_options($style_slug !== '' ? $style_slug : 'hero-mayami-default');
    }

    $defaults = [
        'enabled'                  => true,
        'badge_text'               => __('New Single · Available!', 'em-wp'),
        'badge_text_hidden'        => false,
        'subtitle'                 => __('Mayami, My Miami', 'em-wp'),
        'subtitle_hidden'          => false,
        'main_title'               => __('Mayami, My Miami', 'em-wp'),
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
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('header')) {
        return;
    }

    $embed_slider = array_key_exists('embed_slider', $args) ? (bool) $args['embed_slider'] : true;
    $layout = (string) ($args['layout'] ?? ($embed_slider ? 'default' : 'standalone'));
    $catalog_slug = sanitize_key((string) ($args['catalog_slug'] ?? ''));

    if ($catalog_slug === '' && function_exists('em_wp_header_get_options_for_front')) {
        $header = em_wp_header_get_options_for_front();
        $catalog_slug = sanitize_key((string) ($header['hero_slug'] ?? ''));
    }

    if ($catalog_slug === '' && function_exists('em_wp_hero_active_style_slug')) {
        $catalog_slug = em_wp_hero_active_style_slug();
    }

    $hero = em_wp_get_hero_options_for_front($catalog_slug);
    if (empty($hero['enabled'])) {
        return;
    }

    $slider_slug = sanitize_key((string) ($args['slider_slug'] ?? ''));
    if ($embed_slider && $slider_slug === '' && function_exists('em_wp_header_get_options_for_front')) {
        $header = em_wp_header_get_options_for_front();
        $slider_slug = sanitize_key((string) ($header['slider_slug'] ?? ''));
    }

    $template_layout = 'mayami';

    get_template_part('template-parts/sections/hero/' . $template_layout . '/hero', null, [
        'hero'         => $hero,
        'embed_slider' => $embed_slider,
        'layout'       => $layout,
        'slider_slug'  => $slider_slug,
        'in_header'    => !empty($args['in_header']),
    ]);
}
