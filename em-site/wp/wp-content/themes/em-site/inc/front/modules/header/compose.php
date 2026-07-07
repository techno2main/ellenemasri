<?php
/**
 * Composition de la rubrique HEADER (HERO + SLIDER).
 *
 * Nouvelle logique:
 * - le module HEADER (squelette) pointe vers des items du catalogue `headers`
 * - chaque item HEADER porte sa propre composition (hero/slider/ratio/fond)
 * - fallback legacy conservé sur `em_site_header_<template>`
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Résout la pile CSS de police pour le front HERO/SLIDER.
 */
function em_site_header_font_stack(string $font_key): string
{
    $font_key = sanitize_key($font_key);

    if (function_exists('em_site_rubrique_font_choices')) {
        $choices = em_site_rubrique_font_choices();

        if ($font_key !== '' && isset($choices[$font_key]['stack']) && is_string($choices[$font_key]['stack'])) {
            return (string) $choices[$font_key]['stack'];
        }

        if (isset($choices['archivo_black']['stack']) && is_string($choices['archivo_black']['stack'])) {
            return (string) $choices['archivo_black']['stack'];
        }
    }

    return '"Archivo Black", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif';
}

function em_site_header_active_template(): string
{
    $slug = sanitize_key((string) get_option('em_site_active_template', ''));

    return $slug !== '' ? $slug : 'mayami';
}

function em_site_header_decode_json_field($value): array
{
    if (!is_string($value) || $value === '') {
        return [];
    }

    $decoded = json_decode($value, true);

    return is_array($decoded) ? $decoded : [];
}

function em_site_header_catalog_type_slug(): string
{
    return 'headers';
}

/**
 * @return array<string, string>
 */
function em_site_header_catalog_items(): array
{
    $type = em_site_header_catalog_type_slug();

    if (function_exists('em_site_get_items')) {
        $items = em_site_get_items($type);
        return is_array($items) ? $items : [];
    }

    $items = get_option('em_site_items_' . $type, []);
    if (!is_array($items) || $items === []) {
        // Repli legacy tolérant si un ancien nom d'option existe encore.
        $items = get_option('em_site_items_header', []);
    }

    if (!is_array($items)) {
        return [];
    }

    $normalized = [];
    foreach ($items as $slug => $label) {
        $slug = sanitize_key((string) $slug);
        if ($slug === '') {
            continue;
        }
        $normalized[$slug] = sanitize_text_field((string) $label);
    }

    return $normalized;
}

/**
 * @return array<int, string>
 */
function em_site_header_slug_variants(string $slug): array
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
 * @return array<int, string>
 */
function em_site_header_part_type_candidates(string $keyword): array
{
    $keyword = $keyword === 'slider' ? 'slider' : 'hero';

    if ($keyword === 'hero') {
        return ['hero', 'heroes', 'header'];
    }

    return ['sliders', 'slider'];
}

function em_site_header_part_type_slug(string $keyword): string
{
    $keyword = $keyword === 'slider' ? 'slider' : 'hero';
    $candidates = em_site_header_part_type_candidates($keyword);

    if (!function_exists('em_site_rubrique_type_registry')) {
        return (string) ($candidates[0] ?? '');
    }

    $registry = em_site_rubrique_type_registry();
    $slugs = array_map('strval', array_keys($registry));

    foreach ($candidates as $candidate) {
        if (in_array($candidate, $slugs, true)) {
            return $candidate;
        }
    }

    if (in_array($keyword, $slugs, true)) {
        return $keyword;
    }

    foreach ($slugs as $slug) {
        if (strpos((string) $slug, $keyword) !== false) {
            return (string) $slug;
        }
    }

    foreach ($registry as $slug => $def) {
        $label = strtolower((string) ($def['label'] ?? '') . ' ' . (string) ($def['label_plural'] ?? ''));
        if ($label !== '' && strpos($label, $keyword) !== false) {
            return (string) $slug;
        }
    }

    return '';
}

/**
 * @return array{matrix:string,position:string,hero:string,slider:string,ratio:string,appearance:array<string,mixed>}
 */
