<?php
/**
 * Rangée Accueil : cartes Templates.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Affiche la rangée « Mes templates » + « Nouveau template ».
 *
 * @param string $active_label Libellé template actif live.
 * @param string $active_color Couleur template actif live.
 */
function em_wp_admin_dashboard_render_row_templates(string $active_label, string $active_color): void
{
    ?>
    <section class="em-wp-hub__row em-wp-dashboard__row--hub-cards" aria-label="<?php esc_attr_e('Templates', 'em-wp'); ?>">
        <div class="em-wp-hub__cards">
            <section class="em-wp-hub__card" data-dashboard-section="templates">
                <header class="em-wp-hub__card-header">
                    <div class="em-wp-hub__card-heading">
                        <?php em_wp_admin_dashboard_render_card_title(__('MES TEMPLATES', 'em-wp'), 'templates'); ?>
                    </div>
                    <?php em_wp_admin_dashboard_render_card_gear_link(
                        em_wp_admin_template_choice_admin_url(),
                        __('Gérer mes templates', 'em-wp')
                    ); ?>
                </header>
                <?php em_wp_admin_dashboard_render_live_template_badge($active_label, $active_color, true); ?>
            </section>

            <section class="em-wp-hub__card" data-dashboard-section="templates-create">
                <header class="em-wp-hub__card-header">
                    <div class="em-wp-hub__card-heading">
                        <?php em_wp_admin_dashboard_render_card_title(__('Nouveau Template', 'em-wp'), 'templates'); ?>
                    </div>
                    <?php
                    em_wp_admin_dashboard_render_card_create_link(
                        em_wp_admin_dashboard_new_template_admin_url(),
                        __('Créer un template', 'em-wp')
                    );
                    ?>
                </header>
                <p class="em-wp-hub__card-desc">
                    <?php esc_html_e('Crée un nouveau template à partir d’un modèle vierge.', 'em-wp'); ?>
                </p>
            </section>
        </div>
    </section>
    <?php
}
