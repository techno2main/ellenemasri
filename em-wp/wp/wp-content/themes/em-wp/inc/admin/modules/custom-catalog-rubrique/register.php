<?php
/**
 * Menu et assets admin — rubriques catalogues personnalisés.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_custom_catalog_rubrique_register_admin(): void
{
    if (!function_exists('em_wp_custom_catalog_modules')) {
        return;
    }

    foreach (em_wp_custom_catalog_modules() as $module_slug => $module) {
        $module_slug = sanitize_key((string) $module_slug);
        $page_slug = em_wp_custom_catalog_rubrique_page_slug($module_slug);

        if ($module_slug === '' || $page_slug === '') {
            continue;
        }

        $menu_title = function_exists('em_wp_admin_rubrique_skeleton_label')
            ? em_wp_admin_rubrique_skeleton_label($module_slug)
            : mb_strtoupper((string) ($module['label'] ?? $module_slug));
        $icon = sanitize_key((string) ($module['icon'] ?? 'dashicons-admin-generic')) ?: 'dashicons-admin-generic';

        add_menu_page(
            $menu_title,
            $menu_title,
            'manage_options',
            $page_slug,
            static function () use ($module_slug): void {
                em_wp_custom_catalog_rubrique_render_admin_page($module_slug);
            },
            $icon,
            em_wp_admin_menu_position_for_site_module($module_slug)
        );
    }
}
add_action('admin_menu', 'em_wp_custom_catalog_rubrique_register_admin');

function em_wp_custom_catalog_rubrique_remove_duplicate_submenus(): void
{
    if (!function_exists('em_wp_custom_catalog_modules')) {
        return;
    }

    foreach (array_keys(em_wp_custom_catalog_modules()) as $module_slug) {
        $page_slug = em_wp_custom_catalog_rubrique_page_slug((string) $module_slug);

        if ($page_slug !== '') {
            remove_submenu_page($page_slug, $page_slug);
        }
    }
}
add_action('admin_menu', 'em_wp_custom_catalog_rubrique_remove_duplicate_submenus', 999);

function em_wp_custom_catalog_rubrique_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
    $module_slug = em_wp_custom_catalog_rubrique_module_from_page_slug($page_slug);

    if ($module_slug === '') {
        return;
    }

    em_wp_admin_enqueue_shared_assets();
    em_wp_admin_enqueue_catalog_slug_switch_assets();

    wp_enqueue_style(
        'em-wp-header-admin',
        get_template_directory_uri() . '/assets/admin/css/modules/header/header.css',
        ['em-wp-admin-module-common', 'em-wp-admin-hub-cards'],
        em_wp_admin_asset_version('assets/admin/css/modules/header/header.css')
    );

    wp_enqueue_script(
        'em-wp-custom-catalog-rubrique-admin',
        get_template_directory_uri() . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion', 'em-wp-admin-module-style-preview'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_custom_catalog_rubrique_admin_enqueue');
