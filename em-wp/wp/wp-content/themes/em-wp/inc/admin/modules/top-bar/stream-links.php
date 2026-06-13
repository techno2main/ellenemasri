<?php
/**
 * Modèle de données Stream Links Top Bar (liste ordonnée).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array{slug:string,label:string,href:string,active:bool}
 */
function em_wp_top_bar_default_stream_link_item(string $slug): array
{
    $definitions = em_wp_top_bar_stream_platform_definitions();
    $platform = $definitions[$slug] ?? null;

    return [
        'slug'   => sanitize_key($slug),
        'label'  => is_array($platform) ? (string) ($platform['label'] ?? $slug) : $slug,
        'href'   => '',
        'active' => false,
    ];
}

/**
 * Indique si stream_links[] utilise le nouveau format (liste indexée avec slug).
 *
 * @param mixed $stream_links
 */
function em_wp_top_bar_stream_links_is_list_format($stream_links): bool
{
    if (!is_array($stream_links) || $stream_links === []) {
        return false;
    }

    $first = reset($stream_links);

    return is_array($first) && array_key_exists('slug', $first);
}

/**
 * Convertit l'ancien format associatif (clé = slug) en liste ordonnée.
 *
 * @param array<string, array<string, mixed>> $keyed
 * @return array<int, array{slug:string,label:string,href:string,active:bool}>
 */
function em_wp_top_bar_stream_links_from_keyed(array $keyed): array
{
    $definitions = em_wp_top_bar_stream_platform_definitions();
    $links = [];

    foreach (array_keys($definitions) as $slug) {
        $source = is_array($keyed[$slug] ?? null) ? $keyed[$slug] : [];
        $links[] = em_wp_top_bar_normalize_stream_link_item(
            array_merge(['slug' => $slug], $source)
        );
    }

    return $links;
}

/**
 * @param array<string, mixed> $item
 * @return array{slug:string,label:string,href:string,active:bool}
 */
function em_wp_top_bar_normalize_stream_link_item(array $item): array
{
    $definitions = em_wp_top_bar_stream_platform_definitions();
    $slug = sanitize_key((string) ($item['slug'] ?? ''));
    if ($slug === '' || !isset($definitions[$slug])) {
        $slug = (string) array_key_first($definitions);
    }

    $defaults = em_wp_top_bar_default_stream_link_item($slug);

    return [
        'slug'   => $slug,
        'label'  => (string) ($item['label'] ?? $defaults['label']),
        'href'   => (string) ($item['href'] ?? ''),
        'active' => !empty($item['active']),
    ];
}

/**
 * Retourne la liste ordonnée des stream links.
 *
 * @param array<string, mixed> $options
 * @return array<int, array{slug:string,label:string,href:string,active:bool}>
 */
function em_wp_top_bar_get_stream_links_list(array $options): array
{
    $raw = $options['stream_links'] ?? [];
    $definitions = em_wp_top_bar_stream_platform_definitions();

    if (em_wp_top_bar_stream_links_is_list_format($raw)) {
        $links = [];
        $seen = [];

        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalized = em_wp_top_bar_normalize_stream_link_item($item);
            $slug = $normalized['slug'];
            if (isset($seen[$slug])) {
                continue;
            }

            $seen[$slug] = true;
            $links[] = $normalized;
        }

        foreach (array_keys($definitions) as $slug) {
            if (!isset($seen[$slug])) {
                $links[] = em_wp_top_bar_default_stream_link_item($slug);
            }
        }

        return $links;
    }

    if (is_array($raw) && $raw !== []) {
        return em_wp_top_bar_stream_links_from_keyed($raw);
    }

    $links = [];
    foreach (array_keys($definitions) as $slug) {
        $links[] = em_wp_top_bar_default_stream_link_item($slug);
    }

    return $links;
}

/**
 * Sanitize stream_links depuis le POST (ordre DOM = ordre sauvegardé).
 *
 * @param mixed $raw
 * @return array<int, array{slug:string,label:string,href:string,active:bool}>
 */
function em_wp_top_bar_sanitize_stream_links_from_input($raw): array
{
    if (!is_array($raw)) {
        return em_wp_top_bar_get_stream_links_list(['stream_links' => []]);
    }

    $definitions = em_wp_top_bar_stream_platform_definitions();
    $links = [];
    $seen = [];

    foreach ($raw as $item) {
        if (!is_array($item)) {
            continue;
        }

        $slug = sanitize_key((string) ($item['slug'] ?? ''));
        if ($slug === '' || !isset($definitions[$slug]) || isset($seen[$slug])) {
            continue;
        }

        $seen[$slug] = true;
        $links[] = [
            'slug'   => $slug,
            'label'  => sanitize_text_field($item['label'] ?? $definitions[$slug]['label']),
            'href'   => esc_url_raw($item['href'] ?? ''),
            'active' => !empty($item['active']),
        ];
    }

    foreach (array_keys($definitions) as $slug) {
        if (!isset($seen[$slug])) {
            $links[] = em_wp_top_bar_default_stream_link_item($slug);
        }
    }

    return $links;
}