function em_site_header_config_defaults(): array
{
    return [
        'matrix' => 'hero_slider',
        'position' => 'hero_left',
        'hero' => '',
        'slider' => '',
        'ratio' => '60-40',
        'appearance' => [
            'bg' => '',
            'bg_image_id' => 0,
            'bg_image_pos' => 'cover',
            'bg_image_opacity' => 100,
            'bg_image_mirror' => false,
            'pt' => 0,
            'pb' => 0,
            'pl' => 0,
            'pr' => 0,
        ],
    ];
}

/**
 * @param array<string, mixed> $raw
 * @return array{matrix:string,position:string,hero:string,slider:string,ratio:string,appearance:array<string,mixed>}
 */
function em_site_header_config_normalize(array $raw): array
{
    $defaults = em_site_header_config_defaults();
    $appearance = is_array($raw['appearance'] ?? null) ? $raw['appearance'] : [];

    $config = array_merge($defaults, $raw);
    $config['appearance'] = array_merge($defaults['appearance'], $appearance);
    $matrix_raw = sanitize_key((string) ($config['matrix'] ?? 'hero'));
    $config['matrix'] = in_array($matrix_raw, ['hero', 'hero_slider', 'slider'], true)
        ? $matrix_raw
        : 'hero';
    $config['position'] = ($config['position'] ?? '') === 'slider_left' ? 'slider_left' : 'hero_left';
    $config['hero'] = sanitize_key((string) ($config['hero'] ?? ''));
    $config['slider'] = sanitize_key((string) ($config['slider'] ?? ''));
    $config['ratio'] = sanitize_key((string) ($config['ratio'] ?? '60-40'));

    if (!in_array($config['ratio'], ['75-25', '70-30', '60-40', '50-50'], true)) {
        $config['ratio'] = '60-40';
    }

    $config['appearance']['bg'] = sanitize_hex_color((string) ($config['appearance']['bg'] ?? '')) ?: '';
    $config['appearance']['bg_image_id'] = max(0, (int) ($config['appearance']['bg_image_id'] ?? 0));
    $config['appearance']['bg_image_pos'] = sanitize_key((string) ($config['appearance']['bg_image_pos'] ?? 'cover'));
    $config['appearance']['bg_image_opacity'] = max(0, min(100, (int) ($config['appearance']['bg_image_opacity'] ?? 100)));
    $config['appearance']['bg_image_mirror'] = !empty($config['appearance']['bg_image_mirror']);
    $config['appearance']['pt'] = max(0, (int) ($config['appearance']['pt'] ?? 0));
    $config['appearance']['pb'] = max(0, (int) ($config['appearance']['pb'] ?? 0));
    $config['appearance']['pl'] = max(0, (int) ($config['appearance']['pl'] ?? 0));
    $config['appearance']['pr'] = max(0, (int) ($config['appearance']['pr'] ?? 0));

    return $config;
}

/**
 * @return array{display_mode:string,item_slugs:array<int,string>}
 */
