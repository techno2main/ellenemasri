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
 * 7. Admin obligatoire pour toute nouvelle rubrique (inc/admin/shared/style-panel.php) :
 *    - Style de base : em_wp_admin_render_base_style_panel()
 *    - Preview bloc titre : em_wp_admin_module_style_data_attributes() + em_wp_admin_module_style_inline_vars()
 *    - Panneaux fermés : em_wp_admin_render_module_panel() / em_wp_admin_module_panel_classes()
 *    - Champs ligne : em-wp-admin-panel-body--row + classes em-wp-admin-field--*
 *    - Assets : em_wp_admin_enqueue_shared_assets() (accordion, color picker, preview)
 * 8. Bouton Supprimer : toujours passer par EmWpAdminConfirm.beforeDelete() (confirm-modal.js)
 *    + dépendance script em-wp-admin-confirm-modal.
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
 * Position HEADER dans le menu admin.
 */
function em_wp_admin_menu_position_header(): int
{
    return em_wp_admin_menu_position_for_site_module('header');
}

/**
 * Modules catalogues visibles sous CATALOGUES (ordre menu).
 *
 * @return string[]
 */
function em_wp_admin_catalog_menu_modules(): array
{
    return ['heros', 'sliders', 'videos', 'streams', 'socials'];
}

/**
 * Filet au-dessus du bloc Catalogues (séparation visuelle après MEDIAS).
 */
function em_wp_admin_menu_catalog_separator_top_position(): int
{
    return em_wp_admin_menu_media_position() + 1;
}

/**
 * Position menu MEDIAS (juste au-dessus du bloc Catalogues).
 */
function em_wp_admin_menu_media_position(): int
{
    return em_wp_admin_menu_separator_bottom_position() + 1;
}

/**
 * Position parent « CATALOGUES » (après Rubriques).
 */
function em_wp_admin_menu_position_catalog_parent(): int
{
    return em_wp_admin_menu_catalog_separator_top_position() + 1;
}

/**
 * @deprecated Utiliser em_wp_admin_menu_position_catalog_parent().
 */
function em_wp_admin_menu_catalog_section_label_position(): int
{
    return em_wp_admin_menu_position_catalog_parent();
}

/**
 * Position menu d'un module catalogue (HEROS, SLIDERS, …).
 */
function em_wp_admin_menu_position_for_catalog_module(string $module_slug): int
{
    $modules = em_wp_admin_catalog_menu_modules();
    $index = array_search($module_slug, $modules, true);

    if ($index === false) {
        return em_wp_admin_menu_position_catalog_parent() + 1;
    }

    return em_wp_admin_menu_position_catalog_parent() + 1 + (int) $index;
}

/**
 * Position menu HEROS (catalogues).
 */
function em_wp_admin_menu_position_catalog_heros(): int
{
    return em_wp_admin_menu_position_for_catalog_module('heros');
}

/**
 * Position menu SLIDERS (catalogues).
 */
function em_wp_admin_menu_position_catalog_sliders(): int
{
    return em_wp_admin_menu_position_for_catalog_module('sliders');
}

/**
 * @deprecated Utiliser em_wp_admin_menu_position_catalog_heros().
 */
function em_wp_admin_menu_position_catalog_sommaire(): int
{
    return em_wp_admin_menu_position_catalog_heros();
}

/**
 * Filet sous le bloc Catalogues.
 */
function em_wp_admin_menu_catalog_separator_bottom_position(): int
{
    return em_wp_admin_menu_position_catalog_parent() + 1 + count(em_wp_admin_catalog_menu_modules());
}

/**
 * Position HEROS dans le menu admin (legacy — catalogue).
 */
function em_wp_admin_menu_position_hero(): int
{
    return em_wp_admin_menu_position_catalog_heros();
}

/**
 * Position SLIDERS dans le menu admin (legacy — catalogue).
 */
