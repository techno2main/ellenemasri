<?php
/**
 * Rendu front des rubriques V4 — aperçu admin (?preview=slug) ET vrai front.
 *
 * Le moteur V4 pilote désormais le site public (em_wp_front_v4_live_enabled),
 * en plus de l'aperçu admin. Repli legacy automatique si une rubrique n'a pas
 * d'item V4 (le placeholder discret reste réservé à l'aperçu).
 *
 * Principe :
 *   - le CONTENANT (assets/front/css/rubriques-v4/*.css) gouverne largeur,
 *     responsive et top-bar collante ;
 *   - le CONTENU vient du moteur V4 (em_wp_rubrique_render) selon l'item branché
 *     au template (aperçu : slug prévisualisé ; live : template actif) ;
 *   - une rubrique sans item V4 : placeholder en aperçu, rendu legacy en live.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * L'aperçu V4 est-il actif ? (paramètre ?preview=slug + droits admin).
 *
 * em_wp_get_preview_template_slug() ne renvoie un slug que pour un utilisateur
 * pouvant gérer les options : l'aperçu reste invisible au public.
 */
function em_wp_front_v4_preview_active(): bool
{
    static $active = null;

    if ($active === null) {
        $active = function_exists('em_wp_get_preview_template_slug')
            && em_wp_get_preview_template_slug() !== '';
    }

    return (bool) $active;
}

/**
 * Slug du template prévisualisé.
 */
function em_wp_front_v4_preview_template(): string
{
    return function_exists('em_wp_get_preview_template_slug')
        ? (string) em_wp_get_preview_template_slug()
        : '';
}

/**
 * Le moteur V4 pilote-t-il le vrai front (site public) ?
 *
 * Filtrable pour revenir au rendu legacy si besoin
 * (`add_filter('em_wp_front_v4_live_enabled', '__return_false')`).
 */
function em_wp_front_v4_live_enabled(): bool
{
    return (bool) apply_filters('em_wp_front_v4_live_enabled', true);
}

/**
 * Le rendu V4 est-il actif pour la requête front courante ?
 *
 * Vrai en aperçu (admin, ?preview=slug) OU sur le vrai front quand V4 pilote le
 * site. Jamais en admin.
 */
function em_wp_front_v4_active(): bool
{
    if (em_wp_front_v4_preview_active()) {
        return true;
    }

    if (is_admin()) {
        return false;
    }

    return em_wp_front_v4_live_enabled();
}

/**
 * Slug du template à rendre en V4 : l'aperçu prime, sinon le template live actif.
 */
function em_wp_front_v4_template(): string
{
    return function_exists('em_wp_get_active_template_slug')
        ? (string) em_wp_get_active_template_slug()
        : em_wp_front_v4_preview_template();
}

/**
 * Met en file le contenant V4 (design-system) + le CSS du slider, en aperçu.
 */
