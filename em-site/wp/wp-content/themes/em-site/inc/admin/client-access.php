<?php
/**
 * Acces admin client (admin-ellene) vs acces total (admin-tyson).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_admin_user_login(): string
{
    $user = wp_get_current_user();

    if (!$user || empty($user->user_login)) {
        return '';
    }

    return (string) $user->user_login;
}

function em_site_admin_is_power_user(): bool
{
    return strtolower(em_site_admin_user_login()) === 'admin-tyson';
}

function em_site_admin_is_ellene_admin(): bool
{
    return strtolower(em_site_admin_user_login()) === 'admin-ellene';
}

function em_site_admin_should_limit_ellene_client(): bool
{
    return is_admin()
        && current_user_can('manage_options')
        && em_site_admin_is_ellene_admin()
        && !em_site_admin_is_power_user();
}

function em_site_limit_admin_menu_for_ellene_admin(): void
{
    if (!em_site_admin_should_limit_ellene_client()) {
        return;
    }

    $remove = [
        'edit.php',
        'edit.php?post_type=page',
        'edit-comments.php',
        'users.php',
        'tools.php',
        'plugins.php',
        'site-editor.php',
        'edit.php?post_type=wp_block',
    ];

    foreach ($remove as $slug) {
        remove_menu_page($slug);
    }
}
add_action('admin_menu', 'em_site_limit_admin_menu_for_ellene_admin', 999);

function em_site_limit_appearance_submenu_for_ellene_admin(): void
{
    if (!em_site_admin_should_limit_ellene_client()) {
        return;
    }

    global $submenu;

    if (empty($submenu['themes.php']) || !is_array($submenu['themes.php'])) {
        return;
    }

    foreach ($submenu['themes.php'] as $index => $submenu_item) {
        $slug = isset($submenu_item[2]) ? (string) $submenu_item[2] : '';

        if ($slug !== 'themes.php') {
            unset($submenu['themes.php'][$index]);
        }
    }
}
add_action('admin_menu', 'em_site_limit_appearance_submenu_for_ellene_admin', 1001);

function em_site_limit_settings_submenu_for_ellene_admin(): void
{
    if (!em_site_admin_should_limit_ellene_client()) {
        return;
    }

    global $submenu;

    if (empty($submenu['options-general.php']) || !is_array($submenu['options-general.php'])) {
        return;
    }

    foreach ($submenu['options-general.php'] as $index => $submenu_item) {
        $slug = isset($submenu_item[2]) ? (string) ($submenu_item[2] ?? '') : '';

        if ($slug !== 'options-general.php') {
            unset($submenu['options-general.php'][$index]);
        }
    }
}
add_action('admin_menu', 'em_site_limit_settings_submenu_for_ellene_admin', 1000);

function em_site_redirect_blocked_admin_pages_for_ellene_admin(): void
{
    if (!em_site_admin_should_limit_ellene_client()) {
        return;
    }

    global $pagenow;

    $blocked_pagenow = [
        'customize.php',
        'site-editor.php',
        'theme-editor.php',
        'nav-menus.php',
        'edit.php',
        'edit-comments.php',
        'users.php',
        'tools.php',
        'plugins.php',
        'plugin-install.php',
        'plugin-editor.php',
        'theme-install.php',
        'options-writing.php',
        'options-reading.php',
        'options-discussion.php',
        'options-media.php',
        'options-permalink.php',
        'options-privacy.php',
    ];

    if (in_array((string) $pagenow, $blocked_pagenow, true)) {
        $redirect = str_starts_with((string) $pagenow, 'options-')
            ? admin_url('options-general.php')
            : admin_url('themes.php');
        wp_safe_redirect($redirect);
        exit;
    }

    $post_type = sanitize_key((string) ($_GET['post_type'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($post_type === 'page' || $post_type === 'wp_block') {
        wp_safe_redirect(admin_url('themes.php'));
        exit;
    }

    $page = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($page !== '' && in_array($page, ['gutenberg-fonts', 'fonts', 'customize'], true)) {
        wp_safe_redirect(admin_url('themes.php'));
        exit;
    }
}
add_action('admin_init', 'em_site_redirect_blocked_admin_pages_for_ellene_admin', 20);

function em_site_limit_admin_bar_for_ellene_admin($wp_admin_bar): void
{
    if (!em_site_admin_should_limit_ellene_client()) {
        return;
    }

    $remove = [
        'wp-logo',
        'about',
        'wporg',
        'documentation',
        'support-forums',
        'feedback',
        'new-content',
        'new-post',
        'new-page',
        'new-user',
        'new-media',
        'comments',
        'customize',
        'command-palette',
    ];

    foreach ($remove as $node_id) {
        $wp_admin_bar->remove_node($node_id);
    }
}
add_action('admin_bar_menu', 'em_site_limit_admin_bar_for_ellene_admin', 999);

function em_site_strip_admin_bar_howdy($wp_admin_bar): void
{
    $node = $wp_admin_bar->get_node('my-account');
    if (!$node || !is_string($node->title) || $node->title === '') {
        return;
    }

    $node->title = preg_replace('#^(?:Howdy|Bonjour),\s*#iu', '', $node->title);
    $wp_admin_bar->add_node($node);
}
add_action('admin_bar_menu', 'em_site_strip_admin_bar_howdy', 9999);

function em_site_ellene_admin_disable_command_palette($load)
{
    if (em_site_admin_should_limit_ellene_client()) {
        return false;
    }

    return $load;
}
add_filter('should_load_command_palette', 'em_site_ellene_admin_disable_command_palette');

function em_site_ellene_admin_remove_help_tabs(): void
{
    if (!em_site_admin_should_limit_ellene_client()) {
        return;
    }

    global $pagenow;

    if (!in_array((string) $pagenow, ['profile.php', 'themes.php'], true)) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ($screen instanceof WP_Screen) {
        $screen->remove_help_tabs();
    }
}
add_action('admin_head', 'em_site_ellene_admin_remove_help_tabs', 999);

function em_site_ellene_admin_access_fallback_css(): void
{
    if (!em_site_admin_should_limit_ellene_client()) {
        return;
    }
    ?>
    <style id="em-site-ellene-admin-access">
        #wpadminbar #wp-admin-bar-wp-logo,
        #wpadminbar #wp-admin-bar-new-content,
        #wpadminbar #wp-admin-bar-new-post,
        #wpadminbar #wp-admin-bar-new-page,
        #wpadminbar #wp-admin-bar-new-user,
        #wpadminbar #wp-admin-bar-new-media,
        #wpadminbar #wp-admin-bar-command-palette,
        #adminmenu a[href*="customize.php"],
        #adminmenu a[href*="site-editor.php"],
        #adminmenu a[href*="nav-menus.php"],
        #adminmenu a[href*="theme-editor.php"],
        #adminmenu a[href*="post_type=wp_block"],
        #adminmenu a[href*="fonts"],
        #adminmenu a[href="edit.php"],
        #adminmenu a[href="edit.php?post_type=page"],
        #adminmenu a[href="edit-comments.php"],
        #adminmenu a[href="users.php"],
        #adminmenu a[href="tools.php"],
        #adminmenu a[href="plugins.php"],
        #adminmenu a[href*="plugin-install.php"],
        #adminmenu a[href="options-writing.php"],
        #adminmenu a[href="options-reading.php"],
        #adminmenu a[href="options-discussion.php"],
        #adminmenu a[href="options-media.php"],
        #adminmenu a[href="options-permalink.php"],
        #adminmenu a[href="options-privacy.php"] {
            display: none !important;
        }
    </style>
    <?php
}
add_action('admin_head', 'em_site_ellene_admin_access_fallback_css', 100);
