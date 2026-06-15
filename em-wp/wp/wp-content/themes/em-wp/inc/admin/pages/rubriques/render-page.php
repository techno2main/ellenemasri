<?php
/**
 * Rendu page sommaire Rubriques Template (liste + squelette).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Panneau « + » — rubriques proposables absentes du squelette (sous les onglets).
 */
function em_wp_admin_render_template_skeleton_add_panel(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $proposable = em_wp_admin_template_proposable_rubrique_definitions();
    $template_slug = em_wp_get_editing_template_slug();
    ?>
    <div
        class="em-wp-catalog-sommaire__create-panel em-wp-catalog-sommaire__create-panel--module em-wp-rubrique-skeleton-add-panel"
        id="em-wp-rubrique-skeleton-add-panel"
        hidden
    >
        <div class="em-wp-catalog-sommaire__create-panel-inner">
            <header class="em-wp-catalog-sommaire__create-panel-head">
                <h3 class="em-wp-catalog-sommaire__create-panel-title"><?php esc_html_e('Ajouter une rubrique', 'em-wp'); ?></h3>
            </header>

            <?php if ($proposable === []) { ?>
                <p class="em-wp-rubrique-skeleton-add-panel__empty">
                    <?php esc_html_e('Aucune rubrique disponible pour le moment.', 'em-wp'); ?>
                </p>
            <?php } else { ?>
                <ul class="em-wp-rubrique-skeleton-add-panel__list">
                    <?php foreach ($proposable as $rubrique_slug => $definition) {
                        $label = function_exists('em_wp_admin_rubrique_skeleton_label')
                            ? em_wp_admin_rubrique_skeleton_label((string) $rubrique_slug)
                            : (string) ($definition['label'] ?? $rubrique_slug);
                        ?>
                        <li class="em-wp-rubrique-skeleton-add-panel__item">
                            <span class="em-wp-rubrique-skeleton-add-panel__label"><?php echo esc_html($label); ?></span>
                            <button
                                type="button"
                                class="button button-secondary em-wp-rubriques-admin__add-button"
                                data-rubrique-slug="<?php echo esc_attr((string) $rubrique_slug); ?>"
                                data-template-slug="<?php echo esc_attr($template_slug); ?>"
                            >
                                <?php esc_html_e('Insérer', 'em-wp'); ?>
                            </button>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>
    <?php
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
    $template_slug = em_wp_get_editing_template_slug();
    ?>
    <div class="wrap em-wp-rubriques-admin em-wp-admin-module em-wp-hub-sommaire" data-template-slug="<?php echo esc_attr($template_slug); ?>">
        <?php
        em_wp_admin_hub_render_sommaire_header('', 'dashicons-admin-page', false, true, null, null, true);
        em_wp_admin_rubrique_render_entry_tabs('');
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
                            esc_html__('Squelette du template %s', 'em-wp'),
                            esc_html($editing_template_label)
                        );
                        ?></p>
                        <p class="em-wp-rubriques-admin__map-hint">
                            <?php esc_html_e('Survole ou clique une zone pour ouvrir la rubrique.', 'em-wp'); ?><br>
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
