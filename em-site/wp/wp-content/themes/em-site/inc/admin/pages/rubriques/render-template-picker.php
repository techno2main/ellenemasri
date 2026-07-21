<?php
/**
 * Sommaire Templates — grille de cartes (style Accueil).
 *
 * Réutilisé par la page Templates (list.php).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Onglets sommaire Templates (Liste + templates enregistrés).
 */
function em_site_admin_template_render_nav_tabs(): void
{
    $registry = em_site_template_registry();
    $list_url = em_site_admin_template_choice_admin_url();
    $active_slug = em_site_get_active_template_slug();
    $create_url = function_exists('em_site_admin_template_create_admin_url')
        ? em_site_admin_template_create_admin_url()
        : '';

    if ($registry === []) {
        return;
    }
    ?>
    <nav class="em-site-catalog-edit__nav em-site-template-edit__nav" aria-label="<?php echo esc_attr__('Navigation Templates', 'em-site'); ?>">
        <ul class="em-site-catalog-edit__nav-list">
            <li class="em-site-catalog-edit__nav-item is-active">
                <a
                    class="em-site-catalog-edit__nav-link em-site-catalog-edit__nav-link--list"
                    href="<?php echo esc_url($list_url); ?>"
                    aria-label="<?php echo esc_attr__('Liste', 'em-site'); ?>"
                    aria-current="page"
                >
                    <i class="fa-solid fa-list-ol em-site-catalog-edit__nav-icon" aria-hidden="true"></i>
                </a>
            </li>
            <?php foreach ($registry as $slug => $definition) {
                $label = mb_strtoupper((string) ($definition['label'] ?? $slug));
                $is_live = ((string) $slug === $active_slug);
                $display_label = (string) ($definition['label'] ?? $slug);
                $tab_style = function_exists('em_site_admin_template_tab_style_attr')
                    ? em_site_admin_template_tab_style_attr((string) $slug)
                    : '';
                $entry_url = add_query_arg(
                    ['page' => em_site_admin_template_entry_page_slug((string) $slug)],
                    admin_url('admin.php')
                );
                ?>
                <li class="em-site-catalog-edit__nav-item">
                    <a
                        class="em-site-catalog-edit__nav-link<?php echo $is_live ? ' has-live-pill' : ''; ?>"
                        href="<?php echo esc_url($entry_url); ?>"
                        data-template-section="<?php echo esc_attr((string) $slug); ?>"
                        <?php if (!$is_live && $tab_style !== '') { ?>
                            style="<?php echo esc_attr($tab_style); ?>"
                        <?php } ?>
                    >
                        <?php if ($is_live) { ?>
                            <?php em_site_admin_hub_render_template_active_pill($display_label, (string) $slug); ?>
                        <?php } else { ?>
                            <span class="em-site-catalog-edit__nav-link-text"><?php echo esc_html($label); ?></span>
                        <?php } ?>
                    </a>
                </li>
            <?php } ?>
            <?php if ($create_url !== '') { ?>
                <li class="em-site-catalog-edit__nav-item em-site-catalog-edit__nav-item--add">
                    <button
                        type="button"
                        class="em-site-catalog-edit__nav-link em-site-catalog-edit__nav-link--add"
                        data-template-section="create"
                        data-em-site-new-template-open
                        aria-label="<?php esc_attr_e('Nouveau template', 'em-site'); ?>"
                        title="<?php esc_attr_e('Nouveau template', 'em-site'); ?>"
                    >
                        <i class="fa-solid fa-plus em-site-catalog-edit__nav-icon" aria-hidden="true"></i>
                    </button>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <?php

    if (function_exists('em_site_admin_hub_sticky_head_close')) {
        em_site_admin_hub_sticky_head_close();
    }
}

/**
 * Rendu du sommaire Templates (sélection du template à éditer).
 */
function em_site_admin_render_rubriques_template_picker(): void
{
    $registry = em_site_template_registry();
    $active_slug = em_site_get_active_template_slug();
    $can_manage = em_site_admin_can_manage_templates();
    $create_url = function_exists('em_site_admin_template_create_admin_url')
        ? em_site_admin_template_create_admin_url()
        : '';
    $template_icon = function_exists('em_site_site_icon') ? em_site_site_icon('template', 'dashicons-layout') : 'dashicons-layout';
    ?>
    <div class="wrap em-site-admin-module em-site-hub-sommaire em-site-templates-sommaire em-site-templates-admin" data-active-slug="<?php echo esc_attr($active_slug); ?>">
        <?php
        em_site_admin_hub_render_sommaire_header('', $template_icon, false, true, null, null, true);
        em_site_admin_template_render_nav_tabs();
        ?>

        <div class="em-site-hub__rows">
            <section class="em-site-hub__row" aria-label="<?php esc_attr_e('Templates enregistrés — cartes', 'em-site'); ?>">
                <div class="em-site-hub__cards">
                    <?php foreach ($registry as $slug => $definition) {
                        $label = mb_strtoupper((string) ($definition['label'] ?? $slug));
                        $card_title = sprintf(
                            /* translators: %s: template name */
                            __('TEMPLATE %s', 'em-site'),
                            $label
                        );
                        $display_label = (string) ($definition['label'] ?? $slug);
                        $color = em_site_get_template_color($slug);
                        $is_live = ($slug === $active_slug);
                        $entry_url = add_query_arg(
                            ['page' => em_site_admin_template_entry_page_slug($slug)],
                            admin_url('admin.php')
                        );
                        $preview_url = function_exists('em_site_template_preview_url')
                            ? em_site_template_preview_url((string) $slug)
                            : '';
                        ?>
                        <section
                            class="em-site-hub__card"
                            data-template-section="<?php echo esc_attr((string) $slug); ?>"
                            style="<?php echo esc_attr(em_site_admin_template_tab_style_attr((string) $slug)); ?>"
                        >
                            <header class="em-site-hub__card-header">
                                <div class="em-site-hub__card-heading">
                                    <?php em_site_admin_hub_render_card_title($card_title, $template_icon, null, $color); ?>
                                </div>
                                <div class="em-site-hub__card-header-actions">
                                    <a
                                        class="em-site-catalog-sommaire__edit em-site-templates-admin__template-edit"
                                        href="<?php echo esc_url($entry_url); ?>"
                                        title="<?php echo esc_attr(sprintf(__('Éditer %s', 'em-site'), $display_label)); ?>"
                                        aria-label="<?php echo esc_attr(sprintf(__('Éditer %s', 'em-site'), $display_label)); ?>"
                                    >
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </header>
                            <div class="em-site-templates-sommaire__card-live-footer">
                                <?php
                                em_site_admin_hub_render_template_card_live_footer(
                                    (string) $slug,
                                    $display_label,
                                    $color,
                                    $is_live,
                                    $can_manage
                                );
                                ?>
                                <?php if ($preview_url !== '') { ?>
                                    <button
                                        type="button"
                                        class="em-site-catalog-sommaire__edit em-site-templates-admin__template-preview"
                                        data-em-site-template-preview-url="<?php echo esc_url($preview_url); ?>"
                                        data-em-site-template-preview-label="<?php echo esc_attr($display_label); ?>"
                                        title="<?php echo esc_attr(sprintf(__('Prévisualiser %s', 'em-site'), $display_label)); ?>"
                                        aria-label="<?php echo esc_attr(sprintf(__('Prévisualiser %s', 'em-site'), $display_label)); ?>"
                                    >
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </button>
                                <?php } ?>
                            </div>
                        </section>
                    <?php } ?>

                    <?php
                    em_site_admin_hub_render_template_create_card([
                        'enabled'       => ($create_url !== ''),
                        'can_duplicate' => ($registry !== []),
                    ]);
                    ?>
                </div>
                <?php
                if ($can_manage && count($registry) > 1) {
                    em_site_admin_hub_render_template_set_live_form(em_site_admin_template_choice_page_slug());
                }
                ?>
            </section>

            <?php
            em_site_admin_render_templates_registered_table(
                $registry,
                $active_slug,
                $can_manage,
                'em-site-templates-registered-title'
            );
            ?>
        </div>
        <?php
        if (function_exists('em_site_admin_render_new_template_modals')) {
            em_site_admin_render_new_template_modals();
        }
        ?>
    </div>
    <?php
}
