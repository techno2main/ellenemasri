<?php
/**
 * Définitions des rubriques (sommaire + menu latéral).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Définitions des rubriques affichées dans le sommaire et le menu latéral.
 *
 * @return array<string, array{
 *     label:string,
 *     description:string,
 *     page_slug:string,
 *     menu_title:string,
 *     preview_zone:string,
 *     accent_color:string,
 *     coming_soon?:bool
 * }>
 */
function em_wp_admin_site_rubrique_definitions(): array
{
    $definitions = [
        'top-bar' => [
            'label'        => __('TOP-BAR', 'em-wp'),
            'menu_title'   => __('TOP-BAR', 'em-wp'),
            'description'  => __('Section TOP-BAR / HEADER', 'em-wp'),
            'page_slug'    => function_exists('em_wp_top_bar_page_slug') ? em_wp_top_bar_page_slug() : 'em-wp-top-bar',
            'preview_zone' => 'top_bar',
            'accent_color' => '#100421',
        ],
        'header' => [
            'label'        => __('HEADER', 'em-wp'),
            'menu_title'   => __('HEADER', 'em-wp'),
            'description'  => __('Section HEADER (Hero et/ou Slider)', 'em-wp'),
            'page_slug'    => function_exists('em_wp_header_page_slug') ? em_wp_header_page_slug() : 'em-wp-header',
            'preview_zone' => 'header',
            'accent_color' => '#d94a2d',
        ],
        'stream' => [
            'label'        => __('STREAM', 'em-wp'),
            'menu_title'   => __('STREAM', 'em-wp'),
            'description'  => __('Section 01 / LISTEN', 'em-wp'),
            'page_slug'    => function_exists('em_wp_stream_page_slug') ? em_wp_stream_page_slug() : 'em-wp-stream',
            'preview_zone' => 'section_stream',
            'accent_color' => '#7c3aed',
        ],
        'social' => [
            'label'        => __('SOCIAL', 'em-wp'),
            'menu_title'   => __('SOCIAL', 'em-wp'),
            'description'  => __('Section 02 / FOLLOW', 'em-wp'),
            'page_slug'    => 'em-wp-social',
            'preview_zone' => 'section_social',
            'accent_color' => '#db2777',
            'coming_soon'  => true,
        ],
        'video' => [
            'label'        => __('VIDEOS', 'em-wp'),
            'menu_title'   => __('VIDEOS', 'em-wp'),
            'description'  => __('Section 03 / WATCH', 'em-wp'),
            'page_slug'    => function_exists('em_wp_video_hub_menu_slug') ? em_wp_video_hub_menu_slug() : 'em-wp-videos',
            'preview_zone' => 'section_video',
            'accent_color' => '#ca8a04',
        ],
        'release' => [
            'label'        => __('RELEASES', 'em-wp'),
            'menu_title'   => __('RELEASES', 'em-wp'),
            'description'  => __('Section 04 / RELEASE INFOS', 'em-wp'),
            'page_slug'    => function_exists('em_wp_release_hub_menu_slug') ? em_wp_release_hub_menu_slug() : 'em-wp-releases',
            'preview_zone' => 'section_release',
            'accent_color' => '#b8956a',
        ],
        'cta' => [
            'label'        => __('CTA', 'em-wp'),
            'menu_title'   => __('CTA', 'em-wp'),
            'description'  => __('Section 05 / DON\'T SLEEP ON IT', 'em-wp'),
            'page_slug'    => 'em-wp-cta',
            'preview_zone' => 'section_cta',
            'accent_color' => '#0d9488',
            'coming_soon'  => true,
        ],
        'footer' => [
            'label'        => __('FOOTER', 'em-wp'),
            'menu_title'   => __('FOOTER', 'em-wp'),
            'description'  => __('Section FOOTER', 'em-wp'),
            'page_slug'    => 'em-wp-footer',
            'preview_zone' => 'section_footer',
            'accent_color' => '#100421',
            'coming_soon'  => true,
        ],
    ];

    $ordered = [];

    foreach (em_wp_admin_site_rubrique_modules() as $module_slug) {
        if (isset($definitions[$module_slug])) {
            $ordered[$module_slug] = $definitions[$module_slug];
        }
    }

    return $ordered;
}

/**
 * Slug admin à ouvrir pour une rubrique (variante active si hub multi-choix).
 */
function em_wp_admin_site_rubrique_entry_page_slug(string $module_slug): string
{
    $definitions = em_wp_admin_site_rubrique_definitions();
    $definition = $definitions[$module_slug] ?? null;

    if (!is_array($definition)) {
        return '';
    }

    if ($module_slug === 'header') {
        return function_exists('em_wp_header_page_slug') ? em_wp_header_page_slug() : 'em-wp-header';
    }

    return (string) ($definition['page_slug'] ?? '');
}

/**
 * URL admin d'entrée d'une rubrique (alignée sur le menu latéral).
 */
function em_wp_admin_site_rubrique_entry_url(string $module_slug): string
{
    $page_slug = em_wp_admin_site_rubrique_entry_page_slug($module_slug);

    if ($page_slug === '') {
        return '';
    }

    return add_query_arg(['page' => $page_slug], admin_url('admin.php'));
}
