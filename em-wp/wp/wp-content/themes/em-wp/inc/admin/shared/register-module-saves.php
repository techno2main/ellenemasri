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
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_wp_stream_save_' . $catalog_slug;
            }

            return 'em_wp_stream_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_stream_catalog_item_option_name')) {
                return em_wp_stream_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_wp_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_wp_get_editing_template_slug')) {
                $template_slug = em_wp_get_editing_template_slug();
            }

            return em_wp_stream_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_stream_catalog_item_option_name')) {
                return em_wp_stream_catalog_item_option_name($catalog_slug);
            }

            return em_wp_stream_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_stream_style_definitions')) {
                $definitions = em_wp_stream_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_wp_stream_page_slug());
            }

            return function_exists('em_wp_stream_page_slug') ? em_wp_stream_page_slug() : 'em-wp-stream';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_stream_sanitize_options_for_style')) {
                return em_wp_stream_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_wp_stream_sanitize_options($input);
        },
    ]);

    em_wp_admin_register_module_save('top-bar', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_wp_top_bar_save_' . $catalog_slug;
            }

            return 'em_wp_top_bar_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_top_bar_catalog_item_option_name')) {
                return em_wp_top_bar_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_wp_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_wp_get_editing_template_slug')) {
                $template_slug = em_wp_get_editing_template_slug();
            }

            return em_wp_top_bar_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_top_bar_catalog_item_option_name')) {
                return em_wp_top_bar_catalog_item_option_name($catalog_slug);
            }

            return em_wp_top_bar_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_top_bar_style_definitions')) {
                $definitions = em_wp_top_bar_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_wp_top_bar_page_slug());
            }

            return function_exists('em_wp_top_bar_page_slug') ? em_wp_top_bar_page_slug() : 'em-wp-top-bar';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_top_bar_sanitize_options_for_style')) {
                return em_wp_top_bar_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_wp_top_bar_sanitize_options($input);
        },
    ]);

    em_wp_admin_register_module_save('header', [
        'nonce_action' => 'em_wp_header_save',
        'option_name'  => static function (): string {
            $template_slug = sanitize_key((string) ($_POST['em_wp_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_wp_get_editing_template_slug')) {
                $template_slug = em_wp_get_editing_template_slug();
            }

            return em_wp_header_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => 'em_wp_header_options',
        'page_slug'    => function_exists('em_wp_header_page_slug') ? em_wp_header_page_slug() : 'em-wp-header',
        'sanitize'     => 'em_wp_header_sanitize_options',
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

    em_wp_admin_register_module_save('social', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_wp_social_save_' . $catalog_slug;
            }

            return 'em_wp_social_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_social_catalog_item_option_name')) {
                return em_wp_social_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_wp_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_wp_get_editing_template_slug')) {
                $template_slug = em_wp_get_editing_template_slug();
            }

            return em_wp_social_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_social_catalog_item_option_name')) {
                return em_wp_social_catalog_item_option_name($catalog_slug);
            }

            return em_wp_social_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_social_style_definitions')) {
                $definitions = em_wp_social_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_wp_social_page_slug());
            }

            return function_exists('em_wp_social_page_slug') ? em_wp_social_page_slug() : 'em-wp-social';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_social_sanitize_options_for_style')) {
                return em_wp_social_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_wp_social_sanitize_options($input);
        },
    ]);

    em_wp_admin_register_module_save('video', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_wp_video_save_' . $catalog_slug;
            }

            return 'em_wp_video_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_video_catalog_item_option_name')) {
                return em_wp_video_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_wp_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_wp_get_editing_template_slug')) {
                $template_slug = em_wp_get_editing_template_slug();
            }

            return em_wp_video_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_video_catalog_item_option_name')) {
                return em_wp_video_catalog_item_option_name($catalog_slug);
            }

            return em_wp_video_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_video_style_definitions')) {
                $definitions = em_wp_video_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_wp_video_page_slug());
            }

            return function_exists('em_wp_video_page_slug') ? em_wp_video_page_slug() : 'em-wp-videos';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_video_sanitize_options_for_style')) {
                return em_wp_video_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_wp_video_sanitize_options($input);
        },
    ]);

    em_wp_admin_register_module_save('release', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_wp_release_save_' . $catalog_slug;
            }

            return 'em_wp_release_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_release_catalog_item_option_name')) {
                return em_wp_release_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_wp_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_wp_get_editing_template_slug')) {
                $template_slug = em_wp_get_editing_template_slug();
            }

            return em_wp_release_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_release_catalog_item_option_name')) {
                return em_wp_release_catalog_item_option_name($catalog_slug);
            }

            return em_wp_release_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_release_style_definitions')) {
                $definitions = em_wp_release_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_wp_release_page_slug());
            }

            return function_exists('em_wp_release_page_slug') ? em_wp_release_page_slug() : 'em-wp-releases';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_release_sanitize_options_for_style')) {
                return em_wp_release_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_wp_release_sanitize_options($input);
        },
    ]);

    em_wp_admin_register_module_save('cta', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_wp_cta_save_' . $catalog_slug;
            }

            return 'em_wp_cta_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_cta_catalog_item_option_name')) {
                return em_wp_cta_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_wp_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_wp_get_editing_template_slug')) {
                $template_slug = em_wp_get_editing_template_slug();
            }

            return em_wp_cta_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_cta_catalog_item_option_name')) {
                return em_wp_cta_catalog_item_option_name($catalog_slug);
            }

            return em_wp_cta_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_cta_style_definitions')) {
                $definitions = em_wp_cta_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_wp_cta_page_slug());
            }

            return function_exists('em_wp_cta_page_slug') ? em_wp_cta_page_slug() : 'em-wp-cta';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_cta_sanitize_options_for_style')) {
                return em_wp_cta_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_wp_cta_sanitize_options($input);
        },
    ]);

    em_wp_admin_register_module_save('footer', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_wp_footer_save_' . $catalog_slug;
            }

            return 'em_wp_footer_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_footer_catalog_item_option_name')) {
                return em_wp_footer_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_wp_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_wp_get_editing_template_slug')) {
                $template_slug = em_wp_get_editing_template_slug();
            }

            return em_wp_footer_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_footer_catalog_item_option_name')) {
                return em_wp_footer_catalog_item_option_name($catalog_slug);
            }

            return em_wp_footer_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_footer_style_definitions')) {
                $definitions = em_wp_footer_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_wp_footer_page_slug());
            }

            return function_exists('em_wp_footer_page_slug') ? em_wp_footer_page_slug() : 'em-wp-footer';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_wp_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_wp_footer_sanitize_options_for_style')) {
                return em_wp_footer_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_wp_footer_sanitize_options($input);
        },
    ]);

    if (function_exists('em_wp_custom_catalog_modules')) {
        foreach (array_keys(em_wp_custom_catalog_modules()) as $module_slug) {
            $module_slug = sanitize_key((string) $module_slug);

            if ($module_slug === '') {
                continue;
            }

            $save_key = $module_slug;
            $nonce_prefix = 'em_wp_' . str_replace('-', '_', $module_slug) . '_save';

            em_wp_admin_register_module_save($save_key, [
                'nonce_action' => static function () use ($nonce_prefix): string {
                    return $nonce_prefix;
                },
                'option_name'  => static function () use ($module_slug): string {
                    $template_slug = sanitize_key((string) ($_POST['em_wp_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

                    if ($template_slug === '' && function_exists('em_wp_get_editing_template_slug')) {
                        $template_slug = em_wp_get_editing_template_slug();
                    }

                    return em_wp_custom_catalog_rubrique_option_name($module_slug, $template_slug !== '' ? $template_slug : null);
                },
                'value_field'  => static function () use ($module_slug): string {
                    return em_wp_custom_catalog_rubrique_form_option_key($module_slug);
                },
                'page_slug'    => static function () use ($module_slug): string {
                    return em_wp_custom_catalog_rubrique_page_slug($module_slug);
                },
                'sanitize'     => static function ($input) use ($module_slug): array {
                    return em_wp_custom_catalog_rubrique_sanitize_options($module_slug, $input);
                },
            ]);
        }
    }
}

add_action('admin_init', 'em_wp_admin_register_all_module_saves', 0);
