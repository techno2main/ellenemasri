<?php
/**
 * Types de champ intégrés (V4).
 *
 * Déclare le catalogue fermé de types de champ réutilisables via le filtre
 * `em_wp_field_types`. Chaque type reste volontairement simple ; le rendu admin
 * et front sera branché lors des étapes ultérieures (moteur de rendu + UI).
 *
 * @package em-wp
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
function em_wp_field_types_builtin(array $types): array
{
    $builtin = [
        'text' => [
            'label'    => __('Texte', 'em-wp'),
            'default'  => '',
            'sanitize' => 'sanitize_text_field',
        ],
        'textarea' => [
            'label'    => __('Texte long', 'em-wp'),
            'default'  => '',
            'sanitize' => 'sanitize_textarea_field',
        ],
        'url' => [
            'label'    => __('Lien (URL)', 'em-wp'),
            'default'  => '',
            'sanitize' => 'esc_url_raw',
        ],
        'email' => [
            'label'    => __('Email', 'em-wp'),
            'default'  => '',
            'sanitize' => 'sanitize_email',
        ],
        'image' => [
            'label'    => __('Image (média)', 'em-wp'),
            'default'  => '',
            'sanitize' => 'em_wp_field_sanitize_image',
        ],
        'icon' => [
            'label'    => __('Icône plateforme', 'em-wp'),
            'default'  => '',
            'sanitize' => 'em_wp_field_sanitize_icon',
        ],
        'color' => [
            'label'    => __('Couleur', 'em-wp'),
            'default'  => '',
            'sanitize' => 'em_wp_field_sanitize_color',
        ],
        'toggle' => [
            'label'    => __('Activer / Masquer', 'em-wp'),
            'default'  => false,
            'sanitize' => 'em_wp_field_sanitize_bool',
        ],
        'number' => [
            'label'    => __('Nombre', 'em-wp'),
            'default'  => 0,
            'sanitize' => 'em_wp_field_sanitize_int',
        ],
        'select' => [
            'label'    => __('Liste de choix', 'em-wp'),
            'default'  => '',
            'sanitize' => 'sanitize_key',
        ],
        'repeater' => [
            'label'    => __('Groupe répétable', 'em-wp'),
            'default'  => [],
            'sanitize' => 'em_wp_field_sanitize_repeater',
        ],
        // Champs décoratifs : pas de libellé. Filet et flèches portent une couleur.
        'sep_line' => [
            'label'    => __('Séparateur (filet)', 'em-wp'),
            'default'  => '',
            'sanitize' => 'em_wp_field_sanitize_color',
        ],
        'sep_blank' => [
            'label'    => __('Séparateur (vide)', 'em-wp'),
            'default'  => '',
            'sanitize' => '__return_empty_string',
        ],
        'arrow_up' => [
            'label'    => __('Flèche vers le haut', 'em-wp'),
            'default'  => '',
            'sanitize' => 'em_wp_field_sanitize_arrow',
        ],
        'arrow_down' => [
            'label'    => __('Flèche vers le bas', 'em-wp'),
            'default'  => '',
            'sanitize' => 'em_wp_field_sanitize_arrow',
        ],
    ];

    // Les types intégrés ne doivent pas écraser une éventuelle surcharge tierce.
    return $builtin + $types;
}
add_filter('em_wp_field_types', 'em_wp_field_types_builtin');

/**
 * Choix d'icônes de plateformes (streaming + réseaux sociaux), mutualisés.
 *
 * Clé = « stream:<slug> » ou « social:<slug> » ; valeur = label, icône (FA), groupe.
 *
 * @return array<string, array{label:string, icon:string, group:string}>
 */
function em_wp_rubrique_platform_choices(): array
{
    $choices = [];

    if (function_exists('em_wp_stream_platform_definitions')) {
        foreach (em_wp_stream_platform_definitions() as $slug => $def) {
            $choices['stream:' . $slug] = [
                'label' => (string) ($def['label'] ?? $slug),
                'icon'  => (string) ($def['icon'] ?? 'fa-link'),
                'group' => __('Streaming', 'em-wp'),
            ];
        }
    }

    if (function_exists('em_wp_social_platform_definitions')) {
        foreach (em_wp_social_platform_definitions() as $slug => $def) {
            $choices['social:' . $slug] = [
                'label' => (string) ($def['label'] ?? $slug),
                'icon'  => (string) ($def['icon'] ?? 'fa-link'),
                'group' => __('Réseaux sociaux', 'em-wp'),
            ];
        }
    }

    return $choices;
}

/**
 * Classe d'icône FontAwesome pour une clé de plateforme ('' si inconnue).
 */
function em_wp_rubrique_platform_icon(string $key): string
{
    $choices = em_wp_rubrique_platform_choices();

    return isset($choices[$key]) ? (string) $choices[$key]['icon'] : '';
}

/**
 * Décode la valeur d'un champ icône en { platform, url }.
 *
 * Accepte le format JSON {platform,url} ou, en repli, une simple clé de plateforme.
 *
 * @param mixed $value
 * @return array{platform:string, url:string}
 */
function em_wp_rubrique_icon_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return ['platform' => (string) $value, 'url' => ''];
    }

    return [
        'platform' => (string) ($decoded['platform'] ?? ''),
        'url'      => (string) ($decoded['url'] ?? ''),
    ];
}

/**
 * Sanitise un champ icône : plateforme (clé) + lien (URL), encodé en JSON.
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_icon($value): string
{
    $parsed = em_wp_rubrique_icon_value($value);
    $platform = sanitize_text_field($parsed['platform']);
    $url = esc_url_raw($parsed['url']);

    if ($platform === '' && $url === '') {
        return '';
    }

    return (string) wp_json_encode(['platform' => $platform, 'url' => $url]);
}

/**
 * Décode la valeur d'un champ image en { id, link }.
 *
 * Accepte le format JSON {id,link} ou, en repli, un simple ID de média (legacy).
 *
 * @param mixed $value
 * @return array{id:int, link:string}
 */
function em_wp_rubrique_image_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return ['id' => absint($value), 'link' => '', 'w' => 0, 'h' => 0, 'fx' => 50, 'fy' => 50];
    }

    return [
        'id'   => absint($decoded['id'] ?? 0),
        'link' => (string) ($decoded['link'] ?? ''),
        'w'    => max(0, (int) ($decoded['w'] ?? 0)),
        'h'    => max(0, (int) ($decoded['h'] ?? 0)),
        'fx'   => max(0, min(100, (int) ($decoded['fx'] ?? 50))),
        'fy'   => max(0, min(100, (int) ($decoded['fy'] ?? 50))),
    ];
}

/**
 * Sanitise un champ image : ID de média + lien (URL ou ancre), encodé en JSON.
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_image($value): string
{
    $parsed = em_wp_rubrique_image_value($value);
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
    ]);
}

/**
 * Sanitise une couleur hex (#rgb / #rrggbb), '' si invalide.
 */
function em_wp_field_sanitize_color($value): string
{
    $color = sanitize_text_field((string) $value);
    $sanitized = function_exists('sanitize_hex_color') ? sanitize_hex_color($color) : null;

    return is_string($sanitized) ? $sanitized : '';
}

/**
 * Sanitise un booléen tolérant ('1','true','on'…).
 */
function em_wp_field_sanitize_bool($value): bool
{
    if (is_bool($value)) {
        return $value;
    }

    return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
}

/**
 * Sanitise un entier (>= 0).
 */
function em_wp_field_sanitize_int($value): int
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
function em_wp_field_sanitize_repeater($value): array
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
