<?php
/**
 * Types de champ intégrés (EM-SITE).
 *
 * Déclare le catalogue fermé de types de champ réutilisables via le filtre
 * `em_site_field_types`. Chaque type reste volontairement simple ; le rendu admin
 * et front sera branché lors des étapes ultérieures (moteur de rendu + UI).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre les types de champ de base.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_site_field_types_builtin(array $types): array
{
    $builtin = [
        'text' => [
            'label'    => __('Texte', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_text',
        ],
        'textarea' => [
            'label'    => __('Texte enrichi', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_textarea',
        ],
        'url' => [
            'label'    => __('Lien (URL)', 'em-site'),
            'default'  => '',
            'sanitize' => 'esc_url_raw',
        ],
        'email' => [
            'label'    => __('Email', 'em-site'),
            'default'  => '',
            'sanitize' => 'sanitize_email',
        ],
        'image' => [
            'label'    => __('Image (média)', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_image',
        ],
        'text_image' => [
            'label'    => __('Texte + Image', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_text_image',
        ],
        'text_icon' => [
            'label'    => __('Texte + Icône', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_text_icon',
        ],
        'icon_text' => [
            'label'    => __('Icône + Texte', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_text_icon',
        ],
        'text_text' => [
            'label'    => __('Texte + Texte', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_text_text',
        ],
        'icon' => [
            'label'    => __('Icône plateforme', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_icon',
        ],
        'platform_block' => [
            'label'    => __('Bloc Plateforme', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_platform_block',
        ],
        'button' => [
            'label'    => __('Bouton', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_button',
        ],
        'color' => [
            'label'    => __('Couleur', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_color',
        ],
        'toggle' => [
            'label'    => __('Activer / Masquer', 'em-site'),
            'default'  => false,
            'sanitize' => 'em_site_field_sanitize_bool',
        ],
        'number' => [
            'label'    => __('Nombre', 'em-site'),
            'default'  => 0,
            'sanitize' => 'em_site_field_sanitize_int',
        ],
        'select' => [
            'label'    => __('Liste de choix', 'em-site'),
            'default'  => '',
            'sanitize' => 'sanitize_key',
        ],
        'repeater' => [
            'label'    => __('Groupe répétable', 'em-site'),
            'default'  => [],
            'sanitize' => 'em_site_field_sanitize_repeater',
        ],
        // Médias (vidéo / son / slider) et bloc réseau — avant les séparateurs.
        'video_url' => [
            'label'    => __('Vidéo URL (YouTube ou TikTok)', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_video_url',
        ],
        'video_file' => [
            'label'    => __('Vidéo (fichier média)', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_media_id',
        ],
        'audio_file' => [
            'label'    => __('Son (fichier média)', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_media_id',
        ],
        'audio_url' => [
            'label'    => __('Son URL', 'em-site'),
            'default'  => '',
            'sanitize' => 'esc_url_raw',
        ],
        'network_block' => [
            'label'    => __('Bloc Réseau', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_platform_block',
        ],
        'slider' => [
            'label'    => __('Slider', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_slides',
        ],
        // Champs décoratifs : pas de libellé. Filet et flèches portent une couleur.
        'sep_line' => [
            'label'    => __('Séparateur (filet)', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_color',
        ],
        'sep_blank' => [
            'label'    => __('Séparateur (vide)', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_height',
        ],
        'arrow_up' => [
            'label'    => __('Flèche vers le haut', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_arrow',
        ],
        'arrow_down' => [
            'label'    => __('Flèche vers le bas', 'em-site'),
            'default'  => '',
            'sanitize' => 'em_site_field_sanitize_arrow',
        ],
    ];

    // Les types intégrés ne doivent pas écraser une éventuelle surcharge tierce.
    return $builtin + $types;
}
add_filter('em_site_field_types', 'em_site_field_types_builtin');

/**
 * Décode la valeur d'un champ image en { id, link }.
 *
 * Accepte le format JSON {id,link} ou, en repli, un simple ID de média (legacy).
 *
 * @param mixed $value
 * @return array{id:int, link:string}
 */
function em_site_rubrique_image_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return ['id' => absint($value), 'link' => '', 'w' => 0, 'h' => 0, 'fx' => 50, 'fy' => 50, 'tape' => false, 'tape_color' => ''];
    }

    return [
        'id'   => absint($decoded['id'] ?? 0),
        'link' => (string) ($decoded['link'] ?? ''),
        'w'    => max(0, (int) ($decoded['w'] ?? 0)),
        'h'    => max(0, (int) ($decoded['h'] ?? 0)),
        'fx'   => max(0, min(100, (int) ($decoded['fx'] ?? 50))),
        'fy'   => max(0, min(100, (int) ($decoded['fy'] ?? 50))),
        'tape' => !empty($decoded['tape']),
        'tape_color' => sanitize_hex_color((string) ($decoded['tape_color'] ?? '')) ?: '',
    ];
}

/**
 * Sanitise un champ image : ID de média + lien (URL ou ancre), encodé en JSON.
 *
 * @param mixed $value
 */
function em_site_field_sanitize_image($value): string
{
    $parsed = em_site_rubrique_image_value($value);
    $id = absint($parsed['id']);
    $link = esc_url_raw($parsed['link']);

    if ($id === 0 && $link === '') {
        return '';
    }

    return (string) wp_json_encode([
        'id'   => $id,
        'link' => $link,
        'w'    => max(0, (int) $parsed['w']),
        'h'    => max(0, (int) $parsed['h']),
        'fx'   => max(0, min(100, (int) $parsed['fx'])),
        'fy'   => max(0, min(100, (int) $parsed['fy'])),
        'tape' => !empty($parsed['tape']),
        'tape_color' => sanitize_hex_color((string) ($parsed['tape_color'] ?? '')) ?: '',
    ]);
}

/**
 * Sanitise une couleur hex (#rgb / #rrggbb), '' si invalide.
 */
function em_site_field_sanitize_color($value): string
{
    $color = sanitize_text_field((string) $value);
    $sanitized = function_exists('sanitize_hex_color') ? sanitize_hex_color($color) : null;

    return is_string($sanitized) ? $sanitized : '';
}

/**
 * Sanitise un booléen tolérant ('1','true','on'…).
 */
function em_site_field_sanitize_bool($value): bool
{
    if (is_bool($value)) {
        return $value;
    }

    return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
}

/**
 * Sanitise un entier (>= 0).
 */
function em_site_field_sanitize_int($value): int
{
    return max(0, (int) $value);
}

/**
 * Sanitise un groupe répétable : tableau de lignes (tableaux associatifs).
 * La sanitisation fine des sous-champs est faite par le schéma appelant.
 *
 * @param mixed $value
 * @return array<int, array<string, mixed>>
 */
function em_site_field_sanitize_repeater($value): array
{
    if (!is_array($value)) {
        return [];
    }

    $rows = [];

    foreach ($value as $row) {
        if (is_array($row)) {
            $rows[] = $row;
        }
    }

    return array_values($rows);
}
