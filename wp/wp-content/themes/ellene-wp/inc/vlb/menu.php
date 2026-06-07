<?php

/**
 * Visual Links admin helpers and AJAX handlers.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_visual_links_html_menu() {
    $root_slug = 'mayami_visual_links_builder';

    add_menu_page(
        'VISUAL LINKS BUILDER (VLB)',
        'VLB',
        'manage_options',
        $root_slug,
        'mayami_render_visual_links_html_builder_page',
        'dashicons-format-image',
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
    return is_array($store) ? $store : array();
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
