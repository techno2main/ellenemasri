<?php
/**
 * Partial : panneau texture CTA (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_render_texture_panel_body(array $options): void
{
    $field = em_wp_cta_form_option_key();
    ?>
    <p class="description"><?php esc_html_e('Texture superposée à la couleur de fond', 'em-wp'); ?></p>
    <label class="em-wp-admin-field--wide">
        <span><?php esc_html_e('Texture Image', 'em-wp'); ?></span>
        <div class="em-wp-admin-media-picker">
            <input type="text" id="em-wp-cta-texture" name="<?php echo esc_attr($field); ?>[texture_image]" value="<?php echo esc_attr($options['texture_image']); ?>" class="regular-text em-wp-admin-field-input--wide em-wp-admin-texture-field">
            <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-cta-texture" data-preview="em-wp-cta-texture-preview"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
        </div>
        <div id="em-wp-cta-texture-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['texture_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['texture_image'])) { ?><img src="<?php echo esc_url($options['texture_image']); ?>" alt=""><?php } ?></div>
    </label>
    <?php
}
