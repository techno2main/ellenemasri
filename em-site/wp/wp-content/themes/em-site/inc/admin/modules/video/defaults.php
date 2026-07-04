<?php
/**
 * Defaults et identifiants admin Video.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_video_page_slug(): string
{
    return 'em-videos';
}

function em_wp_video_form_option_key(): string
{
    return em_wp_video_option_name(em_wp_video_admin_template_slug());
}

/**
 * Options rubrique VIDEOS par template (pointeur catalogue + style).
 *
 * @return array{enabled:bool,video_slug:string,background_color:string,text_color:string}
 */
function em_wp_video_rubrique_default_options(): array
{
    return [
        'enabled'          => true,
        'video_slug'       => '',
        'background_color' => '',
        'text_color'       => '',
    ];
}

/**
 * Options contenu d'une entrée catalogue Video.
 *
 * @return array<string, mixed>
 */
function em_wp_video_catalog_default_options(): array
{
    return [
        'kicker'             => __('03 / Watch', 'em-wp'),
        'title'              => __('Official Video', 'em-wp'),
        'description'        => __('Describe the official video for this release.', 'em-wp'),
        'watch_label'        => __('Watch', 'em-wp'),
        'watch_href'         => '',
        'watch_disable_link' => false,
        'cover_image'        => '',
    ];
}

