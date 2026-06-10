<?php

/**
 * Module settings for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_register_cmb2_modules_section($cmb) {
    // ========== SECTION: MODULES ===========

    $cmb->add_field(array(
        'name' => 'Modules',
        'type' => 'title',
        'id'   => 'section_modules_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Modules actifs',
        'id'      => 'modules_enabled',
        'type'    => 'multicheck_inline',
        'select_all_button' => false,
        'options' => array(
            'top-bar'      => 'Top-Bar',
            'hero'         => 'Hero',
            'stream'       => 'Stream',
            'social'       => 'Social',
            'video'        => 'Video',
            'release'      => 'Release',
            'cta'          => 'CTA',
            'footer'       => 'Footer',
        ),
        'default' => array('top-bar', 'hero', 'stream', 'social', 'video', 'release', 'cta', 'footer'),
        'desc' => 'Affiche/Masque les modules',
    ));

    $cmb->add_field(array(
        'name' => 'Ordre des rubriques',
        'id'   => 'modules_order',
        'type' => 'text',
        'desc' => 'Modifie l\'ordre en séparant par une virgule',
    ));

    $cmb->add_field(array(
        'id'   => 'modules_slots_migrated',
        'type' => 'hidden',
    ));
}