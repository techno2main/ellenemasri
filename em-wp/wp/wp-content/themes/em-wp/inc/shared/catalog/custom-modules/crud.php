<?php
/**
 * CRUD modules catalogue personnalisés.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<string, array<string, mixed>> $modules
 */
function em_wp_custom_catalog_persist_modules(array $modules): bool
{
    return (bool) update_option(em_wp_custom_catalog_modules_option_name(), $modules, false);
}

/**
 * @return string|WP_Error
 */
function em_wp_custom_catalog_update_module(string $module_slug, string $label, string $menu_position_after = '__end__')
{
    $module_slug = sanitize_key($module_slug);
    $label = sanitize_text_field($label);

    if (!em_wp_custom_catalog_is_module($module_slug)) {
        return new WP_Error('em_wp_custom_catalog_module_not_found', __('Catalogue introuvable.', 'em-wp'));
    }

    if ($label === '') {
        return new WP_Error('em_wp_custom_catalog_empty_label', __('Le nom du catalogue est obligatoire.', 'em-wp'));
    }

    $menu_position_after = sanitize_key($menu_position_after);
    $allowed_anchors = array_keys(em_wp_catalog_menu_position_options($module_slug));

    if (!in_array($menu_position_after, $allowed_anchors, true)) {
        $menu_position_after = '__end__';
    }

    $modules = em_wp_custom_catalog_modules();
    $current = $modules[$module_slug];
    $guessed_icon = em_wp_catalog_guess_module_icon_from_label($label, $module_slug);
    $icon = (string) ($current['icon'] ?? 'dashicons-admin-generic');

    if ($guessed_icon !== 'dashicons-admin-generic') {
        $icon = $guessed_icon;
    }

    $modules[$module_slug] = [
        'label'                => $label,
        'menu_position_after'  => $menu_position_after,
        'hub_menu_slug'        => (string) ($current['hub_menu_slug'] ?? em_wp_custom_catalog_hub_menu_slug($module_slug)),
        'icon'                 => $icon,
        'description_item'     => $label,
        'description_rubrique' => (string) ($current['description_rubrique'] ?? ''),
    ];

    if (!em_wp_custom_catalog_persist_modules($modules)) {
        return new WP_Error('em_wp_custom_catalog_persist_failed', __('Impossible d\'enregistrer le catalogue.', 'em-wp'));
    }

    return $module_slug;
}

/**
 * @return string|WP_Error
 */
function em_wp_custom_catalog_create_module(string $label, string $menu_position_after = '__end__')
{
    $label = sanitize_text_field($label);

    if ($label === '') {
        return new WP_Error('em_wp_custom_catalog_empty_label', __('Le nom du catalogue est obligatoire.', 'em-wp'));
    }

    $menu_position_after = sanitize_key($menu_position_after);
    $allowed_anchors = array_keys(em_wp_catalog_menu_position_options());

    if (!in_array($menu_position_after, $allowed_anchors, true)) {
        $menu_position_after = '__end__';
    }

    $modules = em_wp_custom_catalog_modules();
    $slug = em_wp_custom_catalog_unique_module_slug(em_wp_custom_catalog_module_slug_from_label($label));

    $modules[$slug] = [
        'label'                => $label,
        'menu_position_after'  => $menu_position_after,
        'hub_menu_slug'        => em_wp_custom_catalog_hub_menu_slug($slug),
        'icon'                 => em_wp_catalog_guess_module_icon_from_label($label, $slug),
        'description_item'     => $label,
        'description_rubrique' => '',
    ];

    if (!em_wp_custom_catalog_persist_modules($modules)) {
        return new WP_Error('em_wp_custom_catalog_persist_failed', __('Impossible d\'enregistrer le catalogue.', 'em-wp'));
    }

    update_option(em_wp_custom_catalog_entries_option_name($slug), [], false);

    return $slug;
}
