<?php
/**
 * Menu latéral : entrée DASHBOARD, flèche, surbrillance Accueil.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug entrée menu décorative (flèche sous DASHBOARD).
 */
function em_wp_admin_dashboard_menu_arrow_slug(): string
{
    return 'em-wp-menu-dashboard-arrow';
}

/**
 * Nom du thème WordPress actif pour le libellé sidebar « THÈME ACTIF ».
 */
function em_wp_admin_sidebar_active_theme_label(): string
{
    return (string) wp_get_theme()->display('Name');
}

/**
 * @deprecated Utiliser em_wp_admin_sidebar_active_theme_label() pour le menu latéral.
 */
function em_wp_admin_dashboard_active_template_label(): string
{
    return em_wp_admin_sidebar_active_theme_label();
}

/**
 * Met en surbrillance DASHBOARD dans le menu latéral sur la page Accueil.
 *
 * @param mixed $parent_file
 * @return mixed
 */
function em_wp_admin_highlight_dashboard_menu($parent_file)
{
    if (em_wp_admin_is_dashboard_admin_screen()) {
        return 'index.php';
    }

    return $parent_file;
}
add_filter('parent_file', 'em_wp_admin_highlight_dashboard_menu');

/**
 * Évite l'ouverture du sous-menu natif Home / Updates sur l'Accueil em-wp.
 *
 * @param mixed $submenu_file
 * @return mixed
 */
function em_wp_admin_highlight_dashboard_submenu($submenu_file)
{
    if (em_wp_admin_is_dashboard_admin_screen()) {
        return '';
    }

    return $submenu_file;
}
add_filter('submenu_file', 'em_wp_admin_highlight_dashboard_submenu');

/**
 * Classe body sur la page Accueil (style menu DASHBOARD actif).
 *
 * @param mixed $classes
 * @return mixed
 */
function em_wp_admin_dashboard_body_class($classes)
{
    if (!em_wp_admin_is_dashboard_admin_screen()) {
        return $classes;
    }

    return $classes . ' em-wp-admin-dashboard-screen';
}
add_filter('admin_body_class', 'em_wp_admin_dashboard_body_class');

/**
 * Personnalise l'entrée Dashboard WP (libellé DASHBOARD, sans sous-menu natif).
 */
function em_wp_admin_point_dashboard_to_home(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $menu, $submenu;

    remove_submenu_page('index.php', 'index.php');
    remove_submenu_page('index.php', 'update-core.php');
    unset($submenu['index.php']);

    foreach ($menu as $position => $item) {
        if (!is_array($item)) {
            continue;
        }

        $slug = function_exists('em_wp_admin_menu_item_slug')
            ? em_wp_admin_menu_item_slug($item)
            : sanitize_key((string) ($item[2] ?? ''));

        if ($slug !== 'index.php' && $slug !== em_wp_admin_dashboard_page_slug()) {
            continue;
        }

        $menu[$position][0] = 'DASHBOARD';
        $menu[$position][3] = 'DASHBOARD';
        $menu[$position][2] = 'index.php';
        $menu[$position][4] = trim(((string) ($item[4] ?? 'menu-top')) . ' em-wp-menu-dashboard-entry');
        $menu[$position][6] = 'dashicons-admin-home';
    }

    foreach ($menu as $position => $item) {
        if (!is_array($item) || (string) ($item[2] ?? '') !== em_wp_admin_dashboard_menu_arrow_slug()) {
            continue;
        }

        unset($menu[$position]);
    }
}
add_action('admin_menu', 'em_wp_admin_point_dashboard_to_home', 1000002);

/**
 * Injecte la flèche sous DASHBOARD dans le menu latéral (thème actif = menu/layout.php).
 */
function em_wp_admin_mount_sidebar_menu_chrome_scripts(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <script id="em-wp-sidebar-menu-chrome">
        (function () {
            function getDashboardItem() {
                return document.getElementById('menu-dashboard')
                    || document.querySelector('#adminmenu li.em-wp-menu-dashboard-entry');
            }

            function mountDashboardArrow() {
                var item = getDashboardItem();

                if (!item || item.querySelector('.em-wp-dashboard-menu-arrow')) {
                    return;
                }

                var link = item.querySelector('a.menu-top');
                var arrow = document.createElement('span');
                arrow.className = 'em-wp-dashboard-menu-arrow';
                arrow.setAttribute('aria-hidden', 'true');

                if (link) {
                    link.insertAdjacentElement('afterend', arrow);
                    return;
                }

                item.appendChild(arrow);
            }

            mountDashboardArrow();

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', mountDashboardArrow);
            }

            window.addEventListener('load', mountDashboardArrow);
        })();
    </script>
    <?php
}
add_action('admin_footer', 'em_wp_admin_mount_sidebar_menu_chrome_scripts', 5);
