<?php
/**
 * Rendu page sommaire Rubriques Template (liste + squelette).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Panneau « + » — rubriques proposables absentes du squelette (sous les onglets).
 */
function em_site_admin_render_template_skeleton_add_panel(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $proposable = em_site_admin_template_proposable_rubrique_definitions();
    $template_slug = em_site_get_editing_template_slug();
    $positions = em_site_admin_template_skeleton_insert_positions($template_slug);
    $position_values = array_column($positions, 'value');
    $default_position = in_array('__before_footer__', $position_values, true)
        ? '__before_footer__'
        : (string) ($position_values[count($position_values) - 1] ?? '__start__');
    ?>
    <div
        class="em-site-catalog-sommaire__create-panel em-site-catalog-sommaire__create-panel--module em-site-rubrique-skeleton-add-panel"
        id="em-site-rubrique-skeleton-add-panel"
        hidden
    >
        <div class="em-site-catalog-sommaire__create-panel-inner">
            <header class="em-site-catalog-sommaire__create-panel-head">
                <h3 class="em-site-catalog-sommaire__create-panel-title"><?php esc_html_e('Ajouter une rubrique', 'em-site'); ?></h3>
                <p class="em-site-catalog-sommaire__create-panel-desc">
                    <?php esc_html_e('Configurez la rubrique, puis cliquez sur Insérer.', 'em-site'); ?>
                </p>
                <p
                    class="em-site-rubrique-skeleton-add-panel__status"
                    id="em-site-rubrique-skeleton-add-status"
                    aria-live="polite"
                    hidden
                ></p>
            </header>

            <?php if ($proposable === []) { ?>
                <p class="em-site-rubrique-skeleton-add-panel__empty">
                    <?php esc_html_e('Aucune rubrique disponible pour le moment.', 'em-site'); ?>
                </p>
            <?php } else { ?>
                <ul class="em-site-rubrique-skeleton-add-panel__list">
                    <?php foreach ($proposable as $rubrique_slug => $definition) {
                        $label = function_exists('em_site_admin_rubrique_skeleton_label')
                            ? em_site_admin_rubrique_skeleton_label((string) $rubrique_slug)
                            : (string) ($definition['label'] ?? $rubrique_slug);
                        $field_id = 'em-site-rubrique-skeleton-insert-after-' . sanitize_html_class((string) $rubrique_slug);
                        ?>
                        <li class="em-site-rubrique-skeleton-add-panel__item">
                            <header class="em-site-rubrique-skeleton-add-panel__item-head">
                                <h4 class="em-site-rubrique-skeleton-add-panel__item-title"><?php echo esc_html($label); ?></h4>
                            </header>

                            <div class="em-site-catalog-sommaire__create-panel-fields em-site-rubrique-skeleton-add-panel__fields">
                                <label class="em-site-catalog-sommaire__field" for="<?php echo esc_attr($field_id); ?>">
                                    <span class="em-site-catalog-sommaire__field-label"><?php esc_html_e('Position dans le squelette', 'em-site'); ?></span>
                                    <select
                                        id="<?php echo esc_attr($field_id); ?>"
                                        class="em-site-catalog-sommaire__control em-site-catalog-sommaire__select em-site-rubrique-skeleton-add-panel__position"
                                    >
                                        <?php foreach ($positions as $position) {
                                            $value = (string) ($position['value'] ?? '');
                                            $position_label = (string) ($position['label'] ?? $value);

                                            if ($value === '') {
                                                continue;
                                            }
                                            ?>
                                            <option
                                                value="<?php echo esc_attr($value); ?>"
                                                <?php selected($value, $default_position); ?>
                                            >
                                                <?php echo esc_html($position_label); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </label>

                                <p class="em-site-rubrique-skeleton-add-panel__note">
                                    <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
                                    <?php esc_html_e('Ajoutée masquée sur le site — à réactiver une fois configurée.', 'em-site'); ?>
                                </p>
                            </div>

                            <footer class="em-site-catalog-sommaire__create-panel-actions em-site-rubrique-skeleton-add-panel__actions">
                                <button
                                    type="button"
                                    class="button button-primary em-site-rubriques-admin__add-button"
                                    data-rubrique-slug="<?php echo esc_attr((string) $rubrique_slug); ?>"
                                    data-template-slug="<?php echo esc_attr($template_slug); ?>"
                                >
                                    <?php esc_html_e('Insérer', 'em-site'); ?>
                                </button>
                            </footer>
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
function em_site_admin_render_rubriques_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!em_site_admin_has_template_context()) {
        em_site_admin_safe_redirect(em_site_admin_template_choice_admin_url());
        return;
    }

    $definitions = em_site_admin_site_rubrique_definitions();
    $editing_template_label = em_site_get_editing_template_label();
    $template_slug = em_site_get_editing_template_slug();
    $unique_mode = function_exists('em_site_template_unique_mode_enabled') && em_site_template_unique_mode_enabled();
    $map_title_default = $unique_mode
        ? __('Squelette', 'em-site')
        : sprintf(
            /* translators: %s: template label */
            __('Squelette %s', 'em-site'),
            $editing_template_label
        );
    $map_title_preview = $unique_mode
        ? __('Aperçu images', 'em-site')
        : sprintf(
            /* translators: %s: template label */
            __('Aperçu images %s', 'em-site'),
            $editing_template_label
        );
    $has_proposable_rubriques = function_exists('em_site_admin_template_proposable_rubrique_definitions')
        && em_site_admin_template_proposable_rubrique_definitions() !== [];
    ?>
    <div class="wrap em-site-rubriques-admin em-site-admin-module em-site-hub-sommaire" data-template-slug="<?php echo esc_attr($template_slug); ?>">
        <?php
        // Bandeau « template en cours d'édition » et barre d'onglets retirés : la
        // navigation se fait par la liste + le wireframe. L'état LIVE est rappelé à
        // côté du titre du squelette (voir plus bas).
        em_site_admin_hub_render_sommaire_header('', 'dashicons-admin-page', false, false, null, null, true);
        ?>

        <?php
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $open_module = sanitize_key((string) ($_GET['open'] ?? ''));
        ?>
        <div class="em-site-rubriques-admin__layout">
            <div class="em-site-rubriques-admin__main">
                <?php if (current_user_can('manage_options') && $has_proposable_rubriques) { ?>
                    <button
                        type="button"
                        class="button button-primary em-site-savebar__btn em-site-rubriques-admin__add-rubrique-toggle"
                        id="em-site-rubrique-skeleton-add-toggle"
                        aria-controls="em-site-rubrique-skeleton-add-panel"
                        aria-expanded="false"
                    >
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        <?php esc_html_e('Nouvelle Rubrique', 'em-site'); ?>
                    </button>

                    <?php
                    if (function_exists('em_site_admin_render_template_skeleton_add_panel')) {
                        em_site_admin_render_template_skeleton_add_panel();
                    }
                    ?>
                <?php } ?>

                <ul class="em-site-rubriques-admin__list">
                    <?php
                    foreach ($definitions as $module_slug => $definition) {
                        em_site_admin_rubriques_render_list_item($module_slug, $definition);

                        if ($open_module === (string) $module_slug) {
                            em_site_admin_render_rubrique_items_picker((string) $module_slug);
                        }
                    }
                    ?>
                </ul>
            </div>

            <?php if (function_exists('em_site_admin_render_landing_map')) { ?>
                <aside class="em-site-rubriques-admin__aside">
                    <div class="em-site-rubriques-admin__map-wrap">
                        <div class="em-site-rubriques-admin__map-head">
                            <span class="em-site-rubriques-admin__map-title">
                                <span
                                    class="em-site-rubriques-admin__map-label"
                                    data-title-default="<?php echo esc_attr($map_title_default); ?>"
                                    data-title-preview="<?php echo esc_attr($map_title_preview); ?>"
                                ><?php echo esc_html($map_title_default); ?></span>
                            </span>
                            <?php
                            if (function_exists('em_site_admin_render_skeleton_full_preview')) {
                                em_site_admin_render_skeleton_full_preview($definitions, (string) $template_slug);
                            }
                            ?>
                        </div>
                        <p class="em-site-rubriques-admin__sort-status" id="em-site-rubriques-sort-status" aria-live="polite" hidden></p>

                        <?php em_site_admin_render_landing_map(); ?>
                    </div>
                </aside>
            <?php } ?>

            <?php
            // Charge les handlers/styles du picker (items + header) même sans
            // rubrique ouverte au premier affichage, pour permettre l'ouverture AJAX.
            if (function_exists('em_site_admin_render_rubrique_items_picker_assets')) {
                em_site_admin_render_rubrique_items_picker_assets();
            }
            if (function_exists('em_site_admin_render_header_section_assets')) {
                em_site_admin_render_header_section_assets();
            }
            ?>
        </div>
    </div>
    <?php
}
