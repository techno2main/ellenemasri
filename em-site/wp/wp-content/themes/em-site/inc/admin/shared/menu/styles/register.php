<?php
/**
 * Enregistrement CSS menu admin.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Styles admin : sidebar marron, accordéons, filets.
 */
function em_site_admin_menu_chrome_styles(): void
{
    ?>
    <style id="em-site-admin-menu-chrome">
        <?php
        require __DIR__ . '/labels.php';
        require __DIR__ . '/sidebar-theme.php';
        require __DIR__ . '/dashboard-arrow.php';
        require __DIR__ . '/accordion.php';
        require __DIR__ . '/separators.php';
        require __DIR__ . '/rubriques-submenu.php';
        ?>
    </style>
    <?php
}
add_action('admin_head', 'em_site_admin_menu_chrome_styles', 9999);