function em_site_header_instance_config(string $template): array
{
    $instance = get_option('em_site_instance_' . sanitize_key($template) . '_header', []);
    $display_mode = is_array($instance) ? sanitize_key((string) ($instance['display_mode'] ?? 'single')) : 'single';
    $transition_mode = is_array($instance) ? sanitize_key((string) ($instance['transition_mode'] ?? 'manual')) : 'manual';
    if (!in_array($display_mode, ['single', 'multi'], true)) {
        $display_mode = 'single';
    }
    if (!in_array($transition_mode, ['manual', 'auto'], true)) {
        $transition_mode = 'manual';
    }

    $items_map = em_site_header_catalog_items();
    $item_slugs = array_values(array_map('strval', array_keys($items_map)));

    $selected = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

    if ($selected !== '' && $item_slugs !== [] && !in_array($selected, $item_slugs, true)) {
        foreach (em_site_header_slug_variants($selected) as $candidate) {
            if (in_array($candidate, $item_slugs, true)) {
                $selected = $candidate;
                break;
            }
        }
    }

    if ($selected === '' && $item_slugs !== []) {
        $selected = (string) ($item_slugs[0] ?? '');
    }

    if ($item_slugs === [] && $selected !== '') {
        $item_slugs = [$selected];
    }

    if ($item_slugs === []) {
        return ['display_mode' => 'single', 'item_slugs' => []];
    }

    if ($display_mode === 'single') {
        return ['display_mode' => 'single', 'item_slugs' => $selected !== '' ? [$selected] : []];
    }

    $hidden_items = [];
    if (is_array($instance['hidden_items'] ?? null)) {
        foreach ((array) $instance['hidden_items'] as $hidden_slug) {
            $hidden_slug = sanitize_key((string) $hidden_slug);
            if ($hidden_slug !== '' && in_array($hidden_slug, $item_slugs, true)) {
                $hidden_items[] = $hidden_slug;
            }
        }
        $hidden_items = array_values(array_unique($hidden_items));
    }

    $first_item = sanitize_key((string) ($instance['first_item'] ?? $selected));
    $visible = array_values(array_diff($item_slugs, $hidden_items));
    if ($selected !== '' && !in_array($selected, $visible, true)) {
        array_unshift($visible, $selected);
        $visible = array_values(array_unique($visible));
    }
    if ($visible === []) {
        $visible = $item_slugs;
    }

    if ($first_item === '' || !in_array($first_item, $visible, true)) {
        $first_item = (string) ($visible[0] ?? '');
    }

    if ($first_item !== '') {
        $ordered = array_values(array_diff($visible, [$first_item]));
        array_unshift($ordered, $first_item);
        $visible = $ordered;
    }

    return ['display_mode' => 'multi', 'item_slugs' => $visible];
}

/**
 * @return array{slug:string,config:array<string,mixed>}
 */
function em_site_header_item_config(string $header_item_slug): array
{
    foreach (em_site_header_slug_variants($header_item_slug) as $candidate_slug) {
        $raw = get_option('em_site_header_item_cfg_' . $candidate_slug, []);
        if (is_array($raw) && $raw !== []) {
            return [
                'slug' => $candidate_slug,
                'config' => em_site_header_config_normalize($raw),
            ];
        }
    }

    $raw = get_option('em_site_header_item_cfg_' . sanitize_key($header_item_slug), []);

    return [
        'slug' => sanitize_key($header_item_slug),
        'config' => em_site_header_config_normalize(is_array($raw) ? $raw : []),
    ];
}

/**
 * @return array{slug:string,config:array<string,mixed>}
 */
function em_site_header_entry_from_legacy(string $template): array
{
    $legacy = get_option('em_site_header_' . sanitize_key($template), []);
    if (!is_array($legacy)) {
        $legacy = [];
    }

    return [
        'slug' => '__legacy__',
        'config' => em_site_header_config_normalize($legacy),
    ];
}

/**
 * @return array<int, array{slug:string,config:array<string,mixed>}>
 */
function em_site_header_entries(): array
{
    $template = em_site_header_active_template();
    $instance = em_site_header_instance_config($template);
    $entries = [];

    foreach ((array) ($instance['item_slugs'] ?? []) as $header_item_slug) {
        $header_item_slug = sanitize_key((string) $header_item_slug);
        if ($header_item_slug === '') {
            continue;
        }

        $entries[] = em_site_header_item_config($header_item_slug);
    }

    if ($entries === []) {
        $entries[] = em_site_header_entry_from_legacy($template);
    }

    return $entries;
}

function em_site_header_config(): array
{
    $entries = em_site_header_entries();

    if ($entries === []) {
        return em_site_header_config_defaults();
    }

    return is_array($entries[0]['config'] ?? null)
        ? (array) $entries[0]['config']
        : em_site_header_config_defaults();
}

