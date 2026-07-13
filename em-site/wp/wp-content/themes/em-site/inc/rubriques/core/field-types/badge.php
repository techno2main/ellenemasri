<?php
/**
 * Type de champ « Badge animé » (EM-SITE).
 *
 * Pilule façon hero mayami : texte + couleur de fond + couleur de texte, avec un
 * point « aqua » et une animation « wiggle ». Réutilisable dans n'importe quelle
 * rubrique. Le rendu reprend exactement le badge du site (.em-hero__badge).
 *
 * Valeur stockée (JSON) : { text, bg, ink } — `ink` = couleur du texte (et de la
 * bordure + ombre portée, comme sur le site).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type « animated_badge » (sans toucher au catalogue intégré).
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_site_field_types_animated_badge(array $types): array
{
    $types['animated_badge'] = [
        'label'    => __('Badge animé', 'em-site'),
        'default'  => '',
        'icon'     => 'dashicons-awards',
        'sanitize' => 'em_site_field_sanitize_animated_badge',
    ];

    return $types;
}
add_filter('em_site_field_types', 'em_site_field_types_animated_badge');

/**
 * Formes disponibles du badge (slug => libellé admin).
 *
 * @return array<string, string>
 */
function em_site_rubrique_badge_shapes(): array
{
    return [
        'pill'     => __('Pastille (arrondi total)', 'em-site'),
        'square'   => __('Carré / rectangle', 'em-site'),
        'triangle' => __('Triangle', 'em-site'),
    ];
}

/**
 * Animations disponibles du badge (slug => libellé admin).
 *
 * @return array<string, string>
 */
function em_site_rubrique_badge_anims(): array
{
    return [
        'wiggle' => __('Balancement', 'em-site'),
        'pulse'  => __('Pulsation', 'em-site'),
        'bounce' => __('Rebond', 'em-site'),
        'none'   => __('Aucune', 'em-site'),
    ];
}

/**
 * Valide une forme (repli « pill »).
 */
function em_site_rubrique_badge_valid_shape(string $shape): string
{
    return isset(em_site_rubrique_badge_shapes()[$shape]) ? $shape : 'pill';
}

/**
 * Valide une animation (repli « wiggle »).
 */
function em_site_rubrique_badge_valid_anim(string $anim): string
{
    return isset(em_site_rubrique_badge_anims()[$anim]) ? $anim : 'wiggle';
}

/**
 * Décode la valeur d'un Badge animé en { text, bg, ink, shape, anim, radius }.
 *
 * @param mixed $value
 * @return array{text:string, bg:string, ink:string, shape:string, anim:string, radius:int}
 */
function em_site_rubrique_animated_badge_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        $decoded = [];
    }

    return [
        'text'   => (string) ($decoded['text'] ?? ''),
        'bg'     => em_site_field_sanitize_color((string) ($decoded['bg'] ?? '')),
        'ink'    => em_site_field_sanitize_color((string) ($decoded['ink'] ?? '')),
        'shape'  => em_site_rubrique_badge_valid_shape((string) ($decoded['shape'] ?? 'pill')),
        'anim'   => em_site_rubrique_badge_valid_anim((string) ($decoded['anim'] ?? 'wiggle')),
        'radius' => max(0, min(40, (int) ($decoded['radius'] ?? 6))),
    ];
}

/**
 * Sanitise un Badge animé : texte + couleurs + forme + animation + arrondi.
 *
 * @param mixed $value
 */
function em_site_field_sanitize_animated_badge($value): string
{
    $badge = em_site_rubrique_animated_badge_value($value);
    $text = sanitize_text_field($badge['text']);

    if ($text === '' && $badge['bg'] === '' && $badge['ink'] === '') {
        return '';
    }

    return (string) wp_json_encode([
        'text'   => $text,
        'bg'     => $badge['bg'],
        'ink'    => $badge['ink'],
        'shape'  => $badge['shape'],
        'anim'   => $badge['anim'],
        'radius' => $badge['radius'],
    ]);
}

/**
 * HTML d'un Badge animé. Forme (pastille / carré / triangle), animation
 * (wiggle / pulse / bounce / aucune) et arrondi (carré) sont gérés via des
 * classes + variables CSS ; les couleurs sont surchargées inline.
 *
 * @param array{text:string, bg:string, ink:string, shape?:string, anim?:string, radius?:int} $badge
 */
function em_site_rubrique_animated_badge_html(array $badge): string
{
    $text = (string) ($badge['text'] ?? '');

    if ($text === '') {
        return '';
    }

    $bg = em_site_field_sanitize_color((string) ($badge['bg'] ?? ''));
    $ink = em_site_field_sanitize_color((string) ($badge['ink'] ?? ''));
    $shape = em_site_rubrique_badge_valid_shape((string) ($badge['shape'] ?? 'pill'));
    $anim = em_site_rubrique_badge_valid_anim((string) ($badge['anim'] ?? 'wiggle'));
    $radius = max(0, min(40, (int) ($badge['radius'] ?? 6)));

    $classes = ['em-rubrique__badge', 'em-rubrique__badge--shape-' . $shape];
    if ($anim !== 'none') {
        $classes[] = 'em-rubrique__badge--anim-' . $anim;
    }

    $style = '';
    if ($bg !== '') {
        $style .= '--em-rubrique-badge-bg:' . $bg . ';';
    }
    if ($ink !== '') {
        $style .= '--em-rubrique-badge-ink:' . $ink . ';';
    }
    if ($shape === 'square') {
        $style .= '--em-rubrique-badge-radius:' . $radius . 'px;';
    }

    return '<span class="' . esc_attr(implode(' ', $classes)) . '"' . ($style !== '' ? ' style="' . esc_attr($style) . '"' : '') . '>'
        . '<span class="em-rubrique__badge-dot" aria-hidden="true"></span>'
        . esc_html($text)
        . '</span>';
}
