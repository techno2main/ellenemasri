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
                $entry_url = add_query_arg(
                    ['page' => em_wp_admin_template_entry_page_slug((string) $slug)],
                    admin_url('admin.php')
                );
                ?>
                <li class="em-wp-catalog-edit__nav-item">
                    <a
                        class="em-wp-catalog-edit__nav-link"
                        href="<?php echo esc_url($entry_url); ?>"
                        data-template-section="<?php echo esc_attr((string) $slug); ?>"
                    >
                        <?php echo esc_html($label); ?>
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
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-templates-sommaire">
        <?php
        em_wp_admin_hub_render_sommaire_header('', 'dashicons-layout', false, true, null, null, true);
        em_wp_admin_template_render_nav_tabs();
        ?>

        <?php em_wp_admin_hub_render_live_template_switcher(em_wp_admin_template_choice_page_slug()); ?>

        <div class="em-wp-hub__rows">
            <section class="em-wp-hub__row" aria-label="<?php esc_attr_e('Templates enregistrés', 'em-wp'); ?>">
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
                        <section class="em-wp-hub__card" data-template-section="<?php echo esc_attr((string) $slug); ?>">
                            <header class="em-wp-hub__card-header">
                                <div class="em-wp-hub__card-heading">
                                    <?php em_wp_admin_hub_render_card_title($card_title, 'dashicons-layout'); ?>
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
                            <p class="em-wp-hub__card-desc">
                                <?php echo esc_html(em_wp_admin_template_active_rubriques_summary($slug)); ?>
                            </p>
                            <?php if ($is_live) {
                                em_wp_admin_hub_render_template_live_badge($display_label, $color);
                            } ?>
                        </section>
                    <?php } ?>

                    <section class="em-wp-hub__card em-wp-hub__card--disabled">
                        <header class="em-wp-hub__card-header">
                            <div class="em-wp-hub__card-heading">
                                <?php em_wp_admin_hub_render_card_title(__('Nouveau Template', 'em-wp'), 'dashicons-layout'); ?>
                            </div>
                            <?php em_wp_admin_hub_render_disabled_action('', 'dashicons dashicons-plus-alt2', true); ?>
                        </header>
                        <p class="em-wp-hub__card-desc">
                            <?php esc_html_e('Crée un nouveau template à partir d’un modèle vierge.', 'em-wp'); ?>
                        </p>
                    </section>
                </div>
            </section>
        </div>
    </div>
    <?php
}
