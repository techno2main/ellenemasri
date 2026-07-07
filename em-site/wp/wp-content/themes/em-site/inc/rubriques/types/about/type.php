<?php
/**
 * Type de rubrique ABOUT (EM-SITE).
 *
 * Rubrique configurable via le builder ; les items restent stockes en base.
 *
 * @package em-site
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
function em_site_rubrique_type_about(array $types): array
{
    $types['about'] = [
        'label'        => __('ABOUT', 'em-site'),
        'label_plural' => __('ABOUTS', 'em-site'),
        'noun'         => __('About', 'em-site'),
        'icon'         => 'dashicons-star-filled',
        'layout'       => ['columns' => 1, 'align' => [1 => 'left']],
        'starter'      => em_site_rubrique_default_appearance_fields(),
    ];

    return $types;
}
add_filter('em_site_rubrique_types', 'em_site_rubrique_type_about');
