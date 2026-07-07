<?php
/**
 * Helpers des champs décoratifs (V4) : flèches haut/bas avec couleur + ancre.
 *
 * Les flèches servent de navigation entre rubriques. Leur valeur est un JSON
 * { color, link } : couleur du glyphe + ancre/URL cible.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Types de flèches (navigation par ancre). @return array<int, string>
 */
function em_wp_rubrique_arrow_types(): array
{
    return ['arrow_up', 'arrow_down'];
}

/**
 * Décode la valeur d'une flèche en { color, link }.
 *
 * Accepte le JSON {color,link} ou, en repli, une simple couleur (legacy).
 *
 * @param mixed $value
 * @return array{color:string, link:string}
 */
function em_wp_rubrique_arrow_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return ['color' => em_wp_field_sanitize_color((string) $value), 'link' => ''];
    }

    return [
        'color' => em_wp_field_sanitize_color((string) ($decoded['color'] ?? '')),
        'link'  => (string) ($decoded['link'] ?? ''),
    ];
}

/**
 * Sanitise une flèche : couleur (hex) + lien (URL ou ancre), encodé en JSON.
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_arrow($value): string
{
    $parsed = em_wp_rubrique_arrow_value($value);
    $color = $parsed['color'];
    $link = esc_url_raw($parsed['link']);

    if ($color === '' && $link === '') {
        return '';
    }

    return (string) wp_json_encode(['color' => $color, 'link' => $link]);
}
