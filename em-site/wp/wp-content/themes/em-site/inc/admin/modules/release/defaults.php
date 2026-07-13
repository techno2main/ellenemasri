<?php
/**
 * Defaults et identifiants admin Release.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_release_page_slug(): string
{
    return 'em-releases';
}

function em_site_release_form_option_key(): string
{
    return em_site_release_option_name(em_site_release_admin_template_slug());
}

/**
 * Options rubrique RELEASE par template.
 *
 * @return array{enabled:bool,release_slug:string,background_color:string,text_color:string}
 */
function em_site_release_rubrique_default_options(): array
{
    return [
        'enabled'          => true,
        'release_slug'     => '',
        'background_color' => '',
        'text_color'       => '',
    ];
}

/**
 * Options contenu catalogue Release.
 *
 * @return array<string, mixed>
 */
function em_site_release_catalog_default_options(): array
{
    return [
        'kicker'          => __('04 / Release', 'em-site'),
        'title_left'      => __('The', 'em-site'),
        'title_highlight' => __('credits', 'em-site'),
        'cover_image'     => '',
        'rows'            => [],
    ];
}

