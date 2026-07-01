<?php
/**
 * Contexte et resolution de pages du module Hero (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retourne le slug de la page hub Heros.
 */
function em_wp_hero_hub_menu_slug(): string
{
    return 'em-wp-catalog-heros';
}

/**
 * Mapping pages admin legacy V1 -> slug catalogue.
 *
 * @return array<string, string>
 */
function em_wp_hero_legacy_page_slug_map(): array
{
    return [
        'em-wp-hero-mayami' => 'hero-mayami-default',
        'em-wp-hero-ellene' => 'hero-ellene-default',
    ];
}

/**
 * Retourne le slug catalogue hero actif (fallback V1).
 */
function em_wp_hero_active_style_slug(): string
{
    if (function_exists('em_wp_header_get_options_for_front')) {
        $header = em_wp_header_get_options_for_front();
        $slug = sanitize_key((string) ($header['hero_slug'] ?? ''));

        if ($slug !== '') {
            return $slug;
        }
    }

    $saved = get_option('em_wp_hero_active_style', 'mayami');

    return em_wp_hero_sanitize_active_style($saved);
}

/**
 * Sanitize le slug du hero actif.
 *
 * @param mixed $value
 */
function em_wp_hero_sanitize_active_style($value): string
{
    $slug = sanitize_key((string) $value);

    if (function_exists('em_wp_hero_normalize_catalog_slug')) {
        $slug = em_wp_hero_normalize_catalog_slug($slug);
    }

    $definitions = em_wp_hero_style_definitions();

    if (isset($definitions[$slug])) {
        return $slug;
    }

    $keys = array_keys($definitions);

    return $keys[0] ?? 'hero-mayami-default';
}

/**
 * Retourne la definition des entrees catalogue HERO.
 */
function em_wp_hero_style_definitions(): array
{
    if (!function_exists('em_wp_hero_catalog_entries')) {
        return [
            'hero-mayami-default' => [
                'label'      => 'Mayami',
                'menu_title' => __('Hero Mayami default', 'em-wp'),
                'page_slug'  => 'em-wp-ch-hero-mayami-default',
            ],
        ];
    }

    $definitions = [];

    foreach (em_wp_hero_catalog_entries() as $catalog_slug => $entry) {
        $label = (string) ($entry['label'] ?? $catalog_slug);
        $definitions[$catalog_slug] = [
            'label'      => $label,
            'menu_title' => $label,
            'page_slug'  => em_wp_hero_catalog_edit_page_slug($catalog_slug),
        ];
    }

    return $definitions;
}

/**
 * Retourne le slug du menu parent Hero.
 */
function em_wp_hero_parent_menu_slug(): string
{
    return em_wp_hero_hub_menu_slug();
}

/**
 * Retourne les slugs de page admin Hero.
 */
function em_wp_hero_admin_page_slugs(): array
{
    $slugs = [
        em_wp_hero_hub_menu_slug(),
    ];

    if (function_exists('em_wp_catalog_parent_menu_slug')) {
        $slugs[] = em_wp_catalog_parent_menu_slug();
    }

    return array_merge(
        $slugs,
        wp_list_pluck(em_wp_hero_style_definitions(), 'page_slug')
    );
}

/**
 * Retourne le slug HERO depuis la page admin.
 */
function em_wp_hero_style_from_page_slug(string $page_slug): string
{
    if ($page_slug === em_wp_hero_hub_menu_slug()) {
        return '';
    }

    if (function_exists('em_wp_hero_catalog_slug_from_page')) {
        $from_catalog = em_wp_hero_catalog_slug_from_page($page_slug);
        if ($from_catalog !== '') {
            return $from_catalog;
        }
    }

    $legacy = em_wp_hero_legacy_page_slug_map();
    if (isset($legacy[$page_slug])) {
        return $legacy[$page_slug];
    }

    foreach (em_wp_hero_style_definitions() as $style_slug => $definition) {
        if (($definition['page_slug'] ?? '') === $page_slug) {
            return $style_slug;
        }
    }

    return '';
}

/**
 * Retourne le contexte admin HERO courant.
 */
function em_wp_hero_get_admin_context(): array
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $style_slug = em_wp_hero_style_from_page_slug($page_slug);
    $definitions = em_wp_hero_style_definitions();

    if ($style_slug === '') {
        return [
            'style_slug'  => '',
            'label'       => '',
            'page_slug'   => em_wp_hero_hub_menu_slug(),
            'option_name' => '',
            'group'       => '',
        ];
    }

    $definition = $definitions[$style_slug] ?? $definitions['mayami'];

    return [
        'style_slug'  => $style_slug,
        'label'       => (string) ($definition['label'] ?? 'Mayami'),
        'page_slug'   => (string) ($definition['page_slug'] ?? 'em-wp-hero-mayami'),
        'option_name' => em_wp_hero_option_name($style_slug),
        'group'       => em_wp_hero_group_name($style_slug),
    ];
}

/**
 * Retourne le nom d'option WordPress pour une variante HERO.
 */
function em_wp_hero_option_name(string $style_slug): string
{
    if (function_exists('em_wp_hero_catalog_item_option_name')) {
        return em_wp_hero_catalog_item_option_name($style_slug);
    }

    return 'em_wp_hero_' . sanitize_key($style_slug) . '_options';
}

/**
 * Retourne le nom de groupe Settings API pour une variante HERO.
 */
function em_wp_hero_group_name(string $style_slug): string
{
    return 'em_wp_hero_' . sanitize_key($style_slug) . '_group';
}
