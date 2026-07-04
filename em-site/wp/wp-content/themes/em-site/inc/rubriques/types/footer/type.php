<?php
/**
 * Type de rubrique FOOTER (V4) — pilote, modèle simplifié.
 *
 * Déclare la rubrique FOOTER : libellé, icône et STRUCTURE DE DÉPART (starter)
 * utilisée quand on crée un nouveau footer. Chaque footer (item) possède ensuite
 * sa propre structure éditable par lignes/colonnes via le builder.
 *
 * Ajouter une rubrique = dupliquer ce dossier (type.php) ; aucun changement dans
 * le cœur n'est requis.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type FOOTER.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_wp_rubrique_type_footer(array $types): array
{
    $types['footer'] = [
        'label'        => __('FOOTER', 'em-wp'),
        'label_plural' => __('FOOTERS', 'em-wp'),
        'noun'         => __('Footer', 'em-wp'),
        'icon'         => 'dashicons-align-center',
        'layout'  => ['columns' => 3, 'align' => [1 => 'left', 2 => 'center', 3 => 'right']],
        'starter' => array_merge(em_wp_rubrique_default_appearance_fields(), [
            ['key' => 'line1', 'type' => 'text', 'label' => __('Copyright', 'em-wp'), 'default' => __('© Your Artist Name', 'em-wp'), 'row' => 1, 'col' => 1],
            ['key' => 'tagline', 'type' => 'text', 'label' => __('Tagline', 'em-wp'), 'default' => __('Your project tagline.', 'em-wp'), 'row' => 1, 'col' => 2],
            ['key' => 'tiktok', 'type' => 'url', 'label' => __('Lien TikTok', 'em-wp'), 'default' => '', 'row' => 1, 'col' => 3],
        ]),
    ];

    return $types;
}
add_filter('em_wp_rubrique_types', 'em_wp_rubrique_type_footer');
