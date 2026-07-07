<?php
/**
 * Type de rubrique FOOTER (EM-SITE) — pilote, modèle simplifié.
 *
 * Déclare la rubrique FOOTER : libellé, icône et STRUCTURE DE DÉPART (starter)
 * utilisée quand on crée un nouveau footer. Chaque footer (item) possède ensuite
 * sa propre structure éditable par lignes/colonnes via le builder.
 *
 * Ajouter une rubrique = dupliquer ce dossier (type.php) ; aucun changement dans
 * le cœur n'est requis.
 *
 * @package em-site
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
function em_site_rubrique_type_footer(array $types): array
{
    $types['footer'] = [
        'label'        => __('FOOTER', 'em-site'),
        'label_plural' => __('FOOTER', 'em-site'),
        'noun'         => __('Footer', 'em-site'),
        'icon'         => 'dashicons-align-center',
        'layout'  => ['columns' => 3, 'align' => [1 => 'left', 2 => 'center', 3 => 'right']],
        'starter' => array_merge(em_site_rubrique_default_appearance_fields(), [
            ['key' => 'line1', 'type' => 'text', 'label' => __('Copyright', 'em-site'), 'default' => __('© Your Artist Name', 'em-site'), 'row' => 1, 'col' => 1],
            ['key' => 'tagline', 'type' => 'text', 'label' => __('Tagline', 'em-site'), 'default' => __('Your project tagline.', 'em-site'), 'row' => 1, 'col' => 2],
            ['key' => 'tiktok', 'type' => 'url', 'label' => __('Lien TikTok', 'em-site'), 'default' => '', 'row' => 1, 'col' => 3],
        ]),
    ];

    return $types;
}
add_filter('em_site_rubrique_types', 'em_site_rubrique_type_footer');