function em_wp_admin_menu_position_slider(): int
{
    return em_wp_admin_menu_position_catalog_sliders();
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
 * Position menu Templates (parent TEMPLATES, après Catalogues).
 */
function em_wp_admin_menu_templates_position(): float
{
    return (float) (em_wp_admin_menu_catalog_separator_bottom_position() + 1);
}

/**
 * Position menu d'un template enregistré (MAYAMI, ELLENE, …).
 */
function em_wp_admin_menu_position_for_template(string $template_slug): int
{
    $registry = em_wp_template_registry();
    $index = array_search($template_slug, array_keys($registry), true);

    if ($index === false) {
        return (int) em_wp_admin_menu_templates_position() + 1;
    }

    return (int) em_wp_admin_menu_templates_position() + 1 + (int) $index;
}

/**
 * Filet sous le bloc Template.
 */
function em_wp_admin_menu_templates_separator_bottom_position(): int
{
    return (int) em_wp_admin_menu_templates_position() + 1 + count(em_wp_template_registry());
}

/**
 * Filet entre Templates et le libellé Paramètres.
 */
function em_wp_admin_menu_settings_separator_position(): int
{
    return em_wp_admin_menu_templates_separator_bottom_position() + 1;
}

/**
 * Position du libellé « Paramètres » (menus WP natifs), juste après Templates.
 */
function em_wp_admin_menu_wp_settings_label_position(): int
{
    return em_wp_admin_menu_settings_separator_position() + 1;
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
        'upload.php',
        'separator-em-wp-catalog-top',
        'separator-em-wp-catalog-bottom',
        'separator-em-wp-before-settings',
        'em-wp-menu-wp-settings-label',
    ];

    if (function_exists('em_wp_catalog_parent_menu_slug')) {
        $slugs[] = em_wp_catalog_parent_menu_slug();
    }

    if (function_exists('em_wp_catalog_registered_hub_menu_slugs')) {
        $slugs = array_merge($slugs, em_wp_catalog_registered_hub_menu_slugs());
    }

    if (function_exists('em_wp_catalog_sommaire_menu_slug')) {
        $slugs[] = em_wp_catalog_sommaire_menu_slug();
    }

    if (function_exists('em_wp_admin_template_parent_page_slug')) {
        $slugs[] = em_wp_admin_template_parent_page_slug();
    }

    if (function_exists('em_wp_admin_template_entry_page_slugs')) {
        $slugs = array_merge($slugs, em_wp_admin_template_entry_page_slugs());
    }

    if (function_exists('em_wp_admin_template_choice_page_slug')) {
        $slugs[] = em_wp_admin_template_choice_page_slug();
    }

    if (function_exists('em_wp_admin_templates_page_slug')) {
        $slugs[] = em_wp_admin_templates_page_slug();
    }

    if (function_exists('em_wp_admin_dashboard_page_slug')) {
        $slugs[] = em_wp_admin_dashboard_page_slug();
    }

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
 * Slugs réservés au bloc « Catalogues ».
 *
 * @return string[]
 */
function em_wp_admin_catalog_reserved_menu_slugs(): array
{
    $slugs = [];

    if (function_exists('em_wp_admin_dashboard_page_slug')) {
        $slugs[] = em_wp_admin_dashboard_page_slug();
    }

    $slugs[] = 'upload.php';

    if (function_exists('em_wp_catalog_parent_menu_slug')) {
        $slugs[] = em_wp_catalog_parent_menu_slug();
    }

    if (function_exists('em_wp_catalog_registered_hub_menu_slugs')) {
        $slugs = array_merge($slugs, em_wp_catalog_registered_hub_menu_slugs());
    }

    if (function_exists('em_wp_catalog_sommaire_menu_slug')) {
        $slugs[] = em_wp_catalog_sommaire_menu_slug();
    }

    return array_values(array_unique($slugs));
}

/**
 * Ordre souhaité des menus WP natifs sous « Paramètres ».
 *
 * @return string[]
 */
function em_wp_admin_native_settings_menu_order(): array
{
    return [
        'themes.php',
        'plugins.php',
        'options-general.php',
    ];
}

/**
 * Indique si l'entrée menu est un séparateur WordPress natif (pas em-wp).
 *
 * @param array<int, string> $item
 */
function em_wp_admin_is_native_wp_menu_separator(array $item): bool
{
    $slug  = (string) ($item[2] ?? '');
    $class = (string) ($item[4] ?? '');

    if (in_array($slug, em_wp_admin_menu_chrome_slugs(), true)) {
        return false;
    }

    if (str_contains($class, 'wp-menu-separator')) {
        return true;
    }

    return $slug !== '' && preg_match('/^separator(-|$|\d)/', $slug) === 1;
}

/**
 * Retire les séparateurs WordPress natifs (separator-last, separator1, etc.).
 * Seuls les filets em-wp (separator-em-wp-*) sont conservés.
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

        if (in_array($slug, em_wp_admin_menu_chrome_slugs(), true) || str_starts_with($slug, 'em-wp-menu-')) {
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
 * Repositionne les menus WP natifs tombés dans Rubriques ou Catalogues (ex. Plugins).
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
        (float) em_wp_admin_menu_catalog_separator_top_position(),
        (float) (em_wp_admin_menu_catalog_separator_bottom_position() - 1),
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
        'separator-em-wp-catalog-top',
        'separator-em-wp-catalog-bottom',
        'separator-em-wp-before-settings',
        'em-wp-menu-wp-settings-label',
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

    $media_item = em_wp_admin_extract_menu_item_by_slug('upload.php');

    $intruders = em_wp_admin_collect_intruding_menus();

    em_wp_admin_shift_admin_menu_positions(em_wp_admin_menu_wp_settings_label_position(), 1);

    $menu[em_wp_admin_menu_separator_above_site_position()] = em_wp_admin_menu_separator_item(
        'separator-em-wp-site-top',
        'separator-em-wp-site-top'
    );

    $menu[em_wp_admin_menu_separator_bottom_position()] = em_wp_admin_menu_separator_item(
        'separator-em-wp-bottom',
        'separator-em-wp-bottom'
    );

    if ($media_item !== null) {
        $media_item[0] = __('MEDIAS', 'em-wp');
        $menu[em_wp_admin_menu_media_position()] = $media_item;
    }

    $menu[em_wp_admin_menu_catalog_separator_top_position()] = em_wp_admin_menu_separator_item(
        'separator-em-wp-catalog-top',
        'separator-em-wp-catalog-top'
    );

    $menu[em_wp_admin_menu_catalog_separator_bottom_position()] = em_wp_admin_menu_separator_item(
        'separator-em-wp-catalog-bottom',
        'separator-em-wp-catalog-bottom'
    );

    $menu[em_wp_admin_menu_settings_separator_position()] = em_wp_admin_menu_separator_item(
        'separator-em-wp-before-settings',
        'separator-em-wp-before-settings'
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

        #adminmenu li.em-wp-menu-wp-settings-label > a {
            cursor: default;
            background: transparent !important;
            box-shadow: none !important;
            padding: 12px 12px 2px;
            margin: 0;
        }

        #adminmenu li.em-wp-menu-wp-settings-label:hover > a,
        #adminmenu li.em-wp-menu-wp-settings-label:focus > a {
            background: transparent !important;
            box-shadow: none !important;
            color: rgba(255, 255, 255, 0.65);
        }

        #adminmenu li.em-wp-menu-wp-settings-label .wp-menu-image,
        #adminmenu li.em-wp-menu-wp-settings-label .wp-menu-image::before {
            display: none;
        }

        #adminmenu li.em-wp-menu-wp-settings-label .wp-menu-name {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.65);
            padding: 0;
        }

        #adminmenu li.em-wp-menu-wp-settings-label::before,
        #adminmenu li.em-wp-menu-wp-settings-label::after {
            content: none !important;
            display: none !important;
        }

        #adminmenu li.em-wp-menu-wp-settings-label ~ li.em-wp-menu-wp-settings-label {
            display: none !important;
        }

        #adminmenu li.em-wp-menu-section-label > a {
            cursor: default;
            background: transparent !important;
            box-shadow: none !important;
            padding: 12px 12px 2px;
            margin: 0;
        }

        #adminmenu li.em-wp-menu-section-label:hover > a,
        #adminmenu li.em-wp-menu-section-label:focus > a {
            background: transparent !important;
            box-shadow: none !important;
            color: rgba(255, 255, 255, 0.65);
        }

        #adminmenu li.em-wp-menu-section-label .wp-menu-image,
        #adminmenu li.em-wp-menu-section-label .wp-menu-image::before {
            display: none;
        }

        #adminmenu li.em-wp-menu-section-label .wp-menu-name {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.65);
            padding: 0;
        }

        #adminmenu li.wp-menu-separator.separator-em-wp-site-top,
        #adminmenu li.wp-menu-separator.separator-em-wp-bottom,
        #adminmenu li.wp-menu-separator.separator-em-wp-catalog-top,
        #adminmenu li.wp-menu-separator.separator-em-wp-catalog-bottom,
        #adminmenu li.wp-menu-separator.separator-em-wp-before-settings {
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

        #adminmenu li.wp-menu-separator.separator-em-wp-site-top .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-bottom .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-catalog-top .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-catalog-bottom .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-before-settings .separator {
            display: block;
            height: 1px;
            margin: 6px 10px;
            padding: 0;
            border: 0;
            background: #ffffff;
            opacity: 0.42;
            box-shadow: none;
        }

        /* Filets WP natifs résiduels (separator-last, etc.) — masqués, remplacés par em-wp. */
        #adminmenu li.wp-menu-separator:not(.separator-em-wp-site-top):not(.separator-em-wp-bottom):not(.separator-em-wp-catalog-top):not(.separator-em-wp-catalog-bottom):not(.separator-em-wp-before-settings) {
            display: none !important;
        }
    </style>
    <?php
}
add_action('admin_head', 'em_wp_admin_menu_chrome_styles');

