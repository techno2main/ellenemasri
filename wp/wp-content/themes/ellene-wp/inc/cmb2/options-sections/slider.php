<?php

/**
 * Slider section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_register_cmb2_slider_section($cmb) {
    // ========== SECTION: SLIDER ==========

    $cmb->add_field(array(
        'name' => 'Slider',
        'type' => 'title',
        'id'   => 'section_slider_title',
    ));

    $slider_group = $cmb->add_field(array(
        'id'      => 'hero_slider',
        'type'    => 'group',
        'options' => array(
            'group_title'   => 'Slide {#}',
            'add_button'    => '+ Ajouter un slide',
            'remove_button' => 'Supprimer',
            'sortable'      => true,
        ),
    ));

    $cmb->add_group_field($slider_group, array(
        'name' => 'Nom du slide',
        'id'   => 'slide_admin_title',
        'type' => 'text',
    ));

    $cmb->add_group_field($slider_group, array(
        'name'    => 'Type',
        'id'      => 'slide_type',
        'type'    => 'select',
        'options' => array(
            'image' => 'Image',
            'video' => 'Vidéo YouTube',
            'tiktok' => 'Vidéo TikTok',
        ),
    ));

    $cmb->add_group_field($slider_group, array(
        'name' => 'Image',
        'id'   => 'slide_image',
        'type' => 'file',
    ));

    $cmb->add_group_field($slider_group, array(
        'name' => 'URL YouTube',
        'id'   => 'video_url',
        'type' => 'text_url',
    ));

    $cmb->add_group_field($slider_group, array(
        'name'    => 'URL TikTok',
        'id'      => 'tiktok_url',
        'type'    => 'text_url',
        'desc'    => 'Colle l URL du post TikTok, par exemple https://www.tiktok.com/@artist/video/1234567890123456789',
        'visible' => array('slide_type', '=', 'tiktok'),
    ));

    $cmb->add_group_field($slider_group, array(
        'name'    => 'Vidéo MP4 TikTok (médiathèque)',
        'id'      => 'tiktok_video_url',
        'type'    => 'file',
        'desc'    => 'Choisis un fichier MP4 déjà uploadé dans la médiathèque pour un rendu plein écran sans chrome ni vidéos similaires. L’embed officiel reste en fallback si ce champ est vide.',
        'visible' => array('slide_type', '=', 'tiktok'),
    ));

    $cmb->add_group_field($slider_group, array(
        'name' => 'Texte Alt',
        'id'   => 'alt_text',
        'type' => 'text',
    ));

    $cmb->add_group_field($slider_group, array(
        'name'       => 'Durée du slide (secondes)',
        'id'         => 'slide_duration',
        'type'       => 'text_small',
        'default'    => '5',
        'attributes' => array(
            'type' => 'number',
            'min'  => '1',
            'step' => '1',
        ),
        'desc'       => 'Durée avant passage au slide suivant.',
    ));
}