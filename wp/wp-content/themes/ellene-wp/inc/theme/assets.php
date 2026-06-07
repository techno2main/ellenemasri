<?php

/**
 * Frontend and admin asset loading.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_enqueue_assets() {
    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        array(),
        '6.5.1'
    );

    wp_enqueue_style(
        'ellene-wp-tailwind',
        get_template_directory_uri() . '/style-compiled.css',
        array(),
        '1.0.0'
    );

    wp_add_inline_style(
        'ellene-wp-tailwind',
        'img, video, iframe { -webkit-user-drag: none; -webkit-touch-callout: none; user-select: none; }'
    );

    $stream_player_js_path = get_template_directory() . '/assets/stream-player.js';
    $content_protection_js_path = get_template_directory() . '/assets/content-protection.js';

    wp_enqueue_script(
        'ellene-wp-stream-player',
        get_template_directory_uri() . '/assets/stream-player.js',
        array(),
        file_exists($stream_player_js_path) ? (string) filemtime($stream_player_js_path) : '1.0.0',
        true
    );

    wp_enqueue_script(
        'ellene-wp-content-protection',
        get_template_directory_uri() . '/assets/content-protection.js',
        array(),
        file_exists($content_protection_js_path) ? (string) filemtime($content_protection_js_path) : '1.0.0',
        true
    );
}

add_action('wp_enqueue_scripts', 'ellene_wp_enqueue_assets');

function ellene_wp_enqueue_admin_assets($hook) {
    $is_landing_page = ('toplevel_page_ellene-wp_landing_options' === $hook);
    $is_visual_links_page = (strpos((string) $hook, 'ellene_wp_visual_links') !== false);

    if (!$is_landing_page && !$is_visual_links_page) {
        return;
    }

    wp_enqueue_media();

    if (!$is_landing_page) {
        return;
    }

    $admin_css_path = get_template_directory() . '/assets/admin-nav.css';
    $admin_js_path = get_template_directory() . '/assets/admin-nav.js';
    $visual_links_admin_css_path = get_template_directory() . '/assets/admin-visual-links-builder.css';
    $visual_links_admin_js_path = get_template_directory() . '/assets/admin-visual-links-builder.js';

    wp_enqueue_style(
        'ellene-wp-admin-nav',
        get_template_directory_uri() . '/assets/admin-nav.css',
        array(),
        file_exists($admin_css_path) ? (string) filemtime($admin_css_path) : '1.0.0'
    );

    wp_enqueue_script(
        'ellene-wp-admin-nav',
        get_template_directory_uri() . '/assets/admin-nav.js',
        array(),
        file_exists($admin_js_path) ? (string) filemtime($admin_js_path) : '1.0.0',
        true
    );

    wp_enqueue_style(
        'ellene-wp-admin-visual-links-builder',
        get_template_directory_uri() . '/assets/admin-visual-links-builder.css',
        array('ellene-wp-admin-nav'),
        file_exists($visual_links_admin_css_path) ? (string) filemtime($visual_links_admin_css_path) : '1.0.0'
    );

    wp_enqueue_script(
        'ellene-wp-admin-visual-links-builder',
        get_template_directory_uri() . '/assets/admin-visual-links-builder.js',
        array('jquery', 'media-editor', 'media-views', 'wp-util'),
        file_exists($visual_links_admin_js_path) ? (string) filemtime($visual_links_admin_js_path) : '1.0.0',
        true
    );
}

add_action('admin_enqueue_scripts', 'ellene_wp_enqueue_admin_assets');

function ellene_wp_hide_wp_footer_text_on_landing($text) {
    $screen = get_current_screen();

    if ($screen && (
        $screen->id === 'toplevel_page_ellene-wp_landing_options' ||
        strpos($screen->id, 'ellene_wp_visual_links') !== false
    )) {
        return '';
    }

    return $text;
}

add_filter('admin_footer_text', 'ellene_wp_hide_wp_footer_text_on_landing', 20);

function ellene_wp_media_modal_edit_button() {
    $screen = get_current_screen();

    if (!$screen || $screen->base !== 'upload') {
        return;
    }

    $current_user = wp_get_current_user();

    if ($current_user && $current_user->user_login === 'admin-my') {
        return;
    }
    ?>
    <script>
    (function() {
        function injectEditButton() {
            var sidebar = document.querySelector('.attachment-details .details');
            if (!sidebar || sidebar.querySelector('.ellene-wp-edit-btn')) return;

            var editLink = sidebar.querySelector('a.edit-attachment');
            if (!editLink) return;

            var btn = document.createElement('a');
            btn.href = editLink.href;
            btn.className = 'button button-primary ellene-wp-edit-btn';
            btn.textContent = 'Modifier / Enregistrer';
            btn.style.cssText = 'display:block;text-align:center;margin:12px 0 4px;width:100%;box-sizing:border-box;';
            sidebar.insertBefore(btn, editLink);
        }

        var observer = new MutationObserver(function() {
            injectEditButton();
        });

        observer.observe(document.body, { childList: true, subtree: true });
    })();
    </script>
    <?php
}

add_action('admin_footer', 'ellene_wp_media_modal_edit_button');

function ellene_wp_limit_admin_menu_for_client() {
    if (!is_admin()) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    $current_user = wp_get_current_user();

    if (!$current_user || empty($current_user->user_login)) {
        return;
    }

    if ($current_user->user_login === 'admin-my') {
        return;
    }

    remove_menu_page('index.php');
    remove_menu_page('edit.php');
    remove_menu_page('edit-comments.php');
    remove_menu_page('edit.php?post_type=page');
    remove_menu_page('themes.php');
    remove_menu_page('plugins.php');
    remove_menu_page('users.php');
    remove_menu_page('tools.php');
    remove_menu_page('options-general.php');
}

add_action('admin_menu', 'ellene_wp_limit_admin_menu_for_client', 999);

function ellene_wp_client_login_redirect($redirect_to, $requested_redirect_to, $user) {
    if (is_wp_error($user) || empty($user->user_login)) {
        return $redirect_to;
    }

    if ($user->user_login === 'admin-my') {
        return $redirect_to;
    }

    return admin_url('admin.php?page=ellene-wp_landing_options');
}

add_filter('login_redirect', 'ellene_wp_client_login_redirect', 10, 3);

function ellene_wp_limit_admin_bar_for_client($wp_admin_bar) {
    if (!is_admin_bar_showing()) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    $current_user = wp_get_current_user();

    if (!$current_user || empty($current_user->user_login)) {
        return;
    }

    if ($current_user->user_login === 'admin-my') {
        return;
    }

    $wp_admin_bar->remove_node('new-content');
    $wp_admin_bar->remove_node('new-post');
    $wp_admin_bar->remove_node('new-page');
    $wp_admin_bar->remove_node('new-user');
    $wp_admin_bar->remove_node('new-media');

    $wp_admin_bar->add_node(array(
        'id'    => 'ellene-wp-new-media',
        'title' => 'Ajouter un media',
        'href'  => admin_url('media-new.php'),
        'meta'  => array(
            'title' => 'Ajouter un media',
        ),
    ));

    $wp_admin_bar->remove_node('comments');
    $wp_admin_bar->remove_node('customize');
}

add_action('admin_bar_menu', 'ellene_wp_limit_admin_bar_for_client', 999);

function ellene_wp_redirect_admin_bar_edit_to_landing($wp_admin_bar) {
    if (!is_admin_bar_showing() || is_admin()) {
        return;
    }

    if (!current_user_can('manage_options') || !is_front_page()) {
        return;
    }

    $current_user = wp_get_current_user();

    if (!$current_user || empty($current_user->user_login)) {
        return;
    }

    if ($current_user->user_login === 'admin-my') {
        return;
    }

    $edit_node = $wp_admin_bar->get_node('edit');
    if (!$edit_node) {
        return;
    }

    $edit_node->href = admin_url('admin.php?page=ellene-wp_landing_options');
    $wp_admin_bar->add_node($edit_node);
}

add_action('admin_bar_menu', 'ellene_wp_redirect_admin_bar_edit_to_landing', 1001);

