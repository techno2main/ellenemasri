<?php
/**
 * Hooks admin du module Slider.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Charge les assets admin du module Slider.
 */
function em_site_slider_admin_enqueue(string $hook_suffix): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (!in_array($page_slug, em_site_slider_admin_page_slugs(), true)) {
        return;
    }

    $context = em_site_slider_get_admin_context();
    $style_slug = sanitize_key((string) ($context['style_slug'] ?? ''));

    em_site_admin_enqueue_shared_assets();

    if ($style_slug === '') {
        return;
    }

    $view_slug = em_site_slider_admin_asset_view_slug($style_slug);
    $theme_uri = get_template_directory_uri();

    wp_enqueue_script(
        'em-site-admin-slide-sortable',
        $theme_uri . '/assets/admin/shared/js/media/slide-sortable.js',
        [],
        em_site_admin_asset_version('assets/admin/shared/js/media/slide-sortable.js'),
        true
    );

    wp_enqueue_style(
        'em-site-slider-admin',
        $theme_uri . '/assets/admin/css/modules/slider/slider.css',
        ['em-site-admin-color-picker', 'em-site-admin-module-common'],
        em_site_admin_asset_version('assets/admin/css/modules/slider/slider.css')
    );

    wp_enqueue_script(
        'em-site-slider-admin-media-type',
        $theme_uri . '/assets/admin/js/modules/slider/' . $view_slug . '/parts/slider-media-and-type.js',
        ['jquery', 'wp-color-picker', 'em-site-admin-color-picker'],
        em_site_admin_asset_version('assets/admin/js/modules/slider/' . $view_slug . '/parts/slider-media-and-type.js'),
        true
    );

    wp_enqueue_script(
        'em-site-slider-admin-list-manager',
        $theme_uri . '/assets/admin/js/modules/slider/' . $view_slug . '/parts/slider-list-manager.js',
        ['jquery', 'em-site-admin-confirm-modal', 'em-site-admin-slide-sortable', 'em-site-slider-admin-media-type'],
        em_site_admin_asset_version('assets/admin/js/modules/slider/' . $view_slug . '/parts/slider-list-manager.js'),
        true
    );

    wp_enqueue_script(
        'em-site-slider-admin',
        $theme_uri . '/assets/admin/js/modules/slider/' . $view_slug . '/slider.js',
        ['jquery', 'em-site-slider-admin-media-type', 'em-site-slider-admin-list-manager'],
        em_site_admin_asset_version('assets/admin/js/modules/slider/' . $view_slug . '/slider.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_site_slider_admin_enqueue');

/**
 * Enregistre les pages d'edition Slider (masquees du menu - accessibles via le sommaire).
 */
function em_site_slider_add_admin_page(): void
{
    $definitions = em_site_slider_style_definitions();

    foreach ($definitions as $definition) {
        $page_slug = (string) ($definition['page_slug'] ?? '');

        if ($page_slug === '') {
            continue;
        }

        add_submenu_page(
            null,
            (string) ($definition['menu_title'] ?? __('Slider', 'em-site')),
            (string) ($definition['menu_title'] ?? __('Slider', 'em-site')),
            'manage_options',
            $page_slug,
            'em_site_slider_render_admin_page'
        );
    }
}
add_action('admin_menu', 'em_site_slider_add_admin_page', 20);

/**
 * Enregistre les options Slider via Settings API.
 */
function em_site_slider_register_settings(): void
{
    register_setting(
        'em_site_slider_global_group',
        'em_site_slider_active_style',
        [
            'type'              => 'string',
            'sanitize_callback' => 'em_site_slider_sanitize_active_style',
            'default'           => 'mayami',
        ]
    );

    foreach (array_keys(em_site_slider_style_definitions()) as $style_slug) {
        register_setting(
            em_site_slider_group_name($style_slug),
            em_site_slider_option_name($style_slug),
            [
                'type'              => 'array',
                'sanitize_callback' => static function ($input) use ($style_slug): array {
                    return em_site_slider_sanitize_options_for_style($input, $style_slug);
                },
                'default'           => em_site_slider_default_options($style_slug),
            ]
        );
    }
}
add_action('admin_init', 'em_site_slider_register_settings');
