<?php
/**
 * Type de rubrique SOCIAL (V4).
 *
 * Réseaux sociaux (icônes plateformes). Duplique le builder du footer ; chaque
 * item possède sa propre structure éditable. Le starter ne pose qu'une base :
 * à ajuster dans le builder.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type SOCIAL.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_wp_rubrique_type_social(array $types): array
{
    $types['social'] = [
        'label'        => __('SOCIAL', 'em-wp'),
        'label_plural' => __('SOCIALS', 'em-wp'),
        'noun'         => __('Social', 'em-wp'),
        'icon'         => 'dashicons-share',
        'layout'       => ['columns' => 1, 'align' => [1 => 'center']],
        'starter'      => array_merge(em_wp_rubrique_default_appearance_fields(), [
            ['key' => 'kicker', 'type' => 'text', 'label' => __('Sur-titre', 'em-wp'), 'default' => __('Follow', 'em-wp'), 'row' => 1, 'col' => 1],
            ['key' => 'title', 'type' => 'text', 'label' => __('Titre', 'em-wp'), 'default' => __('Join the journey', 'em-wp'), 'row' => 2, 'col' => 1],
            ['key' => 'card_1', 'type' => 'network_block', 'label' => __('Carte réseau', 'em-wp'), 'default' => '', 'row' => 3, 'col' => 1],
            ['key' => 'card_2', 'type' => 'network_block', 'label' => __('Carte réseau', 'em-wp'), 'default' => '', 'row' => 3, 'col' => 2],
            ['key' => 'card_3', 'type' => 'network_block', 'label' => __('Carte réseau', 'em-wp'), 'default' => '', 'row' => 3, 'col' => 3],
        ]),
    ];

    return $types;
}
add_filter('em_wp_rubrique_types', 'em_wp_rubrique_type_social');
