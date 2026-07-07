<?php

/**

 * Defaults et identifiants admin Social.

 *

 * @package em-wp

 */



if (!defined('ABSPATH')) {

    exit;

}



function em_wp_social_page_slug(): string

{

    return 'em-social';

}



function em_wp_social_form_option_key(): string

{

    return em_wp_social_option_name(em_wp_social_admin_template_slug());

}



/**

 * Options rubrique SOCIAL par template (pointeur catalogue + style).

 *

 * @return array{enabled:bool,social_slug:string,background_color:string,text_color:string}

 */

function em_wp_social_rubrique_default_options(): array

{

    return [

        'enabled'          => true,

        'social_slug'      => '',

        'background_color' => '',

        'text_color'       => '',

    ];

}



/**

 * Options contenu d'une entrée catalogue Social.

 *

 * @return array<string, mixed>

 */

function em_wp_social_catalog_default_options(): array

{

    $platforms = [];



    foreach (array_keys(em_wp_social_platform_definitions()) as $slug) {

        $platforms[] = em_wp_social_default_platform_item($slug);

    }



    return [

        'kicker'           => __('02 / Follow', 'em-wp'),

        'title_left'       => __('Join the', 'em-wp'),

        'title_right'      => __('journey', 'em-wp'),

        'description'      => __('Share clips, updates, and behind-the-scenes moments.', 'em-wp'),

        'platforms'        => $platforms,

    ];

}



