<?php
/**
 * Type de rubrique STREAM (V4).
 *
 * Liens d'écoute / plateformes de streaming. Duplique le builder du footer ;
 * chaque item possède sa propre structure éditable. Le starter ne pose qu'une
 * base : à ajuster dans le builder.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type STREAM.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_wp_rubrique_type_stream(array $types): array
{
    $types['stream'] = [
        'label'        => __('STREAM', 'em-wp'),
        'label_plural' => __('STREAMS', 'em-wp'),
        'noun'         => __('Stream', 'em-wp'),
        'icon'         => 'dashicons-format-audio',
        'layout'       => ['columns' => 1, 'align' => [1 => 'center']],
        'starter'      => array_merge(em_wp_rubrique_default_appearance_fields(), [
            ['key' => 'platform', 'type' => 'icon', 'label' => __('Plateforme', 'em-wp'), 'default' => '', 'row' => 1, 'col' => 1],
        ]),
    ];

    return $types;
}
add_filter('em_wp_rubrique_types', 'em_wp_rubrique_type_stream');
