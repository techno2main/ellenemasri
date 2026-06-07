<?php

/**
 * Stream section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_register_cmb2_stream_section($cmb) {
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

/**
 * Ensure group checkboxes keep an explicit value in options.
 *
 * Without this, unchecked checkboxes can be omitted from POST data,
 * and CMB2 may render them as checked again due to field defaults.
 *
 * @param mixed $override_value Override value from CMB2 sanitize filter.
 * @param mixed $value Raw submitted value.
 * @return mixed
 */
function ellene_wp_sanitize_stream_platforms_group($override_value, $value, $object_id, $field_args) {
    if (!is_array($field_args) || ($field_args['id'] ?? '') !== 'stream_platforms') {
        return $override_value;
    }

    if (!is_array($value)) {
        return $override_value;
    }

    foreach ($value as $index => $platform) {
        if (!is_array($platform)) {
            continue;
        }

        $platform['is_active'] = !empty($platform['is_active']) ? 'on' : '';
        $value[$index] = $platform;
    }

    return $value;
}

add_filter('cmb2_sanitize_group', 'ellene_wp_sanitize_stream_platforms_group', 10, 4);