<?php
/**
 * Page admin « Templates » (CRUD + template actif live).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug page admin parent Template.
 */
function em_site_admin_template_parent_page_slug(): string
{
    return 'em-template';
}

/**
 * Slug page admin Choix du Template (alias parent).
 */
function em_site_admin_template_choice_page_slug(): string
{
    return em_site_admin_template_parent_page_slug();
}

/**
 * URL page admin Choix du Template.
 */
function em_site_admin_template_choice_admin_url(): string
{
    return admin_url('admin.php?page=' . em_site_admin_template_choice_page_slug());
}

/**
 * Slug page admin d'un template enregistré (MAYAMI, CLIENT, …).
 */
function em_site_admin_template_entry_page_slug(string $template_slug): string
{
    return 'em-template-' . em_site_template_sanitize_slug($template_slug);
}

/**
 * Slugs des pages menu template enregistrées.
 *
 * @return string[]
 */
function em_site_admin_template_entry_page_slugs(): array
{
    if (function_exists('em_site_template_unique_mode_enabled') && em_site_template_unique_mode_enabled()) {
        return [];
    }

    $slugs = [];

    foreach (array_keys(em_site_template_registry()) as $template_slug) {
        $slugs[] = em_site_admin_template_entry_page_slug((string) $template_slug);
    }

    return array_values(array_unique($slugs));
}

/**
 * Slugs réservés au bloc Template (parent + entrées).
 *
 * @return string[]
 */
function em_site_admin_template_reserved_menu_slugs(): array
{
    $slugs = [em_site_admin_template_parent_page_slug()];

    if (function_exists('em_site_admin_templates_page_slug')) {
        $slugs[] = em_site_admin_templates_page_slug();
    }

    if (function_exists('em_site_admin_template_create_page_slug')) {
        $slugs[] = em_site_admin_template_create_page_slug();
    }

    return array_values(array_unique(array_merge($slugs, em_site_admin_template_entry_page_slugs())));
}

/**
 * Retourne le slug template depuis une page menu dédiée.
 */
function em_site_admin_template_slug_from_entry_page(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);
    $prefix = 'em-template-';

    if (!str_starts_with($page_slug, $prefix)) {
        return '';
    }

    $template_slug = em_site_template_sanitize_slug(substr($page_slug, strlen($prefix)));

    if ($template_slug === '' || !em_site_template_exists($template_slug)) {
        return '';
    }

    return $template_slug;
}

/**
 * Rendu page Choix du Template (menu Templates + accueil).
 */
function em_site_admin_render_template_choice_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $target_slug = function_exists('em_site_get_active_template_slug')
        ? em_site_template_sanitize_slug((string) em_site_get_active_template_slug())
        : '';

    if ($target_slug !== '' && function_exists('em_site_set_editing_template_slug')) {
        em_site_set_editing_template_slug($target_slug);
    }

    em_site_admin_safe_redirect(em_site_admin_rubriques_admin_url());
}

/**
 * Slug page admin gestion Templates (CRUD, hors menu).
 */
function em_site_admin_templates_page_slug(): string
{
    return 'em-templates';
}

/**
 * URL page admin Templates (Mes Templates + tableau enregistrés).
 */
function em_site_admin_templates_manage_admin_url(): string
{
    return em_site_admin_template_choice_admin_url() . '#em-site-templates-registered-title';
}

/**
 * URL page admin Templates (legacy CRUD masquée).
 */
function em_site_admin_templates_page_url(): string
{
    return admin_url('admin.php?page=' . em_site_admin_templates_page_slug());
}

/**
 * Enregistre le bloc menu Templates (TEMPLATES + entrées registry) + page CRUD masquée.
 */
