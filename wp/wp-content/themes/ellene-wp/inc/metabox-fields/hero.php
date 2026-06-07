<?php

/**
 * Hero section fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_hero_section_fields($cmb_options) {
    // ============================================
    // TAB: HERO SECTION
    // ============================================

    $cmb_options->add_field([
        'name' => 'Hero Section',
        'id'   => 'hero_tab',
        'type' => 'title',
        'render_row_cb' => 'mayami_cmb2_tab_open',
        'tab' => 'hero',
    ]);

    $cmb_options->add_field([
        'name'    => 'Top Artist',
        'id'      => 'hero_top_artist',
        'type'    => 'text',
        'default' => 'Ellene Leya Masri',
    ]);

    $cmb_options->add_field([
        'name'    => 'Top CTA Label',
        'id'      => 'hero_top_cta_label',
        'type'    => 'text',
        'default' => 'Out tomorrow',
    ]);

    $cmb_options->add_field([
        'name' => 'Top CTA Link',
        'id'   => 'hero_top_cta_href',
        'type' => 'text_url',
    ]);

    $cmb_options->add_field([
        'name'    => 'Badge Text',
        'id'      => 'hero_badge_text',
        'type'    => 'text',
        'default' => 'New Single · Out Tomorrow',
    ]);

    $cmb_options->add_field([
        'name'    => 'Subtitle',
        'id'      => 'hero_subtitle',
        'type'    => 'text',
        'default' => 'ellene-wp',
    ]);

    $cmb_options->add_field([
        'name' => 'Main Title (SEO)',
        'id'   => 'hero_main_title',
        'type' => 'text',
    ]);

    $cmb_options->add_field([
        'name' => 'Background Image',
        'id'   => 'hero_background_image',
        'type' => 'file',
    ]);

    $cmb_options->add_field([
        'name' => 'Main Logo Image',
        'id'   => 'hero_logo_image',
        'type' => 'file',
    ]);

    $cmb_options->add_field([
        'name' => 'Main Logo Alt Text',
        'id'   => 'hero_logo_alt',
        'type' => 'text',
    ]);

    $cmb_options->add_field([
        'name'    => 'Description',
        'id'      => 'hero_description',
        'type'    => 'textarea_small',
        'default' => 'A sunset-soaked love letter to the city. Stream it, watch it, share it — and follow the journey from the painted walls of Miami.',
    ]);

    $cmb_options->add_field([
        'name'    => 'Stream Button Label',
        'id'      => 'hero_stream_label',
        'type'    => 'text',
        'default' => '◉ Stream',
    ]);

    $cmb_options->add_field([
        'name'    => 'Stream Button Link',
        'id'      => 'hero_stream_href',
        'type'    => 'text_url',
        'default' => 'https://ffm.to/mayami',
        'protocols' => ['http', 'https'],
    ]);

    $cmb_options->add_field([
        'name'    => 'Watch Button Label',
        'id'      => 'hero_watch_label',
        'type'    => 'text',
        'default' => '▶ Watch',
    ]);

    $cmb_options->add_field([
        'name'    => 'Watch Button Link',
        'id'      => 'hero_watch_href',
        'type'    => 'text_url',
        'default' => '#video',
    ]);
}