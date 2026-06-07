<?php

/**
 * CTA section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_cta_section_fields($cmb_options) {
    // ============================================
    // TAB: CTA SECTION
    // ============================================

    $cmb_options->add_field([
        'name' => 'CTA Section',
        'id'   => 'cta_tab',
        'type' => 'title',
        'render_row_cb' => 'mayami_cmb2_tab_open',
        'tab' => 'cta',
    ]);

    $cmb_options->add_field([
        'name'    => 'Kicker',
        'id'      => 'cta_kicker',
        'type'    => 'text',
        'default' => 'Join',
    ]);

    $cmb_options->add_field([
        'name'    => 'Title Left',
        'id'      => 'cta_title_left',
        'type'    => 'text',
        'default' => 'Share your',
    ]);

    $cmb_options->add_field([
        'name'    => 'Title Right',
        'id'      => 'cta_title_right',
        'type'    => 'text',
        'default' => 'ELLENE-WP',
    ]);

    $cmb_options->add_field([
        'name'    => 'Description',
        'id'      => 'cta_description',
        'type'    => 'textarea_small',
        'default' => 'Share your Miami moments with the hashtag.',
    ]);

    $cmb_options->add_field([
        'name'    => 'Hashtag',
        'id'      => 'cta_hashtag',
        'type'    => 'text',
        'default' => '#ELLENEWP',
    ]);

    $cmb_options->add_field([
        'name' => 'Texture Image',
        'id'   => 'cta_texture_image',
        'type' => 'file',
    ]);
}