<?php
/**
 * Defaults et identifiants admin CTA.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_page_slug(): string
{
    return 'em-cta';
}

function em_wp_cta_form_option_key(): string
{
    return em_wp_cta_option_name(em_wp_cta_admin_template_slug());
}

function em_wp_cta_default_texture_url(): string
{
    $relative_path = 'assets/front/images/mayami/cta-texture.jpg';

    if (!is_readable(get_template_directory() . '/' . $relative_path)) {
        return '';
    }

    return get_template_directory_uri() . '/' . $relative_path;
}

function em_wp_cta_rubrique_default_options(): array
{
    return [
        'enabled'          => true,
        'cta_slug'         => '',
        'background_color' => '',
        'text_color'       => '',
    ];
}

function em_wp_cta_catalog_default_options(): array
{
    return [
        'kicker'            => __('05 / Call To Action', 'em-wp'),
        'title_left'        => __('Press', 'em-wp'),
        'title_right'       => __('play.', 'em-wp'),
        'description'       => __('Invite your audience to stream, watch, and share.', 'em-wp'),
        'hashtag'           => '#YourHashtag',
        'stream_label'      => __('Stream', 'em-wp'),
        'stream_link'       => '#stream',
        'video_label'       => __('Watch', 'em-wp'),
        'video_link'        => '#video',
        'tiktok_label'      => __('TikTok', 'em-wp'),
        'tiktok_link'       => '',
        'instagram_label'   => __('Instagram', 'em-wp'),
        'instagram_link'    => '',
        'texture_image'     => em_wp_cta_default_texture_url(),
    ];
}

