<?php

/**
 * Home Landing CMB2 configuration.
 *
 * Dedicated admin page and option set for the main Home Landing.
 *
 * @package Mayami
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('cmb2_admin_init', 'ellene_register_home_landing_options');
add_action('admin_init', 'ellene_initialize_home_landing_defaults');
add_action('admin_init', 'ellene_seed_home_landing_from_mayami_once', 20);

/**
 * Register Home Landing options page and fields.
 *
 * @return void
 */
function ellene_register_home_landing_options() {
    $option_key = ellene_get_home_landing_option_key();

    $cmb = new_cmb2_box(array(
        'id'           => 'ellene_home_landing_page',
        'title'        => 'Home Landing Local Settings',
        'object_types' => array('options-page'),
        'option_key'   => $option_key,
        'icon_url'     => 'dashicons-admin-home',
        'menu_title'   => 'Home Landing Local',
        'position'     => 3,
    ));

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
            'top-bar'  => 'Top-Bar',
            'header'   => 'Header',
            'hero'     => 'Hero',
            'contact'  => 'Contact',
            'releases' => 'Releases',
            'footer'   => 'Footer',
        ),
        'default' => array('top-bar', 'header', 'hero', 'contact', 'footer'),
        'desc'    => 'Home Landing: active les modules affiches sur la page principale.',
    ));

    $cmb->add_field(array(
        'name' => 'Top-Bar',
        'type' => 'title',
        'id'   => 'section_marquee_title',
    ));

    $cmb->add_field(array(
        'name' => 'Logo (gauche)',
        'id'   => 'home_topbar_logo_image',
        'type' => 'file',
        'options' => array(
            'url' => false,
        ),
        'text' => array(
            'add_upload_file_text' => 'Choisir logo',
        ),
    ));

    $home_topbar_stream_links = $cmb->add_field(array(
        'name'       => 'Icones Stream',
        'id'         => 'home_topbar_stream_links',
        'type'       => 'group',
        'repeatable' => true,
        'options'    => array(
            'group_title'   => 'Stream {#}',
            'add_button'    => 'Ajouter une icone stream',
            'remove_button' => 'Retirer',
            'sortable'      => true,
        ),
    ));

    $cmb->add_group_field($home_topbar_stream_links, array(
        'name' => 'Nom',
        'id'   => 'label',
        'type' => 'text',
    ));

    $cmb->add_group_field($home_topbar_stream_links, array(
        'name' => 'Lien',
        'id'   => 'href',
        'type' => 'text_url',
    ));

    $home_topbar_social_links = $cmb->add_field(array(
        'name'       => 'Icones Sociaux',
        'id'         => 'home_topbar_social_links',
        'type'       => 'group',
        'repeatable' => true,
        'options'    => array(
            'group_title'   => 'Social {#}',
            'add_button'    => 'Ajouter une icone sociale',
            'remove_button' => 'Retirer',
            'sortable'      => true,
        ),
    ));

    $cmb->add_group_field($home_topbar_social_links, array(
        'name' => 'Nom',
        'id'   => 'label',
        'type' => 'text',
    ));

    $cmb->add_group_field($home_topbar_social_links, array(
        'name' => 'Lien',
        'id'   => 'href',
        'type' => 'text_url',
    ));

    $cmb->add_field(array(
        'name'    => 'CTA (Releases) - Libelle',
        'id'      => 'home_topbar_releases_label',
        'type'    => 'text',
        'default' => 'RELEASES',
    ));

    $cmb->add_field(array(
        'name'    => 'CTA (Releases) - Lien',
        'id'      => 'home_topbar_releases_href',
        'type'    => 'text_url',
        'default' => ellene_get_mayami_landing_public_url(),
    ));

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
        'desc'    => "Colle l'URL du post TikTok, par exemple https://www.tiktok.com/@artist/video/1234567890123456789",
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

    $cmb->add_field(array(
        'name' => 'Contact',
        'type' => 'title',
        'id'   => 'section_contact_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Get in touch - Libelle',
        'id'      => 'home_contact_kicker',
        'type'    => 'text',
        'default' => 'Get in touch',
    ));

    $cmb->add_field(array(
        'name'    => 'Titre Contact - Ligne 1',
        'id'      => 'home_contact_title_line_1',
        'type'    => 'text',
        'default' => 'For bookings,',
    ));

    $cmb->add_field(array(
        'name'    => 'Titre Contact - Ligne 2',
        'id'      => 'home_contact_title_line_2',
        'type'    => 'text',
        'default' => 'collaborations',
    ));

    $cmb->add_field(array(
        'name'    => 'Titre Contact - Ligne 3',
        'id'      => 'home_contact_title_line_3',
        'type'    => 'text',
        'default' => '& press.',
    ));

    $cmb->add_field(array(
        'name'    => 'Intro',
        'id'      => 'home_contact_intro',
        'type'    => 'textarea_small',
        'default' => 'The full website is being shaped. In the meantime, reach out directly or follow the journey across platforms.',
    ));

    $cmb->add_field(array(
        'name'    => 'Email',
        'id'      => 'home_contact_email',
        'type'    => 'text_email',
        'default' => 'contact@ellenemasri.com',
    ));

    $home_contact_stream_links = $cmb->add_field(array(
        'name'       => 'Liens Stream',
        'id'         => 'home_contact_stream_links',
        'type'       => 'group',
        'repeatable' => true,
        'options'    => array(
            'group_title'   => 'Stream {#}',
            'add_button'    => 'Ajouter un lien stream',
            'remove_button' => 'Retirer',
            'sortable'      => true,
        ),
    ));

    $cmb->add_group_field($home_contact_stream_links, array(
        'name' => 'Nom',
        'id'   => 'label',
        'type' => 'text',
    ));

    $cmb->add_group_field($home_contact_stream_links, array(
        'name' => 'Lien',
        'id'   => 'href',
        'type' => 'text_url',
    ));

    $home_contact_social_links = $cmb->add_field(array(
        'name'       => 'Liens Sociaux',
        'id'         => 'home_contact_social_links',
        'type'       => 'group',
        'repeatable' => true,
        'options'    => array(
            'group_title'   => 'Social {#}',
            'add_button'    => 'Ajouter un lien social',
            'remove_button' => 'Retirer',
            'sortable'      => true,
        ),
    ));

    $cmb->add_group_field($home_contact_social_links, array(
        'name' => 'Nom',
        'id'   => 'label',
        'type' => 'text',
    ));

    $cmb->add_group_field($home_contact_social_links, array(
        'name' => 'Lien',
        'id'   => 'href',
        'type' => 'text_url',
    ));

    $cmb->add_field(array(
        'name' => 'Releases',
        'type' => 'title',
        'id'   => 'section_release_title',
    ));

    $home_releases_items = $cmb->add_field(array(
        'name'       => 'Items Releases',
        'id'         => 'home_releases_items',
        'type'       => 'group',
        'repeatable' => true,
        'options'    => array(
            'group_title'   => 'Release {#}',
            'add_button'    => 'Ajouter un release',
            'remove_button' => 'Retirer',
            'sortable'      => true,
        ),
    ));

    $cmb->add_group_field($home_releases_items, array(
        'name'    => 'Titre',
        'id'      => 'label',
        'type'    => 'text',
        'default' => 'Mayami Landing',
    ));

    $cmb->add_group_field($home_releases_items, array(
        'name'    => 'Lien',
        'id'      => 'href',
        'type'    => 'text_url',
        'default' => ellene_get_mayami_landing_public_url(),
    ));

    $cmb->add_field(array(
        'name' => 'Footer',
        'type' => 'title',
        'id'   => 'section_footer_title',
    ));

    $cmb->add_field(array(
        'name'    => 'Copyright (gauche)',
        'id'      => 'home_footer_copyright',
        'type'    => 'text',
        'default' => '© ' . gmdate('Y') . ' ELLENE MASRI',
    ));

    $cmb->add_field(array(
        'name'    => 'URL centrale - Texte',
        'id'      => 'home_footer_center_label',
        'type'    => 'text',
        'default' => 'ellenemasri.com',
    ));

    $cmb->add_field(array(
        'name'    => 'URL centrale - Lien',
        'id'      => 'home_footer_center_href',
        'type'    => 'text_url',
        'default' => home_url('/'),
    ));

    $cmb->add_field(array(
        'name'    => 'CTA droite - Libelle',
        'id'      => 'home_footer_releases_label',
        'type'    => 'text',
        'default' => 'Explore Releases ↗',
    ));

    $cmb->add_field(array(
        'name'    => 'CTA droite - Lien',
        'id'      => 'home_footer_releases_href',
        'type'    => 'text_url',
        'default' => ellene_get_mayami_landing_public_url(),
    ));
}

