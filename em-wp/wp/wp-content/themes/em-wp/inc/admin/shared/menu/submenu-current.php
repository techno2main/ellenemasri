<?php
/**
 * Sous-menu actif — point blanc partagé (rubriques, catalogues…).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe CSS du point blanc sur l'entrée sous-menu active.
 */
function em_wp_admin_menu_submenu_current_class(): string
{
    return 'em-wp-menu-submenu-current';
}

/**
 * Slug menu à surligner pour la page admin courante (rubriques + catalogues).
 */
function em_wp_admin_menu_submenu_highlight_slug(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);

    if ($page_slug === '') {
        return '';
    }

    if (function_exists('em_wp_admin_rubrique_menu_highlight_slug')) {
        $rubrique_slug = em_wp_admin_rubrique_menu_highlight_slug($page_slug);

        if ($rubrique_slug !== '') {
            return $rubrique_slug;
        }
    }

    if (function_exists('em_wp_admin_catalog_menu_highlight_slug')) {
        $catalog_slug = em_wp_admin_catalog_menu_highlight_slug($page_slug);

        if ($catalog_slug !== '') {
            return $catalog_slug;
        }
    }

    return '';
}

/**
 * Met en surbrillance l'entrée sous-menu active dans le menu latéral.
 *
 * @param mixed $parent_file
 * @return mixed
 */
function em_wp_admin_highlight_submenu_parent($parent_file)
{
    global $plugin_page;

    if (!is_string($plugin_page) || $plugin_page === '') {
        return $parent_file;
    }

    $highlight_slug = em_wp_admin_menu_submenu_highlight_slug($plugin_page);

    if ($highlight_slug !== '') {
        return $highlight_slug;
    }

    return $parent_file;
}
add_filter('parent_file', 'em_wp_admin_highlight_submenu_parent');
