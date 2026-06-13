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
    return 'em-wp-releases';
}

function em_wp_release_form_option_key(): string
{
    return 'em_wp_release_options';
}

function em_wp_release_default_options(): array
{
    return [
        'enabled'          => true,
        'background_color' => '',
        'text_color'       => '',
        'kicker'           => __('04 / Release', 'em-wp'),
        'title_left'       => __('The', 'em-wp'),
        'title_highlight'  => __('credits', 'em-wp'),
        'cover_image'      => '',
        'rows'             => [],
    ];
}
