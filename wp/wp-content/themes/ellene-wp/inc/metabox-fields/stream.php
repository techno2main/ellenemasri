<?php

/**
 * Stream section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_stream_section_fields($cmb_options) {
    // ============================================
    // TAB: STREAM SECTION
    // ============================================

    $cmb_options->add_field([
        'name' => 'Stream Section',
        'id'   => 'stream_tab',
        'type' => 'title',
        'render_row_cb' => 'mayami_cmb2_tab_open',
        'tab' => 'stream',
    ]);

    $cmb_options->add_field([
        'name'    => 'Kicker',
        'id'      => 'stream_kicker',
        'type'    => 'text',
        'default' => 'Now Live',
    ]);

    $cmb_options->add_field([
        'name'    => 'Title Prefix',
        'id'      => 'stream_title_prefix',
        'type'    => 'text',
        'default' => 'Listen to',
    ]);

    $cmb_options->add_field([
        'name'    => 'Title Logo',
        'id'      => 'stream_title_highlight',
        'type'    => 'file',
    ]);

    $cmb_options->add_field([
        'name'    => 'Availability Text',
        'id'      => 'stream_availability',
        'type'    => 'text',
        'default' => 'Available on all platforms',
    ]);

    $cmb_options->add_field([
        'name'    => 'Card Label',
        'id'      => 'stream_card_label',
        'type'    => 'text',
        'default' => 'Stream',
    ]);
}