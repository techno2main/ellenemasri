<?php
/**
 * Enqueue des assets admin partagés (tous modules em-wp).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Version d'asset avec bust cache local (filemtime).
 */
function em_wp_admin_asset_version(string $relative_path): string
{
    $absolute_path = get_template_directory() . '/' . ltrim($relative_path, '/');

    if (is_readable($absolute_path)) {
        return wp_get_theme()->get('Version') . '.' . (string) filemtime($absolute_path);
    }

    return wp_get_theme()->get('Version');
}

/**
 * Enqueue styles/scripts communs à tous les modules admin.
 *
 * @return array{styles: string[], scripts: string[]}
 */
function em_wp_admin_enqueue_shared_assets(): array
{
    $theme_uri = get_template_directory_uri();
    $accordion_version = em_wp_admin_asset_version('assets/admin/js/shared/accordion.js');
    $color_picker_css_version = em_wp_admin_asset_version('assets/admin/css/shared/color-picker.css');
    $module_common_css_version = em_wp_admin_asset_version('assets/admin/css/shared/module-common.css');
    $color_picker_js_version = em_wp_admin_asset_version('assets/admin/js/shared/color-picker.js');
    $style_preview_version = em_wp_admin_asset_version('assets/admin/js/shared/admin-module-style-preview.js');
    $confirm_modal_version = em_wp_admin_asset_version('assets/admin/js/shared/confirm-modal.js');

    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );
    wp_enqueue_style(
        'em-wp-admin-color-picker',
        $theme_uri . '/assets/admin/css/shared/color-picker.css',
        [],
        $color_picker_css_version
    );
    wp_enqueue_style(
        'em-wp-admin-module-common',
        $theme_uri . '/assets/admin/css/shared/module-common.css',
        ['em-wp-admin-color-picker'],
        $module_common_css_version
    );
    wp_enqueue_script(
        'em-wp-admin-accordion',
        $theme_uri . '/assets/admin/js/shared/accordion.js',
        [],
        $accordion_version,
        true
    );
    wp_enqueue_script(
        'em-wp-admin-confirm-modal',
        $theme_uri . '/assets/admin/js/shared/confirm-modal.js',
        [],
        $confirm_modal_version,
        true
    );
    wp_enqueue_script(
        'em-wp-admin-color-picker',
        $theme_uri . '/assets/admin/js/shared/color-picker.js',
        ['jquery', 'wp-color-picker'],
        $color_picker_js_version,
        true
    );
    wp_enqueue_script(
        'em-wp-admin-module-style-preview',
        $theme_uri . '/assets/admin/js/shared/admin-module-style-preview.js',
        ['jquery', 'em-wp-admin-color-picker'],
        $style_preview_version,
        true
    );

    return [
        'styles'  => ['em-wp-admin-color-picker', 'em-wp-admin-module-common'],
        'scripts' => ['em-wp-admin-accordion', 'em-wp-admin-confirm-modal', 'em-wp-admin-color-picker', 'em-wp-admin-module-style-preview'],
    ];
}

/**
 * Enqueue d'un module admin (CSS + JS spécifiques).
 */
function em_wp_admin_enqueue_module_assets(
    string $style_handle,
    string $style_relative_path,
    string $script_handle,
    string $script_relative_path,
    array $script_extra_deps = []
): void {
    $theme_version = em_wp_admin_asset_version($style_relative_path);
    $script_version = em_wp_admin_asset_version($script_relative_path);
    $theme_uri = get_template_directory_uri();
    $shared = em_wp_admin_enqueue_shared_assets();

    wp_enqueue_style(
        $style_handle,
        $theme_uri . '/' . ltrim($style_relative_path, '/'),
        $shared['styles'],
        $theme_version
    );

    wp_enqueue_script(
        $script_handle,
        $theme_uri . '/' . ltrim($script_relative_path, '/'),
        array_merge($shared['scripts'], ['jquery'], $script_extra_deps),
        $script_version,
        true
    );
}
