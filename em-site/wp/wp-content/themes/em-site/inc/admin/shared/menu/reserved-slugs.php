<?php
/**
 * Slugs réservés (intrus / expulsion).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slugs réservés au bloc « Rubriques du site » (ne doivent pas être expulsés).
 *
 * @return string[]
 */
function em_site_admin_rubrique_reserved_menu_slugs(): array
{
    $catalog_legacy_enabled = function_exists('em_site_catalog_legacy_admin_enabled')
        ? em_site_catalog_legacy_admin_enabled()
        : false;

    $slugs = [
        em_site_admin_rubriques_page_slug(),
        'separator-em-site-site-top',
        'separator-em-site-bottom',
        'separator-em-site-before-vlb',
        'upload.php',
        'media-new.php',
        em_site_admin_media_parent_menu_slug(),
        'separator-em-site-after-medias',
        'separator-em-site-after-catalog',
        'separator-em-site-after-templates',
        'separator-em-site-before-settings',
        'em-site-menu-wp-settings-label',
        'themes.php',
        'options-general.php',
        em_site_admin_dashicons_manager_page_slug(),
        'plugins.php',
    ];

    if ($catalog_legacy_enabled && function_exists('em_site_catalog_parent_menu_slug')) {
        $slugs[] = em_site_catalog_parent_menu_slug();
    }

    if ($catalog_legacy_enabled && function_exists('em_site_catalog_registered_hub_menu_slugs')) {
        $slugs = array_merge($slugs, em_site_catalog_registered_hub_menu_slugs());
    }

    if ($catalog_legacy_enabled && function_exists('em_site_catalog_sidebar_entry_page_slugs')) {
        $slugs = array_merge($slugs, em_site_catalog_sidebar_entry_page_slugs());
    }

    if ($catalog_legacy_enabled && function_exists('em_site_catalog_sommaire_menu_slug')) {
        $slugs[] = em_site_catalog_sommaire_menu_slug();
    }

    if (function_exists('em_site_admin_template_parent_page_slug')) {
        $slugs[] = em_site_admin_template_parent_page_slug();
    }

    if (function_exists('em_site_admin_template_entry_page_slugs')) {
        $slugs = array_merge($slugs, em_site_admin_template_entry_page_slugs());
    }

    if (function_exists('em_site_admin_template_choice_page_slug')) {
        $slugs[] = em_site_admin_template_choice_page_slug();
    }

    if (function_exists('em_site_admin_templates_page_slug')) {
        $slugs[] = em_site_admin_templates_page_slug();
    }

    if (function_exists('em_site_admin_template_create_page_slug')) {
        $slugs[] = em_site_admin_template_create_page_slug();
    }

    if (function_exists('em_site_admin_dashboard_page_slug')) {
        $slugs[] = em_site_admin_dashboard_page_slug();
    }

    if (function_exists('em_site_admin_site_rubrique_definitions')) {
        foreach (em_site_admin_site_rubrique_definitions() as $definition) {
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
function em_site_admin_catalog_reserved_menu_slugs(): array
{
    $catalog_legacy_enabled = function_exists('em_site_catalog_legacy_admin_enabled')
        ? em_site_catalog_legacy_admin_enabled()
        : false;

    $slugs = [];

    if (function_exists('em_site_admin_dashboard_page_slug')) {
        $slugs[] = em_site_admin_dashboard_page_slug();
    }

    $slugs[] = 'upload.php';
    $slugs[] = 'media-new.php';
    $slugs[] = em_site_admin_media_parent_menu_slug();

    if ($catalog_legacy_enabled && function_exists('em_site_catalog_parent_menu_slug')) {
        $slugs[] = em_site_catalog_parent_menu_slug();
    }

    if ($catalog_legacy_enabled && function_exists('em_site_catalog_registered_hub_menu_slugs')) {
        $slugs = array_merge($slugs, em_site_catalog_registered_hub_menu_slugs());
    }

    if ($catalog_legacy_enabled && function_exists('em_site_catalog_sidebar_entry_page_slugs')) {
        $slugs = array_merge($slugs, em_site_catalog_sidebar_entry_page_slugs());
    }

    if ($catalog_legacy_enabled && function_exists('em_site_catalog_sommaire_menu_slug')) {
        $slugs[] = em_site_catalog_sommaire_menu_slug();
    }

    return array_values(array_unique($slugs));
}
