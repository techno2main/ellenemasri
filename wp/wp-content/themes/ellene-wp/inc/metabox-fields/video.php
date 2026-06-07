<?php

/**
 * Video section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_video_section_fields($cmb_options) {
    // ============================================
    // TAB: VIDEO SECTION
    // ============================================

    $cmb_options->add_field([
        'name' => 'Video Section',
        'id'   => 'video_tab',
        'type' => 'title',
        'render_row_cb' => 'mayami_cmb2_tab_open',
        'tab' => 'video',
    ]);

    $cmb_options->add_field([
        'name'    => 'Kicker',
        'id'      => 'video_kicker',
        'type'    => 'text',
        'default' => 'Official',
    ]);

    $cmb_options->add_field([
        'name'    => 'Title',
        'id'      => 'video_title',
        'type'    => 'text',
        'default' => 'Music Video',
    ]);

    $cmb_options->add_field([
        'name'    => 'Description',
        'id'      => 'video_description',
        'type'    => 'textarea_small',
        'default' => 'Experience the visual journey through Miami.',
    ]);

    $cmb_options->add_field([
        'name'    => 'Status Text',
        'id'      => 'video_status',
        'type'    => 'text',
        'default' => 'Out Now',
    ]);

    $cmb_options->add_field([
        'name'    => 'Watch Button Label',
        'id'      => 'video_watch_label',
        'type'    => 'text',
        'default' => 'Watch Now',
    ]);

    $cmb_options->add_field([
        'name' => 'Cover Image',
        'id'   => 'video_cover_image',
        'type' => 'file',
    ]);
}