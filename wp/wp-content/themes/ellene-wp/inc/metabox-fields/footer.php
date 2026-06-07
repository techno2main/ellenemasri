<?php

/**
 * Footer and sticky bar fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_footer_section_fields($cmb_options) {
    // ============================================
    // TAB: FOOTER & STICKY BAR
    // ============================================

    $cmb_options->add_field([
        'name' => 'Footer & Sticky Bar',
        'id'   => 'footer_tab',
        'type' => 'title',
        'render_row_cb' => 'mayami_cmb2_tab_open',
        'tab' => 'footer',
    ]);

    $cmb_options->add_field([
        'name'    => 'Footer Line 1',
        'id'      => 'footer_line1',
        'type'    => 'text',
        'default' => '© 2026 ellene-wp',
    ]);

    $cmb_options->add_field([
        'name'    => 'Footer Line 2',
        'id'      => 'footer_line2',
        'type'    => 'text',
        'default' => 'All rights reserved',
    ]);

    $cmb_options->add_field([
        'name'    => 'Sticky Bar (Mobile) - Stream Label',
        'id'      => 'sticky_stream_label',
        'type'    => 'text',
        'default' => 'Stream',
    ]);

    $cmb_options->add_field([
        'name'    => 'Sticky Bar (Mobile) - Video Label',
        'id'      => 'sticky_video_label',
        'type'    => 'text',
        'default' => 'Video',
    ]);

    $cmb_options->add_field([
        'name'    => 'Sticky Bar (Mobile) - TikTok Label',
        'id'      => 'sticky_tiktok_label',
        'type'    => 'text',
        'default' => 'TikTok',
    ]);

    $cmb_options->add_field([
        'name' => 'Sticky Bar (Mobile) - TikTok Link',
        'id'   => 'sticky_tiktok_link',
        'type' => 'text_url',
    ]);
}