<?php
/**
 * Type de rubrique TOP-BAR (EM-SITE).
 *
 * Barre supérieure (icônes plateformes de streaming centrées). Duplique le
 * builder du footer ; chaque item possède sa propre structure éditable.
 * Le starter ne pose qu'une base : à ajuster dans le builder.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type TOP-BAR.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_site_rubrique_type_top_bar(array $types): array
{
    $types['top-bar'] = [
        'label'        => __('TOP-BAR', 'em-site'),
        'label_plural' => __('TOP-BAR', 'em-site'),
        'noun'         => __('Top-Bar', 'em-site'),
        'icon'         => 'dashicons-menu-alt3',
        'layout'       => ['columns' => 1, 'align' => [1 => 'center']],
        'starter'      => array_merge(em_site_rubrique_default_appearance_fields(), [
            ['key' => 'platform', 'type' => 'icon', 'label' => __('Plateforme', 'em-site'), 'default' => '', 'row' => 1, 'col' => 1],
        ]),
    ];

    return $types;
}
add_filter('em_site_rubrique_types', 'em_site_rubrique_type_top_bar');
