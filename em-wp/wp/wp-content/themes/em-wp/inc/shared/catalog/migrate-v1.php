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
        em_wp_catalog_maybe_migrate_video_stream_social_entries();
        em_wp_catalog_maybe_migrate_video_stream_social_rubrique_slugs();
        em_wp_catalog_maybe_migrate_top_bar_release_cta_footer_entries();
        em_wp_catalog_maybe_migrate_top_bar_release_cta_footer_rubrique_slugs();

        return;
    }

    em_wp_catalog_migrate_hero_entries();
    em_wp_catalog_migrate_slider_entries();
    em_wp_catalog_migrate_video_entries();
    em_wp_catalog_migrate_stream_entries();
    em_wp_catalog_migrate_social_entries();
    em_wp_catalog_migrate_top_bar_entries();
    em_wp_catalog_migrate_release_entries();
    em_wp_catalog_migrate_cta_entries();
    em_wp_catalog_migrate_footer_entries();
    em_wp_catalog_migrate_header_options();
    em_wp_catalog_migrate_video_rubrique_options();
    em_wp_catalog_migrate_stream_rubrique_options();
    em_wp_catalog_migrate_social_rubrique_options();
    em_wp_catalog_migrate_top_bar_rubrique_options();
    em_wp_catalog_migrate_release_rubrique_options();
    em_wp_catalog_migrate_cta_rubrique_options();
    em_wp_catalog_migrate_footer_rubrique_options();
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
 * Copie les contenus video template → catalogue V2.
 */
function em_wp_catalog_migrate_video_entries(): void
{
    $catalog = em_wp_video_catalog_default_entries();
    $map = em_wp_video_v1_slug_map();

    foreach ($map as $template_slug => $catalog_slug) {
        if (!isset($catalog[$catalog_slug])) {
            continue;
        }

        $target_name = em_wp_video_catalog_item_option_name($catalog_slug);

        if (get_option($target_name, null) !== null) {
            continue;
        }

        if (!function_exists('em_wp_get_template_rubrique_options')) {
            continue;
        }

        $legacy = em_wp_get_template_rubrique_options('video', $template_slug);

        if (is_array($legacy) && $legacy !== []) {
            update_option($target_name, $legacy, false);
        }
    }

    if (get_option(em_wp_video_catalog_option_name(), null) === null) {
        update_option(em_wp_video_catalog_option_name(), $catalog, false);
    }
}

/**
 * Copie les contenus stream template → catalogue V2.
 */
function em_wp_catalog_migrate_stream_entries(): void
{
    $catalog = em_wp_stream_catalog_default_entries();
    $map = em_wp_stream_v1_slug_map();

    foreach ($map as $template_slug => $catalog_slug) {
        if (!isset($catalog[$catalog_slug])) {
            continue;
        }

        $target_name = em_wp_stream_catalog_item_option_name($catalog_slug);

        if (get_option($target_name, null) !== null) {
            continue;
        }

        if (!function_exists('em_wp_get_template_rubrique_options')) {
            continue;
        }

        $legacy = em_wp_get_template_rubrique_options('stream', $template_slug);

        if (is_array($legacy) && $legacy !== []) {
            update_option($target_name, $legacy, false);
        }
    }

    if (get_option(em_wp_stream_catalog_option_name(), null) === null) {
        update_option(em_wp_stream_catalog_option_name(), $catalog, false);
    }
}

/**
 * Copie les contenus social template → catalogue V2.
 */
function em_wp_catalog_migrate_social_entries(): void
{
    $catalog = em_wp_social_catalog_default_entries();
    $map = em_wp_social_v1_slug_map();

    foreach ($map as $template_slug => $catalog_slug) {
        if (!isset($catalog[$catalog_slug])) {
            continue;
        }

        $target_name = em_wp_social_catalog_item_option_name($catalog_slug);

        if (get_option($target_name, null) !== null) {
            continue;
        }

        if (!function_exists('em_wp_get_template_rubrique_options')) {
            continue;
        }

        $legacy = em_wp_get_template_rubrique_options('social', $template_slug);

        if (is_array($legacy) && $legacy !== []) {
            update_option($target_name, $legacy, false);
        }
    }

    if (get_option(em_wp_social_catalog_option_name(), null) === null) {
        update_option(em_wp_social_catalog_option_name(), $catalog, false);
    }
}

