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
    $has_context = em_site_admin_has_template_context();
    ?>
    <div class="wrap em-site-admin-module em-site-hub-sommaire em-site-dashboard">
        <?php
        em_site_admin_hub_render_sommaire_header('', 'dashicons-dashboard', false, true, null, null, true);
        em_site_admin_dashboard_render_nav_tabs();
        ?>

        <?php if ($has_context) { ?>
            <div class="notice notice-info inline">
                <p>
                    <?php
                    printf(
                        /* translators: %s: editing template label */
                        esc_html__('Tu édites actuellement le template « %s ». Utilise « Quitter » dans le bandeau pour revenir ici.', 'em-site'),
                        esc_html(em_site_get_editing_template_label())
                    );
                    ?>
                </p>
            </div>
        <?php } ?>

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
