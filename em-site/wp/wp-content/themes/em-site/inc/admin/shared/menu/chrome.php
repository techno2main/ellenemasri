<?php
/**
 * Chrome menu admin (filets, libellés, purge).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slugs des entrées « chrome » injectées dans le menu admin.
 *
 * @return string[]
 */
function em_wp_admin_menu_chrome_slugs(): array
{
    return [
        'separator-em-wp-site-top',
        'separator-em-wp-bottom',
        'separator-em-wp-before-vlb',
        'separator-em-wp-after-medias',
        'separator-em-wp-after-catalog',
        'separator-em-wp-after-templates',
        'separator-em-wp-before-settings',
        'em-wp-menu-wp-settings-label',
        'em-wp-menu-active-template-label',
    ];
}

/**
 * Retire les entrées chrome déjà présentes (évite doublons si admin_menu repasse).
 */
function em_wp_admin_purge_menu_chrome_entries(): void
{
    global $menu;

    foreach ($menu as $position => $item) {
        $slug = (string) ($item[2] ?? '');

        if (in_array($slug, em_wp_admin_menu_chrome_slugs(), true)) {
            unset($menu[$position]);
        }
    }
}

/**
 * Extrait une entrée du menu admin par slug (retire du tableau global).
 *
 * @return array<int, string>|null
 */
function em_wp_admin_extract_menu_item_by_slug(string $slug): ?array
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
function em_wp_admin_register_menu_chrome(): void
{
    global $menu;

    static $registered = false;

    if ($registered) {
        return;
    }

    $registered = true;

    em_wp_admin_purge_menu_chrome_entries();
    em_wp_admin_purge_native_wp_menu_separators();
    em_wp_admin_remove_native_media_menu();

    $intruders = em_wp_admin_collect_intruding_menus();

    em_wp_admin_shift_admin_menu_positions(em_wp_admin_menu_wp_settings_label_position(), 1);
    em_wp_admin_insert_relocated_menus($intruders);
}
add_action('admin_menu', 'em_wp_admin_register_menu_chrome', 9999);
