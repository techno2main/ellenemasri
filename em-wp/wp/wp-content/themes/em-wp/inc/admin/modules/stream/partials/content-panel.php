<?php
/**
 * Partial : panneau Contenu stream (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<string, mixed> $options
 */
function em_wp_stream_render_content_panel_body(array $options, ?string $field = null): void
{
    $field = $field ?? em_wp_stream_form_option_key();
    ?>
    <label>
        <span><?php esc_html_e('Kicker', 'em-wp'); ?></span>
        <input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[kicker]" value="<?php echo esc_attr($options['kicker']); ?>">
    </label>
    <label>
        <span><?php esc_html_e('Title Prefix', 'em-wp'); ?></span>
        <input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[title_prefix]" value="<?php echo esc_attr($options['title_prefix']); ?>">
    </label>
    <label class="em-wp-admin-field--wide">
        <span><?php esc_html_e('Title Logo', 'em-wp'); ?></span>
        <div class="em-wp-admin-media-picker">
            <input type="text" id="em-wp-stream-title-logo" name="<?php echo esc_attr($field); ?>[title_logo]" value="<?php echo esc_attr($options['title_logo']); ?>" class="regular-text em-wp-admin-field-input--wide">
            <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-stream-title-logo" data-preview="em-wp-stream-title-logo-preview" data-modal-title="<?php echo esc_attr__('Choisir le logo titre', 'em-wp'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser cette image', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
        </div>
        <p class="description"><?php esc_html_e('Logo image affichée à droite de « Stream » dans la section front.', 'em-wp'); ?></p>
        <div id="em-wp-stream-title-logo-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['title_logo']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['title_logo'])) { ?><img src="<?php echo esc_url($options['title_logo']); ?>" alt=""><?php } ?></div>
    </label>
    <label>
        <span><?php esc_html_e('Availability Text', 'em-wp'); ?></span>
        <input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[availability_text]" value="<?php echo esc_attr($options['availability_text']); ?>">
    </label>
    <label>
        <span><?php esc_html_e('Card Label', 'em-wp'); ?></span>
        <input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[card_label]" value="<?php echo esc_attr($options['card_label']); ?>">
    </label>
    <?php
}
