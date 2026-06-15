<?php
/**
 * Page admin rubrique template pour un catalogue personnalisé.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu page rubrique CONTACTS (et futurs catalogues custom).
 */
function em_wp_custom_catalog_rubrique_render_admin_page(string $module_slug): void
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '' || !function_exists('em_wp_custom_catalog_is_module') || !em_wp_custom_catalog_is_module($module_slug)) {
        return;
    }

    if (!function_exists('em_wp_admin_render_catalog_rubrique_page')) {
        return;
    }

    $pointer_key = function_exists('em_wp_admin_rubrique_catalog_pointer_key')
        ? em_wp_admin_rubrique_catalog_pointer_key($module_slug)
        : $module_slug . '_slug';

    em_wp_admin_render_catalog_rubrique_page([
        'module_slug'       => $module_slug,
        'page_slug'         => em_wp_custom_catalog_rubrique_page_slug($module_slug),
        'save_nonce_action' => 'em_wp_' . str_replace('-', '_', $module_slug) . '_save',
        'options'           => em_wp_custom_catalog_rubrique_get_options($module_slug),
        'choices'           => em_wp_custom_catalog_rubrique_catalog_choices($module_slug),
        'pointer_key'       => $pointer_key,
        'field'             => em_wp_custom_catalog_rubrique_form_option_key($module_slug),
        'form_id'           => 'em-wp-custom-catalog-rubrique-form-' . $module_slug,
    ]);
}
