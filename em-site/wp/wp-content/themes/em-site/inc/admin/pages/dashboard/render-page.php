<?php
/**
 * Rendu HTML page Accueil em-site.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu page Accueil.
 */
function em_site_admin_render_dashboard_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $registry = em_site_template_registry();
    $active_slug = em_site_get_active_template_slug();
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    $dashboard_icon = function_exists('em_site_site_icon') ? em_site_site_icon('dashboard', 'dashicons-dashboard') : 'dashicons-dashboard';
    ?>
    <div class="wrap em-site-admin-module em-site-hub-sommaire em-site-dashboard">
        <?php
        em_site_admin_hub_render_sommaire_header('', $dashboard_icon, false, true, null, null, true);
        em_site_admin_dashboard_render_nav_tabs();
        ?>

        <div class="em-site-hub__rows">
            <?php
            em_site_admin_dashboard_render_row_rubriques($active_label, $active_slug);
            em_site_admin_dashboard_render_row_medias_settings();
            ?>
        </div>
        <?php
        if (function_exists('em_site_admin_render_new_template_modals')) {
            em_site_admin_render_new_template_modals();
        }
        ?>
    </div>
    <?php
}
