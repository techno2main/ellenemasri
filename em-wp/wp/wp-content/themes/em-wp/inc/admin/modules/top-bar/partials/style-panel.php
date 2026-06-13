<?php
/**
 * Partial : panneau styles Top Bar (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu du sous-bloc image de fond (panneau styles).
 *
 * @param array<string, mixed> $options
 */
function em_wp_top_bar_render_style_panel_bg_image(array $options): void
{
    $field = em_wp_top_bar_form_option_key();
    ?>
    <div class="em-wp-admin-panel-body--top-border">
        <label class="em-wp-admin-inline-check em-wp-top-bar-bg-enable-check"><span><?php esc_html_e('Activer image de fond', 'em-wp'); ?></span><input id="em-wp-top-bar-bg-image-enabled" type="checkbox" name="<?php echo esc_attr($field); ?>[background_image_enabled]" value="1" <?php checked(!empty($options['background_image_enabled'])); ?>></label>
        <div id="em-wp-top-bar-bg-fields" class="em-wp-top-bar-bg-fields<?php echo empty($options['background_image_enabled']) ? ' is-disabled' : ''; ?>">
            <label class="em-wp-top-bar-background-image-label"><span><?php esc_html_e('Image de fond', 'em-wp'); ?></span></label>
            <div class="em-wp-admin-media-picker em-wp-top-bar-logo-picker">
                <input type="text" id="em-wp-top-bar-bg-image-url" name="<?php echo esc_attr($field); ?>[background_image_url]" value="<?php echo esc_attr($options['background_image_url'] ?? ''); ?>" class="regular-text em-wp-admin-field-input--wide">
                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-top-bar-bg-image-url" data-preview="em-wp-top-bar-bg-image-preview" data-modal-title="<?php echo esc_attr__('Choisir l\'image de fond Top Bar', 'em-wp'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser cette image de fond', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($field); ?>[background_image_hidden]" value="1" <?php checked(!empty($options['background_image_hidden'])); ?>></label>
            </div>
            <div id="em-wp-top-bar-bg-image-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['background_image_url']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['background_image_url'])) { ?><img src="<?php echo esc_url($options['background_image_url']); ?>" alt=""><?php } ?></div>
        </div>
    </div>
    <?php
}

/**
 * Rendu du panneau styles.
 *
 * @param array<string, mixed> $options
 */
function em_wp_top_bar_render_style_panel(array $options): void
{
    em_wp_admin_render_base_style_panel(
        __('Styles de base', 'em-wp'),
        [
            [
                'name'  => 'background_color',
                'label' => __('Couleur de fond', 'em-wp'),
                'value' => (string) ($options['background_color'] ?? ''),
            ],
            [
                'name'  => 'text_color',
                'label' => __('Couleur du texte', 'em-wp'),
                'value' => (string) ($options['text_color'] ?? ''),
            ],
        ],
        em_wp_top_bar_form_option_key(),
        'em-wp-top-bar-panel',
        static function () use ($options): void {
            em_wp_top_bar_render_style_panel_bg_image($options);
        }
    );
}
