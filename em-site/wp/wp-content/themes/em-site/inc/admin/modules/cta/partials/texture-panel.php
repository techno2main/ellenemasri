<?php
/**
 * Partial : panneau texture CTA (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_cta_render_texture_panel_body(array $options, ?string $field = null): void
{
    $field = $field ?? em_site_cta_form_option_key();
    ?>
    <p class="description"><?php esc_html_e('Texture superposée à la couleur de fond', 'em-site'); ?></p>
    <label class="em-site-admin-field--wide">
        <span><?php esc_html_e('Texture Image', 'em-site'); ?></span>
        <div class="em-site-admin-media-picker">
            <input type="text" id="em-site-cta-texture" name="<?php echo esc_attr($field); ?>[texture_image]" value="<?php echo esc_attr($options['texture_image']); ?>" class="regular-text em-site-admin-field-input--wide em-site-admin-texture-field">
            <button type="button" class="button button-secondary em-site-admin-media-button em-site-top-bar-media-button" data-target="em-site-cta-texture" data-preview="em-site-cta-texture-preview"><?php esc_html_e('Modifier', 'em-site'); ?></button>
        </div>
        <div id="em-site-cta-texture-preview" class="em-site-admin-media-preview em-site-admin-media-preview--checkerboard<?php echo empty($options['texture_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['texture_image'])) { ?><img src="<?php echo esc_url($options['texture_image']); ?>" alt=""><?php } ?></div>
    </label>
    <?php
}
