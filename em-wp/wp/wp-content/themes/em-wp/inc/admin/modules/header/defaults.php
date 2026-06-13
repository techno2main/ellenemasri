<?php
/**
 * Valeurs par défaut rubrique HEADER (par template).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Options HEADER par défaut.
 *
 * @return array{enabled:bool,hero_slug:string,slider_slug:string,layout:string}
 */
function em_wp_header_default_options(): array
{
    return [
        'enabled'     => true,
        'hero_slug'   => '',
        'slider_slug' => '',
        'layout'      => 'hero_left',
    ];
}
