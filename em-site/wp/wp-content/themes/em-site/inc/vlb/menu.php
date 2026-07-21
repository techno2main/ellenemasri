<?php

/**
 * Visual Links admin helpers and AJAX handlers.
 *
 * @package ClientWp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option de visibilité du menu VLB pour admin-ellene.
 */
function em_site_vlb_visible_for_admin_ellene(): bool
{
    return (bool) get_option('em_site_vlb_visible_for_admin_ellene', false);
}

/**
 * Login admin ciblé par la règle VLB.
 */
function em_site_vlb_target_login_for_visibility(): string
{
    return 'admin-ellene';
}

/**
 * Indique si le VLB doit être masqué pour l'utilisateur courant.
 */
function em_site_vlb_should_hide_for_current_user(): bool
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return false;
    }

    $current_login = function_exists('em_site_admin_user_login')
        ? em_site_admin_user_login()
        : strtolower((string) (wp_get_current_user()->user_login ?? ''));

    if ($current_login !== em_site_vlb_target_login_for_visibility()) {
        return false;
    }

    return !em_site_vlb_visible_for_admin_ellene();
}

/**
 * URL d'action pour le toggle VLB (admin-tyson).
 */
function em_site_vlb_toggle_for_admin_ellene_url(): string
{
    $url = add_query_arg(
        [
            'action' => 'em_site_toggle_vlb_for_admin_ellene',
            'redirect_to' => rawurlencode(
                function_exists('em_site_admin_dashboard_admin_url')
                    ? em_site_admin_dashboard_admin_url()
                    : admin_url('index.php')
            ),
        ],
        admin_url('admin-post.php')
    );

    return wp_nonce_url($url, 'em_site_toggle_vlb_for_admin_ellene');
}

/**
 * Toggle visibilité VLB pour admin-ellene (réservé admin-tyson).
 */
function em_site_toggle_vlb_for_admin_ellene(): void
{
    if (!current_user_can('manage_options') || !function_exists('em_site_admin_is_power_user') || !em_site_admin_is_power_user()) {
        wp_die(esc_html__('You are not allowed to do this.', 'em-site'), 403);
    }

    check_admin_referer('em_site_toggle_vlb_for_admin_ellene');

    $current = em_site_vlb_visible_for_admin_ellene();
    update_option('em_site_vlb_visible_for_admin_ellene', $current ? 0 : 1, false);

    $redirect_to = isset($_GET['redirect_to'])
        ? rawurldecode(sanitize_text_field(wp_unslash((string) $_GET['redirect_to'])))
        : '';

    if ($redirect_to === '' || !wp_http_validate_url($redirect_to)) {
        $redirect_to = function_exists('em_site_admin_dashboard_admin_url')
            ? em_site_admin_dashboard_admin_url()
            : admin_url('index.php');
    }

    wp_safe_redirect($redirect_to);
    exit;
}
add_action('admin_post_em_site_toggle_vlb_for_admin_ellene', 'em_site_toggle_vlb_for_admin_ellene');

/**
 * Masque les entrées VLB du menu latéral pour admin-ellene selon option.
 */
function em_site_vlb_hide_menu_for_target_user(): void
{
    if (!em_site_vlb_should_hide_for_current_user()) {
        return;
    }

    $vlb_slugs = [
        'mayami_visual_links_builder',
        'mayami_visual_links_builder_new',
        'mayami_visual_links_drafts',
    ];

    foreach ($vlb_slugs as $slug) {
        remove_menu_page($slug);
    }
}
add_action('admin_menu', 'em_site_vlb_hide_menu_for_target_user', 200000);

/**
 * Bloque l'accès direct aux pages VLB quand le menu est masqué.
 */
