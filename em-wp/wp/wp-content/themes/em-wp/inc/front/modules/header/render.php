<?php
/**
 * Rendu front rubrique HEADER (Hero et/ou Slider catalogue).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Affiche la rubrique HEADER selon le template live.
 */
function em_wp_render_header(): void
{
    if (!function_exists('em_wp_header_get_options_for_front')) {
        return;
    }

    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('header')) {
        return;
    }

    $header = em_wp_header_get_options_for_front();

    if (empty($header['enabled'])) {
        return;
    }

    $hero_slug = sanitize_key((string) ($header['hero_slug'] ?? ''));
    $slider_slug = sanitize_key((string) ($header['slider_slug'] ?? ''));
    $layout = (string) ($header['layout'] ?? 'hero_left');

    if ($hero_slug === '' && $slider_slug === '') {
        return;
    }

    if ($hero_slug !== '' && $slider_slug === '') {
        em_wp_render_hero([
            'catalog_slug' => $hero_slug,
            'embed_slider' => false,
        ]);

        return;
    }

    if ($slider_slug !== '' && $hero_slug === '') {
        em_wp_render_slider_section([
            'catalog_slug' => $slider_slug,
            'wrapper'      => 'section',
            'skip_visibility_check' => true,
        ]);

        return;
    }

    $slider_first = $layout === 'slider_left';

    if (!$slider_first) {
        em_wp_render_hero([
            'catalog_slug' => $hero_slug,
            'slider_slug'  => $slider_slug,
            'embed_slider' => true,
        ]);

        return;
    }

    get_template_part('template-parts/sections/landing/header-pair', null, [
        'hero_slug'   => $hero_slug,
        'slider_slug' => $slider_slug,
        'layout'      => $layout,
    ]);
}
