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
 * @param string $active_slug Slug template actif live.
 */
function em_wp_admin_dashboard_render_row_templates(string $active_label, string $active_slug): void
{
    $create_url = em_wp_admin_dashboard_new_template_admin_url();
    $can_duplicate = em_wp_template_registry() !== [];
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
                <?php em_wp_admin_dashboard_render_live_template_badge($active_label, $active_slug, true); ?>
            </section>

            <?php
            em_wp_admin_hub_render_template_create_card([
                'enabled'       => ($create_url !== ''),
                'can_duplicate' => $can_duplicate,
                'section_attr'  => 'data-dashboard-section',
                'section_value' => 'templates-create',
            ]);
            ?>
        </div>
    </section>
    <?php
}
