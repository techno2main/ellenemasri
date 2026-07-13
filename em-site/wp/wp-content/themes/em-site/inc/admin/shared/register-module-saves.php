<?php
/**
 * Enregistrement centralisé des handlers de sauvegarde admin em-site.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre les handlers de sauvegarde pour tous les modules.
 */
function em_site_admin_register_all_module_saves(): void
{
    em_site_admin_register_module_save('stream', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_site_stream_save_' . $catalog_slug;
            }

            return 'em_site_stream_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_stream_catalog_item_option_name')) {
                return em_site_stream_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_site_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_site_get_editing_template_slug')) {
                $template_slug = em_site_get_editing_template_slug();
            }

            return em_site_stream_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_stream_catalog_item_option_name')) {
                return em_site_stream_catalog_item_option_name($catalog_slug);
            }

            return em_site_stream_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_stream_style_definitions')) {
                $definitions = em_site_stream_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_site_stream_page_slug());
            }

            return function_exists('em_site_stream_page_slug') ? em_site_stream_page_slug() : 'em-stream';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_stream_sanitize_options_for_style')) {
                return em_site_stream_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_site_stream_sanitize_options($input);
        },
    ]);

    em_site_admin_register_module_save('top-bar', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_site_top_bar_save_' . $catalog_slug;
            }

            return 'em_site_top_bar_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_top_bar_catalog_item_option_name')) {
                return em_site_top_bar_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_site_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_site_get_editing_template_slug')) {
                $template_slug = em_site_get_editing_template_slug();
            }

            return em_site_top_bar_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_top_bar_catalog_item_option_name')) {
                return em_site_top_bar_catalog_item_option_name($catalog_slug);
            }

            return em_site_top_bar_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_top_bar_style_definitions')) {
                $definitions = em_site_top_bar_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_site_top_bar_page_slug());
            }

            return function_exists('em_site_top_bar_page_slug') ? em_site_top_bar_page_slug() : 'em-top-bar';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_top_bar_sanitize_options_for_style')) {
                return em_site_top_bar_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_site_top_bar_sanitize_options($input);
        },
    ]);

    em_site_admin_register_module_save('header', [
        'nonce_action' => 'em_site_header_save',
        'option_name'  => static function (): string {
            $template_slug = sanitize_key((string) ($_POST['em_site_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_site_get_editing_template_slug')) {
                $template_slug = em_site_get_editing_template_slug();
            }

            return em_site_header_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => 'em_site_header_options',
        'page_slug'    => function_exists('em_site_header_page_slug') ? em_site_header_page_slug() : 'em-header',
        'sanitize'     => 'em_site_header_sanitize_options',
    ]);

    em_site_admin_register_module_save('hero', [
        'nonce_action' => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return 'em_site_hero_save_' . ($style_slug !== '' ? $style_slug : 'mayami');
        },
        'option_name'  => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return em_site_hero_option_name($style_slug !== '' ? $style_slug : 'mayami');
        },
        'page_slug'    => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $definitions = em_site_hero_style_definitions();

            return (string) ($definitions[$style_slug]['page_slug'] ?? 'em-hero-mayami');
        },
        'sanitize'     => static function ($input): array {
            $style_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return em_site_hero_sanitize_options_for_style($input, $style_slug !== '' ? $style_slug : 'mayami');
        },
    ]);

    em_site_admin_register_module_save('slider', [
        'nonce_action' => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return 'em_site_slider_save_' . ($style_slug !== '' ? $style_slug : 'mayami');
        },
        'option_name'  => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return em_site_slider_option_name($style_slug !== '' ? $style_slug : 'mayami');
        },
        'page_slug'    => static function (): string {
            $style_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $definitions = em_site_slider_style_definitions();

            return (string) ($definitions[$style_slug]['page_slug'] ?? 'em-slider-mayami');
        },
        'sanitize'     => static function ($input): array {
            $style_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            return em_site_slider_sanitize_options_for_style($input, $style_slug !== '' ? $style_slug : 'mayami');
        },
    ]);

    em_site_admin_register_module_save('social', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_site_social_save_' . $catalog_slug;
            }

            return 'em_site_social_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_social_catalog_item_option_name')) {
                return em_site_social_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_site_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_site_get_editing_template_slug')) {
                $template_slug = em_site_get_editing_template_slug();
            }

            return em_site_social_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_social_catalog_item_option_name')) {
                return em_site_social_catalog_item_option_name($catalog_slug);
            }

            return em_site_social_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_social_style_definitions')) {
                $definitions = em_site_social_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_site_social_page_slug());
            }

            return function_exists('em_site_social_page_slug') ? em_site_social_page_slug() : 'em-social';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_social_sanitize_options_for_style')) {
                return em_site_social_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_site_social_sanitize_options($input);
        },
    ]);

    em_site_admin_register_module_save('video', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_site_video_save_' . $catalog_slug;
            }

            return 'em_site_video_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_video_catalog_item_option_name')) {
                return em_site_video_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_site_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_site_get_editing_template_slug')) {
                $template_slug = em_site_get_editing_template_slug();
            }

            return em_site_video_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_video_catalog_item_option_name')) {
                return em_site_video_catalog_item_option_name($catalog_slug);
            }

            return em_site_video_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_video_style_definitions')) {
                $definitions = em_site_video_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_site_video_page_slug());
            }

            return function_exists('em_site_video_page_slug') ? em_site_video_page_slug() : 'em-videos';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_video_sanitize_options_for_style')) {
                return em_site_video_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_site_video_sanitize_options($input);
        },
    ]);

    em_site_admin_register_module_save('release', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_site_release_save_' . $catalog_slug;
            }

            return 'em_site_release_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_release_catalog_item_option_name')) {
                return em_site_release_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_site_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_site_get_editing_template_slug')) {
                $template_slug = em_site_get_editing_template_slug();
            }

            return em_site_release_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_release_catalog_item_option_name')) {
                return em_site_release_catalog_item_option_name($catalog_slug);
            }

            return em_site_release_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_release_style_definitions')) {
                $definitions = em_site_release_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_site_release_page_slug());
            }

            return function_exists('em_site_release_page_slug') ? em_site_release_page_slug() : 'em-releases';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_release_sanitize_options_for_style')) {
                return em_site_release_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_site_release_sanitize_options($input);
        },
    ]);

    em_site_admin_register_module_save('cta', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_site_cta_save_' . $catalog_slug;
            }

            return 'em_site_cta_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_cta_catalog_item_option_name')) {
                return em_site_cta_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_site_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_site_get_editing_template_slug')) {
                $template_slug = em_site_get_editing_template_slug();
            }

            return em_site_cta_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_cta_catalog_item_option_name')) {
                return em_site_cta_catalog_item_option_name($catalog_slug);
            }

            return em_site_cta_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_cta_style_definitions')) {
                $definitions = em_site_cta_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_site_cta_page_slug());
            }

            return function_exists('em_site_cta_page_slug') ? em_site_cta_page_slug() : 'em-cta';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_cta_sanitize_options_for_style')) {
                return em_site_cta_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_site_cta_sanitize_options($input);
        },
    ]);

    em_site_admin_register_module_save('footer', [
        'nonce_action' => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '') {
                return 'em_site_footer_save_' . $catalog_slug;
            }

            return 'em_site_footer_save';
        },
        'option_name'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_footer_catalog_item_option_name')) {
                return em_site_footer_catalog_item_option_name($catalog_slug);
            }

            $template_slug = sanitize_key((string) ($_POST['em_site_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($template_slug === '' && function_exists('em_site_get_editing_template_slug')) {
                $template_slug = em_site_get_editing_template_slug();
            }

            return em_site_footer_option_name($template_slug !== '' ? $template_slug : null);
        },
        'value_field'  => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_footer_catalog_item_option_name')) {
                return em_site_footer_catalog_item_option_name($catalog_slug);
            }

            return em_site_footer_form_option_key();
        },
        'page_slug'    => static function (): string {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_footer_style_definitions')) {
                $definitions = em_site_footer_style_definitions();

                return (string) ($definitions[$catalog_slug]['page_slug'] ?? em_site_footer_page_slug());
            }

            return function_exists('em_site_footer_page_slug') ? em_site_footer_page_slug() : 'em-footer';
        },
        'sanitize'     => static function ($input): array {
            $catalog_slug = sanitize_key((string) ($_POST['em_site_module_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ($catalog_slug !== '' && function_exists('em_site_footer_sanitize_options_for_style')) {
                return em_site_footer_sanitize_options_for_style($input, $catalog_slug);
            }

            return em_site_footer_sanitize_options($input);
        },
    ]);

    if (function_exists('em_site_custom_catalog_modules')) {
        foreach (array_keys(em_site_custom_catalog_modules()) as $module_slug) {
            $module_slug = sanitize_key((string) $module_slug);

            if ($module_slug === '') {
                continue;
            }

            $save_key = $module_slug;
            $nonce_prefix = 'em_site_' . str_replace('-', '_', $module_slug) . '_save';

            em_site_admin_register_module_save($save_key, [
                'nonce_action' => static function () use ($nonce_prefix): string {
                    return $nonce_prefix;
                },
                'option_name'  => static function () use ($module_slug): string {
                    $template_slug = sanitize_key((string) ($_POST['em_site_template_context'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

                    if ($template_slug === '' && function_exists('em_site_get_editing_template_slug')) {
                        $template_slug = em_site_get_editing_template_slug();
                    }

                    return em_site_custom_catalog_rubrique_option_name($module_slug, $template_slug !== '' ? $template_slug : null);
                },
                'value_field'  => static function () use ($module_slug): string {
                    return em_site_custom_catalog_rubrique_form_option_key($module_slug);
                },
                'page_slug'    => static function () use ($module_slug): string {
                    return em_site_custom_catalog_rubrique_page_slug($module_slug);
                },
                'sanitize'     => static function ($input) use ($module_slug): array {
                    return em_site_custom_catalog_rubrique_sanitize_options($module_slug, $input);
                },
            ]);
        }
    }
}

add_action('admin_init', 'em_site_admin_register_all_module_saves', 0);
