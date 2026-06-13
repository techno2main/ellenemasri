<?php
/**
 * Page admin Release (rendu).
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

    $template_slug = em_wp_release_admin_template_slug();
    $template_label = function_exists('em_wp_get_editing_template_label')
        ? em_wp_get_editing_template_label()
        : $template_slug;
    $options = em_wp_release_get_options($template_slug);
    $rows = $options['rows'];

    if ($rows === []) {
        $rows = [['key' => '', 'value' => '', 'hidden' => false]];
    }

    $style_defaults = em_wp_admin_module_default_style_colors('release');
    $field = em_wp_release_form_option_key();
    ?>
    <div class="wrap em-wp-release-admin em-wp-admin-module" <?php echo em_wp_admin_module_style_data_attributes_for_module('release', $field, $options); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('release', $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="em-wp-admin-module__hero">
            <div>
                <p class="em-wp-admin-module__eyebrow"><?php esc_html_e('RELEASE', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php
                printf(
                    esc_html__('Section 04 / RELEASE INFOS — Template %s', 'em-wp'),
                    esc_html($template_label)
                );
                ?></p>
            </div>
            <label class="em-wp-admin-module__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="<?php echo esc_attr($field); ?>[enabled]" value="1" form="em-wp-release-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>

        <form id="em-wp-release-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_release_page_slug())); ?>">
            <?php em_wp_admin_render_form_save_fields('release', 'em_wp_release_save'); ?>
            <input type="hidden" name="em_wp_template_context" value="<?php echo esc_attr($template_slug); ?>">

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

                em_wp_admin_render_module_items_section_title('release');

                em_wp_admin_render_module_panel(
                    __('Contenu', 'em-wp'),
                    'em-wp-release-panel',
                    static function () use ($options): void {
                        em_wp_release_render_content_panel_body($options);
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Infos release', 'em-wp'),
                    'em-wp-release-panel',
                    static function () use ($rows): void {
                        em_wp_release_render_rows_panel_body($rows);
                    }
                );
                ?>
            </div>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}
