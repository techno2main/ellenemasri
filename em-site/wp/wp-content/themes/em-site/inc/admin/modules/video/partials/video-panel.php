<?php
/**
 * Partial : panneau Vidéo (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_video_render_video_panel_body(array $options, ?string $field = null): void
{
    $field = $field ?? em_site_video_form_option_key();
    ?>
    <label class="em-site-admin-field--wide">
        <span><?php esc_html_e('Image de couverture', 'em-site'); ?></span>
        <div class="em-site-admin-media-picker">
            <input type="text" id="em-site-video-cover" name="<?php echo esc_attr($field); ?>[cover_image]" value="<?php echo esc_attr($options['cover_image']); ?>" class="regular-text em-site-admin-field-input--wide">
            <button type="button" class="button button-secondary em-site-admin-media-button em-site-top-bar-media-button" data-target="em-site-video-cover" data-preview="em-site-video-cover-preview"><?php esc_html_e('Modifier', 'em-site'); ?></button>
        </div>
        <div id="em-site-video-cover-preview" class="em-site-admin-media-preview em-site-admin-media-preview--checkerboard<?php echo empty($options['cover_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['cover_image'])) { ?><img src="<?php echo esc_url($options['cover_image']); ?>" alt=""><?php } ?></div>
    </label>
    <label><span><?php esc_html_e('Label bouton Watch', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[watch_label]" value="<?php echo esc_attr($options['watch_label']); ?>"></label>
    <label><span><?php esc_html_e('Lien Watch', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[watch_href]" value="<?php echo esc_attr($options['watch_href']); ?>"></label>
    <label class="em-site-admin-inline-check"><span><?php esc_html_e('Désactiver le lien', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($field); ?>[watch_disable_link]" value="1" <?php checked(!empty($options['watch_disable_link'])); ?>></label>
    <?php
}
