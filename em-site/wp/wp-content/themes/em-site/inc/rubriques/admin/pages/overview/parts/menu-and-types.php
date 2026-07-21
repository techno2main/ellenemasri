<?php
/**
 * Page Rubriques EM-SITE (admin) — modèle simplifié.
 *
 * Par rubrique : la liste des footers (items). Chaque footer s'édite en une
 * seule étape (structure + contenu + couleurs + aperçu temps réel) via le
 * builder. Plus de notion de « modèle ». Additif, sans impact sur le front.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre la page (menu top-level dédié).
 */
function em_site_overview_menu(): void
{
    // Placé sous le bloc « Rubriques du site » (après son séparateur bas) pour
    // ne pas se mélanger aux modules de rubriques (qui occupent la plage 55→62).
    $position = 64;
    if (function_exists('em_site_admin_menu_separator_bottom_position')) {
        $position = em_site_admin_menu_separator_bottom_position() + 1;
    }

    add_menu_page(
        __('RUBRIQUES', 'em-site'),
        __('RUBRIQUES', 'em-site'),
        'manage_options',
        'em-rubriques-overview',
        'em_site_overview_render',
        'dashicons-screenoptions',
        $position
    );

}
add_action('admin_menu', 'em_site_overview_menu', 100);

// Masque les sous-menus sous RUBRIQUES: on conserve uniquement l'entrée parent.
add_action('admin_menu', static function (): void {
    remove_submenu_page('em-rubriques-overview', 'em-rubriques-overview');
}, 999);

/**
 * Assets de la page Rubriques EM-SITE (inclut le header admin partage).
 */
function em_site_overview_enqueue_assets(string $hook_suffix): void
{
    unset($hook_suffix);
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page = sanitize_key((string) ($_GET['page'] ?? ''));
    if ($page !== 'em-rubriques-overview') {
        return;
    }

    if (function_exists('em_site_admin_hub_cards_enqueue_assets')) {
        em_site_admin_hub_cards_enqueue_assets();
    } elseif (function_exists('em_site_admin_enqueue_shared_assets')) {
        em_site_admin_enqueue_shared_assets();
    }

    $base_rel = '/assets/admin/css/rubriques-overview/';
    $base_abs = get_template_directory() . $base_rel;
    $styles = [
        'fields-controls.css',
        'summary-directory-top.css',
        'summary-directory-meta-and-focus.css',
        'cards-and-items.css',
        'builder-rows.css',
        'module-chips.css',
        'savebar-and-preview.css',
    ];

    $last_handle = '';
    foreach ($styles as $style_file) {
        $style_abs = $base_abs . $style_file;
        if (!is_readable($style_abs)) {
            continue;
        }

        $handle = 'em-site-rubriques-overview-' . sanitize_title(str_replace('.css', '', $style_file));
        wp_enqueue_style(
            $handle,
            get_template_directory_uri() . $base_rel . $style_file,
            [],
            (string) filemtime($style_abs)
        );
        $last_handle = $handle;
    }

    if ($last_handle !== '' && function_exists('em_site_admin_rubriques_preview_css')) {
        $preview_css = em_site_admin_rubriques_preview_css();
        if ($preview_css !== '') {
            wp_add_inline_style($last_handle, $preview_css);
        }
    }
}
add_action('admin_enqueue_scripts', 'em_site_overview_enqueue_assets');

/**
 * Option des rubriques masquées depuis l'overview (suppression safe).
 */
function em_site_hidden_rubriques_option_name(): string
{
    return 'em_site_hidden_rubriques';
}

/**
 * Slugs de rubriques masquées (suppression safe non destructive).
 *
 * @return array<int, string>
 */
function em_site_get_hidden_rubriques(): array
{
    $raw = get_option(em_site_hidden_rubriques_option_name(), []);

    if (!is_array($raw)) {
        return [];
    }

    $hidden = [];

    foreach ($raw as $slug) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || in_array($slug, ['top-bar', 'footer', 'headers'], true) || in_array($slug, $hidden, true)) {
            continue;
        }

        $hidden[] = $slug;
    }

    return $hidden;
}

/**
 * Types triés dans l'ordre des rubriques du site (HEADER absent du EM-SITE).
 *
 * @return array<string, array<string, mixed>>
 */
function em_site_ordered_types(): array
{
    $types = em_site_rubrique_type_registry();
    $ordered = [];
    $footer_type = null;
    $hidden_types = em_site_get_hidden_rubriques();

    foreach ($hidden_types as $hidden_slug) {
        unset($types[$hidden_slug]);
    }

    if (isset($types['footer'])) {
        $footer_type = $types['footer'];
        unset($types['footer']);
    }

    // Priorité UX: TOP-BAR puis HEADER avant HERO/SLIDERS.
    foreach (['top-bar', 'headers', 'header', 'sliders'] as $priority_slug) {
        if (isset($types[$priority_slug])) {
            $ordered[$priority_slug] = $types[$priority_slug];
            unset($types[$priority_slug]);
        }
    }

    // 1) Ordre personnalisé enregistré (glisser-déposer de l'aperçu) — prioritaire.
    foreach (em_site_get_rubrique_order() as $slug) {
        if (isset($types[$slug])) {
            $ordered[$slug] = $types[$slug];
            unset($types[$slug]);
        }
    }

    // 2) Repli : ordre des rubriques du site pour les non classées.
    if (function_exists('em_site_get_site_rubrique_order')) {
        foreach (em_site_get_site_rubrique_order() as $slug) {
            if (isset($types[$slug])) {
                $ordered[$slug] = $types[$slug];
                unset($types[$slug]);
            }
        }
    }

    // 3) Reste éventuel (types personnalisés non classés) en fin.
    $result = $ordered + $types;

    // Footer reste systématiquement tout en bas.
    if (is_array($footer_type)) {
        $result['footer'] = $footer_type;
    }

    return $result;
}

/**
 * Types de rubriques créés via l'admin (custom).
 *
 * @return array<string, true>
 */
function em_site_overview_custom_type_map(): array
{
    static $map = null;

    if (is_array($map)) {
        return $map;
    }

    $raw = function_exists('em_site_rubrique_types_option_name')
        ? get_option(em_site_rubrique_types_option_name(), [])
        : [];

    $map = [];

    if (is_array($raw)) {
        foreach ($raw as $slug => $definition) {
            $slug = sanitize_key((string) $slug);

            if ($slug === '' || !is_array($definition)) {
                continue;
            }

            $map[$slug] = true;
        }
    }

    return $map;
}

/**
 * URL canonique du sommaire Rubriques.
 */
function em_site_overview_summary_url(): string
{
    return (string) admin_url('admin.php?page=em-rubriques-overview');
}

/**
 * Barre discrète de retour au sommaire en mode focus.
 *
 * @param array<string, mixed> $type
 */
