<?php
/**
 * Type de rubrique HEADERS (catalogue des assemblages HEADER).
 *
 * Chaque item HEADERS représente une composition HEADER complète
 * (HERO/SLIDER + fond partagé), pilotée dans le panneau HEADER du squelette.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type HEADERS.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_site_rubrique_type_headers(array $types): array
{
    $types['headers'] = [
        'label'        => __('HEADER', 'em-site'),
        'label_plural' => __('HEADER', 'em-site'),
        'noun'         => __('Header', 'em-site'),
        'icon'         => 'dashicons-columns',
        'layout'       => ['columns' => 1, 'align' => [1 => 'left']],
        // Le contenu propre de l\'assemblage est piloté par le panneau HEADER.
        'starter'      => em_site_rubrique_default_appearance_fields(),
    ];

    return $types;
}
add_filter('em_site_rubrique_types', 'em_site_rubrique_type_headers');
