<?php
/**
 * Rendu admin du module Slider.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/render-slide-item.php';

/**
 * Rendu de la page admin Slider (hub + configuration).
 */
function em_site_slider_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $context = em_site_slider_get_admin_context();
    $style_slug = (string) ($context['style_slug'] ?? '');
    $definitions = em_site_slider_style_definitions();
    ?>
    <div class="wrap em-site-slider-admin em-site-admin-module em-site-hub-sommaire em-site-catalog-sommaire em-site-catalog-edit">
        <?php
        em_site_admin_render_settings_notices();
        if (function_exists('em_site_slider_catalog_render_admin_notices')) {
            em_site_slider_catalog_render_admin_notices();
        }
        ?>
        <?php
        em_site_catalog_render_edit_sommaire_header(
            'sliders',
            'dashicons-slides',
            $context,
            $definitions,
            $style_slug,
            em_site_slider_hub_page_url(),
            static function () use ($definitions, $style_slug): void {
                em_site_catalog_render_edit_banner('slider', $definitions, $style_slug, em_site_slider_hub_menu_slug());
            }
        );

        em_site_catalog_render_module_entry_tabs(
            em_site_slider_hub_menu_slug(),
            $definitions,
            $style_slug,
            __('Navigation Slider catalogue', 'em-site')
        );
        ?>

        <div class="em-site-catalog-edit__body">
            <?php if ($style_slug === '') { ?>
                <p class="em-site-catalog-sommaire__empty"><?php esc_html_e('Selectionnez un slider dans la liste ci-dessous.', 'em-site'); ?></p>
            <?php } else {
                $options = em_site_slider_get_options($style_slug);
                em_site_slider_render_edit_page_layout($context, $options, $style_slug);
            } ?>
        </div>
    </div>
    <?php
}

/**
 * Layout edition Slider (formulaire + apercu HEADER).
 *
 * @param array<string, mixed> $context
 * @param array<string, mixed> $options
 */
function em_site_slider_render_edit_page_layout(array $context, array $options, string $style_slug): void
{
    ?>
    <div class="em-site-catalog-edit__layout">
        <div class="em-site-catalog-edit__main">
            <?php em_site_slider_render_style_setup($context, $options, $style_slug); ?>
        </div>
    </div>
    <?php
}

/**
 * Rendu du panneau de configuration d'une variante Slider.
 *
 * @param array<string, mixed> $context
 * @param array<string, mixed> $options
 */
