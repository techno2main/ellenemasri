<?php
/**
 * Type de rubrique RELEASE (V4).
 *
 * Sortie / release (pochette + titre + lien). Duplique le builder du footer ;
 * chaque item possède sa propre structure éditable. Le starter ne pose qu'une
 * base : à ajuster dans le builder.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type RELEASE.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_wp_rubrique_type_release(array $types): array
{
    $types['release'] = [
        'label'        => __('RELEASE', 'em-wp'),
        'label_plural' => __('RELEASES', 'em-wp'),
        'noun'         => __('Release', 'em-wp'),
        'icon'         => 'dashicons-album',
        'layout'       => ['columns' => 2, 'align' => [1 => 'center', 2 => 'left']],
        'starter'      => array_merge(em_wp_rubrique_default_appearance_fields(), [
            ['key' => 'cover', 'type' => 'image', 'label' => __('Pochette', 'em-wp'), 'default' => '', 'row' => 1, 'col' => 1],
            ['key' => 'title', 'type' => 'text', 'label' => __('Titre', 'em-wp'), 'default' => '', 'row' => 1, 'col' => 2],
            ['key' => 'link', 'type' => 'url', 'label' => __('Lien', 'em-wp'), 'default' => '', 'row' => 1, 'col' => 2],
        ]),
    ];

    return $types;
}
add_filter('em_wp_rubrique_types', 'em_wp_rubrique_type_release');
