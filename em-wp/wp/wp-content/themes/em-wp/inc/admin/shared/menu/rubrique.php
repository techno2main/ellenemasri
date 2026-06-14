<?php
/**
 * Positions et helpers bloc Rubriques du site.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slugs des modules dans le bloc « Rubriques du site » (ordre du menu).
 *
 * @return string[]
 */
function em_wp_admin_site_rubrique_modules(): array
{
    return em_wp_get_site_rubrique_order();
}

/**
 * Première position menu d'un module Rubriques.
 */
function em_wp_admin_site_rubrique_menu_base(): int
{
    return function_exists('em_wp_admin_menu_rubrique_block_base')
        ? em_wp_admin_menu_rubrique_block_base()
        : 55;
}

/**
 * Position menu admin d'un module Rubriques.
 */
function em_wp_admin_menu_position_for_site_module(string $module_slug): int
{
    if (function_exists('em_wp_admin_site_rubrique_definitions')) {
        $definition = em_wp_admin_site_rubrique_definitions()[$module_slug] ?? null;
        $page_slug = is_array($definition) ? (string) ($definition['page_slug'] ?? '') : '';

        if ($page_slug !== '') {
            return (int) em_wp_admin_menu_position_for_slug($page_slug);
        }
    }

    return em_wp_admin_site_rubrique_menu_base();
}

/**
 * Position TOP-BAR dans le menu admin.
 */
function em_wp_admin_menu_position_top_bar(): int
{
    return em_wp_admin_menu_position_for_site_module('top-bar');
}

/**
 * Position HEADER dans le menu admin.
 */
function em_wp_admin_menu_position_header(): int
{
    return em_wp_admin_menu_position_for_site_module('header');
}

/**
 * Position du filet au-dessus de « Rubriques du site ».
 */
function em_wp_admin_menu_separator_above_site_position(): int
{
    return (int) em_wp_admin_menu_position_for_slug('separator-em-wp-site-top');
}

/**
 * Position du libellé « Rubriques du site ».
 */
function em_wp_admin_menu_section_label_position(): int
{
    if (function_exists('em_wp_admin_rubriques_page_slug')) {
        return (int) em_wp_admin_menu_position_for_slug(em_wp_admin_rubriques_page_slug());
    }

    return em_wp_admin_site_rubrique_menu_base() - 1;
}

/**
 * Position du séparateur sous le bloc Rubriques.
 */
function em_wp_admin_menu_separator_bottom_position(): int
{
    return (int) em_wp_admin_menu_position_for_slug('separator-em-wp-bottom');
}

/**
 * Filet entre Templates et le libellé Paramètres.
 */
function em_wp_admin_menu_settings_separator_position(): int
{
    return (int) em_wp_admin_menu_position_for_slug('separator-em-wp-before-settings');
}

/**
 * Position du libellé « Paramètres » (menus WP natifs).
 */
function em_wp_admin_menu_wp_settings_label_position(): int
{
    return (int) em_wp_admin_menu_position_for_slug('em-wp-menu-wp-settings-label');
}
