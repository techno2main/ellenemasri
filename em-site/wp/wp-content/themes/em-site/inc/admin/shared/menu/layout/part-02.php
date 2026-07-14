<?php
function em_site_admin_menu_layout_ensure_medias_entries(array $relocate): array
{
    $capability = em_site_admin_menu_capability();
    $parent_slug = em_site_admin_media_parent_menu_slug();
    $medias_icon = function_exists('em_site_site_icon') ? em_site_site_icon('medias', 'dashicons-admin-media') : 'dashicons-admin-media';
    $library_icon = function_exists('em_site_site_icon') ? em_site_site_icon('library', $medias_icon) : $medias_icon;
    $media_add_icon = function_exists('em_site_site_icon') ? em_site_site_icon('media-add', 'dashicons-plus-alt') : 'dashicons-plus-alt';

    if (!isset($relocate[$parent_slug]) || !is_array($relocate[$parent_slug])) {
        $relocate[$parent_slug] = [
            __('MEDIAS', 'em-site'),
            $capability,
            $parent_slug,
            __('MEDIAS', 'em-site'),
            'menu-top em-site-menu-accordion-parent em-site-menu-accordion-medias-parent',
            $parent_slug,
            $medias_icon,
        ];
    } else {
        $relocate[$parent_slug][1] = $capability;
        $relocate[$parent_slug][0] = __('MEDIAS', 'em-site');
        $relocate[$parent_slug][2] = $parent_slug;
        $relocate[$parent_slug][3] = __('MEDIAS', 'em-site');
        $relocate[$parent_slug][5] = $parent_slug;

        if (function_exists('em_site_admin_menu_item_append_class')) {
            $relocate[$parent_slug] = em_site_admin_menu_item_append_class(
                $relocate[$parent_slug],
                'em-site-menu-accordion-parent em-site-menu-accordion-medias-parent'
            );
        }
    }

    $relocate['upload.php'] = [
        __('Librairie', 'em-site'),
        $capability,
        'upload.php',
        __('Librairie', 'em-site'),
        'menu-top em-site-menu-accordion-child em-site-menu-accordion-medias-child',
        'upload',
        $library_icon,
    ];

    $relocate['media-new.php'] = [
        __('Ajouter', 'em-site'),
        $capability,
        'media-new.php',
        __('Ajouter', 'em-site'),
        'menu-top em-site-menu-accordion-child em-site-menu-accordion-medias-child',
        'media-new',
        $media_add_icon,
    ];

    return $relocate;
}

/**
 * Supprime tout sous-menu WordPress sur un parent accordéon (évite wp-has-submenu + wp-menu-arrow).
 */
function em_site_admin_menu_layout_clear_parent_submenu(string $parent_slug): void
{
    global $submenu;

    if ($parent_slug === '') {
        return;
    }

    if (isset($submenu[$parent_slug]) && is_array($submenu[$parent_slug])) {
        foreach (array_keys($submenu[$parent_slug]) as $submenu_key) {
            $child_slug = (string) ($submenu[$parent_slug][$submenu_key][2] ?? '');

            if ($child_slug !== '') {
                remove_submenu_page($parent_slug, $child_slug);
            }

            unset($submenu[$parent_slug][$submenu_key]);
        }
    }

    unset($submenu[$parent_slug]);
    remove_submenu_page($parent_slug, $parent_slug);
}

/**
 * Reconstruit le bloc menu CATALOGUES (même structure que MEDIAS).
 *
 * @param array<string, array<int, string>> $relocate
 * @return array<string, array<int, string>>
 */
function em_site_admin_menu_layout_ensure_catalog_entries(array $relocate): array
{
    if (!function_exists('em_site_catalog_parent_menu_slug')) {
        return $relocate;
    }

    $capability = em_site_admin_menu_capability();
    $parent_slug = em_site_catalog_parent_menu_slug();
    $catalogues_icon = function_exists('em_site_site_icon')
        ? em_site_site_icon('catalogues', 'dashicons-index-card')
        : 'dashicons-index-card';
    $catalog_entry_icon = function_exists('em_site_site_icon')
        ? em_site_site_icon('generic', 'dashicons-marker')
        : 'dashicons-marker';

    $relocate[$parent_slug] = [
        __('CATALOGUES', 'em-site'),
        $capability,
        $parent_slug,
        __('Catalogues', 'em-site'),
        'menu-top em-site-menu-accordion-parent em-site-menu-accordion-catalog-parent',
        $parent_slug,
        $catalogues_icon,
    ];

    if (!function_exists('em_site_admin_catalog_menu_modules') || !function_exists('em_site_catalog_menu_definitions')) {
        return $relocate;
    }

    foreach (em_site_admin_catalog_menu_modules() as $module_slug) {
        $definition = em_site_catalog_menu_definitions()[$module_slug] ?? null;

        if (!is_array($definition)) {
            continue;
        }

        $page_slug = (string) ($definition['slug'] ?? '');

        if ($page_slug === '') {
            continue;
        }

        $relocate[$page_slug] = [
            (string) ($definition['menu_title'] ?? $module_slug),
            $capability,
            $page_slug,
            (string) ($definition['label'] ?? $module_slug),
            'menu-top em-site-menu-accordion-child em-site-menu-accordion-catalog-child',
            $page_slug,
            (string) ($definition['icon'] ?? 'dashicons-admin-generic'),
        ];

        if (function_exists('em_site_catalog_sidebar_entry_definitions')) {
            foreach (em_site_catalog_sidebar_entry_definitions() as $entry_slug => $entry) {
                if ((string) ($entry['module'] ?? '') !== $module_slug) {
                    continue;
                }

                $entry_module = sanitize_key((string) ($entry['module'] ?? ''));
                $entry_label = (string) ($entry['label'] ?? $entry_slug);

                $relocate[$entry_slug] = [
                    $entry_label,
                    $capability,
                    $entry_slug,
                    $entry_label,
                    'menu-top em-site-menu-accordion-child em-site-menu-accordion-catalog-child em-site-menu-accordion-catalog-entry-child em-site-menu-catalog-' . $entry_module . '-entry',
                    $entry_slug,
                    $catalog_entry_icon,
                ];
            }
        }
    }

    return $relocate;
}

