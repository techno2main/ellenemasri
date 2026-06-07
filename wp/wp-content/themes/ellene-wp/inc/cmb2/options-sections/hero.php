<?php

/**
 * Hero section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_register_cmb2_hero_section($cmb) {
    // ========== SECTION: HERO ==========

    $cmb->add_field(array(
        'name' => 'Hero',
        'type' => 'title',
        'id'   => 'section_hero_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Top Artist',
        'id'      => 'hero_top_artist',
        'type'    => 'text',
        'default' => 'Artist Name',
        'row_classes' => 'cmb-field-with-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'hero_top_artist_hidden',
        'type' => 'checkbox',
        'row_classes' => 'cmb-inline-toggle',
    ));

    $cmb->add_field(array(
        'name'    => 'Top CTA Label',
        'id'      => 'hero_top_cta_label',
        'type'    => 'text',
        'default' => 'Out now',
        'row_classes' => 'cmb-field-with-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'hero_top_cta_hidden',
        'type' => 'checkbox',
        'row_classes' => 'cmb-inline-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Top CTA Link',
        'id'   => 'hero_top_cta_href',
        'type' => 'text_url',
        'row_classes' => 'cmb-field-with-toggle',
    ));

    $cmb->add_field(array(
        'name'    => 'Badge Text',
        'id'      => 'hero_badge_text',
        'type'    => 'text',
        'default' => 'New Release',
        'row_classes' => 'cmb-field-with-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'hero_badge_text_hidden',
        'type' => 'checkbox',
        'row_classes' => 'cmb-inline-toggle',
    ));

    $cmb->add_field(array(
        'name'    => 'Subtitle',
        'id'      => 'hero_subtitle',
        'type'    => 'text',
        'default' => 'Release Subtitle',
        'row_classes' => 'cmb-field-with-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'hero_subtitle_hidden',
        'type' => 'checkbox',
        'row_classes' => 'cmb-inline-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Main Title (SEO)',
        'id'   => 'hero_main_title',
        'type' => 'text',
    ));

    $cmb->add_field(array(
        'name' => 'Background Image',
        'id'   => 'hero_background_image',
        'type' => 'file',
        'row_classes' => 'cmb-field-with-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'hero_background_image_hidden',
        'type' => 'checkbox',
        'row_classes' => 'cmb-inline-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Main Logo Image',
        'id'   => 'hero_logo_image',
        'type' => 'file',
        'row_classes' => 'cmb-field-with-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'hero_logo_hidden',
        'type' => 'checkbox',
        'row_classes' => 'cmb-inline-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Main Logo Alt Text',
        'id'   => 'hero_logo_alt',
        'type' => 'text',
    ));

    $cmb->add_field(array(
        'name'    => 'Description',
        'id'      => 'hero_description',
        'type'    => 'textarea_small',
        'default' => 'Present the release and invite visitors to stream, watch, and share.',
        'row_classes' => 'cmb-field-with-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'hero_description_hidden',
        'type' => 'checkbox',
        'row_classes' => 'cmb-inline-toggle',
    ));

    $cmb->add_field(array(
        'name'    => 'Stream Button - Label',
        'id'      => 'hero_stream_label',
        'type'    => 'text',
        'default' => '◉ Stream',
        'row_classes' => 'cmb-field-with-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'hero_stream_hidden',
        'type' => 'checkbox',
        'row_classes' => 'cmb-inline-toggle',
    ));

    $cmb->add_field(array(
        'name'    => 'Stream Button - Link',
        'id'      => 'hero_stream_href',
        'type'    => 'text_url',
        'default' => '#stream',
    ));

    $cmb->add_field(array(
        'name'    => 'Watch Button - Label',
        'id'      => 'hero_watch_label',
        'type'    => 'text',
        'default' => '▶ Watch',
        'row_classes' => 'cmb-field-with-toggle',
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'hero_watch_hidden',
        'type' => 'checkbox',
        'row_classes' => 'cmb-inline-toggle',
    ));

    $cmb->add_field(array(
        'name'    => 'Watch Button - Link',
        'id'      => 'hero_watch_href',
        'type'    => 'text',
        'default' => '#video',
    ));
}