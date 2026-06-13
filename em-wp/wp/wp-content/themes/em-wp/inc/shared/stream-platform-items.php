<?php
/**
 * Données plateformes stream : contenu (STREAM) + masquage section icônes (TOP-BAR).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array{slug:string,label:string,href:string,active:bool}
 */
function em_wp_stream_default_platform_item(string $slug): array
{
    $definitions = em_wp_stream_platform_definitions();
    $platform = $definitions[$slug] ?? null;

    return [
        'slug'   => sanitize_key($slug),
        'label'  => is_array($platform) ? (string) ($platform['label'] ?? $slug) : $slug,
        'href'   => '',
        'active' => false,
    ];
}

/**
 * @param mixed $platforms
 */
function em_wp_stream_platforms_is_list_format($platforms): bool
{
    if (!is_array($platforms) || $platforms === []) {
        return false;
    }

    $first = reset($platforms);

    return is_array($first) && array_key_exists('slug', $first);
}

/**
 * @param array<string, mixed> $item
 * @return array{slug:string,label:string,href:string,active:bool}
 */
function em_wp_stream_normalize_platform_item(array $item): array
{
    $definitions = em_wp_stream_platform_definitions();
    $slug = sanitize_key((string) ($item['slug'] ?? ''));
    if ($slug === '' || !isset($definitions[$slug])) {
        $slug = (string) array_key_first($definitions);
    }

    $defaults = em_wp_stream_default_platform_item($slug);

    return [
        'slug'   => $slug,
        'label'  => (string) ($item['label'] ?? $defaults['label']),
        'href'   => (string) ($item['href'] ?? ''),
        'active' => !empty($item['active']),
    ];
}

/**
 * Liste ordonnée des plateformes stream (contenu).
 *
 * @param array<string, mixed>|null $stream_options
 * @return array<int, array{slug:string,label:string,href:string,active:bool}>
 */
function em_wp_stream_get_platforms_list(?array $stream_options = null): array
{
    if ($stream_options === null) {
        $stream_options = function_exists('em_wp_stream_get_options')
            ? em_wp_stream_get_options()
            : em_wp_stream_default_options();
    }

    $raw = $stream_options['platforms'] ?? [];
    $definitions = em_wp_stream_platform_definitions();
    $platforms = [];
    $seen = [];

    if (em_wp_stream_platforms_is_list_format($raw)) {
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalized = em_wp_stream_normalize_platform_item($item);
            $slug = $normalized['slug'];
            if (isset($seen[$slug])) {
                continue;
            }

            $seen[$slug] = true;
            $platforms[] = $normalized;
        }
    }

    foreach (array_keys($definitions) as $slug) {
        if (!isset($seen[$slug])) {
            $platforms[] = em_wp_stream_default_platform_item($slug);
        }
    }

    return $platforms;
}

/**
 * @param mixed $raw
 * @return array<int, array{slug:string,label:string,href:string,active:bool}>
 */
function em_wp_stream_sanitize_platforms_from_input($raw): array
{
    if (!is_array($raw)) {
        return em_wp_stream_get_platforms_list(['platforms' => []]);
    }

    $definitions = em_wp_stream_platform_definitions();
    $platforms = [];
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
        $platforms[] = [
            'slug'   => $slug,
            'label'  => sanitize_text_field($item['label'] ?? $definitions[$slug]['label']),
            'href'   => esc_url_raw($item['href'] ?? ''),
            'active' => !empty($item['active']),
        ];
    }

    foreach (array_keys($definitions) as $slug) {
        if (!isset($seen[$slug])) {
            $platforms[] = em_wp_stream_default_platform_item($slug);
        }
    }

    return $platforms;
}

/**
 * @return array{slug:string,hidden:bool}
 */
function em_wp_top_bar_default_stream_icon_item(string $slug): array
{
    return [
        'slug'   => sanitize_key($slug),
        'hidden' => false,
    ];
}

/**
 * @param mixed $icons
 */
function em_wp_top_bar_stream_icons_is_list_format($icons): bool
{
    if (!is_array($icons) || $icons === []) {
        return false;
    }

    $first = reset($icons);

    return is_array($first) && array_key_exists('slug', $first);
}

