<?php
/**
 * Correctifs de chargement des scripts emoji.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Force les URLs emoji en absolu pour éviter les résolutions relatives cassées.
 */
function em_wp_force_emoji_script_src(string $src, string $handle): string
{
    $map = [
        'wp-emoji-loader' => 'js/wp-emoji-loader.js',
        'wpemoji'         => 'js/wp-emoji.js',
        'twemoji'         => 'js/twemoji.js',
        'concatemoji'     => 'js/wp-emoji-release.min.js',
    ];

    if (!isset($map[$handle])) {
        return $src;
    }

    $query = (string) wp_parse_url($src, PHP_URL_QUERY);
    $url = includes_url($map[$handle]);

    if ($query !== '') {
        $url .= '?' . $query;
    }

    return $url;
}
add_filter('script_loader_src', 'em_wp_force_emoji_script_src', 999, 2);

/**
 * Désactive les scripts/styles emoji core pour éviter l'injection du loader module.
 */
function em_wp_disable_core_emoji_assets(): void
{
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('init', 'em_wp_disable_core_emoji_assets', 1);
