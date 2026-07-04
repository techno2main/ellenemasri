<?php
/**
 * Partial : panneau Contenu release (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_render_content_panel_body(array $options, ?string $field = null): void
{
    $field = $field ?? em_wp_release_form_option_key();
    ?>
    <label><span><?php esc_html_e('Kicker', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[kicker]" value="<?php echo esc_attr($options['kicker']); ?>"></label>
    <label><span><?php esc_html_e('Titre gauche', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[title_left]" value="<?php echo esc_attr($options['title_left']); ?>"></label>
    <label><span><?php esc_html_e('Titre surligné', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[title_highlight]" value="<?php echo esc_attr($options['title_highlight']); ?>"></label>
    <label class="em-wp-admin-field--wide">
        <span><?php esc_html_e('Cover', 'em-wp'); ?></span>
        <div class="em-wp-admin-media-picker">
            <input type="text" id="em-wp-release-cover" name="<?php echo esc_attr($field); ?>[cover_image]" value="<?php echo esc_attr($options['cover_image']); ?>" class="regular-text em-wp-admin-field-input--wide">
            <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-release-cover" data-preview="em-wp-release-cover-preview"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
        </div>
        <div id="em-wp-release-cover-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['cover_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['cover_image'])) { ?><img src="<?php echo esc_url($options['cover_image']); ?>" alt=""><?php } ?></div>
    </label>
    <?php
}
