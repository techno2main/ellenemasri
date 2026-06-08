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
        'name' => 'Modules / Rubriques',
        'type' => 'title',
        'id'   => 'section_modules_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Modules actifs',
        'id'      => 'modules_enabled',
        'type'    => 'multicheck_inline',
        'options' => array(
            'top-bar'      => 'Top-Bar',
            'hero'         => 'Hero',
            'stream'       => 'Stream',
            'social'       => 'Social',
            'video'        => 'Video',
            'release' => 'Release',
            'cta'          => 'CTA',
            'footer'       => 'Footer',
        ),
        'default' => array('top-bar', 'hero', 'stream', 'social', 'video', 'release', 'cta', 'footer'),
        'desc' => 'Décoche un module pour le masquer sur le site (le module ne sera pas supprimé, tu pourras le réactiver à tout moment).',
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
}