<?php

/**

 * CMB2 Fields Configuration for ellene-wp Landing Page

 * Professional solution - 100% Free & Robust

 * 

 * @package ElleneWp

 */



// Prevent direct access

if (!defined('ABSPATH')) {

    exit;

}

require_once get_template_directory() . '/inc/metabox-fields/hero.php';
require_once get_template_directory() . '/inc/metabox-fields/slider.php';
require_once get_template_directory() . '/inc/metabox-fields/stream.php';
require_once get_template_directory() . '/inc/metabox-fields/social.php';
require_once get_template_directory() . '/inc/metabox-fields/video.php';
require_once get_template_directory() . '/inc/metabox-fields/release.php';
require_once get_template_directory() . '/inc/metabox-fields/cta.php';
require_once get_template_directory() . '/inc/metabox-fields/footer.php';



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

        'title'        => 'ellene-wp Landing Settings',

        'object_types' => ['options-page'],

        'option_key'   => 'mayami_options',

        'icon_url'     => 'dashicons-admin-site-alt3',

        'position'     => 2,

        'menu_title'   => 'ellene-wp Landing',

        'save_button'  => 'Enregistrer',

    ]);



    mayami_register_hero_section_fields($cmb_options);



    mayami_register_slider_section_fields($cmb_options);



    mayami_register_stream_section_fields($cmb_options);



    mayami_register_social_section_fields($cmb_options);

    

    mayami_register_video_section_fields($cmb_options);



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

    

    mayami_register_release_section_fields($cmb_options);



    mayami_register_cta_section_fields($cmb_options);



    mayami_register_footer_section_fields($cmb_options);



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

                'std'  => 'ellene-wp',

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

                'std'  => 'From studio sessions to city streets. Follow the story behind ellene-wp.',

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

                'std'  => 'ELLENE-WP',

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

                'std'  => 'ELLENE-WP',

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

                'std'  => '#ELLENEWP',

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

                'std'  => '© 2026 ellene-wp',

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

