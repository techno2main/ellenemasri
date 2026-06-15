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
    <section class="em-wp-hub__row em-wp-dashboard__row--hub-cards" aria-label="<?php esc_attr_e('Médias et réglages', 'em-wp'); ?>">
        <div class="em-wp-hub__cards">
            <section class="em-wp-hub__card" data-dashboard-section="medias">
                <header class="em-wp-hub__card-header">
                    <div class="em-wp-hub__card-heading">
                        <?php em_wp_admin_dashboard_render_card_title(__('MES MEDIAS', 'em-wp'), 'medias'); ?>
                    </div>
                    <?php em_wp_admin_dashboard_render_card_gear_link(
                        admin_url('upload.php'),
                        __('Gérer mes médias', 'em-wp')
                    ); ?>
                </header>
                <p class="em-wp-hub__card-desc">
                    <?php esc_html_e('Accède à ta bibliothèque de fichiers (images, vidéos, documents).', 'em-wp'); ?>
                </p>
                <?php em_wp_admin_dashboard_render_medias_badge(); ?>
            </section>

            <section class="em-wp-hub__card" data-dashboard-section="settings">
                <header class="em-wp-hub__card-header">
                    <div class="em-wp-hub__card-heading">
                        <?php em_wp_admin_dashboard_render_card_title(__('MES SETTINGS', 'em-wp'), 'settings'); ?>
                    </div>
                    <?php em_wp_admin_dashboard_render_card_gear_link(
                        admin_url('options-general.php'),
                        __('Voir mes settings', 'em-wp')
                    ); ?>
                </header>
                <p class="em-wp-hub__card-desc">
                    <?php esc_html_e('Réglages généraux de ton site.', 'em-wp'); ?>
                </p>
                <?php em_wp_admin_dashboard_render_settings_badge(); ?>
            </section>
        </div>
    </section>
    <?php
}
