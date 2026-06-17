<?php
/**
 * Slugs réservés (intrus / expulsion).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slugs réservés au bloc « Rubriques du site » (ne doivent pas être expulsés).
 *
 * @return string[]
 */
function em_wp_admin_rubrique_reserved_menu_slugs(): array
{
    $slugs = [
        em_wp_admin_rubriques_page_slug(),
        'separator-em-wp-site-top',
        'separator-em-wp-bottom',
        'upload.php',
        'media-new.php',
        em_wp_admin_media_parent_menu_slug(),
        'separator-em-wp-after-medias',
        'separator-em-wp-after-catalog',
        'separator-em-wp-after-templates',
        'separator-em-wp-before-settings',
        'em-wp-menu-wp-settings-label',
        'themes.php',
        'options-general.php',
        'plugins.php',
    ];

    if (function_exists('em_wp_catalog_parent_menu_slug')) {
        $slugs[] = em_wp_catalog_parent_menu_slug();
    }

    if (function_exists('em_wp_catalog_registered_hub_menu_slugs')) {
        $slugs = array_merge($slugs, em_wp_catalog_registered_hub_menu_slugs());
    }

    if (function_exists('em_wp_catalog_sidebar_entry_page_slugs')) {
        $slugs = array_merge($slugs, em_wp_catalog_sidebar_entry_page_slugs());
    }

    if (function_exists('em_wp_catalog_sommaire_menu_slug')) {
        $slugs[] = em_wp_catalog_sommaire_menu_slug();
    }

    if (function_exists('em_wp_admin_template_parent_page_slug')) {
        $slugs[] = em_wp_admin_template_parent_page_slug();
    }

    if (function_exists('em_wp_admin_template_entry_page_slugs')) {
        $slugs = array_merge($slugs, em_wp_admin_template_entry_page_slugs());
    }

    if (function_exists('em_wp_admin_template_choice_page_slug')) {
        $slugs[] = em_wp_admin_template_choice_page_slug();
    }

    if (function_exists('em_wp_admin_templates_page_slug')) {
        $slugs[] = em_wp_admin_templates_page_slug();
    }

    if (function_exists('em_wp_admin_template_create_page_slug')) {
        $slugs[] = em_wp_admin_template_create_page_slug();
    }

    if (function_exists('em_wp_admin_dashboard_page_slug')) {
        $slugs[] = em_wp_admin_dashboard_page_slug();
    }

    if (function_exists('em_wp_admin_site_rubrique_definitions')) {
        foreach (em_wp_admin_site_rubrique_definitions() as $definition) {
            $page_slug = (string) ($definition['page_slug'] ?? '');
            if ($page_slug !== '') {
                $slugs[] = $page_slug;
            }
        }
    }

    return array_values(array_unique($slugs));
}

/**
 * Slugs réservés au bloc « Catalogues ».
 *
 * @return string[]
 */
function em_wp_admin_catalog_reserved_menu_slugs(): array
{
    $slugs = [];

    if (function_exists('em_wp_admin_dashboard_page_slug')) {
        $slugs[] = em_wp_admin_dashboard_page_slug();
    }

    $slugs[] = 'upload.php';
    $slugs[] = 'media-new.php';
    $slugs[] = em_wp_admin_media_parent_menu_slug();

    if (function_exists('em_wp_catalog_parent_menu_slug')) {
        $slugs[] = em_wp_catalog_parent_menu_slug();
    }

    if (function_exists('em_wp_catalog_registered_hub_menu_slugs')) {
        $slugs = array_merge($slugs, em_wp_catalog_registered_hub_menu_slugs());
    }

    if (function_exists('em_wp_catalog_sidebar_entry_page_slugs')) {
        $slugs = array_merge($slugs, em_wp_catalog_sidebar_entry_page_slugs());
    }

    if (function_exists('em_wp_catalog_sommaire_menu_slug')) {
        $slugs[] = em_wp_catalog_sommaire_menu_slug();
    }

    return array_values(array_unique($slugs));
}