/**
 * @param array<string, mixed> $item
 * @return array{slug:string,hidden:bool}
 */
function em_wp_top_bar_normalize_stream_icon_item(array $item): array
{
    $definitions = em_wp_stream_platform_definitions();
    $slug = sanitize_key((string) ($item['slug'] ?? ''));
    if ($slug === '' || !isset($definitions[$slug])) {
        $slug = (string) array_key_first($definitions);
    }

    return [
        'slug'   => $slug,
        'hidden' => !empty($item['hidden']),
    ];
}

/**
 * Liste ordonnée des icônes stream TOP-BAR (ordre + visibilité uniquement).
 *
 * @param array<string, mixed>|null $top_bar_options
 * @return array<int, array{slug:string,hidden:bool}>
 */
function em_wp_top_bar_get_stream_icons_list(?array $top_bar_options = null): array
{
    if ($top_bar_options === null) {
        $top_bar_options = function_exists('em_wp_top_bar_get_options')
            ? em_wp_top_bar_get_options()
            : [];
    }

    $raw = $top_bar_options['stream_icons'] ?? null;
    $definitions = em_wp_stream_platform_definitions();
    $icons = [];
    $seen = [];

    if (em_wp_top_bar_stream_icons_is_list_format($raw)) {
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalized = em_wp_top_bar_normalize_stream_icon_item($item);
            $slug = $normalized['slug'];
            if (isset($seen[$slug])) {
                continue;
            }

            $seen[$slug] = true;
            $icons[] = $normalized;
        }
    }

    foreach (array_keys($definitions) as $slug) {
        if (!isset($seen[$slug])) {
            $icons[] = em_wp_top_bar_default_stream_icon_item($slug);
        }
    }

    return $icons;
}

/**
 * @param mixed $raw
 * @return array<int, array{slug:string,hidden:bool}>
 */
function em_wp_top_bar_sanitize_stream_icons_from_input($raw): array
{
    if (!is_array($raw)) {
        return em_wp_top_bar_get_stream_icons_list(['stream_icons' => []]);
    }

    $definitions = em_wp_stream_platform_definitions();
    $icons = [];
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
        $icons[] = [
            'slug'   => $slug,
            'hidden' => !empty($item['hidden']),
        ];
    }

    foreach (array_keys($definitions) as $slug) {
        if (!isset($seen[$slug])) {
            $icons[] = em_wp_top_bar_default_stream_icon_item($slug);
        }
    }

    return $icons;
}

/**
 * Construit une entrée plateforme enrichie pour le front.
 *
 * @param array{slug:string,label:string,href:string,active:bool} $link
 * @return array<string, mixed>|null
 */
function em_wp_stream_build_front_platform_item(array $link): ?array
{
    $definitions = em_wp_stream_platform_definitions();
    $slug = sanitize_key((string) ($link['slug'] ?? ''));
    $href = trim((string) ($link['href'] ?? ''));
    $label = trim((string) ($link['label'] ?? ''));

    if ($slug === '' || !isset($definitions[$slug]) || empty($link['active']) || $href === '') {
        return null;
    }

    $definition = $definitions[$slug];
    if ($label === '') {
        $label = (string) ($definition['label'] ?? $slug);
    }

    $platform_type = em_wp_stream_detect_stream_platform_key($slug, $href);
    $embed_src = em_wp_stream_build_stream_embed_src($platform_type, $href);

    return [
        'slug'          => $slug,
        'key'           => $slug,
        'label'         => $label,
        'href'          => $href,
        'icon'          => (string) ($definition['icon'] ?? 'fa-link'),
        'color'         => (string) ($definition['color'] ?? '#410b49'),
        'embed_src'     => $embed_src,
        'has_player'    => $embed_src !== '',
        'player_height' => em_wp_stream_player_height($platform_type, $embed_src),
    ];
}

/**
 * Migre l'ancien format TOP-BAR stream_links vers STREAM platforms + TOP-BAR stream_icons.
 */
