<?php
/**
 * Defaults et identifiants admin Footer.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_footer_page_slug(): string
{
    return 'em-footer';
}

function em_site_footer_form_option_key(): string
{
    return em_site_footer_option_name(em_site_footer_admin_template_slug());
}

function em_site_footer_rubrique_default_options(): array
{
    return [
        'enabled'          => true,
        'footer_slug'      => '',
        'background_color' => '',
        'text_color'       => '',
    ];
}

function em_site_footer_catalog_default_options(): array
{
    return [
        'line1'               => __('© Your Artist Name', 'em-site'),
        'line2'               => __('Your project tagline.', 'em-site'),
        'sticky_stream_label' => __('▶ Stream', 'em-site'),
        'sticky_video_label'  => __('◉ Video', 'em-site'),
        'sticky_tiktok_label' => __('TikTok', 'em-site'),
        'sticky_tiktok_link'  => '',
    ];
}

