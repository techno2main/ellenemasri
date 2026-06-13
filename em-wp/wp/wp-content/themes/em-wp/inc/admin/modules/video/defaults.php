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
    return 'em-wp-videos';
}

function em_wp_video_form_option_key(): string
{
    return 'em_wp_video_options';
}

function em_wp_video_default_options(): array
{
    return [
        'enabled'            => true,
        'background_color'   => '',
        'text_color'         => '',
        'kicker'             => __('03 / Watch', 'em-wp'),
        'title'              => __('Official Video', 'em-wp'),
        'description'        => __('Describe the official video for this release.', 'em-wp'),
        'watch_label'        => __('Watch', 'em-wp'),
        'watch_href'         => '',
        'watch_disable_link' => false,
        'cover_image'        => '',
    ];
}
