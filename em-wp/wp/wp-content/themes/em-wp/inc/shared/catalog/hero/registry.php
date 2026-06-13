<?php
/**
 * Registre catalogue Hero.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress du registre catalogue Hero.
 */
function em_wp_hero_catalog_option_name(): string
{
    return 'em_wp_hero_catalog';
}

/**
 * Entrées catalogue par défaut (slugs V2).
 *
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_hero_catalog_default_entries(): array
{
    return [
        'hero-mayami-default' => [
            'label'  => __('Hero Mayami default', 'em-wp'),
            'layout' => 'default',
        ],
        'hero-ellene-default' => [
            'label'  => __('Hero Ellene default', 'em-wp'),
            'layout' => 'default',
        ],
    ];
}

/**
 * Mapping slugs V1 → catalogue.
 *
 * @return array<string, string>
 */
function em_wp_hero_v1_slug_map(): array
{
    return [
        'mayami' => 'hero-mayami-default',
        'ellene' => 'hero-ellene-default',
    ];
}

/**
 * Slug catalogue depuis un slug V1 ou catalogue.
 */
function em_wp_hero_normalize_catalog_slug(string $slug): string
{
    $slug = sanitize_key($slug);
    $map = em_wp_hero_v1_slug_map();

    return $map[$slug] ?? $slug;
}

/**
 * Entrées catalogue enregistrées.
 *
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_hero_catalog_entries(): array
{
    $saved = get_option(em_wp_hero_catalog_option_name(), []);

    if (!is_array($saved) || $saved === []) {
        return em_wp_hero_catalog_default_entries();
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

    return $normalized !== [] ? $normalized : em_wp_hero_catalog_default_entries();
}

/**
 * Indique si un slug hero catalogue existe.
 */
function em_wp_hero_catalog_has(string $slug): bool
{
    $slug = em_wp_hero_normalize_catalog_slug($slug);

    return $slug !== '' && isset(em_wp_hero_catalog_entries()[$slug]);
}

/**
 * Slug page admin d'édition d'un hero catalogue.
 */
function em_wp_hero_catalog_edit_page_slug(string $catalog_slug): string
{
    return 'em-wp-ch-' . sanitize_key($catalog_slug);
}

/**
 * Slug catalogue depuis une page admin d'édition.
 */
function em_wp_hero_catalog_slug_from_page(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);
    $prefix = 'em-wp-ch-';

    if (!str_starts_with($page_slug, $prefix)) {
        return '';
    }

    return sanitize_key(substr($page_slug, strlen($prefix)));
}

/**
 * Liste pour select admin (slug => label).
 *
 * @return array<string, string>
 */
function em_wp_hero_catalog_choices(): array
{
    $choices = [];

    foreach (em_wp_hero_catalog_entries() as $slug => $entry) {
        $choices[$slug] = (string) ($entry['label'] ?? $slug);
    }

    return $choices;
}
