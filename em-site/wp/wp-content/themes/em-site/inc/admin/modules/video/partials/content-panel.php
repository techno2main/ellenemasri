<?php
/**
 * Partial : panneau Contenu video (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_video_render_content_panel_body(array $options, ?string $field = null): void
{
    $field = $field ?? em_site_video_form_option_key();
    ?>
    <label><span><?php esc_html_e('Kicker', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[kicker]" value="<?php echo esc_attr($options['kicker']); ?>"></label>
    <label><span><?php esc_html_e('Titre', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[title]" value="<?php echo esc_attr($options['title']); ?>"></label>
    <label><span><?php esc_html_e('Description', 'em-site'); ?></span><textarea class="large-text" rows="3" name="<?php echo esc_attr($field); ?>[description]"><?php echo esc_textarea($options['description']); ?></textarea></label>
    <?php
}
