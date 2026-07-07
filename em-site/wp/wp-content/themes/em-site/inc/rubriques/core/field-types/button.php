<?php
/**
 * Helpers du champ « Bouton » (EM-SITE).
 *
 * Le type « button » reste enregistré dans builtin.php ; seules les fonctions de
 * décodage/sanitisation vivent ici (fichier dédié pour rester sous 300 lignes).
 *
 * Valeur stockée (JSON) : { link, bg, text, ml, mr, shape, anim, radius }. Forme,
 * animation et arrondi réutilisent les options/ helpers du Badge animé (badge.php).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Décode la valeur d'un champ « Bouton ».
 *
 * Le LIBELLÉ du champ porte le texte du bouton ; la valeur stocke le lien, les
 * couleurs (fond + texte), les marges et la forme/animation/arrondi.
 *
 * @param mixed $value
 * @return array{link:string, bg:string, text:string, ml:int, mr:int, shape:string, anim:string, radius:int}
 */
function em_site_rubrique_button_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        $decoded = [];
    }

    return [
        'link'   => (string) ($decoded['link'] ?? ''),
        'bg'     => em_site_field_sanitize_color((string) ($decoded['bg'] ?? '')),
        'text'   => em_site_field_sanitize_color((string) ($decoded['text'] ?? '')),
        'ml'     => max(0, (int) ($decoded['ml'] ?? 0)),
        'mr'     => max(0, (int) ($decoded['mr'] ?? 0)),
        // Forme / animation / arrondi : mêmes options que le Badge animé (helpers
        // mutualisés). Défaut « pill » + aucune animation pour ne rien changer aux
        // boutons existants.
        'shape'  => em_site_rubrique_badge_valid_shape((string) ($decoded['shape'] ?? 'pill')),
        'anim'   => em_site_rubrique_badge_valid_anim((string) ($decoded['anim'] ?? 'none')),
        'radius' => max(0, min(40, (int) ($decoded['radius'] ?? 6))),
    ];
}

/**
 * Sanitise un champ « Bouton » : lien (URL/ancre) + couleurs + forme, en JSON.
 *
 * @param mixed $value
 */
function em_site_field_sanitize_button($value): string
{
    $parsed = em_site_rubrique_button_value($value);
    $link = esc_url_raw($parsed['link']);

    if ($link === '' && $parsed['bg'] === '' && $parsed['text'] === '' && $parsed['ml'] === 0 && $parsed['mr'] === 0) {
        return '';
    }

    return (string) wp_json_encode([
        'link'   => $link,
        'bg'     => $parsed['bg'],
        'text'   => $parsed['text'],
        'ml'     => $parsed['ml'],
        'mr'     => $parsed['mr'],
        'shape'  => $parsed['shape'],
        'anim'   => $parsed['anim'],
        'radius' => $parsed['radius'],
    ]);
}
