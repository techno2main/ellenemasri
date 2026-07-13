<?php
/**
 * Rangée Accueil : carte Rubriques (EM-SITE).
 *
 * Remplace l'ancienne rangée « Catalogues » : le site se compose désormais de
 * rubriques EM-SITE, plus de catalogues réutilisables séparés.
 *
 * @package em-site
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
function em_site_admin_dashboard_render_row_rubriques(string $active_label, string $active_slug): void
{
    ?>
    <section class="em-site-hub__row em-site-dashboard__row--hub-cards" aria-label="<?php esc_attr_e('Rubriques', 'em-site'); ?>">
        <div class="em-site-hub__cards">
            <section class="em-site-hub__card" data-dashboard-section="rubriques">
                <header class="em-site-hub__card-header">
                    <div class="em-site-hub__card-heading">
                        <?php em_site_admin_dashboard_render_card_title(__('MES RUBRIQUES', 'em-site'), 'rubriques'); ?>
                    </div>
                    <?php em_site_admin_dashboard_render_card_gear_link(
                        em_site_admin_dashboard_rubriques_overview_url(),
                        __('Gérer mes rubriques', 'em-site')
                    ); ?>
                </header>
                <div class="em-site-hub__card-live-status em-site-hub__card-live-status--rubriques">
                    <p class="em-site-hub__card-live-status-prefix">
                        <?php esc_html_e('Sections réutilisables qui composent tes templates.', 'em-site'); ?>
                    </p>
                    <a
                        class="em-site-hub__template-live-pill em-site-hub__template-live-pill--rubriques-link"
                        href="<?php echo esc_url(em_site_admin_dashboard_rubriques_overview_url()); ?>"
                        style="--em-site-template-accent:#af16a8;--em-site-template-text:#ffffff;"
                    >
                        <span class="em-site-hub__template-live-pill-name"><?php echo esc_html(mb_strtoupper(__('Gérer les rubriques', 'em-site'))); ?></span>
                    </a>
                </div>
                <p class="em-site-hub__live em-site-hub__live--in-card em-site-hub__live--entry-links em-site-hub__live--uppercase em-site-hub__live--rubriques-empty" aria-hidden="true">
                    <span class="em-site-hub__catalog-entry-arrow">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.5 6h5.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M6.25 3.25 9.5 6l-3.25 2.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="em-site-hub__live-text">&nbsp;</span>
                </p>
            </section>

            <?php em_site_admin_dashboard_render_templates_card($active_label, $active_slug); ?>
        </div>
    </section>
    <?php
}
