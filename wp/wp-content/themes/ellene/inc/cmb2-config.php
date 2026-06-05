<?php

/**

 * CMB2 Configuration - Page unique avec navigation sticky

 */



if (!defined('ABSPATH')) exit;



add_action('cmb2_admin_init', 'mayami_register_options');

add_action('admin_init', 'mayami_initialize_default_content');

// Platform link sync disabled to keep links fully admin-driven.

// Hero top-artist sync disabled to keep hero values fully admin-driven.

// Marquee play-link sync disabled to avoid automatic link rewrites.

// Stream sync disabled to keep stream URLs fully admin-driven and avoid re-injection.

// Follow YouTube sync disabled to avoid hardcoded profile URL writes.

// Marquee/social/sticky legacy migrations disabled to keep options fully admin-driven.

// Stream force/sync hooks disabled to avoid hardcoded stream URL writes.

add_action('admin_head', 'mayami_sticky_save_button');



/**

 * Add sticky save button at the top of CMB2 admin page

 */

function mayami_sticky_save_button() {

    $screen = get_current_screen();

    if ($screen && $screen->id === ellene_get_landing_admin_hook_suffix()) {

        ?>

        <style>

            .wrap > h1,

            .wrap > h1.wp-heading-inline {

                display: none !important;

            }

            #mayami-save-button-sticky {

                background: #fff !important;

                color: #6b21a8 !important;

                border: 2px solid #fff !important;

                padding: 8px 20px !important;

                font-size: 13px !important;

                font-weight: 700 !important;

                border-radius: 6px !important;

                cursor: pointer !important;

                transition: all 0.2s !important;

                text-transform: uppercase !important;

                letter-spacing: 0.5px !important;

                margin-left: auto !important;

            }

            #mayami-save-button-sticky:hover {

                background: #f0f0f1 !important;

                transform: translateY(-1px) !important;

                box-shadow: 0 3px 8px rgba(0,0,0,0.2) !important;

            }

            

            

        </style>

        <script>

            jQuery(document).ready(function($) {

                // Attendre que la navbar soit créée par CMB2

                setTimeout(function() {

                    var $navbar = $('.cmb-tabs-nav, .cmb2-wrap > nav, [class*="cmb"] nav, .cmb-tabs');

                    

                    if (!$navbar.length) {

                        // Chercher toute div qui contient les boutons de navigation

                        $navbar = $('div').filter(function() {

                            return $(this).css('background-color') === 'rgb(107, 33, 168)' || 

                                   $(this).css('background-color').includes('107, 33, 168');

                        });

                    }

                    

                    if ($navbar.length) {

                        var $saveButton = $('<button type="button" id="mayami-save-button-sticky">💾 Save</button>');

                        

                        $navbar.append($saveButton);

                        

                        $saveButton.on('click', function(e) {

                            e.preventDefault();

                            var $realButton = $('.cmb-form input[type="submit"], .cmb2-wrap input[type="submit"]').first();

                            if ($realButton.length) {

                                $realButton.trigger('click');

                            }

                        });

                    } else {

                        console.log('Navbar violette non trouvée');

                    }

                }, 500);

            });

        </script>

        <?php

    }

}



/**

 * Initialize default content for groups (slider, marquee, release rows)

 */

