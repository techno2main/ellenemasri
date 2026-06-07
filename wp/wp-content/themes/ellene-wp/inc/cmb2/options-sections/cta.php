<?php

/**
 * CTA section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_register_cmb2_cta_section($cmb) {
    // ========== SECTION: CTA ==========

    $cmb->add_field(array(
        'name' => 'CTA',
        'type' => 'title',
        'id'   => 'section_cta_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Kicker',
        'id'      => 'cta_kicker',
        'type'    => 'text',
        'default' => '05 / Call To Action',
    ));

    $cmb->add_field(array(
        'name'    => 'Title Left',
        'id'      => 'cta_title_left',
        'type'    => 'text',
        'default' => 'Press',
    ));

    $cmb->add_field(array(
        'name'    => 'Title Right',
        'id'      => 'cta_title_right',
        'type'    => 'text',
        'default' => 'play.',
    ));

    $cmb->add_field(array(
        'name'    => 'Description',
        'id'      => 'cta_description',
        'type'    => 'textarea_small',
        'default' => 'Invite your audience to stream, watch, and share.',
    ));

    $cmb->add_field(array(
        'name'    => 'Hashtag',
        'id'      => 'cta_hashtag',
        'type'    => 'text',
        'default' => '#YourHashtag',
    ));

    $cmb->add_field(array(
        'name'    => 'Stream Button Label',
        'id'      => 'cta_stream_label',
        'type'    => 'text',
        'default' => 'Stream',
    ));

    $cmb->add_field(array(
        'name'    => 'Stream Button Link',
        'id'      => 'cta_stream_link',
        'type'    => 'text',
        'default' => '#stream',
    ));

    $cmb->add_field(array(
        'name'    => 'Video Button Label',
        'id'      => 'cta_video_label',
        'type'    => 'text',
        'default' => 'Watch',
    ));

    $cmb->add_field(array(
        'name'    => 'Video Button Link',
        'id'      => 'cta_video_link',
        'type'    => 'text',
        'default' => '#video',
    ));

    $cmb->add_field(array(
        'name'    => 'TikTok Button Label',
        'id'      => 'cta_tiktok_label',
        'type'    => 'text',
        'default' => 'TikTok',
    ));

    $cmb->add_field(array(
        'name'    => 'TikTok Button Link',
        'id'      => 'cta_tiktok_link',
        'type'    => 'text_url',
        'default' => '',
    ));

    $cmb->add_field(array(
        'name'    => 'Instagram Button Label',
        'id'      => 'cta_instagram_label',
        'type'    => 'text',
        'default' => 'Instagram',
    ));

    $cmb->add_field(array(
        'name'    => 'Instagram Button Link',
        'id'      => 'cta_instagram_link',
        'type'    => 'text_url',
        'default' => '',
    ));

    $cmb->add_field(array(
        'name' => 'Texture Image',
        'id'   => 'cta_texture_image',
        'type' => 'file',
    ));
}