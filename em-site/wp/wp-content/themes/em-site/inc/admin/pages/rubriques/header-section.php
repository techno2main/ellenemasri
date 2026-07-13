<?php
/**
 * Section HEADER du squelette (composite HERO + SLIDER).
 *
 * HEADER n'est PAS une rubrique EM-SITE : c'est une « section » du squelette qui
 * compose une rubrique HERO et, optionnellement, une rubrique SLIDER (toutes
 * deux gérées comme des rubriques EM-SITE indépendantes). Avant d'afficher les items
 * disponibles, l'admin choisit une MATRICE (HERO seul / HERO + SLIDER) et, si
 * les deux, leur POSITION. La configuration est persistée par template dans
 * l'option `em_site_header_<template>`.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug de la section HEADER dans le squelette.
 */
function em_site_admin_header_section_slug(): string
{
    return 'header';
}

/**
 * Slug du type de rubrique EM-SITE jouant le rôle « hero » ou « slider ».
 *
 * Détecté dynamiquement parmi les types enregistrés (l'admin a pu les nommer
 * « hero »/« heros »/« slider »…). Renvoie '' si aucun type ne correspond.
 */
function em_site_admin_header_part_type_slug(string $keyword): string
{
    $keyword = $keyword === 'slider' ? 'slider' : 'hero';
    $candidates = $keyword === 'hero'
        ? ['header', 'hero', 'heros']
        : ['sliders', 'slider'];

    if (!function_exists('em_site_rubrique_type_registry')) {
        return (string) ($candidates[0] ?? '');
    }

    $registry = em_site_rubrique_type_registry();
    $slugs = array_map('strval', array_keys($registry));

    // 1) Candidats explicites (compat historique), puis mot-clé exact.
    foreach ($candidates as $candidate) {
        if (in_array($candidate, $slugs, true)) {
            return $candidate;
        }
    }

    if (in_array($keyword, $slugs, true)) {
        return (string) $keyword;
    }

    // 2) Slug contenant le mot-clé.
    foreach ($slugs as $slug) {
        if (strpos((string) $slug, $keyword) !== false) {
            return (string) $slug;
        }
    }

    // 3) Libellé contenant le mot-clé : indispensable car renommer une rubrique
    // change le LIBELLÉ mais pas le SLUG (ex. HERO créé puis renommé garde slug
    // « header »). Le rôle HERO/SLIDER se reconnaît alors au libellé saisi.
    foreach ($registry as $slug => $def) {
        $label = strtolower((string) ($def['label'] ?? '') . ' ' . (string) ($def['label_plural'] ?? ''));

        if ($label !== '' && strpos($label, $keyword) !== false) {
            return (string) $slug;
        }
    }

    return '';
}

/**
 * Nom d'option de la config HEADER pour un template.
 */
function em_site_admin_header_section_option_name(string $template): string
{
    return 'em_site_header_' . sanitize_key($template);
}

/**
 * Type EM-SITE dédié au catalogue des assemblages HEADER.
 */
function em_site_admin_header_catalog_type_slug(): string
{
    return 'headers';
}

/**
 * Option dédiée à la config d'un item HEADER (assemblage HERO/SLIDER + fond).
 */
function em_site_admin_header_item_config_option_name(string $header_item_slug): string
{
    return 'em_site_header_item_cfg_' . sanitize_key($header_item_slug);
}

/**
 * Prépare le HTML d'une partie HERO/SLIDER pour la preview HEADER.
 * - Remplace <footer class="em-rubrique"> par <div> (pas de sémantique footer dans une colonne).
 * - Retire fond + padding rubrique (hérités du shell ou configurés sur l'item SLIDER).
 */
function em_site_admin_header_preview_part_content(string $html): string
{
    if ($html === '') {
        return '';
    }

    if (!preg_match('/<footer\b([^>]*)>(.*)<\/footer>\s*$/is', $html, $match)) {
        return $html;
    }

    $attrs = (string) $match[1];
    $inner = (string) $match[2];

    if (preg_match('/\bstyle="([^"]*)"/i', $attrs, $style_match)) {
        $style = (string) $style_match[1];
        $style = preg_replace('/--em-rubrique-bg(?:-image|-size|-repeat|-position|-opacity|-transform)?\s*:[^;"]*;?/i', '', $style) ?? '';
        $style = preg_replace('/--em-rubrique-p[tlrb]\s*:[^;"]*;?/i', '', $style) ?? '';
        $style = preg_replace('/\s*;+\s*/', ';', $style) ?? '';
        $style = trim($style, '; ');
        if ($style !== '') {
            $style .= ';';
        }
        $style .= '--em-rubrique-bg:transparent;--em-rubrique-bg-image:none;--em-rubrique-pt:0;--em-rubrique-pb:0;--em-rubrique-pl:0;--em-rubrique-pr:0;';
        $attrs = (string) preg_replace('/\bstyle="[^"]*"/i', 'style="' . $style . '"', $attrs, 1);
    }

    return '<div' . $attrs . '>' . $inner . '</div>';
}

/**
 * Colonne preview HEADER (#hero ou #slider).
 */
function em_site_admin_header_preview_col(string $part, string $content): string
{
    if (trim($content) === '') {
        return '';
    }

    $part = $part === 'slider' ? 'slider' : 'hero';
    $id = $part === 'slider' ? 'slider' : 'hero';

    return '<div id="' . esc_attr($id) . '" class="em-header-shell__col em-header-shell__col--' . esc_attr($part) . '">'
        . $content
        . '</div>';
}

/**
 * Variables CSS du shell HEADER pour la preview admin (rubrique HEADER uniquement).
 *
 * @param array<string, mixed> $config
 * @return array<int, string>
 */
function em_site_admin_header_shell_style_vars(array $config): array
{
    if (!function_exists('em_site_header_bg_position_css')) {
        $compose = get_template_directory() . '/inc/front/modules/header/compose.php';
        if (is_readable($compose)) {
            require_once $compose;
        }
    }

    $appearance = is_array($config['appearance'] ?? null) ? $config['appearance'] : [];
    if (function_exists('em_site_admin_header_appearance_normalize')) {
        $appearance = em_site_admin_header_appearance_normalize($appearance);
    }

    $appearance_bg = sanitize_hex_color((string) ($appearance['bg'] ?? '')) ?: '';
    $bg = $appearance_bg !== '' ? $appearance_bg : 'transparent';

    $vars = [
        '--em-rubrique-bg:' . $bg,
        '--em-rubrique-pt:' . max(0, (int) ($appearance['pt'] ?? 0)) . 'px',
        '--em-rubrique-pb:' . max(0, (int) ($appearance['pb'] ?? 0)) . 'px',
        '--em-rubrique-pl:' . max(0, (int) ($appearance['pl'] ?? 0)) . 'px',
        '--em-rubrique-pr:' . max(0, (int) ($appearance['pr'] ?? 0)) . 'px',
        '--em-rubrique-bg-image:none',
    ];

    $bg_image_id = (int) ($appearance['bg_image_id'] ?? 0);
    $bg_url = $bg_image_id > 0 ? (string) wp_get_attachment_image_url($bg_image_id, 'full') : '';

    if ($bg_url !== '' && function_exists('em_site_header_bg_position_css')) {
        $bg_pos = em_site_header_bg_position_css((string) ($appearance['bg_image_pos'] ?? 'cover'));
        $opacity = max(0, min(100, (int) ($appearance['bg_image_opacity'] ?? 100)));
        $vars = array_filter($vars, static fn(string $line): bool => strpos($line, '--em-rubrique-bg-image:') !== 0);
        $vars[] = '--em-rubrique-bg-image:url(' . esc_url_raw($bg_url) . ')';
        $vars[] = '--em-rubrique-bg-size:' . $bg_pos['size'];
        $vars[] = '--em-rubrique-bg-repeat:' . $bg_pos['repeat'];
        $vars[] = '--em-rubrique-bg-position:' . $bg_pos['position'];
        $vars[] = '--em-rubrique-bg-opacity:' . round($opacity / 100, 2);
        $vars[] = '--em-rubrique-bg-transform:' . (!empty($appearance['bg_image_mirror']) ? 'scaleX(-1)' : 'none');
    }

    return array_values($vars);
}

/**
 * Padding du conteneur grille preview (HEADER appearance + défauts front-like).
 *
 * @param array<string, mixed> $appearance
 */
function em_site_admin_header_preview_inner_style_vars(array $appearance): string
{
    if (function_exists('em_site_admin_header_appearance_normalize')) {
        $appearance = em_site_admin_header_appearance_normalize($appearance);
    }

    $pt = max(0, (int) ($appearance['pt'] ?? 0));
    $pb = max(0, (int) ($appearance['pb'] ?? 0));
    $pl = max(0, (int) ($appearance['pl'] ?? 0));
    $pr = max(0, (int) ($appearance['pr'] ?? 0));

    if ($pt === 0) {
        $pt = 44;
    }
    if ($pb === 0) {
        $pb = 68;
    }
    if ($pl === 0) {
        $pl = 24;
    }
    if ($pr === 0) {
        $pr = 24;
    }

    return sprintf(
        'padding:%dpx %dpx %dpx %dpx;gap:28px;box-sizing:border-box;',
        $pt,
        $pr,
        $pb,
        $pl
    );
}

/**
 * Colonnes CSS preview HEADER — même logique que le front (em_site_header_ratio_columns).
 */
