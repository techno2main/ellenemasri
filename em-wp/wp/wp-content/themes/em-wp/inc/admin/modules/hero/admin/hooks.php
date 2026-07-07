<?php
/**
 * Hooks admin du module Hero.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre les pages d'edition Hero (masquees du menu - accessibles via le sommaire).
 */
function em_wp_hero_add_admin_page(): void
{
    $definitions = em_wp_hero_style_definitions();

    foreach ($definitions as $definition) {
        $page_slug = (string) ($definition['page_slug'] ?? '');

        if ($page_slug === '') {
            continue;
        }

        add_submenu_page(
            null,
            (string) ($definition['menu_title'] ?? __('Hero', 'em-wp')),
            (string) ($definition['menu_title'] ?? __('Hero', 'em-wp')),
            'manage_options',
            $page_slug,
            'em_wp_hero_render_admin_page'
        );
    }
}
add_action('admin_menu', 'em_wp_hero_add_admin_page', 20);

/**
 * Charge les assets admin du module Hero.
 */
function em_wp_hero_admin_enqueue(string $hook_suffix): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (!in_array($page_slug, em_wp_hero_admin_page_slugs(), true)) {
        return;
    }

    $context = em_wp_hero_get_admin_context();
    $style_slug = sanitize_key((string) ($context['style_slug'] ?? ''));

    if ($style_slug === '') {
        em_wp_admin_enqueue_shared_assets();

        return;
    }

    // Tous les heros catalogue partagent le layout admin Mayami.
    $asset_style_slug = 'mayami';

    em_wp_admin_enqueue_module_assets(
        'em-wp-hero-admin',
        'assets/admin/css/modules/hero/' . $asset_style_slug . '/hero.css',
        'em-wp-hero-admin',
        'assets/admin/js/modules/hero/' . $asset_style_slug . '/hero.js',
        ['wp-color-picker']
    );
}
add_action('admin_enqueue_scripts', 'em_wp_hero_admin_enqueue');

/**
 * Enregistre les options Hero via Settings API.
 */
function em_wp_hero_register_settings(): void
{
    register_setting(
        'em_wp_hero_global_group',
        'em_wp_hero_active_style',
        [
            'type'              => 'string',
            'sanitize_callback' => 'em_wp_hero_sanitize_active_style',
            'default'           => 'mayami',
        ]
    );

    foreach (array_keys(em_wp_hero_style_definitions()) as $style_slug) {
        register_setting(
            em_wp_hero_group_name($style_slug),
            em_wp_hero_option_name($style_slug),
            [
                'type'              => 'array',
                'sanitize_callback' => static function ($input) use ($style_slug): array {
                    return em_wp_hero_sanitize_options_for_style($input, $style_slug);
                },
                'default'           => em_wp_hero_default_options(),
            ]
        );
    }
}
add_action('admin_init', 'em_wp_hero_register_settings');
