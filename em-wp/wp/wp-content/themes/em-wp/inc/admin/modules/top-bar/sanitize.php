<?php
/**
 * Sanitize options Top Bar.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_top_bar_sanitize_rubrique_options($input): array
{
    $template_slug = em_wp_top_bar_resolve_template_slug();
    $existing = em_wp_top_bar_get_saved_rubrique_options($template_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);
    $top_bar_slug = sanitize_key((string) ($input['top_bar_slug'] ?? ($existing['top_bar_slug'] ?? '')));

    if ($top_bar_slug !== '' && function_exists('em_wp_top_bar_normalize_catalog_slug')) {
        $top_bar_slug = em_wp_top_bar_normalize_catalog_slug($top_bar_slug);
    }

    if ($top_bar_slug !== '' && function_exists('em_wp_top_bar_catalog_has') && !em_wp_top_bar_catalog_has($top_bar_slug)) {
        $top_bar_slug = sanitize_key((string) ($existing['top_bar_slug'] ?? ''));
    }

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        em_wp_admin_sync_rubrique_visibility_from_post('top-bar');
    }

    return [
        'enabled'          => $enabled,
        'top_bar_slug'     => $top_bar_slug,
        'background_color' => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'       => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
    ];
}

function em_wp_top_bar_sanitize_catalog_options($input): array
{
    $existing = em_wp_top_bar_catalog_default_options();

    if (!is_array($input)) {
        return $existing;
    }

    $defaults = em_wp_top_bar_catalog_default_options();
    $items = [];

    foreach (em_wp_top_bar_item_definitions() as $key => $title) {
        unset($title);
        $source = is_array($input['items'][$key] ?? null) ? $input['items'][$key] : [];
        $items[$key] = [
            'label'  => sanitize_text_field($source['label'] ?? $defaults['items'][$key]['label']),
            'href'   => esc_url_raw($source['href'] ?? $defaults['items'][$key]['href']),
            'hidden' => !empty($source['hidden']),
        ];
    }

    return [
        'logo_url'                 => esc_url_raw($input['logo_url'] ?? ''),
        'logo_hidden'              => !empty($input['logo_hidden']),
        'background_image_enabled' => !empty($input['background_image_enabled']),
        'background_image_url'     => esc_url_raw($input['background_image_url'] ?? ''),
        'background_image_hidden'  => !empty($input['background_image_hidden']),
        'items'                    => $items,
        'stream_icons_hidden'      => !empty($input['stream_icons_hidden']),
    ];
}

function em_wp_top_bar_sanitize_options($input, bool $sync_rubrique = true): array
{
    if ($sync_rubrique) {
        return em_wp_top_bar_sanitize_rubrique_options($input);
    }

    return em_wp_top_bar_sanitize_catalog_options($input);
}