function em_site_admin_templates_register_menu(): void
{
    add_menu_page(
        __('Template', 'em-site'),
        __('TEMPLATE', 'em-site'),
        'manage_options',
        em_site_admin_template_parent_page_slug(),
        'em_site_admin_render_template_choice_page',
        'dashicons-layout',
        em_site_admin_menu_templates_position()
    );

    $unique_mode = function_exists('em_site_template_unique_mode_enabled') && em_site_template_unique_mode_enabled();

    if (!$unique_mode) {
        foreach (em_site_template_registry() as $slug => $definition) {
            $menu_label = mb_strtoupper((string) ($definition['label'] ?? $slug));

            add_menu_page(
                $menu_label,
                $menu_label,
                'manage_options',
                em_site_admin_template_entry_page_slug($slug),
                'em_site_admin_render_template_entry_page',
                'dashicons-admin-appearance',
                em_site_admin_menu_position_for_template($slug)
            );
        }
    }

    add_submenu_page(
        null,
        __('Gérer les templates', 'em-site'),
        __('Gérer les templates', 'em-site'),
        'manage_options',
        em_site_admin_templates_page_slug(),
        'em_site_admin_render_templates_page'
    );

    add_submenu_page(
        null,
        __('Nouveau template', 'em-site'),
        __('Nouveau template', 'em-site'),
        'manage_options',
        em_site_admin_template_create_page_slug(),
        'em_site_admin_render_template_create_page'
    );
}
add_action('admin_menu', 'em_site_admin_templates_register_menu');

/**
 * Retire les sous-menus dupliqués WordPress.
 */
function em_site_admin_templates_remove_duplicate_submenu(): void
{
    $pages = array_merge(
        [em_site_admin_template_parent_page_slug()],
        em_site_admin_template_entry_page_slugs()
    );

    foreach ($pages as $page_slug) {
        remove_submenu_page($page_slug, $page_slug);
    }
}
add_action('admin_menu', 'em_site_admin_templates_remove_duplicate_submenu', 999);

/**
 * Démarre l'édition d'un template depuis son entrée menu (MAYAMI, CLIENT, …).
 */
function em_site_admin_template_entry_start_editing(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $template_slug = em_site_admin_template_slug_from_entry_page($page_slug);

    if ($template_slug === '') {
        return;
    }

    $result = em_site_set_editing_template_slug($template_slug);

    if (is_wp_error($result)) {
        set_transient(
            'em_site_template_admin_notice_' . get_current_user_id(),
            [
                'type'    => 'error',
                'message' => $result->get_error_message(),
            ],
            30
        );
        em_site_admin_safe_redirect(em_site_admin_template_choice_admin_url());
    }

    em_site_admin_safe_redirect(em_site_admin_rubriques_admin_url());
}
add_action('admin_init', 'em_site_admin_template_entry_start_editing', 1);

/**
 * Définit le template à éditer depuis un lien rubrique de carte (em_site_edit_template).
 *
 * Permet d'ouvrir directement la bonne rubrique du bon template depuis les blocs
 * Templates, sans passer par la sélection préalable d'un template.
 */
function em_site_admin_rubrique_set_editing_from_query(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $requested = isset($_GET['em_site_edit_template'])
        ? em_site_template_sanitize_slug((string) wp_unslash($_GET['em_site_edit_template']))
        : '';

    if ($requested === '' || !em_site_template_exists($requested)) {
        return;
    }

    em_site_set_editing_template_slug($requested);

    // Redirige vers l'URL nettoyée (sans le paramètre) pour ne pas le rejouer.
    em_site_admin_safe_redirect(remove_query_arg('em_site_edit_template'));
}
add_action('admin_init', 'em_site_admin_rubrique_set_editing_from_query', 1);

/**
 * Redirige l'ancien slug em-template-choice vers le parent TEMPLATES.
 */
function em_site_admin_template_redirect_legacy_choice_slug(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug !== 'em-template-choice') {
        return;
    }

    em_site_admin_safe_redirect(em_site_admin_template_choice_admin_url());
}
add_action('admin_init', 'em_site_admin_template_redirect_legacy_choice_slug', 1);

