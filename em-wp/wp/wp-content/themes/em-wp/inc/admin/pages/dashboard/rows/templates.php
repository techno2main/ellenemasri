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
    <section class="em-wp-hub__row" aria-label="<?php esc_attr_e('Templates', 'em-wp'); ?>">
        <div class="em-wp-hub__cards">
            <section class="em-wp-hub__card">
                <?php em_wp_admin_dashboard_render_card_title(__('MES TEMPLATES', 'em-wp'), 'templates'); ?>
                <?php em_wp_admin_dashboard_render_live_template_badge($active_label, $active_color, true); ?>
                <div class="em-wp-hub__card-actions">
                    <?php em_wp_admin_dashboard_render_action_link(
                        em_wp_admin_template_choice_admin_url(),
                        __('GÉRER MES TEMPLATES', 'em-wp'),
                        'templates'
                    ); ?>
                </div>
            </section>

            <section class="em-wp-hub__card em-wp-hub__card--disabled">
                <?php em_wp_admin_dashboard_render_card_title(__('Nouveau Template', 'em-wp'), 'templates'); ?>
                <p class="em-wp-hub__card-desc">
                    <?php esc_html_e('Crée un nouveau template à partir d’un modèle vierge.', 'em-wp'); ?>
                </p>
                <div class="em-wp-hub__card-actions">
                    <?php em_wp_admin_dashboard_render_disabled_action(__('Nouveau Template', 'em-wp')); ?>
                </div>
            </section>
        </div>
    </section>
    <?php
}
