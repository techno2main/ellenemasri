<?php
/**
 * Defaults et identifiants admin CTA.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_cta_page_slug(): string
{
    return 'em-cta';
}

function em_site_cta_form_option_key(): string
{
    return em_site_cta_option_name(em_site_cta_admin_template_slug());
}

function em_site_cta_default_texture_url(): string
{
    $relative_path = 'assets/front/images/mayami/cta-texture.jpg';

    if (!is_readable(get_template_directory() . '/' . $relative_path)) {
        return '';
    }

    return get_template_directory_uri() . '/' . $relative_path;
}

function em_site_cta_rubrique_default_options(): array
{
    return [
        'enabled'          => true,
        'cta_slug'         => '',
        'background_color' => '',
        'text_color'       => '',
    ];
}

function em_site_cta_catalog_default_options(): array
{
    return [
        'kicker'            => __('05 / Call To Action', 'em-site'),
        'title_left'        => __('Press', 'em-site'),
        'title_right'       => __('play.', 'em-site'),
        'description'       => __('Invite your audience to stream, watch, and share.', 'em-site'),
        'hashtag'           => '#YourHashtag',
        'stream_label'      => __('Stream', 'em-site'),
        'stream_link'       => '#stream',
        'video_label'       => __('Watch', 'em-site'),
        'video_link'        => '#video',
        'tiktok_label'      => __('TikTok', 'em-site'),
        'tiktok_link'       => '',
        'instagram_label'   => __('Instagram', 'em-site'),
        'instagram_link'    => '',
        'texture_image'     => em_site_cta_default_texture_url(),
    ];
}

