<?php

/**
 * Social section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_social_section_fields($cmb_options) {
    // ============================================
    // TAB: SOCIAL SECTION
    // ============================================

    $cmb_options->add_field([
        'name' => 'Social Section',
        'id'   => 'social_tab',
        'type' => 'title',
        'render_row_cb' => 'mayami_cmb2_tab_open',
        'tab' => 'social',
    ]);

    $cmb_options->add_field([
        'name'    => 'Kicker',
        'id'      => 'social_kicker',
        'type'    => 'text',
        'default' => 'Follow',
    ]);

    $cmb_options->add_field([
        'name'    => 'Title Left',
        'id'      => 'social_title_left',
        'type'    => 'text',
        'default' => 'The journey on',
    ]);

    $cmb_options->add_field([
        'name'    => 'Title Right',
        'id'      => 'social_title_right',
        'type'    => 'text',
        'default' => 'social',
    ]);

    $cmb_options->add_field([
        'name'    => 'Description',
        'id'      => 'social_description',
        'type'    => 'textarea_small',
        'default' => 'From studio sessions to city streets. Follow the story behind ellene-wp.',
    ]);
}