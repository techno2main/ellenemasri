<?php
/**
 * Onboarding admin : contexte template, gating menu, garde d'accès.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pages accessibles sans contexte template (zone neutre).
 *
 * @return string[]
 */
function em_wp_admin_neutral_page_slugs(): array
{
    $slugs = [
        em_wp_admin_dashboard_page_slug(),
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

    if (function_exists('em_wp_admin_templates_page_slug')) {
        $slugs[] = em_wp_admin_templates_page_slug();
    }

    if (function_exists('em_wp_hero_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_hero_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_slider_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_slider_style_definitions(), 'page_slug'));
    }

    return array_values(array_unique(array_filter($slugs)));
}

/**
 * Pages nécessitant un contexte template explicite.
 *
 * @return string[]
 */
function em_wp_admin_template_scoped_page_slugs(): array
{
    $slugs = [em_wp_admin_rubriques_page_slug()];

    if (function_exists('em_wp_admin_site_rubrique_definitions')) {
        foreach (em_wp_admin_site_rubrique_definitions() as $definition) {
            $page_slug = (string) ($definition['page_slug'] ?? '');

            if ($page_slug !== '') {
                $slugs[] = $page_slug;
            }
        }
    }

    if (function_exists('em_wp_video_admin_page_slugs')) {
        $slugs = array_merge($slugs, em_wp_video_admin_page_slugs());
    }

    if (function_exists('em_wp_release_admin_page_slugs')) {
        $slugs = array_merge($slugs, em_wp_release_admin_page_slugs());
    }

    return array_values(array_unique(array_filter($slugs)));
}

/**
 * Indique si le menu Rubriques Template doit être visible.
 */
function em_wp_admin_should_show_rubrique_menus(): bool
{
    return em_wp_admin_has_template_context();
}

/**
 * Slugs menu à masquer sans contexte template.
 *
 * @return string[]
 */
function em_wp_admin_rubrique_menu_slugs_to_hide(): array
{
    return em_wp_admin_template_scoped_page_slugs();
}

/**
 * Masque les entrées Rubriques Template + modules sans contexte.
 */
function em_wp_admin_hide_rubrique_menus_without_context(): void
{
    if (em_wp_admin_should_show_rubrique_menus()) {
        return;
    }

    foreach (em_wp_admin_rubrique_menu_slugs_to_hide() as $page_slug) {
        remove_menu_page($page_slug);
    }
}
add_action('admin_menu', 'em_wp_admin_hide_rubrique_menus_without_context', 10003);

/**
 * Masque les filets du bloc rubriques sans contexte template.
 */
function em_wp_admin_hide_rubrique_chrome_without_context(): void
{
    if (em_wp_admin_should_show_rubrique_menus()) {
        return;
    }

    global $menu;

    $chrome_slugs = [
        'separator-em-wp-site-top',
        'separator-em-wp-bottom',
    ];

    foreach ($menu as $position => $item) {
        if (!is_array($item)) {
            continue;
        }

        if (in_array((string) ($item[2] ?? ''), $chrome_slugs, true)) {
            unset($menu[$position]);
        }
    }
}
add_action('admin_menu', 'em_wp_admin_hide_rubrique_chrome_without_context', 10004);

/**
 * Redirige les pages template-scoped si aucun contexte (sauf page Rubriques = choix template).
 */
function em_wp_admin_guard_template_scoped_pages(): void
{
    if (!current_user_can('manage_options') || em_wp_admin_has_template_context()) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug === '' || $page_slug === em_wp_admin_rubriques_page_slug() || $page_slug === em_wp_admin_template_parent_page_slug()) {
        return;
    }

    if (!in_array($page_slug, em_wp_admin_template_scoped_page_slugs(), true)) {
        return;
    }

    set_transient(
        'em_wp_template_admin_notice_' . get_current_user_id(),
        [
            'type'    => 'warning',
            'message' => __('Choisis d’abord un template à éditer depuis le menu TEMPLATES.', 'em-wp'),
        ],
        30
    );

    em_wp_admin_safe_redirect(em_wp_admin_template_choice_admin_url());
}
add_action('admin_init', 'em_wp_admin_guard_template_scoped_pages', 2);

/**
 * Indique si le bandeau template doit s'afficher (contexte + page template-scoped).
 */
function em_wp_admin_should_show_template_editing_banner(): bool
{
    if (!em_wp_admin_has_template_context()) {
        return false;
    }

    if (!function_exists('em_wp_admin_is_em_wp_screen') || !em_wp_admin_is_em_wp_screen()) {
        return false;
    }

    if (function_exists('em_wp_admin_is_catalog_screen') && em_wp_admin_is_catalog_screen()) {
        return false;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug === em_wp_admin_dashboard_page_slug()) {
        return false;
    }

    if (function_exists('em_wp_admin_template_parent_page_slug') && $page_slug === em_wp_admin_template_parent_page_slug()) {
        return false;
    }

    if (function_exists('em_wp_admin_template_entry_page_slugs') && in_array($page_slug, em_wp_admin_template_entry_page_slugs(), true)) {
        return false;
    }

    if (function_exists('em_wp_admin_templates_page_slug') && $page_slug === em_wp_admin_templates_page_slug()) {
        return false;
    }

    return in_array($page_slug, em_wp_admin_template_scoped_page_slugs(), true);
}

/**
 * URL de navigation avec confirmation « quitter l'édition » (menu admin).
 */
function em_wp_admin_quit_editing_nav_url(string $target_url): string
{
    return wp_nonce_url(
        add_query_arg(
            [
                'em_wp_quit_editing' => '1',
                'redirect_to'        => $target_url,
            ],
            admin_url('admin.php')
        ),
        'em_wp_quit_editing_nav'
    );
}

/**
 * Quitte l'édition template puis redirige (navigation menu hors rubriques).
 */
function em_wp_admin_handle_quit_editing_nav_request(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (sanitize_key((string) ($_GET['em_wp_quit_editing'] ?? '')) !== '1') {
        return;
    }

    check_admin_referer('em_wp_quit_editing_nav');

    em_wp_clear_editing_template_context();

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $redirect_to = esc_url_raw(wp_unslash((string) ($_GET['redirect_to'] ?? '')));
    $redirect_to = wp_validate_redirect($redirect_to, admin_url());

    wp_safe_redirect($redirect_to);
    exit;
}
add_action('admin_init', 'em_wp_admin_handle_quit_editing_nav_request', 0);

/**
 * Intercepte les clics menu admin hors rubriques quand une édition est en cours.
 */
function em_wp_admin_enqueue_quit_editing_nav_guard(): void
{
    if (!is_admin() || !current_user_can('manage_options') || !em_wp_admin_has_template_context()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    wp_enqueue_script(
        'em-wp-admin-confirm-modal',
        $theme_uri . '/assets/admin/js/shared/confirm-modal.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/confirm-modal.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-quit-editing-nav',
        $theme_uri . '/assets/admin/js/shared/quit-editing-nav.js',
        ['em-wp-admin-confirm-modal'],
        em_wp_admin_asset_version('assets/admin/js/shared/quit-editing-nav.js'),
        true
    );

    wp_localize_script(
        'em-wp-admin-quit-editing-nav',
        'EmWpQuitEditingNav',
        [
            'rubriqueSlugs' => em_wp_admin_template_scoped_page_slugs(),
            'quitEndpoint'  => admin_url('admin.php'),
            'nonce'         => wp_create_nonce('em_wp_quit_editing_nav'),
            'strings'       => [
                'message' => __('Tu es en train d’éditer un template. Quitter l’édition pour continuer ?', 'em-wp'),
                'confirm' => __('Quitter l’édition', 'em-wp'),
                'cancel'  => __('Rester', 'em-wp'),
            ],
        ]
    );
}
add_action('admin_enqueue_scripts', 'em_wp_admin_enqueue_quit_editing_nav_guard');
