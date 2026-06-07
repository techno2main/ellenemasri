<?php

/**
 * Social section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_register_cmb2_social_section($cmb) {
    // ========== SECTION: SOCIAL ==========

    $cmb->add_field(array(
        'name' => 'Social',
        'type' => 'title',
        'id'   => 'section_social_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Kicker',
        'id'      => 'social_kicker',
        'type'    => 'text',
        'default' => '02 / Follow',
    ));

    $cmb->add_field(array(
        'name'    => 'Title Left',
        'id'      => 'social_title_left',
        'type'    => 'text',
        'default' => 'Join the',
    ));

    $cmb->add_field(array(
        'name'    => 'Title Right',
        'id'      => 'social_title_right',
        'type'    => 'text',
        'default' => 'journey',
    ));

    $cmb->add_field(array(
        'name'    => 'Description',
        'id'      => 'social_description',
        'type'    => 'textarea_small',
        'default' => 'Share clips, updates, and behind-the-scenes moments.',
    ));

    $cmb->add_field(array(
        'name' => 'TikTok Link',
        'id'   => 'social_tiktok_link',
        'type' => 'text_url',
    ));

    $cmb->add_field(array(
        'name'    => 'TikTok Label',
        'id'      => 'social_tiktok_label',
        'type'    => 'text',
        'default' => 'TikTok',
    ));

    $cmb->add_field(array(
        'name'    => 'TikTok Badge',
        'id'      => 'social_tiktok_badge',
        'type'    => 'text',
        'default' => 'Follow',
    ));

    $cmb->add_field(array(
        'name' => 'Instagram Link',
        'id'   => 'social_instagram_link',
        'type' => 'text_url',
    ));

    $cmb->add_field(array(
        'name'    => 'Instagram Label',
        'id'      => 'social_instagram_label',
        'type'    => 'text',
        'default' => 'Instagram',
    ));

    $cmb->add_field(array(
        'name'    => 'Instagram Badge',
        'id'      => 'social_instagram_badge',
        'type'    => 'text',
        'default' => 'Follow',
    ));

    $cmb->add_field(array(
        'name' => 'YouTube Link',
        'id'   => 'social_youtube_link',
        'type' => 'text_url',
    ));

    $cmb->add_field(array(
        'name'    => 'YouTube Label',
        'id'      => 'social_youtube_label',
        'type'    => 'text',
        'default' => 'YouTube',
    ));

    $cmb->add_field(array(
        'name'    => 'YouTube Badge',
        'id'      => 'social_youtube_badge',
        'type'    => 'text',
        'default' => 'Watch',
    ));
}