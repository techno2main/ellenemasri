<?php
/**
 * Menu admin partagé (bloc Rubriques, Paramètres, séparateurs).
 *
 * Convention : tout nouveau module front/admin em-wp s'enregistre dans le bloc
 * « Rubriques du site » (entre le filet du haut et le filet du bas).
 *
 * Pour ajouter un module :
 * 1. Ajouter son slug dans em_wp_admin_site_rubrique_modules() (ordre d'affichage).
 * 2. Ajouter sa définition dans em_wp_admin_site_rubrique_definitions() (inc/admin/pages/rubriques.php)
 *    avec preview_zone (voir inc/admin/shared/landing-preview.php).
 * 3. Dans settings.php du module : add_menu_page(..., em_wp_admin_menu_position_for_site_module('mon-slug')).
 * 4. Si la rubrique occupe une nouvelle zone front : étendre la mini-maquette dans landing-preview.php.
 * 5. Ordre / visibilité : rubrique-order.php (milieu réordonnable ; TOP-BAR / FOOTER = afficher-masquer).
 * 6. Modules multi-variantes (HEROS, SLIDERS, VIDEOS, RELEASES) : hub + sous-menus par variante.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slugs des modules dans le bloc « Rubriques du site » (ordre du menu).
 *
 * @return string[]
 */
function em_wp_admin_site_rubrique_modules(): array
{
    return em_wp_get_site_rubrique_order();
}

/**
 * Première position menu d'un module Rubriques.
 */
function em_wp_admin_site_rubrique_menu_base(): int
{
    return 55;
}

/**
 * Position menu admin d'un module Rubriques.
 */
function em_wp_admin_menu_position_for_site_module(string $module_slug): int
{
    $modules = em_wp_admin_site_rubrique_modules();
    $index = array_search($module_slug, $modules, true);

    if ($index === false) {
        return em_wp_admin_site_rubrique_menu_base();
    }

    return em_wp_admin_site_rubrique_menu_base() + (int) $index;
}

/**
 * Position TOP-BAR dans le menu admin.
 */
function em_wp_admin_menu_position_top_bar(): int
{
    return em_wp_admin_menu_position_for_site_module('top-bar');
}

/**
 * Position HEROS dans le menu admin.
 */
function em_wp_admin_menu_position_hero(): int
{
    return em_wp_admin_menu_position_for_site_module('hero');
}

/**
 * Position SLIDERS dans le menu admin.
 */
function em_wp_admin_menu_position_slider(): int
{
    return em_wp_admin_menu_position_for_site_module('slider');
}

/**
 * Position du filet au-dessus de « Rubriques du site ».
 */
function em_wp_admin_menu_separator_above_site_position(): int
{
    return em_wp_admin_site_rubrique_menu_base() - 2;
}

/**
 * Position du libellé « Rubriques du site ».
 */
function em_wp_admin_menu_section_label_position(): int
{
    return em_wp_admin_site_rubrique_menu_base() - 1;
}

/**
 * Position du séparateur sous le bloc Rubriques.
 */
function em_wp_admin_menu_separator_bottom_position(): int
{
    return em_wp_admin_site_rubrique_menu_base() + count(em_wp_admin_site_rubrique_modules());
}

/**
 * Position du séparateur d'espace avant « Paramètres ».
 */
function em_wp_admin_menu_wp_settings_gap_position(): int
{
    return em_wp_admin_menu_separator_bottom_position() + 1;
}

/**
 * Position du libellé « Paramètres » (menus WP natifs).
 */
function em_wp_admin_menu_wp_settings_label_position(): int
{
    return em_wp_admin_menu_separator_bottom_position() + 2;
}

/**
 * Décale les entrées du menu admin à partir d'une position (clés entières).
 */
function em_wp_admin_shift_admin_menu_positions(int $from_position, int $offset): void
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
function em_wp_admin_menu_separator_item(string $slug, string $class_suffix): array
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
function em_wp_admin_menu_section_label_item(string $slug, string $label, string $class_suffix): array
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
 * Slugs réservés au bloc « Rubriques du site » (ne doivent pas être expulsés).
 *
 * @return string[]
 */
function em_wp_admin_rubrique_reserved_menu_slugs(): array
{
    $slugs = [
        em_wp_admin_rubriques_page_slug(),
        'separator-em-wp-site-top',
        'separator-em-wp-bottom',
        'separator-em-wp-wp-gap',
        'em-wp-menu-wp-settings-label',
    ];

    if (function_exists('em_wp_admin_site_rubrique_definitions')) {
        foreach (em_wp_admin_site_rubrique_definitions() as $definition) {
            $page_slug = (string) ($definition['page_slug'] ?? '');
            if ($page_slug !== '') {
                $slugs[] = $page_slug;
            }
        }
    }

    return array_values(array_unique($slugs));
}

/**
 * Repositionne les menus WP natifs tombés dans le bloc Rubriques (ex. Apparence à 60).
 *
 * @return array<int, array<int, string>>
 */