function em_site_header_part_item_slug(array $config, string $part): string
{
    $part = $part === 'slider' ? 'slider' : 'hero';
    $saved = sanitize_key((string) ($config[$part] ?? ''));

    // La valeur choisie dans Rubriques reste la source de vérité.
    if ($saved !== '') {
        return $saved;
    }

    $type = em_site_header_part_type_slug($part);

    if ($type === '' || !function_exists('em_site_get_items')) {
        return '';
    }

    $items = em_site_get_items($type);
    if ($items === []) {
        return '';
    }

    return function_exists('em_site_rubrique_default_item_slug')
        ? em_site_rubrique_default_item_slug($type)
        : (string) array_key_first($items);
}

function em_site_header_hero_item_slug(array $config): string
{
    return em_site_header_part_item_slug($config, 'hero');
}

function em_site_header_slider_item_slug(array $config): string
{
    return em_site_header_part_item_slug($config, 'slider');
}

function em_site_header_part_item(string $part, string $item_slug): array
{
    $part = $part === 'slider' ? 'slider' : 'hero';
    $item_slug = sanitize_key($item_slug);
    $type = em_site_header_part_type_slug($part);
    $candidates = em_site_header_part_type_candidates($part);

    if ($type !== '' && !in_array($type, $candidates, true)) {
        array_unshift($candidates, $type);
    }

    if ($item_slug === '') {
        return [];
    }

    foreach ($candidates as $candidate_type) {
        $candidate_type = sanitize_key((string) $candidate_type);
        if ($candidate_type === '') {
            continue;
        }

        $item = get_option('em_site_item_' . $candidate_type . '_' . $item_slug, []);
        if (is_array($item) && $item !== []) {
            return $item;
        }
    }

    // Repli robuste: retrouve l'item par son slug dans tous les types connus.
    if (function_exists('em_site_rubrique_type_registry')) {
        foreach (array_keys((array) em_site_rubrique_type_registry()) as $candidate_type) {
            $candidate_type = sanitize_key((string) $candidate_type);
            if ($candidate_type === '') {
                continue;
            }
            $item = get_option('em_site_item_' . $candidate_type . '_' . $item_slug, []);
            if (is_array($item) && $item !== []) {
                return $item;
            }
        }
    }

    return [];
}

function em_site_header_hero_item(string $item_slug): array
{
    return em_site_header_part_item('hero', $item_slug);
}

function em_site_header_slider_item(string $item_slug): array
{
    return em_site_header_part_item('slider', $item_slug);
}

/**
 * @return array{size:string,repeat:string,position:string}
 */
function em_site_header_bg_position_css(string $value): array
{
    $map = [
        'cover' => ['size' => 'cover', 'repeat' => 'no-repeat', 'position' => 'center'],
        'contain' => ['size' => 'contain', 'repeat' => 'no-repeat', 'position' => 'center'],
        'center' => ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'center'],
        'tile' => ['size' => 'auto', 'repeat' => 'repeat', 'position' => 'center'],
        'tile-x' => ['size' => 'auto', 'repeat' => 'repeat-x', 'position' => 'center'],
        'tile-y' => ['size' => 'auto', 'repeat' => 'repeat-y', 'position' => 'center'],
        'left' => ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'left center'],
        'right' => ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'right center'],
        'top' => ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'center top'],
        'bottom' => ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'center bottom'],
    ];

    return $map[$value] ?? $map['cover'];
}

/**
 * @return array<int, string>
 */
