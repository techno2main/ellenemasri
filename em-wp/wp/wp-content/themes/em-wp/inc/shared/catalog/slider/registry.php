<?php
/**
 * Registre catalogue Slider.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress du registre catalogue Slider.
 */
function em_wp_slider_catalog_option_name(): string
{
    return 'em_wp_slider_catalog';
}

/**
 * Entrées catalogue par défaut.
 *
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_slider_catalog_default_entries(): array
{
    return [
        'slider-mayami-default' => [
            'label'  => __('Slider Mayami default', 'em-wp'),
            'layout' => 'default',
        ],
        'slider-ellene-default' => [
            'label'  => __('Slider Ellene default', 'em-wp'),
            'layout' => 'default',
        ],
    ];
}

/**
 * Mapping slugs V1 → catalogue.
 *
 * @return array<string, string>
 */
function em_wp_slider_v1_slug_map(): array
{
    return [
        'mayami' => 'slider-mayami-default',
        'ellene' => 'slider-ellene-default',
    ];
}

/**
 * Slug catalogue depuis V1 ou catalogue.
 */
function em_wp_slider_normalize_catalog_slug(string $slug): string
{
    $slug = sanitize_key($slug);
    $map = em_wp_slider_v1_slug_map();

    return $map[$slug] ?? $slug;
}

/**
 * Entrées catalogue enregistrées.
 *
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_slider_catalog_entries(): array
{
    $saved = get_option(em_wp_slider_catalog_option_name(), []);

    if (!is_array($saved) || $saved === []) {
        return em_wp_slider_catalog_default_entries();
    }

    $normalized = [];

    foreach ($saved as $slug => $entry) {
        $slug = sanitize_key((string) $slug);
        if ($slug === '' || !is_array($entry)) {
            continue;
        }

        $normalized[$slug] = [
            'label'  => sanitize_text_field((string) ($entry['label'] ?? $slug)),
            'layout' => sanitize_key((string) ($entry['layout'] ?? 'default')) ?: 'default',
        ];
    }

    return $normalized !== [] ? $normalized : em_wp_slider_catalog_default_entries();
}

/**
 * Indique si un slug slider catalogue existe.
 */
function em_wp_slider_catalog_has(string $slug): bool
{
    $slug = em_wp_slider_normalize_catalog_slug($slug);

    return $slug !== '' && isset(em_wp_slider_catalog_entries()[$slug]);
}

/**
 * Slug page admin d'édition d'un slider catalogue.
 */
function em_wp_slider_catalog_edit_page_slug(string $catalog_slug): string
{
    return 'em-wp-cs-' . sanitize_key($catalog_slug);
}

/**
 * Slug catalogue depuis page admin.
 */
function em_wp_slider_catalog_slug_from_page(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);
    $prefix = 'em-wp-cs-';

    if (!str_starts_with($page_slug, $prefix)) {
        return '';
    }

    return sanitize_key(substr($page_slug, strlen($prefix)));
}

/**
 * Liste pour select admin.
 *
 * @return array<string, string>
 */
function em_wp_slider_catalog_choices(): array
{
    $choices = [];

    foreach (em_wp_slider_catalog_entries() as $slug => $entry) {
        $choices[$slug] = (string) ($entry['label'] ?? $slug);
    }

    return $choices;
}