function em_wp_stream_maybe_migrate_legacy_platform_data(): void
{
    if (get_option('em_wp_stream_platforms_migrated_v2')) {
        return;
    }

    $stream_saved = get_option('em_wp_stream_options', []);
    $top_bar_saved = get_option('em_wp_top_bar_options', []);

    if (!is_array($stream_saved)) {
        $stream_saved = [];
    }
    if (!is_array($top_bar_saved)) {
        $top_bar_saved = [];
    }

    $legacy_links = $top_bar_saved['stream_links'] ?? null;
    $has_platforms = em_wp_stream_platforms_is_list_format($stream_saved['platforms'] ?? null);
    $has_icons = em_wp_top_bar_stream_icons_is_list_format($top_bar_saved['stream_icons'] ?? null);

    if (!$has_platforms && is_array($legacy_links) && $legacy_links !== []) {
        $first = reset($legacy_links);
        $is_list = is_array($first) && array_key_exists('slug', $first);
        $definitions = em_wp_stream_platform_definitions();
        $platforms = [];

        if ($is_list) {
            foreach ($legacy_links as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $platforms[] = em_wp_stream_normalize_platform_item($item);
            }
        } else {
            foreach (array_keys($definitions) as $slug) {
                $source = is_array($legacy_links[$slug] ?? null) ? $legacy_links[$slug] : [];
                $platforms[] = em_wp_stream_normalize_platform_item(array_merge(['slug' => $slug], $source));
            }
        }

        $stream_saved['platforms'] = $platforms;
        update_option('em_wp_stream_options', wp_parse_args($stream_saved, em_wp_stream_default_options()));
    }

    if (!$has_icons && is_array($legacy_links) && $legacy_links !== []) {
        $icons = [];
        $first = reset($legacy_links);
        $is_list = is_array($first) && array_key_exists('slug', $first);

        if ($is_list) {
            foreach ($legacy_links as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $slug = sanitize_key((string) ($item['slug'] ?? ''));
                if ($slug === '') {
                    continue;
                }
                $icons[] = [
                    'slug'   => $slug,
                    'hidden' => empty($item['active']),
                ];
            }
        } else {
            foreach ($legacy_links as $slug => $item) {
                if (!is_array($item)) {
                    continue;
                }
                $icons[] = [
                    'slug'   => sanitize_key((string) $slug),
                    'hidden' => empty($item['active']),
                ];
            }
        }

        $all_hidden = $icons !== [] && !array_filter($icons, static function (array $icon): bool {
            return empty($icon['hidden']);
        });
        $top_bar_saved['stream_icons_hidden'] = $all_hidden;
        unset($top_bar_saved['stream_links']);
        update_option('em_wp_top_bar_options', $top_bar_saved);
    }

    update_option('em_wp_stream_platforms_migrated_v2', 1);
}
add_action('init', 'em_wp_stream_maybe_migrate_legacy_platform_data', 1);
add_action('admin_init', 'em_wp_stream_maybe_migrate_legacy_platform_data', 1);

/**
 * Migre l'ancienne liste stream_icons TOP-BAR vers stream_icons_hidden (section).
 */
function em_wp_top_bar_maybe_migrate_stream_icons_section(): void
{
    if (get_option('em_wp_top_bar_stream_icons_v3')) {
        return;
    }

    $saved = get_option('em_wp_top_bar_options', []);
    if (!is_array($saved)) {
        update_option('em_wp_top_bar_stream_icons_v3', 1);
        return;
    }

    if (!array_key_exists('stream_icons_hidden', $saved) && isset($saved['stream_icons']) && is_array($saved['stream_icons'])) {
        $icons = em_wp_top_bar_get_stream_icons_list($saved);
        $all_hidden = $icons !== [] && !array_filter($icons, static function (array $icon): bool {
            return empty($icon['hidden']);
        });
        $saved['stream_icons_hidden'] = $all_hidden;
    }

    unset($saved['stream_icons']);

    if (function_exists('em_wp_top_bar_default_options')) {
        $saved = wp_parse_args($saved, em_wp_top_bar_default_options());
    }

    update_option('em_wp_top_bar_options', $saved);
    update_option('em_wp_top_bar_stream_icons_v3', 1);
}
add_action('init', 'em_wp_top_bar_maybe_migrate_stream_icons_section', 2);
add_action('admin_init', 'em_wp_top_bar_maybe_migrate_stream_icons_section', 2);
