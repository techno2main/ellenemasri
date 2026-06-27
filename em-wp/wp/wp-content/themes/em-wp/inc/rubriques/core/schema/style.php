<?php
/**
 * Style de contenu par champ (V4).
 *
 * Réglages propres à un champ (et non globaux à la rubrique) :
 *   - Texte : taille (px), police (typo), couleur — indépendants par champ.
 *   - Séparateur vide : hauteur (px).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Types de champ qui supportent un style de texte propre (taille/typo/couleur).
 *
 * @return array<int, string>
 */
function em_wp_rubrique_text_style_types(): array
{
    return ['text', 'textarea'];
}

/**
 * Le type de champ supporte-t-il un style de texte par champ ?
 */
function em_wp_rubrique_field_supports_text_style(string $type): bool
{
    return in_array($type, em_wp_rubrique_text_style_types(), true);
}

/**
 * Normalise un style de texte par champ (taille px, clé de police, couleur hex).
 *
 * @param mixed $raw
 * @return array{size:int, font:string, color:string}
 */
function em_wp_rubrique_normalize_text_style($raw): array
{
    $raw = is_array($raw) ? $raw : [];
    $font = sanitize_key((string) ($raw['font'] ?? ''));

    if ($font !== '' && !isset(em_wp_rubrique_font_choices()[$font])) {
        $font = '';
    }

    return [
        'size'  => max(0, min(200, (int) ($raw['size'] ?? 0))),
        'font'  => $font,
        'color' => em_wp_field_sanitize_color((string) ($raw['color'] ?? '')),
    ];
}

/**
 * CSS inline d'un style de texte par champ ('' si aucun réglage).
 *
 * @param array<string, mixed> $style
 */
function em_wp_rubrique_text_style_css(array $style): string
{
    $style = em_wp_rubrique_normalize_text_style($style);
    $css = '';

    if ($style['size'] > 0) {
        $css .= 'font-size:' . $style['size'] . 'px;';
    }

    if ($style['font'] !== '') {
        $stack = em_wp_rubrique_font_stack($style['font']);
        if ($stack !== '') {
            $css .= 'font-family:' . $stack . ';';
        }
    }

    if ($style['color'] !== '') {
        $css .= 'color:' . $style['color'] . ';';
    }

    return $css;
}

/**
 * Hauteur d'un séparateur vide en px (clampée 0..400).
 *
 * @param mixed $value
 */
function em_wp_rubrique_sep_blank_height($value): int
{
    return max(0, min(400, (int) $value));
}

/**
 * Sanitise une hauteur en px (séparateur vide) ; '' si nulle.
 *
 * Utilisé comme `sanitize` du type de champ `sep_blank` (voir builtin.php).
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_height($value): string
{
    $h = em_wp_rubrique_sep_blank_height($value);

    return $h > 0 ? (string) $h : '';
}
