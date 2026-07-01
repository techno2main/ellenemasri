<?php
/**
 * Type de rubrique ABOUT (V4).
 *
 * Rubrique configurable via le builder ; les items restent stockes en base.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type ABOUT.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_wp_rubrique_type_about(array $types): array
{
    $types['about'] = [
        'label'        => __('ABOUT', 'em-wp'),
        'label_plural' => __('ABOUTS', 'em-wp'),
        'noun'         => __('About', 'em-wp'),
        'icon'         => 'dashicons-star-filled',
        'layout'       => ['columns' => 1, 'align' => [1 => 'left']],
        'starter'      => em_wp_rubrique_default_appearance_fields(),
    ];

    return $types;
}
add_filter('em_wp_rubrique_types', 'em_wp_rubrique_type_about');