/**
 * Indique si l'écran admin courant appartient au bloc « Rubriques du site ».
 */
function em_wp_admin_is_rubrique_screen(): bool
{
    if (!is_admin()) {
        return false;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return false;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    return $page_slug !== '' && str_starts_with($page_slug, 'em-wp-');
}

/**
 * Texte pied de page admin des écrans Rubriques du site.
 */
function em_wp_admin_rubrique_footer_text(): string
{
    return 'Made with ❤️ for Ellene Masri - © Tyson - 2026';
}

/**
 * Remplace « Thank you for creating with WordPress. » sur les écrans Rubriques.
 */
function em_wp_admin_filter_rubrique_footer_text(string $text): string
{
    if (!em_wp_admin_is_rubrique_screen()) {
        return $text;
    }

    return em_wp_admin_rubrique_footer_text();
}
add_filter('admin_footer_text', 'em_wp_admin_filter_rubrique_footer_text');

/**
 * Masque « Version X.X » à droite du pied de page sur les écrans Rubriques.
 */
function em_wp_admin_filter_rubrique_update_footer(string $version): string
{
    if (!em_wp_admin_is_rubrique_screen()) {
        return $version;
    }

    return '';
}
add_filter('update_footer', 'em_wp_admin_filter_rubrique_update_footer');