/**
 * Migration idempotente video/stream/social pour sites déjà migrés Phase 4.
 */
function em_wp_catalog_maybe_migrate_video_stream_social_entries(): void
{
    $flag = 'em_wp_catalog_v2b_migrated';

    if (get_option($flag, false)) {
        return;
    }

    em_wp_catalog_migrate_video_entries();
    em_wp_catalog_migrate_stream_entries();
    em_wp_catalog_migrate_social_entries();

    update_option($flag, 1, false);
}

/**
 * Migration idempotente : options rubrique template → pointeurs catalogue (video/stream/social).
 */
function em_wp_catalog_maybe_migrate_video_stream_social_rubrique_slugs(): void
{
    $flag = 'em_wp_catalog_v2c_rubrique_slugs_migrated';

    if (get_option($flag, false)) {
        return;
    }

    em_wp_catalog_migrate_video_rubrique_options();
    em_wp_catalog_migrate_stream_rubrique_options();
    em_wp_catalog_migrate_social_rubrique_options();

    update_option($flag, 1, false);
}

/**
 * Réduit les options rubrique VIDEOS par template (slug catalogue + style).
 */
function em_wp_catalog_migrate_video_rubrique_options(): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_video_option_name')) {
        return;
    }

    $map = function_exists('em_wp_video_v1_slug_map') ? em_wp_video_v1_slug_map() : [];
    $legacy_keys = ['kicker', 'title', 'description', 'watch_label', 'watch_href', 'cover_image'];

    foreach (array_keys(em_wp_template_registry()) as $template_slug) {
        $template_slug = sanitize_key((string) $template_slug);

        if ($template_slug === '') {
            continue;
        }

        $saved = get_option(em_wp_video_option_name($template_slug), null);

        if (!is_array($saved)) {
            continue;
        }

        $has_legacy = false;

        foreach ($legacy_keys as $key) {
            if (array_key_exists($key, $saved)) {
                $has_legacy = true;
                break;
            }
        }

        if (!$has_legacy && array_key_exists('video_slug', $saved)) {
            continue;
        }

        $video_slug = sanitize_key((string) ($saved['video_slug'] ?? ''));

        if ($video_slug === '') {
            $video_slug = sanitize_key((string) ($map[$template_slug] ?? ''));
        }

        update_option(
            em_wp_video_option_name($template_slug),
            [
                'enabled'          => !empty($saved['enabled']),
                'video_slug'       => $video_slug,
                'background_color' => (string) ($saved['background_color'] ?? ''),
                'text_color'       => (string) ($saved['text_color'] ?? ''),
            ],
            false
        );
    }
}

/**
 * Réduit les options rubrique STREAM par template (slug catalogue + style).
 */
function em_wp_catalog_migrate_stream_rubrique_options(): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_stream_option_name')) {
        return;
    }

    $map = function_exists('em_wp_stream_v1_slug_map') ? em_wp_stream_v1_slug_map() : [];
    $legacy_keys = ['kicker', 'title_prefix', 'title_logo', 'availability_text', 'card_label', 'platforms'];

    foreach (array_keys(em_wp_template_registry()) as $template_slug) {
        $template_slug = sanitize_key((string) $template_slug);

        if ($template_slug === '') {
            continue;
        }

        $saved = get_option(em_wp_stream_option_name($template_slug), null);

        if (!is_array($saved)) {
            continue;
        }

        $has_legacy = false;

        foreach ($legacy_keys as $key) {
            if (array_key_exists($key, $saved)) {
                $has_legacy = true;
                break;
            }
        }

        if (!$has_legacy && array_key_exists('stream_slug', $saved)) {
            continue;
        }

        $stream_slug = sanitize_key((string) ($saved['stream_slug'] ?? ''));

        if ($stream_slug === '') {
            $stream_slug = sanitize_key((string) ($map[$template_slug] ?? ''));
        }

        update_option(
            em_wp_stream_option_name($template_slug),
            [
                'enabled'          => !empty($saved['enabled']),
                'stream_slug'      => $stream_slug,
                'background_color' => (string) ($saved['background_color'] ?? ''),
                'text_color'       => (string) ($saved['text_color'] ?? ''),
            ],
            false
        );
    }
}

/**
 * Réduit les options rubrique SOCIAL par template (slug catalogue + style).
 */