function em_site_admin_header_preview_grid_columns(string $ratio, bool $slider_left): string
{
    if (function_exists('em_site_header_ratio_columns')) {
        return em_site_header_ratio_columns($ratio, $slider_left);
    }

    return em_site_admin_header_ratio_columns($ratio, $slider_left);
}

/**
 * @deprecated Utiliser em_site_admin_header_preview_part_content().
 */
function em_site_admin_header_embed_part_html(string $html): string
{
    return em_site_admin_header_preview_part_content($html);
}

/**
 * Enveloppe le markup composite HEADER (aligné sur inc/front/modules/header/render.php).
 *
 * @param array<int, string>|string $shell_style
 */
function em_site_admin_header_wrap_composite_html($shell_style, string $inner_class, string $cols, string $inner_html, array $appearance = []): string
{
    if ($inner_html === '') {
        return '';
    }

    $shell_attr = is_array($shell_style)
        ? esc_attr(implode(';', $shell_style))
        : esc_attr((string) $shell_style);

    $box = em_site_admin_header_preview_inner_style_vars($appearance);
    $is_pair = strpos($inner_class, 'is-pair') !== false;
    $inner_style = '--em-header-ratio-cols:' . esc_attr($cols) . ';' . $box;
    if ($is_pair) {
        $inner_style .= 'display:grid!important;grid-template-columns:' . esc_attr($cols) . '!important;';
    } else {
        $inner_style .= 'grid-template-columns:' . esc_attr($cols) . ';';
    }

    return '<section class="em-section em-section--header" data-em-rubrique="header">'
        . '<div class="em-rubrique em-header-shell" style="' . $shell_attr . '">'
        . '<div class="' . esc_attr($inner_class) . '" data-em-header-ratio="' . esc_attr($cols) . '" style="' . esc_attr($inner_style) . '">'
        . $inner_html
        . '</div></div></section>';
}

/**
 * Rend un preview HEADER en réutilisant la logique front.
 *
 * @param array<string, mixed> $cfg
 */
function em_site_admin_header_preview_html_from_config(array $cfg): string
{
    if (!function_exists('em_site_header_hero_item_slug') || !function_exists('em_site_header_slider_item_slug')) {
        $compose = get_template_directory() . '/inc/front/modules/header/compose.php';
        if (is_readable($compose)) {
            require_once $compose;
        }
    }
    if (!function_exists('em_site_render_header_hero_html') || !function_exists('em_site_render_header_slider_html')) {
        $hero_render = get_template_directory() . '/inc/front/modules/hero/render.php';
        $slider_render = get_template_directory() . '/inc/front/modules/slider/render.php';
        if (is_readable($hero_render)) {
            require_once $hero_render;
        }
        if (is_readable($slider_render)) {
            require_once $slider_render;
        }
    }
    if (!function_exists('em_site_header_ratio_columns')) {
        return '';
    }

    $matrix = sanitize_key((string) ($cfg['matrix'] ?? 'hero'));
    $position = sanitize_key((string) ($cfg['position'] ?? 'hero_left'));
    $slider_left = $position === 'slider_left';

    $hero_item_slug = sanitize_key((string) ($cfg['hero'] ?? ''));
    if ($hero_item_slug === '') {
        $hero_item_slug = em_site_header_hero_item_slug($cfg);
    }
    $hero_item = $hero_item_slug !== '' && function_exists('em_site_header_hero_item') ? em_site_header_hero_item($hero_item_slug) : [];
    $hero_content = is_array($hero_item['content'] ?? null) ? $hero_item['content'] : [];
    $hero_html = ($matrix !== 'slider' && $hero_item_slug !== '')
        ? em_site_admin_header_preview_part_content(em_site_render_header_hero_html($hero_content, $hero_item_slug, true))
        : '';

    $slider_item_slug = sanitize_key((string) ($cfg['slider'] ?? ''));
    if ($slider_item_slug === '') {
        $slider_item_slug = em_site_header_slider_item_slug($cfg);
    }
    $slider_html = ($matrix !== 'hero' && $slider_item_slug !== '')
        ? em_site_admin_header_preview_part_content(em_site_render_header_slider_html($slider_item_slug))
        : '';

    $has_hero = trim($hero_html) !== '';
    $has_slider = trim($slider_html) !== '';
    if (!$has_hero && !$has_slider) {
        return '';
    }

    $is_pair = $has_hero && $has_slider;
    $cols = $is_pair
        ? em_site_admin_header_preview_grid_columns((string) ($cfg['ratio'] ?? '60-40'), $slider_left)
        : 'minmax(0,1fr)';
    $shell_style = em_site_admin_header_shell_style_vars($cfg);
    $inner_class = 'em-header-shell__inner';
    if ($slider_left) {
        $inner_class .= ' is-slider-first';
    }
    $inner_class .= $is_pair ? ' is-pair' : ' is-single';

    $inner = '';
    if ($is_pair && $slider_left) {
        $inner .= em_site_admin_header_preview_col('slider', $slider_html);
    }
    if ($has_hero) {
        $inner .= em_site_admin_header_preview_col('hero', $hero_html);
    }
    if ($is_pair && !$slider_left) {
        $inner .= em_site_admin_header_preview_col('slider', $slider_html);
    }
    if (!$is_pair && $has_slider) {
        $inner .= em_site_admin_header_preview_col('slider', $slider_html);
    }

    return em_site_admin_header_wrap_composite_html($shell_style, $inner_class, $cols, $inner, (array) ($cfg['appearance'] ?? []));
}

/**
 * Variantes de slug HEADER pour compatibilité legacy (`header-` / `headers-`).
 *
 * @return array<int,string>
 */
function em_site_admin_header_slug_variants(string $slug): array
{
    $slug = sanitize_key($slug);

    if ($slug === '') {
        return [];
    }

    $variants = [$slug];

    if (strpos($slug, 'headers-') === 0) {
        $variants[] = 'header-' . substr($slug, 8);
    } elseif (strpos($slug, 'header-') === 0) {
        $variants[] = 'headers-' . substr($slug, 7);
    }

    return array_values(array_unique(array_filter(array_map('sanitize_key', $variants))));
}

/**
 * Libellé par défaut d'un item HEADER lié au template.
 */
function em_site_admin_header_default_item_label(string $template): string
{
    return strtoupper($template !== '' ? $template : 'DEFAULT');
}

/**
 * S'assure qu'un item HEADER existe et que l'instance template `header` est valide.
 */
function em_site_admin_header_ensure_catalog_item(string $template): string
{
    $template = sanitize_key($template);

    if ($template === '' || !function_exists('em_site_get_items') || !function_exists('em_site_register_item')) {
        return '';
    }

    $type = em_site_admin_header_catalog_type_slug();
    $items = em_site_get_items($type);

    if ($items === []) {
        $seed_slug = em_site_unique_item_slug($type, em_site_item_slug_base($type, 'header-' . $template));
        em_site_register_item($type, $seed_slug, em_site_admin_header_default_item_label($template));
        $items = em_site_get_items($type);
    }

    $instance = function_exists('em_site_get_instance') ? em_site_get_instance($template, em_site_admin_header_section_slug()) : [];
    $selected = sanitize_key((string) ($instance['item'] ?? ''));

    if ($selected === '' || !isset($items[$selected])) {
        $selected = (string) array_key_first($items);
        if ($selected !== '' && function_exists('em_site_save_instance')) {
            $instance['item'] = $selected;
            $instance['display_mode'] = sanitize_key((string) ($instance['display_mode'] ?? 'single')) === 'multi' ? 'multi' : 'single';
            em_site_save_instance($template, em_site_admin_header_section_slug(), $instance);
        }
    }

    return $selected;
}

/**
 * Empêche les labels du catalogue HEADER d'être strictement identiques.
 */
function em_site_admin_header_ensure_unique_catalog_labels(): void
{
    if (!function_exists('em_site_get_items') || !function_exists('em_site_save_items')) {
        return;
    }

    $type = em_site_admin_header_catalog_type_slug();
    $items = em_site_get_items($type);

    if ($items === []) {
        return;
    }

    $seen = [];
    $changed = false;

    foreach ($items as $slug => $label) {
        $slug = (string) $slug;
        $base = trim((string) $label);
        if (stripos($base, 'HEADER ') === 0) {
            $base = trim((string) substr($base, 7));
        }
        if ($base === '') {
            $base = strtoupper($slug);
        }

        $key = strtolower($base);
        $count = (int) ($seen[$key] ?? 0) + 1;
        $seen[$key] = $count;

        if ($count === 1) {
            if ($items[$slug] !== $base) {
                $items[$slug] = $base;
                $changed = true;
            }
            continue;
        }

        $new_label = $base . ' ' . $count;
        if ($items[$slug] !== $new_label) {
            $items[$slug] = $new_label;
            $changed = true;
        }
    }

    if ($changed) {
        em_site_save_items($type, $items);
    }
}

/**
 * Migration one-shot: remplace le préfixe legacy `headers-` par `header-`.
 *
 * Exemple: `headers-header-mayami` -> `header-mayami`.
 * La config item dédiée (`em_site_header_item_cfg_*`) est déplacée aussi.
 */
