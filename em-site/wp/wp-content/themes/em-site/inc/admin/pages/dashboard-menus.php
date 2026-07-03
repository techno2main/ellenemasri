<?php
/**
 * Menus latéraux admin (Lot B) pour rapprochement source.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_admin_templates_page_slug(): string
{
    return 'em-site-templates';
}

function em_site_admin_rubriques_page_slug(): string
{
    return 'em-wp-v4-overview';
}

function em_site_admin_vlb_page_slug(): string
{
    return 'em-site-vlb';
}

function em_site_admin_register_secondary_menus(): void
{
    add_menu_page(
        __('Templates', 'em-wp'),
        __('TEMPLATES', 'em-wp'),
        'manage_options',
        em_site_admin_templates_page_slug(),
        'em_site_admin_render_templates_page',
        'dashicons-layout',
        55
    );

    add_menu_page(
        __('Rubriques', 'em-wp'),
        __('RUBRIQUES', 'em-wp'),
        'manage_options',
        em_site_admin_rubriques_page_slug(),
        'em_site_admin_render_rubriques_page',
        'dashicons-grid-view',
        56
    );

    add_menu_page(
        __('Visual Links Builder', 'em-wp'),
        __('VLB', 'em-wp'),
        'manage_options',
        em_site_admin_vlb_page_slug(),
        'em_site_admin_render_vlb_page',
        'dashicons-admin-links',
        57
    );
}
add_action('admin_menu', 'em_site_admin_register_secondary_menus', 20);

function em_site_admin_rename_native_menus(): void
{
    global $menu;

    if (!is_array($menu)) {
        return;
    }

    foreach ($menu as $index => $item) {
        if (!is_array($item)) {
            continue;
        }

        $slug = (string) ($item[2] ?? '');

        if ($slug === 'upload.php') {
            $menu[$index][0] = __('MEDIAS', 'em-wp');
            continue;
        }

        if ($slug === 'options-general.php') {
            $menu[$index][0] = __('PARAMÈTRES', 'em-wp');
        }
    }
}
add_action('admin_menu', 'em_site_admin_rename_native_menus', 999);

function em_site_admin_render_templates_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap"><h1>TEMPLATES</h1><p>Lot C à venir: branchement écran templates identique source.</p></div>';
}

function em_site_admin_render_rubriques_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap"><h1>RUBRIQUES</h1><p>Lot C à venir: branchement écran rubriques identique source.</p></div>';
}

function em_site_admin_render_vlb_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap"><h1>VLB</h1><p>Lot D à venir: branchement VLB identique source.</p></div>';
}
