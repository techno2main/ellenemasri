<?php
/**
 * Partial : panneau Contenu social (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_social_render_content_panel_body(array $options): void
{
    $field = em_wp_social_form_option_key();
    ?>
    <label><span><?php esc_html_e('Kicker', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[kicker]" value="<?php echo esc_attr($options['kicker']); ?>"></label>
    <label><span><?php esc_html_e('Titre gauche', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[title_left]" value="<?php echo esc_attr($options['title_left']); ?>"></label>
    <label><span><?php esc_html_e('Titre droite', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[title_right]" value="<?php echo esc_attr($options['title_right']); ?>"></label>
    <label><span><?php esc_html_e('Description', 'em-wp'); ?></span><textarea class="large-text" rows="3" name="<?php echo esc_attr($field); ?>[description]"><?php echo esc_textarea($options['description']); ?></textarea></label>
    <?php
}
