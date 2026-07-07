<?php
/**
 * Catalogue et listes front des plateformes stream.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Définitions des plateformes stream (slug, label, icône, couleur).
 *
 * @return array<string, array{label:string,icon:string,color:string}>
 */
function em_site_stream_platform_definitions(): array
{
    return [
        'spotify' => [
            'label' => __('Spotify', 'em-site'),
            'icon'  => 'fa-spotify',
            'color' => '#1DB954',
        ],
        'apple-music' => [
            'label' => __('Apple Music', 'em-site'),
            'icon'  => 'fa-apple',
            'color' => '#FC3C44',
        ],
        'youtube-music' => [
            'label' => __('YouTube Music', 'em-site'),
            'icon'  => 'fa-youtube',
            'color' => '#FF0000',
        ],
        'deezer' => [
            'label' => __('Deezer', 'em-site'),
            'icon'  => 'fa-deezer',
            'color' => '#A238FF',
        ],
        'amazon-music' => [
            'label' => __('Amazon Music', 'em-site'),
            'icon'  => 'fa-amazon',
            'color' => '#00A8E1',
        ],
        'soundcloud' => [
            'label' => __('SoundCloud', 'em-site'),
            'icon'  => 'fa-soundcloud',
            'color' => '#FF5500',
        ],
    ];
}

/**
 * Compat TOP-BAR : label + icône uniquement.
 *
 * @return array<string, array{label:string,icon:string}>
 */
function em_site_top_bar_stream_platform_definitions(): array
{
    $definitions = [];

    foreach (em_site_stream_platform_definitions() as $slug => $definition) {
        $definitions[$slug] = [
            'label' => (string) ($definition['label'] ?? $slug),
            'icon'  => (string) ($definition['icon'] ?? 'fa-link'),
        ];
    }

    return $definitions;
}

/**
 * Plateformes actives pour la section STREAM (source : em_site_stream_options.platforms).
 *
 * @return array<int, array{
 *     slug:string,
 *     key:string,
 *     label:string,
 *     href:string,
 *     icon:string,
 *     color:string,
 *     embed_src:string,
 *     has_player:bool,
 *     player_height:int
 * }>
 */
function em_site_get_stream_platforms_for_front(): array
{
    $platforms = [];

    foreach (em_site_stream_get_platforms_list() as $link) {
        $item = em_site_stream_build_front_platform_item($link);
        if ($item !== null) {
            $platforms[] = $item;
        }
    }

    return $platforms;
}

/**
 * Icônes stream pour la TOP-BAR (plateformes actives STREAM, masquage section TOP-BAR).
 *
 * @return array<int, array{slug:string,label:string,icon:string}>
 */
function em_site_get_top_bar_stream_icons_for_front(): array
{
    $top_bar_options = function_exists('em_site_top_bar_get_options_for_front')
        ? em_site_top_bar_get_options_for_front()
        : (function_exists('em_site_top_bar_get_options') ? em_site_top_bar_get_options() : []);

    if (!empty($top_bar_options['stream_icons_hidden'])) {
        return [];
    }

    $icons = [];

    foreach (em_site_get_stream_platforms_for_front() as $platform) {
        $icons[] = [
            'slug'  => (string) ($platform['slug'] ?? ''),
            'label' => (string) ($platform['label'] ?? ''),
            'icon'  => (string) ($platform['icon'] ?? 'fa-link'),
        ];
    }

    return $icons;
}
