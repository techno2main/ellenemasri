<?php
/**
 * Enregistrement CSS menu admin.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Styles admin : sidebar marron, accordéons, filets.
 */
function em_wp_admin_menu_chrome_styles(): void
{
    ?>
    <style id="em-wp-admin-menu-chrome">
        <?php
        require __DIR__ . '/labels.php';
        require __DIR__ . '/sidebar-theme.php';
        require __DIR__ . '/dashboard-arrow.php';
        require __DIR__ . '/accordion.php';
        require __DIR__ . '/separators.php';
        ?>
    </style>
    <?php
}
add_action('admin_head', 'em_wp_admin_menu_chrome_styles', 9999);
