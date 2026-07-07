<?php
/**
 * Menu MEDIAS (accordéon + redirection Media Library).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug parent menu MEDIAS (accordéon).
 */
function em_wp_admin_media_parent_menu_slug(): string
{
    return 'em-medias';
}

/**
 * URL Media Library WordPress.
 */
function em_wp_admin_medias_library_admin_url(): string
{
    return admin_url('upload.php');
}

/**
 * Position menu MEDIAS (bloc navigation principale).
 */
function em_wp_admin_menu_media_position(): int
{
    return (int) em_wp_admin_menu_position_for_slug(em_wp_admin_media_parent_menu_slug());
}

/**
 * Position sous-menu Librairie (accordéon MEDIAS).
 */
function em_wp_admin_menu_media_library_position(): float
{
    return em_wp_admin_menu_position_for_slug('upload.php');
}

/**
 * Position sous-menu Ajouter (accordéon MEDIAS).
 */
function em_wp_admin_menu_media_add_position(): float
{
    return em_wp_admin_menu_position_for_slug('media-new.php');
}

/**
 * Position parent MEDIAS (accordéon).
 */
function em_wp_admin_menu_medias_parent_position(): float
{
    return em_wp_admin_menu_position_for_slug(em_wp_admin_media_parent_menu_slug());
}

/**
 * Slugs des entrées accordéon MEDIAS.
 *
 * @return string[]
 */
function em_wp_admin_media_accordion_child_slugs(): array
{
    return ['upload.php', 'media-new.php'];
}

/**
 * Parent MEDIAS (accordéon) — enregistrement natif WP.
 */
function em_wp_admin_medias_register_menu(): void
{
    if (!current_user_can(em_wp_admin_menu_capability())) {
        return;
    }

    add_menu_page(
        __('MEDIAS', 'em-wp'),
        __('MEDIAS', 'em-wp'),
        em_wp_admin_menu_capability(),
        em_wp_admin_media_parent_menu_slug(),
        '__return_null',
        'dashicons-admin-media',
        em_wp_admin_menu_medias_parent_position()
    );
}
add_action('admin_menu', 'em_wp_admin_medias_register_menu', 25);

/**
 * Redirige le hub MEDIAS vers upload.php (URL directe dans le navigateur).
 */
function em_wp_admin_redirect_medias_hub_to_library(): void
{
    if (!current_user_can(em_wp_admin_menu_capability())) {
        return;
    }

    wp_safe_redirect(em_wp_admin_medias_library_admin_url());
    exit;
}
add_action('load-toplevel_page_' . em_wp_admin_media_parent_menu_slug(), 'em_wp_admin_redirect_medias_hub_to_library');

/**
 * Secours si accès admin.php?page=em-medias avant le hook load-*.
 */
function em_wp_admin_redirect_medias_hub_on_admin_init(): void
{
    if (!current_user_can(em_wp_admin_menu_capability())) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug !== em_wp_admin_media_parent_menu_slug()) {
        return;
    }

    em_wp_admin_redirect_medias_hub_to_library();
}
add_action('admin_init', 'em_wp_admin_redirect_medias_hub_on_admin_init', 0);

/**
 * Retire le sous-menu dupliqué WordPress sur MEDIAS.
 */
function em_wp_admin_medias_remove_duplicate_submenu(): void
{
    remove_submenu_page(
        em_wp_admin_media_parent_menu_slug(),
        em_wp_admin_media_parent_menu_slug()
    );
}
add_action('admin_menu', 'em_wp_admin_medias_remove_duplicate_submenu', 999);

/**
 * Retire l'entrée Media native (Library) du menu latéral.
 */
function em_wp_admin_remove_native_media_menu(): void
{
    global $menu, $submenu;

    foreach ($menu as $position => $item) {
        if (!is_array($item)) {
            continue;
        }

        $slug = (string) ($item[2] ?? '');

        if ($slug === 'upload.php' || $slug === 'media-new.php') {
            unset($menu[$position]);
        }
    }

    unset($submenu['upload.php']);
    remove_submenu_page('upload.php', 'upload.php');
    remove_submenu_page('upload.php', 'media-new.php');
}

/**
 * Retire toute entrée MEDIAS / upload / media-new avant reconstruction layout.
 */
function em_wp_admin_purge_media_menu_entries(): void
{
    global $menu, $submenu;

    $slugs = [
        em_wp_admin_media_parent_menu_slug(),
        'upload.php',
        'media-new.php',
    ];

    foreach ($menu as $position => $item) {
        if (!is_array($item)) {
            continue;
        }

        $slug = function_exists('em_wp_admin_menu_item_slug')
            ? em_wp_admin_menu_item_slug($item)
            : (string) ($item[2] ?? '');

        if (in_array($slug, $slugs, true)) {
            unset($menu[$position]);
        }
    }

    unset($submenu['upload.php']);
    unset($submenu[em_wp_admin_media_parent_menu_slug()]);

    foreach ($slugs as $slug) {
        remove_submenu_page('upload.php', $slug);
        remove_submenu_page(em_wp_admin_media_parent_menu_slug(), $slug);
    }
}

