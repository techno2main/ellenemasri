<?php
/**
 * Menus latéraux admin (Lot B) pour rapprochement source.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

// Fichier legacy conservé pour historique. Les menus actifs sont dans pages/dashboard/* et pages/rubriques/*.
return;

function em_site_admin_templates_page_slug(): string
{
    return function_exists('em_site_admin_template_parent_page_slug')
        ? em_site_admin_template_parent_page_slug()
        : 'em-template';
}

function em_site_admin_rubriques_page_slug(): string
{
    return function_exists('em_site_page_slug')
        ? em_site_page_slug()
        : 'em-rubriques-overview';
}

function em_site_admin_vlb_page_slug(): string
{
    return function_exists('em_site_admin_menu_vlb_parent_slug')
        ? em_site_admin_menu_vlb_parent_slug()
        : 'mayami_visual_links_builder';
}

function em_site_admin_register_secondary_menus(): void
{
    if (function_exists('em_site_admin_template_parent_page_slug') && function_exists('em_site_page_slug')) {
        return;
    }

    add_menu_page(
        __('Templates', 'em-site'),
        __('TEMPLATES', 'em-site'),
        'manage_options',
        em_site_admin_templates_page_slug(),
        'em_site_admin_render_templates_page',
        'dashicons-layout',
        55
    );

    add_menu_page(
        __('Rubriques', 'em-site'),
        __('RUBRIQUES', 'em-site'),
        'manage_options',
        em_site_admin_rubriques_page_slug(),
        'em_site_admin_render_rubriques_page',
        'dashicons-grid-view',
        56
    );

    add_menu_page(
        __('Visual Links Builder', 'em-site'),
        __('VLB', 'em-site'),
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
            $menu[$index][0] = __('MEDIAS', 'em-site');
            continue;
        }

        if ($slug === 'options-general.php') {
            $menu[$index][0] = __('PARAMÈTRES', 'em-site');
        }
    }
}
add_action('admin_menu', 'em_site_admin_rename_native_menus', 999);

function em_site_admin_group_settings_menus(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    remove_menu_page('themes.php');

    add_submenu_page(
        'options-general.php',
        __('Apparence', 'em-site'),
        __('Apparence', 'em-site'),
        'manage_options',
        'themes.php'
    );

    global $submenu;

    if (!empty($submenu['options-general.php']) && is_array($submenu['options-general.php'])) {
        foreach ($submenu['options-general.php'] as $index => $submenu_item) {
            $slug = (string) ($submenu_item[2] ?? '');

            if ($slug === 'options-general.php') {
                $submenu['options-general.php'][$index][0] = __('Settings', 'em-site');
            }
        }
    }
}
add_action('admin_menu', 'em_site_admin_group_settings_menus', 1002);

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
