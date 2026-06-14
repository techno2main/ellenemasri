<?php
/**
 * Rangée Accueil : cartes Médias + Settings.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Affiche la rangée « Mes médias » + « Mes settings ».
 */
function em_wp_admin_dashboard_render_row_medias_settings(): void
{
    ?>
    <section class="em-wp-dashboard__row" aria-label="<?php esc_attr_e('Médias et réglages', 'em-wp'); ?>">
        <div class="em-wp-dashboard__cards">
            <section class="em-wp-dashboard__card">
                <?php em_wp_admin_dashboard_render_card_title(__('MES MEDIAS', 'em-wp'), 'medias'); ?>
                <p class="em-wp-dashboard__card-desc">
                    <?php esc_html_e('Accède à ta bibliothèque de fichiers (images, vidéos, documents).', 'em-wp'); ?>
                </p>
                <?php em_wp_admin_dashboard_render_medias_badge(); ?>
                <div class="em-wp-dashboard__card-actions">
                    <?php em_wp_admin_dashboard_render_action_link(
                        admin_url('upload.php'),
                        __('GÉRER MES MEDIAS', 'em-wp'),
                        'medias'
                    ); ?>
                </div>
            </section>

            <section class="em-wp-dashboard__card">
                <?php em_wp_admin_dashboard_render_card_title(__('MES SETTINGS', 'em-wp'), 'settings'); ?>
                <p class="em-wp-dashboard__card-desc">
                    <?php esc_html_e('Réglages généraux de ton site.', 'em-wp'); ?>
                </p>
                <?php em_wp_admin_dashboard_render_settings_badge(); ?>
                <div class="em-wp-dashboard__card-actions">
                    <?php em_wp_admin_dashboard_render_action_link(
                        admin_url('options-general.php'),
                        __('VOIR MES SETTINGS', 'em-wp'),
                        'settings'
                    ); ?>
                </div>
            </section>
        </div>
    </section>
    <?php
}
