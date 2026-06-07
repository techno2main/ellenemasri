<?php

/**
 * Release section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_register_cmb2_release_section($cmb) {
    // ========== SECTION: RELEASE INFO ==========

    $cmb->add_field(array(
        'name' => 'Release',
        'type' => 'title',
        'id'   => 'section_release_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Kicker',
        'id'      => 'release_kicker',
        'type'    => 'text',
        'default' => '04 / Release Info',
    ));

    $cmb->add_field(array(
        'name'    => 'Title Left',
        'id'      => 'release_title_left',
        'type'    => 'text',
        'default' => 'The',
    ));

    $cmb->add_field(array(
        'name'    => 'Title Highlight',
        'id'      => 'release_title_highlight',
        'type'    => 'text',
        'default' => 'credits',
    ));

    $cmb->add_field(array(
        'name' => 'Cover Image',
        'id'   => 'release_cover_image',
        'type' => 'file',
    ));

    $release_rows = $cmb->add_field(array(
        'id'      => 'release_rows',
        'type'    => 'group',
        'options' => array(
            'group_title'   => 'Info {#}',
            'add_button'    => '+ Ajouter',
            'remove_button' => 'Supprimer',
            'sortable'      => true,
        ),
    ));

    $cmb->add_group_field($release_rows, array(
        'name' => 'Label',
        'id'   => 'key',
        'type' => 'text',
    ));

    $cmb->add_group_field($release_rows, array(
        'name' => 'Valeur',
        'id'   => 'value',
        'type' => 'text',
    ));
}