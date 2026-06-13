<?php
/**
 * Lecture options Top Bar par template.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_top_bar_option_name(?string $template_slug = null): string
{
    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_wp_get_editing_template_slug();
    }

    return em_wp_template_resolve_option_name('top-bar', $template_slug);
}

function em_wp_top_bar_admin_template_slug(): string
{
    return em_wp_get_editing_template_slug();
}

/**
 * Retourne les options Top Bar normalisees.
 */
function em_wp_top_bar_get_options(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        if (is_admin()) {
            $template_slug = em_wp_get_editing_template_slug();
        } else {
            $template_slug = em_wp_get_active_template_slug();
        }
    }

    $saved = em_wp_get_template_rubrique_options('top-bar', $template_slug);

    if ($saved === []) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_top_bar_default_options());
    $options['items'] = wp_parse_args(
        is_array($saved['items'] ?? null) ? $saved['items'] : [],
        em_wp_top_bar_default_options()['items']
    );
    $options['stream_icons_hidden'] = !empty($saved['stream_icons_hidden']);

    if (function_exists('em_wp_rubrique_sync_enabled_for_admin')) {
        return em_wp_rubrique_sync_enabled_for_admin('top-bar', $options);
    }

    return $options;
}

function em_wp_top_bar_get_options_for_front(): array
{
    $template_slug = function_exists('em_wp_front_get_live_template_slug')
        ? em_wp_front_get_live_template_slug()
        : em_wp_get_active_template_slug();

    $saved = em_wp_get_template_rubrique_options('top-bar', $template_slug);

    if ($saved === []) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_top_bar_default_options());
    $options['items'] = wp_parse_args(
        is_array($saved['items'] ?? null) ? $saved['items'] : [],
        em_wp_top_bar_default_options()['items']
    );
    $options['stream_icons_hidden'] = !empty($saved['stream_icons_hidden']);

    return $options;
}
