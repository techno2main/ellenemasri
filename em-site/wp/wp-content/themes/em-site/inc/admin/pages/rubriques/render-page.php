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
    $positions = em_wp_admin_template_skeleton_insert_positions($template_slug);
    $position_values = array_column($positions, 'value');
    $default_position = in_array('__before_footer__', $position_values, true)
        ? '__before_footer__'
        : (string) ($position_values[count($position_values) - 1] ?? '__start__');
    ?>
    <div
        class="em-wp-catalog-sommaire__create-panel em-wp-catalog-sommaire__create-panel--module em-wp-rubrique-skeleton-add-panel"
        id="em-wp-rubrique-skeleton-add-panel"
        hidden
    >
        <div class="em-wp-catalog-sommaire__create-panel-inner">
            <header class="em-wp-catalog-sommaire__create-panel-head">
                <h3 class="em-wp-catalog-sommaire__create-panel-title"><?php esc_html_e('Ajouter une rubrique', 'em-wp'); ?></h3>
                <p class="em-wp-catalog-sommaire__create-panel-desc">
                    <?php esc_html_e('Configurez la rubrique, puis cliquez sur Insérer.', 'em-wp'); ?>
                </p>
                <p
                    class="em-wp-rubrique-skeleton-add-panel__status"
                    id="em-wp-rubrique-skeleton-add-status"
                    aria-live="polite"
                    hidden
                ></p>
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
                        $field_id = 'em-wp-rubrique-skeleton-insert-after-' . sanitize_html_class((string) $rubrique_slug);
                        ?>
                        <li class="em-wp-rubrique-skeleton-add-panel__item">
                            <header class="em-wp-rubrique-skeleton-add-panel__item-head">
                                <h4 class="em-wp-rubrique-skeleton-add-panel__item-title"><?php echo esc_html($label); ?></h4>
                            </header>

                            <div class="em-wp-catalog-sommaire__create-panel-fields em-wp-rubrique-skeleton-add-panel__fields">
                                <label class="em-wp-catalog-sommaire__field" for="<?php echo esc_attr($field_id); ?>">
                                    <span class="em-wp-catalog-sommaire__field-label"><?php esc_html_e('Position dans le squelette', 'em-wp'); ?></span>
                                    <select
                                        id="<?php echo esc_attr($field_id); ?>"
                                        class="em-wp-catalog-sommaire__control em-wp-catalog-sommaire__select em-wp-rubrique-skeleton-add-panel__position"
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

                                <p class="em-wp-rubrique-skeleton-add-panel__note">
                                    <i class="fa-regular fa-eye-slash" aria-hidden="true"></i>
                                    <?php esc_html_e('Ajoutée masquée sur le site — à réactiver une fois configurée.', 'em-wp'); ?>
                                </p>
                            </div>

                            <footer class="em-wp-catalog-sommaire__create-panel-actions em-wp-rubrique-skeleton-add-panel__actions">
                                <button
                                    type="button"
                                    class="button button-primary em-wp-rubriques-admin__add-button"
                                    data-rubrique-slug="<?php echo esc_attr((string) $rubrique_slug); ?>"
                                    data-template-slug="<?php echo esc_attr($template_slug); ?>"
                                >
                                    <?php esc_html_e('Insérer', 'em-wp'); ?>
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
    $is_template_live = function_exists('em_wp_get_active_template_slug')
        && $template_slug !== ''
        && em_wp_get_active_template_slug() === $template_slug;
    // Couleur d'accent du template (violet pour Mayami…) pour le badge LIVE.
    $template_accent = function_exists('em_wp_get_template_color')
        ? em_wp_get_template_color($template_slug)
        : '';
    $has_proposable_rubriques = function_exists('em_wp_admin_template_proposable_rubrique_definitions')
        && em_wp_admin_template_proposable_rubrique_definitions() !== [];
    ?>
    <div class="wrap em-wp-rubriques-admin em-wp-admin-module em-wp-hub-sommaire" data-template-slug="<?php echo esc_attr($template_slug); ?>">
        <?php
        // Bandeau « template en cours d'édition » et barre d'onglets retirés : la
        // navigation se fait par la liste + le wireframe. L'état LIVE est rappelé à
        // côté du titre du squelette (voir plus bas).
        em_wp_admin_hub_render_sommaire_header('', 'dashicons-admin-page', false, false, null, null, true);
        ?>

        <?php
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $open_module = sanitize_key((string) ($_GET['open'] ?? ''));
        ?>
        <div class="em-wp-rubriques-admin__layout">
            <div class="em-wp-rubriques-admin__main">
                <?php if (current_user_can('manage_options') && $has_proposable_rubriques) { ?>
                    <button
                        type="button"
                        class="button button-primary em-v4-savebar__btn em-wp-rubriques-admin__add-rubrique-toggle"
                        id="em-wp-rubrique-skeleton-add-toggle"
                        aria-controls="em-wp-rubrique-skeleton-add-panel"
                        aria-expanded="false"
                    >
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        <?php esc_html_e('Nouvelle Rubrique', 'em-wp'); ?>
                    </button>

                    <?php
                    if (function_exists('em_wp_admin_render_template_skeleton_add_panel')) {
                        em_wp_admin_render_template_skeleton_add_panel();
                    }
                    ?>
                <?php } ?>

                <ul class="em-wp-rubriques-admin__list">
                    <?php
                    foreach ($definitions as $module_slug => $definition) {
                        em_wp_admin_rubriques_render_list_item($module_slug, $definition);

                        if ($open_module === (string) $module_slug) {
                            em_wp_admin_render_rubrique_items_picker((string) $module_slug);
                        }
                    }
                    ?>
                </ul>
            </div>

            <?php if (function_exists('em_wp_admin_render_landing_map')) { ?>
                <aside class="em-wp-rubriques-admin__aside">
                    <div class="em-wp-rubriques-admin__map-wrap">
                        <div class="em-wp-rubriques-admin__map-head">
                            <span class="em-wp-rubriques-admin__map-title">
                                <span
                                    class="em-wp-rubriques-admin__map-label"
                                    data-title-default="<?php echo esc_attr(sprintf(
                                        /* translators: %s: template label */
                                        __('Squelette %s', 'em-wp'),
                                        $editing_template_label
                                    )); ?>"
                                    data-title-preview="<?php echo esc_attr(sprintf(
                                        /* translators: %s: template label */
                                        __('Aperçu %s', 'em-wp'),
                                        $editing_template_label
                                    )); ?>"
                                ><?php
                                printf(
                                    /* translators: %s: template label */
                                    esc_html__('Squelette %s', 'em-wp'),
                                    esc_html($editing_template_label)
                                );
                                ?></span>
                                <?php if ($is_template_live) { ?>
                                    <span
                                        class="em-wp-rubriques-admin__live-badge"
                                        title="<?php esc_attr_e('Template actif sur le site', 'em-wp'); ?>"
                                        <?php if ($template_accent !== '') { ?>style="--em-wp-live-color: <?php echo esc_attr($template_accent); ?>;"<?php } ?>
                                    >
                                        <span class="em-wp-rubriques-admin__live-dot" aria-hidden="true"></span><?php esc_html_e('LIVE', 'em-wp'); ?>
                                    </span>
                                <?php } ?>
                                <?php
                                $em_wp_site_preview_url = function_exists('em_wp_template_preview_url')
                                    ? (string) em_wp_template_preview_url((string) $template_slug)
                                    : '';
                                if ($em_wp_site_preview_url !== '') {
                                    ?>
                                    <a
                                        class="em-wp-rubriques-admin__site-preview"
                                        href="<?php echo esc_url($em_wp_site_preview_url); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        title="<?php echo esc_attr(sprintf(
                                            /* translators: %s: template label */
                                            __('Prévisualiser le site (%s) dans un nouvel onglet', 'em-wp'),
                                            $editing_template_label
                                        )); ?>"
                                    >
                                        <span class="dashicons dashicons-external" aria-hidden="true"></span>
                                        <span><?php esc_html_e('APERÇU', 'em-wp'); ?></span>
                                    </a>
                                <?php } ?>
                            </span>
                            <?php
                            if (function_exists('em_wp_admin_render_skeleton_full_preview')) {
                                em_wp_admin_render_skeleton_full_preview($definitions, (string) $template_slug);
                            }
                            ?>
                        </div>
                        <p class="em-wp-rubriques-admin__sort-status" id="em-wp-rubriques-sort-status" aria-live="polite" hidden></p>

                        <?php em_wp_admin_render_landing_map(); ?>
                    </div>
                </aside>
            <?php } ?>

            <?php
            // Charge les handlers/styles du picker (items + header) même sans
            // rubrique ouverte au premier affichage, pour permettre l'ouverture AJAX.
            if (function_exists('em_wp_admin_render_rubrique_items_picker_assets')) {
                em_wp_admin_render_rubrique_items_picker_assets();
            }
            if (function_exists('em_wp_admin_render_header_section_assets')) {
                em_wp_admin_render_header_section_assets();
            }
            ?>
        </div>
    </div>
    <?php
}