/**
 * Reconstruit le bloc menu TEMPLATES (même structure que MEDIAS).
 *
 * @param array<string, array<int, string>> $relocate
 * @return array<string, array<int, string>>
 */
function em_site_admin_menu_layout_ensure_template_entries(array $relocate): array
{
    if (!function_exists('em_site_admin_template_parent_page_slug')) {
        return $relocate;
    }

    $capability = em_site_admin_menu_capability();
    $parent_slug = em_site_admin_template_parent_page_slug();
    $parent_label = __('TEMPLATE', 'em-site');
    $template_icon = function_exists('em_site_site_icon') ? em_site_site_icon('template', 'dashicons-layout') : 'dashicons-layout';
    $appearance_icon = function_exists('em_site_site_icon') ? em_site_site_icon('appearance', 'dashicons-admin-appearance') : 'dashicons-admin-appearance';
    $unique_mode = function_exists('em_site_template_unique_mode_enabled') && em_site_template_unique_mode_enabled();
    $editing_template_slug = function_exists('em_site_get_explicit_editing_template_slug')
        ? em_site_get_explicit_editing_template_slug()
        : '';

    $relocate[$parent_slug] = [
        $parent_label,
        $capability,
        $parent_slug,
        __('Template', 'em-site'),
        'menu-top em-site-menu-accordion-parent em-site-menu-accordion-templates-parent',
        $parent_slug,
        $template_icon,
    ];

    if ($unique_mode) {
        return $relocate;
    }

    if (!function_exists('em_site_template_registry') || !function_exists('em_site_admin_template_entry_page_slug')) {
        return $relocate;
    }

    foreach (em_site_template_registry() as $slug => $definition) {
        $page_slug = em_site_admin_template_entry_page_slug((string) $slug);
        $menu_label = mb_strtoupper((string) ($definition['label'] ?? $slug));
        $child_classes = 'menu-top em-site-menu-accordion-child em-site-menu-accordion-templates-child';

        if (function_exists('em_site_get_active_template_slug') && (string) $slug === em_site_get_active_template_slug()) {
            $child_classes .= ' em-site-menu-template-live';
        }

        if ($editing_template_slug !== '' && (string) $slug === $editing_template_slug) {
            $child_classes .= ' em-site-menu-template-editing';
        }

        $relocate[$page_slug] = [
            $menu_label,
            $capability,
            $page_slug,
            $menu_label,
            $child_classes,
            $page_slug,
            $appearance_icon,
        ];
    }

    return $relocate;
}

/**
 * Libellés menu latéral Rubriques template (singulier, neutre — pas les catalogues).
 *
 * @param array<string, array<int, string>> $relocate
 * @return array<string, array<int, string>>
 */
function em_site_admin_menu_layout_ensure_rubrique_entries(array $relocate): array
{
    if (!function_exists('em_site_admin_site_rubrique_definitions') || !function_exists('em_site_admin_rubrique_skeleton_label')) {
        return $relocate;
    }

    foreach (em_site_admin_site_rubrique_definitions() as $module_slug => $definition) {
        if (!is_array($definition)) {
            continue;
        }

        $page_slug = sanitize_key((string) ($definition['page_slug'] ?? ''));

        if ($page_slug === '' || !isset($relocate[$page_slug]) || !is_array($relocate[$page_slug])) {
            continue;
        }

        $label = em_site_admin_rubrique_skeleton_label((string) $module_slug);
        $relocate[$page_slug][0] = $label;

        if (isset($relocate[$page_slug][3])) {
            $relocate[$page_slug][3] = $label;
        }
    }

    return $relocate;
}

/**
 * @param array<string, array<int, string>> $relocate
 * @return array<string, array<int, string>>
 */

