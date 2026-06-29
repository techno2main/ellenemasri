<?php
/**
 * Rangée Accueil : carte Rubriques (V4).
 *
 * Remplace l'ancienne rangée « Catalogues » : le site se compose désormais de
 * rubriques V4, plus de catalogues réutilisables séparés.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Affiche la rangée « Mes rubriques ».
 */
function em_wp_admin_dashboard_render_row_rubriques(): void
{
    ?>
    <section class="em-wp-hub__row em-wp-dashboard__row--hub-cards" aria-label="<?php esc_attr_e('Rubriques', 'em-wp'); ?>">
        <div class="em-wp-hub__cards">
            <section class="em-wp-hub__card" data-dashboard-section="rubriques">
                <header class="em-wp-hub__card-header">
                    <div class="em-wp-hub__card-heading">
                        <?php em_wp_admin_dashboard_render_card_title(__('MES RUBRIQUES', 'em-wp'), 'rubriques'); ?>
                    </div>
                    <?php em_wp_admin_dashboard_render_card_gear_link(
                        em_wp_admin_dashboard_rubriques_overview_url(),
                        __('Gérer mes rubriques', 'em-wp')
                    ); ?>
                </header>
                <p class="em-wp-hub__card-desc">
                    <?php esc_html_e('Sections réutilisables (Top-bar, Hero, Slider, Stream…) qui composent tes templates.', 'em-wp'); ?>
                </p>
                <?php em_wp_admin_dashboard_render_rubriques_badge(); ?>
            </section>
        </div>
    </section>
    <?php
}
