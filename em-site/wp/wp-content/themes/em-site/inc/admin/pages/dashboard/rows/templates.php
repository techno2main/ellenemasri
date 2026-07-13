<?php
/**
 * Rangée Accueil : cartes Templates.
 *
 * @package em-site
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
function em_site_admin_dashboard_render_templates_card(string $active_label, string $active_slug): void
{
    $active_label = 'Default';

    ?>
    <section class="em-site-hub__card" data-dashboard-section="templates">
        <header class="em-site-hub__card-header">
            <div class="em-site-hub__card-heading">
                <?php em_site_admin_dashboard_render_card_title(__('MON TEMPLATE', 'em-site'), 'templates'); ?>
            </div>
            <?php em_site_admin_dashboard_render_card_gear_link(
                em_site_admin_template_choice_admin_url(),
                __('Gérer mes templates', 'em-site')
            ); ?>
        </header>
        <?php em_site_admin_dashboard_render_template_context_badge($active_label, $active_slug); ?>
    </section>
    <?php
}
