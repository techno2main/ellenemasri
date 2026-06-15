<?php
/**
 * Overrides des modules catalogue intégrés (libellé, position, icône).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_catalog_module_overrides_option_name(): string
{
    return 'em_wp_catalog_module_overrides';
}

/**
 * @return array<string, array{label?:string,description_item?:string,menu_position_after?:string,icon?:string}>
 */
function em_wp_catalog_module_overrides(): array
{
    $saved = get_option(em_wp_catalog_module_overrides_option_name(), []);

    if (!is_array($saved)) {
        return [];
    }

    $overrides = [];

    foreach ($saved as $slug => $entry) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || !is_array($entry)) {
            continue;
        }

        $overrides[$slug] = [
            'label'                => sanitize_text_field((string) ($entry['label'] ?? '')),
            'description_item'     => sanitize_text_field((string) ($entry['description_item'] ?? '')),
            'menu_position_after'  => sanitize_key((string) ($entry['menu_position_after'] ?? '')),
            'icon'                 => sanitize_key((string) ($entry['icon'] ?? '')),
        ];
    }

    return $overrides;
}

/**
 * @return array{label?:string,description_item?:string,menu_position_after?:string,icon?:string}|null
 */
function em_wp_catalog_module_override(string $module_slug): ?array
{
    $module_slug = sanitize_key($module_slug);
    $override = em_wp_catalog_module_overrides()[$module_slug] ?? null;

    return is_array($override) ? $override : null;
}

function em_wp_catalog_default_menu_position_after(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);
    $builtin = em_wp_admin_catalog_menu_modules_builtin();
    $index = array_search($module_slug, $builtin, true);

    if ($index === false) {
        return '__end__';
    }

    if ($index === 0) {
        return '__start__';
    }

    return (string) $builtin[$index - 1];
}

function em_wp_catalog_module_menu_position_after(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return '__end__';
    }

    if (function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
        $module = em_wp_custom_catalog_module($module_slug);

        return sanitize_key((string) ($module['menu_position_after'] ?? '__end__')) ?: '__end__';
    }

    $override = em_wp_catalog_module_override($module_slug);
    $stored = sanitize_key((string) ($override['menu_position_after'] ?? ''));

    if ($stored !== '') {
        return $stored;
    }

    return em_wp_catalog_default_menu_position_after($module_slug);
}

function em_wp_catalog_guess_module_icon_from_label(string $label, string $module_slug = ''): string
{
    $label = mb_strtolower(trim($label));
    $module_slug = sanitize_key($module_slug);
    $haystack = $label . ' ' . str_replace('-', ' ', $module_slug);

    if (str_contains($haystack, 'contact')) {
        return 'dashicons-email-alt';
    }

    if (str_contains($haystack, 'footer')) {
        return 'dashicons-table-row-after';
    }

    return 'dashicons-admin-generic';
}

function em_wp_catalog_resolve_module_icon(string $module_slug, string $fallback = 'dashicons-admin-generic'): string
{
    $module_slug = sanitize_key($module_slug);
    $fallback = sanitize_key($fallback) ?: 'dashicons-admin-generic';

    if (function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
        $module = em_wp_custom_catalog_module($module_slug);
        $icon = sanitize_key((string) ($module['icon'] ?? ''));

        if ($icon !== '' && $icon !== 'dashicons-admin-generic') {
            return $icon;
        }

        return em_wp_catalog_guess_module_icon_from_label(
            (string) ($module['label'] ?? ''),
            $module_slug
        );
    }

    $override = em_wp_catalog_module_override($module_slug);
    $icon = sanitize_key((string) ($override['icon'] ?? ''));

    if ($icon !== '' && $icon !== 'dashicons-admin-generic') {
        return $icon;
    }

    if ($module_slug === 'footers') {
        return 'dashicons-table-row-after';
    }

    return em_wp_catalog_guess_module_icon_from_label('', $module_slug) !== 'dashicons-admin-generic'
        ? em_wp_catalog_guess_module_icon_from_label('', $module_slug)
        : $fallback;
}

/**
 * @param string[] $base_modules
 * @return string[]
 */
function em_wp_catalog_apply_menu_order(array $base_modules): array
{
    $custom_slugs = function_exists('em_wp_custom_catalog_modules')
        ? array_keys(em_wp_custom_catalog_modules())
        : [];
    $all_slugs = array_values(array_unique(array_merge($base_modules, $custom_slugs)));

    if ($all_slugs === []) {
        return [];
    }

    $placed = [];

    foreach ($all_slugs as $module_slug) {
        if (em_wp_catalog_module_menu_position_after($module_slug) === '__start__') {
            $placed[] = $module_slug;
        }
    }

    $guard = count($all_slugs) * count($all_slugs);

    while (count($placed) < count($all_slugs) && $guard > 0) {
        $guard--;
        $progress = false;

        foreach ($all_slugs as $module_slug) {
            if (in_array($module_slug, $placed, true)) {
                continue;
            }

            $anchor = em_wp_catalog_module_menu_position_after($module_slug);

            if ($anchor === '__end__' || $anchor === '__start__') {
                continue;
            }

            $anchor_index = array_search($anchor, $placed, true);

            if ($anchor_index === false) {
                continue;
            }

            array_splice($placed, $anchor_index + 1, 0, [$module_slug]);
            $progress = true;
            break;
        }

        if (!$progress) {
            break;
        }
    }

    foreach ($all_slugs as $module_slug) {
        if (!in_array($module_slug, $placed, true)) {
            $placed[] = $module_slug;
        }
    }

    return array_values(array_unique($placed));
}

