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
    <section class="em-wp-hub__row" aria-label="<?php esc_attr_e('Catalogues', 'em-wp'); ?>">
        <div class="em-wp-hub__cards">
            <section class="em-wp-hub__card">
                <?php em_wp_admin_dashboard_render_card_title(__('MES CATALOGUES', 'em-wp'), 'catalogues'); ?>
                <p class="em-wp-hub__card-desc">
                    <?php esc_html_e('Bibliothèque de contenus réutilisables, indépendants des templates.', 'em-wp'); ?>
                </p>
                <?php em_wp_admin_dashboard_render_catalog_modules_badge(); ?>
                <div class="em-wp-hub__card-actions">
                    <?php em_wp_admin_dashboard_render_action_link(
                        em_wp_catalog_parent_page_url(),
                        __('GÉRER MES CATALOGUES', 'em-wp'),
                        'catalogues'
                    ); ?>
                </div>
            </section>

            <section class="em-wp-hub__card em-wp-hub__card--disabled">
                <?php em_wp_admin_dashboard_render_card_title(__('Nouveau Catalogue', 'em-wp'), 'catalogues'); ?>
                <p class="em-wp-hub__card-desc">
                    <?php esc_html_e('Crée un nouveau catalogue réutilisable (Hero, Slider, Vidéo…).', 'em-wp'); ?>
                </p>
                <div class="em-wp-hub__card-actions">
                    <?php em_wp_admin_dashboard_render_disabled_action(__('Nouveau Catalogue', 'em-wp')); ?>
                </div>
            </section>
        </div>
    </section>
    <?php
}
