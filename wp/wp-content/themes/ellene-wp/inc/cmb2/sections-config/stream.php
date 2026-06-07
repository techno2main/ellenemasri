<?php

/**
 * Stream section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_cmb2_stream_section($cmb) {
    // ========== SECTION: STREAM ==========

    $cmb->add_field(array(
        'name' => 'Stream',
        'type' => 'title',
        'id'   => 'section_stream_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Kicker',
        'id'      => 'stream_kicker',
        'type'    => 'text',
        'default' => '01 / Listen',
    ));

    $cmb->add_field(array(
        'name'    => 'Title Prefix',
        'id'      => 'stream_title_prefix',
        'type'    => 'text',
        'default' => 'Stream',
    ));

    $cmb->add_field(array(
        'name'    => 'Title Logo',
        'id'      => 'stream_title_highlight',
        'type'    => 'file',
        'desc'    => 'Logo image affiche a droite de "Stream" dans la section front.',
    ));

    $cmb->add_field(array(
        'name'    => 'Availability Text',
        'id'      => 'stream_availability_text',
        'type'    => 'text',
        'default' => 'Available everywhere',
    ));

    $cmb->add_field(array(
        'name'    => 'Card Label',
        'id'      => 'stream_card_label',
        'type'    => 'text',
        'default' => 'Listen on',
    ));

    $stream_platforms = $cmb->add_field(array(
        'id'      => 'stream_platforms',
        'type'    => 'group',
        'options' => array(
            'group_title'   => 'Plateforme {#}',
            'add_button'    => '+ Ajouter une plateforme',
            'remove_button' => 'Supprimer',
            'sortable'      => true,
        ),
    ));

    $cmb->add_group_field($stream_platforms, array(
        'name'    => 'Active',
        'id'      => 'is_active',
        'type'    => 'checkbox',
        'default' => 'on',
    ));

    $cmb->add_group_field($stream_platforms, array(
        'name' => 'Nom',
        'id'   => 'label',
        'type' => 'text',
    ));

    $cmb->add_group_field($stream_platforms, array(
        'name' => 'Lien',
        'id'   => 'href',
        'type' => 'text_url',
    ));
}