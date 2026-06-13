<?php
/**
 * Lecture options HEADER par template.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nom d'option WP pour HEADER × template.
 */
function em_wp_header_option_name(?string $template_slug = null): string
{
    if ($template_slug === null || $template_slug === '') {
        $template_slug = is_admin() && function_exists('em_wp_get_editing_template_slug')
            ? em_wp_get_editing_template_slug()
            : em_wp_get_active_template_slug();
    }

    return em_wp_template_resolve_option_name('header', $template_slug);
}

/**
 * Options HEADER normalisées.
 *
 * @return array{enabled:bool,hero_slug:string,slider_slug:string,layout:string}
 */
function em_wp_header_get_options(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        if (is_admin()) {
            $template_slug = em_wp_get_editing_template_slug();
        } else {
            $template_slug = function_exists('em_wp_front_get_live_template_slug')
                ? em_wp_front_get_live_template_slug()
                : em_wp_get_active_template_slug();
        }
    }

    $saved = em_wp_get_template_rubrique_options('header', $template_slug);

    return wp_parse_args($saved, em_wp_header_default_options());
}

/**
 * Options HEADER pour le front (template live).
 */
function em_wp_header_get_options_for_front(): array
{
    $template_slug = function_exists('em_wp_front_get_live_template_slug')
        ? em_wp_front_get_live_template_slug()
        : em_wp_get_active_template_slug();

    return em_wp_header_get_options($template_slug);
}
