<?php
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
    // Demande UI: masquer l'entree parent Â« CATALOGUES Â» dans le menu gauche,
    // sans impacter les autres blocs.
    if (function_exists('em_wp_catalog_parent_menu_slug')) {
        unset($relocate[em_wp_catalog_parent_menu_slug()]);
        // Retire aussi uniquement le filet du bloc catalogues.
        unset($relocate['separator-em-wp-after-catalog']);
    }
    $relocate = em_wp_admin_menu_layout_ensure_template_entries($relocate);
    $relocate = em_wp_admin_menu_layout_ensure_rubrique_entries($relocate);
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

    em_wp_admin_menu_layout_purge_duplicate_catalog_hubs();
    em_wp_admin_menu_layout_purge_out_of_context_rubriques();

    if (function_exists('em_wp_admin_apply_menu_accordion_classes')) {
        em_wp_admin_apply_menu_accordion_classes();
    }

    ksort($menu, SORT_NUMERIC);
}
add_action('admin_menu', 'em_wp_admin_apply_menu_layout', 1000010);