/**
 * Redirige l'ancien deeplink ?em_site_open=template-create vers la page création.
 */
function em_site_admin_template_redirect_legacy_create_deeplink(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $open = sanitize_key((string) ($_GET['em_site_open'] ?? ''));

    if ($open !== 'template-create') {
        return;
    }

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $legacy_pages = [
        em_site_admin_templates_page_slug(),
        em_site_admin_template_choice_page_slug(),
    ];

    if (!in_array($page_slug, $legacy_pages, true)) {
        return;
    }

    if (function_exists('em_site_template_unique_mode_enabled') && em_site_template_unique_mode_enabled()) {
        em_site_admin_safe_redirect(em_site_admin_template_choice_admin_url());
    }

    em_site_admin_safe_redirect(em_site_admin_template_create_admin_url());
}
add_action('admin_init', 'em_site_admin_template_redirect_legacy_create_deeplink', 1);

/**
 * Callback placeholder pour les entrées template (redirection admin_init).
 */
function em_site_admin_render_template_entry_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    em_site_admin_safe_redirect(em_site_admin_rubriques_admin_url());
}

/**
 * Assets page Templates.
 */
function em_site_admin_templates_enqueue(): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    $template_pages = [
        em_site_admin_templates_page_slug(),
        em_site_admin_template_choice_page_slug(),
        em_site_admin_template_create_page_slug(),
    ];

    if (!in_array($page_slug, $template_pages, true)) {
        return;
    }

    em_site_admin_enqueue_shared_assets();

    if ($page_slug === em_site_admin_template_create_page_slug()) {
        em_site_admin_hub_cards_enqueue_assets();

        wp_enqueue_style(
            'font-awesome-6',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
            [],
            '6.5.1'
        );

        wp_enqueue_style(
            'em-site-admin-template-list',
            get_template_directory_uri() . '/assets/admin/css/template/list-page.css',
            ['em-site-admin-hub-cards'],
            em_site_admin_asset_version('assets/admin/css/template/list-page.css')
        );

        em_site_admin_template_wizard_enqueue();
        em_site_admin_template_enqueue_new_template_launcher();

        return;
    }

    if ($page_slug === em_site_admin_template_choice_page_slug()) {
        em_site_admin_hub_cards_enqueue_assets();
        em_site_admin_hub_enqueue_template_live_switcher();

        wp_enqueue_style(
            'font-awesome-6',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
            [],
            '6.5.1'
        );

        wp_enqueue_style(
            'em-site-admin-template-list',
            get_template_directory_uri() . '/assets/admin/css/template/list-page.css',
            ['em-site-admin-hub-cards'],
            em_site_admin_asset_version('assets/admin/css/template/list-page.css')
        );

        wp_enqueue_script(
            'em-site-template-list-row-edit',
            get_template_directory_uri() . '/assets/admin/js/template/list-row-edit.js',
            ['em-site-admin-color-modal'],
            em_site_admin_asset_version('assets/admin/js/template/list-row-edit.js'),
            true
        );

        wp_enqueue_script(
            'em-site-template-list-delete',
            get_template_directory_uri() . '/assets/admin/js/template/list-delete-confirm.js',
            ['em-site-admin-confirm-modal'],
            em_site_admin_asset_version('assets/admin/js/template/list-delete-confirm.js'),
            true
        );

        wp_enqueue_script(
            'em-site-template-preview-thumb',
            get_template_directory_uri() . '/assets/admin/js/template/preview-thumb.js',
            [],
            em_site_admin_asset_version('assets/admin/js/template/preview-thumb.js'),
            true
        );

        em_site_admin_template_enqueue_new_template_launcher();

        return;
    }
}
add_action('admin_enqueue_scripts', 'em_site_admin_templates_enqueue');

require_once __DIR__ . '/render-templates-page.php';
require_once __DIR__ . '/new-template-modals.php';
require_once __DIR__ . '/create-page.php';

