<?php
/**
 * Positions et helpers bloc Rubriques du site.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slugs des modules dans le bloc « Rubriques du site » (ordre du menu).
 *
 * @return string[]
 */
function em_site_admin_site_rubrique_modules(): array
{
    if (function_exists('em_site_admin_has_template_context')
        && em_site_admin_has_template_context()
        && function_exists('em_site_get_rubrique_order_for_template')) {
        return em_site_get_rubrique_order_for_template();
    }

    return em_site_get_site_rubrique_order();
}

/**
 * Première position menu d'un module Rubriques.
 */
function em_site_admin_site_rubrique_menu_base(): int
{
    return function_exists('em_site_admin_menu_rubrique_block_base')
        ? em_site_admin_menu_rubrique_block_base()
        : 55;
}

/**
 * Position menu admin d'un module Rubriques.
 */
function em_site_admin_menu_position_for_site_module(string $module_slug): int
{
    if (function_exists('em_site_admin_site_rubrique_definitions')) {
        $definition = em_site_admin_site_rubrique_definitions()[$module_slug] ?? null;
        $page_slug = is_array($definition) ? (string) ($definition['page_slug'] ?? '') : '';

        if ($page_slug !== '') {
            return (int) em_site_admin_menu_position_for_slug($page_slug);
        }
    }

    return em_site_admin_site_rubrique_menu_base();
}

/**
 * Slugs menu des modules Rubriques (sous-menus visuels sous RUBRIQUES).
 *
 * @return string[]
 */
function em_site_admin_rubrique_menu_child_slugs(): array
{
    if (function_exists('em_site_admin_should_show_rubrique_menus') && !em_site_admin_should_show_rubrique_menus()) {
        return [];
    }

    if (!function_exists('em_site_admin_site_rubrique_definitions')) {
        return [];
    }

    $slugs = [];

    foreach (em_site_admin_site_rubrique_definitions() as $definition) {
        $page_slug = (string) ($definition['page_slug'] ?? '');

        if ($page_slug !== '') {
            $slugs[] = $page_slug;
        }
    }

    return array_values(array_unique($slugs));
}

/**
 * Position TOP-BAR dans le menu admin.
 */
function em_site_admin_menu_position_top_bar(): int
{
    return em_site_admin_menu_position_for_site_module('top-bar');
}

/**
 * Position HEADER dans le menu admin.
 */
function em_site_admin_menu_position_header(): int
{
    return em_site_admin_menu_position_for_site_module('header');
}

/**
 * Position du filet au-dessus de « Rubriques du site ».
 */
function em_site_admin_menu_separator_above_site_position(): int
{
    return (int) em_site_admin_menu_position_for_slug('separator-em-site-site-top');
}

/**
 * Position du libellé « Rubriques du site ».
 */
function em_site_admin_menu_section_label_position(): int
{
    if (function_exists('em_site_admin_rubriques_page_slug')) {
        return (int) em_site_admin_menu_position_for_slug(em_site_admin_rubriques_page_slug());
    }

    return em_site_admin_site_rubrique_menu_base() - 1;
}

/**
 * Position du séparateur sous le bloc Rubriques.
 */
function em_site_admin_menu_separator_bottom_position(): int
{
    return (int) em_site_admin_menu_position_for_slug('separator-em-site-bottom');
}

/**
 * Filet entre Templates et le libellé Paramètres.
 */
function em_site_admin_menu_settings_separator_position(): int
{
    return (int) em_site_admin_menu_position_for_slug('separator-em-site-before-settings');
}

/**
 * Position du libellé « Paramètres » (menus WP natifs).
 */
function em_site_admin_menu_wp_settings_label_position(): int
{
    return (int) em_site_admin_menu_position_for_slug('em-site-menu-wp-settings-label');
}

/**
 * Slugs admin rattachés à une rubrique (hub + variantes).
 *
 * @return string[]
 */
function em_site_admin_rubrique_module_admin_page_slugs(string $module_slug): array
{
    if (!function_exists('em_site_admin_site_rubrique_definitions')) {
        return [];
    }

    $definitions = em_site_admin_site_rubrique_definitions();
    $definition = $definitions[$module_slug] ?? null;

    if (!is_array($definition)) {
        return [];
    }

    $slugs = [];
    $hub_slug = (string) ($definition['page_slug'] ?? '');

    if ($hub_slug !== '') {
        $slugs[] = $hub_slug;
    }

    if (function_exists('em_site_admin_site_rubrique_entry_page_slug')) {
        $entry_slug = em_site_admin_site_rubrique_entry_page_slug($module_slug);

        if ($entry_slug !== '') {
            $slugs[] = $entry_slug;
        }
    }

    return array_values(array_unique(array_filter($slugs)));
}

/**
 * Slug menu rubrique à surligner pour la page admin courante.
 */
function em_site_admin_rubrique_menu_highlight_slug(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);

    if ($page_slug === '' || !function_exists('em_site_admin_site_rubrique_definitions')) {
        return '';
    }

    foreach (em_site_admin_site_rubrique_definitions() as $module_slug => $definition) {
        $hub_slug = (string) ($definition['page_slug'] ?? '');

        if ($hub_slug === '') {
            continue;
        }

        if (in_array($page_slug, em_site_admin_rubrique_module_admin_page_slugs($module_slug), true)) {
            return $hub_slug;
        }
    }

    return '';
}
