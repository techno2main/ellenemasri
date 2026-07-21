<?php
/**
 * Registre unique des positions du menu admin em-site.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Position du libellé « Thème actif » (au-dessus de DASHBOARD).
 */
function em_site_admin_menu_active_template_label_position(): int
{
    return 1;
}

/**
 * Début du bloc navigation principale (MEDIAS, CATALOGUES, TEMPLATES).
 */
function em_site_admin_menu_main_nav_base(): int
{
    return 10;
}

/**
 * Début du bloc Rubriques template.
 */
function em_site_admin_menu_rubrique_block_base(): int
{
    return 55;
}

/**
 * Début du bloc Paramètres (filet + accordéon + menus WP natifs).
 */
function em_site_admin_menu_settings_block_base(): int
{
    return 80;
}

/**
 * Slug parent du menu VLB (Visual Links Builder).
 */
function em_site_admin_menu_vlb_parent_slug(): string
{
    return 'mayami_visual_links_builder';
}

/**
 * Position du menu VLB : après RUBRIQUES et avant Paramètres.
 */
function em_site_admin_menu_vlb_position(): int
{
    return em_site_admin_menu_settings_block_base() - 2;
}

/**
 * Position du séparateur visuel juste avant VLB.
 */
function em_site_admin_menu_vlb_separator_position(): int
{
    return em_site_admin_menu_vlb_position() - 1;
}

/**
 * Slugs des menus WordPress natifs sous PARAMÈTRES.
 *
 * @return string[]
 */
function em_site_admin_menu_native_settings_registry_slugs(): array
{
    return em_site_admin_native_settings_menu_order();
}

/**
 * Entrée menu « THÈME ACTIF : … ».
 *
 * @return array<int, string>
 */
function em_site_admin_menu_active_template_label_item(string $theme_name): array
{
    $label = sprintf(
        'THÈME ACTIF : %s',
        mb_strtoupper($theme_name, 'UTF-8')
    );

    return em_site_admin_menu_section_label_item(
        'em-site-menu-active-template-label',
        $label,
        'em-site-menu-active-template-label'
    );
}

/**
 * Slug registre pour une entrée menu (évite collision upload.php / em-site-medias).
 */
function em_site_admin_menu_registry_slug_for_item(array $item): string
{
    $hook = (string) ($item[5] ?? '');

    if ($hook === em_site_admin_media_parent_menu_slug()) {
        return em_site_admin_media_parent_menu_slug();
    }

    if (function_exists('em_site_catalog_parent_menu_slug') && $hook === em_site_catalog_parent_menu_slug()) {
        return em_site_catalog_parent_menu_slug();
    }

    if (function_exists('em_site_admin_template_parent_page_slug') && $hook === em_site_admin_template_parent_page_slug()) {
        return em_site_admin_template_parent_page_slug();
    }

    if (function_exists('em_site_admin_menu_item_slug')) {
        return em_site_admin_menu_item_slug($item);
    }

    return sanitize_key((string) ($item[2] ?? ''));
}

/**
 * Registre slug => position (source de vérité).
 *
 * @return array<string, int>
 */
