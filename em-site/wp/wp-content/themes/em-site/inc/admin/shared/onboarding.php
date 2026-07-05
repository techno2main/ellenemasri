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
    $catalog_legacy_enabled = function_exists('em_wp_catalog_legacy_admin_enabled')
        ? em_wp_catalog_legacy_admin_enabled()
        : false;

    $slugs = [
        em_wp_admin_dashboard_page_slug(),
    ];

    if (function_exists('em_wp_admin_media_parent_menu_slug')) {
        $slugs[] = em_wp_admin_media_parent_menu_slug();
    }

    if (function_exists('em_wp_admin_media_accordion_child_slugs')) {
        $slugs = array_merge($slugs, em_wp_admin_media_accordion_child_slugs());
    }

    if ($catalog_legacy_enabled && function_exists('em_wp_catalog_parent_menu_slug')) {
        $slugs[] = em_wp_catalog_parent_menu_slug();
    }

    if ($catalog_legacy_enabled && function_exists('em_wp_catalog_registered_hub_menu_slugs')) {
        $slugs = array_merge($slugs, em_wp_catalog_registered_hub_menu_slugs());
    }

    if ($catalog_legacy_enabled && function_exists('em_wp_catalog_sommaire_menu_slug')) {
        $slugs[] = em_wp_catalog_sommaire_menu_slug();
    }

    if (function_exists('em_wp_admin_template_parent_page_slug')) {
        $slugs[] = em_wp_admin_template_parent_page_slug();
    }

    if (function_exists('em_wp_admin_template_entry_page_slugs')) {
        $slugs = array_merge($slugs, em_wp_admin_template_entry_page_slugs());
    }

    if (function_exists('em_wp_admin_template_create_page_slug')) {
        $slugs[] = em_wp_admin_template_create_page_slug();
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

    if (function_exists('em_wp_video_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_video_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_stream_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_stream_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_social_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_social_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_top_bar_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_top_bar_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_release_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_release_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_cta_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_cta_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_footer_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_footer_style_definitions(), 'page_slug'));
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

    if (function_exists('em_wp_video_page_slug')) {
        $slugs[] = em_wp_video_page_slug();
    }

    if (function_exists('em_wp_cta_page_slug')) {
        $slugs[] = em_wp_cta_page_slug();
    }

    if (function_exists('em_wp_footer_page_slug')) {
        $slugs[] = em_wp_footer_page_slug();
    }

    if (function_exists('em_wp_top_bar_page_slug')) {
        $slugs[] = em_wp_top_bar_page_slug();
    }

    return array_values(array_unique(array_filter($slugs)));
}

/**
 * Slug de la page admin courante (admin.php?page=…).
 */
function em_wp_admin_current_admin_page_slug(): string
{
    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return '';
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return sanitize_key((string) ($_GET['page'] ?? ''));
}

/**
 * Page « zone neutre » (sommaire templates, dashboard, catalogues…) — pas en édition rubriques.
 */
function em_wp_admin_is_neutral_admin_page(?string $page_slug = null): bool
{
    if ($page_slug === null) {
        $page_slug = em_wp_admin_current_admin_page_slug();
    }

    $page_slug = sanitize_key((string) $page_slug);

    if ($page_slug === '') {
        return false;
    }

    return in_array($page_slug, em_wp_admin_neutral_page_slugs(), true);
}

/**
 * Indique si l'ancien bloc « Rubriques du site » (entrées par module : TOP-BAR,
 * HEADER, STREAM…) doit être visible dans le menu latéral.
 *
 * Déprécié : l'édition des rubriques se fait désormais sous le squelette via la
 * nouvelle gestion (V4, menu « RUBRIQUES » dédié). On masque donc toujours
 * l'ancien bloc par module dans la sidebar. La page squelette reste accessible
 * depuis les cartes TEMPLATES.
 */
function em_wp_admin_should_show_rubrique_menus(): bool
{
    return false;
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

    if (function_exists('em_wp_admin_rubriques_page_slug')) {
        $chrome_slugs[] = em_wp_admin_rubriques_page_slug();
    }

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

    // Ne jamais bloquer la navigation admin sur un nonce expiré : on redirige
    // proprement vers la cible demandée pour éviter un écran blanc en cours de clic menu.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $nonce = (string) ($_GET['_wpnonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'em_wp_quit_editing_nav')) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $fallback_redirect = esc_url_raw(wp_unslash((string) ($_GET['redirect_to'] ?? '')));
        $fallback_redirect = wp_validate_redirect($fallback_redirect, admin_url());

        wp_safe_redirect($fallback_redirect);
        exit;
    }

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
        $theme_uri . '/assets/admin/js/shared/modals/confirm-modal.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/modals/confirm-modal.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-module-form-dirty-engine',
        $theme_uri . '/assets/admin/js/shared/state/module-form-dirty/engine.js',
        ['em-wp-admin-confirm-modal'],
        em_wp_admin_asset_version('assets/admin/js/shared/state/module-form-dirty/engine.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-module-form-dirty',
        $theme_uri . '/assets/admin/js/shared/state/module-form-dirty.js',
        ['em-wp-admin-confirm-modal', 'em-wp-admin-module-form-dirty-engine'],
        em_wp_admin_asset_version('assets/admin/js/shared/state/module-form-dirty.js'),
        true
    );

    wp_localize_script(
        'em-wp-admin-module-form-dirty',
        'EmWpModuleFormDirtyConfig',
        [
            'i18n' => [
                'saveConfirm' => __('Enregistrer la configuration actuelle et continuer ?', 'em-wp'),
                'saveLabel'   => __('Enregistrer', 'em-wp'),
                'cancelLabel' => __('Annuler', 'em-wp'),
            ],
        ]
    );

    wp_enqueue_script(
        'em-wp-admin-quit-editing-nav',
        $theme_uri . '/assets/admin/js/shared/navigation/quit-editing-nav.js',
        ['em-wp-admin-confirm-modal', 'em-wp-admin-module-form-dirty'],
        em_wp_admin_asset_version('assets/admin/js/shared/navigation/quit-editing-nav.js'),
        true
    );

    $template_label = function_exists('em_wp_get_editing_template_label')
        ? (string) em_wp_get_editing_template_label()
        : '';

    wp_localize_script(
        'em-wp-admin-quit-editing-nav',
        'EmWpQuitEditingNav',
        [
            'rubriqueSlugs' => em_wp_admin_template_scoped_page_slugs(),
            'quitEndpoint'  => admin_url('admin.php'),
            'nonce'         => wp_create_nonce('em_wp_quit_editing_nav'),
            'strings'       => [
                'messageTemplate' => __('Tu vas quitter l\'édition de ton template « %s ».', 'em-wp'),
                'templateLabel'   => $template_label,
                'confirmQuit'     => __('Quitter', 'em-wp'),
                'confirmSaveQuit' => __('Enregistrer & Quitter', 'em-wp'),
                'stay'            => __('Rester', 'em-wp'),
                'saveConfirm'     => __('Enregistrer la configuration actuelle et continuer ?', 'em-wp'),
                'saveLabel'       => __('Enregistrer', 'em-wp'),
                'saveCancel'      => __('Annuler', 'em-wp'),
            ],
        ]
    );
}
add_action('admin_enqueue_scripts', 'em_wp_admin_enqueue_quit_editing_nav_guard');
