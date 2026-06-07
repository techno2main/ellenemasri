<?php

/**
 * Video section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_cmb2_video_section($cmb) {
    // ========== SECTION: VIDEO ==========

    $cmb->add_field(array(
        'name' => 'Video',
        'type' => 'title',
        'id'   => 'section_video_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Kicker',
        'id'      => 'video_kicker',
        'type'    => 'text',
        'default' => '03 / Watch',
    ));

    $cmb->add_field(array(
        'name'    => 'Title',
        'id'      => 'video_title',
        'type'    => 'text',
        'default' => 'Official Video',
    ));

    $cmb->add_field(array(
        'name'    => 'Description',
        'id'      => 'video_description',
        'type'    => 'textarea_small',
        'default' => 'Describe the official video for this release.',
    ));

    $cmb->add_field(array(
        'name'    => 'Status Text',
        'id'      => 'video_status',
        'type'    => 'text',
        'default' => 'Coming soon',
    ));

    $cmb->add_field(array(
        'name'    => 'Watch Button Label',
        'id'      => 'video_watch_label',
        'type'    => 'text',
        'default' => 'Watch',
    ));

    $cmb->add_field(array(
        'name'    => 'Watch Button Link',
        'id'      => 'video_watch_href',
        'type'    => 'text_url',
        'default' => '',
    ));

    $cmb->add_field(array(
        'name'    => 'Disable Watch Link',
        'id'      => 'video_watch_disable_link',
        'type'    => 'checkbox',
        'default' => '',
    ));

    $cmb->add_field(array(
        'name' => 'Cover Image',
        'id'   => 'video_cover_image',
        'type' => 'file',
    ));
}