<?php
/**
 * Partial : panneau Vidéo (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_video_render_video_panel_body(array $options): void
{
    $field = em_wp_video_form_option_key();
    ?>
    <label class="em-wp-admin-field--wide">
        <span><?php esc_html_e('Image de couverture', 'em-wp'); ?></span>
        <div class="em-wp-admin-media-picker">
            <input type="text" id="em-wp-video-cover" name="<?php echo esc_attr($field); ?>[cover_image]" value="<?php echo esc_attr($options['cover_image']); ?>" class="regular-text em-wp-admin-field-input--wide">
            <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-video-cover" data-preview="em-wp-video-cover-preview"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
        </div>
        <div id="em-wp-video-cover-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['cover_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['cover_image'])) { ?><img src="<?php echo esc_url($options['cover_image']); ?>" alt=""><?php } ?></div>
    </label>
    <label><span><?php esc_html_e('Label bouton Watch', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[watch_label]" value="<?php echo esc_attr($options['watch_label']); ?>"></label>
    <label><span><?php esc_html_e('Lien Watch', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[watch_href]" value="<?php echo esc_attr($options['watch_href']); ?>"></label>
    <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Désactiver le lien', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($field); ?>[watch_disable_link]" value="1" <?php checked(!empty($options['watch_disable_link'])); ?>></label>
    <?php
}
