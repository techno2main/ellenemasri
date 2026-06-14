<?php
/**
 * Page admin CTA (rendu).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $template_slug = em_wp_cta_admin_template_slug();
    $template_label = function_exists('em_wp_get_editing_template_label')
        ? em_wp_get_editing_template_label()
        : $template_slug;
    $options = em_wp_cta_get_options($template_slug);
    $style_defaults = em_wp_admin_module_default_style_colors('cta');
    $field = em_wp_cta_form_option_key();
    $texture_image = trim((string) ($options['texture_image'] ?? ''));
    ?>
    <div class="wrap em-wp-cta-admin em-wp-admin-module em-wp-hub-sommaire em-wp-admin-module--texture-preview" <?php echo em_wp_admin_module_style_data_attributes_for_module('cta', $field, $options); ?> data-em-admin-texture-field="<?php echo esc_attr($field); ?>[texture_image]" style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('cta', $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <?php em_wp_admin_rubrique_render_editing_page_header('cta'); ?>

        <form id="em-wp-cta-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_cta_page_slug())); ?>">
            <?php em_wp_admin_render_form_save_fields('cta', 'em_wp_cta_save'); ?>
            <input type="hidden" name="em_wp_template_context" value="<?php echo esc_attr($template_slug); ?>">

            <?php em_wp_admin_rubrique_open_section('cta', $options); ?>
            <div class="em-wp-admin-module__panels">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        ['name' => 'background_color', 'label' => __('Couleur de fond', 'em-wp'), 'value' => (string) ($options['background_color'] ?? ''), 'placeholder' => $style_defaults['background']],
                        ['name' => 'text_color', 'label' => __('Couleur du texte', 'em-wp'), 'value' => (string) ($options['text_color'] ?? ''), 'placeholder' => $style_defaults['text']],
                    ],
                    $field,
                    'em-wp-cta-panel'
                );

                em_wp_admin_render_module_items_section_title('cta');

                em_wp_admin_render_module_panel(
                    __('Image de fond (texture)', 'em-wp'),
                    'em-wp-cta-panel em-wp-cta-texture-panel',
                    static function () use ($options): void {
                        em_wp_cta_render_texture_panel_body($options);
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Contenu', 'em-wp'),
                    'em-wp-cta-panel',
                    static function () use ($options): void {
                        em_wp_cta_render_content_panel_body($options);
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Boutons', 'em-wp'),
                    'em-wp-cta-panel',
                    static function () use ($options): void {
                        em_wp_cta_render_buttons_panel_body($options);
                    }
                );
                ?>
            </div>
            <?php em_wp_admin_rubrique_close_section(); ?>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}