function mayami_initialize_default_content() {

    $option_key = ellene_get_landing_option_key();

    

    // Check if already initialized (or if reset is requested)

    $reset_requested = isset($_GET['mayami_reset']) && $_GET['mayami_reset'] === '1';

    

    if (!$reset_requested && get_option('mayami_content_initialized')) {

        return;

    }

    

    $theme_url = get_template_directory_uri();

    

    // Default slider content

    $default_slider = array(

        array(

            'slide_admin_title' => 'Slide 1',

            'slide_type' => 'image',

            'slide_image' => $theme_url . '/assets/mayami-artist.jpg',

            'alt_text' => 'Artist portrait 1',

            'slide_duration' => '5',

        ),

        array(

            'slide_admin_title' => 'Slide 2',

            'slide_type' => 'image',

            'slide_image' => $theme_url . '/assets/mayami-cover.jpg',

            'alt_text' => 'Artist cover image',

            'slide_duration' => '5',

        ),

        array(

            'slide_admin_title' => 'Slide 3',

            'slide_type' => 'image',

            'slide_image' => $theme_url . '/assets/mayami-artist.jpg',

            'alt_text' => 'Artist portrait 2',

            'slide_duration' => '5',

        ),

    );

    

    // Default release rows

    $default_release_rows = array(

        array('key' => 'Artists', 'value' => 'Artist Name'),

        array('key' => 'Title', 'value' => 'Release Title'),

        array('key' => 'Release date', 'value' => 'TBD'),

        array('key' => 'Location', 'value' => 'City, Country'),

        array('key' => 'Video', 'value' => 'Coming soon'),

    );

    

    // Get current options

    $options = get_option($option_key, array());

    

    // Initialize groups if empty

    if (empty($options['hero_slider'])) {

        $options['hero_slider'] = $default_slider;

    }

    

    if (empty($options['release_rows'])) {

        $options['release_rows'] = $default_release_rows;

    }



    if (empty($options['marquee_play_link']) && !empty($options['link_spotify'])) {

        $options['marquee_play_link'] = $options['link_spotify'];

    }



    if (!isset($options['marquee_show_music_icon'])) {

        $options['marquee_show_music_icon'] = 'on';

    }



    if (!isset($options['marquee_logo_hidden'])) {

        $options['marquee_logo_hidden'] = '';

    }



    // Set default images

    if (empty($options['video_cover_image'])) {

        $options['video_cover_image'] = $theme_url . '/assets/mayami-cover.jpg';

    }

    

    if (empty($options['release_cover_image'])) {

        $options['release_cover_image'] = $theme_url . '/assets/mayami-cover.jpg';

    }

    

    if (empty($options['cta_texture_image'])) {

        $options['cta_texture_image'] = $theme_url . '/assets/mayami-texture.jpg';

    }



    // Set text defaults to keep front labels admin-driven.

    if (empty($options['social_tiktok_label'])) {

        $options['social_tiktok_label'] = 'TikTok';

    }

    if (empty($options['social_tiktok_badge'])) {

        $options['social_tiktok_badge'] = 'Follow';

    }

    if (empty($options['social_instagram_label'])) {

        $options['social_instagram_label'] = 'Instagram';

    }

    if (empty($options['social_instagram_badge'])) {

        $options['social_instagram_badge'] = 'Follow';

    }

    if (empty($options['social_youtube_label'])) {

        $options['social_youtube_label'] = 'YouTube';

    }

    if (empty($options['social_youtube_badge'])) {

        $options['social_youtube_badge'] = 'Watch';

    }



    if (empty($options['cta_stream_label'])) {

        $options['cta_stream_label'] = 'Stream';

    }

    if (empty($options['cta_video_label'])) {

        $options['cta_video_label'] = 'Watch';

    }

    if (empty($options['cta_tiktok_label'])) {

        $options['cta_tiktok_label'] = 'TikTok';

    }

    if (empty($options['cta_instagram_label'])) {

        $options['cta_instagram_label'] = 'Instagram';

    }

    

    // Save options

    update_option($option_key, $options);

    

    // Mark as initialized

    update_option('mayami_content_initialized', true);

}



/**

 * One-time sync for marquee play icon link based on Spotify link.

 */

function mayami_sync_marquee_play_link_once() {

    $sync_flag = 'mayami_marquee_play_link_synced_20260529';

    if (get_option($sync_flag)) {

        return;

    }



    $option_key = ellene_get_landing_option_key();

    $options = get_option($option_key, array());

    if (!is_array($options)) {

        update_option($sync_flag, true);

        return;

    }



    $changed = false;



    $spotify_link = isset($options['link_spotify']) ? trim((string) $options['link_spotify']) : '';

    $marquee_play_link = isset($options['marquee_play_link']) ? trim((string) $options['marquee_play_link']) : '';



    if ($marquee_play_link === '' && $spotify_link !== '') {

        $options['marquee_play_link'] = $spotify_link;

        $changed = true;

    }



    if (!isset($options['marquee_show_music_icon'])) {

        $options['marquee_show_music_icon'] = 'on';

        $changed = true;

    }



    if ($changed) {

        update_option($option_key, $options);

    }



    update_option($sync_flag, true);

}



