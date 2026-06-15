<?php
/**
 * Partial : panneau Boutons CTA (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_render_buttons_panel_body(array $options, ?string $field = null): void
{
    $field = $field ?? em_wp_cta_form_option_key();
    ?>
    <div class="em-wp-admin-panel-body--row">
        <label class="em-wp-admin-field--compact"><span><?php esc_html_e('Stream label', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[stream_label]" value="<?php echo esc_attr($options['stream_label']); ?>"></label>
        <label class="em-wp-admin-field--wide-inline"><span><?php esc_html_e('Stream lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[stream_link]" value="<?php echo esc_attr($options['stream_link']); ?>"></label>
    </div>
    <div class="em-wp-admin-panel-body--row">
        <label class="em-wp-admin-field--compact"><span><?php esc_html_e('Video label', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[video_label]" value="<?php echo esc_attr($options['video_label']); ?>"></label>
        <label class="em-wp-admin-field--wide-inline"><span><?php esc_html_e('Video lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[video_link]" value="<?php echo esc_attr($options['video_link']); ?>"></label>
    </div>
    <div class="em-wp-admin-panel-body--row">
        <label class="em-wp-admin-field--compact"><span><?php esc_html_e('TikTok label', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[tiktok_label]" value="<?php echo esc_attr($options['tiktok_label']); ?>"></label>
        <label class="em-wp-admin-field--wide-inline"><span><?php esc_html_e('TikTok lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[tiktok_link]" value="<?php echo esc_attr($options['tiktok_link']); ?>"></label>
    </div>
    <div class="em-wp-admin-panel-body--row">
        <label class="em-wp-admin-field--compact"><span><?php esc_html_e('Instagram label', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[instagram_label]" value="<?php echo esc_attr($options['instagram_label']); ?>"></label>
        <label class="em-wp-admin-field--wide-inline"><span><?php esc_html_e('Instagram lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[instagram_link]" value="<?php echo esc_attr($options['instagram_link']); ?>"></label>
    </div>
    <?php
}
