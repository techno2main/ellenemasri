<?php
/**
 * Page admin Release (rubrique template — sélection catalogue).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $options = em_wp_release_get_options();
    $choices = function_exists('em_wp_release_catalog_choices') ? em_wp_release_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['release_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_wp_release_normalize_catalog_slug')) {
        $selected = em_wp_release_normalize_catalog_slug($selected);
    }

    $style_defaults = em_wp_admin_module_default_style_colors('release');
    $field = em_wp_release_form_option_key();
    ?>
    <div class="wrap em-wp-release-admin em-wp-header-admin em-wp-admin-module em-wp-hub-sommaire" <?php echo em_wp_admin_module_style_data_attributes_for_module('release', $field, $options); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('release', $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <?php em_wp_admin_rubrique_render_editing_page_header('release'); ?>

        <form id="em-wp-release-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_release_page_slug())); ?>">
            <?php
            em_wp_admin_render_form_save_fields(
                'release',
                'em_wp_release_save',
                ['em_wp_template_context' => em_wp_get_editing_template_slug()]
            );
            ?>

            <?php em_wp_admin_rubrique_open_section('release', $options); ?>
            <div class="em-wp-admin-module__panels">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        ['name' => 'background_color', 'label' => __('Couleur de fond', 'em-wp'), 'value' => (string) ($options['background_color'] ?? ''), 'placeholder' => $style_defaults['background']],
                        ['name' => 'text_color', 'label' => __('Couleur du texte', 'em-wp'), 'value' => (string) ($options['text_color'] ?? ''), 'placeholder' => $style_defaults['text']],
                    ],
                    $field,
                    'em-wp-release-panel'
                );

                em_wp_admin_render_module_panel(
                    __('Release du catalogue', 'em-wp'),
                    'em-wp-release-panel',
                    static function () use ($field, $selected, $choices): void {
                        ?>
                        <p class="description"><?php esc_html_e('Choisis la release du catalogue à afficher dans la rubrique RELEASES de ce template. Édite le contenu dans Catalogues → Releases.', 'em-wp'); ?></p>
                        <?php
                        em_wp_admin_render_catalog_slug_switcher(
                            $field . '[release_slug]',
                            $selected,
                            $choices,
                            __('Release du catalogue', 'em-wp'),
                            'release'
                        );
                    },
                    'em-wp-admin-panel-body--stack em-wp-header-admin__selection',
                    true
                );
                ?>
            </div>
            <?php em_wp_admin_rubrique_close_section(); ?>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}