/**
 * Initialize default values for Home Landing once.
 *
 * @return void
 */
function ellene_initialize_home_landing_defaults() {
    $option_key = ellene_get_home_landing_option_key();
    $flag_key = 'ellene_home_landing_initialized';

    if (get_option($flag_key)) {
        return;
    }

    $options = get_option($option_key, array());
    if (!is_array($options)) {
        $options = array();
    }

    if (!isset($options['modules_enabled']) || !is_array($options['modules_enabled'])) {
        $options['modules_enabled'] = array('top-bar', 'header', 'hero', 'contact', 'footer');
    }

    if (!in_array('contact', $options['modules_enabled'], true)) {
        $options['modules_enabled'][] = 'contact';
    }

    $options['modules_enabled'] = array_values(array_filter($options['modules_enabled'], static function ($module) {
        return $module !== 'releases';
    }));

    if (empty($options['home_releases_items']) || !is_array($options['home_releases_items'])) {
        $options['home_releases_items'] = array(
            array(
                'label' => 'Mayami Landing',
                'href'  => ellene_get_mayami_landing_public_url(),
            ),
        );
    }

    update_option($option_key, $options, false);
    update_option($flag_key, '1', false);
}

/**
 * One-time seed: copy existing Mayami values into Home option table.
 *
 * Home then stays independent from Mayami.
 *
 * @return void
 */
