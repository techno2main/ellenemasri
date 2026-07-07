<?php
/**
 * Partial : panneau Contenu release (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_release_render_content_panel_body(array $options, ?string $field = null): void
{
    $field = $field ?? em_site_release_form_option_key();
    ?>
    <label><span><?php esc_html_e('Kicker', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[kicker]" value="<?php echo esc_attr($options['kicker']); ?>"></label>
    <label><span><?php esc_html_e('Titre gauche', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[title_left]" value="<?php echo esc_attr($options['title_left']); ?>"></label>
    <label><span><?php esc_html_e('Titre surligné', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[title_highlight]" value="<?php echo esc_attr($options['title_highlight']); ?>"></label>
    <label class="em-site-admin-field--wide">
        <span><?php esc_html_e('Cover', 'em-site'); ?></span>
        <div class="em-site-admin-media-picker">
            <input type="text" id="em-site-release-cover" name="<?php echo esc_attr($field); ?>[cover_image]" value="<?php echo esc_attr($options['cover_image']); ?>" class="regular-text em-site-admin-field-input--wide">
            <button type="button" class="button button-secondary em-site-admin-media-button em-site-top-bar-media-button" data-target="em-site-release-cover" data-preview="em-site-release-cover-preview"><?php esc_html_e('Modifier', 'em-site'); ?></button>
        </div>
        <div id="em-site-release-cover-preview" class="em-site-admin-media-preview em-site-admin-media-preview--checkerboard<?php echo empty($options['cover_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['cover_image'])) { ?><img src="<?php echo esc_url($options['cover_image']); ?>" alt=""><?php } ?></div>
    </label>
    <?php
}