function em_wp_catalog_migrate_social_rubrique_options(): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_social_option_name')) {
        return;
    }

    $map = function_exists('em_wp_social_v1_slug_map') ? em_wp_social_v1_slug_map() : [];
    $legacy_keys = ['kicker', 'title_left', 'title_right', 'description', 'platforms'];

    foreach (array_keys(em_wp_template_registry()) as $template_slug) {
        $template_slug = sanitize_key((string) $template_slug);

        if ($template_slug === '') {
            continue;
        }

        $saved = get_option(em_wp_social_option_name($template_slug), null);

        if (!is_array($saved)) {
            continue;
        }

        $has_legacy = false;

        foreach ($legacy_keys as $key) {
            if (array_key_exists($key, $saved)) {
                $has_legacy = true;
                break;
            }
        }

        if (!$has_legacy && array_key_exists('social_slug', $saved)) {
            continue;
        }

        $social_slug = sanitize_key((string) ($saved['social_slug'] ?? ''));

        if ($social_slug === '') {
            $social_slug = sanitize_key((string) ($map[$template_slug] ?? ''));
        }

        update_option(
            em_wp_social_option_name($template_slug),
            [
                'enabled'          => !empty($saved['enabled']),
                'social_slug'      => $social_slug,
                'background_color' => (string) ($saved['background_color'] ?? ''),
                'text_color'       => (string) ($saved['text_color'] ?? ''),
            ],
            false
        );
    }
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

/**
 * Copie les contenus top-bar template → catalogue V2.
 */
function em_wp_catalog_migrate_top_bar_entries(): void
{
    $catalog = em_wp_top_bar_catalog_default_entries();
    $map = em_wp_top_bar_v1_slug_map();

    foreach ($map as $template_slug => $catalog_slug) {
        if (!isset($catalog[$catalog_slug])) {
            continue;
        }

        $target_name = em_wp_top_bar_catalog_item_option_name($catalog_slug);

        if (get_option($target_name, null) !== null) {
            continue;
        }

        if (!function_exists('em_wp_get_template_rubrique_options')) {
            continue;
        }

        $legacy = em_wp_get_template_rubrique_options('top-bar', $template_slug);

        if (is_array($legacy) && $legacy !== []) {
            update_option($target_name, $legacy, false);
        }
    }

    if (get_option(em_wp_top_bar_catalog_option_name(), null) === null) {
        update_option(em_wp_top_bar_catalog_option_name(), $catalog, false);
    }
}

/**
 * Copie les contenus release template → catalogue V2.
 */
function em_wp_catalog_migrate_release_entries(): void
{
    $catalog = em_wp_release_catalog_default_entries();
    $map = em_wp_release_v1_slug_map();

    foreach ($map as $template_slug => $catalog_slug) {
        if (!isset($catalog[$catalog_slug])) {
            continue;
        }

        $target_name = em_wp_release_catalog_item_option_name($catalog_slug);

        if (get_option($target_name, null) !== null) {
            continue;
        }

        if (!function_exists('em_wp_get_template_rubrique_options')) {
            continue;
        }

        $legacy = em_wp_get_template_rubrique_options('release', $template_slug);

        if (is_array($legacy) && $legacy !== []) {
            update_option($target_name, $legacy, false);
        }
    }

    if (get_option(em_wp_release_catalog_option_name(), null) === null) {
        update_option(em_wp_release_catalog_option_name(), $catalog, false);
    }
}

/**
 * Copie les contenus cta template → catalogue V2.
 */
function em_wp_catalog_migrate_cta_entries(): void
{
    $catalog = em_wp_cta_catalog_default_entries();
    $map = em_wp_cta_v1_slug_map();

    foreach ($map as $template_slug => $catalog_slug) {
        if (!isset($catalog[$catalog_slug])) {
            continue;
        }

        $target_name = em_wp_cta_catalog_item_option_name($catalog_slug);

        if (get_option($target_name, null) !== null) {
            continue;
        }

        if (!function_exists('em_wp_get_template_rubrique_options')) {
            continue;
        }

        $legacy = em_wp_get_template_rubrique_options('cta', $template_slug);

        if (is_array($legacy) && $legacy !== []) {
            update_option($target_name, $legacy, false);
        }
    }

    if (get_option(em_wp_cta_catalog_option_name(), null) === null) {
        update_option(em_wp_cta_catalog_option_name(), $catalog, false);
    }
}

/**
 * Copie les contenus footer template → catalogue V2.
 */
