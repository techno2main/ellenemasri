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
    return ['heros', 'sliders', 'videos', 'streams', 'socials'];
}
