<?php
/**
 * Partial : panneau styles Top Bar (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu du sous-bloc image de fond (panneau styles).
 *
 * @param array<string, mixed> $options
 */
function em_site_top_bar_render_style_panel_bg_image(array $options, ?string $field = null): void
{
    $field = $field ?? em_site_top_bar_form_option_key();
    ?>
    <div class="em-site-admin-panel-body--top-border">
        <label class="em-site-admin-inline-check em-site-top-bar-bg-enable-check"><span><?php esc_html_e('Activer image de fond', 'em-site'); ?></span><input id="em-site-top-bar-bg-image-enabled" type="checkbox" name="<?php echo esc_attr($field); ?>[background_image_enabled]" value="1" <?php checked(!empty($options['background_image_enabled'])); ?>></label>
        <div id="em-site-top-bar-bg-fields" class="em-site-top-bar-bg-fields<?php echo empty($options['background_image_enabled']) ? ' is-disabled' : ''; ?>">
            <label class="em-site-top-bar-background-image-label"><span><?php esc_html_e('Image de fond', 'em-site'); ?></span></label>
            <div class="em-site-admin-media-picker em-site-top-bar-logo-picker">
                <input type="text" id="em-site-top-bar-bg-image-url" name="<?php echo esc_attr($field); ?>[background_image_url]" value="<?php echo esc_attr($options['background_image_url'] ?? ''); ?>" class="regular-text em-site-admin-field-input--wide">
                <button type="button" class="button button-secondary em-site-admin-media-button em-site-top-bar-media-button" data-target="em-site-top-bar-bg-image-url" data-preview="em-site-top-bar-bg-image-preview" data-modal-title="<?php echo esc_attr__('Choisir l\'image de fond Top-Bar', 'em-site'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser cette image de fond', 'em-site'); ?>"><?php esc_html_e('Modifier', 'em-site'); ?></button>
                <label class="em-site-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($field); ?>[background_image_hidden]" value="1" <?php checked(!empty($options['background_image_hidden'])); ?>></label>
            </div>
            <div id="em-site-top-bar-bg-image-preview" class="em-site-admin-media-preview em-site-admin-media-preview--checkerboard<?php echo empty($options['background_image_url']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['background_image_url'])) { ?><img src="<?php echo esc_url($options['background_image_url']); ?>" alt=""><?php } ?></div>
        </div>
    </div>
    <?php
}

/**
 * Rendu du panneau styles.
 *
 * @param array<string, mixed> $options
 */
function em_site_top_bar_render_style_panel(array $options): void
{
    em_site_admin_render_base_style_panel(
        __('Styles de base', 'em-site'),
        [
            [
                'name'  => 'background_color',
                'label' => __('Couleur de fond', 'em-site'),
                'value' => (string) ($options['background_color'] ?? ''),
            ],
            [
                'name'  => 'text_color',
                'label' => __('Couleur du texte', 'em-site'),
                'value' => (string) ($options['text_color'] ?? ''),
            ],
        ],
        em_site_top_bar_form_option_key(),
        'em-site-top-bar-panel',
        static function () use ($options): void {
            em_site_top_bar_render_style_panel_bg_image($options);
        }
    );
}