function em_site_slider_render_style_setup(array $context, array $options, string $active_style_slug = ''): void
{
    $slider_label = (string) ($context['label'] ?? 'Mayami');
    $style_slug = (string) ($context['style_slug'] ?? 'mayami');
    $page_slug = (string) ($context['page_slug'] ?? 'em-slider-mayami');
    ?>
    <div class="em-site-slider-admin__setup">
        <?php em_site_catalog_render_edit_section_open(__('Slider', 'em-site'), $slider_label); ?>

        <form id="em-site-slider-form" method="post" action="<?php echo esc_url(em_site_admin_module_form_action($page_slug)); ?>">
            <input type="hidden" name="<?php echo esc_attr(em_site_admin_rubrique_visibility_field_name('slider')); ?>" value="0">
            <?php
            em_site_admin_render_form_save_fields(
                'slider',
                'em_site_slider_save_' . $style_slug,
                ['em_site_module_context' => $style_slug]
            );
            ?>

            <div class="em-site-slider-admin__panels em-site-admin-module__panels">
                <?php
                em_site_admin_render_module_items_section_title(
                    'slider',
                    __('Items', 'em-site'),
                    (string) ($context['label'] ?? 'Slider Mayami')
                );
                ?>

                <section class="em-site-slider-panel em-site-admin-module__panel em-site-slider-item-panel">
                    <button class="<?php echo esc_attr(em_site_admin_panel_header_class('em-site-slider-panel')); ?>" type="button" aria-expanded="false">
                        <?php em_site_admin_render_panel_edit_trigger(); ?>
                        <span class="em-site-admin-module__item-header-line">
                            <span class="em-site-admin-module__item-visibility<?php echo !empty($options['slider_title_hidden']) ? ' is-hidden' : ''; ?>" aria-hidden="true"><i class="fa-solid <?php echo !empty($options['slider_title_hidden']) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i></span>
                            <span><?php esc_html_e('Titre Slider', 'em-site'); ?></span>
                        </span>
                    </button>
                    <div class="em-site-admin-module__panel-body em-site-admin-panel-body--row">
                        <input type="text" class="regular-text em-site-admin-field-input--wide" name="<?php echo esc_attr($context['option_name']); ?>[footer_title]" value="<?php echo esc_attr((string) ($options['footer_title'] ?? '')); ?>">
                        <label class="em-site-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name']); ?>[slider_title_hidden]" value="1" <?php checked(!empty($options['slider_title_hidden'])); ?>></label>
                        <?php
                        $slider_color_base = sanitize_html_class((string) $context['option_name']);
                        em_site_admin_render_color_field([
                            'id'            => $slider_color_base . '-footer-bg',
                            'name'          => $context['option_name'] . '[footer_bg_color]',
                            'value'         => (string) ($options['footer_bg_color'] ?? ''),
                            'default'       => '#f2ebd1',
                            'field_label'   => __('Fond du bandeau titre', 'em-site'),
                            'preview_label' => __('Fond du bandeau titre', 'em-site'),
                        ]);
                        em_site_admin_render_color_field([
                            'id'            => $slider_color_base . '-footer-text',
                            'name'          => $context['option_name'] . '[footer_text]',
                            'value'         => (string) ($options['footer_text'] ?? ''),
                            'default'       => '#100421',
                            'field_label'   => __('Couleur du titre', 'em-site'),
                            'preview_label' => __('Couleur du titre', 'em-site'),
                            'preview_type'  => 'text',
                            'bg_target_id'  => $slider_color_base . '-footer-bg',
                        ]);
                        ?>
                        <label class="em-site-admin-inline-check"><span><?php esc_html_e('Masquer les scotchs', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name']); ?>[tapes_hidden]" value="1" <?php checked(!empty($options['tapes_hidden'])); ?>></label>
                        <?php
                        em_site_admin_render_color_field([
                            'id'            => $slider_color_base . '-tapes-color',
                            'name'          => $context['option_name'] . '[tapes_color]',
                            'value'         => (string) ($options['tapes_color'] ?? ''),
                            'default'       => '#39c7ca',
                            'field_label'   => __('Couleur des scotchs', 'em-site'),
                            'preview_label' => __('Couleur des scotchs', 'em-site'),
                        ]);
                        ?>
                    </div>
                </section>

                <?php em_site_slider_render_slides_panel($context, $options); ?>
            </div>

            <?php submit_button(__('Enregistrer', 'em-site')); ?>
        </form>

        <?php em_site_catalog_render_edit_section_close(); ?>
    </div>
    <?php
}

/**
 * Rendu du panneau commun des slides.
 */
function em_site_slider_render_slides_panel(array $context, array $options): void
{
    $slides = em_site_slider_get_slides_list($options);
    ?>
    <section class="em-site-slider-panel em-site-admin-module__panel">
        <button class="<?php echo esc_attr(em_site_admin_panel_header_class('em-site-slider-panel')); ?>" type="button" aria-expanded="false">
            <?php em_site_admin_render_panel_edit_trigger(); ?>
            <span class="em-site-admin-module__item-header-line"><span class="em-site-admin-panel__has-children" title="<?php esc_attr_e('Contient des sous-elements', 'em-site'); ?>"><i class="fa-solid fa-list" aria-hidden="true"></i></span><span><?php esc_html_e('Slides', 'em-site'); ?></span></span>
        </button>
        <div class="em-site-admin-module__panel-body">
            <div class="em-site-admin-nested-list em-site-slider-slide-list" id="em-site-slider-slide-list" data-option-name="<?php echo esc_attr($context['option_name']); ?>">
                <?php foreach ($slides as $list_index => $slide) {
                    em_site_slider_render_slide_item((int) $list_index, $context, $slide);
                } ?>
            </div>
            <div class="em-site-slider-slide-actions">
                <button type="button" class="button button-secondary em-site-slider-add-slide"><?php esc_html_e('+ Ajouter un slide', 'em-site'); ?></button>
            </div>
            <template id="em-site-slider-slide-template">
                <?php em_site_slider_render_slide_item('__INDEX__', $context, em_site_slider_default_slide(), true); ?>
            </template>
        </div>
    </section>
    <?php
}
