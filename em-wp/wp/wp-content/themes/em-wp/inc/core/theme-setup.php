<?php
/**
 * Setup des supports et menus du thème.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('em_wp_setup')) {
    /**
     * Déclare les supports WordPress du thème.
     */
    function em_wp_setup(): void
    {
        load_theme_textdomain('em-wp', get_template_directory() . '/languages');

        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support(
            'html5',
            ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']
        );
        add_theme_support('custom-logo', [
            'height'      => 120,
            'width'       => 240,
            'flex-height' => true,
            'flex-width'  => true,
        ]);

        register_nav_menus([
            'primary' => __('Menu principal', 'em-wp'),
        ]);
    }
}
add_action('after_setup_theme', 'em_wp_setup');

/**
 * Retourne la limite d'upload cible du projet local (128 Mo).
 */
function em_wp_upload_limit_bytes(): int
{
    return 128 * 1024 * 1024;
}

/**
 * Applique des directives PHP permissives pour l'upload local.
 */
function em_wp_apply_upload_ini_limits(): void
{
    @ini_set('upload_max_filesize', '128M');
    @ini_set('post_max_size', '128M');
    @ini_set('max_execution_time', '300');
    @ini_set('max_input_time', '300');
    @ini_set('memory_limit', '512M');
}
add_action('init', 'em_wp_apply_upload_ini_limits', 1);

/**
 * Force la limite d'upload dans WordPress.
 */
function em_wp_filter_upload_size_limit(int $size): int
{
    return max($size, em_wp_upload_limit_bytes());
}
add_filter('upload_size_limit', 'em_wp_filter_upload_size_limit');

/**
 * Etend les tailles mime pour rester coherentes avec la limite locale.
 */
function em_wp_plupload_default_settings(array $settings): array
{
    $settings['filters'] = $settings['filters'] ?? [];
    $settings['filters']['max_file_size'] = '128mb';

    return $settings;
}
add_filter('plupload_default_settings', 'em_wp_plupload_default_settings');
