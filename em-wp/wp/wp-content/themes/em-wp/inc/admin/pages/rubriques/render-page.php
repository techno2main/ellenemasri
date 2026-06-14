<?php
/**
 * Rendu page sommaire Rubriques Template (liste + plan du site).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu de la page sommaire Rubriques Template.
 */
function em_wp_admin_render_rubriques_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!em_wp_admin_has_template_context()) {
        em_wp_admin_safe_redirect(em_wp_admin_template_choice_admin_url());
        return;
    }

    $definitions = em_wp_admin_site_rubrique_definitions();
    $editing_template_label = em_wp_get_editing_template_label();
    ?>
    <div class="wrap em-wp-rubriques-admin em-wp-admin-module em-wp-hub-sommaire">
        <?php
        em_wp_admin_hub_render_sommaire_header(
            sprintf(
                /* translators: %s: template label */
                __('Choisis une rubrique à configurer. Tu édites le template %s.', 'em-wp'),
                $editing_template_label
            ),
            'dashicons-admin-page'
        );
        ?>

        <div class="em-wp-rubriques-admin__layout">
            <div class="em-wp-rubriques-admin__main">
                <ul class="em-wp-rubriques-admin__list">
                    <?php
                    foreach ($definitions as $module_slug => $definition) {
                        em_wp_admin_rubriques_render_list_item($module_slug, $definition);
                    }
                    ?>
                </ul>
            </div>

            <?php if (function_exists('em_wp_admin_render_landing_map')) { ?>
                <aside class="em-wp-rubriques-admin__aside">
                    <div class="em-wp-rubriques-admin__map-wrap">
                        <p class="em-wp-rubriques-admin__map-label"><?php
                        printf(
                            /* translators: %s: template label */
                            esc_html__('Plan du site - Template %s', 'em-wp'),
                            esc_html($editing_template_label)
                        );
                        ?></p>
                        <p class="em-wp-rubriques-admin__map-hint">
                            <?php esc_html_e('Survole ou clique une zone pour ouvrir la rubrique.', 'em-wp'); ?><br>
                            <?php esc_html_e('Tu peux afficher ou masquer chaque rubrique via l’icône œil.', 'em-wp'); ?><br>
                            <?php esc_html_e('Glisse les sections pour changer leur ordre.', 'em-wp'); ?><br>
                            <?php esc_html_e('HEADER : aperçu structurel du Hero et/ou Slider configurés pour ce template.', 'em-wp'); ?>
                        </p>
                        <p class="em-wp-rubriques-admin__sort-status" id="em-wp-rubriques-sort-status" aria-live="polite" hidden></p>

                        <?php em_wp_admin_render_landing_map(); ?>
                    </div>
                </aside>
            <?php } ?>
        </div>
    </div>
    <?php
}
