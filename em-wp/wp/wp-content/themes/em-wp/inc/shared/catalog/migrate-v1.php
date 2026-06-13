<?php
/**
 * Migration V1 Hero/Slider → catalogues + rubrique HEADER (idempotent).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Flag option indiquant que la migration Phase 4 a été exécutée.
 */
function em_wp_catalog_migration_flag_option(): string
{
    return 'em_wp_catalog_v1_migrated';
}

/**
 * Migration idempotente catalogues + HEADER + ordre rubriques.
 */
function em_wp_catalog_maybe_migrate_v1(): void
{
    if (get_option(em_wp_catalog_migration_flag_option(), false)) {
        em_wp_catalog_maybe_migrate_rubrique_order();

        return;
    }

    em_wp_catalog_migrate_hero_entries();
    em_wp_catalog_migrate_slider_entries();
    em_wp_catalog_migrate_header_options();
    em_wp_catalog_maybe_migrate_rubrique_order();

    update_option(em_wp_catalog_migration_flag_option(), 1, false);
}

/**
 * Copie les contenus hero V1 vers le catalogue V2.
 */
function em_wp_catalog_migrate_hero_entries(): void
{
    $catalog = em_wp_hero_catalog_default_entries();
    $map = em_wp_hero_v1_slug_map();

    foreach ($map as $v1_slug => $catalog_slug) {
        if (!isset($catalog[$catalog_slug])) {
            continue;
        }

        $legacy_name = 'em_wp_hero_' . $v1_slug . '_options';
        $target_name = em_wp_hero_catalog_item_option_name($catalog_slug);

        if (get_option($target_name, null) === null) {
            $legacy = get_option($legacy_name, null);
            if ($legacy === null && $v1_slug === 'mayami') {
                $legacy = get_option('em_wp_hero_options', null);
            }

            if (is_array($legacy) && $legacy !== []) {
                update_option($target_name, $legacy, false);
            }
        }
    }

    update_option(em_wp_hero_catalog_option_name(), $catalog, false);
}

/**
 * Copie les contenus slider V1 vers le catalogue V2.
 */
function em_wp_catalog_migrate_slider_entries(): void
{
    $catalog = em_wp_slider_catalog_default_entries();
    $map = em_wp_slider_v1_slug_map();

    foreach ($map as $v1_slug => $catalog_slug) {
        if (!isset($catalog[$catalog_slug])) {
            continue;
        }

        $legacy_name = 'em_wp_slider_' . $v1_slug . '_options';
        $target_name = em_wp_slider_catalog_item_option_name($catalog_slug);

        if (get_option($target_name, null) === null) {
            $legacy = get_option($legacy_name, null);
            if (is_array($legacy) && $legacy !== []) {
                update_option($target_name, $legacy, false);
            }
        }
    }

    update_option(em_wp_slider_catalog_option_name(), $catalog, false);
}

/**
 * Crée les options HEADER par template depuis hero/slider actifs V1.
 */
function em_wp_catalog_migrate_header_options(): void
{
    if (!function_exists('em_wp_template_registry')) {
        return;
    }

    $hero_map = em_wp_hero_v1_slug_map();
    $slider_map = em_wp_slider_v1_slug_map();
    $active_hero = sanitize_key((string) get_option('em_wp_hero_active_style', 'mayami'));
    $active_slider = sanitize_key((string) get_option('em_wp_slider_active_style', 'mayami'));
    $default_hero = $hero_map[$active_hero] ?? 'hero-mayami-default';
    $default_slider = $slider_map[$active_slider] ?? 'slider-mayami-default';
    $layout = em_wp_catalog_detect_header_layout_from_order();

    foreach (em_wp_template_registry() as $template_slug => $definition) {
        $template_slug = sanitize_key((string) $template_slug);
        if ($template_slug === '') {
            continue;
        }

        $option_name = em_wp_template_option_name('header', $template_slug);

        if (get_option($option_name, null) !== null) {
            continue;
        }

        $hero_slug = $template_slug === em_wp_template_default_slug()
            ? $default_hero
            : ($hero_map[$template_slug] ?? 'hero-ellene-default');
        $slider_slug = $template_slug === em_wp_template_default_slug()
            ? $default_slider
            : ($slider_map[$template_slug] ?? 'slider-ellene-default');

        $enabled = true;

        if (function_exists('em_wp_hero_get_options')) {
            $hero_v1 = em_wp_hero_get_options($template_slug === em_wp_template_default_slug() ? 'mayami' : 'ellene');
            $enabled = !empty($hero_v1['enabled']);
        }

        update_option(
            $option_name,
            [
                'enabled'     => $enabled,
                'hero_slug'   => $hero_slug,
                'slider_slug' => $slider_slug,
                'layout'      => $layout,
            ],
            false
        );
    }
}

/**
 * Détecte layout HEADER depuis l'ordre hero/slider V1.
 */
function em_wp_catalog_detect_header_layout_from_order(): string
{
    $order = function_exists('em_wp_get_site_rubrique_order')
        ? em_wp_get_site_rubrique_order()
        : em_wp_site_rubrique_default_order();

    $hero_index = array_search('hero', $order, true);
    $slider_index = array_search('slider', $order, true);

    if ($hero_index === false || $slider_index === false) {
        return 'hero_left';
    }

    return $slider_index < $hero_index ? 'slider_left' : 'hero_left';
}

/**
 * Remplace hero + slider par header dans l'ordre des rubriques.
 */
function em_wp_catalog_maybe_migrate_rubrique_order(): void
{
    $order = get_option(em_wp_site_rubrique_order_option_name(), []);

    if (!is_array($order) || $order === []) {
        $order = em_wp_site_rubrique_default_order();
    }

    if (in_array('header', $order, true)) {
        return;
    }

    $filtered = array_values(array_filter(
        $order,
        static fn(string $slug): bool => !in_array($slug, ['hero', 'slider'], true)
    ));

    $top_index = array_search('top-bar', $filtered, true);
    $insert_at = $top_index !== false ? (int) $top_index + 1 : 0;

    array_splice($filtered, $insert_at, 0, 'header');

    update_option(em_wp_site_rubrique_order_option_name(), $filtered, false);
}
