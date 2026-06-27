<?php
/**
 * Type de rubrique CTA (V4).
 *
 * Appel à l'action (libellé + lien). Duplique le builder du footer ; chaque
 * item possède sa propre structure éditable. Le starter ne pose qu'une base :
 * à ajuster dans le builder.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type CTA.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_wp_rubrique_type_cta(array $types): array
{
    $types['cta'] = [
        'label'        => __('CTA', 'em-wp'),
        'label_plural' => __('CTAS', 'em-wp'),
        'noun'         => __('CTA', 'em-wp'),
        'icon'         => 'dashicons-megaphone',
        'layout'       => ['columns' => 1, 'align' => [1 => 'center']],
        'starter'      => array_merge(em_wp_rubrique_default_appearance_fields(), [
            ['key' => 'label', 'type' => 'text', 'label' => __('Libellé', 'em-wp'), 'default' => '', 'row' => 1, 'col' => 1],
            ['key' => 'link', 'type' => 'url', 'label' => __('Lien', 'em-wp'), 'default' => '', 'row' => 1, 'col' => 1],
        ]),
    ];

    return $types;
}
add_filter('em_wp_rubrique_types', 'em_wp_rubrique_type_cta');