function em_site_admin_header_maybe_migrate_headers_slug_prefix(): void
{
    $flag = 'em_site_header_slug_prefix_migrated_v1';

    if (!function_exists('em_site_get_items') || !function_exists('em_site_rename_item')) {
        return;
    }

    $type = em_site_admin_header_catalog_type_slug();
    $items = em_site_get_items($type);

    $has_legacy = false;
    foreach (array_keys($items) as $slug) {
        $slug = sanitize_key((string) $slug);
        if ($slug !== '' && strpos($slug, 'headers-') === 0) {
            $has_legacy = true;
            break;
        }
    }

    // Garde le flag pour traçabilité mais ne bloque jamais si un legacy subsiste.
    if (!$has_legacy) {
        if (!get_option($flag, false)) {
            update_option($flag, '1', false);
        }
        return;
    }

    foreach ($items as $item_slug => $item_label) {
        $item_slug = sanitize_key((string) $item_slug);
        $item_label = sanitize_text_field((string) $item_label);

        if ($item_slug === '' || strpos($item_slug, 'headers-') !== 0) {
            continue;
        }

        $old_cfg_raw = get_option(em_site_admin_header_item_config_option_name($item_slug), null);
        $result = em_site_rename_item($type, $item_slug, $item_label);
        $new_slug = sanitize_key((string) ($result['item'] ?? ''));

        // Rename avec conservation explicite de la config HEADER dédiée.
        if ($new_slug !== '' && $new_slug !== $item_slug) {
            $new_cfg_option = em_site_admin_header_item_config_option_name($new_slug);
            if (is_array($old_cfg_raw) && get_option($new_cfg_option, null) === null) {
                update_option($new_cfg_option, em_site_admin_header_item_config_normalize($old_cfg_raw), false);
            }
            delete_option(em_site_admin_header_item_config_option_name($item_slug));
        }
    }

    update_option($flag, '1', false);
}
add_action('admin_init', 'em_site_admin_header_maybe_migrate_headers_slug_prefix', 12);

/**
 * Config par défaut d'un item HEADER.
 *
 * @return array{matrix:string,position:string,hero:string,slider:string,ratio:string,appearance:array<string,mixed>}
 */
function em_site_admin_header_item_config_defaults(): array
{
    return [
        'matrix'     => 'hero_slider',
        'position'   => 'hero_left',
        'hero'       => '',
        'slider'     => '',
        'ratio'      => '60-40',
        'appearance' => em_site_admin_header_appearance_defaults(),
    ];
}

/**
 * Normalise la config d'un item HEADER.
 *
 * @param array<string, mixed> $raw
 * @return array{matrix:string,position:string,hero:string,slider:string,ratio:string,appearance:array<string,mixed>}
 */
function em_site_admin_header_item_config_normalize(array $raw): array
{
    $defaults = em_site_admin_header_item_config_defaults();
    $hero_value = (string) ($raw['hero'] ?? ($raw['hero_slug'] ?? ''));
    $slider_value = (string) ($raw['slider'] ?? ($raw['slider_slug'] ?? ''));
    $position_value = (string) ($raw['position'] ?? ($raw['layout'] ?? 'hero_left'));
    $matrix_value = (string) ($raw['matrix'] ?? '');
    if ($matrix_value === '' && ($hero_value !== '' || $slider_value !== '')) {
        if ($hero_value !== '' && $slider_value !== '') {
            $matrix_value = 'hero_slider';
        } elseif ($slider_value !== '') {
            $matrix_value = 'slider';
        } else {
            $matrix_value = 'hero';
        }
    }
    $ratio = sanitize_key((string) ($raw['ratio'] ?? $defaults['ratio']));
    if (!isset(em_site_admin_header_ratio_choices()[$ratio])) {
        $ratio = (string) $defaults['ratio'];
    }

    return [
        'matrix'     => in_array($matrix_value, ['hero', 'hero_slider', 'slider'], true)
            ? $matrix_value
            : 'hero',
        'position'   => $position_value === 'slider_left' ? 'slider_left' : 'hero_left',
        'hero'       => sanitize_key($hero_value),
        'slider'     => sanitize_key($slider_value),
        'ratio'      => $ratio,
        'appearance' => em_site_admin_header_appearance_normalize(is_array($raw['appearance'] ?? null) ? $raw['appearance'] : []),
    ];
}

/**
 * Lit la config d'un item HEADER.
 *
 * @return array{matrix:string,position:string,hero:string,slider:string,ratio:string,appearance:array<string,mixed>}
 */
function em_site_admin_header_item_config_get(string $header_item_slug): array
{
    $header_item_slug = sanitize_key($header_item_slug);

    if ($header_item_slug === '') {
        return em_site_admin_header_item_config_defaults();
    }

    $raw = [];
    $best_score = -1;
    foreach (em_site_admin_header_slug_variants($header_item_slug) as $candidate_slug) {
        $candidate_raw = get_option(em_site_admin_header_item_config_option_name($candidate_slug), []);
        if (is_array($candidate_raw) && $candidate_raw !== []) {
            $candidate_norm = em_site_admin_header_item_config_normalize($candidate_raw);
            $score = 0;
            if (($candidate_norm['hero'] ?? '') !== '') {
                $score += 1;
            }
            if (($candidate_norm['slider'] ?? '') !== '') {
                $score += 2;
            }
            if (($candidate_norm['matrix'] ?? '') === 'hero_slider') {
                $score += 3;
            }
            if ($score > $best_score) {
                $best_score = $score;
                $raw = $candidate_raw;
            }
        }
    }

    return em_site_admin_header_item_config_normalize($raw);
}

/**
 * Enregistre la config d'un item HEADER.
 *
 * @param array<string, mixed> $config
 */
function em_site_admin_header_item_config_save(string $header_item_slug, array $config): void
{
    $header_item_slug = sanitize_key($header_item_slug);

    if ($header_item_slug === '') {
        return;
    }

    $normalized = em_site_admin_header_item_config_normalize($config);
    update_option(em_site_admin_header_item_config_option_name($header_item_slug), $normalized, false);

    foreach (em_site_admin_header_slug_variants($header_item_slug) as $candidate_slug) {
        if ($candidate_slug === $header_item_slug) {
            continue;
        }
        delete_option(em_site_admin_header_item_config_option_name($candidate_slug));
    }
}

/**
 * Migration douce: ancienne config template unique -> item HEADER dédié.
 */
function em_site_admin_header_maybe_migrate_legacy_template_config(string $template): void
{
    $template = sanitize_key($template);

    if ($template === '' || !function_exists('em_site_register_item')) {
        return;
    }

    $flag_option = 'em_site_header_migrated_' . $template;
    if (get_option($flag_option, false)) {
        return;
    }

    $legacy = get_option(em_site_admin_header_section_option_name($template), null);
    $selected_item = em_site_admin_header_ensure_catalog_item($template);

    if (is_array($legacy) && $legacy !== []) {
        if ($selected_item === '') {
            $selected_item = em_site_admin_header_ensure_catalog_item($template);
        }

        $instance = function_exists('em_site_get_instance') ? em_site_get_instance($template, em_site_admin_header_section_slug()) : [];
        $instance['item'] = $selected_item;
        $instance['display_mode'] = sanitize_key((string) ($legacy['display_mode'] ?? 'single')) === 'multi' ? 'multi' : 'single';
        if (function_exists('em_site_save_instance')) {
            em_site_save_instance($template, em_site_admin_header_section_slug(), $instance);
        }

        em_site_admin_header_item_config_save($selected_item, [
            'matrix'     => ($legacy['matrix'] ?? '') === 'hero_slider' ? 'hero_slider' : 'hero',
            'position'   => ($legacy['position'] ?? '') === 'slider_left' ? 'slider_left' : 'hero_left',
            'hero'       => sanitize_key((string) ($legacy['hero'] ?? '')),
            'slider'     => sanitize_key((string) ($legacy['slider'] ?? '')),
            'ratio'      => sanitize_key((string) ($legacy['ratio'] ?? '60-40')),
            'appearance' => is_array($legacy['appearance'] ?? null) ? $legacy['appearance'] : [],
        ]);
    }

    update_option($flag_option, '1', false);
}

/**
 * Choix de ratio HERO / SLIDER (largeur des colonnes).
 *
 * @return array<string, string>
 */
function em_site_admin_header_ratio_choices(): array
{
    return [
        '75-25' => '75 / 25',
        '70-30' => '70 / 30',
        '60-40' => '60 / 40',
        '50-50' => '50 / 50',
    ];
}

/**
 * Colonnes CSS (grid-template-columns) selon le ratio et la position.
 *
 * EXACTEMENT comme la hero-row du SITE (landing.css :
 * minmax(0,640px) minmax(320px,430px)) : le SLIDER (téléphone) garde sa largeur
 * native bornée (320–430px) et sa hauteur native (610px), le HERO prend le reste
 * (plafond variant selon le ratio). Le front de l'aperçu EM-SITE = le vrai site.
 */
function em_site_admin_header_ratio_columns(string $ratio, bool $slider_left): string
{
    $hero_max = ['75-25' => 1000, '70-30' => 860, '60-40' => 640, '50-50' => 430];
    $hmax = $hero_max[$ratio] ?? 640;

    $hero = 'minmax(0, ' . $hmax . 'px)';
    $slider = 'minmax(320px, 430px)';

    return $slider_left
        ? $slider . ' ' . $hero
        : $hero . ' ' . $slider;
}

/**
 * Apparence partagée du HEADER (valeurs par défaut).
 *
 * @return array<string, mixed>
 */
function em_site_admin_header_appearance_defaults(): array
{
    // Valeurs par défaut calquées sur le rendu du site (modules/header/admin-preview-render-header.css) :
    // fond orange via le repli CSS (.em-header-shell, FOND laissé vide), image de
    // fond faible (opacité 32 %) et miroir activé → fidèle sans réglage manuel.
    return [
        'bg'               => '',
        'bg_image_id'      => 0,
        'bg_image_pos'     => 'cover',
        'bg_image_opacity' => 32,
        'bg_image_mirror'  => true,
        'pt'               => 0,
        'pb'               => 0,
        'pl'               => 0,
        'pr'               => 0,
    ];
}

