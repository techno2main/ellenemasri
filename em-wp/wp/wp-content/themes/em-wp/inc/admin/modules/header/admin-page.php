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
    $option_name = em_wp_header_option_name();
    $editing_label = function_exists('em_wp_get_editing_template_label')
        ? em_wp_get_editing_template_label()
        : '';
    ?>
    <div class="wrap em-wp-header-admin em-wp-admin-module">
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="em-wp-admin-module__hero">
            <div>
                <p class="em-wp-admin-module__eyebrow"><?php esc_html_e('HEADER', 'em-wp'); ?></p>
                <h1 class="em-wp-admin-module__title"><?php esc_html_e('HEADER', 'em-wp'); ?></h1>
                <?php if ($editing_label !== '') { ?>
                    <p class="em-wp-admin-module__description"><?php
                    printf(
                        esc_html__('Configuration pour le template %s', 'em-wp'),
                        esc_html($editing_label)
                    );
                    ?></p>
                <?php } ?>
            </div>
            <?php em_wp_admin_render_rubrique_visibility_toggle('header', 'em-wp-header-form'); ?>
        </div>

        <form id="em-wp-header-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_header_page_slug())); ?>">
            <input type="hidden" name="<?php echo esc_attr(em_wp_admin_rubrique_visibility_field_name('header')); ?>" value="0">
            <?php
            em_wp_admin_render_form_save_fields(
                'header',
                'em_wp_header_save',
                ['em_wp_template_context' => em_wp_get_editing_template_slug()]
            );
            ?>

            <div class="em-wp-admin-module__panels">
                <section class="em-wp-admin-module__panel is-open">
                    <div class="em-wp-admin-module__panel-body em-wp-header-admin__selection">
                        <p class="description"><?php esc_html_e('Choisis le Hero et/ou le Slider du catalogue à afficher dans le HEADER de ce template. Édite le contenu dans Catalogues → Heros / Sliders.', 'em-wp'); ?></p>

                        <label class="em-wp-header-admin__field">
                            <span><?php esc_html_e('Hero du catalogue', 'em-wp'); ?></span>
                            <select name="<?php echo esc_attr($option_name); ?>[hero_slug]" class="regular-text">
                                <option value=""><?php esc_html_e('— Aucun —', 'em-wp'); ?></option>
                                <?php foreach ($hero_choices as $slug => $label) { ?>
                                    <option value="<?php echo esc_attr($slug); ?>" <?php selected((string) ($options['hero_slug'] ?? ''), $slug); ?>><?php echo esc_html($label); ?></option>
                                <?php } ?>
                            </select>
                        </label>

                        <label class="em-wp-header-admin__field">
                            <span><?php esc_html_e('Slider du catalogue', 'em-wp'); ?></span>
                            <select name="<?php echo esc_attr($option_name); ?>[slider_slug]" class="regular-text">
                                <option value=""><?php esc_html_e('— Aucun —', 'em-wp'); ?></option>
                                <?php foreach ($slider_choices as $slug => $label) { ?>
                                    <option value="<?php echo esc_attr($slug); ?>" <?php selected((string) ($options['slider_slug'] ?? ''), $slug); ?>><?php echo esc_html($label); ?></option>
                                <?php } ?>
                            </select>
                        </label>

                        <fieldset class="em-wp-header-admin__layout">
                            <legend><?php esc_html_e('Disposition (Hero + Slider)', 'em-wp'); ?></legend>
                            <label>
                                <input type="radio" name="<?php echo esc_attr($option_name); ?>[layout]" value="hero_left" <?php checked((string) ($options['layout'] ?? 'hero_left'), 'hero_left'); ?>>
                                <span><?php esc_html_e('Hero à gauche, Slider à droite', 'em-wp'); ?></span>
                            </label>
                            <label>
                                <input type="radio" name="<?php echo esc_attr($option_name); ?>[layout]" value="slider_left" <?php checked((string) ($options['layout'] ?? ''), 'slider_left'); ?>>
                                <span><?php esc_html_e('Slider à gauche, Hero à droite', 'em-wp'); ?></span>
                            </label>
                        </fieldset>
                    </div>
                </section>
            </div>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}
