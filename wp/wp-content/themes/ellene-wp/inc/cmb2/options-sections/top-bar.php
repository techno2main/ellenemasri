<?php

/**
 * Top bar fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_register_cmb2_top_bar_section($cmb) {
    // ========== SECTION: TOP-BAR ==========

    $cmb->add_field(array(
        'name' => 'Top-Bar',
        'type' => 'title',
        'id'   => 'section_top_bar_title',
    ));

    $cmb->add_field(array(
        'name' => 'Logo TOP-BAR',
        'id'   => 'top_bar_logo_png',
        'type' => 'file',
        'text' => array(
            'add_upload_file_text' => 'Modifier',
        ),
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'top_bar_logo_hidden',
        'type' => 'checkbox',
    ));

    $top_bar_group = $cmb->add_field(array(
        'id'      => 'top_bar_items',
        'type'    => 'group',
        'options' => array(
            'group_title'   => 'Item {#}',
            'add_button'    => '+ Ajouter',
            'remove_button' => 'Supprimer',
            'sortable'      => true,
        ),
    ));

    $cmb->add_group_field($top_bar_group, array(
        'name' => 'Label',
        'id'   => 'label',
        'type' => 'text',
    ));

    $cmb->add_group_field($top_bar_group, array(
        'name' => 'Lien',
        'id'   => 'href',
        'type' => 'text_url',
    ));

    $cmb->add_group_field($top_bar_group, array(
        'name' => 'Masquer',
        'id'   => 'is_hidden',
        'type' => 'checkbox',
    ));
}