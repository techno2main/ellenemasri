<?php
/**
 * Defaults et identifiants admin Stream.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_stream_page_slug(): string
{
    return 'em-wp-stream';
}

function em_wp_stream_form_option_key(): string
{
    return 'em_wp_stream_options';
}

/**
 * Options rubrique STREAM par template (pointeur catalogue + style).
 *
 * @return array{enabled:bool,stream_slug:string,background_color:string,text_color:string}
 */
function em_wp_stream_rubrique_default_options(): array
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
function em_wp_stream_catalog_default_options(): array
{
    $platforms = [];

    foreach (array_keys(em_wp_stream_platform_definitions()) as $slug) {
        $platforms[] = em_wp_stream_default_platform_item($slug);
    }

    return [
        'kicker'            => __('01 / Listen', 'em-wp'),
        'title_prefix'      => __('Stream', 'em-wp'),
        'title_logo'        => '',
        'availability_text' => __('Available everywhere', 'em-wp'),
        'card_label'        => __('Listen on', 'em-wp'),
        'platforms'         => $platforms,
    ];
}
