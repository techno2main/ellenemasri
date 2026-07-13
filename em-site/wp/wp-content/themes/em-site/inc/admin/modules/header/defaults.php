<?php
/**
 * Valeurs par défaut rubrique HEADER (par template).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Options HEADER par défaut.
 *
 * @return array{
 *     enabled:bool,
 *     hero_slug:string,
 *     slider_slug:string,
 *     layout:string,
 *     background_color:string,
 *     text_color:string,
 *     background_image:string,
 *     background_image_hidden:bool
 * }
 */
function em_site_header_default_options(): array
{
    return [
        'enabled'                  => true,
        'hero_slug'                => '',
        'slider_slug'              => '',
        'layout'                   => 'hero_left',
        'background_color'         => '',
        'text_color'               => '',
        'background_image'         => '',
        'background_image_hidden'  => false,
    ];
}

/**
 * Clé POST / champs formulaire HEADER (fixe, indépendante du template).
 */
function em_site_header_form_option_key(): string
{
    return 'em_site_header_options';
}
