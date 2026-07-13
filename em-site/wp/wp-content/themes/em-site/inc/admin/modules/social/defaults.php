<?php

/**

 * Defaults et identifiants admin Social.

 *

 * @package em-site

 */



if (!defined('ABSPATH')) {

    exit;

}



function em_site_social_page_slug(): string

{

    return 'em-social';

}



function em_site_social_form_option_key(): string

{

    return em_site_social_option_name(em_site_social_admin_template_slug());

}



/**

 * Options rubrique SOCIAL par template (pointeur catalogue + style).

 *

 * @return array{enabled:bool,social_slug:string,background_color:string,text_color:string}

 */

function em_site_social_rubrique_default_options(): array

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

function em_site_social_catalog_default_options(): array

{

    $platforms = [];



    foreach (array_keys(em_site_social_platform_definitions()) as $slug) {

        $platforms[] = em_site_social_default_platform_item($slug);

    }



    return [

        'kicker'           => __('02 / Follow', 'em-site'),

        'title_left'       => __('Join the', 'em-site'),

        'title_right'      => __('journey', 'em-site'),

        'description'      => __('Share clips, updates, and behind-the-scenes moments.', 'em-site'),

        'platforms'        => $platforms,

    ];

}



