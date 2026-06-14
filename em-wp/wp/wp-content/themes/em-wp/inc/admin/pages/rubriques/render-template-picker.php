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
 * Rendu du sommaire Templates (sélection du template à éditer).
 */
function em_wp_admin_render_rubriques_template_picker(): void
{
    $registry = em_wp_template_registry();
    $active_slug = em_wp_get_active_template_slug();
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire">
        <?php
        em_wp_admin_hub_render_sommaire_header(
            __('Choisis le template que tu veux éditer. Tu peux aussi switcher le thème du site public.', 'em-wp'),
            'dashicons-layout'
        );
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
                        <section class="em-wp-hub__card">
                            <?php em_wp_admin_hub_render_card_title($card_title, 'dashicons-layout'); ?>
                            <p class="em-wp-hub__card-desc">
                                <?php echo esc_html(em_wp_admin_template_active_rubriques_summary($slug)); ?>
                            </p>
                            <?php if ($is_live) {
                                em_wp_admin_hub_render_template_live_badge($display_label, $color);
                            } ?>
                            <div class="em-wp-hub__card-actions">
                                <?php em_wp_admin_hub_render_action_link(
                                    $entry_url,
                                    sprintf(
                                        /* translators: %s: template label */
                                        __('ÉDITER %s', 'em-wp'),
                                        $label
                                    ),
                                    'dashicons-layout'
                                ); ?>
                            </div>
                        </section>
                    <?php } ?>

                    <section class="em-wp-hub__card em-wp-hub__card--disabled">
                        <?php em_wp_admin_hub_render_card_title(__('Nouveau Template', 'em-wp'), 'dashicons-layout'); ?>
                        <p class="em-wp-hub__card-desc">
                            <?php esc_html_e('Crée un nouveau template à partir d’un modèle vierge.', 'em-wp'); ?>
                        </p>
                        <div class="em-wp-hub__card-actions">
                            <?php em_wp_admin_hub_render_disabled_action(__('Nouveau Template', 'em-wp')); ?>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </div>
    <?php
}
