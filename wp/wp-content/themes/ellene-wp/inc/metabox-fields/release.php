<?php

/**
 * Release section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_release_section_fields($cmb_options) {
    // ============================================
    // TAB: RELEASE INFO SECTION
    // ============================================

    $cmb_options->add_field([
        'name' => 'Release Info Section',
        'id'   => 'release_tab',
        'type' => 'title',
        'render_row_cb' => 'mayami_cmb2_tab_open',
        'tab' => 'release',
    ]);

    $cmb_options->add_field([
        'name'    => 'Kicker',
        'id'      => 'release_kicker',
        'type'    => 'text',
        'default' => 'Release',
    ]);

    $cmb_options->add_field([
        'name'    => 'Title Left',
        'id'      => 'release_title_left',
        'type'    => 'text',
        'default' => 'About',
    ]);

    $cmb_options->add_field([
        'name'    => 'Title Highlight',
        'id'      => 'release_title_highlight',
        'type'    => 'text',
        'default' => 'ELLENE-WP',
    ]);

    $cmb_options->add_field([
        'name' => 'Cover Image',
        'id'   => 'release_cover_image',
        'type' => 'file',
    ]);

    $release_group = $cmb_options->add_field([
        'id'          => 'release_rows',
        'type'        => 'group',
        'description' => 'Lignes d\'information (Label : Valeur)',
        'options'     => [
            'group_title'   => 'Info {#}',
            'add_button'    => 'Ajouter une ligne',
            'remove_button' => 'Supprimer',
            'sortable'      => true,
        ],
    ]);

    $cmb_options->add_group_field($release_group, [
        'name' => 'Label',
        'id'   => 'key',
        'type' => 'text',
    ]);

    $cmb_options->add_group_field($release_group, [
        'name' => 'Valeur',
        'id'   => 'value',
        'type' => 'text',
    ]);
}