function em_site_header_shell_style_vars(array $config, array $hero_content): array
{
    $appearance = is_array($config['appearance'] ?? null) ? $config['appearance'] : [];
    $hero_transparent = !empty($hero_content['bg_transparent']);
    $hero_bg = sanitize_hex_color((string) ($hero_content['bg_color'] ?? ''));
    $appearance_bg = sanitize_hex_color((string) ($appearance['bg'] ?? '')) ?: '';
    $bg = !$hero_transparent && $hero_bg !== false && $hero_bg !== null && $hero_bg !== ''
        ? $hero_bg
        : $appearance_bg;
    if (!is_string($bg) || $bg === '') {
        $bg = 'transparent';
    }

    $vars = [
        '--em-rubrique-bg:' . $bg,
        '--em-rubrique-pt:' . max(0, (int) ($appearance['pt'] ?? 0)) . 'px',
        '--em-rubrique-pb:' . max(0, (int) ($appearance['pb'] ?? 0)) . 'px',
        '--em-rubrique-pl:' . max(0, (int) ($appearance['pl'] ?? 0)) . 'px',
        '--em-rubrique-pr:' . max(0, (int) ($appearance['pr'] ?? 0)) . 'px',
    ];

    $bg_image_id = (int) ($appearance['bg_image_id'] ?? 0);
    $bg_url = $bg_image_id > 0 ? (string) wp_get_attachment_image_url($bg_image_id, 'full') : '';
    if ($bg_url === '') {
        $hero_bg = em_site_header_decode_json_field((string) ($hero_content['bg_image'] ?? ''));
        $hero_bg_id = (int) ($hero_bg['id'] ?? 0);
        $bg_url = $hero_bg_id > 0 ? (string) wp_get_attachment_image_url($hero_bg_id, 'full') : '';
    }

    if ($bg_url !== '') {
        $bg_pos = em_site_header_bg_position_css((string) ($appearance['bg_image_pos'] ?? 'cover'));
        $opacity = max(0, min(100, (int) ($appearance['bg_image_opacity'] ?? 100)));
        $vars[] = '--em-rubrique-bg-image:url(' . esc_url_raw($bg_url) . ')';
        $vars[] = '--em-rubrique-bg-size:' . $bg_pos['size'];
        $vars[] = '--em-rubrique-bg-repeat:' . $bg_pos['repeat'];
        $vars[] = '--em-rubrique-bg-position:' . $bg_pos['position'];
        $vars[] = '--em-rubrique-bg-opacity:' . round($opacity / 100, 2);
        $vars[] = '--em-rubrique-bg-transform:' . (!empty($appearance['bg_image_mirror']) ? 'scaleX(-1)' : 'none');
    }

    $arrow_down = em_site_header_decode_json_field((string) ($hero_content['arrow_down'] ?? ''));
    $nav_color = sanitize_hex_color((string) ($arrow_down['color'] ?? ''));
    if (!is_string($nav_color) || $nav_color === '') {
        $nav_color = sanitize_hex_color((string) ($hero_content['text_color'] ?? ''));
    }
    if (is_string($nav_color) && $nav_color !== '') {
        $vars[] = '--em-header-switch-color:' . $nav_color;
    }

    return $vars;
}

function em_site_header_ratio_columns(string $ratio, bool $slider_left): string
{
    $ratio = sanitize_key($ratio);
    $hero_max = [
        '75-25' => 1000,
        '70-30' => 860,
        '60-40' => 640,
        '50-50' => 430,
    ];

    $hero = 'minmax(0, ' . (int) ($hero_max[$ratio] ?? 640) . 'px)';
    $slider = 'minmax(320px, 430px)';

    return $slider_left ? ($slider . ' ' . $hero) : ($hero . ' ' . $slider);
}

function em_site_header_is_ready(): bool
{
    foreach (em_site_header_entries() as $entry) {
        $config = is_array($entry['config'] ?? null) ? $entry['config'] : em_site_header_config_defaults();
        $matrix = (string) ($config['matrix'] ?? 'hero');

        $hero_item_slug = em_site_header_hero_item_slug($config);
        $has_hero_source = $hero_item_slug !== '';

        $has_slider_source = false;
        $slider_item_slug = em_site_header_slider_item_slug($config);
        if ($slider_item_slug !== '') {
            $slider_item = em_site_header_slider_item($slider_item_slug);
            $slider_content = is_array($slider_item['content'] ?? null) ? $slider_item['content'] : [];
            $slider_meta = em_site_header_decode_json_field((string) ($slider_content['slider'] ?? ''));
            $slides = is_array($slider_meta['slides'] ?? null) ? $slider_meta['slides'] : [];
            $has_slider_source = $slides !== [];
        }

        if ($matrix === 'slider') {
            if ($has_slider_source) {
                return true;
            }
            continue;
        }

        if ($matrix === 'hero') {
            if ($has_hero_source) {
                return true;
            }
            continue;
        }

        if ($has_hero_source || $has_slider_source) {
            return true;
        }
    }

    return false;
}
