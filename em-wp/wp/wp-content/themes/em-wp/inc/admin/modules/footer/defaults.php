<?php
/**
 * Defaults et identifiants admin Footer.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_footer_page_slug(): string
{
    return 'em-wp-footer';
}

function em_wp_footer_form_option_key(): string
{
    return 'em_wp_footer_options';
}

function em_wp_footer_default_options(): array
{
    return [
        'enabled'             => true,
        'background_color'    => '',
        'text_color'          => '',
        'line1'               => __('© Your Artist Name', 'em-wp'),
        'line2'               => __('Your project tagline.', 'em-wp'),
        'sticky_stream_label' => __('▶ Stream', 'em-wp'),
        'sticky_video_label'  => __('◉ Video', 'em-wp'),
        'sticky_tiktok_label' => __('TikTok', 'em-wp'),
        'sticky_tiktok_link'  => '',
    ];
}
