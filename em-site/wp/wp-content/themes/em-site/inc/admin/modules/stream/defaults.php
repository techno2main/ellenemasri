<?php
/**
 * Defaults et identifiants admin Stream.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_stream_page_slug(): string
{
    return 'em-stream';
}

function em_site_stream_form_option_key(): string
{
    return em_site_stream_option_name(em_site_stream_admin_template_slug());
}

/**
 * Options rubrique STREAM par template (pointeur catalogue + style).
 *
 * @return array{enabled:bool,stream_slug:string,background_color:string,text_color:string}
 */
function em_site_stream_rubrique_default_options(): array
{
    return [
        'enabled'          => true,
        'stream_slug'      => '',
        'background_color' => '',
        'text_color'       => '',
    ];
}

/**
 * Options contenu d'une entrée catalogue Stream.
 *
 * @return array<string, mixed>
 */
function em_site_stream_catalog_default_options(): array
{
    $platforms = [];

    foreach (array_keys(em_site_stream_platform_definitions()) as $slug) {
        $platforms[] = em_site_stream_default_platform_item($slug);
    }

    return [
        'kicker'            => __('01 / Listen', 'em-site'),
        'title_prefix'      => __('Stream', 'em-site'),
        'title_logo'        => '',
        'availability_text' => __('Available everywhere', 'em-site'),
        'card_label'        => __('Listen on', 'em-site'),
        'platforms'         => $platforms,
    ];
}

