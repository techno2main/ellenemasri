<?php
/**
 * Enregistrement centralisé des handlers de sauvegarde admin em-wp.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre les handlers de sauvegarde pour tous les modules.
 */
function em_wp_admin_register_all_module_saves(): void
{
    em_wp_admin_register_module_save('stream', [
        'nonce_action' => 'em_wp_stream_save',
        'option_name'  => 'em_wp_stream_options',
        'page_slug'    => function_exists('em_wp_stream_page_slug') ? em_wp_stream_page_slug() : 'em-wp-stream',
        'sanitize'     => 'em_wp_stream_sanitize_options',
    ]);

    em_wp_admin_register_module_save('top-bar', [
        'nonce_action' => 'em_wp_top_bar_save',
        'option_name'  => 'em_wp_top_bar_options',
        'page_slug'    => function_exists('em_wp_top_bar_page_slug') ? em_wp_top_bar_page_slug() : 'em-wp-top-bar',
        'sanitize'     => 'em_wp_top_bar_sanitize_options',
    ]);

    em_wp_admin_register_module_save('hero', [
        'nonce_action' => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return 'em_wp_hero_save_' . ($style_slug !== '' ? $style_slug : 'mayami');
        },
        'option_name'  => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return em_wp_hero_option_name($style_slug !== '' ? $style_slug : 'mayami');
        },
        'page_slug'    => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $definitions = em_wp_hero_style_definitions();

            return (string) ($definitions[$style_slug]['page_slug'] ?? 'em-wp-hero-mayami');
        },
        'sanitize'     => static function ($input): array {
            $style_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return em_wp_hero_sanitize_options_for_style($input, $style_slug !== '' ? $style_slug : 'mayami');
        },
    ]);

    em_wp_admin_register_module_save('hero-active', [
        'type'          => 'active_style',
        'nonce_action'  => 'em_wp_hero_active_save',
        'option_name'   => 'em_wp_hero_active_style',
        'value_field'   => 'em_wp_hero_active_style',
        'page_slug'     => 'referer',
        'fallback_page' => function_exists('em_wp_hero_hub_menu_slug') ? em_wp_hero_hub_menu_slug() : 'em-wp-heros',
        'sanitize'      => 'em_wp_hero_sanitize_active_style',
    ]);

    em_wp_admin_register_module_save('slider', [
        'nonce_action' => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return 'em_wp_slider_save_' . ($style_slug !== '' ? $style_slug : 'mayami');
        },
        'option_name'  => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return em_wp_slider_option_name($style_slug !== '' ? $style_slug : 'mayami');
        },
        'page_slug'    => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $definitions = em_wp_slider_style_definitions();

            return (string) ($definitions[$style_slug]['page_slug'] ?? 'em-wp-slider-mayami');
        },
        'sanitize'     => static function ($input): array {
            $style_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return em_wp_slider_sanitize_options_for_style($input, $style_slug !== '' ? $style_slug : 'mayami');
        },
    ]);

    em_wp_admin_register_module_save('slider-active', [
        'type'          => 'active_style',
        'nonce_action'  => 'em_wp_slider_active_save',
        'option_name'   => 'em_wp_slider_active_style',
        'value_field'   => 'em_wp_slider_active_style',
        'page_slug'     => 'referer',
        'fallback_page' => function_exists('em_wp_slider_hub_menu_slug') ? em_wp_slider_hub_menu_slug() : 'em-wp-sliders',
        'sanitize'      => 'em_wp_slider_sanitize_active_style',
    ]);
}

add_action('admin_init', 'em_wp_admin_register_all_module_saves', 0);
