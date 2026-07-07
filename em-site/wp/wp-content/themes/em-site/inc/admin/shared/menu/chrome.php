<?php
/**
 * Chrome menu admin (filets, libellés, purge).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slugs des entrées « chrome » injectées dans le menu admin.
 *
 * @return string[]
 */
function em_site_admin_menu_chrome_slugs(): array
{
    return [
        'separator-em-site-site-top',
        'separator-em-site-bottom',
        'separator-em-site-before-vlb',
        'separator-em-site-after-medias',
        'separator-em-site-after-catalog',
        'separator-em-site-after-templates',
        'separator-em-site-before-settings',
        'em-site-menu-wp-settings-label',
        'em-site-menu-active-template-label',
    ];
}

/**
 * Retire les entrées chrome déjà présentes (évite doublons si admin_menu repasse).
 */
function em_site_admin_purge_menu_chrome_entries(): void
{
    global $menu;

    foreach ($menu as $position => $item) {
        $slug = (string) ($item[2] ?? '');

        if (in_array($slug, em_site_admin_menu_chrome_slugs(), true)) {
            unset($menu[$position]);
        }
    }
}

/**
 * Extrait une entrée du menu admin par slug (retire du tableau global).
 *
 * @return array<int, string>|null
 */
function em_site_admin_extract_menu_item_by_slug(string $slug): ?array
{
    global $menu;

    foreach ($menu as $position => $item) {
        if (!is_array($item) || (string) ($item[2] ?? '') !== $slug) {
            continue;
        }

        unset($menu[$position]);

        return $item;
    }

    return null;
}

/**
 * Libellés de section, séparateurs et espace autour des blocs Rubriques / WP natif.
 */
function em_site_admin_register_menu_chrome(): void
{
    global $menu;

    static $registered = false;

    if ($registered) {
        return;
    }

    $registered = true;

    em_site_admin_purge_menu_chrome_entries();
    em_site_admin_purge_native_wp_menu_separators();
    em_site_admin_remove_native_media_menu();

    $intruders = em_site_admin_collect_intruding_menus();

    em_site_admin_shift_admin_menu_positions(em_site_admin_menu_wp_settings_label_position(), 1);
    em_site_admin_insert_relocated_menus($intruders);
}
add_action('admin_menu', 'em_site_admin_register_menu_chrome', 9999);
