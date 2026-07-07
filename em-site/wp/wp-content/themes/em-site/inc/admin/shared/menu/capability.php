<?php
/**
 * Capability commune des menus em-site.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Capability commune des menus em-site (tous les admins BO).
 */
function em_site_admin_menu_capability(): string
{
    return 'manage_options';
}

/**
 * Modules catalogues visibles sous CATALOGUES (ordre menu).
 *
 * @return string[]
 */
function em_site_admin_catalog_menu_modules(): array
{
    $base = em_site_admin_catalog_menu_modules_builtin();

    if (function_exists('em_site_catalog_apply_menu_order')) {
        return em_site_catalog_apply_menu_order($base);
    }

    if (function_exists('em_site_custom_catalog_apply_menu_order')) {
        return em_site_custom_catalog_apply_menu_order($base);
    }

    return $base;
}
