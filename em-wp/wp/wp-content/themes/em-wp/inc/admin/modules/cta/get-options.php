<?php
/**
 * Lecture options CTA par template.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_option_name(?string $template_slug = null): string
{
    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_wp_get_editing_template_slug();
    }

    return em_wp_template_resolve_option_name('cta', $template_slug);
}

function em_wp_cta_admin_template_slug(): string
{
    return em_wp_get_editing_template_slug();
}

function em_wp_cta_fill_texture_default(array $options): array
{
    if (trim((string) ($options['texture_image'] ?? '')) === '') {
        $options['texture_image'] = em_wp_cta_default_texture_url();
    }

    return $options;
}

function em_wp_cta_get_options(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        if (is_admin()) {
            $template_slug = em_wp_get_editing_template_slug();
        } else {
            $template_slug = em_wp_get_active_template_slug();
        }
    }

    $saved = em_wp_get_template_rubrique_options('cta', $template_slug);

    if ($saved === []) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_cta_default_options());
    $options = em_wp_cta_fill_texture_default($options);

    return function_exists('em_wp_rubrique_sync_enabled_for_admin')
        ? em_wp_rubrique_sync_enabled_for_admin('cta', $options)
        : $options;
}

function em_wp_cta_get_options_for_front(): array
{
    $template_slug = function_exists('em_wp_front_get_live_template_slug')
        ? em_wp_front_get_live_template_slug()
        : em_wp_get_active_template_slug();

    $saved = em_wp_get_template_rubrique_options('cta', $template_slug);

    if ($saved === []) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_cta_default_options());

    return em_wp_cta_fill_texture_default($options);
}
