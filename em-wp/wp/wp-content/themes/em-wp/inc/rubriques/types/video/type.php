<?php
/**
 * Type de rubrique VIDEO (V4).
 *
 * Vidéo(s) mises en avant. Duplique le builder du footer ; chaque item possède
 * sa propre structure éditable. Le starter ne pose qu'une base : à ajuster dans
 * le builder.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type VIDEO.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_wp_rubrique_type_video(array $types): array
{
    $types['video'] = [
        'label'        => __('VIDEO', 'em-wp'),
        'label_plural' => __('VIDEOS', 'em-wp'),
        'noun'         => __('Vidéo', 'em-wp'),
        'icon'         => 'dashicons-video-alt3',
        'layout'       => ['columns' => 1, 'align' => [1 => 'center']],
        'starter'      => array_merge(em_wp_rubrique_default_appearance_fields(), [
            ['key' => 'kicker', 'type' => 'text', 'label' => __('Sur-titre', 'em-wp'), 'default' => __('Watch', 'em-wp'), 'row' => 1, 'col' => 1],
            ['key' => 'title', 'type' => 'text', 'label' => __('Titre', 'em-wp'), 'default' => __('Official Video', 'em-wp'), 'row' => 2, 'col' => 1],
            ['key' => 'description', 'type' => 'textarea', 'label' => __('Description', 'em-wp'), 'default' => '', 'row' => 3, 'col' => 1],
            ['key' => 'video', 'type' => 'video_url', 'label' => __('Vidéo (YouTube / TikTok)', 'em-wp'), 'default' => '', 'row' => 4, 'col' => 1],
        ]),
    ];

    return $types;
}
add_filter('em_wp_rubrique_types', 'em_wp_rubrique_type_video');