function em_wp_catalog_migrate_footer_entries(): void
{
    $catalog = em_wp_footer_catalog_default_entries();
    $map = em_wp_footer_v1_slug_map();

    foreach ($map as $template_slug => $catalog_slug) {
        if (!isset($catalog[$catalog_slug])) {
            continue;
        }

        $target_name = em_wp_footer_catalog_item_option_name($catalog_slug);

        if (get_option($target_name, null) !== null) {
            continue;
        }

        if (!function_exists('em_wp_get_template_rubrique_options')) {
            continue;
        }

        $legacy = em_wp_get_template_rubrique_options('footer', $template_slug);

        if (is_array($legacy) && $legacy !== []) {
            update_option($target_name, $legacy, false);
        }
    }

    if (get_option(em_wp_footer_catalog_option_name(), null) === null) {
        update_option(em_wp_footer_catalog_option_name(), $catalog, false);
    }
}

/**
 * Migration idempotente top-bar/release/cta/footer pour sites déjà migrés Phase 4.
 */
function em_wp_catalog_maybe_migrate_top_bar_release_cta_footer_entries(): void
{
    $flag = 'em_wp_catalog_v2d_entries_migrated';

    if (get_option($flag, false)) {
        return;
    }

    em_wp_catalog_migrate_top_bar_entries();
    em_wp_catalog_migrate_release_entries();
    em_wp_catalog_migrate_cta_entries();
    em_wp_catalog_migrate_footer_entries();

    update_option($flag, 1, false);
}

/**
 * Migration idempotente : options rubrique template → pointeurs catalogue (top-bar/release/cta/footer).
 */
function em_wp_catalog_maybe_migrate_top_bar_release_cta_footer_rubrique_slugs(): void
{
    $flag = 'em_wp_catalog_v2d_rubrique_slugs_migrated';

    if (get_option($flag, false)) {
        return;
    }

    em_wp_catalog_migrate_top_bar_rubrique_options();
    em_wp_catalog_migrate_release_rubrique_options();
    em_wp_catalog_migrate_cta_rubrique_options();
    em_wp_catalog_migrate_footer_rubrique_options();

    update_option($flag, 1, false);
}

/**
 * Réduit les options rubrique TOP-BAR par template (slug catalogue + style).
 */
function em_wp_catalog_migrate_top_bar_rubrique_options(): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_top_bar_option_name')) {
        return;
    }

    $map = function_exists('em_wp_top_bar_v1_slug_map') ? em_wp_top_bar_v1_slug_map() : [];
    $legacy_keys = ['logo_url', 'logo_hidden', 'background_image_enabled', 'background_image_url', 'background_image_hidden', 'items', 'stream_icons_hidden'];

    foreach (array_keys(em_wp_template_registry()) as $template_slug) {
        $template_slug = sanitize_key((string) $template_slug);

        if ($template_slug === '') {
            continue;
        }

        $saved = get_option(em_wp_top_bar_option_name($template_slug), null);

        if (!is_array($saved)) {
            continue;
        }

        $has_legacy = false;

        foreach ($legacy_keys as $key) {
            if (array_key_exists($key, $saved)) {
                $has_legacy = true;
                break;
            }
        }

        if (!$has_legacy && array_key_exists('top_bar_slug', $saved)) {
            continue;
        }

        $top_bar_slug = sanitize_key((string) ($saved['top_bar_slug'] ?? ''));

        if ($top_bar_slug === '') {
            $top_bar_slug = sanitize_key((string) ($map[$template_slug] ?? ''));
        }

        update_option(
            em_wp_top_bar_option_name($template_slug),
            [
                'enabled'          => !empty($saved['enabled']),
                'top_bar_slug'     => $top_bar_slug,
                'background_color' => (string) ($saved['background_color'] ?? ''),
                'text_color'       => (string) ($saved['text_color'] ?? ''),
            ],
            false
        );
    }
}

/**
 * Réduit les options rubrique RELEASE par template (slug catalogue + style).
 */
