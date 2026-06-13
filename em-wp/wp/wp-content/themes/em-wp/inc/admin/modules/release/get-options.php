<?php
/**
 * Lecture options Release par template.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_option_name(?string $template_slug = null): string
{
    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_wp_get_editing_template_slug();
    }

    return em_wp_template_resolve_option_name('release', $template_slug);
}

function em_wp_release_admin_template_slug(): string
{
    return em_wp_get_editing_template_slug();
}

function em_wp_release_get_options(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        if (is_admin()) {
            $template_slug = em_wp_get_editing_template_slug();
        } else {
            $template_slug = em_wp_get_active_template_slug();
        }
    }

    $saved = em_wp_get_template_rubrique_options('release', $template_slug);

    if ($saved === []) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_release_default_options());
    $options['rows'] = em_wp_release_normalize_rows($options['rows'] ?? []);

    return function_exists('em_wp_rubrique_sync_enabled_for_admin')
        ? em_wp_rubrique_sync_enabled_for_admin('release', $options)
        : $options;
}

function em_wp_release_get_options_for_front(): array
{
    $template_slug = function_exists('em_wp_front_get_live_template_slug')
        ? em_wp_front_get_live_template_slug()
        : em_wp_get_active_template_slug();

    $saved = em_wp_get_template_rubrique_options('release', $template_slug);

    if ($saved === []) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_release_default_options());
    $options['rows'] = em_wp_release_normalize_rows($options['rows'] ?? []);

    return $options;
}
