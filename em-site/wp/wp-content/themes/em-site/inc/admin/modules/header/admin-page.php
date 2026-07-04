<?php

/**

 * Page admin rubrique HEADER.

 *

 * @package em-wp

 */



if (!defined('ABSPATH')) {

    exit;

}



/**

 * Slug page admin HEADER.

 */

function em_wp_header_page_slug(): string

{

    return 'em-header';

}



/**

 * Rendu page admin HEADER (sélection catalogue par template).

 */

function em_wp_header_render_admin_page(): void

{

    if (!current_user_can('manage_options')) {

        return;

    }



    $options = em_wp_header_get_options();

    $hero_choices = function_exists('em_wp_hero_catalog_choices') ? em_wp_hero_catalog_choices() : [];

    $slider_choices = function_exists('em_wp_slider_catalog_choices') ? em_wp_slider_catalog_choices() : [];

    $hero_selected = sanitize_key((string) ($options['hero_slug'] ?? ''));
    $slider_selected = sanitize_key((string) ($options['slider_slug'] ?? ''));

    if ($hero_selected !== '' && function_exists('em_wp_hero_normalize_catalog_slug')) {
        $hero_selected = em_wp_hero_normalize_catalog_slug($hero_selected);
    }

    if ($slider_selected !== '' && function_exists('em_wp_slider_normalize_catalog_slug')) {
        $slider_selected = em_wp_slider_normalize_catalog_slug($slider_selected);
    }

    $hero_display_name = $hero_selected !== '' && isset($hero_choices[$hero_selected])
        ? trim((string) $hero_choices[$hero_selected])
        : '';
    $slider_display_name = $slider_selected !== '' && isset($slider_choices[$slider_selected])
        ? trim((string) $slider_choices[$slider_selected])
        : '';

    $hero_wireframe_name = $hero_display_name !== ''
        ? em_wp_admin_catalog_choice_switch_label($hero_selected, $hero_display_name)
        : '';
    $slider_wireframe_name = $slider_display_name !== ''
        ? em_wp_admin_catalog_choice_switch_label($slider_selected, $slider_display_name)
        : '';

    $field = em_wp_header_form_option_key();

    ?>

    <div class="wrap em-wp-header-admin em-wp-admin-module em-wp-hub-sommaire" <?php echo em_wp_admin_module_style_data_attributes_for_module('header', $field, $options); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('header', $options)); ?>">

        <?php em_wp_admin_render_settings_notices(); ?>

        <?php em_wp_admin_rubrique_render_editing_page_header('header'); ?>



        <form id="em-wp-header-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_header_page_slug())); ?>">

            <?php

            em_wp_admin_render_form_save_fields(

                'header',

                'em_wp_header_save',

                ['em_wp_template_context' => em_wp_get_editing_template_slug()]

            );

            ?>



            <?php em_wp_admin_rubrique_open_section('header', $options); ?>

            <div class="em-wp-admin-module__panels">
                <?php
                em_wp_header_render_style_panel($options, $field);

                em_wp_admin_render_module_panel(

                    em_wp_admin_rubrique_label('header'),

                    'em-wp-header-admin__panel',

                    static function () use ($field, $options, $hero_choices, $slider_choices, $hero_selected, $slider_selected, $hero_wireframe_name, $slider_wireframe_name, $hero_display_name, $slider_display_name): void {

                        ?>

                        <p class="description"><?php echo esc_html(em_wp_admin_header_catalog_selection_description()); ?></p>



                        <?php
                        em_wp_admin_render_catalog_slug_switcher(
                            $field . '[hero_slug]',
                            $hero_selected,
                            $hero_choices,
                            em_wp_admin_catalog_module_import_switcher_label('hero'),
                            'hero'
                        );

                        em_wp_admin_render_catalog_slug_switcher(
                            $field . '[slider_slug]',
                            $slider_selected,
                            $slider_choices,
                            em_wp_admin_catalog_module_import_switcher_label('slider'),
                            'slider'
                        );

                        em_wp_header_render_layout_switcher(
                            $field . '[layout]',
                            (string) ($options['layout'] ?? 'hero_left'),
                            $hero_wireframe_name,
                            $slider_wireframe_name,
                            $hero_display_name,
                            $slider_display_name
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



