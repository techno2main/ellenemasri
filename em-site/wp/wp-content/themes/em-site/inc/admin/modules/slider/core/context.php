<?php
/**
 * Contexte et resolution de pages du module Slider (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retourne le slug de la page hub Sliders.
 */
function em_wp_slider_hub_menu_slug(): string
{
    return 'em-catalog-sliders';
}

/**
 * Retourne le slug de style du Slider actif sur le front.
 */
function em_wp_slider_active_style_slug(): string
{
    if (function_exists('em_wp_header_get_options_for_front')) {
        $header = em_wp_header_get_options_for_front();
        $slug = sanitize_key((string) ($header['slider_slug'] ?? ''));

        if ($slug !== '') {
            return $slug;
        }
    }

    $saved = get_option('em_wp_slider_active_style', 'mayami');

    return em_wp_slider_sanitize_active_style($saved);
}

/**
 * Sanitize le slug du slider actif.
 *
 * @param mixed $value
 */
function em_wp_slider_sanitize_active_style($value): string
{
    $slug = sanitize_key((string) $value);

    if (function_exists('em_wp_slider_normalize_catalog_slug')) {
        $slug = em_wp_slider_normalize_catalog_slug($slug);
    }

    $definitions = em_wp_slider_style_definitions();

    if (isset($definitions[$slug])) {
        return $slug;
    }

    $keys = array_keys($definitions);

    return $keys[0] ?? 'slider-mayami-default';
}

/**
 * Retourne la definition des entrees catalogue Slider.
 */
function em_wp_slider_style_definitions(): array
{
    if (!function_exists('em_wp_slider_catalog_entries')) {
        return [
            'slider-mayami-default' => [
                'label'      => 'Mayami',
                'menu_title' => __('Slider Mayami default', 'em-wp'),
                'page_slug'  => 'em-cs-slider-mayami-default',
            ],
        ];
    }

    $definitions = [];

    foreach (em_wp_slider_catalog_entries() as $catalog_slug => $entry) {
        $label = (string) ($entry['label'] ?? $catalog_slug);
        $definitions[$catalog_slug] = [
            'label'      => $label,
            'menu_title' => $label,
            'page_slug'  => em_wp_slider_catalog_edit_page_slug($catalog_slug),
        ];
    }

    return $definitions;
}

/**
 * Retourne le slug du menu parent Slider.
 */
function em_wp_slider_parent_menu_slug(): string
{
    return em_wp_slider_hub_menu_slug();
}

/**
 * Retourne les slugs de page admin Slider.
 */
function em_wp_slider_admin_page_slugs(): array
{
    $slugs = [
        em_wp_slider_hub_menu_slug(),
    ];

    if (function_exists('em_wp_catalog_parent_menu_slug')) {
        $slugs[] = em_wp_catalog_parent_menu_slug();
    }

    return array_merge(
        $slugs,
        wp_list_pluck(em_wp_slider_style_definitions(), 'page_slug')
    );
}

/**
 * Retourne le slug Slider depuis la page admin.
 */
function em_wp_slider_style_from_page_slug(string $page_slug): string
{
    if ($page_slug === em_wp_slider_hub_menu_slug()) {
        return '';
    }

    if (function_exists('em_wp_slider_catalog_slug_from_page')) {
        $from_catalog = em_wp_slider_catalog_slug_from_page($page_slug);
        if ($from_catalog !== '') {
            return $from_catalog;
        }
    }

    $legacy = [
        'em-slider-mayami' => 'slider-mayami-default',
        'em-slider-ellene' => 'slider-ellene-default',
    ];

    if (isset($legacy[$page_slug])) {
        return $legacy[$page_slug];
    }

    foreach (em_wp_slider_style_definitions() as $style_slug => $definition) {
        if (($definition['page_slug'] ?? '') === $page_slug) {
            return $style_slug;
        }
    }

    return '';
}

/**
 * Retourne le contexte admin Slider courant.
 */
function em_wp_slider_get_admin_context(): array
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $style_slug = em_wp_slider_style_from_page_slug($page_slug);
    $definitions = em_wp_slider_style_definitions();

    if ($style_slug === '') {
        return [
            'style_slug'  => '',
            'label'       => '',
            'page_slug'   => em_wp_slider_hub_menu_slug(),
            'option_name' => '',
            'group'       => '',
        ];
    }

    $definition = $definitions[$style_slug] ?? $definitions['mayami'];

    return [
        'style_slug'  => $style_slug,
        'label'       => (string) ($definition['label'] ?? 'Mayami'),
        'page_slug'   => (string) ($definition['page_slug'] ?? 'em-slider-mayami'),
        'option_name' => em_wp_slider_option_name($style_slug),
        'group'       => em_wp_slider_group_name($style_slug),
    ];
}

/**
 * Retourne le nom d'option WordPress pour une variante Slider.
 */
function em_wp_slider_option_name(string $style_slug): string
{
    if (function_exists('em_wp_slider_catalog_item_option_name')) {
        return em_wp_slider_catalog_item_option_name($style_slug);
    }

    return 'em_wp_slider_' . sanitize_key($style_slug) . '_options';
}

/**
 * Retourne le nom de groupe Settings API pour une variante Slider.
 */
function em_wp_slider_group_name(string $style_slug): string
{
    return 'em_wp_slider_' . sanitize_key($style_slug) . '_group';
}

/**
 * Resout le dossier d'assets admin (vue) pour une variante Slider.
 *
 * Les slugs catalogue (ex. slider-mayami-default) ne correspondent pas aux
 * dossiers d'assets (mayami/ellene) : on retombe sur une vue existante.
 */
function em_wp_slider_admin_asset_view_slug(string $style_slug): string
{
    $style_slug = sanitize_key($style_slug);
    $theme_dir = get_template_directory();
    $js_base = $theme_dir . '/assets/admin/js/modules/slider/';
    $css_base = $theme_dir . '/assets/admin/css/modules/slider/';

    $has_view_assets = static function (string $view_slug) use ($js_base, $css_base): bool {
        return is_dir($js_base . $view_slug) && is_file($css_base . $view_slug . '/slider.css');
    };

    if ($style_slug !== '' && $has_view_assets($style_slug)) {
        return $style_slug;
    }

    if (strpos($style_slug, 'ellene') !== false && $has_view_assets('ellene')) {
        return 'ellene';
    }

    return 'mayami';
}

