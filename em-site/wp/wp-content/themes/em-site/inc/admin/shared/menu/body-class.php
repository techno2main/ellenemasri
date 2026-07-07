<?php
/**
 * Body class sidebar marron em-site.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Body class : sidebar marron em-site sur tout le BO admin.
 *
 * @param mixed $classes
 * @return mixed
 */
function em_site_admin_sidebar_chrome_body_class($classes)
{
    if (!current_user_can(em_site_admin_menu_capability())) {
        return $classes;
    }

    return $classes . ' em-site-admin-sidebar-chrome';
}
add_filter('admin_body_class', 'em_site_admin_sidebar_chrome_body_class');