/**

 * Ensure TOP-BAR has required management items and clean legacy icon-toggle items.

 */

function mayami_sync_marquee_items_once() {

     $sync_flag = 'mayami_marquee_items_synced_20260530_v5';

    if (get_option($sync_flag)) {

        return;

    }



    $option_key = ellene_get_landing_option_key();

    $options = get_option($option_key, array());

    if (!is_array($options)) {

        update_option($sync_flag, true);

        return;

    }



    $items = isset($options['marquee_items']) && is_array($options['marquee_items']) ? $options['marquee_items'] : array();

    $clean_items = array();



    $changed = false;



    if (array_key_exists('marquee_show_stream_icons', $options)) {

        unset($options['marquee_show_stream_icons']);

        $changed = true;

    }



    if (array_key_exists('marquee_stream_icons_new_tab', $options)) {

        unset($options['marquee_stream_icons_new_tab']);

        $changed = true;

    }



    foreach ($items as $item) {

        if (!is_array($item)) {

            continue;

        }



        $label = strtolower(trim(remove_accents((string) ($item['label'] ?? ''))));

        if ($label === 'afficher les icones' || $label === 'icones stream' || $label === 'icone plateformes') {

            $changed = true;

            continue;

        }



        if (!empty($item['external'])) {

            $item['external'] = '';

            $changed = true;

        }



        $clean_items[] = $item;

    }



    if ($changed) {

        $options['marquee_items'] = $clean_items;

        update_option($option_key, $options);

    }



    update_option($sync_flag, true);

}



