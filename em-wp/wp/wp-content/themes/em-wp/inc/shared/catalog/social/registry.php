<?php
/**
 * Registre catalogue Social.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_social_catalog_option_name(): string
{
    return 'em_wp_social_catalog';
}

/**
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_social_catalog_default_entries(): array
{
    return [
        'social-mayami-default' => [
            'label'  => __('Social Mayami default', 'em-wp'),
            'layout' => 'default',
        ],
        'social-ellene-default' => [
            'label'  => __('Social Ellene default', 'em-wp'),
            'layout' => 'default',
        ],
    ];
}

/**
 * @return array<string, string>
 */
function em_wp_social_v1_slug_map(): array
{
    return [
        'mayami' => 'social-mayami-default',
        'ellene' => 'social-ellene-default',
    ];
}

function em_wp_social_normalize_catalog_slug(string $slug): string
{
    $slug = sanitize_key($slug);
    $map = em_wp_social_v1_slug_map();

    return $map[$slug] ?? $slug;
}

/**
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_social_catalog_entries(): array
{
    $saved = get_option(em_wp_social_catalog_option_name(), []);

    if (!is_array($saved) || $saved === []) {
        return em_wp_catalog_apply_default_entry(em_wp_social_catalog_default_entries());
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

    $entries = $normalized !== [] ? $normalized : em_wp_social_catalog_default_entries();

    return em_wp_catalog_apply_default_entry($entries);
}

function em_wp_social_catalog_has(string $slug): bool
{
    $slug = em_wp_social_normalize_catalog_slug($slug);

    return $slug !== '' && isset(em_wp_social_catalog_entries()[$slug]);
}

function em_wp_social_catalog_edit_page_slug(string $catalog_slug): string
{
    return 'em-wp-cso-' . sanitize_key($catalog_slug);
}

function em_wp_social_catalog_slug_from_page(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);
    $prefix = 'em-wp-cso-';

    if (!str_starts_with($page_slug, $prefix)) {
        return '';
    }

    return sanitize_key(substr($page_slug, strlen($prefix)));
}

/**
 * @return array<string, string>
 */
function em_wp_social_catalog_choices(): array
{
    $choices = [];

    foreach (em_wp_social_catalog_entries() as $slug => $entry) {
        $choices[$slug] = (string) ($entry['label'] ?? $slug);
    }

    return $choices;
}
