<?php
/**
 * Style de contenu par champ (EM-SITE).
 *
 * Réglages propres à un champ (et non globaux à la rubrique) :
 *   - Texte : taille (px), police (typo), couleur — indépendants par champ.
 *   - Séparateur vide : hauteur (px).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Types de champ qui supportent un style de texte propre (taille/typo/couleur).
 *
 * @return array<int, string>
 */
function em_site_rubrique_text_style_types(): array
{
    return ['text', 'textarea'];
}

/**
 * Le type de champ supporte-t-il un style de texte par champ ?
 */
function em_site_rubrique_field_supports_text_style(string $type): bool
{
    return in_array($type, em_site_rubrique_text_style_types(), true);
}

/**
 * Normalise un style de texte par champ (taille px, clé de police, couleur hex).
 *
 * @param mixed $raw
 * @return array{size:int, font:string, color:string, align:string}
 */
function em_site_rubrique_normalize_text_style($raw): array
{
    $raw = is_array($raw) ? $raw : [];
    $font = sanitize_key((string) ($raw['font'] ?? ''));
    $align = sanitize_key((string) ($raw['align'] ?? ''));

    if (!in_array($align, ['left', 'center', 'right', 'justify'], true)) {
        $align = '';
    }

    if ($font !== '' && !isset(em_site_rubrique_font_choices()[$font])) {
        $font = '';
    }

    return [
        'size'  => max(0, min(200, (int) ($raw['size'] ?? 0))),
        'font'  => $font,
        'color' => em_site_field_sanitize_color((string) ($raw['color'] ?? '')),
        'align' => $align,
    ];
}

/**
 * CSS inline d'un style de texte par champ ('' si aucun réglage).
 *
 * @param array<string, mixed> $style
 */
function em_site_rubrique_text_style_css(array $style): string
{
    $style = em_site_rubrique_normalize_text_style($style);
    $css = '';

    if ($style['size'] > 0) {
        $size = (int) $style['size'];
        if ($size >= 28) {
            // Grands titres : taille FLUIDE. On garde la taille choisie comme
            // plafond (desktop) et on réduit progressivement jusqu'à un plancher
            // en mobile (interpolation linéaire entre 360px et 1100px de large).
            $min = max(20, (int) round($size * 0.5));
            $css .= 'font-size:clamp(' . $min . 'px, calc(' . $min . 'px + (' . $size . ' - ' . $min . ') * ((100vw - 360px) / 740)), ' . $size . 'px);';
        } else {
            $css .= 'font-size:' . $size . 'px;';
        }
    }

    if ($style['font'] !== '') {
        $stack = em_site_rubrique_font_stack($style['font']);
        if ($stack !== '') {
            $css .= 'font-family:' . $stack . ';';
        }
    }

    if ($style['color'] !== '') {
        $css .= 'color:' . $style['color'] . ';';
    }

    if ($style['align'] !== '') {
        $css .= 'text-align:' . $style['align'] . ';';
    }

    return $css;
}

/**
 * Modes de POSITION d'une image de fond de rubrique (clé => libellé).
 *
 * @return array<string, string>
 */
function em_site_rubrique_bg_position_choices(): array
{
    return [
        'cover'    => __('Étirée (remplir le cadre)', 'em-site'),
        'contain'  => __('Adaptée (entière visible)', 'em-site'),
        'center'   => __('Centrée (taille réelle)', 'em-site'),
        'repeat'   => __('Mosaïque (répétée)', 'em-site'),
        'repeat-x' => __('Répétée horizontalement', 'em-site'),
        'repeat-y' => __('Répétée verticalement', 'em-site'),
    ];
}

/**
 * Traduit un mode de position en propriétés CSS de fond.
 *
 * @return array{size:string, repeat:string, position:string}
 */
function em_site_rubrique_bg_position_css(string $pos): array
{
    switch ($pos) {
        case 'contain':
            return ['size' => 'contain', 'repeat' => 'no-repeat', 'position' => 'center'];
        case 'center':
            return ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'center'];
        case 'repeat':
            return ['size' => 'auto', 'repeat' => 'repeat', 'position' => 'top left'];
        case 'repeat-x':
            return ['size' => 'auto', 'repeat' => 'repeat-x', 'position' => 'top center'];
        case 'repeat-y':
            return ['size' => 'auto', 'repeat' => 'repeat-y', 'position' => 'center left'];
        case 'cover':
        default:
            return ['size' => 'cover', 'repeat' => 'no-repeat', 'position' => 'center'];
    }
}

/**
 * Hauteur d'un séparateur vide en px (clampée 0..400).
 *
 * @param mixed $value
 */
function em_site_rubrique_sep_blank_height($value): int
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
function em_site_field_sanitize_height($value): string
{
    $h = em_site_rubrique_sep_blank_height($value);

    return $h > 0 ? (string) $h : '';
}
