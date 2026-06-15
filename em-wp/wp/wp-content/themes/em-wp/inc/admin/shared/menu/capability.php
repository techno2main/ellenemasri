<?php
/**
 * Capability commune des menus em-wp.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Capability commune des menus em-wp (tous les admins BO).
 */
function em_wp_admin_menu_capability(): string
{
    return 'manage_options';
}

/**
 * Modules catalogues visibles sous CATALOGUES (ordre menu).
 *
 * @return string[]
 */
function em_wp_admin_catalog_menu_modules(): array
{
    $base = em_wp_admin_catalog_menu_modules_builtin();

    if (function_exists('em_wp_catalog_apply_menu_order')) {
        return em_wp_catalog_apply_menu_order($base);
    }

    if (function_exists('em_wp_custom_catalog_apply_menu_order')) {
        return em_wp_custom_catalog_apply_menu_order($base);
    }

    return $base;
}
