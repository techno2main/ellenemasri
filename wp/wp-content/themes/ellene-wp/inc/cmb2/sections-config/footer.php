<?php

/**
 * Footer and sticky bar fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_cmb2_footer_section($cmb) {
    // ========== SECTION: FOOTER & STICKY BAR ==========

    $cmb->add_field(array(
        'name' => 'Footer',
        'type' => 'title',
        'id'   => 'section_footer_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Footer Line 1',
        'id'      => 'footer_line1',
        'type'    => 'text',
        'default' => '© Your Artist Name',
    ));

    $cmb->add_field(array(
        'name'    => 'Footer Line 2',
        'id'      => 'footer_line2',
        'type'    => 'text',
        'default' => 'Your project tagline.',
    ));

    $cmb->add_field(array(
        'name'    => 'Sticky Bar (Mobile) - Stream',
        'id'      => 'sticky_stream_label',
        'type'    => 'text',
        'default' => '▶ Stream',
    ));

    $cmb->add_field(array(
        'name'    => 'Sticky Bar (Mobile) - Video',
        'id'      => 'sticky_video_label',
        'type'    => 'text',
        'default' => '◉ Video',
    ));

    $cmb->add_field(array(
        'name'    => 'Sticky Bar (Mobile) - TikTok',
        'id'      => 'sticky_tiktok_label',
        'type'    => 'text',
        'default' => 'TikTok',
    ));

    $cmb->add_field(array(
        'name' => 'Sticky Bar (Mobile) - TikTok Link',
        'id'   => 'sticky_tiktok_link',
        'type' => 'text_url',
    ));
}