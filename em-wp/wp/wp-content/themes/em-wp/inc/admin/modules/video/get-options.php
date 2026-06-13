<?php
/**
 * Lecture options Video par template.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_video_option_name(?string $template_slug = null): string
{
    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_wp_get_editing_template_slug();
    }

    return em_wp_template_resolve_option_name('video', $template_slug);
}

function em_wp_video_admin_template_slug(): string
{
    return em_wp_get_editing_template_slug();
}

function em_wp_video_get_options(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        if (is_admin()) {
            $template_slug = em_wp_get_editing_template_slug();
        } else {
            $template_slug = em_wp_get_active_template_slug();
        }
    }

    $saved = em_wp_get_template_rubrique_options('video', $template_slug);

    if ($saved === []) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_video_default_options());

    return function_exists('em_wp_rubrique_sync_enabled_for_admin')
        ? em_wp_rubrique_sync_enabled_for_admin('video', $options)
        : $options;
}

function em_wp_video_get_options_for_front(): array
{
    $template_slug = function_exists('em_wp_front_get_live_template_slug')
        ? em_wp_front_get_live_template_slug()
        : em_wp_get_active_template_slug();

    $saved = em_wp_get_template_rubrique_options('video', $template_slug);

    if ($saved === []) {
        $saved = [];
    }

    return wp_parse_args($saved, em_wp_video_default_options());
}