function em_site_vlb_block_hidden_pages_for_target_user(): void
{
    if (!em_site_vlb_should_hide_for_current_user()) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    $page = isset($_GET['page']) ? sanitize_key((string) $_GET['page']) : '';
    $blocked = [
        'mayami_visual_links_builder',
        'mayami_visual_links_builder_new',
        'mayami_visual_links_drafts',
        'mayami_visual_links_preview',
    ];

    if (!in_array($page, $blocked, true)) {
        return;
    }

    $redirect = function_exists('em_site_admin_dashboard_admin_url')
        ? em_site_admin_dashboard_admin_url()
        : admin_url('index.php');

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_init', 'em_site_vlb_block_hidden_pages_for_target_user', 20);

function mayami_register_visual_links_html_menu() {
    $root_slug = 'mayami_visual_links_builder';
    $vlb_icon = function_exists('em_site_site_icon') ? em_site_site_icon('vlb', 'dashicons-format-image') : 'dashicons-format-image';

    add_menu_page(
        'VISUAL LINKS BUILDER (VLB)',
        'VLB',
        'manage_options',
        $root_slug,
        'mayami_render_visual_links_html_builder_page',
        $vlb_icon,
        3
    );

    add_submenu_page(
        $root_slug,
        'Nouveau visuel',
        'Nouveau visuel',
        'manage_options',
        'mayami_visual_links_builder_new',
        'mayami_render_visual_links_new_submenu_page'
    );

    add_submenu_page(
        $root_slug,
        'Liste des visuels',
        'Liste des visuels',
        'manage_options',
        'mayami_visual_links_drafts',
        'mayami_render_visual_links_drafts_page'
    );

    add_submenu_page(
        null,
        'VISUAL LINKS BUILDER (VLB) - Preview',
        'VISUAL LINKS BUILDER (VLB) - Preview',
        'manage_options',
        'mayami_visual_links_preview',
        'mayami_render_visual_links_preview_page'
    );
}

add_action('admin_menu', 'mayami_register_visual_links_html_menu', 20);

function mayami_remove_visual_links_duplicate_submenu() {
    remove_submenu_page('mayami_visual_links_builder', 'mayami_visual_links_builder');
}

add_action('admin_menu', 'mayami_remove_visual_links_duplicate_submenu', 999);

function mayami_visual_links_redirect_legacy_admin_pages() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    if ($page === '') {
        return;
    }

    $targets = array(
        'mayami_epk_html_builder' => 'mayami_visual_links_builder',
        'mayami_epk_html_builder_new' => 'mayami_visual_links_builder_new',
        'mayami_epk_drafts' => 'mayami_visual_links_drafts',
    );

    if (!isset($targets[$page])) {
        return;
    }

    $target_page = $targets[$page];
    $redirect = add_query_arg(array('page' => $target_page), admin_url('admin.php'));

    $draft_id = isset($_GET['draft_id']) ? sanitize_text_field(wp_unslash($_GET['draft_id'])) : '';
    if ($draft_id !== '') {
        $redirect = add_query_arg(array('draft_id' => $draft_id), $redirect);
    }

    wp_safe_redirect($redirect);
    exit;
}

add_action('admin_init', 'mayami_visual_links_redirect_legacy_admin_pages', 5);

function mayami_render_visual_links_new_submenu_page() {
    if (isset($_GET['draft_id'])) {
        unset($_GET['draft_id']);
    }

    mayami_render_visual_links_html_builder_page();
}

function mayami_handle_delete_visual_links_draft() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to do this.', 'mayami'), 403);
    }

    check_admin_referer('mayami_delete_visual_links_draft');

    $draft_id = isset($_POST['draft_id']) ? sanitize_text_field(wp_unslash($_POST['draft_id'])) : '';
    if ($draft_id !== '') {
        $store = mayami_get_visual_links_drafts_store();
        if (isset($store[$draft_id])) {
            unset($store[$draft_id]);
            mayami_update_visual_links_drafts_store($store);
        }
    }

    wp_safe_redirect(admin_url('admin.php?page=mayami_visual_links_drafts'));
    exit;
}

add_action('admin_post_mayami_delete_visual_links_draft', 'mayami_handle_delete_visual_links_draft');

function mayami_get_visual_links_drafts_store() {
    $store = get_option('mayami_visual_links_drafts_store', array());

    if (is_array($store) && !empty($store)) {
        return $store;
    }

    // Compat legacy: certains environnements ont encore les drafts sous l'ancien nom d'option.
    $legacy_store = get_option('mayami_epk_drafts_store', array());

    if (!is_array($legacy_store) || empty($legacy_store)) {
        return is_array($store) ? $store : array();
    }

    mayami_update_visual_links_drafts_store($legacy_store);

    return $legacy_store;
}

function mayami_update_visual_links_drafts_store($store) {
    if (!is_array($store)) {
        $store = array();
    }

    return update_option('mayami_visual_links_drafts_store', $store, false);
}

function mayami_visual_links_handle_legacy_draft_page_slugs() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    if ($page === '' || strpos($page, 'mayami_epk_draft_') !== 0) {
        return;
    }

    $store = mayami_get_visual_links_drafts_store();
    foreach ($store as $draft) {
        $draft_id = isset($draft['id']) ? (string) $draft['id'] : '';
        if ($draft_id === '') {
            continue;
        }

        $legacy_slug = 'mayami_epk_draft_' . substr(md5($draft_id), 0, 12);
        if ($legacy_slug === $page) {
            wp_safe_redirect(admin_url('admin.php?page=mayami_visual_links_builder&draft_id=' . rawurlencode($draft_id)));
            exit;
        }
    }

    wp_safe_redirect(admin_url('admin.php?page=mayami_visual_links_drafts'));
    exit;
}

add_action('admin_init', 'mayami_visual_links_handle_legacy_draft_page_slugs');
