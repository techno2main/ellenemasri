<?php

/**
 * Top bar fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_top_bar_section_fields($cmb_options) {
    // ============================================
    // TAB: TOP BAR
    // ============================================

    $cmb_options->add_field([
        'name' => 'Top Bar',
        'id'   => 'marquee_tab',
        'type' => 'title',
        'render_row_cb' => 'mayami_cmb2_tab_open',
        'tab' => 'marquee',
    ]);

    $marquee_group = $cmb_options->add_field([
        'id'          => 'marquee_items',
        'type'        => 'group',
        'description' => 'Items défilants du top-bar en haut de page',
        'options'     => [
            'group_title'   => 'Item {#}',
            'add_button'    => 'Ajouter un item',
            'remove_button' => 'Supprimer',
            'sortable'      => true,
        ],
    ]);

    $cmb_options->add_group_field($marquee_group, [
        'name' => 'Label',
        'id'   => 'label',
        'type' => 'text',
    ]);

    $cmb_options->add_group_field($marquee_group, [
        'name' => 'Lien (URL)',
        'id'   => 'href',
        'type' => 'text_url',
    ]);

    $cmb_options->add_group_field($marquee_group, [
        'name' => 'Lien externe',
        'id'   => 'external',
        'type' => 'checkbox',
        'desc' => 'Ouvrir dans un nouvel onglet',
    ]);
}