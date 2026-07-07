<?php
/**
 * Positions menu Catalogues.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug parent Catalogues avec fallback si le module legacy n'est pas chargé.
 */
function em_site_admin_catalog_parent_menu_slug_fallback(): string
{
    if (function_exists('em_site_catalog_parent_menu_slug')) {
        return em_site_catalog_parent_menu_slug();
    }

    return 'em-catalog';
}

/**
 * Filet au-dessus du bloc Catalogues (séparation visuelle après MEDIAS).
 */
function em_site_admin_menu_catalog_separator_top_position(): int
{
    return (int) em_site_admin_menu_position_for_slug('separator-em-site-after-medias');
}

/**
 * Position parent « CATALOGUES » (bloc navigation principale).
 */
function em_site_admin_menu_position_catalog_parent(): int
{
    return (int) em_site_admin_menu_position_for_slug(em_site_admin_catalog_parent_menu_slug_fallback());
}

/**
 * @deprecated Utiliser em_site_admin_menu_position_catalog_parent().
 */
function em_site_admin_menu_catalog_section_label_position(): int
{
    return em_site_admin_menu_position_catalog_parent();
}

/**
 * Position menu d'un module catalogue (HEROS, SLIDERS, …).
 */
function em_site_admin_menu_position_for_catalog_module(string $module_slug): int
{
    if (!function_exists('em_site_catalog_menu_definitions')) {
        return em_site_admin_menu_position_catalog_parent() + 1;
    }

    $definition = em_site_catalog_menu_definitions()[$module_slug] ?? null;
    $hub_slug = is_array($definition) ? (string) ($definition['slug'] ?? '') : '';

    if ($hub_slug === '') {
        return em_site_admin_menu_position_catalog_parent() + 1;
    }

    return (int) em_site_admin_menu_position_for_slug($hub_slug);
}

/**
 * Position menu HEROS (catalogues).
 */
function em_site_admin_menu_position_catalog_heros(): int
{
    return em_site_admin_menu_position_for_catalog_module('heros');
}

/**
 * Position menu SLIDERS (catalogues).
 */
function em_site_admin_menu_position_catalog_sliders(): int
{
    return em_site_admin_menu_position_for_catalog_module('sliders');
}

/**
 * @deprecated Utiliser em_site_admin_menu_position_catalog_heros().
 */
function em_site_admin_menu_position_catalog_sommaire(): int
{
    return em_site_admin_menu_position_catalog_heros();
}

/**
 * Filet sous le bloc Catalogues.
 */
function em_site_admin_menu_catalog_separator_bottom_position(): int
{
    return (int) em_site_admin_menu_position_for_slug('separator-em-site-after-catalog');
}

/**
 * Position HEROS dans le menu admin (legacy — catalogue).
 */
function em_site_admin_menu_position_hero(): int
{
    return em_site_admin_menu_position_catalog_heros();
}

/**
 * Position SLIDERS dans le menu admin (legacy — catalogue).
 */
function em_site_admin_menu_position_slider(): int
{
    return em_site_admin_menu_position_catalog_sliders();
}

