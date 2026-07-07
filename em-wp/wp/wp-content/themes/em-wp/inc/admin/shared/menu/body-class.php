<?php
/**
 * Body class sidebar marron em-wp.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Body class : sidebar marron em-wp sur tout le BO admin.
 *
 * @param mixed $classes
 * @return mixed
 */
function em_wp_admin_sidebar_chrome_body_class($classes)
{
    if (!current_user_can(em_wp_admin_menu_capability())) {
        return $classes;
    }

    return $classes . ' em-wp-admin-sidebar-chrome';
}
add_filter('admin_body_class', 'em_wp_admin_sidebar_chrome_body_class');