function mayami_register_options() {

    

    $option_key = ellene_get_landing_option_key();

    

    // PAGE UNIQUE

    $cmb = new_cmb2_box(array(

        'id'           => 'mayami_main_page',

        'title'        => 'Mayami Landing Local Settings',

        'object_types' => array('options-page'),

        'option_key'   => $option_key,

        'icon_url'     => 'dashicons-admin-site-alt3',

        'menu_title'   => 'Mayami Landing Local',

        'position'     => 2,

    ));



    // ========== SECTION: MODULES ==========

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

        'desc' => 'Coche/décoche les blocs du front (Top-Bar, Header, Hero, sections content, Footer).',

    ));



    $cmb->add_field(array(

        'name' => 'Ordre des modules',

        'id'   => 'modules_order',

        'type' => 'text',

        'desc' => 'Ordre des modules (assistant visuel disponible sous le champ). Glissez les modules actifs, le champ se met à jour automatiquement.',

    ));



    $cmb->add_field(array(

        'id'   => 'modules_slots_migrated',

        'type' => 'hidden',

    ));



    $cmb->add_field(array(

        'name'    => 'Modules mutualisés',

        'id'      => 'modules_shared',

        'type'    => 'multicheck_inline',

        'options' => array(

            'stream' => 'Stream',

            'social' => 'Social',

            'video'  => 'Video',

            'cta'    => 'CTA',

        ),

        'desc' => 'Active le mode source partagée (mutualisée) pour les modules compatibles.',

    ));



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

        'default' => 'on',

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



    $cmb->add_field(array(

        'name' => 'Stream partagé (mode mutualisé)',

        'type' => 'title',

        'id'   => 'section_stream_shared_title',

    ));



    $cmb->add_field(array(

        'name' => 'Activer la source partagée Stream',

        'id'   => 'shared_stream_enabled',

        'type' => 'checkbox',

        'desc' => 'Quand activé : Stream lit les champs partagés ci-dessous. Quand désactivé : Stream reste 100% local.',

    ));



    $cmb->add_field(array(

        'name'    => 'Kicker partagé',

        'id'      => 'shared_stream_kicker',

        'type'    => 'text',

    ));



    $cmb->add_field(array(

        'name'    => 'Title Prefix partagé',

        'id'      => 'shared_stream_title_prefix',

        'type'    => 'text',

    ));



    $cmb->add_field(array(

        'name'    => 'Title Logo partagé',

        'id'      => 'shared_stream_title_highlight',

        'type'    => 'file',

        'desc'    => 'Source partagée utilisée quand le module Stream est mutualisé.',

    ));



    $cmb->add_field(array(

        'name'    => 'Availability Text partagé',

        'id'      => 'shared_stream_availability_text',

        'type'    => 'text',

    ));



    $cmb->add_field(array(

        'name'    => 'Card Label partagé',

        'id'      => 'shared_stream_card_label',

        'type'    => 'text',

    ));



    $shared_stream_platforms = $cmb->add_field(array(

        'id'      => 'shared_stream_platforms',

        'type'    => 'group',

        'options' => array(

            'group_title'   => 'Plateforme partagée {#}',

            'add_button'    => '+ Ajouter une plateforme partagée',

            'remove_button' => 'Supprimer',

            'sortable'      => true,

        ),

    ));



    $cmb->add_group_field($shared_stream_platforms, array(

        'name'    => 'Active',

        'id'      => 'is_active',

        'type'    => 'checkbox',

        'default' => 'on',

    ));



    $cmb->add_group_field($shared_stream_platforms, array(

        'name' => 'Nom',

        'id'   => 'label',

        'type' => 'text',

    ));



    $cmb->add_group_field($shared_stream_platforms, array(

        'name' => 'Lien',

        'id'   => 'href',

        'type' => 'text_url',

    ));



    // ========== SECTION: SOCIAL ==========

    $cmb->add_field(array(

        'name' => 'Social',

        'type' => 'title',

        'id'   => 'section_social_title',

    ));



    $cmb->add_field(array(

        'name'    => 'Kicker',

        'id'      => 'social_kicker',

        'type'    => 'text',

        'default' => '02 / Follow',

    ));



    $cmb->add_field(array(

        'name'    => 'Title Left',

        'id'      => 'social_title_left',

        'type'    => 'text',

        'default' => 'Join the',

    ));



    $cmb->add_field(array(

        'name'    => 'Title Right',

        'id'      => 'social_title_right',

        'type'    => 'text',

        'default' => 'journey',

    ));



    $cmb->add_field(array(

        'name'    => 'Description',

        'id'      => 'social_description',

        'type'    => 'textarea_small',

        'default' => 'Share clips, updates, and behind-the-scenes moments.',

    ));



    $cmb->add_field(array(

        'name' => 'TikTok Link',

        'id'   => 'social_tiktok_link',

        'type' => 'text_url',

    ));



    $cmb->add_field(array(

        'name'    => 'TikTok Label',

        'id'      => 'social_tiktok_label',

        'type'    => 'text',

        'default' => 'TikTok',

    ));



    $cmb->add_field(array(

        'name'    => 'TikTok Badge',

        'id'      => 'social_tiktok_badge',

        'type'    => 'text',

        'default' => 'Follow',

    ));



    $cmb->add_field(array(

        'name' => 'Instagram Link',

        'id'   => 'social_instagram_link',

        'type' => 'text_url',

    ));



    $cmb->add_field(array(

        'name'    => 'Instagram Label',

        'id'      => 'social_instagram_label',

        'type'    => 'text',

        'default' => 'Instagram',

    ));



    $cmb->add_field(array(

        'name'    => 'Instagram Badge',

        'id'      => 'social_instagram_badge',

        'type'    => 'text',

        'default' => 'Follow',

    ));



    $cmb->add_field(array(

        'name' => 'YouTube Link',

        'id'   => 'social_youtube_link',

        'type' => 'text_url',

    ));



    $cmb->add_field(array(

        'name'    => 'YouTube Label',

        'id'      => 'social_youtube_label',

        'type'    => 'text',

        'default' => 'YouTube',

    ));



    $cmb->add_field(array(

        'name'    => 'YouTube Badge',

        'id'      => 'social_youtube_badge',

        'type'    => 'text',

        'default' => 'Watch',

    ));



    // ========== SECTION: VIDEO ==========

    $cmb->add_field(array(

        'name' => 'Video',

        'type' => 'title',

        'id'   => 'section_video_title',

    ));



    $cmb->add_field(array(

        'name'    => 'Kicker',

        'id'      => 'video_kicker',

        'type'    => 'text',

        'default' => '03 / Watch',

    ));



    $cmb->add_field(array(

        'name'    => 'Title',

        'id'      => 'video_title',

        'type'    => 'text',

        'default' => 'Official Video',

    ));



    $cmb->add_field(array(

        'name'    => 'Description',

        'id'      => 'video_description',

        'type'    => 'textarea_small',

        'default' => 'Describe the official video for this release.',

    ));



    $cmb->add_field(array(

        'name'    => 'Status Text',

        'id'      => 'video_status',

        'type'    => 'text',

        'default' => 'Coming soon',

    ));



    $cmb->add_field(array(

        'name'    => 'Watch Button Label',

        'id'      => 'video_watch_label',

        'type'    => 'text',

        'default' => 'Watch',

    ));



    $cmb->add_field(array(

        'name'    => 'Watch Button Link',

        'id'      => 'video_watch_href',

        'type'    => 'text_url',

        'default' => '',

    ));



    $cmb->add_field(array(

        'name'    => 'Disable Watch Link',

        'id'      => 'video_watch_disable_link',

        'type'    => 'checkbox',

        'default' => '',

    ));



    $cmb->add_field(array(

        'name' => 'Cover Image',

        'id'   => 'video_cover_image',

        'type' => 'file',

    ));



    // ========== SECTION: RELEASE INFO ==========

    $cmb->add_field(array(

        'name' => 'Release',

        'type' => 'title',

        'id'   => 'section_release_title',

    ));



    $cmb->add_field(array(

        'name'    => 'Kicker',

        'id'      => 'release_kicker',

        'type'    => 'text',

        'default' => '04 / Release Info',

    ));



    $cmb->add_field(array(

        'name'    => 'Title Left',

        'id'      => 'release_title_left',

        'type'    => 'text',

        'default' => 'The',

    ));



    $cmb->add_field(array(

        'name'    => 'Title Highlight',

        'id'      => 'release_title_highlight',

        'type'    => 'text',

        'default' => 'credits',

    ));



    $cmb->add_field(array(

        'name' => 'Cover Image',

        'id'   => 'release_cover_image',

        'type' => 'file',

    ));



    $release_rows = $cmb->add_field(array(

        'id'      => 'release_rows',

        'type'    => 'group',

        'options' => array(

            'group_title'   => 'Info {#}',

            'add_button'    => '+ Ajouter',

            'remove_button' => 'Supprimer',

            'sortable'      => true,

        ),

    ));



    $cmb->add_group_field($release_rows, array(

        'name' => 'Label',

        'id'   => 'key',

        'type' => 'text',

    ));



    $cmb->add_group_field($release_rows, array(

        'name' => 'Valeur',

        'id'   => 'value',

        'type' => 'text',

    ));



    // ========== SECTION: CTA ==========

    $cmb->add_field(array(

        'name' => 'CTA',

        'type' => 'title',

        'id'   => 'section_cta_title',

    ));



    $cmb->add_field(array(

        'name'    => 'Kicker',

        'id'      => 'cta_kicker',

        'type'    => 'text',

        'default' => '05 / Call To Action',

    ));



    $cmb->add_field(array(

        'name'    => 'Title Left',

        'id'      => 'cta_title_left',

        'type'    => 'text',

        'default' => 'Press',

    ));



    $cmb->add_field(array(

        'name'    => 'Title Right',

        'id'      => 'cta_title_right',

        'type'    => 'text',

        'default' => 'play.',

    ));



    $cmb->add_field(array(

        'name'    => 'Description',

        'id'      => 'cta_description',

        'type'    => 'textarea_small',

        'default' => 'Invite your audience to stream, watch, and share.',

    ));



    $cmb->add_field(array(

        'name'    => 'Hashtag',

        'id'      => 'cta_hashtag',

        'type'    => 'text',

        'default' => '#YourHashtag',

    ));



    $cmb->add_field(array(

        'name'    => 'Stream Button Label',

        'id'      => 'cta_stream_label',

        'type'    => 'text',

        'default' => 'Stream',

    ));



    $cmb->add_field(array(

        'name'    => 'Stream Button Link',

        'id'      => 'cta_stream_link',

        'type'    => 'text',

        'default' => '#stream',

    ));



    $cmb->add_field(array(

        'name'    => 'Video Button Label',

        'id'      => 'cta_video_label',

        'type'    => 'text',

        'default' => 'Watch',

    ));



    $cmb->add_field(array(

        'name'    => 'Video Button Link',

        'id'      => 'cta_video_link',

        'type'    => 'text',

        'default' => '#video',

    ));



    $cmb->add_field(array(

        'name'    => 'TikTok Button Label',

        'id'      => 'cta_tiktok_label',

        'type'    => 'text',

        'default' => 'TikTok',

    ));



    $cmb->add_field(array(

        'name'    => 'TikTok Button Link',

        'id'      => 'cta_tiktok_link',

        'type'    => 'text_url',

        'default' => '',

    ));



    $cmb->add_field(array(

        'name'    => 'Instagram Button Label',

        'id'      => 'cta_instagram_label',

        'type'    => 'text',

        'default' => 'Instagram',

    ));



    $cmb->add_field(array(

        'name'    => 'Instagram Button Link',

        'id'      => 'cta_instagram_link',

        'type'    => 'text_url',

        'default' => '',

    ));



    $cmb->add_field(array(

        'name' => 'Texture Image',

        'id'   => 'cta_texture_image',

        'type' => 'file',

    ));



    // ========== SECTION: FOOTER & STICKY BAR ==========

    $cmb->add_field(array(

        'name' => 'Footer',

        'type' => 'title',

        'id'   => 'section_footer_title',

    ));



    $cmb->add_field(array(

        'name'    => 'Footer Line 1',

        'id'      => 'footer_line1',

        'type'    => 'text',

        'default' => '© Your Artist Name',

    ));



    $cmb->add_field(array(

        'name'    => 'Footer Line 2',

        'id'      => 'footer_line2',

        'type'    => 'text',

        'default' => 'Your project tagline.',

    ));



    $cmb->add_field(array(

        'name'    => 'Sticky Bar (Mobile) - Stream',

        'id'      => 'sticky_stream_label',

        'type'    => 'text',

        'default' => '▶ Stream',

    ));



    $cmb->add_field(array(

        'name'    => 'Sticky Bar (Mobile) - Video',

        'id'      => 'sticky_video_label',

        'type'    => 'text',

        'default' => '◉ Video',

    ));



    $cmb->add_field(array(

        'name'    => 'Sticky Bar (Mobile) - TikTok',

        'id'      => 'sticky_tiktok_label',

        'type'    => 'text',

        'default' => 'TikTok',

    ));



    $cmb->add_field(array(

        'name' => 'Sticky Bar (Mobile) - TikTok Link',

        'id'   => 'sticky_tiktok_link',

        'type' => 'text_url',

    ));



    // ========== SECTION: MARQUEE ==========

    $cmb->add_field(array(

        'name' => 'Top-Bar',

        'type' => 'title',

        'id'   => 'section_marquee_title',

    ));

    $marquee_group = $cmb->add_field(array(

        'id'      => 'marquee_items',

        'type'    => 'group',

        'options' => array(

            'group_title'   => 'Item {#}',

            'add_button'    => '+ Ajouter',

            'remove_button' => 'Supprimer',

            'sortable'      => true,

        ),

    ));



    $cmb->add_group_field($marquee_group, array(

        'name' => 'Label',

        'id'   => 'label',

        'type' => 'text',

    ));



    $cmb->add_group_field($marquee_group, array(

        'name' => 'Lien',

        'id'   => 'href',

        'type' => 'text_url',

    ));



    $cmb->add_group_field($marquee_group, array(

        'name' => 'Masquer',

        'id'   => 'is_hidden',

        'type' => 'checkbox',

    ));



    $cmb->add_field(array(

        'name' => 'Visuel TOP-BAR',

        'id'   => 'marquee_logo_png',

        'type' => 'file',

        'text' => array(

            'add_upload_file_text' => 'Modifier',

        ),

    ));



    $cmb->add_field(array(

        'name' => 'Masquer',

        'id'   => 'marquee_logo_hidden',

        'type' => 'checkbox',

    ));



}


