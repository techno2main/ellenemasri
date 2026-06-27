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
        'icon'         => 'dashicons-align-center',
        'layout'  => ['columns' => 3, 'align' => [1 => 'left', 2 => 'center', 3 => 'right']],
        'starter' => [
            ['key' => 'bg_color', 'type' => 'color', 'label' => __('Fond', 'em-wp'), 'default' => '#0f172a', 'options' => ['role' => 'background'], 'row' => 1, 'col' => 1],
            ['key' => 'text_color', 'type' => 'color', 'label' => __('Texte', 'em-wp'), 'default' => '#e2e8f0', 'options' => ['role' => 'text'], 'row' => 1, 'col' => 1],
            ['key' => 'link_color', 'type' => 'color', 'label' => __('Liens', 'em-wp'), 'default' => '#38bdf8', 'options' => ['role' => 'link'], 'row' => 1, 'col' => 1],
            ['key' => 'link_hover_color', 'type' => 'color', 'label' => __('Survol', 'em-wp'), 'default' => '#7dd3fc', 'options' => ['role' => 'link_hover'], 'row' => 1, 'col' => 1],
            ['key' => 'link_visited_color', 'type' => 'color', 'label' => __('Cliqués', 'em-wp'), 'default' => '#818cf8', 'options' => ['role' => 'link_visited'], 'row' => 1, 'col' => 1],
            ['key' => 'link_underline', 'type' => 'toggle', 'label' => __('Soulignés', 'em-wp'), 'default' => true, 'options' => ['role' => 'link_underline'], 'row' => 1, 'col' => 1],
            ['key' => 'space_top', 'type' => 'number', 'label' => __('Haut', 'em-wp'), 'default' => 18, 'options' => ['role' => 'space_top'], 'row' => 1, 'col' => 1],
            ['key' => 'space_bottom', 'type' => 'number', 'label' => __('Bas', 'em-wp'), 'default' => 18, 'options' => ['role' => 'space_bottom'], 'row' => 1, 'col' => 1],
            ['key' => 'space_left', 'type' => 'number', 'label' => __('Gauche', 'em-wp'), 'default' => 20, 'options' => ['role' => 'space_left'], 'row' => 1, 'col' => 1],
            ['key' => 'space_right', 'type' => 'number', 'label' => __('Droite', 'em-wp'), 'default' => 20, 'options' => ['role' => 'space_right'], 'row' => 1, 'col' => 1],
            ['key' => 'font_family', 'type' => 'select', 'label' => __('Police', 'em-wp'), 'default' => 'archivo_black', 'options' => ['role' => 'font'], 'row' => 1, 'col' => 1],
            ['key' => 'line1', 'type' => 'text', 'label' => __('Copyright', 'em-wp'), 'default' => __('© Your Artist Name', 'em-wp'), 'row' => 1, 'col' => 1],
            ['key' => 'tagline', 'type' => 'text', 'label' => __('Tagline', 'em-wp'), 'default' => __('Your project tagline.', 'em-wp'), 'row' => 1, 'col' => 2],
            ['key' => 'tiktok', 'type' => 'url', 'label' => __('Lien TikTok', 'em-wp'), 'default' => '', 'row' => 1, 'col' => 3],
        ],
    ];

    return $types;
}
add_filter('em_wp_rubrique_types', 'em_wp_rubrique_type_footer');
