<?php
/**
 * Type de rubrique VIDEO (EM-SITE).
 *
 * Vidéo(s) mises en avant. Duplique le builder du footer ; chaque item possède
 * sa propre structure éditable. Le starter ne pose qu'une base : à ajuster dans
 * le builder.
 *
 * @package em-site
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
function em_site_rubrique_type_video(array $types): array
{
    $types['video'] = [
        'label'        => __('VIDEO', 'em-site'),
        'label_plural' => __('VIDEOS', 'em-site'),
        'noun'         => __('Vidéo', 'em-site'),
        'icon'         => 'dashicons-video-alt3',
        'layout'       => ['columns' => 1, 'align' => [1 => 'center']],
        'starter'      => array_merge(em_site_rubrique_default_appearance_fields(), [
            ['key' => 'kicker', 'type' => 'text', 'label' => __('Sur-titre', 'em-site'), 'default' => __('Watch', 'em-site'), 'row' => 1, 'col' => 1],
            ['key' => 'title', 'type' => 'text', 'label' => __('Titre', 'em-site'), 'default' => __('Official Video', 'em-site'), 'row' => 2, 'col' => 1],
            ['key' => 'description', 'type' => 'textarea', 'label' => __('Description', 'em-site'), 'default' => '', 'row' => 3, 'col' => 1],
            ['key' => 'video', 'type' => 'video_url', 'label' => __('Vidéo (YouTube / TikTok)', 'em-site'), 'default' => '', 'row' => 4, 'col' => 1],
        ]),
    ];

    return $types;
}
add_filter('em_site_rubrique_types', 'em_site_rubrique_type_video');
