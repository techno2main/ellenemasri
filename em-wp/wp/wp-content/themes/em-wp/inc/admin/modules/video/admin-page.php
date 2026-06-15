<?php
/**
 * Page admin Video (rubrique template — sélection catalogue).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_video_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $options = em_wp_video_get_options();
    $choices = function_exists('em_wp_video_catalog_choices') ? em_wp_video_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['video_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_wp_video_normalize_catalog_slug')) {
        $selected = em_wp_video_normalize_catalog_slug($selected);
    }

    $style_defaults = em_wp_admin_module_default_style_colors('video');
    $field = em_wp_video_form_option_key();
    ?>
    <div class="wrap em-wp-video-admin em-wp-header-admin em-wp-admin-module em-wp-hub-sommaire" <?php echo em_wp_admin_module_style_data_attributes_for_module('video', $field, $options); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('video', $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <?php em_wp_admin_rubrique_render_editing_page_header('video'); ?>

        <form id="em-wp-video-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_video_page_slug())); ?>">
            <?php
            em_wp_admin_render_form_save_fields(
                'video',
                'em_wp_video_save',
                ['em_wp_template_context' => em_wp_get_editing_template_slug()]
            );
            ?>

            <?php em_wp_admin_rubrique_open_section('video', $options); ?>
            <div class="em-wp-admin-module__panels">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        ['name' => 'background_color', 'label' => __('Couleur de fond', 'em-wp'), 'value' => (string) ($options['background_color'] ?? ''), 'placeholder' => $style_defaults['background']],
                        ['name' => 'text_color', 'label' => __('Couleur du texte', 'em-wp'), 'value' => (string) ($options['text_color'] ?? ''), 'placeholder' => $style_defaults['text']],
                    ],
                    $field,
                    'em-wp-video-panel'
                );

                em_wp_admin_render_module_panel(
                    __('Vidéo du catalogue', 'em-wp'),
                    'em-wp-video-panel',
                    static function () use ($field, $selected, $choices): void {
                        ?>
                        <p class="description"><?php esc_html_e('Choisis la vidéo du catalogue à afficher dans la rubrique VIDEOS de ce template. Édite le contenu dans Catalogues → Vidéos.', 'em-wp'); ?></p>
                        <?php
                        em_wp_admin_render_catalog_slug_switcher(
                            $field . '[video_slug]',
                            $selected,
                            $choices,
                            __('Vidéo du catalogue', 'em-wp'),
                            'video'
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
