<?php
/**
 * Page admin Stream (rendu).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu page admin Stream.
 */
function em_wp_stream_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $template_slug = em_wp_stream_admin_template_slug();
    $template_label = function_exists('em_wp_get_editing_template_label')
        ? em_wp_get_editing_template_label()
        : $template_slug;
    $options = em_wp_stream_get_options($template_slug);
    $platforms = em_wp_stream_get_platforms_list($options);
    $definitions = em_wp_stream_platform_definitions();
    $top_bar_url = admin_url('admin.php?page=' . (function_exists('em_wp_top_bar_page_slug') ? em_wp_top_bar_page_slug() : 'em-wp-top-bar'));
    $style_defaults = em_wp_admin_module_default_style_colors('stream');
    $field = em_wp_stream_form_option_key();
    ?>
    <div class="wrap em-wp-stream-admin em-wp-admin-module" <?php echo em_wp_admin_module_style_data_attributes($field, $style_defaults); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars($options, $style_defaults)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>

        <div class="em-wp-stream-admin__hero em-wp-admin-module__hero">
            <div>
                <p class="em-wp-admin-module__eyebrow"><?php esc_html_e('STREAM', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php
                printf(
                    /* translators: %s: template label */
                    esc_html__('Section 01 / LISTEN — Template %s', 'em-wp'),
                    esc_html($template_label)
                );
                ?></p>
            </div>
            <label class="em-wp-admin-module__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="<?php echo esc_attr($field); ?>[enabled]" value="1" form="em-wp-stream-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>

        <form id="em-wp-stream-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_stream_page_slug())); ?>">
            <?php em_wp_admin_render_form_save_fields('stream', 'em_wp_stream_save'); ?>
            <input type="hidden" name="em_wp_template_context" value="<?php echo esc_attr($template_slug); ?>">

            <div class="em-wp-stream-admin__panels em-wp-admin-module__panels">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        [
                            'name'        => 'background_color',
                            'label'       => __('Couleur de fond', 'em-wp'),
                            'value'       => (string) ($options['background_color'] ?? ''),
                            'placeholder' => $style_defaults['background'],
                        ],
                        [
                            'name'        => 'text_color',
                            'label'       => __('Couleur du texte', 'em-wp'),
                            'value'       => (string) ($options['text_color'] ?? ''),
                            'placeholder' => $style_defaults['text'],
                        ],
                    ],
                    $field,
                    'em-wp-stream-panel'
                );
                ?>

                <?php em_wp_admin_render_module_items_section_title('stream'); ?>

                <?php
                em_wp_admin_render_module_panel(
                    __('Contenu', 'em-wp'),
                    'em-wp-stream-panel',
                    static function () use ($options): void {
                        em_wp_stream_render_content_panel_body($options);
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Plateformes', 'em-wp'),
                    'em-wp-stream-panel',
                    static function () use ($platforms, $definitions, $top_bar_url): void {
                        em_wp_stream_render_platforms_panel_body($platforms, $definitions, $top_bar_url);
                    }
                );
                ?>
            </div>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}