function ellene_seed_home_landing_from_mayami_once() {
    $seed_flag_key = 'ellene_home_seeded_from_mayami_v1';
    if (get_option($seed_flag_key)) {
        return;
    }

    $source = get_option(ellene_get_landing_option_key(), array());
    $target_key = ellene_get_home_landing_option_key();
    $target = get_option($target_key, array());

    if (!is_array($source)) {
        $source = array();
    }
    if (!is_array($target)) {
        $target = array();
    }

    $copy_if_empty = static function (&$arr, $key, $value) {
        if (!array_key_exists($key, $arr) || $arr[$key] === '' || $arr[$key] === null || (is_array($arr[$key]) && empty($arr[$key]))) {
            $arr[$key] = $value;
        }
    };

    $hero_keys = array(
        'hero_top_artist',
        'hero_top_artist_hidden',
        'hero_top_cta_label',
        'hero_top_cta_hidden',
        'hero_top_cta_href',
        'hero_badge_text',
        'hero_badge_text_hidden',
        'hero_subtitle',
        'hero_subtitle_hidden',
        'hero_main_title',
        'hero_background_image',
        'hero_background_image_hidden',
        'hero_logo_image',
        'hero_logo_hidden',
        'hero_logo_alt',
        'hero_description',
        'hero_description_hidden',
        'hero_stream_label',
        'hero_stream_hidden',
        'hero_stream_href',
        'hero_watch_label',
        'hero_watch_hidden',
        'hero_watch_href',
        'hero_slider',
    );

    foreach ($hero_keys as $hero_key) {
        if (array_key_exists($hero_key, $source)) {
            $copy_if_empty($target, $hero_key, $source[$hero_key]);
        }
    }

    $stream_links = array();
    if (!empty($source['stream_platforms']) && is_array($source['stream_platforms'])) {
        foreach ($source['stream_platforms'] as $platform) {
            if (!is_array($platform) || empty($platform['is_active'])) {
                continue;
            }
            $label = trim((string) ($platform['label'] ?? ''));
            $href = trim((string) ($platform['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }
            $stream_links[] = array(
                'label' => $label,
                'href' => $href,
            );
        }
    }
    if (!empty($stream_links)) {
        $copy_if_empty($target, 'home_topbar_stream_links', $stream_links);
        $copy_if_empty($target, 'home_contact_stream_links', $stream_links);
    }

    $social_links = array();
    $social_candidates = array(
        array('label' => trim((string) ($source['social_tiktok_label'] ?? 'TikTok')), 'href' => trim((string) ($source['social_tiktok_link'] ?? ''))),
        array('label' => trim((string) ($source['social_instagram_label'] ?? 'Instagram')), 'href' => trim((string) ($source['social_instagram_link'] ?? ''))),
        array('label' => trim((string) ($source['social_youtube_label'] ?? 'YouTube')), 'href' => trim((string) ($source['social_youtube_link'] ?? ''))),
    );
    foreach ($social_candidates as $social_item) {
        if ($social_item['label'] === '' || $social_item['href'] === '') {
            continue;
        }
        $social_links[] = $social_item;
    }
    if (!empty($social_links)) {
        $copy_if_empty($target, 'home_topbar_social_links', $social_links);
        $copy_if_empty($target, 'home_contact_social_links', $social_links);
    }

    if (!empty($source['marquee_logo_png'])) {
        $copy_if_empty($target, 'home_topbar_logo_image', trim((string) $source['marquee_logo_png']));
    }

    if (!isset($target['modules_enabled']) || !is_array($target['modules_enabled'])) {
        $target['modules_enabled'] = array('top-bar', 'header', 'hero', 'contact', 'footer');
    }
    if (!in_array('contact', $target['modules_enabled'], true)) {
        $target['modules_enabled'][] = 'contact';
    }
    $target['modules_enabled'] = array_values(array_filter($target['modules_enabled'], static function ($module) {
        return $module !== 'releases';
    }));

    update_option($target_key, $target, false);
    update_option($seed_flag_key, '1', false);
}
