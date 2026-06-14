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
    $active_color = em_wp_get_template_color($active_slug);
    $has_context = em_wp_admin_has_template_context();
    $greeting_name = em_wp_admin_dashboard_greeting_name();
    ?>
    <div class="wrap em-wp-admin-module em-wp-dashboard">
        <h1 class="em-wp-dashboard__greeting">
            <span class="dashicons dashicons-dashboard em-wp-dashboard__greeting-icon" aria-hidden="true"></span>
            <span class="em-wp-dashboard__greeting-text">
                <?php
                if ($greeting_name !== '') {
                    printf(
                        /* translators: %s: admin first name */
                        esc_html__('Hello %s', 'em-wp'),
                        esc_html($greeting_name)
                    );
                } else {
                    esc_html_e('Hello', 'em-wp');
                }
                ?>
            </span>
            <?php
            echo get_avatar(
                get_current_user_id(),
                40,
                '',
                $greeting_name !== '' ? sprintf(__('Avatar de %s', 'em-wp'), $greeting_name) : __('Avatar', 'em-wp'),
                ['class' => 'em-wp-dashboard__greeting-avatar']
            );
            ?>
        </h1>

        <div class="em-wp-dashboard__intro">
            <p class="description em-wp-dashboard__intro-text">
                <?php esc_html_e('Que veux-tu faire ? Choisis une action pour commencer.', 'em-wp'); ?>
            </p>
            <span class="em-wp-dashboard__intro-arrow" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11 4v11.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M6 12.5 11 17.5 16 12.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>

        <?php if ($has_context) { ?>
            <div class="notice notice-info inline">
                <p>
                    <?php
                    printf(
                        /* translators: %s: editing template label */
                        esc_html__('Tu édites actuellement le template « %s ». Utilise « Quitter l’édition » dans le bandeau pour revenir ici.', 'em-wp'),
                        esc_html(em_wp_get_editing_template_label())
                    );
                    ?>
                </p>
            </div>
        <?php } ?>

        <div class="em-wp-dashboard__rows">
            <?php
            em_wp_admin_dashboard_render_row_catalogues();
            em_wp_admin_dashboard_render_row_templates($active_label, $active_color);
            em_wp_admin_dashboard_render_row_medias_settings();
            ?>
        </div>
    </div>
    <?php
}
