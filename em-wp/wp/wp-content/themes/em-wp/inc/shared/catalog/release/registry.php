<?php
/**
 * Registre catalogue Release.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_catalog_option_name(): string
{
    return 'em_wp_release_catalog';
}

/**
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_release_catalog_default_entries(): array
{
    return [
        'release-mayami-default' => [
            'label'  => __('Release Mayami default', 'em-wp'),
            'layout' => 'default',
        ],
        'release-ellene-default' => [
            'label'  => __('Release Ellene default', 'em-wp'),
            'layout' => 'default',
        ],
    ];
}

/**
 * @return array<string, string>
 */
function em_wp_release_v1_slug_map(): array
{
    return [
        'mayami' => 'release-mayami-default',
        'ellene' => 'release-ellene-default',
    ];
}

function em_wp_release_normalize_catalog_slug(string $slug): string
{
    $slug = sanitize_key($slug);
    $map = em_wp_release_v1_slug_map();

    return $map[$slug] ?? $slug;
}

/**
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_release_catalog_entries(): array
{
    $saved = get_option(em_wp_release_catalog_option_name(), []);

    if (!is_array($saved) || $saved === []) {
        return em_wp_release_catalog_default_entries();
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

    return $normalized !== [] ? $normalized : em_wp_release_catalog_default_entries();
}

function em_wp_release_catalog_has(string $slug): bool
{
    $slug = em_wp_release_normalize_catalog_slug($slug);

    return $slug !== '' && isset(em_wp_release_catalog_entries()[$slug]);
}

function em_wp_release_catalog_edit_page_slug(string $catalog_slug): string
{
    return 'em-wp-crel-' . sanitize_key($catalog_slug);
}

function em_wp_release_catalog_slug_from_page(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);
    $prefix = 'em-wp-crel-';

    if (!str_starts_with($page_slug, $prefix)) {
        return '';
    }

    return sanitize_key(substr($page_slug, strlen($prefix)));
}

/**
 * @return array<string, string>
 */
function em_wp_release_catalog_choices(): array
{
    $choices = [];

    foreach (em_wp_release_catalog_entries() as $slug => $entry) {
        $choices[$slug] = (string) ($entry['label'] ?? $slug);
    }

    return $choices;
}