/**
 * Normalise un tableau d'apparence HEADER.
 *
 * @param array<string, mixed> $raw
 * @return array<string, mixed>
 */
function em_site_admin_header_appearance_normalize(array $raw): array
{
    $bg = sanitize_hex_color((string) ($raw['bg'] ?? ''));

    return [
        'bg'               => is_string($bg) ? $bg : '',
        'bg_image_id'      => max(0, (int) ($raw['bg_image_id'] ?? 0)),
        'bg_image_pos'     => sanitize_key((string) ($raw['bg_image_pos'] ?? 'cover')),
        // Repli = rendu du site : image faible (32 %) + miroir, quand non défini.
        'bg_image_opacity' => max(0, min(100, (int) ($raw['bg_image_opacity'] ?? 32))),
        'bg_image_mirror'  => array_key_exists('bg_image_mirror', $raw) ? !empty($raw['bg_image_mirror']) : true,
        'pt'               => max(0, (int) ($raw['pt'] ?? 0)),
        'pb'               => max(0, (int) ($raw['pb'] ?? 0)),
        'pl'               => max(0, (int) ($raw['pl'] ?? 0)),
        'pr'               => max(0, (int) ($raw['pr'] ?? 0)),
    ];
}

/**
 * Config HEADER normalisée d'un template.
 *
 * @return array{display_mode:string,transition_mode:string,transition_timer:int,first_item:string,hidden_items:array<int,string>,header_item:string,matrix:string,position:string,hero:string,slider:string,ratio:string,appearance:array<string,mixed>}
 */
function em_site_admin_header_section_get(string $template): array
{
    $template = sanitize_key($template);

    if ($template === '') {
        $defaults = em_site_admin_header_item_config_defaults();
        return [
            'display_mode' => 'single',
            'transition_mode' => 'manual',
            'transition_timer' => 6,
            'first_item'   => '',
            'hidden_items' => [],
            'header_item'  => '',
            'matrix'       => $defaults['matrix'],
            'position'     => $defaults['position'],
            'hero'         => $defaults['hero'],
            'slider'       => $defaults['slider'],
            'ratio'        => $defaults['ratio'],
            'appearance'   => $defaults['appearance'],
        ];
    }

    em_site_admin_header_maybe_migrate_headers_slug_prefix();
    em_site_admin_header_maybe_migrate_legacy_template_config($template);
    em_site_admin_header_ensure_unique_catalog_labels();
    $header_item = em_site_admin_header_ensure_catalog_item($template);
    $instance = function_exists('em_site_get_instance') ? em_site_get_instance($template, em_site_admin_header_section_slug()) : [];
    $display_mode_raw = sanitize_key((string) ($instance['display_mode'] ?? 'single'));
    $display_mode = $display_mode_raw === 'multi' ? 'multi' : 'single';
    $transition_mode = sanitize_key((string) ($instance['transition_mode'] ?? 'manual'));
    if (!in_array($transition_mode, ['manual', 'auto'], true)) {
        $transition_mode = 'manual';
    }
    $transition_timer = max(2, min(120, (int) ($instance['transition_timer'] ?? 6)));
    $hidden_items = [];
    foreach ((array) ($instance['hidden_items'] ?? []) as $hidden_slug) {
        $hidden_slug = sanitize_key((string) $hidden_slug);
        if ($hidden_slug !== '') {
            $hidden_items[] = $hidden_slug;
        }
    }
    $hidden_items = array_values(array_unique($hidden_items));
    $first_item = sanitize_key((string) ($instance['first_item'] ?? ''));
    if ($first_item === '') {
        $first_item = sanitize_key((string) ($instance['item'] ?? ''));
    }
    $item_cfg = em_site_admin_header_item_config_get($header_item);

    return [
        'display_mode' => $display_mode,
        'transition_mode' => $transition_mode,
        'transition_timer' => $transition_timer,
        'first_item'   => $first_item,
        'hidden_items' => $hidden_items,
        'header_item'  => $header_item,
        'matrix'       => $item_cfg['matrix'],
        'position'     => $item_cfg['position'],
        'hero'         => $item_cfg['hero'],
        'slider'       => $item_cfg['slider'],
        'ratio'        => $item_cfg['ratio'],
        'appearance'   => $item_cfg['appearance'],
    ];
}

/**
 * Persiste la config HEADER d'un template.
 *
 * @param array<string, mixed> $data
 */
function em_site_admin_header_section_save(string $template, array $data): void
{
    $template = sanitize_key($template);

    if ($template === '') {
        return;
    }

    em_site_admin_header_ensure_catalog_item($template);

    $display_mode = sanitize_key((string) ($data['display_mode'] ?? 'single'));
    if (!in_array($display_mode, ['single', 'multi'], true)) {
        $display_mode = 'single';
    }
    $transition_mode = sanitize_key((string) ($data['transition_mode'] ?? 'manual'));
    if (!in_array($transition_mode, ['manual', 'auto'], true)) {
        $transition_mode = 'manual';
    }
    $transition_timer = max(2, min(120, (int) ($data['transition_timer'] ?? 6)));

    $type = em_site_admin_header_catalog_type_slug();
    $items = function_exists('em_site_get_items') ? em_site_get_items($type) : [];
    $header_item = sanitize_key((string) ($data['header_item'] ?? ''));
    if ($header_item === '' || !isset($items[$header_item])) {
        $header_item = em_site_admin_header_ensure_catalog_item($template);
    }

    if ($header_item === '') {
        return;
    }

    $item_slugs = array_values(array_map('strval', array_keys($items)));
    $hidden_items = [];
    foreach ((array) ($data['hidden_items'] ?? []) as $hidden_slug) {
        $hidden_slug = sanitize_key((string) $hidden_slug);
        if ($hidden_slug !== '' && in_array($hidden_slug, $item_slugs, true)) {
            $hidden_items[] = $hidden_slug;
        }
    }
    $hidden_items = array_values(array_unique($hidden_items));

    $first_item = sanitize_key((string) ($data['first_item'] ?? $header_item));
    if ($display_mode !== 'multi') {
        $first_item = $header_item;
        $hidden_items = [];
    } else {
        if ($transition_mode === 'auto') {
            $visible_items = array_values(array_diff($item_slugs, $hidden_items));
            if ($visible_items === []) {
                $visible_items = $item_slugs;
                $hidden_items = [];
            }
            if (!in_array($first_item, $visible_items, true)) {
                $first_item = (string) ($visible_items[0] ?? $header_item);
            }
            if ($first_item !== '' && $header_item !== $first_item && in_array($first_item, $item_slugs, true)) {
                $header_item = $first_item;
            }
        } else {
            $visible_items = array_values(array_diff($item_slugs, $hidden_items));
            if ($visible_items === []) {
                $visible_items = $item_slugs;
                $hidden_items = [];
            }
            if (!in_array($first_item, $visible_items, true)) {
                $first_item = (string) ($visible_items[0] ?? $header_item);
            }
        }
    }

    $instance = function_exists('em_site_get_instance') ? em_site_get_instance($template, em_site_admin_header_section_slug()) : [];
    $instance['item'] = $header_item;
    $instance['display_mode'] = $display_mode;
    $instance['transition_mode'] = $transition_mode;
    $instance['transition_timer'] = $transition_timer;
    $instance['first_item'] = $first_item;
    $instance['hidden_items'] = $hidden_items;
    if (function_exists('em_site_save_instance')) {
        em_site_save_instance($template, em_site_admin_header_section_slug(), $instance);
    }

    // Le squelette ne pilote plus la composition des items HEADER.
    // On persiste uniquement la sélection d'item + le mode d'affichage.
    $item_cfg = em_site_admin_header_item_config_get($header_item);
    update_option(em_site_admin_header_section_option_name($template), [
        'display_mode' => $display_mode,
        'matrix'       => (string) ($item_cfg['matrix'] ?? 'hero'),
        'position'     => (string) ($item_cfg['position'] ?? 'hero_left'),
        'hero'         => (string) ($item_cfg['hero'] ?? ''),
        'slider'       => (string) ($item_cfg['slider'] ?? ''),
        'ratio'        => (string) ($item_cfg['ratio'] ?? '60-40'),
        'appearance'   => (array) ($item_cfg['appearance'] ?? em_site_admin_header_appearance_defaults()),
        'header_item'  => $header_item,
    ], false);
}

/**
 * Item effectif (branché ou défaut) pour une partie HERO/SLIDER d'un template.
 */
function em_site_admin_header_effective_item(string $template, string $part): string
{
    $type = em_site_admin_header_part_type_slug($part);

    if ($type === '' || !function_exists('em_site_get_items')) {
        return '';
    }

    $cfg = em_site_admin_header_section_get($template);
    $saved = (string) ($cfg[$part] ?? '');
    $items = em_site_get_items($type);

    if ($saved !== '' && isset($items[$saved])) {
        return $saved;
    }

    return function_exists('em_site_rubrique_default_item_slug')
        ? em_site_rubrique_default_item_slug($type)
        : '';
}

/**
 * Rendu de la liste des items HEADER (assemblages) disponibles.
 */
