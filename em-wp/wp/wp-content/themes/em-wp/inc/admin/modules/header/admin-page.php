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

    return 'em-wp-header';

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

    $field = em_wp_header_form_option_key();

    ?>

    <div class="wrap em-wp-header-admin em-wp-admin-module em-wp-hub-sommaire">

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



            <?php em_wp_admin_rubrique_open_section('header'); ?>

            <div class="em-wp-admin-module__panels">
                <?php
                em_wp_header_render_style_panel($options, $field);

                em_wp_admin_render_module_panel(

                    __('Hero et Slider', 'em-wp'),

                    'em-wp-header-admin__panel',

                    static function () use ($field, $options, $hero_choices, $slider_choices): void {

                        ?>

                        <p class="description"><?php esc_html_e('Choisis le Hero et/ou le Slider du catalogue à afficher dans le HEADER de ce template. Édite le contenu dans Catalogues → Heros / Sliders.', 'em-wp'); ?></p>



                        <label class="em-wp-header-admin__field">

                            <span><?php esc_html_e('Hero du catalogue', 'em-wp'); ?></span>

                            <select name="<?php echo esc_attr($field); ?>[hero_slug]" class="regular-text">

                                <option value=""><?php esc_html_e('— Aucun —', 'em-wp'); ?></option>

                                <?php foreach ($hero_choices as $slug => $label) { ?>

                                    <option value="<?php echo esc_attr($slug); ?>" <?php selected((string) ($options['hero_slug'] ?? ''), $slug); ?>><?php echo esc_html($label); ?></option>

                                <?php } ?>

                            </select>

                        </label>



                        <label class="em-wp-header-admin__field">

                            <span><?php esc_html_e('Slider du catalogue', 'em-wp'); ?></span>

                            <select name="<?php echo esc_attr($field); ?>[slider_slug]" class="regular-text">

                                <option value=""><?php esc_html_e('— Aucun —', 'em-wp'); ?></option>

                                <?php foreach ($slider_choices as $slug => $label) { ?>

                                    <option value="<?php echo esc_attr($slug); ?>" <?php selected((string) ($options['slider_slug'] ?? ''), $slug); ?>><?php echo esc_html($label); ?></option>

                                <?php } ?>

                            </select>

                        </label>



                        <fieldset class="em-wp-header-admin__layout">

                            <legend><?php esc_html_e('Disposition (Hero + Slider)', 'em-wp'); ?></legend>

                            <label>

                                <input type="radio" name="<?php echo esc_attr($field); ?>[layout]" value="hero_left" <?php checked((string) ($options['layout'] ?? 'hero_left'), 'hero_left'); ?>>

                                <span><?php esc_html_e('Hero à gauche, Slider à droite', 'em-wp'); ?></span>

                            </label>

                            <label>

                                <input type="radio" name="<?php echo esc_attr($field); ?>[layout]" value="slider_left" <?php checked((string) ($options['layout'] ?? ''), 'slider_left'); ?>>

                                <span><?php esc_html_e('Slider à gauche, Hero à droite', 'em-wp'); ?></span>

                            </label>

                        </fieldset>

                        <?php

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


