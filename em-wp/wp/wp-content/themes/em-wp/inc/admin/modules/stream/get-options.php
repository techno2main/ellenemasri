<?php
/**
 * Lecture options Stream par template.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nom d'option WP pour Stream × template.
 */
function em_wp_stream_option_name(?string $template_slug = null): string
{
    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_wp_get_editing_template_slug();
    }

    return em_wp_template_resolve_option_name('stream', $template_slug);
}

/**
 * Slug template en édition pour l'admin Stream.
 */
function em_wp_stream_admin_template_slug(): string
{
    return em_wp_get_editing_template_slug();
}

/**
 * Options Stream normalisées pour un template.
 */
function em_wp_stream_get_options(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        if (is_admin()) {
            $template_slug = em_wp_get_editing_template_slug();
        } else {
            $template_slug = em_wp_get_active_template_slug();
        }
    }

    $saved = em_wp_get_template_rubrique_options('stream', $template_slug);

    if ($saved === []) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_stream_default_options());
    $options['platforms'] = em_wp_stream_get_platforms_list($options);

    return function_exists('em_wp_rubrique_sync_enabled_for_admin')
        ? em_wp_rubrique_sync_enabled_for_admin('stream', $options)
        : $options;
}

/**
 * Options Stream pour le front (template live).
 */
function em_wp_stream_get_options_for_front(): array
{
    $template_slug = function_exists('em_wp_front_get_live_template_slug')
        ? em_wp_front_get_live_template_slug()
        : em_wp_get_active_template_slug();

    $saved = em_wp_get_template_rubrique_options('stream', $template_slug);

    if ($saved === []) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_stream_default_options());
    $options['platforms'] = em_wp_stream_get_platforms_list($options);

    return $options;
}