function em_wp_catalog_migrate_release_rubrique_options(): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_release_option_name')) {
        return;
    }

    $map = function_exists('em_wp_release_v1_slug_map') ? em_wp_release_v1_slug_map() : [];
    $legacy_keys = ['kicker', 'title_left', 'title_highlight', 'cover_image', 'rows'];

    foreach (array_keys(em_wp_template_registry()) as $template_slug) {
        $template_slug = sanitize_key((string) $template_slug);

        if ($template_slug === '') {
            continue;
        }

        $saved = get_option(em_wp_release_option_name($template_slug), null);

        if (!is_array($saved)) {
            continue;
        }

        $has_legacy = false;

        foreach ($legacy_keys as $key) {
            if (array_key_exists($key, $saved)) {
                $has_legacy = true;
                break;
            }
        }

        if (!$has_legacy && array_key_exists('release_slug', $saved)) {
            continue;
        }

        $release_slug = sanitize_key((string) ($saved['release_slug'] ?? ''));

        if ($release_slug === '') {
            $release_slug = sanitize_key((string) ($map[$template_slug] ?? ''));
        }

        update_option(
            em_wp_release_option_name($template_slug),
            [
                'enabled'          => !empty($saved['enabled']),
                'release_slug'     => $release_slug,
                'background_color' => (string) ($saved['background_color'] ?? ''),
                'text_color'       => (string) ($saved['text_color'] ?? ''),
            ],
            false
        );
    }
}

/**
 * Réduit les options rubrique CTA par template (slug catalogue + style).
 */
function em_wp_catalog_migrate_cta_rubrique_options(): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_cta_option_name')) {
        return;
    }

    $map = function_exists('em_wp_cta_v1_slug_map') ? em_wp_cta_v1_slug_map() : [];
    $legacy_keys = ['kicker', 'title_left', 'title_right', 'description', 'hashtag', 'stream_label', 'stream_link', 'video_label', 'video_link', 'tiktok_label', 'tiktok_link', 'instagram_label', 'instagram_link', 'texture_image'];

    foreach (array_keys(em_wp_template_registry()) as $template_slug) {
        $template_slug = sanitize_key((string) $template_slug);

        if ($template_slug === '') {
            continue;
        }

        $saved = get_option(em_wp_cta_option_name($template_slug), null);

        if (!is_array($saved)) {
            continue;
        }

        $has_legacy = false;

        foreach ($legacy_keys as $key) {
            if (array_key_exists($key, $saved)) {
                $has_legacy = true;
                break;
            }
        }

        if (!$has_legacy && array_key_exists('cta_slug', $saved)) {
            continue;
        }

        $cta_slug = sanitize_key((string) ($saved['cta_slug'] ?? ''));

        if ($cta_slug === '') {
            $cta_slug = sanitize_key((string) ($map[$template_slug] ?? ''));
        }

        update_option(
            em_wp_cta_option_name($template_slug),
            [
                'enabled'          => !empty($saved['enabled']),
                'cta_slug'         => $cta_slug,
                'background_color' => (string) ($saved['background_color'] ?? ''),
                'text_color'       => (string) ($saved['text_color'] ?? ''),
            ],
            false
        );
    }
}

/**
 * Réduit les options rubrique FOOTER par template (slug catalogue + style).
 */
function em_wp_catalog_migrate_footer_rubrique_options(): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_footer_option_name')) {
        return;
    }

    $map = function_exists('em_wp_footer_v1_slug_map') ? em_wp_footer_v1_slug_map() : [];
    $legacy_keys = ['line1', 'line2', 'sticky_stream_label', 'sticky_video_label', 'sticky_tiktok_label', 'sticky_tiktok_link'];

    foreach (array_keys(em_wp_template_registry()) as $template_slug) {
        $template_slug = sanitize_key((string) $template_slug);

        if ($template_slug === '') {
            continue;
        }

        $saved = get_option(em_wp_footer_option_name($template_slug), null);

        if (!is_array($saved)) {
            continue;
        }

        $has_legacy = false;

        foreach ($legacy_keys as $key) {
            if (array_key_exists($key, $saved)) {
                $has_legacy = true;
                break;
            }
        }

        if (!$has_legacy && array_key_exists('footer_slug', $saved)) {
            continue;
        }

        $footer_slug = sanitize_key((string) ($saved['footer_slug'] ?? ''));

        if ($footer_slug === '') {
            $footer_slug = sanitize_key((string) ($map[$template_slug] ?? ''));
        }

        update_option(
            em_wp_footer_option_name($template_slug),
            [
                'enabled'          => !empty($saved['enabled']),
                'footer_slug'      => $footer_slug,
                'background_color' => (string) ($saved['background_color'] ?? ''),
                'text_color'       => (string) ($saved['text_color'] ?? ''),
            ],
            false
        );
    }
}
