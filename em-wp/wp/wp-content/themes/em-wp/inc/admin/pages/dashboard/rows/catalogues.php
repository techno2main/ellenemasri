<?php
/**
 * Rangée Accueil : cartes Catalogues.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Affiche la rangée « Mes catalogues » + « Nouveau catalogue ».
 */
function em_wp_admin_dashboard_render_row_catalogues(): void
{
    ?>
    <section class="em-wp-hub__row em-wp-dashboard__row--hub-cards" aria-label="<?php esc_attr_e('Catalogues', 'em-wp'); ?>">
        <div class="em-wp-hub__cards">
            <section class="em-wp-hub__card" data-dashboard-section="catalogues">
                <header class="em-wp-hub__card-header">
                    <div class="em-wp-hub__card-heading">
                        <?php em_wp_admin_dashboard_render_card_title(__('MES CATALOGUES', 'em-wp'), 'catalogues'); ?>
                    </div>
                    <?php em_wp_admin_dashboard_render_card_gear_link(
                        em_wp_catalog_parent_page_url(),
                        __('Gérer mes catalogues', 'em-wp')
                    ); ?>
                </header>
                <p class="em-wp-hub__card-desc">
                    <?php esc_html_e('Bibliothèque de contenus réutilisables, indépendants des templates.', 'em-wp'); ?>
                </p>
                <?php em_wp_admin_dashboard_render_catalog_modules_badge(); ?>
            </section>

            <section class="em-wp-hub__card em-wp-hub__card--disabled">
                <header class="em-wp-hub__card-header">
                    <div class="em-wp-hub__card-heading">
                        <?php em_wp_admin_dashboard_render_card_title(__('Nouveau Catalogue', 'em-wp'), 'catalogues'); ?>
                    </div>
                    <?php em_wp_admin_dashboard_render_card_disabled_gear(); ?>
                </header>
                <p class="em-wp-hub__card-desc">
                    <?php esc_html_e('Crée un nouveau catalogue réutilisable (Hero, Slider, Vidéo…).', 'em-wp'); ?>
                </p>
            </section>
        </div>
    </section>
    <?php
}
