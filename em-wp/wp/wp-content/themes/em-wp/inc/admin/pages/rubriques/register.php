<?php
/**
 * Enregistrement page Rubriques + assets.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre la page sommaire dans le menu admin.
 */
function em_wp_admin_rubriques_add_admin_page(): void
{
    add_menu_page(
        __('Rubriques', 'em-wp'),
        __('RUBRIQUES', 'em-wp'),
        'manage_options',
        em_wp_admin_rubriques_page_slug(),
        'em_wp_admin_render_rubriques_page',
        'dashicons-admin-home',
        em_wp_admin_menu_section_label_position()
    );
}
add_action('admin_menu', 'em_wp_admin_rubriques_add_admin_page');

/**
 * Retire le sous-menu dupliqué créé automatiquement par WordPress.
 */
function em_wp_admin_rubriques_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_admin_rubriques_page_slug(), em_wp_admin_rubriques_page_slug());
}
add_action('admin_menu', 'em_wp_admin_rubriques_remove_duplicate_submenu', 999);

/**
 * Charge les assets de la page sommaire.
 */
function em_wp_admin_rubriques_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug !== em_wp_admin_rubriques_page_slug()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    em_wp_admin_hub_cards_enqueue_assets();

    if (!em_wp_admin_has_template_context()) {
        return;
    }

    wp_enqueue_script(
        'em-wp-admin-slide-sortable',
        $theme_uri . '/assets/admin/js/shared/slide-sortable.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/slide-sortable.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-rubriques',
        $theme_uri . '/assets/admin/js/pages/rubriques.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/pages/rubriques.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-rubriques-sortable',
        $theme_uri . '/assets/admin/js/pages/rubriques-sortable.js',
        ['em-wp-admin-slide-sortable'],
        em_wp_admin_asset_version('assets/admin/js/pages/rubriques-sortable.js'),
        true
    );

    wp_localize_script(
        'em-wp-admin-rubriques-sortable',
        'emWpRubriquesSortable',
        [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('em_wp_rubrique_order'),
            'templateSlug' => function_exists('em_wp_get_editing_template_slug')
                ? em_wp_get_editing_template_slug()
                : '',
            'i18n'    => [
                'saved'                 => __('Ordre enregistré.', 'em-wp'),
                'error'                 => __('Impossible d\'enregistrer l\'ordre.', 'em-wp'),
                'handle'                => __('Réordonner', 'em-wp'),
                'swapHeroSlider'        => __('Inverser Hero et Slider dans HEADER', 'em-wp'),
                'layoutSaved'           => __('Layout HEADER enregistré.', 'em-wp'),
                'layoutError'           => __('Impossible d\'enregistrer le layout HEADER.', 'em-wp'),
                'visibilityShown'       => __('Afficher sur le site', 'em-wp'),
                'visibilityHidden'      => __('Masquer sur le site', 'em-wp'),
                'visibilityHiddenLabel' => __('Masqué', 'em-wp'),
                'visibilitySaved'       => __('Visibilité enregistrée.', 'em-wp'),
                'visibilityError'       => __('Impossible d\'enregistrer la visibilité.', 'em-wp'),
            ],
        ]
    );
}
add_action('admin_enqueue_scripts', 'em_wp_admin_rubriques_enqueue');