function em_site_admin_menu_position_registry(): array
{
    static $registry = null;

    if ($registry !== null) {
        return $registry;
    }

    $registry = [
        'em-site-menu-active-template-label' => em_site_admin_menu_active_template_label_position(),
    ];

    $p = em_site_admin_menu_main_nav_base();

    // Ordre principal voulu : TEMPLATE puis RUBRIQUES puis MEDIAS puis VLB.
    if (function_exists('em_site_admin_template_parent_page_slug')) {
        $registry[em_site_admin_template_parent_page_slug()] = $p++;

        if (function_exists('em_site_template_registry') && function_exists('em_site_admin_template_entry_page_slug')) {
            foreach (array_keys(em_site_template_registry()) as $template_slug) {
                $registry[em_site_admin_template_entry_page_slug($template_slug)] = $p++;
            }
        }

        $registry['separator-em-site-after-templates'] = $p++;
    }

    $catalog_legacy_enabled = function_exists('em_site_catalog_legacy_admin_enabled')
        ? em_site_catalog_legacy_admin_enabled()
        : false;

    if ($catalog_legacy_enabled && function_exists('em_site_catalog_parent_menu_slug')) {
        $registry[em_site_catalog_parent_menu_slug()] = $p++;

        if (function_exists('em_site_catalog_menu_definitions') && function_exists('em_site_admin_catalog_menu_modules')) {
            foreach (em_site_admin_catalog_menu_modules() as $module_slug) {
                $definition = em_site_catalog_menu_definitions()[$module_slug] ?? null;
                $hub_slug = is_array($definition) ? (string) ($definition['slug'] ?? '') : '';

                if ($hub_slug !== '') {
                    $registry[$hub_slug] = $p++;
                }

                if (function_exists('em_site_catalog_sidebar_entry_definitions')) {
                    foreach (em_site_catalog_sidebar_entry_definitions() as $entry) {
                        if ((string) ($entry['module'] ?? '') !== $module_slug) {
                            continue;
                        }

                        $entry_slug = (string) ($entry['page_slug'] ?? '');

                        if ($entry_slug !== '') {
                            $registry[$entry_slug] = $p++;
                        }
                    }
                }
            }
        }

    }

    $rub_base = em_site_admin_menu_rubrique_block_base();
    $rubriques_item_count = 0;
    $registry['separator-em-site-site-top'] = $rub_base - 2;

    if (function_exists('em_site_admin_rubriques_page_slug')) {
        $registry[em_site_admin_rubriques_page_slug()] = $rub_base - 1;
    }

    if (function_exists('em_site_admin_site_rubrique_modules') && function_exists('em_site_admin_site_rubrique_definitions')) {
        $definitions = em_site_admin_site_rubrique_definitions();
        $idx = 0;

        foreach (em_site_admin_site_rubrique_modules() as $module_slug) {
            $definition = $definitions[$module_slug] ?? null;
            $page_slug = is_array($definition) ? (string) ($definition['page_slug'] ?? '') : '';

            if ($page_slug !== '') {
                $registry[$page_slug] = $rub_base + $idx;
            }

            $idx++;
        }

        $rubriques_item_count = $idx;

        $registry['separator-em-site-bottom'] = $rub_base + $idx;
    }

    $media_base = em_site_admin_menu_vlb_separator_position() - 4;
    $media_after_rubriques = $rub_base + $rubriques_item_count + 2;

    if ($media_base < $media_after_rubriques) {
        $media_base = $media_after_rubriques;
    }

    $registry[em_site_admin_media_parent_menu_slug()] = $media_base;
    $registry['upload.php'] = $media_base + 1;
    $registry['media-new.php'] = $media_base + 2;
    $registry['separator-em-site-after-medias'] = $media_base + 3;

    $registry['separator-em-site-before-vlb'] = em_site_admin_menu_vlb_separator_position();
    $registry[em_site_admin_menu_vlb_parent_slug()] = em_site_admin_menu_vlb_position();

    $settings = em_site_admin_menu_settings_block_base();
    $registry['separator-em-site-before-settings'] = $settings;
    $registry['em-site-menu-wp-settings-label'] = $settings + 1;

    $settings_child = $settings + 2;

    foreach (em_site_admin_menu_native_settings_registry_slugs() as $slug) {
        $registry[$slug] = $settings_child++;
    }

    return $registry;
}

/**
 * Position menu pour un slug enregistré.
 */
function em_site_admin_menu_position_for_slug(string $slug): float
{
    $registry = em_site_admin_menu_position_registry();

    if (array_key_exists($slug, $registry)) {
        return (float) $registry[$slug];
    }

    return (float) em_site_admin_menu_rubrique_block_base();
}

/**
 * @param array<string, array<int, string>> $relocate
 * @return array<string, array<int, string>>
 */

