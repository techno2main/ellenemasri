<?php
/**
 * Partial : panneau Boutons CTA (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_cta_render_buttons_panel_body(array $options, ?string $field = null): void
{
    $field = $field ?? em_site_cta_form_option_key();
    ?>
    <div class="em-site-admin-panel-body--row">
        <label class="em-site-admin-field--compact"><span><?php esc_html_e('Stream label', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[stream_label]" value="<?php echo esc_attr($options['stream_label']); ?>"></label>
        <label class="em-site-admin-field--wide-inline"><span><?php esc_html_e('Stream lien', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[stream_link]" value="<?php echo esc_attr($options['stream_link']); ?>"></label>
    </div>
    <div class="em-site-admin-panel-body--row">
        <label class="em-site-admin-field--compact"><span><?php esc_html_e('Video label', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[video_label]" value="<?php echo esc_attr($options['video_label']); ?>"></label>
        <label class="em-site-admin-field--wide-inline"><span><?php esc_html_e('Video lien', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[video_link]" value="<?php echo esc_attr($options['video_link']); ?>"></label>
    </div>
    <div class="em-site-admin-panel-body--row">
        <label class="em-site-admin-field--compact"><span><?php esc_html_e('TikTok label', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[tiktok_label]" value="<?php echo esc_attr($options['tiktok_label']); ?>"></label>
        <label class="em-site-admin-field--wide-inline"><span><?php esc_html_e('TikTok lien', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[tiktok_link]" value="<?php echo esc_attr($options['tiktok_link']); ?>"></label>
    </div>
    <div class="em-site-admin-panel-body--row">
        <label class="em-site-admin-field--compact"><span><?php esc_html_e('Instagram label', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[instagram_label]" value="<?php echo esc_attr($options['instagram_label']); ?>"></label>
        <label class="em-site-admin-field--wide-inline"><span><?php esc_html_e('Instagram lien', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[instagram_link]" value="<?php echo esc_attr($options['instagram_link']); ?>"></label>
    </div>
    <?php
}
