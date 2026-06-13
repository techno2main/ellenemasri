<?php
/**
 * Partial : panneaux Footer (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_footer_render_content_panel_body(array $options): void
{
    $field = em_wp_footer_form_option_key();
    ?>
    <label><span><?php esc_html_e('Ligne 1', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[line1]" value="<?php echo esc_attr($options['line1']); ?>"></label>
    <label><span><?php esc_html_e('Ligne 2', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[line2]" value="<?php echo esc_attr($options['line2']); ?>"></label>
    <?php
}

function em_wp_footer_render_sticky_panel_body(array $options): void
{
    $field = em_wp_footer_form_option_key();
    ?>
    <label><span><?php esc_html_e('Label Stream', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[sticky_stream_label]" value="<?php echo esc_attr($options['sticky_stream_label']); ?>"></label>
    <label><span><?php esc_html_e('Label Video', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[sticky_video_label]" value="<?php echo esc_attr($options['sticky_video_label']); ?>"></label>
    <label><span><?php esc_html_e('Label TikTok', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[sticky_tiktok_label]" value="<?php echo esc_attr($options['sticky_tiktok_label']); ?>"></label>
    <label><span><?php esc_html_e('Lien TikTok', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[sticky_tiktok_link]" value="<?php echo esc_attr($options['sticky_tiktok_link']); ?>"></label>
    <?php
}
