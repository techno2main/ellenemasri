<?php
/**
 * Registre des modules catalogue personnalisés.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_custom_catalog_modules_option_name(): string
{
    return 'em_wp_custom_catalog_modules';
}

/**
 * Modules catalogue intégrés (ordre par défaut).
 *
 * @return string[]
 */
function em_wp_admin_catalog_menu_modules_builtin(): array
{
    return ['top-bars', 'heros', 'sliders', 'streams', 'socials', 'videos', 'releases', 'ctas', 'footers'];
}

/**
 * @return array<string, array{
 *     label:string,
 *     menu_position_after:string,
 *     hub_menu_slug:string,
 *     icon:string,
 *     description_item:string,
 *     description_rubrique:string
 * }>
 */
function em_wp_custom_catalog_modules(): array
{
    $saved = get_option(em_wp_custom_catalog_modules_option_name(), []);

    if (!is_array($saved)) {
        return [];
    }

    $modules = [];

    foreach ($saved as $slug => $module) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || !is_array($module)) {
            continue;
        }

        $label = trim(sanitize_text_field((string) ($module['label'] ?? '')));

        if ($label === '') {
            continue;
        }

        $modules[$slug] = [
            'label'                => $label,
            'menu_position_after'  => sanitize_key((string) ($module['menu_position_after'] ?? '__end__')),
            'hub_menu_slug'        => em_wp_custom_catalog_hub_menu_slug($slug),
            'icon'                 => sanitize_key((string) ($module['icon'] ?? 'dashicons-admin-generic')) ?: 'dashicons-admin-generic',
            'description_item'     => sanitize_text_field((string) ($module['description_item'] ?? $label)),
            'description_rubrique' => sanitize_text_field((string) ($module['description_rubrique'] ?? '')),
        ];
    }

    return $modules;
}

function em_wp_custom_catalog_is_module(string $module_slug): bool
{
    $module_slug = sanitize_key($module_slug);

    return $module_slug !== '' && isset(em_wp_custom_catalog_modules()[$module_slug]);
}

/**
 * @return array<string, array{
 *     label:string,
 *     menu_position_after:string,
 *     hub_menu_slug:string,
 *     icon:string,
 *     description_item:string,
 *     description_rubrique:string
 * }>|null
 */
function em_wp_custom_catalog_module(string $module_slug): ?array
{
    $module_slug = sanitize_key($module_slug);
    $modules = em_wp_custom_catalog_modules();

    return $modules[$module_slug] ?? null;
}

function em_wp_custom_catalog_hub_menu_slug(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return '';
    }

    return 'em-wp-cc-' . $module_slug;
}

/**
 * @return string[]
 */
function em_wp_custom_catalog_hub_menu_slugs(): array
{
    return array_values(array_filter(array_map(
        static fn (array $module): string => sanitize_key((string) ($module['hub_menu_slug'] ?? '')),
        em_wp_custom_catalog_modules()
    )));
}

function em_wp_custom_catalog_module_slug_from_hub(string $hub_menu_slug): string
{
    $hub_menu_slug = sanitize_key($hub_menu_slug);

    foreach (em_wp_custom_catalog_modules() as $module_slug => $module) {
        if (sanitize_key((string) ($module['hub_menu_slug'] ?? '')) === $hub_menu_slug) {
            return (string) $module_slug;
        }
    }

    return '';
}

function em_wp_custom_catalog_module_slug_from_label(string $label): string
{
    $base = sanitize_title($label);

    if ($base === '') {
        $base = 'catalog';
    }

    if (!str_starts_with($base, 'custom-')) {
        $base = 'custom-' . $base;
    }

    return sanitize_key($base);
}

function em_wp_custom_catalog_unique_module_slug(string $base_slug): string
{
    $base_slug = sanitize_key($base_slug);
    $builtin = em_wp_admin_catalog_menu_modules_builtin();
    $modules = em_wp_custom_catalog_modules();

    if ($base_slug === '') {
        $base_slug = 'custom-catalog';
    }

    $slug = $base_slug;
    $suffix = 2;

    while (in_array($slug, $builtin, true) || isset($modules[$slug])) {
        $slug = sanitize_key($base_slug . '-' . $suffix);
        $suffix++;
    }

    return $slug;
}

/**
 * Insère les modules personnalisés dans l'ordre menu catalogue.
 *
 * @param string[] $base_modules
 * @return string[]
 */
function em_wp_custom_catalog_apply_menu_order(array $base_modules): array
{
    if (function_exists('em_wp_catalog_apply_menu_order')) {
        return em_wp_catalog_apply_menu_order($base_modules);
    }

    $custom_modules = em_wp_custom_catalog_modules();

    if ($custom_modules === []) {
        return $base_modules;
    }

    $by_anchor = [
        '__start__' => [],
        '__end__'   => [],
    ];

    foreach ($base_modules as $base_slug) {
        $by_anchor[$base_slug] = [];
    }

    foreach ($custom_modules as $module_slug => $module) {
        $anchor = sanitize_key((string) ($module['menu_position_after'] ?? '__end__'));

        if (!isset($by_anchor[$anchor])) {
            $anchor = '__end__';
        }

        $by_anchor[$anchor][] = $module_slug;
    }

    $ordered = [];

    foreach ($by_anchor['__start__'] as $module_slug) {
        $ordered[] = $module_slug;
    }

    foreach ($base_modules as $base_slug) {
        $ordered[] = $base_slug;

        foreach ($by_anchor[$base_slug] ?? [] as $module_slug) {
            $ordered[] = $module_slug;
        }
    }

    foreach ($by_anchor['__end__'] as $module_slug) {
        $ordered[] = $module_slug;
    }

    return array_values(array_unique($ordered));
}

/**
 * Options du sélecteur « position dans le menu ».
 *
 * @return array<string, string>
 */
function em_wp_catalog_menu_position_options(string $except_module_slug = ''): array
{
    $except_module_slug = sanitize_key($except_module_slug);
    $options = [
        '__start__' => __('Au début', 'em-wp'),
    ];

    foreach (em_wp_admin_catalog_menu_modules_builtin() as $module_slug) {
        if ($module_slug === $except_module_slug) {
            continue;
        }

        if (!function_exists('em_wp_catalog_module_label')) {
            $options[$module_slug] = strtoupper(str_replace('-', ' ', $module_slug));
            continue;
        }

        $label = em_wp_catalog_module_label($module_slug);
        $options[$module_slug] = $label !== ''
            ? sprintf(
                /* translators: %s: catalogue module label */
                __('Après %s', 'em-wp'),
                $label
            )
            : strtoupper(str_replace('-', ' ', $module_slug));
    }

    if (function_exists('em_wp_custom_catalog_modules')) {
        foreach (array_keys(em_wp_custom_catalog_modules()) as $module_slug) {
            if ($module_slug === $except_module_slug) {
                continue;
            }

            $label = function_exists('em_wp_catalog_module_label')
                ? em_wp_catalog_module_label($module_slug)
                : strtoupper(str_replace('-', ' ', $module_slug));

            $options[$module_slug] = $label !== ''
                ? sprintf(
                    /* translators: %s: catalogue module label */
                    __('Après %s', 'em-wp'),
                    $label
                )
                : strtoupper(str_replace('-', ' ', $module_slug));
        }
    }

    $options['__end__'] = __('À la fin', 'em-wp');

    return $options;
}

/**
 * @deprecated Utiliser em_wp_catalog_menu_position_options().
 * @return array<string, string>
 */
function em_wp_custom_catalog_menu_position_options(): array
{
    return em_wp_catalog_menu_position_options();
}