function em_wp_front_v4_preview_enqueue(): void
{
    if (!em_wp_front_v4_active()) {
        return;
    }

    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();

    // Contenant + composants V4 : on met en file tout le dossier rubriques-v4/
    // (ajouter un composant = nouveau .css auto-chargé). layout d'abord.
    $ds_dir = '/assets/front/css/rubriques-v4';
    $files = glob($theme_dir . $ds_dir . '/*.css') ?: [];
    usort($files, static function (string $a, string $b): int {
        // layout.css en premier (base du contenant), puis ordre alphabétique.
        $pa = basename($a) === 'layout.css' ? '0' : basename($a);
        $pb = basename($b) === 'layout.css' ? '0' : basename($b);
        return strcmp($pa, $pb);
    });

    $deps = [];
    foreach ($files as $file) {
        $name = basename($file, '.css');
        $handle = 'em-wp-rubriques-v4-' . sanitize_html_class($name);
        wp_enqueue_style(
            $handle,
            $theme_uri . $ds_dir . '/' . basename($file),
            $deps,
            (string) filemtime($file)
        );
        $deps[] = $handle;
    }

    $slider_rel = '/assets/front/css/modules/slider/mayami/slider.css';
    if (file_exists($theme_dir . $slider_rel)) {
        wp_enqueue_style(
            'em-wp-slider-mayami',
            $theme_uri . $slider_rel,
            [],
            (string) filemtime($theme_dir . $slider_rel)
        );
    }

    // Système d'ouverture des plateformes (parité site réel) : le moteur est le
    // même stream.js que le front legacy ; il n'est pas chargé par la section
    // legacy en aperçu V4, on l'enfile donc ici. Dépend de em-wp-theme (theme.js)
    // pour emWpScrollToElement.
    $stream_js_rel = '/assets/front/js/modules/stream/stream.js';
    if (file_exists($theme_dir . $stream_js_rel)) {
        wp_enqueue_script(
            'em-wp-stream',
            $theme_uri . $stream_js_rel,
            ['em-wp-theme'],
            (string) filemtime($theme_dir . $stream_js_rel),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'em_wp_front_v4_preview_enqueue');

/**
 * Ancre (#id) d'une section en aperçu V4 : ancre personnalisée de l'item effectif
 * si définie (champ « #ancre » de l'en-tête), sinon la carte d'ancres par défaut.
 *
 * Pour HEADER (composite), l'ancre provient de l'item HERO branché.
 */
function em_wp_front_v4_section_anchor(string $module_slug, string $template): string
{
    $type = $module_slug;
    $item = '';

    if ($module_slug === 'header') {
        $type = function_exists('em_wp_admin_header_part_type_slug') ? em_wp_admin_header_part_type_slug('hero') : '';
        $item = ($type !== '' && function_exists('em_wp_admin_header_effective_item'))
            ? em_wp_admin_header_effective_item($template, 'hero')
            : '';
    } elseif (function_exists('em_wp_rubrique_type_exists') && em_wp_rubrique_type_exists($module_slug) && function_exists('em_wp_rubrique_resolve_item_slug')) {
        $item = em_wp_rubrique_resolve_item_slug($module_slug, ['template' => $template]);
    }

    if ($type !== '' && $item !== '' && function_exists('em_wp_v4_get_item')) {
        $custom = (string) (em_wp_v4_get_item($type, $item)['anchor'] ?? '');
        if ($custom !== '') {
            return $custom;
        }
    }

    return function_exists('em_wp_landing_section_anchor_id')
        ? em_wp_landing_section_anchor_id($module_slug)
        : $module_slug;
}

/**
 * Rend une rubrique du squelette via la V4, dans le contenant (aperçu).
 *
 * @return bool true si la rubrique a été prise en charge (aperçu V4).
 */
function em_wp_front_v4_render_module(string $module_slug): bool
{
    if (!em_wp_front_v4_active()) {
        return false;
    }

    $module_slug = sanitize_key($module_slug);

    // Respecte la visibilité « masquée sur le site », comme le rendu live.
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility($module_slug)) {
        return true;
    }

    $template = em_wp_front_v4_template();
    $anchor = em_wp_front_v4_section_anchor($module_slug, $template);

    // Réinitialise l'accumulateur de players AVANT le rendu : les cartes plateforme
    // de cette section y enregistrent leur embed (système d'ouverture du site réel).
    if (function_exists('em_wp_v4_players_reset')) {
        em_wp_v4_players_reset();
    }

    // HEADER : section COMPOSITE (HERO seul ou HERO + SLIDER selon la matrice),
    // rendue par le même moteur que l'aperçu back → parité front/back.
    if ($module_slug === 'header' && function_exists('em_wp_admin_header_composite_html')) {
        $html = em_wp_admin_header_composite_html($template);
    } else {
        $html = (function_exists('em_wp_rubrique_type_exists') && em_wp_rubrique_type_exists($module_slug))
            ? em_wp_rubrique_render($module_slug, ['template' => $template])
            : '';
    }

    if (trim($html) === '') {
        // Mode V4 strict (preview + live) : pas de repli legacy.
        // Une rubrique sans item V4 reste explicitement visible via placeholder.
        em_wp_front_v4_render_placeholder($module_slug, $anchor);
        return true;
    }

    // Players inline (#player-*-{slug}) accumulés pendant le rendu des cartes.
    // On les injecte À L'INTÉRIEUR du conteneur .em-rubrique (avant sa balise
    // fermante) et non en frère : ils héritent ainsi du fond + cadre de la
    // section, exactement comme la section Stream du site réel (sinon ils
    // s'ouvrent hors de la zone colorée, sur le fond de page).
    $players_html = function_exists('em_wp_v4_players_html') ? em_wp_v4_players_html() : '';
    if ($players_html !== '') {
        $injected = preg_replace('/<\/([a-zA-Z0-9]+)>\s*$/', $players_html . '</$1>', $html, 1, $count);
        $html = ($count === 1 && $injected !== null) ? $injected : ($html . $players_html);
    }

    $tag = $module_slug === 'footer' ? 'footer' : 'section';
    $class = 'emv4-section emv4-section--' . sanitize_html_class($module_slug);

    echo '<' . $tag // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        . ' id="' . esc_attr($anchor) . '"'
        . ' class="' . esc_attr($class) . '"'
        . ' data-emv4-rubrique="' . esc_attr($module_slug) . '">'
        . $html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML moteur V4 + players.
        . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    return true;
}

/**
 * Placeholder discret pour une rubrique sans item V4 (ou type non V4, ex. header
 * composite — traité à une étape ultérieure).
 */
function em_wp_front_v4_render_placeholder(string $module_slug, string $anchor): void
{
    $label = strtoupper($module_slug);
    $tag = $module_slug === 'footer' ? 'footer' : 'section';

    echo '<' . $tag // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        . ' id="' . esc_attr($anchor) . '"'
        . ' class="emv4-section emv4-section--placeholder emv4-section--' . esc_attr(sanitize_html_class($module_slug)) . '"'
        . ' style="min-height:120px;display:flex;align-items:center;justify-content:center;background:#1f2937;color:#9ca3af;font:600 13px/1.4 system-ui,sans-serif;letter-spacing:.08em;">'
        . esc_html($label . ' — aperçu V4 à venir')
        . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
