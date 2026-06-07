<?php

/**
 * Module settings for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_cmb2_modules_section($cmb) {
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
        'options' => array(
            'top-bar'      => 'Top-Bar',
            'header'       => 'Header',
            'hero'         => 'Hero',
            'stream'       => 'Stream',
            'social'       => 'Social',
            'video'        => 'Video',
            'release-info' => 'Release Info',
            'cta'          => 'CTA',
            'footer'       => 'Footer',
        ),
        'default' => array('top-bar', 'header', 'hero', 'stream', 'social', 'video', 'release-info', 'cta', 'footer'),
        'desc' => 'Coche/decoche les blocs du front (Top-Bar, Header, Hero, sections content, Footer).',
    ));

    $cmb->add_field(array(
        'name' => 'Ordre des modules',
        'id'   => 'modules_order',
        'type' => 'text',
        'desc' => 'Ordre des modules (assistant visuel disponible sous le champ). Glissez les modules actifs, le champ se met a jour automatiquement.',
    ));

    $cmb->add_field(array(
        'id'   => 'modules_slots_migrated',
        'type' => 'hidden',
    ));

    $cmb->add_field(array(
        'name'    => 'Modules mutualises',
        'id'      => 'modules_shared',
        'type'    => 'multicheck_inline',
        'options' => array(
            'stream' => 'Stream',
            'social' => 'Social',
            'video' => 'Video',
            'cta' => 'CTA',
        ),
        'desc' => 'Active le mode source partagee (mutualise) pour les modules compatibles.',
    ));
}