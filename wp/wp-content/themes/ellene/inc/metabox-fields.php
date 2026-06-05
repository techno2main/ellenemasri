<?php

/**

 * CMB2 Fields Configuration for Mayami Landing Page

 * Professional solution - 100% Free & Robust

 * 

 * @package Mayami

 */



// Prevent direct access

if (!defined('ABSPATH')) {

    exit;

}



/**

 * Register CMB2 Options Page

 */

add_action('cmb2_admin_init', 'mayami_register_options_page');

function mayami_register_options_page() {

    

    // ============================================

    // OPTIONS PAGE PRINCIPALE

    // ============================================

    $cmb_options = new_cmb2_box([

        'id'           => 'mayami_options_page',

        'title'        => 'Mayami Landing Local Settings',

        'object_types' => ['options-page'],

        'option_key'   => 'mayami_options',

        'icon_url'     => 'dashicons-admin-site-alt3',

        'position'     => 2,

        'menu_title'   => 'Mayami Landing Local',

        'save_button'  => 'Enregistrer',

    ]);



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

        'default' => 'Mayami, My Miami',

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



    // ============================================

    // TAB: STREAM SECTION

    // ============================================

    $cmb_options->add_field([

        'name' => 'Stream Section',

        'id'   => 'stream_tab',

        'type' => 'title',

        'render_row_cb' => 'mayami_cmb2_tab_open',

        'tab' => 'stream',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Kicker',

        'id'      => 'stream_kicker',

        'type'    => 'text',

        'default' => 'Now Live',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Title Prefix',

        'id'      => 'stream_title_prefix',

        'type'    => 'text',

        'default' => 'Listen to',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Title Logo',

        'id'      => 'stream_title_highlight',

        'type'    => 'file',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Availability Text',

        'id'      => 'stream_availability',

        'type'    => 'text',

        'default' => 'Available on all platforms',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Card Label',

        'id'      => 'stream_card_label',

        'type'    => 'text',

        'default' => 'Stream',

    ]);



    // ============================================

    // TAB: SOCIAL SECTION

    // ============================================

    $cmb_options->add_field([

        'name' => 'Social Section',

        'id'   => 'social_tab',

        'type' => 'title',

        'render_row_cb' => 'mayami_cmb2_tab_open',

        'tab' => 'social',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Kicker',

        'id'      => 'social_kicker',

        'type'    => 'text',

        'default' => 'Follow',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Title Left',

        'id'      => 'social_title_left',

        'type'    => 'text',

        'default' => 'The journey on',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Title Right',

        'id'      => 'social_title_right',

        'type'    => 'text',

        'default' => 'social',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Description',

        'id'      => 'social_description',

        'type'    => 'textarea_small',

        'default' => 'From studio sessions to city streets. Follow the story behind Mayami.',

    ]);

    

    // ============================================

    // TAB: VIDEO SECTION

    // ============================================

    $cmb_options->add_field([

        'name' => 'Video Section',

        'id'   => 'video_tab',

        'type' => 'title',

        'render_row_cb' => 'mayami_cmb2_tab_open',

        'tab' => 'video',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Kicker',

        'id'      => 'video_kicker',

        'type'    => 'text',

        'default' => 'Official',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Title',

        'id'      => 'video_title',

        'type'    => 'text',

        'default' => 'Music Video',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Description',

        'id'      => 'video_description',

        'type'    => 'textarea_small',

        'default' => 'Experience the visual journey through Miami.',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Status Text',

        'id'      => 'video_status',

        'type'    => 'text',

        'default' => 'Out Now',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Watch Button Label',

        'id'      => 'video_watch_label',

        'type'    => 'text',

        'default' => 'Watch Now',

    ]);

    

    $cmb_options->add_field([

        'name' => 'Cover Image',

        'id'   => 'video_cover_image',

        'type' => 'file',

    ]);



    // ============================================

    // TAB: RELEASE INFO SECTION

    // ============================================

    $cmb_options->add_field([

        'name' => 'Release Info Section',

        'id'   => 'release_tab',

        'type' => 'title',

        'render_row_cb' => 'mayami_cmb2_tab_open',

        'tab' => 'release',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Kicker',

        'id'      => 'release_kicker',

        'type'    => 'text',

        'default' => 'Release',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Title Left',

        'id'      => 'release_title_left',

        'type'    => 'text',

        'default' => 'About',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Title Highlight',

        'id'      => 'release_title_highlight',

        'type'    => 'text',

        'default' => 'MAYAMI',

    ]);

    

    $cmb_options->add_field([

        'name' => 'Cover Image',

        'id'   => 'release_cover_image',

        'type' => 'file',

    ]);

    

    $release_group = $cmb_options->add_field([

        'id'          => 'release_rows',

        'type'        => 'group',

        'description' => 'Lignes d\'information (Label : Valeur)',

        'options'     => [

            'group_title'   => 'Info {#}',

            'add_button'    => 'Ajouter une ligne',

            'remove_button' => 'Supprimer',

            'sortable'      => true,

        ],

    ]);

    

    $cmb_options->add_group_field($release_group, [

        'name' => 'Label',

        'id'   => 'key',

        'type' => 'text',

    ]);

    

    $cmb_options->add_group_field($release_group, [

        'name' => 'Valeur',

        'id'   => 'value',

        'type' => 'text',

    ]);



    // ============================================

    // TAB: CTA SECTION

    // ============================================

    $cmb_options->add_field([

        'name' => 'CTA Section',

        'id'   => 'cta_tab',

        'type' => 'title',

        'render_row_cb' => 'mayami_cmb2_tab_open',

        'tab' => 'cta',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Kicker',

        'id'      => 'cta_kicker',

        'type'    => 'text',

        'default' => 'Join',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Title Left',

        'id'      => 'cta_title_left',

        'type'    => 'text',

        'default' => 'Share your',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Title Right',

        'id'      => 'cta_title_right',

        'type'    => 'text',

        'default' => 'MAYAMI',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Description',

        'id'      => 'cta_description',

        'type'    => 'textarea_small',

        'default' => 'Share your Miami moments with the hashtag.',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Hashtag',

        'id'      => 'cta_hashtag',

        'type'    => 'text',

        'default' => '#MAYAMI',

    ]);

    

    $cmb_options->add_field([

        'name' => 'Texture Image',

        'id'   => 'cta_texture_image',

        'type' => 'file',

    ]);



    // ============================================

    // TAB: FOOTER & STICKY BAR

    // ============================================

    $cmb_options->add_field([

        'name' => 'Footer & Sticky Bar',

        'id'   => 'footer_tab',

        'type' => 'title',

        'render_row_cb' => 'mayami_cmb2_tab_open',

        'tab' => 'footer',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Footer Line 1',

        'id'      => 'footer_line1',

        'type'    => 'text',

        'default' => '© 2026 Mayami',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Footer Line 2',

        'id'      => 'footer_line2',

        'type'    => 'text',

        'default' => 'All rights reserved',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Sticky Bar (Mobile) - Stream Label',

        'id'      => 'sticky_stream_label',

        'type'    => 'text',

        'default' => 'Stream',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Sticky Bar (Mobile) - Video Label',

        'id'      => 'sticky_video_label',

        'type'    => 'text',

        'default' => 'Video',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'Sticky Bar (Mobile) - TikTok Label',

        'id'      => 'sticky_tiktok_label',

        'type'    => 'text',

        'default' => 'TikTok',

    ]);



    $cmb_options->add_field([

        'name' => 'Sticky Bar (Mobile) - TikTok Link',

        'id'   => 'sticky_tiktok_link',

        'type' => 'text_url',

    ]);



    // ============================================

    // TAB: PLATFORM LINKS

    // ============================================

    $cmb_options->add_field([

        'name' => 'Platform Links',

        'id'   => 'links_tab',

        'type' => 'title',

        'render_row_cb' => 'mayami_cmb2_tab_open',

        'tab' => 'links',

    ]);

    

    $cmb_options->add_field([

        'name'    => 'FFM.to (Fan Link)',

        'id'      => 'link_ffm',

        'type'    => 'text_url',

        'default' => 'https://ffm.to/mayami',

    ]);

    

    $cmb_options->add_field([

        'name' => 'Spotify',

        'id'   => 'link_spotify',

        'type' => 'text_url',

    ]);

    

    $cmb_options->add_field([

        'name' => 'Apple Music',

        'id'   => 'link_apple',

        'type' => 'text_url',

    ]);

    

    $cmb_options->add_field([

        'name' => 'YouTube Music',

        'id'   => 'link_youtube_music',

        'type' => 'text_url',

    ]);

    

    $cmb_options->add_field([

        'name' => 'Deezer',

        'id'   => 'link_deezer',

        'type' => 'text_url',

    ]);

    

    $cmb_options->add_field([

        'name' => 'Amazon Music',

        'id'   => 'link_amazon',

        'type' => 'text_url',

    ]);

    

    $cmb_options->add_field([

        'name' => 'SoundCloud',

        'id'   => 'link_soundcloud',

        'type' => 'text_url',

    ]);

    

    $cmb_options->add_field([

        'name' => 'YouTube Video',

        'id'   => 'link_youtube_video',

        'type' => 'text_url',

    ]);

    

    $cmb_options->add_field([

        'name' => 'TikTok',

        'id'   => 'link_tiktok',

        'type' => 'text_url',

    ]);

    

    $cmb_options->add_field([

        'name' => 'Instagram',

        'id'   => 'link_instagram',

        'type' => 'text_url',

    ]);



    // ============================================

    // TAB: MARQUEE (Top Bar)

    // ============================================

    $cmb_options->add_field([

        'name' => 'Marquee (Top Sticky Bar)',

        'id'   => 'marquee_tab',

        'type' => 'title',

        'render_row_cb' => 'mayami_cmb2_tab_open',

        'tab' => 'marquee',

    ]);

    

    $marquee_group = $cmb_options->add_field([

        'id'          => 'marquee_items',

        'type'        => 'group',

        'description' => 'Items défilants du marquee en haut de page',

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



/**

 * Helper function to render tabs (simplified)

 */

function mayami_cmb2_tab_open($field_args, $field) {

    // CMB2 tabs will be handled via CSS/JS or can use CMB2 Tabs extension

    // For now, just render the title

    if ($field->args('type') === 'title') {

        echo '<h2>' . esc_html($field->args('name')) . '</h2>';

    }

}



/**

 * Register Meta Box Fields

 */

add_filter('rwmb_meta_boxes', 'mayami_register_meta_boxes');

function mayami_register_meta_boxes($meta_boxes) {

    

    // ============================================

    // HERO SECTION

    // ============================================

    $meta_boxes[] = [

        'title'      => 'Hero Section',

        'id'         => 'mayami-hero',

        'context'    => 'normal',

        'fields'     => [

            [

                'name' => 'Top Artist',

                'id'   => 'hero_top_artist',

                'type' => 'text',

                'std'  => 'Ellene Leya Masri',

            ],

            [

                'name' => 'Top CTA Label',

                'id'   => 'hero_top_cta_label',

                'type' => 'text',

                'std'  => 'Out tomorrow',

            ],

            [

                'name' => 'Top CTA Link',

                'id'   => 'hero_top_cta_href',

                'type' => 'url',

            ],

            [

                'name' => 'Badge Text',

                'id'   => 'hero_badge_text',

                'type' => 'text',

                'std'  => 'New Single · Out Tomorrow',

            ],

            [

                'name' => 'Subtitle',

                'id'   => 'hero_subtitle',

                'type' => 'text',

                'std'  => 'Mayami, My Miami',

            ],

            [

                'name' => 'Main Title (SEO)',

                'id'   => 'hero_main_title',

                'type' => 'text',

            ],

            [

                'name' => 'Background Image',

                'id'   => 'hero_background_image',

                'type' => 'image_advanced',

            ],

            [

                'name' => 'Main Logo Image',

                'id'   => 'hero_logo_image',

                'type' => 'image_advanced',

            ],

            [

                'name' => 'Main Logo Alt Text',

                'id'   => 'hero_logo_alt',

                'type' => 'text',

            ],

            [

                'name' => 'Description',

                'id'   => 'hero_description',

                'type' => 'textarea',

                'std'  => 'A sunset-soaked love letter to the city. Stream it, watch it, share it — and follow the journey from the painted walls of Miami.',

            ],

            [

                'name' => 'Stream Button Label',

                'id'   => 'hero_stream_label',

                'type' => 'text',

                'std'  => '◉ Stream',

            ],

            [

                'name' => 'Stream Button Link',

                'id'   => 'hero_stream_href',

                'type' => 'url',

                'std'  => 'https://ffm.to/mayami',

            ],

            [

                'name' => 'Watch Button Label',

                'id'   => 'hero_watch_label',

                'type' => 'text',

                'std'  => '▶ Watch',

            ],

            [

                'name' => 'Watch Button Link',

                'id'   => 'hero_watch_href',

                'type' => 'url',

                'std'  => '#video',

            ],

        ],

    ];



    // ============================================

    // STREAM SECTION

    // ============================================

    $meta_boxes[] = [

        'title'      => 'Stream Section',

        'id'         => 'mayami-stream',

        'settings_pages' => 'mayami-settings',

        'tab'        => 'stream',

        'fields'     => [

            [

                'name' => 'Kicker',

                'id'   => 'stream_kicker',

                'type' => 'text',

                'std'  => 'Now Live',

            ],

            [

                'name' => 'Title Prefix',

                'id'   => 'stream_title_prefix',

                'type' => 'text',

                'std'  => 'Listen to',

            ],

            [

                'name' => 'Title Logo',

                'id'   => 'stream_title_highlight',

                'type' => 'image_advanced',

            ],

            [

                'name' => 'Availability Text',

                'id'   => 'stream_availability',

                'type' => 'text',

                'std'  => 'Available on all platforms',

            ],

            [

                'name' => 'Card Label',

                'id'   => 'stream_card_label',

                'type' => 'text',

                'std'  => 'Stream',

            ],

        ],

    ];



    // ============================================

    // SOCIAL SECTION

    // ============================================

    $meta_boxes[] = [

        'title'      => 'Social Section',

        'id'         => 'mayami-social',

        'settings_pages' => 'mayami-settings',

        'tab'        => 'social',

        'fields'     => [

            [

                'name' => 'Kicker',

                'id'   => 'social_kicker',

                'type' => 'text',

                'std'  => 'Follow',

            ],

            [

                'name' => 'Title Left',

                'id'   => 'social_title_left',

                'type' => 'text',

                'std'  => 'The journey on',

            ],

            [

                'name' => 'Title Right',

                'id'   => 'social_title_right',

                'type' => 'text',

                'std'  => 'social',

            ],

            [

                'name' => 'Description',

                'id'   => 'social_description',

                'type' => 'textarea',

                'std'  => 'From studio sessions to city streets. Follow the story behind Mayami.',

            ],

        ],

    ];



    // ============================================

    // VIDEO SECTION

    // ============================================

    $meta_boxes[] = [

        'title'      => 'Video Section',

        'id'         => 'mayami-video',

        'settings_pages' => 'mayami-settings',

        'tab'        => 'video',

        'fields'     => [

            [

                'name' => 'Kicker',

                'id'   => 'video_kicker',

                'type' => 'text',

                'std'  => 'Official',

            ],

            [

                'name' => 'Title',

                'id'   => 'video_title',

                'type' => 'text',

                'std'  => 'Music Video',

            ],

            [

                'name' => 'Description',

                'id'   => 'video_description',

                'type' => 'textarea',

                'std'  => 'Experience the visual journey through Miami.',

            ],

            [

                'name' => 'Status Text',

                'id'   => 'video_status',

                'type' => 'text',

                'std'  => 'Out Now',

            ],

            [

                'name' => 'Watch Button Label',

                'id'   => 'video_watch_label',

                'type' => 'text',

                'std'  => 'Watch Now',

            ],

            [

                'name' => 'Cover Image',

                'id'   => 'video_cover_image',

                'type' => 'image_advanced',

            ],

        ],

    ];



    // ============================================

    // RELEASE INFO SECTION

    // ============================================

    $meta_boxes[] = [

        'title'      => 'Release Info Section',

        'id'         => 'mayami-release',

        'settings_pages' => 'mayami-settings',

        'tab'        => 'release',

        'fields'     => [

            [

                'name' => 'Kicker',

                'id'   => 'release_kicker',

                'type' => 'text',

                'std'  => 'Release',

            ],

            [

                'name' => 'Title Left',

                'id'   => 'release_title_left',

                'type' => 'text',

                'std'  => 'About',

            ],

            [

                'name' => 'Title Highlight',

                'id'   => 'release_title_highlight',

                'type' => 'text',

                'std'  => 'MAYAMI',

            ],

            [

                'name' => 'Cover Image',

                'id'   => 'release_cover_image',

                'type' => 'image_advanced',

            ],

            [

                'name' => 'Info Rows',

                'id'   => 'release_rows',

                'type' => 'group',

                'clone' => true,

                'sort_clone' => true,

                'fields' => [

                    [

                        'name' => 'Label',

                        'id'   => 'key',

                        'type' => 'text',

                    ],

                    [

                        'name' => 'Value',

                        'id'   => 'value',

                        'type' => 'text',

                    ],

                ],

            ],

        ],

    ];



    // ============================================

    // CTA SECTION

    // ============================================

    $meta_boxes[] = [

        'title'      => 'CTA Section',

        'id'         => 'mayami-cta',

        'settings_pages' => 'mayami-settings',

        'tab'        => 'cta',

        'fields'     => [

            [

                'name' => 'Kicker',

                'id'   => 'cta_kicker',

                'type' => 'text',

                'std'  => 'Join',

            ],

            [

                'name' => 'Title Left',

                'id'   => 'cta_title_left',

                'type' => 'text',

                'std'  => 'Share your',

            ],

            [

                'name' => 'Title Right',

                'id'   => 'cta_title_right',

                'type' => 'text',

                'std'  => 'MAYAMI',

            ],

            [

                'name' => 'Description',

                'id'   => 'cta_description',

                'type' => 'textarea',

                'std'  => 'Share your Miami moments with the hashtag.',

            ],

            [

                'name' => 'Hashtag',

                'id'   => 'cta_hashtag',

                'type' => 'text',

                'std'  => '#MAYAMI',

            ],

            [

                'name' => 'Texture Image',

                'id'   => 'cta_texture_image',

                'type' => 'image_advanced',

            ],

        ],

    ];



    // ============================================

    // FOOTER & STICKY BAR

    // ============================================

    $meta_boxes[] = [

        'title'      => 'Footer & Sticky Bar',

        'id'         => 'mayami-footer',

        'settings_pages' => 'mayami-settings',

        'tab'        => 'footer',

        'fields'     => [

            [

                'name' => 'Footer Line 1',

                'id'   => 'footer_line1',

                'type' => 'text',

                'std'  => '© 2026 Mayami',

            ],

            [

                'name' => 'Footer Line 2',

                'id'   => 'footer_line2',

                'type' => 'text',

                'std'  => 'All rights reserved',

            ],

            [

                'name' => 'Sticky Bar (Mobile) - Stream Label',

                'id'   => 'sticky_stream_label',

                'type' => 'text',

                'std'  => 'Stream',

            ],

            [

                'name' => 'Sticky Bar (Mobile) - Video Label',

                'id'   => 'sticky_video_label',

                'type' => 'text',

                'std'  => 'Video',

            ],

            [

                'name' => 'Sticky Bar (Mobile) - TikTok Label',

                'id'   => 'sticky_tiktok_label',

                'type' => 'text',

                'std'  => 'TikTok',

            ],

            [

                'name' => 'Sticky Bar (Mobile) - TikTok Link',

                'id'   => 'sticky_tiktok_link',

                'type' => 'url',

            ],

        ],

    ];



    // ============================================

    // LINKS (PLATFORMS)

    // ============================================

    $meta_boxes[] = [

        'title'      => 'Platform Links',

        'id'         => 'mayami-links',

        'settings_pages' => 'mayami-settings',

        'tab'        => 'links',

        'fields'     => [

            [

                'name' => 'FFM.to (Fan Link)',

                'id'   => 'link_ffm',

                'type' => 'url',

                'std'  => 'https://ffm.to/mayami',

            ],

            [

                'name' => 'Spotify',

                'id'   => 'link_spotify',

                'type' => 'url',

            ],

            [

                'name' => 'Apple Music',

                'id'   => 'link_apple',

                'type' => 'url',

            ],

            [

                'name' => 'YouTube Music',

                'id'   => 'link_youtube_music',

                'type' => 'url',

            ],

            [

                'name' => 'Deezer',

                'id'   => 'link_deezer',

                'type' => 'url',

            ],

            [

                'name' => 'Amazon Music',

                'id'   => 'link_amazon',

                'type' => 'url',

            ],

            [

                'name' => 'SoundCloud',

                'id'   => 'link_soundcloud',

                'type' => 'url',

            ],

            [

                'name' => 'YouTube Video',

                'id'   => 'link_youtube_video',

                'type' => 'url',

            ],

            [

                'name' => 'TikTok',

                'id'   => 'link_tiktok',

                'type' => 'url',

            ],

            [

                'name' => 'Instagram',

                'id'   => 'link_instagram',

                'type' => 'url',

            ],

        ],

    ];



    // ============================================

    // HERO SLIDER

    // ============================================

    $meta_boxes[] = [

        'title'      => 'Hero Slider',

        'id'         => 'mayami-hero-slider',

        'settings_pages' => 'mayami-settings',

        'tab'        => 'hero-slider',

        'fields'     => [

            [

                'name'   => 'Slider Items',

                'id'     => 'hero_slider',

                'type'   => 'group',

                'clone'  => true,

                'sort_clone' => true,

                'fields' => [

                    [

                        'name'    => 'Type',

                        'id'      => 'slide_type',

                        'type'    => 'select',

                        'options' => [

                            'image' => 'Image',

                            'video' => 'Video (YouTube)',

                        ],

                        'std'     => 'image',

                    ],

                    [

                        'name'       => 'Image',

                        'id'         => 'slide_image',

                        'type'       => 'image_advanced',

                        'visible'    => ['slide_type', '=', 'image'],

                    ],

                    [

                        'name'       => 'YouTube URL',

                        'id'         => 'video_url',

                        'type'       => 'url',

                        'visible'    => ['slide_type', '=', 'video'],

                    ],

                    [

                        'name'       => 'Video Thumbnail (optional)',

                        'id'         => 'thumbnail_url',

                        'type'       => 'image_advanced',

                        'visible'    => ['slide_type', '=', 'video'],

                    ],

                    [

                        'name' => 'Alt Text',

                        'id'   => 'alt_text',

                        'type' => 'text',

                    ],

                ],

            ],

        ],

    ];



    // ============================================

    // MARQUEE (Top Sticky Bar)

    // ============================================

    $meta_boxes[] = [

        'title'      => 'Marquee (Top Bar)',

        'id'         => 'mayami-marquee',

        'settings_pages' => 'mayami-settings',

        'tab'        => 'marquee',

        'fields'     => [

            [

                'name'   => 'Marquee Items',

                'id'     => 'marquee_items',

                'type'   => 'group',

                'clone'  => true,

                'sort_clone' => true,

                'fields' => [

                    [

                        'name' => 'Label',

                        'id'   => 'label',

                        'type' => 'text',

                    ],

                    [

                        'name' => 'Link (URL)',

                        'id'   => 'href',

                        'type' => 'url',

                    ],

                    [

                        'name'    => 'External Link',

                        'id'      => 'external',

                        'type'    => 'checkbox',

                        'desc'    => 'Check if link opens in new tab',

                    ],

                ],

            ],

        ],

    ];



    return $meta_boxes;

}

