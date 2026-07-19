<?php
/**
 * Helpers des champs texte composites (EM-SITE) :
 * « Texte + Image » (text_image) et « Texte + Texte » (text_text).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Famille des champs « texte » (un seul champ de saisie = le contenu affiché,
 * avec lien URL/ancre optionnel) : texte, texte enrichi, texte+image, texte+texte.
 */
function em_site_rubrique_field_is_text_family(string $type): bool
{
    return in_array($type, ['text', 'textarea', 'text_image', 'text_icon', 'icon_text', 'text_text'], true);
}

/**
 * Rend un texte échappé, enveloppé dans un lien (URL/ancre) s'il est fourni.
 * Lien externe → nouvel onglet ; ancre interne (#) → même page.
 */
function em_site_rubrique_text_link_wrap(string $text, string $link): string
{
    $html = esc_html($text);

    if ($link === '') {
        return $html;
    }

    $target = strpos($link, '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';

    return '<a class="em-rubrique__link" href="' . esc_url($link) . '"' . $target . '>' . $html . '</a>';
}

/**
 * Décode la valeur d'un champ « Texte » (ou « Texte enrichi ») en { text, link }.
 *
 * Rétro-compatible : une valeur en chaîne simple (ancien format) est traitée
 * comme le texte sans lien.
 *
 * @param mixed $value
 * @return array{text:string, link:string}
 */
function em_site_rubrique_text_value($value): array
{
    if (is_array($value)) {
        return ['text' => (string) ($value['text'] ?? ''), 'link' => (string) ($value['link'] ?? '')];
    }

    $str = (string) $value;
    $decoded = json_decode($str, true);

    if (is_array($decoded) && array_key_exists('text', $decoded)) {
        return ['text' => (string) ($decoded['text'] ?? ''), 'link' => (string) ($decoded['link'] ?? '')];
    }

    return ['text' => $str, 'link' => ''];
}

/**
 * Détermine si un texte contient déjà du HTML.
 */
function em_site_rubrique_text_has_html(string $text): bool
{
    return (bool) preg_match('/<[^>]+>/', $text);
}

/**
 * Nettoie/rend un texte enrichi pour affichage front.
 *
 * - Si HTML détecté: conserve uniquement les balises autorisées (wp_kses_post)
 * - Sinon: convertit les retours ligne en <br>
 */
function em_site_rubrique_textarea_render_html(string $text): string
{
    if ($text === '') {
        return '';
    }

    if (em_site_rubrique_text_has_html($text)) {
        return wp_kses_post($text);
    }

    return nl2br(esc_html($text));
}

/**
 * Prépare la valeur d'un textarea riche pour l'éditeur admin (contenteditable).
 */
function em_site_rubrique_textarea_editor_html(string $text): string
{
    return em_site_rubrique_textarea_render_html($text);
}

/**
 * Sanitise un champ « Texte » : texte + lien (URL/ancre). Stocké en chaîne
 * simple s'il n'y a pas de lien, sinon en JSON { text, link }.
 *
 * @param mixed $value
 */
function em_site_field_sanitize_text($value): string
{
    $v = em_site_rubrique_text_value($value);
    $text = sanitize_text_field($v['text']);
    $link = esc_url_raw($v['link']);

    if ($text === '' && $link === '') {
        return '';
    }

    return $link === '' ? $text : (string) wp_json_encode(['text' => $text, 'link' => $link]);
}

/**
 * Sanitise un champ « Texte enrichi » (HTML autorisé via wp_kses_post).
 *
 * @param mixed $value
 */
function em_site_field_sanitize_textarea($value): string
{
    $v = em_site_rubrique_text_value($value);
    $text = wp_kses_post($v['text']);
    $link = esc_url_raw($v['link']);

    if ($text === '' && $link === '') {
        return '';
    }

    return $link === '' ? $text : (string) wp_json_encode(['text' => $text, 'link' => $link]);
}

/**
 * Décode la valeur d'un champ « Texte + Image » en { text, link, style, image }.
 *
 * @param mixed $value
 * @return array{text:string, link:string, style:array{size:int,font:string,color:string}, image:array}
 */
function em_site_rubrique_text_image_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        $decoded = [];
    }

    return [
        'text'  => (string) ($decoded['text'] ?? ''),
        'link'  => (string) ($decoded['link'] ?? ''),
        'style' => em_site_rubrique_normalize_text_style($decoded['style'] ?? []),
        'image' => em_site_rubrique_image_value($decoded['image'] ?? ''),
    ];
}

