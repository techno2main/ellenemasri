<?php
/**
 * Positions menu Catalogues.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filet au-dessus du bloc Catalogues (séparation visuelle après MEDIAS).
 */
function em_wp_admin_menu_catalog_separator_top_position(): int
{
    return (int) em_wp_admin_menu_position_for_slug('separator-em-wp-after-medias');
}

/**
 * Position parent « CATALOGUES » (bloc navigation principale).
 */
function em_wp_admin_menu_position_catalog_parent(): int
{
    return (int) em_wp_admin_menu_position_for_slug(em_wp_catalog_parent_menu_slug());
}

/**
 * @deprecated Utiliser em_wp_admin_menu_position_catalog_parent().
 */
function em_wp_admin_menu_catalog_section_label_position(): int
{
    return em_wp_admin_menu_position_catalog_parent();
}

/**
 * Position menu d'un module catalogue (HEROS, SLIDERS, …).
 */
function em_wp_admin_menu_position_for_catalog_module(string $module_slug): int
{
    if (!function_exists('em_wp_catalog_menu_definitions')) {
        return em_wp_admin_menu_position_catalog_parent() + 1;
    }

    $definition = em_wp_catalog_menu_definitions()[$module_slug] ?? null;
    $hub_slug = is_array($definition) ? (string) ($definition['slug'] ?? '') : '';

    if ($hub_slug === '') {
        return em_wp_admin_menu_position_catalog_parent() + 1;
    }

    return (int) em_wp_admin_menu_position_for_slug($hub_slug);
}

/**
 * Position menu HEROS (catalogues).
 */
function em_wp_admin_menu_position_catalog_heros(): int
{
    return em_wp_admin_menu_position_for_catalog_module('heros');
}

/**
 * Position menu SLIDERS (catalogues).
 */
function em_wp_admin_menu_position_catalog_sliders(): int
{
    return em_wp_admin_menu_position_for_catalog_module('sliders');
}

/**
 * @deprecated Utiliser em_wp_admin_menu_position_catalog_heros().
 */
function em_wp_admin_menu_position_catalog_sommaire(): int
{
    return em_wp_admin_menu_position_catalog_heros();
}

/**
 * Filet sous le bloc Catalogues.
 */
function em_wp_admin_menu_catalog_separator_bottom_position(): int
{
    return (int) em_wp_admin_menu_position_for_slug('separator-em-wp-after-catalog');
}

/**
 * Position HEROS dans le menu admin (legacy — catalogue).
 */
function em_wp_admin_menu_position_hero(): int
{
    return em_wp_admin_menu_position_catalog_heros();
}

/**
 * Position SLIDERS dans le menu admin (legacy — catalogue).
 */
function em_wp_admin_menu_position_slider(): int
{
    return em_wp_admin_menu_position_catalog_sliders();
}
