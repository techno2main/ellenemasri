<?php
/**
 * Registre unique des positions du menu admin em-wp.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Position du libellé « Thème actif » (au-dessus de DASHBOARD).
 */
function em_wp_admin_menu_active_template_label_position(): int
{
    return 1;
}

/**
 * Début du bloc navigation principale (MEDIAS, CATALOGUES, TEMPLATES).
 */
function em_wp_admin_menu_main_nav_base(): int
{
    return 10;
}

/**
 * Début du bloc Rubriques template.
 */
function em_wp_admin_menu_rubrique_block_base(): int
{
    return 55;
}

/**
 * Début du bloc Paramètres (filet + accordéon + menus WP natifs).
 */
function em_wp_admin_menu_settings_block_base(): int
{
    return 80;
}

/**
 * Slugs des menus WordPress natifs sous PARAMÈTRES.
 *
 * @return string[]
 */
function em_wp_admin_menu_native_settings_registry_slugs(): array
{
    return em_wp_admin_native_settings_menu_order();
}

/**
 * Entrée menu « THÈME ACTIF : … ».
 *
 * @return array<int, string>
 */
function em_wp_admin_menu_active_template_label_item(string $theme_name): array
{
    $label = sprintf(
        'THÈME ACTIF : %s',
        mb_strtoupper($theme_name, 'UTF-8')
    );

    return em_wp_admin_menu_section_label_item(
        'em-wp-menu-active-template-label',
        $label,
        'em-wp-menu-active-template-label'
    );
}

/**
 * Slug registre pour une entrée menu (évite collision upload.php / em-wp-medias).
 */
function em_wp_admin_menu_registry_slug_for_item(array $item): string
{
    $hook = (string) ($item[5] ?? '');

    if ($hook === em_wp_admin_media_parent_menu_slug()) {
        return em_wp_admin_media_parent_menu_slug();
    }

    if (function_exists('em_wp_catalog_parent_menu_slug') && $hook === em_wp_catalog_parent_menu_slug()) {
        return em_wp_catalog_parent_menu_slug();
    }

    if (function_exists('em_wp_admin_template_parent_page_slug') && $hook === em_wp_admin_template_parent_page_slug()) {
        return em_wp_admin_template_parent_page_slug();
    }

    if (function_exists('em_wp_admin_menu_item_slug')) {
        return em_wp_admin_menu_item_slug($item);
    }

    return sanitize_key((string) ($item[2] ?? ''));
}

/**
 * Registre slug => position (source de vérité).
 *
 * @return array<string, int>
 */
function em_wp_admin_menu_position_registry(): array
{
    static $registry = null;

    if ($registry !== null) {
        return $registry;
    }

    $registry = [
        'em-wp-menu-active-template-label' => em_wp_admin_menu_active_template_label_position(),
    ];

    $p = em_wp_admin_menu_main_nav_base();

    $registry[em_wp_admin_media_parent_menu_slug()] = $p++;
    $registry['upload.php'] = $p++;
    $registry['media-new.php'] = $p++;
    $registry['separator-em-wp-after-medias'] = $p++;

    if (function_exists('em_wp_catalog_parent_menu_slug')) {
        $registry[em_wp_catalog_parent_menu_slug()] = $p++;

        if (function_exists('em_wp_catalog_menu_definitions') && function_exists('em_wp_admin_catalog_menu_modules')) {
            foreach (em_wp_admin_catalog_menu_modules() as $module_slug) {
                $definition = em_wp_catalog_menu_definitions()[$module_slug] ?? null;
                $hub_slug = is_array($definition) ? (string) ($definition['slug'] ?? '') : '';

                if ($hub_slug !== '') {
                    $registry[$hub_slug] = $p++;
                }
            }
        }

        $registry['separator-em-wp-after-catalog'] = $p++;
    }

    if (function_exists('em_wp_admin_template_parent_page_slug')) {
        $registry[em_wp_admin_template_parent_page_slug()] = $p++;

        if (function_exists('em_wp_template_registry') && function_exists('em_wp_admin_template_entry_page_slug')) {
            foreach (array_keys(em_wp_template_registry()) as $template_slug) {
                $registry[em_wp_admin_template_entry_page_slug($template_slug)] = $p++;
            }
        }

        $registry['separator-em-wp-after-templates'] = $p++;
    }

    $rub_base = em_wp_admin_menu_rubrique_block_base();
    $registry['separator-em-wp-site-top'] = $rub_base - 2;

    if (function_exists('em_wp_admin_rubriques_page_slug')) {
        $registry[em_wp_admin_rubriques_page_slug()] = $rub_base - 1;
    }

    if (function_exists('em_wp_admin_site_rubrique_modules') && function_exists('em_wp_admin_site_rubrique_definitions')) {
        $definitions = em_wp_admin_site_rubrique_definitions();
        $idx = 0;

        foreach (em_wp_admin_site_rubrique_modules() as $module_slug) {
            $definition = $definitions[$module_slug] ?? null;
            $page_slug = is_array($definition) ? (string) ($definition['page_slug'] ?? '') : '';

            if ($page_slug !== '') {
                $registry[$page_slug] = $rub_base + $idx;
            }

            $idx++;
        }

        $registry['separator-em-wp-bottom'] = $rub_base + $idx;
    }

    $settings = em_wp_admin_menu_settings_block_base();
    $registry['separator-em-wp-before-settings'] = $settings;
    $registry['em-wp-menu-wp-settings-label'] = $settings + 1;

    $settings_child = $settings + 2;

    foreach (em_wp_admin_menu_native_settings_registry_slugs() as $slug) {
        $registry[$slug] = $settings_child++;
    }

    return $registry;
}

/**
 * Position menu pour un slug enregistré.
 */
