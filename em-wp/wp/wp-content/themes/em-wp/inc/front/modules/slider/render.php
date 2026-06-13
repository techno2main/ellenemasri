<?php
/**
 * Rendu front du module Slider.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Extrait un identifiant YouTube depuis une URL.
 */
function em_wp_slider_extract_youtube_id(string $url): string
{
    if ($url === '') {
        return '';
    }

    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $matches)) {
        return (string) ($matches[1] ?? '');
    }

    return '';
}

/**
 * Extrait un identifiant video TikTok depuis une URL.
 */
function em_wp_slider_extract_tiktok_video_id(string $url): string
{
    if ($url === '') {
        return '';
    }

    if (preg_match('~/video/(\d+)~', $url, $matches)) {
        return (string) ($matches[1] ?? '');
    }

    return '';
}

/**
 * Retourne les options slider pour le front.
 */
function em_wp_get_slider_options_for_front(string $style_slug = 'mayami'): array
{
    if (function_exists('em_wp_slider_get_options')) {
        return em_wp_slider_get_options($style_slug);
    }

    return [
        'enabled'         => true,
        'frame_bg_color'  => '#12338f',
        'footer_bg_color' => '#f2ebd1',
        'footer_text'     => '#100421',
        'footer_title'    => __('Mayami, My Miami', 'em-wp'),
        'slider_title_hidden' => false,
        'slides'          => [],
    ];
}

/**
 * Retourne la liste des slides actives (ordre du tableau slides[]).
 */
function em_wp_slider_collect_slides(array $slider): array
{
    $slides = [];
    $raw_slides = function_exists('em_wp_slider_get_slides_list')
        ? em_wp_slider_get_slides_list($slider)
        : [];

    foreach ($raw_slides as $index => $item) {
        if (!is_array($item)) {
            continue;
        }

        $slide = em_wp_slider_normalize_slide_item($item);
        $slide_type = $slide['type'];
        $name = trim($slide['name']);
        $image = trim($slide['image']);
        $video_url = trim($slide['video_url']);
        $tiktok_url = trim($slide['tiktok_url']);
        $tiktok_video_url = trim($slide['tiktok_video_url']);
        $alt_text = trim($slide['alt_text']);
        $slide_duration_seconds = max(1, intval($slide['duration']));
        $slide_delay_ms = $slide_duration_seconds * 1000;
        $position = (int) $index + 1;

        if (!empty($slide['hidden'])) {
            continue;
        }

        if ($slide_type === 'video') {
            $video_id = em_wp_slider_extract_youtube_id($video_url);
            if ($video_id === '') {
                continue;
            }

            $slides[] = [
                'type' => 'video',
                'name' => ($name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position)),
                'delay_ms' => $slide_delay_ms,
                'video_id' => $video_id,
            ];

            continue;
        }

        if ($slide_type === 'tiktok') {
            if ($tiktok_url === '' && $tiktok_video_url === '') {
                continue;
            }

            $slides[] = [
                'type' => 'tiktok',
                'name' => ($name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position)),
                'delay_ms' => $slide_delay_ms,
                'tiktok_url' => $tiktok_url,
                'tiktok_video_url' => $tiktok_video_url,
                'tiktok_video_id' => em_wp_slider_extract_tiktok_video_id($tiktok_url),
                'image' => $image,
                'alt' => $alt_text,
            ];

            continue;
        }

        if ($image === '') {
            continue;
        }

        $slides[] = [
            'type'  => 'image',
            'image' => $image,
            'name'  => ($name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position)),
            'alt'   => ($alt_text !== '' ? $alt_text : ($name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position))),
            'delay_ms' => $slide_delay_ms,
        ];
    }

    return $slides;
}

/**
 * Affiche le module slider dans la colonne droite du Hero.
 */
function em_wp_render_slider_in_hero(): void
{
    $slider_style_slug = function_exists('em_wp_slider_active_style_slug')
        ? em_wp_slider_active_style_slug()
        : 'mayami';

    $slider = em_wp_get_slider_options_for_front($slider_style_slug);
    if (empty($slider['enabled'])) {
        return;
    }

    $slides = em_wp_slider_collect_slides($slider);

    get_template_part('template-parts/sections/slider/' . $slider_style_slug . '/slider', null, [
        'slider' => $slider,
        'slides' => $slides,
    ]);
}
