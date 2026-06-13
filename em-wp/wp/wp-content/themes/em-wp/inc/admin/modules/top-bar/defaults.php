<?php
/**
 * Defaults et identifiants admin Top Bar.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Definitions fixes des items de la top bar.
 */
function em_wp_top_bar_item_definitions(): array
{
    return [
        'line_1_center' => __('URL', 'em-wp'),
        'line_1_right'  => __('Titre Single', 'em-wp'),
        'baseline'      => __('Baseline', 'em-wp'),
        'cta'           => __('CTA', 'em-wp'),
    ];
}

/**
 * Retourne le slug de page admin Top Bar.
 */
function em_wp_top_bar_page_slug(): string
{
    return 'em-wp-top-bar';
}

function em_wp_top_bar_form_option_key(): string
{
    return 'em_wp_top_bar_options';
}

/**
 * Valeurs par defaut du module Top Bar.
 */
function em_wp_top_bar_default_options(): array
{
    $items = [];
    foreach (em_wp_top_bar_item_definitions() as $key => $title) {
        unset($title);
        $items[$key] = [
            'label'  => '',
            'href'   => '',
            'hidden' => false,
        ];
    }

    $items['cta']['label'] = __('Stream & Share', 'em-wp');
    $items['cta']['href'] = '#stream';
    $items['baseline']['label'] = __('Join the Journey!', 'em-wp');
    $items['baseline']['href'] = '#social';
    $items['line_1_center']['label'] = '';
    $items['line_1_center']['href'] = '';
    $items['line_1_right']['label'] = '';
    $items['line_1_right']['href'] = '';

    return [
        'enabled'                  => true,
        'logo_url'                 => '',
        'logo_hidden'              => false,
        'background_image_enabled' => false,
        'background_image_url'     => '',
        'background_image_hidden'  => false,
        'background_color'         => '',
        'text_color'               => '',
        'items'                    => $items,
        'stream_icons_hidden'      => false,
    ];
}
