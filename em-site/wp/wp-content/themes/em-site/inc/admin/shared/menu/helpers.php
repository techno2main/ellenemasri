<?php
/**
 * Helpers menu admin (structures, décalage positions).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Décale les entrées du menu admin à partir d'une position (clés entières).
 */
function em_site_admin_shift_admin_menu_positions(int $from_position, int $offset): void
{
    global $menu;

    if ($offset <= 0) {
        return;
    }

    $keys_to_shift = [];

    foreach (array_keys($menu) as $position) {
        if (!is_int($position)) {
            continue;
        }

        if ($position >= $from_position) {
            $keys_to_shift[] = $position;
        }
    }

    rsort($keys_to_shift, SORT_NUMERIC);

    foreach ($keys_to_shift as $position) {
        $menu[$position + $offset] = $menu[$position];
        unset($menu[$position]);
    }
}

/**
 * Structure d'un item séparateur compatible menu-header.php.
 *
 * @return array<int, string>
 */
function em_site_admin_menu_separator_item(string $slug, string $class_suffix): array
{
    return [
        '',
        'read',
        $slug,
        '',
        'wp-menu-separator ' . $class_suffix,
        '',
        '',
    ];
}

/**
 * Structure d'un libellé de section non cliquable.
 *
 * @return array<int, string>
 */
function em_site_admin_menu_section_label_item(string $slug, string $label, string $class_suffix): array
{
    return [
        $label,
        'read',
        $slug,
        $label,
        $class_suffix . ' menu-top',
        '',
        'none',
    ];
}

/**
 * Ordre souhaité des menus WP natifs sous « Paramètres ».
 *
 * @return string[]
 */
function em_site_admin_native_settings_menu_order(): array
{
    return [
        em_site_admin_dashicons_manager_page_slug(),
        'themes.php',
        'options-general.php',
        'plugins.php',
    ];
}

/**
 * Indique si l'entrée menu est un séparateur WordPress natif (pas em-site).
 *
 * @param array<int, string> $item
 */
function em_site_admin_is_native_wp_menu_separator(array $item): bool
{
    $slug  = (string) ($item[2] ?? '');
    $class = (string) ($item[4] ?? '');

    if (in_array($slug, em_site_admin_menu_chrome_slugs(), true)) {
        return false;
    }

    if (str_contains($class, 'wp-menu-separator')) {
        return true;
    }

    return $slug !== '' && preg_match('/^separator(-|$|\d)/', $slug) === 1;
}
