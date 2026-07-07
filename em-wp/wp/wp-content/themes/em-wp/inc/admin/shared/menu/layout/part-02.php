<?php
function em_wp_admin_menu_layout_ensure_medias_entries(array $relocate): array
{
    $capability = em_wp_admin_menu_capability();
    $parent_slug = em_wp_admin_media_parent_menu_slug();

    if (!isset($relocate[$parent_slug]) || !is_array($relocate[$parent_slug])) {
        $relocate[$parent_slug] = [
            __('MEDIAS', 'em-wp'),
            $capability,
            $parent_slug,
            __('MEDIAS', 'em-wp'),
            'menu-top em-wp-menu-accordion-parent em-wp-menu-accordion-medias-parent',
            $parent_slug,
            'dashicons-admin-media',
        ];
    } else {
        $relocate[$parent_slug][1] = $capability;
        $relocate[$parent_slug][0] = __('MEDIAS', 'em-wp');
        $relocate[$parent_slug][2] = $parent_slug;
        $relocate[$parent_slug][3] = __('MEDIAS', 'em-wp');
        $relocate[$parent_slug][5] = $parent_slug;

        if (function_exists('em_wp_admin_menu_item_append_class')) {
            $relocate[$parent_slug] = em_wp_admin_menu_item_append_class(
                $relocate[$parent_slug],
                'em-wp-menu-accordion-parent em-wp-menu-accordion-medias-parent'
            );
        }
    }

    $relocate['upload.php'] = [
        __('Librairie', 'em-wp'),
        $capability,
        'upload.php',
        __('Librairie', 'em-wp'),
        'menu-top em-wp-menu-accordion-child em-wp-menu-accordion-medias-child',
        'upload',
        'dashicons-admin-media',
    ];

    $relocate['media-new.php'] = [
        __('Ajouter', 'em-wp'),
        $capability,
        'media-new.php',
        __('Ajouter', 'em-wp'),
        'menu-top em-wp-menu-accordion-child em-wp-menu-accordion-medias-child',
        'media-new',
        'dashicons-plus-alt',
    ];

    return $relocate;
}

/**
 * Supprime tout sous-menu WordPress sur un parent accordÃ©on (Ã©vite wp-has-submenu + wp-menu-arrow).
 */
function em_wp_admin_menu_layout_clear_parent_submenu(string $parent_slug): void
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
 * Reconstruit le bloc menu CATALOGUES (mÃªme structure que MEDIAS).
 *
 * @param array<string, array<int, string>> $relocate
 * @return array<string, array<int, string>>
 */
function em_wp_admin_menu_layout_ensure_catalog_entries(array $relocate): array
{
    if (!function_exists('em_wp_catalog_parent_menu_slug')) {
        return $relocate;
    }

    $capability = em_wp_admin_menu_capability();
    $parent_slug = em_wp_catalog_parent_menu_slug();

    $relocate[$parent_slug] = [
        __('CATALOGUES', 'em-wp'),
        $capability,
        $parent_slug,
        __('Catalogues', 'em-wp'),
        'menu-top em-wp-menu-accordion-parent em-wp-menu-accordion-catalog-parent',
        $parent_slug,
        'dashicons-index-card',
    ];

    if (!function_exists('em_wp_admin_catalog_menu_modules') || !function_exists('em_wp_catalog_menu_definitions')) {
        return $relocate;
    }

    foreach (em_wp_admin_catalog_menu_modules() as $module_slug) {
        $definition = em_wp_catalog_menu_definitions()[$module_slug] ?? null;

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
            'menu-top em-wp-menu-accordion-child em-wp-menu-accordion-catalog-child',
            $page_slug,
            (string) ($definition['icon'] ?? 'dashicons-admin-generic'),
        ];

        if (function_exists('em_wp_catalog_sidebar_entry_definitions')) {
            foreach (em_wp_catalog_sidebar_entry_definitions() as $entry_slug => $entry) {
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
                    'menu-top em-wp-menu-accordion-child em-wp-menu-accordion-catalog-child em-wp-menu-accordion-catalog-entry-child em-wp-menu-catalog-' . $entry_module . '-entry',
                    $entry_slug,
                    'dashicons-marker',
                ];
            }
        }
    }

    return $relocate;
}

/**
 * Reconstruit le bloc menu TEMPLATES (mÃªme structure que MEDIAS).
 *
 * @param array<string, array<int, string>> $relocate
 * @return array<string, array<int, string>>
 */
function em_wp_admin_menu_layout_ensure_template_entries(array $relocate): array
{
    if (!function_exists('em_wp_admin_template_parent_page_slug')) {
        return $relocate;
    }

    $capability = em_wp_admin_menu_capability();
    $parent_slug = em_wp_admin_template_parent_page_slug();
    $parent_label = __('TEMPLATES', 'em-wp');
    $editing_template_slug = function_exists('em_wp_get_explicit_editing_template_slug')
        ? em_wp_get_explicit_editing_template_slug()
        : '';

    $relocate[$parent_slug] = [
        $parent_label,
        $capability,
        $parent_slug,
        __('Templates', 'em-wp'),
        'menu-top em-wp-menu-accordion-parent em-wp-menu-accordion-templates-parent',
        $parent_slug,
        'dashicons-layout',
    ];

    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_admin_template_entry_page_slug')) {
        return $relocate;
    }

    foreach (em_wp_template_registry() as $slug => $definition) {
        $page_slug = em_wp_admin_template_entry_page_slug((string) $slug);
        $menu_label = mb_strtoupper((string) ($definition['label'] ?? $slug));
        $child_classes = 'menu-top em-wp-menu-accordion-child em-wp-menu-accordion-templates-child';

        if (function_exists('em_wp_get_active_template_slug') && (string) $slug === em_wp_get_active_template_slug()) {
            $child_classes .= ' em-wp-menu-template-live';
        }

        if ($editing_template_slug !== '' && (string) $slug === $editing_template_slug) {
            $child_classes .= ' em-wp-menu-template-editing';
        }

        $relocate[$page_slug] = [
            $menu_label,
            $capability,
            $page_slug,
            $menu_label,
            $child_classes,
            $page_slug,
            'dashicons-admin-appearance',
        ];
    }

    return $relocate;
}

/**
 * LibellÃ©s menu latÃ©ral Rubriques template (singulier, neutre â€” pas les catalogues).
 *
 * @param array<string, array<int, string>> $relocate
 * @return array<string, array<int, string>>
 */
function em_wp_admin_menu_layout_ensure_rubrique_entries(array $relocate): array
{
    if (!function_exists('em_wp_admin_site_rubrique_definitions') || !function_exists('em_wp_admin_rubrique_skeleton_label')) {
        return $relocate;
    }

    foreach (em_wp_admin_site_rubrique_definitions() as $module_slug => $definition) {
        if (!is_array($definition)) {
            continue;
        }

        $page_slug = sanitize_key((string) ($definition['page_slug'] ?? ''));

        if ($page_slug === '' || !isset($relocate[$page_slug]) || !is_array($relocate[$page_slug])) {
            continue;
        }

        $label = em_wp_admin_rubrique_skeleton_label((string) $module_slug);
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

