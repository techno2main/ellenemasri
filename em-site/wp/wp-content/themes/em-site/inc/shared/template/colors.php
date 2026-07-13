<?php
/**
 * Couleurs dédiées par template (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Couleur de repli si invalide.
 */
function em_site_template_fallback_color(): string
{
    return '#2d1454';
}

/**
 * Palette par défaut selon le slug connu.
 */
function em_site_template_default_color_for_slug(string $slug): string
{
    $known = [
        'mayami' => '#2d1454',
        'client' => '#1a4d7c',
    ];

    $slug = em_site_template_sanitize_slug($slug);

    if (isset($known[$slug])) {
        return $known[$slug];
    }

    $palette = ['#0f766e', '#9a3412', '#6b21a8', '#b45309', '#334155', '#be123c'];
    $index = abs(crc32($slug)) % count($palette);

    return $palette[$index];
}

/**
 * Sanitize couleur hex (#rgb ou #rrggbb).
 */
function em_site_template_sanitize_color(string $color, string $fallback_slug = ''): string
{
    $sanitized = sanitize_hex_color($color);

    if ($sanitized !== null && $sanitized !== '') {
        return $sanitized;
    }

    if ($fallback_slug !== '') {
        return em_site_template_default_color_for_slug($fallback_slug);
    }

    return em_site_template_fallback_color();
}

/**
 * Couleur enregistrée d'un template.
 */
function em_site_get_template_color(string $slug): string
{
    $slug = em_site_template_sanitize_slug($slug);
    $template = em_site_template_get($slug);

    if ($template === null) {
        return em_site_template_default_color_for_slug($slug);
    }

    $color = sanitize_hex_color((string) ($template['color'] ?? ''));

    if ($color !== null && $color !== '') {
        return $color;
    }

    return em_site_template_default_color_for_slug($slug);
}

/**
 * Convertit #rrggbb en rgba().
 */
function em_site_template_color_rgba(string $hex, float $alpha): string
{
    $hex = ltrim(em_site_template_sanitize_color($hex), '#');

    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    if (strlen($hex) !== 6) {
        return 'rgba(45, 20, 84, ' . $alpha . ')';
    }

    $red = hexdec(substr($hex, 0, 2));
    $green = hexdec(substr($hex, 2, 2));
    $blue = hexdec(substr($hex, 4, 2));

    return 'rgba(' . $red . ', ' . $green . ', ' . $blue . ', ' . $alpha . ')';
}

/**
 * Assombrit une couleur hex (facteur 0–1).
 */
function em_site_template_color_darken(string $hex, float $factor = 0.82): string
{
    $hex = ltrim(em_site_template_sanitize_color($hex), '#');

    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    if (strlen($hex) !== 6) {
        return em_site_template_fallback_color();
    }

    $red = (int) round(hexdec(substr($hex, 0, 2)) * $factor);
    $green = (int) round(hexdec(substr($hex, 2, 2)) * $factor);
    $blue = (int) round(hexdec(substr($hex, 4, 2)) * $factor);

    return sprintf('#%02x%02x%02x', min(255, $red), min(255, $green), min(255, $blue));
}

/**
 * Choisit une couleur palette non utilisée (nouveau template).
 */
function em_site_template_suggest_new_color(): string
{
    $used = [];

    foreach (em_site_template_registry() as $slug => $definition) {
        $used[] = em_site_get_template_color((string) $slug);
    }

    $palette = ['#2d1454', '#1a4d7c', '#0f766e', '#9a3412', '#6b21a8', '#b45309', '#334155', '#be123c'];

    foreach ($palette as $color) {
        if (!in_array($color, $used, true)) {
            return $color;
        }
    }

    return em_site_template_default_color_for_slug('template-' . wp_generate_password(4, false, false));
}

/**
 * Met à jour la couleur d'un template.
 *
 * @return true|WP_Error
 */
function em_site_template_set_color(string $slug, string $color)
{
    $slug = em_site_template_sanitize_slug($slug);
    $registry = em_site_template_registry();

    if (!isset($registry[$slug])) {
        return new WP_Error('em_site_template_missing', __('Template introuvable.', 'em-site'));
    }

    $registry[$slug]['color'] = em_site_template_sanitize_color($color, $slug);

    if (!em_site_template_save_registry($registry)) {
        return new WP_Error('em_site_template_save_failed', __('Impossible d’enregistrer la couleur.', 'em-site'));
    }

    return true;
}

/**
 * Texte lisible sur fond de couleur template (onglets admin).
 */
function em_site_template_contrast_text_color(string $hex): string
{
    $hex = ltrim(em_site_template_sanitize_color($hex), '#');

    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    if (strlen($hex) !== 6) {
        return '#ffffff';
    }

    $red = hexdec(substr($hex, 0, 2));
    $green = hexdec(substr($hex, 2, 2));
    $blue = hexdec(substr($hex, 4, 2));
    $luminance = (0.299 * $red + 0.587 * $green + 0.114 * $blue) / 255;

    return $luminance > 0.58 ? '#100421' : '#ffffff';
}

/**
 * Variables CSS inline pour un onglet template (couleur dédiée).
 */
function em_site_admin_template_tab_style_attr(string $slug): string
{
    $accent = em_site_get_template_color($slug);
    $text = em_site_template_contrast_text_color($accent);

    return sprintf(
        '--em-site-template-accent:%1$s;--em-site-template-text:%2$s;',
        esc_attr($accent),
        esc_attr($text)
    );
}
