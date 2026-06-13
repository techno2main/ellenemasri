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

    em_wp_admin_register_module_save('social', [
        'nonce_action' => 'em_wp_social_save',
        'option_name'  => 'em_wp_social_options',
        'page_slug'    => function_exists('em_wp_social_page_slug') ? em_wp_social_page_slug() : 'em-wp-social',
        'sanitize'     => 'em_wp_social_sanitize_options',
    ]);

    em_wp_admin_register_module_save('video', [
        'nonce_action' => 'em_wp_video_save',
        'option_name'  => 'em_wp_video_options',
        'page_slug'    => function_exists('em_wp_video_page_slug') ? em_wp_video_page_slug() : 'em-wp-videos',
        'sanitize'     => 'em_wp_video_sanitize_options',
    ]);

    em_wp_admin_register_module_save('release', [
        'nonce_action' => 'em_wp_release_save',
        'option_name'  => 'em_wp_release_options',
        'page_slug'    => function_exists('em_wp_release_page_slug') ? em_wp_release_page_slug() : 'em-wp-releases',
        'sanitize'     => 'em_wp_release_sanitize_options',
    ]);

    em_wp_admin_register_module_save('cta', [
        'nonce_action' => 'em_wp_cta_save',
        'option_name'  => 'em_wp_cta_options',
        'page_slug'    => function_exists('em_wp_cta_page_slug') ? em_wp_cta_page_slug() : 'em-wp-cta',
        'sanitize'     => 'em_wp_cta_sanitize_options',
    ]);

    em_wp_admin_register_module_save('footer', [
        'nonce_action' => 'em_wp_footer_save',
        'option_name'  => 'em_wp_footer_options',
        'page_slug'    => function_exists('em_wp_footer_page_slug') ? em_wp_footer_page_slug() : 'em-wp-footer',
        'sanitize'     => 'em_wp_footer_sanitize_options',
    ]);
}

add_action('admin_init', 'em_wp_admin_register_all_module_saves', 0);
