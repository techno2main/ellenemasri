<?php
/**
 * Defaults et identifiants admin Release.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_page_slug(): string
{
    return 'em-releases';
}

function em_wp_release_form_option_key(): string
{
    return em_wp_release_option_name(em_wp_release_admin_template_slug());
}

/**
 * Options rubrique RELEASE par template.
 *
 * @return array{enabled:bool,release_slug:string,background_color:string,text_color:string}
 */
function em_wp_release_rubrique_default_options(): array
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
function em_wp_release_catalog_default_options(): array
{
    return [
        'kicker'          => __('04 / Release', 'em-wp'),
        'title_left'      => __('The', 'em-wp'),
        'title_highlight' => __('credits', 'em-wp'),
        'cover_image'     => '',
        'rows'            => [],
    ];
}

