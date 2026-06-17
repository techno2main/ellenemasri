<?php
/**
 * Rendu HTML page Accueil em-wp.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu page Accueil.
 */
function em_wp_admin_render_dashboard_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $registry = em_wp_template_registry();
    $active_slug = em_wp_get_active_template_slug();
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    $has_context = em_wp_admin_has_template_context();
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-dashboard">
        <?php
        em_wp_admin_hub_render_sommaire_header('', 'dashicons-dashboard', false, true, null, null, true);
        em_wp_admin_dashboard_render_nav_tabs();
        ?>

        <?php if ($has_context) { ?>
            <div class="notice notice-info inline">
                <p>
                    <?php
                    printf(
                        /* translators: %s: editing template label */
                        esc_html__('Tu édites actuellement le template « %s ». Utilise « Quitter » dans le bandeau pour revenir ici.', 'em-wp'),
                        esc_html(em_wp_get_editing_template_label())
                    );
                    ?>
                </p>
            </div>
        <?php } ?>

        <div class="em-wp-hub__rows">
            <?php
            em_wp_admin_dashboard_render_row_catalogues();
            em_wp_admin_dashboard_render_row_templates($active_label, $active_slug);
            em_wp_admin_dashboard_render_row_medias_settings();
            ?>
        </div>
    </div>
    <?php
}
