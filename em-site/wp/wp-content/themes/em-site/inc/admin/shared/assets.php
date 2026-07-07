<?php
/**
 * Enqueue des assets admin partagés (tous modules em-site).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Version d'asset avec bust cache local (filemtime).
 */
function em_site_admin_asset_version(string $relative_path): string
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
function em_site_admin_enqueue_shared_assets(): array
{
    $theme_uri = get_template_directory_uri();
    $bootstrap_version = em_site_admin_asset_version('assets/admin/js/core/admin-bootstrap.js');
    $accordion_version = em_site_admin_asset_version('assets/admin/shared/js/navigation/accordion.js');
    $color_picker_css_version = em_site_admin_asset_version('assets/admin/shared/css/color-picker.css');
    $module_common_css_version = em_site_admin_asset_version('assets/admin/shared/css/module-common.css');
    $live_badge_css_version = em_site_admin_asset_version('assets/admin/shared/css/live-badge.css');
    $color_picker_js_version = em_site_admin_asset_version('assets/admin/shared/js/modals/color-picker.js');
    $color_modal_css_version = em_site_admin_asset_version('assets/admin/shared/css/color-modal.css');
    $color_modal_helpers_version = em_site_admin_asset_version('assets/admin/shared/js/modals/color-modal/helpers.js');
    $color_modal_engine_version = em_site_admin_asset_version('assets/admin/shared/js/modals/color-modal/engine.js');
    $color_modal_js_version = em_site_admin_asset_version('assets/admin/shared/js/modals/color-modal.js');
    $style_preview_version = em_site_admin_asset_version('assets/admin/shared/js/preview/admin-module-style-preview.js');
    $confirm_modal_version = em_site_admin_asset_version('assets/admin/shared/js/modals/confirm-modal.js');

    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );
    wp_enqueue_style(
        'em-site-admin-color-picker',
        $theme_uri . '/assets/admin/shared/css/color-picker.css',
        [],
        $color_picker_css_version
    );
    wp_enqueue_style(
        'em-site-admin-color-modal',
        $theme_uri . '/assets/admin/shared/css/color-modal.css',
        ['em-site-admin-color-picker'],
        $color_modal_css_version
    );
    wp_enqueue_style(
        'em-site-admin-module-common',
        $theme_uri . '/assets/admin/shared/css/module-common.css',
        ['em-site-admin-color-picker', 'em-site-admin-color-modal'],
        $module_common_css_version
    );
    wp_enqueue_style(
        'em-site-admin-live-badge',
        $theme_uri . '/assets/admin/shared/css/live-badge.css',
        ['em-site-admin-module-common'],
        $live_badge_css_version
    );
    wp_enqueue_script(
        'em-site-admin-bootstrap',
        $theme_uri . '/assets/admin/js/core/admin-bootstrap.js',
        [],
        $bootstrap_version,
        true
    );
    wp_enqueue_script(
        'em-site-admin-accordion',
        $theme_uri . '/assets/admin/shared/js/navigation/accordion.js',
        ['em-site-admin-bootstrap'],
        $accordion_version,
        true
    );
    wp_enqueue_script(
        'em-site-admin-confirm-modal',
        $theme_uri . '/assets/admin/shared/js/modals/confirm-modal.js',
        [],
        $confirm_modal_version,
        true
    );
    wp_enqueue_script(
        'em-site-admin-color-picker',
        $theme_uri . '/assets/admin/shared/js/modals/color-picker.js',
        ['jquery', 'wp-color-picker'],
        $color_picker_js_version,
        true
    );
    wp_enqueue_script(
        'em-site-admin-color-modal-helpers',
        $theme_uri . '/assets/admin/shared/js/modals/color-modal/helpers.js',
        ['jquery', 'wp-color-picker', 'em-site-admin-color-picker'],
        $color_modal_helpers_version,
        true
    );
    wp_enqueue_script(
        'em-site-admin-color-modal-engine',
        $theme_uri . '/assets/admin/shared/js/modals/color-modal/engine.js',
        ['jquery', 'wp-color-picker', 'em-site-admin-color-picker', 'em-site-admin-color-modal-helpers'],
        $color_modal_engine_version,
        true
    );
    wp_enqueue_script(
        'em-site-admin-color-modal',
        $theme_uri . '/assets/admin/shared/js/modals/color-modal.js',
        ['jquery', 'wp-color-picker', 'em-site-admin-color-picker', 'em-site-admin-color-modal-engine'],
        $color_modal_js_version,
        true
    );
    wp_enqueue_script(
        'em-site-admin-module-style-preview',
        $theme_uri . '/assets/admin/shared/js/preview/admin-module-style-preview.js',
        ['jquery', 'em-site-admin-color-picker', 'em-site-admin-color-modal'],
        $style_preview_version,
        true
    );

    return [
        'styles'  => ['em-site-admin-color-picker', 'em-site-admin-color-modal', 'em-site-admin-module-common', 'em-site-admin-live-badge'],
        'scripts' => ['em-site-admin-bootstrap', 'em-site-admin-accordion', 'em-site-admin-confirm-modal', 'em-site-admin-color-picker', 'em-site-admin-color-modal', 'em-site-admin-module-style-preview'],
    ];
}

/**
 * Auto-dismiss des notices admin (toasts) après 3 s sur les écrans em-site.
 */
function em_site_admin_enqueue_notice_autodismiss(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    $is_em_site = function_exists('em_site_admin_is_em_site_screen') && em_site_admin_is_em_site_screen();
    $is_dashboard = $pagenow === 'index.php';

    if (!$is_em_site && !$is_dashboard) {
        return;
    }

    wp_enqueue_script(
        'em-site-admin-notice-autodismiss',
        get_template_directory_uri() . '/assets/admin/shared/js/feedback/notice-autodismiss.js',
        [],
        em_site_admin_asset_version('assets/admin/shared/js/feedback/notice-autodismiss.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_site_admin_enqueue_notice_autodismiss', 5);

/**
 * Compatibilité des classes admin: ajoute des alias "em-" quand le markup
 * expose encore des classes "em-site-" (transition de préfixe).
 */
function em_site_admin_enqueue_class_prefix_compat(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    $is_em_site = function_exists('em_site_admin_is_em_site_screen') && em_site_admin_is_em_site_screen();
    $is_dashboard = $pagenow === 'index.php';

    if (!$is_em_site && !$is_dashboard) {
        return;
    }

    wp_enqueue_script(
        'em-site-admin-class-prefix-compat',
        get_template_directory_uri() . '/assets/admin/shared/js/compat/class-prefix-compat.js',
        [],
        em_site_admin_asset_version('assets/admin/shared/js/compat/class-prefix-compat.js'),
        false
    );
}
add_action('admin_enqueue_scripts', 'em_site_admin_enqueue_class_prefix_compat', 6);

/**
 * Enqueue d'un module admin (CSS + JS spécifiques).
 */
function em_site_admin_enqueue_module_assets(
    string $style_handle,
    string $style_relative_path,
    string $script_handle,
    string $script_relative_path,
    array $script_extra_deps = []
): void {
    $theme_version = em_site_admin_asset_version($style_relative_path);
    $script_version = em_site_admin_asset_version($script_relative_path);
    $theme_uri = get_template_directory_uri();
    $shared = em_site_admin_enqueue_shared_assets();

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
