<?php
/**
 * Plateformes sociales (SOCIAL).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, array{label:string,icon:string,default_account:string}>
 */
function em_wp_social_platform_definitions(): array
{
    return [
        'tiktok' => [
            'label'           => 'TikTok',
            'icon'            => 'fa-tiktok',
            'default_account' => '@ellenemasri',
        ],
        'instagram' => [
            'label'           => 'Instagram',
            'icon'            => 'fa-instagram',
            'default_account' => '@ellenemasri',
        ],
        'youtube' => [
            'label'           => 'YouTube',
            'icon'            => 'fa-youtube',
            'default_account' => '@ELLENEMASRI',
        ],
    ];
}

/**
 * @return array{slug:string,link:string,label:string,badge:string,account:string,active:bool}
 */
function em_wp_social_default_platform_item(string $slug): array
{
    $definitions = em_wp_social_platform_definitions();
    $platform = $definitions[$slug] ?? null;

    return [
        'slug'    => sanitize_key($slug),
        'link'    => '',
        'label'   => is_array($platform) ? (string) ($platform['label'] ?? $slug) : $slug,
        'badge'   => $slug === 'youtube' ? __('Watch', 'em-wp') : __('Follow', 'em-wp'),
        'account' => is_array($platform) ? (string) ($platform['default_account'] ?? '') : '',
        'active'  => true,
    ];
}

/**
 * @param mixed $raw
 * @return array<int, array{slug:string,link:string,label:string,badge:string,account:string,active:bool}>
 */
function em_wp_social_sanitize_platforms_from_input($raw): array
{
    $definitions = em_wp_social_platform_definitions();
    $platforms = [];
    $seen = [];

    if (is_array($raw)) {
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }

            $slug = sanitize_key((string) ($item['slug'] ?? ''));
            if ($slug === '' || !isset($definitions[$slug]) || isset($seen[$slug])) {
                continue;
            }

            $defaults = em_wp_social_default_platform_item($slug);
            $seen[$slug] = true;
            $platforms[] = [
                'slug'    => $slug,
                'link'    => esc_url_raw((string) ($item['link'] ?? '')),
                'label'   => sanitize_text_field((string) ($item['label'] ?? $defaults['label'])),
                'badge'   => sanitize_text_field((string) ($item['badge'] ?? $defaults['badge'])),
                'account' => sanitize_text_field((string) ($item['account'] ?? $defaults['account'])),
                'active'  => !empty($item['active']),
            ];
        }
    }

    foreach (array_keys($definitions) as $slug) {
        if (!isset($seen[$slug])) {
            $platforms[] = em_wp_social_default_platform_item($slug);
        }
    }

    return $platforms;
}

/**
 * @param array<string, mixed>|null $social_options
 * @return array<int, array{slug:string,link:string,label:string,badge:string,account:string,active:bool}>
 */
function em_wp_social_get_platforms_list(?array $social_options = null): array
{
    if ($social_options === null) {
        if (!is_admin() && function_exists('em_wp_social_get_options_for_front')) {
            $social_options = em_wp_social_get_options_for_front();
        } elseif (function_exists('em_wp_social_get_options')) {
            $social_options = em_wp_social_get_options();
        } else {
            $social_options = em_wp_social_catalog_default_options();
        }
    }

    $raw = $social_options['platforms'] ?? [];
    $definitions = em_wp_social_platform_definitions();
    $platforms = [];
    $seen = [];

    if (is_array($raw)) {
        $first = reset($raw);
        $is_list = is_array($first) && array_key_exists('slug', $first);

        if ($is_list) {
            foreach ($raw as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $slug = sanitize_key((string) ($item['slug'] ?? ''));
                if ($slug === '' || !isset($definitions[$slug]) || isset($seen[$slug])) {
                    continue;
                }

                $defaults = em_wp_social_default_platform_item($slug);
                $seen[$slug] = true;
                $platforms[] = [
                    'slug'    => $slug,
                    'link'    => (string) ($item['link'] ?? ''),
                    'label'   => (string) ($item['label'] ?? $defaults['label']),
                    'badge'   => (string) ($item['badge'] ?? $defaults['badge']),
                    'account' => (string) ($item['account'] ?? $defaults['account']),
                    'active'  => !empty($item['active']),
                ];
            }
        }
    }

    foreach (array_keys($definitions) as $slug) {
        if (!isset($seen[$slug])) {
            $platforms[] = em_wp_social_default_platform_item($slug);
        }
    }

    return $platforms;
}

/**
 * Cartes actives pour le front.
 *
 * @return array<int, array{slug:string,link:string,label:string,badge:string,account:string,icon:string}>
 */
function em_wp_get_social_cards_for_front(): array
{
    $options = function_exists('em_wp_social_get_options_for_front')
        ? em_wp_social_get_options_for_front()
        : (function_exists('em_wp_social_get_options') ? em_wp_social_get_options() : em_wp_social_catalog_default_options());
    $definitions = em_wp_social_platform_definitions();
    $cards = [];

    foreach (em_wp_social_get_platforms_list($options) as $platform) {
        if (empty($platform['active'])) {
            continue;
        }

        $link = trim((string) ($platform['link'] ?? ''));
        $label = trim((string) ($platform['label'] ?? ''));
        if ($link === '' || $label === '') {
            continue;
        }

        $slug = (string) ($platform['slug'] ?? '');
        $definition = $definitions[$slug] ?? [];

        $cards[] = [
            'slug'    => $slug,
            'link'    => $link,
            'label'   => $label,
            'badge'   => trim((string) ($platform['badge'] ?? '')),
            'account' => trim((string) ($platform['account'] ?? '')),
            'icon'    => (string) ($definition['icon'] ?? 'fa-link'),
        ];
    }

    return $cards;
}