/**
 * Sanitise un champ « Texte + Image » : texte + lien + style + image, en JSON.
 *
 * @param mixed $value
 */
function em_site_field_sanitize_text_image($value): string
{
    $ti = em_site_rubrique_text_image_value($value);
    $text = sanitize_text_field($ti['text']);
    $link = esc_url_raw($ti['link']);
    $image = em_site_rubrique_image_value(em_site_field_sanitize_image($ti['image']));

    if ($text === '' && (int) $image['id'] === 0 && $image['link'] === '') {
        return '';
    }

    return (string) wp_json_encode([
        'text'  => $text,
        'link'  => $link,
        'style' => em_site_rubrique_normalize_text_style($ti['style']),
        'image' => $image,
    ]);
}

/**
 * Décode la valeur d'un champ « Texte + Icône » en { text, link, icon, style }.
 *
 * @param mixed $value
 * @return array{text:string, link:string, icon:string, style:array{size:int,font:string,color:string,align:string}}
 */
function em_site_rubrique_text_icon_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        $decoded = [];
    }

    $icon = trim((string) ($decoded['icon'] ?? ''));
    if (!function_exists('em_site_dashicon_is_allowed') || !em_site_dashicon_is_allowed($icon)) {
        $icon = '';
    }

    return [
        'text'  => (string) ($decoded['text'] ?? ''),
        'link'  => (string) ($decoded['link'] ?? ''),
        'icon'  => $icon,
        'style' => em_site_rubrique_normalize_text_style($decoded['style'] ?? []),
    ];
}

/**
 * Sanitise un champ « Texte + Icône » : texte + lien + dashicon + style, en JSON.
 *
 * @param mixed $value
 */
function em_site_field_sanitize_text_icon($value): string
{
    $ti = em_site_rubrique_text_icon_value($value);
    $text = sanitize_text_field($ti['text']);
    $link = esc_url_raw($ti['link']);
    $icon = trim((string) $ti['icon']);

    if ($icon !== '' && function_exists('em_site_dashicon_is_allowed') && !em_site_dashicon_is_allowed($icon)) {
        $icon = '';
    }

    if ($text === '' && $link === '' && $icon === '') {
        return '';
    }

    return (string) wp_json_encode([
        'text'  => $text,
        'link'  => $link,
        'icon'  => $icon,
        'style' => em_site_rubrique_normalize_text_style($ti['style']),
    ]);
}

/**
 * Décode la valeur d'un champ « Texte + Texte » en { text, link, style, text2, link2, style2 }.
 *
 * @param mixed $value
 * @return array{text:string, link:string, style:array, text2:string, link2:string, style2:array}
 */
function em_site_rubrique_text_text_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        $decoded = [];
    }

    return [
        'text'   => (string) ($decoded['text'] ?? ''),
        'link'   => (string) ($decoded['link'] ?? ''),
        'style'  => em_site_rubrique_normalize_text_style($decoded['style'] ?? []),
        'text2'  => (string) ($decoded['text2'] ?? ''),
        'link2'  => (string) ($decoded['link2'] ?? ''),
        'style2' => em_site_rubrique_normalize_text_style($decoded['style2'] ?? []),
    ];
}

/**
 * Sanitise un champ « Texte + Texte » : deux textes + liens + styles, en JSON.
 *
 * @param mixed $value
 */
function em_site_field_sanitize_text_text($value): string
{
    $tt = em_site_rubrique_text_text_value($value);
    $text = sanitize_text_field($tt['text']);
    $text2 = sanitize_text_field($tt['text2']);

    if ($text === '' && $text2 === '') {
        return '';
    }

    return (string) wp_json_encode([
        'text'   => $text,
        'link'   => esc_url_raw($tt['link']),
        'style'  => em_site_rubrique_normalize_text_style($tt['style']),
        'text2'  => $text2,
        'link2'  => esc_url_raw($tt['link2']),
        'style2' => em_site_rubrique_normalize_text_style($tt['style2']),
    ]);
}
