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
 * Affiche la rangée « Mes rubriques » + « Mes templates ».
 *
 * @param string $active_label Libellé template actif live.
 * @param string $active_slug Slug template actif live.
 */
function em_wp_admin_dashboard_render_row_rubriques(string $active_label, string $active_slug): void
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
                <div class="em-wp-hub__card-live-status em-wp-hub__card-live-status--rubriques">
                    <p class="em-wp-hub__card-live-status-prefix">
                        <?php esc_html_e('Sections réutilisables qui composent tes templates.', 'em-wp'); ?>
                    </p>
                    <a
                        class="em-wp-hub__template-live-pill em-wp-hub__template-live-pill--rubriques-link"
                        href="<?php echo esc_url(em_wp_admin_dashboard_rubriques_overview_url()); ?>"
                        style="--em-wp-template-accent:#af16a8;--em-wp-template-text:#ffffff;"
                    >
                        <span class="em-wp-hub__template-live-pill-name"><?php echo esc_html(mb_strtoupper(__('Gérer les rubriques', 'em-wp'))); ?></span>
                    </a>
                </div>
                <p class="em-wp-hub__live em-wp-hub__live--in-card em-wp-hub__live--entry-links em-wp-hub__live--uppercase em-wp-hub__live--rubriques-empty" aria-hidden="true">
                    <span class="em-wp-hub__catalog-entry-arrow">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.5 6h5.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M6.25 3.25 9.5 6l-3.25 2.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="em-wp-hub__live-text">&nbsp;</span>
                </p>
            </section>

            <?php em_wp_admin_dashboard_render_templates_card($active_label, $active_slug); ?>
        </div>
    </section>
    <?php
}
