<?php
function em_site_admin_menu_layout_ensure_settings_entries(array $relocate): array
{
    if (!isset($relocate['em-site-menu-wp-settings-label'])) {
        $relocate['em-site-menu-wp-settings-label'] = em_site_admin_menu_section_label_item(
            'em-site-menu-wp-settings-label',
            __('Paramètres', 'em-site'),
            'em-site-menu-wp-settings-label em-site-menu-accordion-parent em-site-menu-accordion-settings-parent'
        );
    } elseif (function_exists('em_site_admin_menu_item_append_class')) {
        $relocate['em-site-menu-wp-settings-label'] = em_site_admin_menu_item_append_class(
            $relocate['em-site-menu-wp-settings-label'],
            'em-site-menu-accordion-parent em-site-menu-accordion-settings-parent'
        );
    }

    foreach (em_site_admin_menu_native_settings_registry_slugs() as $slug) {
        if (!isset($relocate[$slug]) || !is_array($relocate[$slug])) {
            continue;
        }

        if (function_exists('em_site_admin_menu_item_append_class')) {
            $relocate[$slug] = em_site_admin_menu_item_append_class(
                $relocate[$slug],
                'em-site-menu-accordion-child em-site-menu-accordion-settings-child'
            );
        }
    }

    return $relocate;
}

/**
 * Retire du menu latéral les rubriques absentes du squelette / contexte courant.
 *
 * Les pages enregistrées via add_menu_page (Release, Contact…) restent accessibles par URL
 * mais ne doivent pas apparaître en orphelines entre TEMPLATES et PARAMÈTRES.
 */
function em_site_admin_menu_layout_purge_out_of_context_rubriques(): void
{
    if (!function_exists('em_site_admin_site_rubrique_all_definitions')
        || !function_exists('em_site_admin_rubrique_menu_child_slugs')) {
        return;
    }

    global $menu;

    $allowed = em_site_admin_rubrique_menu_child_slugs();
    $known_rubrique_slugs = [];

    foreach (em_site_admin_site_rubrique_all_definitions() as $definition) {
        if (!is_array($definition)) {
            continue;
        }

        $page_slug = sanitize_key((string) ($definition['page_slug'] ?? ''));

        if ($page_slug !== '') {
            $known_rubrique_slugs[] = $page_slug;
        }
    }

    if ($known_rubrique_slugs === []) {
        return;
    }

    if (function_exists('em_site_admin_should_show_rubrique_menus') && !em_site_admin_should_show_rubrique_menus()) {
        $purge_slugs = array_merge(
            $known_rubrique_slugs,
            ['separator-em-site-site-top', 'separator-em-site-bottom']
        );

        if (function_exists('em_site_admin_rubriques_page_slug')) {
            $purge_slugs[] = em_site_admin_rubriques_page_slug();
        }

        $purge_slugs = array_values(array_unique($purge_slugs));

        foreach ($menu as $position => $item) {
            if (!is_array($item)) {
                continue;
            }

            $slug = function_exists('em_site_admin_menu_item_slug')
                ? em_site_admin_menu_item_slug($item)
                : sanitize_key((string) ($item[2] ?? ''));

            if ($slug !== '' && in_array($slug, $purge_slugs, true)) {
                unset($menu[$position]);
            }
        }

        return;
    }

    foreach ($menu as $position => $item) {
        if (!is_array($item)) {
            continue;
        }

        $slug = function_exists('em_site_admin_menu_item_slug')
            ? em_site_admin_menu_item_slug($item)
            : sanitize_key((string) ($item[2] ?? ''));

        if ($slug === '' || !in_array($slug, $known_rubrique_slugs, true)) {
            continue;
        }

        if (!in_array($slug, $allowed, true)) {
            unset($menu[$position]);
        }
    }
}

/**
 * Supprime les doublons de hubs catalogues sans classe accordéon (legacy add_menu_page).
 */
function em_site_admin_menu_layout_purge_duplicate_catalog_hubs(): void
{
    if (!function_exists('em_site_catalog_registered_hub_menu_slugs')) {
        return;
    }

    global $menu;

    $with_class = [];
    $without_class = [];

    foreach ($menu as $position => $item) {
        if (!is_array($item)) {
            continue;
        }

        $slug = em_site_admin_menu_registry_slug_for_item($item);

        if (!in_array($slug, em_site_catalog_registered_hub_menu_slugs(), true)) {
            continue;
        }

        $classes = (string) ($item[4] ?? '');

        if (str_contains($classes, 'em-site-menu-accordion-catalog-child')) {
            $with_class[$slug][] = $position;
            continue;
        }

        $without_class[$slug][] = $position;
    }

    foreach ($without_class as $slug => $positions) {
        if (empty($with_class[$slug])) {
            continue;
        }

        foreach ($positions as $position) {
            unset($menu[$position]);
        }
    }
}

/**
 * Applique le registre de positions (dernier mot sur le menu latéral).
 */