/**
 * @return array{label:string,menu_position_after:string}|null
 */
function em_wp_catalog_get_module_edit_settings(string $module_slug): ?array
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return null;
    }

    if (function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
        $module = em_wp_custom_catalog_module($module_slug);

        if ($module === null) {
            return null;
        }

        return [
            'label'                => (string) ($module['label'] ?? ''),
            'menu_position_after'  => em_wp_catalog_module_menu_position_after($module_slug),
        ];
    }

    if (!in_array($module_slug, em_wp_admin_catalog_menu_modules_builtin(), true)) {
        return null;
    }

    if (!function_exists('em_wp_catalog_menu_definitions')) {
        return null;
    }

    $definition = em_wp_catalog_menu_definitions()[$module_slug] ?? null;

    if (!is_array($definition)) {
        return null;
    }

    $override = em_wp_catalog_module_override($module_slug);
    $label = trim((string) ($override['label'] ?? ''));

    if ($label === '') {
        $label = trim((string) ($definition['description_item'] ?? $definition['label'] ?? $module_slug));
    }

    return [
        'label'                => $label,
        'menu_position_after'  => em_wp_catalog_module_menu_position_after($module_slug),
    ];
}

/**
 * @return string|WP_Error
 */
function em_wp_catalog_update_builtin_module(string $module_slug, string $label, string $menu_position_after = '__end__')
{
    $module_slug = sanitize_key($module_slug);
    $label = sanitize_text_field($label);

    if (!in_array($module_slug, em_wp_admin_catalog_menu_modules_builtin(), true)) {
        return new WP_Error('em_wp_catalog_module_not_found', __('Catalogue introuvable.', 'em-wp'));
    }

    if ($label === '') {
        return new WP_Error('em_wp_catalog_empty_label', __('Le nom du catalogue est obligatoire.', 'em-wp'));
    }

    $menu_position_after = sanitize_key($menu_position_after);
    $allowed_anchors = array_keys(em_wp_catalog_menu_position_options($module_slug));

    if (!in_array($menu_position_after, $allowed_anchors, true)) {
        $menu_position_after = em_wp_catalog_default_menu_position_after($module_slug);
    }

    $overrides = em_wp_catalog_module_overrides();
    $current = $overrides[$module_slug] ?? [];

    $overrides[$module_slug] = [
        'label'                => $label,
        'description_item'     => $label,
        'menu_position_after'  => $menu_position_after,
        'icon'                 => sanitize_key((string) ($current['icon'] ?? '')),
    ];

    if (!update_option(em_wp_catalog_module_overrides_option_name(), $overrides, false)) {
        return new WP_Error('em_wp_catalog_persist_failed', __('Impossible d\'enregistrer le catalogue.', 'em-wp'));
    }

    return $module_slug;
}

/**
 * @return string|WP_Error
 */
function em_wp_catalog_update_module_settings(string $module_slug, string $label, string $menu_position_after = '__end__')
{
    $module_slug = sanitize_key($module_slug);

    if (function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
        return em_wp_custom_catalog_update_module($module_slug, $label, $menu_position_after);
    }

    return em_wp_catalog_update_builtin_module($module_slug, $label, $menu_position_after);
}

/**
 * Applique label / icône / description sur les définitions menu.
 *
 * @param array<string, array<string, mixed>> $definitions
 * @return array<string, array<string, mixed>>
 */
function em_wp_catalog_apply_module_definition_overrides(array $definitions): array
{
    foreach ($definitions as $module_slug => $definition) {
        if (!is_array($definition)) {
            continue;
        }

        $module_slug = sanitize_key((string) $module_slug);

        if ($module_slug === '') {
            continue;
        }

        if (!function_exists('em_wp_custom_catalog_is_module') || !em_wp_custom_catalog_is_module($module_slug)) {
            $override = em_wp_catalog_module_override($module_slug);

            if (is_array($override) && trim((string) ($override['label'] ?? '')) !== '') {
                $label = trim((string) $override['label']);
                $definitions[$module_slug]['label'] = mb_strtoupper($label);
                $definitions[$module_slug]['menu_title'] = mb_strtoupper($label);
                $definitions[$module_slug]['description_item'] = trim((string) ($override['description_item'] ?? $label));
            }
        }

        $definitions[$module_slug]['icon'] = em_wp_catalog_resolve_module_icon(
            $module_slug,
            (string) ($definition['icon'] ?? 'dashicons-admin-generic')
        );
    }

    return $definitions;
}
