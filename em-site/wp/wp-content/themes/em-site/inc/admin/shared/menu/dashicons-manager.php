<?php
/**
 * Page admin cachée : gestion Dashicons.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_admin_dashicons_manager_page_slug(): string
{
    return 'em-site-dashicons-manager';
}

function em_site_admin_dashicons_manager_admin_url(): string
{
    return admin_url('admin.php?page=' . em_site_admin_dashicons_manager_page_slug());
}

function em_site_admin_register_dashicons_manager_page(): void
{
    add_submenu_page(
        null,
        __('Icônes BO', 'em-site'),
        __('Icônes BO', 'em-site'),
        em_site_admin_menu_capability(),
        em_site_admin_dashicons_manager_page_slug(),
        'em_site_admin_render_dashicons_manager_page'
    );
}
add_action('admin_menu', 'em_site_admin_register_dashicons_manager_page', 50);

function em_site_admin_render_dashicons_manager_page(): void
{
    if (!current_user_can(em_site_admin_menu_capability())) {
        return;
    }

    require get_template_directory() . '/inc/shared/icons/dashicons-preview.php';
}