function em_wp_admin_collect_intruding_menus(): array
{
    global $menu;

    $block_start = em_wp_admin_menu_section_label_position();
    $block_end = em_wp_admin_menu_separator_bottom_position() - 1;
    $allowed = em_wp_admin_rubrique_reserved_menu_slugs();
    $intruders = [];

    foreach ($menu as $position => $item) {
        if (!is_int($position) || $position < $block_start || $position > $block_end) {
            continue;
        }

        $slug = (string) ($item[2] ?? '');
        if (in_array($slug, $allowed, true)) {
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

/**
 * Libellés de section, séparateurs et espace autour des blocs Rubriques / WP natif.
 */
function em_wp_admin_register_menu_chrome(): void
{
    global $menu;

    $intruders = em_wp_admin_collect_intruding_menus();

    em_wp_admin_shift_admin_menu_positions(em_wp_admin_menu_wp_settings_label_position(), 2);

    $menu[em_wp_admin_menu_separator_above_site_position()] = em_wp_admin_menu_separator_item(
        'separator-em-wp-site-top',
        'separator-em-wp-site-top'
    );

    $menu[em_wp_admin_menu_separator_bottom_position()] = em_wp_admin_menu_separator_item(
        'separator-em-wp-bottom',
        'separator-em-wp-bottom'
    );

    $menu[em_wp_admin_menu_wp_settings_gap_position()] = em_wp_admin_menu_separator_item(
        'separator-em-wp-wp-gap',
        'separator-em-wp-wp-gap'
    );

    $menu[em_wp_admin_menu_wp_settings_label_position()] = em_wp_admin_menu_section_label_item(
        'em-wp-menu-wp-settings-label',
        __('Paramètres', 'em-wp'),
        'em-wp-menu-wp-settings-label'
    );

    em_wp_admin_insert_relocated_menus($intruders);
}
add_action('admin_menu', 'em_wp_admin_register_menu_chrome', 9999);

/**
 * Styles admin : libellés de section + filets blancs visibles.
 */
function em_wp_admin_menu_chrome_styles(): void
{
    ?>
    <style id="em-wp-admin-menu-chrome">
        #adminmenu li.em-wp-menu-section-label,
        #adminmenu li.em-wp-menu-wp-settings-label {
            cursor: default;
            pointer-events: none;
            margin: 0;
            padding: 0;
        }

        #adminmenu li.em-wp-menu-section-label > a,
        #adminmenu li.em-wp-menu-wp-settings-label > a {
            cursor: default;
            background: transparent !important;
            box-shadow: none !important;
            padding: 12px 12px 2px;
            margin: 0;
        }

        #adminmenu li.em-wp-menu-wp-settings-label > a {
            padding-top: 6px;
        }

        #adminmenu li.em-wp-menu-section-label:hover > a,
        #adminmenu li.em-wp-menu-section-label:focus > a,
        #adminmenu li.em-wp-menu-wp-settings-label:hover > a,
        #adminmenu li.em-wp-menu-wp-settings-label:focus > a {
            background: transparent !important;
            box-shadow: none !important;
            color: rgba(255, 255, 255, 0.65);
        }

        #adminmenu li.em-wp-menu-section-label .wp-menu-image,
        #adminmenu li.em-wp-menu-section-label .wp-menu-image::before,
        #adminmenu li.em-wp-menu-wp-settings-label .wp-menu-image,
        #adminmenu li.em-wp-menu-wp-settings-label .wp-menu-image::before {
            display: none;
        }

        #adminmenu li.em-wp-menu-section-label .wp-menu-name,
        #adminmenu li.em-wp-menu-wp-settings-label .wp-menu-name {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.65);
            padding: 0;
        }

        #adminmenu li.wp-menu-separator.separator-em-wp-site-top,
        #adminmenu li.wp-menu-separator.separator-em-wp-bottom,
        #adminmenu li.wp-menu-separator.separator-em-wp-wp-gap {
            cursor: default;
            pointer-events: none;
            margin: 0;
            padding: 0;
            height: auto;
            min-height: 0;
            background: transparent !important;
            border: 0;
            box-shadow: none;
        }

        #adminmenu li.wp-menu-separator.separator-em-wp-wp-gap {
            margin-top: 10px;
            min-height: 28px;
        }

        #adminmenu li.wp-menu-separator.separator-em-wp-site-top .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-bottom .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-wp-gap .separator {
            display: block;
            height: 1px;
            margin: 6px 10px;
            padding: 0;
            border: 0;
            background: #ffffff;
            opacity: 0.42;
            box-shadow: none;
        }

        #adminmenu li.wp-menu-separator.separator-em-wp-wp-gap .separator {
            margin-top: 20px;
            margin-bottom: 6px;
        }
    </style>
    <?php
}
add_action('admin_head', 'em_wp_admin_menu_chrome_styles');
