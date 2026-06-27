<?php
/**
 * Helpers des champs texte composites (V4) :
 * « Texte + Image » (text_image) et « Texte + Texte » (text_text).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Décode la valeur d'un champ « Texte + Image » en { text, style, image }.
 *
 * @param mixed $value
 * @return array{text:string, style:array{size:int,font:string,color:string}, image:array}
 */
function em_wp_rubrique_text_image_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        $decoded = [];
    }

    return [
        'text'  => (string) ($decoded['text'] ?? ''),
        'style' => em_wp_rubrique_normalize_text_style($decoded['style'] ?? []),
        'image' => em_wp_rubrique_image_value($decoded['image'] ?? ''),
    ];
}

/**
 * Sanitise un champ « Texte + Image » : texte + style + image, encodé en JSON.
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_text_image($value): string
{
    $ti = em_wp_rubrique_text_image_value($value);
    $text = sanitize_text_field($ti['text']);
    $image = em_wp_rubrique_image_value(em_wp_field_sanitize_image($ti['image']));

    if ($text === '' && (int) $image['id'] === 0 && $image['link'] === '') {
        return '';
    }

    return (string) wp_json_encode([
        'text'  => $text,
        'style' => em_wp_rubrique_normalize_text_style($ti['style']),
        'image' => $image,
    ]);
}

/**
 * Décode la valeur d'un champ « Texte + Texte » en { text, style, text2, style2 }.
 *
 * @param mixed $value
 * @return array{text:string, style:array, text2:string, style2:array}
 */
function em_wp_rubrique_text_text_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        $decoded = [];
    }

    return [
        'text'   => (string) ($decoded['text'] ?? ''),
        'style'  => em_wp_rubrique_normalize_text_style($decoded['style'] ?? []),
        'text2'  => (string) ($decoded['text2'] ?? ''),
        'style2' => em_wp_rubrique_normalize_text_style($decoded['style2'] ?? []),
    ];
}

/**
 * Sanitise un champ « Texte + Texte » : deux textes + leurs styles, encodé en JSON.
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_text_text($value): string
{
    $tt = em_wp_rubrique_text_text_value($value);
    $text = sanitize_text_field($tt['text']);
    $text2 = sanitize_text_field($tt['text2']);

    if ($text === '' && $text2 === '') {
        return '';
    }

    return (string) wp_json_encode([
        'text'   => $text,
        'style'  => em_wp_rubrique_normalize_text_style($tt['style']),
        'text2'  => $text2,
        'style2' => em_wp_rubrique_normalize_text_style($tt['style2']),
    ]);
}
