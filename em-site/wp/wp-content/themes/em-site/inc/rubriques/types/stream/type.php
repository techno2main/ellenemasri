<?php
/**
 * Type de rubrique STREAM (EM-SITE).
 *
 * Liens d'écoute / plateformes de streaming. Duplique le builder du footer ;
 * chaque item possède sa propre structure éditable. Le starter ne pose qu'une
 * base : à ajuster dans le builder.
 *
 * @package em-site
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
function em_site_rubrique_type_stream(array $types): array
{
    $types['stream'] = [
        'label'        => __('STREAM', 'em-site'),
        'label_plural' => __('STREAMS', 'em-site'),
        'noun'         => __('Stream', 'em-site'),
        'icon'         => 'dashicons-format-audio',
        'layout'       => ['columns' => 1, 'align' => [1 => 'center']],
        'starter'      => array_merge(em_site_rubrique_default_appearance_fields(), [
            ['key' => 'kicker', 'type' => 'text', 'label' => __('Sur-titre', 'em-site'), 'default' => __('Listen', 'em-site'), 'row' => 1, 'col' => 1],
            ['key' => 'title', 'type' => 'text', 'label' => __('Titre', 'em-site'), 'default' => __('Stream', 'em-site'), 'row' => 2, 'col' => 1],
            ['key' => 'card_1', 'type' => 'platform_block', 'label' => __('Carte plateforme', 'em-site'), 'default' => '', 'row' => 3, 'col' => 1],
            ['key' => 'card_2', 'type' => 'platform_block', 'label' => __('Carte plateforme', 'em-site'), 'default' => '', 'row' => 3, 'col' => 2],
            ['key' => 'card_3', 'type' => 'platform_block', 'label' => __('Carte plateforme', 'em-site'), 'default' => '', 'row' => 3, 'col' => 3],
        ]),
    ];

    return $types;
}
add_filter('em_site_rubrique_types', 'em_site_rubrique_type_stream');