function em_wp_admin_menu_position_for_slug(string $slug): float
{
    $registry = em_wp_admin_menu_position_registry();

    if (array_key_exists($slug, $registry)) {
        return (float) $registry[$slug];
    }

    return (float) em_wp_admin_menu_rubrique_block_base();
}

/**
 * @param array<string, array<int, string>> $relocate
 * @return array<string, array<int, string>>
 */
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
 * Supprime tout sous-menu WordPress sur un parent accordéon (évite wp-has-submenu + wp-menu-arrow).
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
 * Reconstruit le bloc menu CATALOGUES (même structure que MEDIAS).
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
    }

    return $relocate;
}

/**
 * Reconstruit le bloc menu TEMPLATES (même structure que MEDIAS).
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

    $relocate[$parent_slug] = [
        __('TEMPLATES', 'em-wp'),
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

        $relocate[$page_slug] = [
            $menu_label,
            $capability,
            $page_slug,
            $menu_label,
            'menu-top em-wp-menu-accordion-child em-wp-menu-accordion-templates-child',
            $page_slug,
            'dashicons-admin-appearance',
        ];
    }

    return $relocate;
}

/**
 * @param array<string, array<int, string>> $relocate
 * @return array<string, array<int, string>>
 */
function em_wp_admin_menu_layout_ensure_settings_entries(array $relocate): array
{
    if (!isset($relocate['em-wp-menu-wp-settings-label'])) {
        $relocate['em-wp-menu-wp-settings-label'] = em_wp_admin_menu_section_label_item(
            'em-wp-menu-wp-settings-label',
            __('Paramètres', 'em-wp'),
            'em-wp-menu-wp-settings-label em-wp-menu-accordion-parent em-wp-menu-accordion-settings-parent'
        );
    } elseif (function_exists('em_wp_admin_menu_item_append_class')) {
        $relocate['em-wp-menu-wp-settings-label'] = em_wp_admin_menu_item_append_class(
            $relocate['em-wp-menu-wp-settings-label'],
            'em-wp-menu-accordion-parent em-wp-menu-accordion-settings-parent'
        );
    }

    foreach (em_wp_admin_menu_native_settings_registry_slugs() as $slug) {
        if (!isset($relocate[$slug]) || !is_array($relocate[$slug])) {
            continue;
        }

        if (function_exists('em_wp_admin_menu_item_append_class')) {
            $relocate[$slug] = em_wp_admin_menu_item_append_class(
                $relocate[$slug],
                'em-wp-menu-accordion-child em-wp-menu-accordion-settings-child'
            );
        }
    }

    return $relocate;
}

/**
 * Applique le registre de positions (dernier mot sur le menu latéral).
 */
function em_wp_admin_apply_menu_layout(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $menu, $submenu;

    em_wp_admin_purge_media_menu_entries();
    em_wp_admin_remove_native_media_menu();

    $registry = em_wp_admin_menu_position_registry();
    $relocate = [];

    foreach ($menu as $position => $item) {
        if (!is_array($item)) {
            continue;
        }

        $slug = em_wp_admin_menu_registry_slug_for_item($item);

        if ($slug === '' || !array_key_exists($slug, $registry)) {
            continue;
        }

        $relocate[$slug] = $item;
        unset($menu[$position]);
    }

    foreach (em_wp_admin_menu_native_settings_registry_slugs() as $slug) {
        if (isset($relocate[$slug])) {
            continue;
        }

        $item = em_wp_admin_extract_menu_item_by_slug($slug);

        if ($item !== null) {
            $relocate[$slug] = $item;
        }
    }

    $relocate = em_wp_admin_menu_layout_ensure_medias_entries($relocate);
    $relocate = em_wp_admin_menu_layout_ensure_catalog_entries($relocate);
    $relocate = em_wp_admin_menu_layout_ensure_template_entries($relocate);
    $relocate = em_wp_admin_menu_layout_ensure_settings_entries($relocate);

    if (
        function_exists('em_wp_admin_sidebar_active_theme_label')
        && !isset($relocate['em-wp-menu-active-template-label'])
    ) {
        $theme_name = em_wp_admin_sidebar_active_theme_label();

        if ($theme_name !== '') {
            $relocate['em-wp-menu-active-template-label'] = em_wp_admin_menu_active_template_label_item($theme_name);
        }
    }

    foreach (array_keys($registry) as $slug) {
        if (isset($relocate[$slug]) || !str_starts_with($slug, 'separator-em-wp-')) {
            continue;
        }

        $relocate[$slug] = em_wp_admin_menu_separator_item($slug, $slug);
    }

    $sorted = $registry;
    asort($sorted, SORT_NUMERIC);

    foreach ($sorted as $slug => $position) {
        if (!isset($relocate[$slug])) {
            continue;
        }

        $menu[$position] = $relocate[$slug];
    }

    $parent_slug = em_wp_admin_media_parent_menu_slug();
    unset($submenu[$parent_slug]);
    remove_submenu_page($parent_slug, $parent_slug);

    if (function_exists('em_wp_catalog_parent_menu_slug')) {
        $catalog_slug = em_wp_catalog_parent_menu_slug();
        em_wp_admin_menu_layout_clear_parent_submenu($catalog_slug);
    }

    if (function_exists('em_wp_admin_template_parent_page_slug')) {
        $template_slug = em_wp_admin_template_parent_page_slug();
        em_wp_admin_menu_layout_clear_parent_submenu($template_slug);
    }

    if (function_exists('em_wp_admin_apply_menu_accordion_classes')) {
        em_wp_admin_apply_menu_accordion_classes();
    }

    ksort($menu, SORT_NUMERIC);
}
add_action('admin_menu', 'em_wp_admin_apply_menu_layout', 1000010);
