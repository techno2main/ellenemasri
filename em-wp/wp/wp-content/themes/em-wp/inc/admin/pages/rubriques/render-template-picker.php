<?php
/**
 * Sommaire Templates — grille de cartes (style Accueil).
 *
 * Réutilisé par la page Templates (list.php).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Onglets sommaire Templates (Liste + templates enregistrés).
 */
function em_wp_admin_template_render_nav_tabs(): void
{
    $registry = em_wp_template_registry();
    $list_url = em_wp_admin_template_choice_admin_url();
    $active_slug = em_wp_get_active_template_slug();
    $create_url = function_exists('em_wp_admin_template_create_admin_url')
        ? em_wp_admin_template_create_admin_url()
        : '';

    if ($registry === []) {
        return;
    }
    ?>
    <nav class="em-wp-catalog-edit__nav em-wp-template-edit__nav" aria-label="<?php echo esc_attr__('Navigation Templates', 'em-wp'); ?>">
        <ul class="em-wp-catalog-edit__nav-list">
            <li class="em-wp-catalog-edit__nav-item is-active">
                <a
                    class="em-wp-catalog-edit__nav-link em-wp-catalog-edit__nav-link--list"
                    href="<?php echo esc_url($list_url); ?>"
                    aria-label="<?php echo esc_attr__('Liste', 'em-wp'); ?>"
                    aria-current="page"
                >
                    <i class="fa-solid fa-list-ol em-wp-catalog-edit__nav-icon" aria-hidden="true"></i>
                </a>
            </li>
            <?php foreach ($registry as $slug => $definition) {
                $label = mb_strtoupper((string) ($definition['label'] ?? $slug));
                $is_live = ((string) $slug === $active_slug);
                $display_label = (string) ($definition['label'] ?? $slug);
                $tab_style = function_exists('em_wp_admin_template_tab_style_attr')
                    ? em_wp_admin_template_tab_style_attr((string) $slug)
                    : '';
                $entry_url = add_query_arg(
                    ['page' => em_wp_admin_template_entry_page_slug((string) $slug)],
                    admin_url('admin.php')
                );
                ?>
                <li class="em-wp-catalog-edit__nav-item">
                    <a
                        class="em-wp-catalog-edit__nav-link<?php echo $is_live ? ' has-live-pill' : ''; ?>"
                        href="<?php echo esc_url($entry_url); ?>"
                        data-template-section="<?php echo esc_attr((string) $slug); ?>"
                        <?php if (!$is_live && $tab_style !== '') { ?>
                            style="<?php echo esc_attr($tab_style); ?>"
                        <?php } ?>
                    >
                        <?php if ($is_live) { ?>
                            <?php em_wp_admin_hub_render_template_active_pill($display_label, (string) $slug); ?>
                        <?php } else { ?>
                            <span class="em-wp-catalog-edit__nav-link-text"><?php echo esc_html($label); ?></span>
                        <?php } ?>
                    </a>
                </li>
            <?php } ?>
            <?php if ($create_url !== '') { ?>
                <li class="em-wp-catalog-edit__nav-item em-wp-catalog-edit__nav-item--add">
                    <a
                        class="em-wp-catalog-edit__nav-link em-wp-catalog-edit__nav-link--add"
                        href="<?php echo esc_url($create_url); ?>"
                        data-template-section="create"
                        data-em-wp-new-template-open
                        aria-label="<?php esc_attr_e('Nouveau template', 'em-wp'); ?>"
                        title="<?php esc_attr_e('Nouveau template', 'em-wp'); ?>"
                    >
                        <i class="fa-solid fa-plus em-wp-catalog-edit__nav-icon" aria-hidden="true"></i>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <?php

    if (function_exists('em_wp_admin_hub_sticky_head_close')) {
        em_wp_admin_hub_sticky_head_close();
    }
}

/**
 * Rendu du sommaire Templates (sélection du template à éditer).
 */
function em_wp_admin_render_rubriques_template_picker(): void
{
    $registry = em_wp_template_registry();
    $active_slug = em_wp_get_active_template_slug();
    $can_manage = em_wp_admin_can_manage_templates();
    $create_url = function_exists('em_wp_admin_template_create_admin_url')
        ? em_wp_admin_template_create_admin_url()
        : '';
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-templates-sommaire em-wp-templates-admin" data-active-slug="<?php echo esc_attr($active_slug); ?>">
        <?php
        em_wp_admin_hub_render_sommaire_header('', 'dashicons-layout', false, true, null, null, true);
        em_wp_admin_template_render_nav_tabs();
        ?>

        <div class="em-wp-hub__rows">
            <section class="em-wp-hub__row" aria-label="<?php esc_attr_e('Templates enregistrés — cartes', 'em-wp'); ?>">
                <div class="em-wp-hub__cards">
                    <?php foreach ($registry as $slug => $definition) {
                        $label = mb_strtoupper((string) ($definition['label'] ?? $slug));
                        $card_title = sprintf(
                            /* translators: %s: template name */
                            __('TEMPLATE %s', 'em-wp'),
                            $label
                        );
                        $display_label = (string) ($definition['label'] ?? $slug);
                        $color = em_wp_get_template_color($slug);
                        $is_live = ($slug === $active_slug);
                        $entry_url = add_query_arg(
                            ['page' => em_wp_admin_template_entry_page_slug($slug)],
                            admin_url('admin.php')
                        );
                        ?>
                        <section
                            class="em-wp-hub__card"
                            data-template-section="<?php echo esc_attr((string) $slug); ?>"
                            style="<?php echo esc_attr(em_wp_admin_template_tab_style_attr((string) $slug)); ?>"
                        >
                            <header class="em-wp-hub__card-header">
                                <div class="em-wp-hub__card-heading">
                                    <?php em_wp_admin_hub_render_card_title($card_title, 'dashicons-layout', null, $color); ?>
                                </div>
                                <?php em_wp_admin_hub_render_action_link(
                                    $entry_url,
                                    '',
                                    'dashicons-admin-generic',
                                    true,
                                    sprintf(
                                        /* translators: %s: template label */
                                        __('Éditer %s', 'em-wp'),
                                        $display_label
                                    )
                                ); ?>
                            </header>
                            <div class="em-wp-hub__card-desc em-wp-templates-sommaire__card-desc">
                                <?php
                                $summary_parts = em_wp_admin_template_site_rubriques_summary_parts($slug);
                                ?>
                                <p class="em-wp-templates-sommaire__card-desc-label"><?php echo esc_html($summary_parts['label']); ?></p>
                                <p class="em-wp-templates-sommaire__card-desc-list"><?php echo esc_html($summary_parts['list']); ?></p>
                            </div>
                            <div class="em-wp-templates-sommaire__card-live-footer">
                                <?php
                                em_wp_admin_hub_render_template_card_live_footer(
                                    (string) $slug,
                                    $display_label,
                                    $color,
                                    $is_live,
                                    $can_manage
                                );
                                ?>
                            </div>
                        </section>
                    <?php } ?>

                    <?php if ($create_url !== '') { ?>
                        <section
                            class="em-wp-hub__card em-wp-hub__card--template-create"
                            data-template-section="create"
                            style="--em-wp-template-accent: #751820; --em-wp-template-text: #ffffff;"
                        >
                            <header class="em-wp-hub__card-header">
                                <div class="em-wp-hub__card-heading">
                                    <?php em_wp_admin_hub_render_card_title(
                                        mb_strtoupper(__('Nouveau template', 'em-wp')),
                                        'dashicons-layout'
                                    ); ?>
                                </div>
                                <button
                                    type="button"
                                    class="em-wp-hub__card-create-icon"
                                    data-em-wp-new-template-open
                                    aria-label="<?php esc_attr_e('Création d\'un nouveau template', 'em-wp'); ?>"
                                >
                                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                                </button>
                            </header>
                            <div class="em-wp-hub__card-desc em-wp-templates-sommaire__card-desc">
                                <p class="em-wp-templates-sommaire__card-desc-label">
                                    <?php esc_html_e('Création d\'un nouveau Template', 'em-wp'); ?>
                                </p>
                                <p class="em-wp-templates-sommaire__card-desc-list">
                                    <?php esc_html_e('Duplique un template existant ou utilise le Wizard de création', 'em-wp'); ?>
                                </p>
                            </div>
                            <div class="em-wp-templates-sommaire__card-live-footer">
                                <?php em_wp_admin_hub_render_template_create_actions_badge($registry !== []); ?>
                            </div>
                        </section>
                    <?php } else { ?>
                        <section class="em-wp-hub__card em-wp-hub__card--disabled">
                            <header class="em-wp-hub__card-header">
                                <div class="em-wp-hub__card-heading">
                                    <?php em_wp_admin_hub_render_card_title(
                                        mb_strtoupper(__('Nouveau template', 'em-wp')),
                                        'dashicons-layout'
                                    ); ?>
                                </div>
                                <?php em_wp_admin_hub_render_disabled_action('', 'dashicons dashicons-plus-alt2', true); ?>
                            </header>
                            <div class="em-wp-hub__card-desc em-wp-templates-sommaire__card-desc">
                                <p class="em-wp-templates-sommaire__card-desc-label">
                                    <?php esc_html_e('Création d\'un nouveau Template', 'em-wp'); ?>
                                </p>
                                <p class="em-wp-templates-sommaire__card-desc-list">
                                    <?php esc_html_e('Duplique un template existant ou utilise le Wizard de création', 'em-wp'); ?>
                                </p>
                            </div>
                        </section>
                    <?php } ?>
                </div>
                <?php
                if ($can_manage && count($registry) > 1) {
                    em_wp_admin_hub_render_template_set_live_form(em_wp_admin_template_choice_page_slug());
                }
                ?>
            </section>

            <?php
            em_wp_admin_render_templates_registered_table(
                $registry,
                $active_slug,
                $can_manage,
                'em-wp-templates-registered-title'
            );
            ?>
        </div>
        <?php
        if (function_exists('em_wp_admin_render_new_template_modals')) {
            em_wp_admin_render_new_template_modals();
        }
        ?>
    </div>
    <?php
}