function em_site_admin_render_header_catalog_items(string $template): void
{
    em_site_admin_header_maybe_migrate_headers_slug_prefix();
    em_site_admin_header_ensure_unique_catalog_labels();
    $type = em_site_admin_header_catalog_type_slug();
    $items = function_exists('em_site_get_items') ? em_site_get_items($type) : [];
    $cfg = em_site_admin_header_section_get($template);
    $display_mode = in_array((string) ($cfg['display_mode'] ?? ''), ['single', 'multi'], true)
        ? (string) $cfg['display_mode']
        : 'single';
    $current = sanitize_key((string) ($cfg['header_item'] ?? ''));
    $transition_mode = in_array((string) ($cfg['transition_mode'] ?? ''), ['manual', 'auto'], true)
        ? (string) $cfg['transition_mode']
        : 'manual';
    $transition_timer = max(2, min(120, (int) ($cfg['transition_timer'] ?? 6)));
    $hidden_items = [];
    foreach ((array) ($cfg['hidden_items'] ?? []) as $hidden_slug) {
        $hidden_slug = sanitize_key((string) $hidden_slug);
        if ($hidden_slug !== '' && isset($items[$hidden_slug])) {
            $hidden_items[] = $hidden_slug;
        }
    }
    $hidden_items = array_values(array_unique($hidden_items));
    $visible_items = array_values(array_diff(array_values(array_map('strval', array_keys($items))), $hidden_items));
    if ($visible_items === []) {
        $visible_items = array_values(array_map('strval', array_keys($items)));
        $hidden_items = [];
    }
    $first_item = sanitize_key((string) ($cfg['first_item'] ?? $current));
    if ($first_item === '' || !in_array($first_item, $visible_items, true)) {
        $first_item = (string) ($visible_items[0] ?? $current);
    }
    $preview_item = $display_mode === 'multi' ? $first_item : $current;
    if ($preview_item === '' && $items !== []) {
        $preview_item = (string) array_key_first($items);
    }

    // Même logique que les autres pickers: l'item effectif affiché est en tête.
    $head_item = $display_mode === 'multi' ? $first_item : $current;
    if ($head_item !== '' && isset($items[$head_item])) {
        $items = [$head_item => $items[$head_item]] + $items;
    }
    ?>
    <div class="em-site-header-picker__part" data-part="header-item">
        <p class="em-site-header-picker__subhead"><?php esc_html_e('Items disponibles pour HEADER', 'em-site'); ?></p>
        <?php em_site_admin_render_multi_transition_controls(
            'em-site-header-transition',
            $display_mode,
            $transition_mode,
            $transition_timer,
            'data-em-header-multi-options',
            'data-em-header-multi-timer-wrap',
            'data-em-header-multi-timer-input'
        ); ?>
        <?php if ($items === []) : ?>
            <p class="em-site-rubriques-admin__picker-empty"><?php esc_html_e('Aucun item HEADER disponible.', 'em-site'); ?></p>
        <?php else : ?>
            <ul class="em-site-instance-picker em-site-header-picker__items" data-part="header-item" data-type="header" data-display-mode="<?php echo esc_attr($display_mode); ?>" data-current="<?php echo esc_attr($preview_item); ?>" data-first-item="<?php echo esc_attr($first_item); ?>" data-hidden-items="<?php echo esc_attr((string) wp_json_encode($hidden_items)); ?>">
                <?php foreach ($items as $slug => $item_label) :
                    $slug = (string) $slug;
                    $radio_id = 'em-site-header-item-' . sanitize_html_class($slug);
                    $multi_toggle_id = 'em-site-header-item-multi-toggle-' . sanitize_html_class($slug);
                    $multi_first_id = 'em-site-header-item-multi-first-' . sanitize_html_class($slug);
                    $is_hidden_in_multi = in_array($slug, $hidden_items, true);
                    $is_first_in_multi = $slug === $first_item;
                    ?>
                    <li class="em-site-instance-picker__row">
                        <label class="em-site-instance-picker__label" for="<?php echo esc_attr($radio_id); ?>">
                            <?php em_site_admin_render_picker_row_selectors(
                                $slug,
                                $radio_id,
                                'em-site-header-item',
                                $display_mode,
                                true,
                                $multi_toggle_id,
                                $multi_first_id,
                                'em-site-header-first-item',
                                $is_hidden_in_multi,
                                $is_first_in_multi,
                                $current
                            ); ?>
                            <?php
                            $display_label = (string) $item_label;
                            if (stripos($display_label, 'HEADER ') !== 0) {
                                $display_label = 'HEADER ' . $display_label;
                            }
                            ?>
                            <span class="em-site-instance-picker__name"><?php echo esc_html($display_label); ?></span>
                            <?php em_site_admin_render_picker_row_badges($slug, $display_mode, $current, true, $is_first_in_multi); ?>
                        </label>
                        <span class="em-site-instance-picker__actions">
                            <button type="button" class="em-site-instance-picker__eye" data-item="<?php echo esc_attr($slug); ?>" aria-pressed="false" title="<?php esc_attr_e('Aperçu de la section', 'em-site'); ?>" aria-label="<?php esc_attr_e('Aperçu de la section', 'em-site'); ?>">
                                <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                            </button>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="em-site-instance-picker__previews em-site-header-picker__previews" data-part="header-item">
                <?php foreach ($items as $slug => $item_label) :
                    $slug = (string) $slug;
                    ?>
                    <div class="em-site-instance-picker__preview" data-item="<?php echo esc_attr($slug); ?>" hidden>
                        <div class="em-site-instance-picker__stage" data-module-slug="header">
                            <?php echo em_site_admin_header_composite_html_for_item($template, $slug); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Style inline (variables CSS) du conteneur HEADER : fond partagé (couleur +
 * image explicite optionnelle + opacité + miroir + position) et marges.
 *
 * @param array<string, mixed> $appearance
 */
function em_site_admin_header_part_item_data(string $part, string $item_slug): array
{
    $part = $part === 'slider' ? 'slider' : 'hero';
    $item_slug = sanitize_key($item_slug);
    if ($item_slug === '') {
        return [];
    }

    if (!function_exists('em_site_header_hero_item') || !function_exists('em_site_header_slider_item')) {
        $compose = get_template_directory() . '/inc/front/modules/header/compose.php';
        if (is_readable($compose)) {
            require_once $compose;
        }
    }

    if ($part === 'slider' && function_exists('em_site_header_slider_item')) {
        $item = (array) em_site_header_slider_item($item_slug);
        return is_array($item['content'] ?? null) ? (array) $item['content'] : [];
    }

    if ($part === 'hero' && function_exists('em_site_header_hero_item')) {
        $item = (array) em_site_header_hero_item($item_slug);
        return is_array($item['content'] ?? null) ? (array) $item['content'] : [];
    }

    return [];
}

/**
 * Style inline (variables CSS) du conteneur HEADER : fond partagé (couleur +
 * image explicite optionnelle + opacité + miroir + position) et marges.
 *
 * @param array<string, mixed> $appearance
 * @param array<string, mixed> $hero_content
 */
function em_site_admin_header_shell_style(string $template, array $appearance, array $hero_content = []): string
{
    unset($template, $hero_content);

    return implode(';', em_site_admin_header_shell_style_vars(['appearance' => $appearance]));
}

/**
 * Rendu robuste d'un item HERO/SLIDER pour la preview admin.
 *
 * Tente d'abord le type attendu (hero/slider), puis un fallback sur les types
 * enregistrés afin de retrouver l'item par son slug si le mapping a dérivé.
 */
function em_site_admin_header_render_part_html(string $part, string $item_slug): string
{
    if (!function_exists('em_site_rubrique_render')) {
        return '';
    }

    $part = $part === 'slider' ? 'slider' : 'hero';
    $item_slug = sanitize_key($item_slug);
    if ($item_slug === '') {
        return '';
    }

    if ($part === 'slider') {
        if (!function_exists('em_site_render_header_slider_html')) {
            $slider_render = get_template_directory() . '/inc/front/modules/slider/render.php';
            if (is_readable($slider_render)) {
                require_once $slider_render;
            }
        }
        if (function_exists('em_site_render_header_slider_html')) {
            $direct_html = (string) em_site_render_header_slider_html($item_slug);
            if (trim($direct_html) !== '') {
                return $direct_html;
            }
        }
    }

    $types = [];
    $expected = em_site_admin_header_part_type_slug($part);
    if ($expected !== '') {
        $types[] = $expected;
    }

    if (function_exists('em_site_rubrique_type_registry')) {
        foreach (array_keys((array) em_site_rubrique_type_registry()) as $candidate_type) {
            $candidate_type = sanitize_key((string) $candidate_type);
            if ($candidate_type !== '') {
                $types[] = $candidate_type;
            }
        }
    }

    $types = array_values(array_unique($types));

    foreach ($types as $type) {
        $html = em_site_rubrique_render($type, ['item' => $item_slug]);
        if (trim($html) !== '') {
            return $html;
        }
    }

    return '';
}

/**
 * HTML composite du HEADER : un conteneur « shell » qui porte le FOND PARTAGÉ,
 * sur lequel HERO (et SLIDER) sont posés en colonnes, rendus SANS fond propre
 * (transparents) — reproduit le rendu du site (un seul fond, deux colonnes).
 */
function em_site_admin_header_composite_html(string $template): string
{
    if (!function_exists('em_site_rubrique_render')) {
        return '';
    }

    $cfg = em_site_admin_header_section_get($template);
    $preview_html = em_site_admin_header_preview_html_from_config($cfg);
    if ($preview_html !== '') {
        return $preview_html;
    }
    $header_item = sanitize_key((string) ($cfg['header_item'] ?? ''));
    if (($cfg['display_mode'] ?? 'single') === 'multi') {
        $first_item = sanitize_key((string) ($cfg['first_item'] ?? ''));
        if ($first_item !== '') {
            $header_item = $first_item;
        }
    }

    if ($header_item !== '') {
        return em_site_admin_header_composite_html_for_item($template, $header_item);
    }

    $hero_type = em_site_admin_header_part_type_slug('hero');
    $hero_item = em_site_admin_header_effective_item($template, 'hero');
    $hero_content = $hero_item !== '' ? em_site_admin_header_part_item_data('hero', $hero_item) : [];
    $hero_html = $hero_item !== ''
        ? em_site_admin_header_render_part_html('hero', $hero_item)
        : '';
    $hero_col = '<div class="em-header-shell__col em-header-shell__col--hero">' . $hero_html . '</div>';

    $slider_type = em_site_admin_header_part_type_slug('slider');
    $slider_item = em_site_admin_header_effective_item($template, 'slider');
    $slider_html = $slider_item !== ''
        ? em_site_admin_header_render_part_html('slider', $slider_item)
        : '';
    $slider_col = '<div id="hero-slider" class="em-header-shell__col em-header-shell__col--slider">' . $slider_html . '</div>';

    $slider_left = $cfg['position'] === 'slider_left';
    $cols = '1fr';
    $inner = $hero_col;
    $is_pair = false;

    if ($cfg['matrix'] === 'slider') {
        if ($slider_html === '') {
            return '';
        }
        $inner = $slider_col;
    } elseif ($cfg['matrix'] === 'hero_slider') {
        if ($hero_html !== '' && $slider_html !== '') {
            $is_pair = true;
            $cols = em_site_admin_header_ratio_columns($cfg['ratio'], $slider_left);
            $inner = $slider_left ? ($slider_col . $hero_col) : ($hero_col . $slider_col);
        } elseif ($hero_html !== '') {
            $inner = $hero_col;
        } elseif ($slider_html !== '') {
            $inner = $slider_col;
        } else {
            return '';
        }
    } elseif ($hero_html === '') {
        return '';
    }

    // Le SHELL porte le fond partagé pleine largeur ; la grille HERO/SLIDER est
    // dans un conteneur centré (comme .em-landing-hero-row__inner du front :
    // max 1100px, gap, padding vertical, colonnes alignées en haut).
    $shell_style = em_site_admin_header_shell_style($template, (array) ($cfg['appearance'] ?? []), $hero_content);
    $nav_color = '';
    if (!empty($cfg['appearance']['nav_color'])) {
        $nav_color = sanitize_hex_color((string) $cfg['appearance']['nav_color']) ?: '';
    }
    if ($nav_color === '' && !empty($cfg['appearance']['bg'])) {
        $nav_color = sanitize_hex_color((string) $cfg['appearance']['bg']) ?: '';
    }
    if ($nav_color !== '') {
        $shell_style .= '--em-header-switch-color:' . $nav_color . ';';
    }
    $inner_style = 'display:grid;align-items:start;gap:28px;width:min(1100px,92vw);margin:0 auto;padding:44px 0 68px;grid-template-columns:' . $cols . ';';
    $inner_class = 'em-header-shell__inner' . ($slider_left ? ' is-slider-first' : '') . ($is_pair ? ' is-pair' : ' is-single');

    return '<div class="em-rubrique em-header-shell" style="' . esc_attr($shell_style) . '">'
        . '<div class="' . esc_attr($inner_class) . '" style="' . esc_attr($inner_style) . '">' . $inner . '</div>'
        . '</div>';
}

/**
 * HTML composite du HEADER pour un item spécifique (aperçu œil + mode multi).
 */
function em_site_admin_header_composite_html_for_item(string $template, string $header_item_slug, array $override_config = []): string
{
    if (!function_exists('em_site_rubrique_render')) {
        return '';
    }

    $template = sanitize_key($template);
    $header_item_slug = sanitize_key($header_item_slug);
    if ($header_item_slug === '') {
        return '';
    }

    $cfg = em_site_admin_header_item_config_get($header_item_slug);
    if ($override_config !== []) {
        $base_appearance = is_array($cfg['appearance'] ?? null) ? $cfg['appearance'] : [];
        $incoming_appearance = is_array($override_config['appearance'] ?? null) ? $override_config['appearance'] : [];
        $merged = $cfg;
        foreach ($override_config as $key => $value) {
            if ($key === 'appearance') {
                continue;
            }
            $merged[$key] = $value;
        }
        $merged['appearance'] = array_merge($base_appearance, $incoming_appearance);
        $cfg = em_site_admin_header_item_config_normalize($merged);
    }
    $preview_html = em_site_admin_header_preview_html_from_config($cfg);
    if ($preview_html !== '') {
        return $preview_html;
    }
    $hero_item = sanitize_key((string) ($cfg['hero'] ?? ''));
    if ($hero_item === '' && $template !== '') {
        $hero_item = em_site_admin_header_effective_item($template, 'hero');
    }
    $hero_content = $hero_item !== '' ? em_site_admin_header_part_item_data('hero', $hero_item) : [];
    if ($hero_item === '' && function_exists('em_site_admin_header_part_type_slug')) {
        $hero_type = em_site_admin_header_part_type_slug('hero');
        if ($hero_type !== '' && function_exists('em_site_rubrique_default_item_slug')) {
            $hero_item = em_site_rubrique_default_item_slug($hero_type);
            $hero_content = $hero_item !== '' ? em_site_admin_header_part_item_data('hero', $hero_item) : [];
        }
    }
    $hero_html = $hero_item !== ''
        ? em_site_admin_header_render_part_html('hero', $hero_item)
        : '';
    $hero_col = '<div class="em-header-shell__col em-header-shell__col--hero">' . $hero_html . '</div>';

    $slider_type = em_site_admin_header_part_type_slug('slider');
    $slider_item = sanitize_key((string) ($cfg['slider'] ?? ''));
    if ($slider_item === '' && $template !== '') {
        $slider_item = em_site_admin_header_effective_item($template, 'slider');
    }
    if ($slider_item === '' && $slider_type !== '' && function_exists('em_site_rubrique_default_item_slug')) {
        $slider_item = em_site_rubrique_default_item_slug($slider_type);
    }
    $slider_html = $slider_item !== ''
        ? em_site_admin_header_render_part_html('slider', $slider_item)
        : '';
    $slider_col = '<div id="hero-slider" class="em-header-shell__col em-header-shell__col--slider">' . $slider_html . '</div>';

    $slider_left = (string) ($cfg['position'] ?? '') === 'slider_left';
    $cols = '1fr';
    $inner = $hero_col;
    $is_pair = false;
    $matrix = (string) ($cfg['matrix'] ?? 'hero');

    if ($matrix === 'slider') {
        if ($slider_html === '') {
            return '';
        }
        $inner = $slider_col;
    } elseif ($matrix === 'hero_slider') {
        if ($hero_html !== '' && $slider_html !== '') {
            $is_pair = true;
            $cols = em_site_admin_header_ratio_columns((string) ($cfg['ratio'] ?? '60-40'), $slider_left);
            $inner = $slider_left ? ($slider_col . $hero_col) : ($hero_col . $slider_col);
        } elseif ($hero_html !== '') {
            $inner = $hero_col;
        } elseif ($slider_html !== '') {
            $inner = $slider_col;
        } else {
            return '';
        }
    } elseif ($hero_html === '') {
        return '';
    }

    $shell_style = em_site_admin_header_shell_style($template, (array) ($cfg['appearance'] ?? []), $hero_content);
    $nav_color = '';
    if (!empty($cfg['appearance']['nav_color'])) {
        $nav_color = sanitize_hex_color((string) $cfg['appearance']['nav_color']) ?: '';
    }
    if ($nav_color === '' && !empty($cfg['appearance']['bg'])) {
        $nav_color = sanitize_hex_color((string) $cfg['appearance']['bg']) ?: '';
    }
    if ($nav_color !== '') {
        $shell_style .= '--em-header-switch-color:' . $nav_color . ';';
    }
    $inner_style = 'display:grid;align-items:start;gap:28px;width:min(1100px,92vw);margin:0 auto;padding:44px 0 68px;grid-template-columns:' . $cols . ';';
    $inner_class = 'em-header-shell__inner' . ($slider_left ? ' is-slider-first' : '') . ($is_pair ? ' is-pair' : ' is-single');

    return '<div class="em-rubrique em-header-shell" style="' . esc_attr($shell_style) . '">'
        . '<div class="' . esc_attr($inner_class) . '" style="' . esc_attr($inner_style) . '">' . $inner . '</div>'
        . '</div>';
}

/**
 * Rendu de la liste d'items d'une partie (HERO ou SLIDER) + sources d'aperçu.
 */
function em_site_admin_render_header_part_items(string $template, string $part, string $type): void
{
    $part_label = $part === 'slider' ? __('SLIDER', 'em-site') : __('HERO', 'em-site');
    ?>
    <div class="em-site-header-picker__part" data-part="<?php echo esc_attr($part); ?>">
        <p class="em-site-header-picker__subhead">
            <?php
            /* translators: %s: HERO ou SLIDER. */
            echo esc_html(sprintf(__('Items disponibles pour %s', 'em-site'), $part_label));
            ?>
        </p>
        <?php
        $items = $type !== '' && function_exists('em_site_get_items') ? em_site_get_items($type) : [];

        if ($type === '' || $items === []) {
            ?>
            <p class="em-site-rubriques-admin__picker-empty">
                <?php
                /* translators: %s: HERO ou SLIDER. */
                echo esc_html(sprintf(__('Crée d’abord une rubrique %s pour pouvoir la brancher.', 'em-site'), $part_label));
                ?>
            </p>
            </div>
            <?php
            return;
        }

        $effective = em_site_admin_header_effective_item($template, $part);

        if ($effective !== '' && isset($items[$effective])) {
            $items = [$effective => $items[$effective]] + $items;
        }
        ?>
        <ul
            class="em-site-instance-picker em-site-header-picker__items"
            data-part="<?php echo esc_attr($part); ?>"
            data-type="<?php echo esc_attr($type); ?>"
            data-current="<?php echo esc_attr($effective); ?>"
        >
            <?php foreach ($items as $slug => $item_label) :
                $slug = (string) $slug;
                $radio_id = 'em-site-header-' . sanitize_html_class($part . '-' . $slug);
                ?>
                <li class="em-site-instance-picker__row">
                    <label class="em-site-instance-picker__label" for="<?php echo esc_attr($radio_id); ?>">
                        <input
                            type="radio"
                            id="<?php echo esc_attr($radio_id); ?>"
                            name="em-site-header-<?php echo esc_attr($part); ?>"
                            value="<?php echo esc_attr($slug); ?>"
                            <?php checked($slug === $effective); ?>
                        >
                        <span class="em-site-instance-picker__name"><?php echo esc_html($part_label . ' ' . $item_label); ?></span>
                        <?php if ($slug === $effective) : ?>
                            <span class="em-site-instance-picker__badge"><?php esc_html_e('Item en ligne actuellement', 'em-site'); ?></span>
                        <?php endif; ?>
                    </label>
                    <span class="em-site-instance-picker__actions">
                        <button type="button" class="em-site-instance-picker__eye" data-part="<?php echo esc_attr($part); ?>" data-item="<?php echo esc_attr($slug); ?>" aria-pressed="false" title="<?php esc_attr_e('Aperçu de la section', 'em-site'); ?>" aria-label="<?php esc_attr_e('Aperçu de la section', 'em-site'); ?>">
                            <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                        </button>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="em-site-instance-picker__previews em-site-header-picker__previews" data-part="<?php echo esc_attr($part); ?>">
            <?php foreach ($items as $slug => $item_label) :
                $slug = (string) $slug;
                ?>
                <div class="em-site-instance-picker__preview" data-part="<?php echo esc_attr($part); ?>" data-item="<?php echo esc_attr($slug); ?>" hidden>
                    <div class="em-site-instance-picker__stage">
                        <?php echo em_site_rubrique_render($type, ['item' => $slug]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Rendu d'une liste HERO/SLIDER dans l'éditeur d'un item HEADER.
 */
function em_site_admin_render_header_item_part_items(string $header_item_slug, string $part, string $type, string $selected): void
{
    $part_label = $part === 'slider' ? __('SLIDER', 'em-site') : __('HERO', 'em-site');
    $selected = sanitize_key($selected);
    ?>
    <div class="em-site-header-picker__part" data-part="<?php echo esc_attr($part); ?>">
        <p class="em-site-header-picker__subhead">
            <?php echo esc_html(sprintf(__('Items disponibles pour %s', 'em-site'), $part_label)); ?>
        </p>
        <?php
        $items = $type !== '' && function_exists('em_site_get_items') ? em_site_get_items($type) : [];

        if ($type === '' || $items === []) {
            ?>
            <p class="em-site-rubriques-admin__picker-empty"><?php echo esc_html(sprintf(__('Crée d’abord une rubrique %s pour pouvoir la brancher.', 'em-site'), $part_label)); ?></p>
            </div>
            <?php
            return;
        }

        if (($selected === '' || !isset($items[$selected])) && function_exists('em_site_rubrique_default_item_slug')) {
            $selected = em_site_rubrique_default_item_slug($type);
        }

        if ($selected !== '' && isset($items[$selected])) {
            $items = [$selected => $items[$selected]] + $items;
        }
        ?>
        <ul class="em-site-instance-picker em-site-header-picker__items" data-part="<?php echo esc_attr($part); ?>" data-type="<?php echo esc_attr($type); ?>" data-current="<?php echo esc_attr($selected); ?>">
            <?php foreach ($items as $slug => $item_label) :
                $slug = (string) $slug;
                $radio_id = 'em-site-header-itemcfg-' . sanitize_html_class($header_item_slug . '-' . $part . '-' . $slug);
                ?>
                <li class="em-site-instance-picker__row">
                    <label class="em-site-instance-picker__label" for="<?php echo esc_attr($radio_id); ?>">
                        <input type="radio" id="<?php echo esc_attr($radio_id); ?>" name="em-site-header-itemcfg-<?php echo esc_attr($header_item_slug . '-' . $part); ?>" value="<?php echo esc_attr($slug); ?>" <?php checked($slug === $selected); ?>>
                        <span class="em-site-instance-picker__name"><?php echo esc_html($part_label . ' ' . $item_label); ?></span>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

/**
 * Éditeur de composition d'un item HEADER dans la page RUBRIQUES.
 */
function em_site_admin_render_header_item_editor(string $header_item_slug): void
{
    $header_item_slug = sanitize_key($header_item_slug);
    if ($header_item_slug === '') {
        return;
    }

    $cfg = em_site_admin_header_item_config_get($header_item_slug);
    $hero_type = em_site_admin_header_part_type_slug('hero');
    $slider_type = em_site_admin_header_part_type_slug('slider');
    $matrix = in_array((string) ($cfg['matrix'] ?? ''), ['hero', 'hero_slider', 'slider'], true)
        ? (string) $cfg['matrix']
        : 'hero';
    $editing_template = function_exists('em_site_get_editing_template_slug')
        ? sanitize_key((string) em_site_get_editing_template_slug())
        : sanitize_key((string) get_option('em_site_active_template', ''));
    if ($editing_template === '') {
        $editing_template = 'mayami';
    }
    ?>
    <div class="em-site-header-picker em-site-header-item-editor" data-header-item="<?php echo esc_attr($header_item_slug); ?>" data-template="<?php echo esc_attr($editing_template); ?>" data-config="<?php echo esc_attr((string) wp_json_encode($cfg)); ?>" data-matrix="<?php echo esc_attr($matrix); ?>" data-position="<?php echo esc_attr((string) ($cfg['position'] ?? 'hero_left')); ?>">
        <details class="em-site-collapse em-site-builder__section em-site-header-item-editor__tab" data-item-section="appearance">
            <summary class="em-site-collapse__summary">
                <span class="em-site-collapse__chevron" aria-hidden="true"></span>
                <strong><?php esc_html_e('Apparence', 'em-site'); ?></strong>
            </summary>
            <div class="em-site-collapse__body">
            <?php em_site_admin_render_header_appearance((array) ($cfg['appearance'] ?? []), (string) ($cfg['ratio'] ?? '60-40'), $header_item_slug); ?>
            </div>
        </details>

        <details class="em-site-collapse em-site-builder__section em-site-header-item-editor__tab" data-item-section="composition">
            <summary class="em-site-collapse__summary">
                <span class="em-site-collapse__chevron" aria-hidden="true"></span>
                <strong><?php esc_html_e('Composition', 'em-site'); ?></strong>
            </summary>
            <div class="em-site-collapse__body">
            <div class="em-site-header-picker__controls">
                <div class="em-site-header-picker__line em-site-header-picker__line--composition">
                    <p class="em-site-rubriques-admin__picker-head"><?php esc_html_e('Composition du HEADER', 'em-site'); ?></p>
                    <div class="em-site-header-picker__compo">
                        <div class="em-site-header-picker__matrix" role="radiogroup">
                            <label class="em-site-header-picker__opt">
                                <input type="radio" name="em-site-header-matrix-<?php echo esc_attr($header_item_slug); ?>" value="hero_slider" <?php checked($matrix === 'hero_slider'); ?>>
                                <span><?php esc_html_e('HERO + SLIDER', 'em-site'); ?></span>
                            </label>
                            <label class="em-site-header-picker__opt">
                                <input type="radio" name="em-site-header-matrix-<?php echo esc_attr($header_item_slug); ?>" value="hero" <?php checked($matrix === 'hero'); ?>>
                                <span><?php esc_html_e('HERO seul', 'em-site'); ?></span>
                            </label>
                            <label class="em-site-header-picker__opt">
                                <input type="radio" name="em-site-header-matrix-<?php echo esc_attr($header_item_slug); ?>" value="slider" <?php checked($matrix === 'slider'); ?>>
                                <span><?php esc_html_e('SLIDER seul', 'em-site'); ?></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="em-site-header-picker__line em-site-header-picker__position"<?php echo $matrix === 'hero_slider' ? '' : ' hidden'; ?>>
                    <p class="em-site-rubriques-admin__picker-head"><?php esc_html_e('Position', 'em-site'); ?></p>
                    <div class="em-site-header-picker__position-options">
                            <label class="em-site-header-picker__opt">
                                <input type="radio" name="em-site-header-position-<?php echo esc_attr($header_item_slug); ?>" value="hero_left" <?php checked((string) ($cfg['position'] ?? 'hero_left') !== 'slider_left'); ?>>
                                <span><?php esc_html_e('HERO à gauche', 'em-site'); ?></span>
                            </label>
                            <label class="em-site-header-picker__opt">
                                <input type="radio" name="em-site-header-position-<?php echo esc_attr($header_item_slug); ?>" value="slider_left" <?php checked((string) ($cfg['position'] ?? '') === 'slider_left'); ?>>
                                <span><?php esc_html_e('SLIDER à gauche', 'em-site'); ?></span>
                            </label>
                    </div>
                </div>
            </div>

            <div class="em-site-header-picker__lists">
                <div class="em-site-header-item-editor__hero-wrap"<?php echo in_array($matrix, ['hero', 'hero_slider'], true) ? '' : ' hidden'; ?>>
                    <?php em_site_admin_render_header_item_part_items($header_item_slug, 'hero', $hero_type, (string) ($cfg['hero'] ?? '')); ?>
                </div>

                <div class="em-site-header-item-editor__slider-wrap"<?php echo in_array($matrix, ['slider', 'hero_slider'], true) ? '' : ' hidden'; ?>>
                    <?php em_site_admin_render_header_item_part_items($header_item_slug, 'slider', $slider_type, (string) ($cfg['slider'] ?? '')); ?>
                </div>
            </div>
            </div>
        </details>

        <div class="em-site-header-picker__savebar">
            <button type="button" class="button button-primary em-site-header-item-editor__save" disabled><?php esc_html_e('Enregistrer la composition', 'em-site'); ?></button>
            <p class="em-site-instance-picker__status" aria-live="polite" hidden></p>
        </div>
    </div>
    <?php
}

/**
 * Apparence partagée du HEADER (fond, image optionnelle, marges) + ratio.
 *
 * @param array<string, mixed> $appearance
 */
function em_site_admin_render_header_appearance(array $appearance, string $ratio, string $scope_key = ''): void
{
    $bg = (string) ($appearance['bg'] ?? '');
    $op = max(0, min(100, (int) ($appearance['bg_image_opacity'] ?? 100)));
    $pos = (string) ($appearance['bg_image_pos'] ?? 'cover');
    $bg_image_id = (int) ($appearance['bg_image_id'] ?? 0);
    $bg_thumb = $bg_image_id > 0 ? (string) wp_get_attachment_image_url($bg_image_id, 'medium') : '';
    $has_bg_image = $bg_image_id > 0;
    $bg_field_id = 'em-site-header-appr-bg';
    $scope_key = sanitize_html_class($scope_key);
    if ($scope_key !== '') {
        $bg_field_id .= '-' . $scope_key;
    }
    ?>
    <div class="em-site-header-picker__appearance em-site-appearance em-site-header-appr">
        <div class="em-site-appearance__line em-site-appearance__line--colors">
            <span class="em-site-appearance__title"><?php esc_html_e('Couleurs', 'em-site'); ?></span>
            <div class="em-site-appearance__item" data-role="background">
                <span class="em-site-appearance__label"><?php esc_html_e('Fond', 'em-site'); ?></span>
                <?php em_site_admin_render_color_field([
                    'id'            => $bg_field_id,
                    'value'         => $bg,
                    'input_class'   => 'em-site-header-appr__bg',
                    'preview_label' => __('Fond du HEADER', 'em-site'),
                ]); ?>
            </div>
        </div>

        <div class="em-site-appearance__line em-site-appearance__line--spaces">
            <span class="em-site-appearance__title"><?php esc_html_e('Espacements', 'em-site'); ?></span>
            <span class="em-site-header-appr__pads">
                <label class="em-site-appearance__num">
                    <span class="em-site-appearance__label"><?php esc_html_e('Haut', 'em-site'); ?></span>
                    <input type="number" class="em-site-appearance__num-input em-site-header-appr__pt" min="0" value="<?php echo esc_attr((string) (int) ($appearance['pt'] ?? 0)); ?>">
                </label>
                <label class="em-site-appearance__num">
                    <span class="em-site-appearance__label"><?php esc_html_e('Bas', 'em-site'); ?></span>
                    <input type="number" class="em-site-appearance__num-input em-site-header-appr__pb" min="0" value="<?php echo esc_attr((string) (int) ($appearance['pb'] ?? 0)); ?>">
                </label>
                <label class="em-site-appearance__num">
                    <span class="em-site-appearance__label"><?php esc_html_e('Gauche', 'em-site'); ?></span>
                    <input type="number" class="em-site-appearance__num-input em-site-header-appr__pl" min="0" value="<?php echo esc_attr((string) (int) ($appearance['pl'] ?? 0)); ?>">
                </label>
                <label class="em-site-appearance__num">
                    <span class="em-site-appearance__label"><?php esc_html_e('Droite', 'em-site'); ?></span>
                    <input type="number" class="em-site-appearance__num-input em-site-header-appr__pr" min="0" value="<?php echo esc_attr((string) (int) ($appearance['pr'] ?? 0)); ?>">
                </label>
            </span>
        </div>

        <div class="em-site-appearance__line em-site-appearance__line--fonts em-site-header-appr__layout-line">
            <span class="em-site-appearance__title"><?php esc_html_e('Mise en page', 'em-site'); ?></span>
            <label class="em-site-appearance__font em-site-header-appr__ratio-wrap">
                <span class="em-site-appearance__label"><?php esc_html_e('Ratio HERO/SLIDER', 'em-site'); ?></span>
                <select class="em-site-header-appr__ratio em-site-appearance__font-input">
                    <?php foreach (em_site_admin_header_ratio_choices() as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($ratio, $key); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="em-site-appearance__line em-site-appearance__line--bgimage">
            <span class="em-site-appearance__title"><?php esc_html_e('Image de fond', 'em-site'); ?></span>
            <span class="em-site-header-appr__media em-site-appearance__bgmedia" data-id="<?php echo esc_attr((string) $bg_image_id); ?>">
                <img class="em-site-header-appr__thumb em-site-appearance__bgthumb"<?php echo $bg_thumb !== '' ? ' src="' . esc_url($bg_thumb) . '"' : ''; ?> alt=""<?php echo $bg_thumb === '' ? ' hidden' : ''; ?>>
                <button type="button" class="button button-small em-site-header-appr__pick"><?php esc_html_e('Choisir', 'em-site'); ?></button>
                <button type="button" class="button button-small em-site-header-appr__clear" title="<?php esc_attr_e('Aucune image de fond', 'em-site'); ?>" aria-label="<?php esc_attr_e('Aucune image de fond', 'em-site'); ?>"><?php esc_html_e('Aucune image', 'em-site'); ?></button>
            </span>
            <label class="em-site-appearance__font em-site-header-appr__image-opt"<?php echo $has_bg_image ? '' : ' hidden'; ?>>
                <span class="em-site-appearance__label"><?php esc_html_e('Position', 'em-site'); ?></span>
                <select class="em-site-header-appr__pos em-site-appearance__bgpos-input">
                    <?php foreach (em_site_rubrique_bg_position_choices() as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($pos, $key); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="em-site-appearance__font em-site-header-appr__image-opt"<?php echo $has_bg_image ? '' : ' hidden'; ?>>
                <span class="em-site-appearance__label"><?php esc_html_e('Opacité', 'em-site'); ?></span>
                <input type="range" class="em-site-header-appr__op em-site-appearance__bgopacity-input" min="0" max="100" step="1" value="<?php echo esc_attr((string) $op); ?>" oninput="this.nextElementSibling.textContent=this.value+'%'">
                <output class="em-site-appearance__bgopacity-out"><?php echo esc_html($op . '%'); ?></output>
            </label>
            <label class="em-site-appearance__toggle em-site-header-appr__image-opt"<?php echo $has_bg_image ? '' : ' hidden'; ?>>
                <input type="checkbox" class="em-site-header-appr__mirror" <?php checked(!empty($appearance['bg_image_mirror'])); ?>>
                <span class="em-site-appearance__label"><?php esc_html_e('Miroir', 'em-site'); ?></span>
            </label>
        </div>
    </div>
    <?php
}

/**
 * Rendu du sélecteur HEADER : matrice (HERO / HERO+SLIDER), position, items.
 */
function em_site_admin_render_header_section_picker(string $template): void
{
    $cfg = em_site_admin_header_section_get($template);
    $is_live = $template !== ''
        && function_exists('em_site_get_active_template_slug')
        && em_site_get_active_template_slug() === $template;
    $template_label = function_exists('em_site_get_editing_template_label')
        ? (string) em_site_get_editing_template_label()
        : '';
    $display_mode = in_array((string) ($cfg['display_mode'] ?? ''), ['single', 'multi'], true)
        ? (string) $cfg['display_mode']
        : 'single';
    ?>
    <div
        class="em-site-header-picker"
        data-template="<?php echo esc_attr($template); ?>"
        data-template-label="<?php echo esc_attr($template_label); ?>"
        data-live="<?php echo $is_live ? '1' : '0'; ?>"
        data-matrix="<?php echo esc_attr($cfg['matrix']); ?>"
        data-position="<?php echo esc_attr($cfg['position']); ?>"
        data-config="<?php echo esc_attr((string) wp_json_encode($cfg)); ?>"
    >
        <?php em_site_admin_render_display_mode_controls('em-site-header-display-mode', $display_mode, false, true); ?>

        <?php em_site_admin_render_header_catalog_items($template); ?>

        <div class="em-site-header-picker__savebar">
            <button type="button" class="button button-primary em-site-header-picker__save" disabled><?php esc_html_e('Sauvegarder', 'em-site'); ?></button>
            <p class="em-site-instance-picker__status" aria-live="polite" hidden></p>
        </div>
    </div>
    <?php
}
