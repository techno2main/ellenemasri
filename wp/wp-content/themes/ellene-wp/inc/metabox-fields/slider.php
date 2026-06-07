<?php

/**
 * Slider section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_slider_section_fields($cmb_options) {
    // ============================================
    // TAB: HERO SLIDER
    // ============================================

    $cmb_options->add_field([
        'name' => 'Hero Slider',
        'id'   => 'slider_tab',
        'type' => 'title',
        'render_row_cb' => 'mayami_cmb2_tab_open',
        'tab' => 'slider',
    ]);

    $slider_group = $cmb_options->add_field([
        'id'          => 'hero_slider',
        'type'        => 'group',
        'description' => 'Slides du Hero (images ou vidéos YouTube)',
        'options'     => [
            'group_title'   => 'Slide {#}',
            'add_button'    => 'Ajouter un slide',
            'remove_button' => 'Supprimer',
            'sortable'      => true,
        ],
    ]);

    $cmb_options->add_group_field($slider_group, [
        'name'    => 'Type de slide',
        'id'      => 'slide_type',
        'type'    => 'select',
        'default' => 'image',
        'options' => [
            'image' => 'Image',
            'video' => 'Vidéo (YouTube)',
        ],
    ]);

    $cmb_options->add_group_field($slider_group, [
        'name' => 'Image',
        'id'   => 'slide_image',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'text' => [
            'add_upload_file_text' => 'Ajouter une image',
        ],
        'attributes' => [
            'data-conditional-id'    => 'slide_type',
            'data-conditional-value' => 'image',
        ],
    ]);

    $cmb_options->add_group_field($slider_group, [
        'name' => 'URL YouTube',
        'id'   => 'video_url',
        'type' => 'text_url',
        'desc' => 'URL complète de la vidéo YouTube',
        'attributes' => [
            'data-conditional-id'    => 'slide_type',
            'data-conditional-value' => 'video',
        ],
    ]);

    $cmb_options->add_group_field($slider_group, [
        'name' => 'Miniature vidéo (optionnel)',
        'id'   => 'thumbnail_url',
        'type' => 'file',
        'desc' => 'Si vide, sera auto-généré depuis YouTube',
        'attributes' => [
            'data-conditional-id'    => 'slide_type',
            'data-conditional-value' => 'video',
        ],
    ]);

    $cmb_options->add_group_field($slider_group, [
        'name' => 'Texte alternatif (alt)',
        'id'   => 'alt_text',
        'type' => 'text',
    ]);
}