<?php
/**
 * Repositionnement des menus WP natifs hors place.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retire les séparateurs WordPress natifs (separator-last, separator1, etc.).
 */
function em_wp_admin_purge_native_wp_menu_separators(): void
{
    global $menu;

    foreach ($menu as $position => $item) {
        if (!is_array($item) || !em_wp_admin_is_native_wp_menu_separator($item)) {
            continue;
        }

        unset($menu[$position]);
    }
}

/**
 * Extrait les entrées de menu hors place dans une plage de positions.
 *
 * @return array<int|float, array<int, string>>
 */
function em_wp_admin_collect_menu_intruders_in_range(float $range_start, float $range_end, array $allowed_slugs): array
{
    global $menu;

    $intruders = [];

    foreach ($menu as $position => $item) {
        if (!is_numeric($position)) {
            continue;
        }

        $pos = (float) $position;

        if ($pos < $range_start || $pos > $range_end) {
            continue;
        }

        $slug = (string) ($item[2] ?? '');

        if ($slug === '' || in_array($slug, $allowed_slugs, true)) {
            continue;
        }

        if (
            in_array($slug, em_wp_admin_menu_chrome_slugs(), true)
            || str_starts_with($slug, 'em-wp-menu-')
            || str_starts_with($slug, 'em-wp-')
        ) {
            continue;
        }

        if (em_wp_admin_is_native_wp_menu_separator($item)) {
            unset($menu[$position]);
            continue;
        }

        $intruders[$position] = $item;
    }

    foreach (array_keys($intruders) as $position) {
        unset($menu[$position]);
    }

    return $intruders;
}

/**
 * Repositionne les menus WP natifs tombés dans Rubriques ou Catalogues.
 *
 * @return array<int|float, array<int, string>>
 */
function em_wp_admin_collect_intruding_menus(): array
{
    $intruders = em_wp_admin_collect_menu_intruders_in_range(
        (float) em_wp_admin_menu_section_label_position(),
        (float) (em_wp_admin_menu_separator_bottom_position() - 1),
        em_wp_admin_rubrique_reserved_menu_slugs()
    );

    $intruders += em_wp_admin_collect_menu_intruders_in_range(
        (float) em_wp_admin_menu_position_catalog_parent(),
        (float) (em_wp_admin_menu_catalog_separator_bottom_position() - 0.01),
        em_wp_admin_catalog_reserved_menu_slugs()
    );

    $intruders += em_wp_admin_collect_menu_intruders_in_range(
        em_wp_admin_menu_templates_position(),
        (float) (em_wp_admin_menu_templates_separator_bottom_position() - 1),
        em_wp_admin_template_reserved_menu_slugs()
    );

    return $intruders;
}

/**
 * Trie les menus natifs pour l'insertion sous Paramètres.
 *
 * @param array<int|float, array<int, string>> $intruders
 * @return array<int|float, array<int, string>>
 */
function em_wp_admin_sort_intruders_for_settings(array $intruders): array
{
    $order = array_flip(em_wp_admin_native_settings_menu_order());

    uasort(
        $intruders,
        static function (array $item_a, array $item_b) use ($order): int {
            $slug_a = (string) ($item_a[2] ?? '');
            $slug_b = (string) ($item_b[2] ?? '');
            $rank_a = $order[$slug_a] ?? 99;
            $rank_b = $order[$slug_b] ?? 99;

            if ($rank_a !== $rank_b) {
                return $rank_a <=> $rank_b;
            }

            return strcmp($slug_a, $slug_b);
        }
    );

    return $intruders;
}

/**
 * Insère les menus natifs déplacés sous le libellé « Paramètres ».
 *
 * @param array<int, array<int, string>> $intruders
 */
function em_wp_admin_insert_relocated_menus(array $intruders): void
{
    global $menu;

    if ($intruders === []) {
        return;
    }

    $intruders = em_wp_admin_sort_intruders_for_settings($intruders);

    ksort($intruders, SORT_NUMERIC);

    $insert_at = em_wp_admin_menu_wp_settings_label_position() + 1;

    foreach ($intruders as $item) {
        while (isset($menu[$insert_at])) {
            $insert_at++;
        }

        $menu[$insert_at] = $item;
        $insert_at++;
    }
}
