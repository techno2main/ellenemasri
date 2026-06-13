<?php
/**
 * Page admin Top Bar (rendu).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu de la page admin Top Bar.
 */
function em_wp_top_bar_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $template_slug = em_wp_top_bar_admin_template_slug();
    $template_label = function_exists('em_wp_get_editing_template_label')
        ? em_wp_get_editing_template_label()
        : $template_slug;
    $options = em_wp_top_bar_get_options($template_slug);
    $style_defaults = em_wp_admin_module_default_style_colors('top-bar');
    $field = em_wp_top_bar_form_option_key();
    ?>
    <div class="wrap em-wp-top-bar-admin em-wp-admin-module" <?php echo em_wp_admin_module_style_data_attributes_for_module('top-bar', $field, $options); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('top-bar', $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="em-wp-top-bar-admin__hero em-wp-admin-module__hero">
            <div>
                <p class="em-wp-top-bar-admin__eyebrow em-wp-admin-module__eyebrow"><?php esc_html_e('TOP-BAR', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php
                printf(
                    esc_html__('Menu de navigation du haut — Template %s', 'em-wp'),
                    esc_html($template_label)
                );
                ?></p>
            </div>
            <label class="em-wp-admin-module__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="<?php echo esc_attr($field); ?>[enabled]" value="1" form="em-wp-top-bar-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>
        <form id="em-wp-top-bar-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_top_bar_page_slug())); ?>">
            <?php em_wp_admin_render_form_save_fields('top-bar', 'em_wp_top_bar_save'); ?>
            <input type="hidden" name="em_wp_template_context" value="<?php echo esc_attr($template_slug); ?>">

            <div class="em-wp-top-bar-admin__panels em-wp-admin-module__panels">
                <?php em_wp_top_bar_render_style_panel($options); ?>
                <?php em_wp_admin_render_module_items_section_title('top-bar', '', __('Top-Bar', 'em-wp')); ?>
                <?php em_wp_top_bar_render_logo_panel($options); ?>
                <?php foreach (em_wp_top_bar_item_definitions() as $key => $title) {
                    em_wp_top_bar_render_item_panel($key, $title, $options['items'][$key] ?? []);
                } ?>
                <?php em_wp_top_bar_render_stream_icons_panel($options); ?>
            </div>
            <?php
            submit_button(__('Enregistrer', 'em-wp'));
            ?>
        </form>
    </div>
    <?php
}
