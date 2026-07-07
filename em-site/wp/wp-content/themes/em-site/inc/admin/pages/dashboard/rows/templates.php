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
 * Affiche la carte « Mes templates » (sans bloc Nouveau Template).
 *
 * @param string $active_label Libellé template actif live.
 * @param string $active_slug Slug template actif live.
 */
function em_wp_admin_dashboard_render_templates_card(string $active_label, string $active_slug): void
{
    ?>
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
        <?php em_wp_admin_dashboard_render_templates_badge(); ?>
    </section>
    <?php
}
