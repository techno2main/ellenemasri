<?php
/**
 * Partial : panneau Contenu stream (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<string, mixed> $options
 */
function em_site_stream_render_content_panel_body(array $options, ?string $field = null): void
{
    $field = $field ?? em_site_stream_form_option_key();
    ?>
    <label>
        <span><?php esc_html_e('Kicker', 'em-site'); ?></span>
        <input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[kicker]" value="<?php echo esc_attr($options['kicker']); ?>">
    </label>
    <label>
        <span><?php esc_html_e('Title Prefix', 'em-site'); ?></span>
        <input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[title_prefix]" value="<?php echo esc_attr($options['title_prefix']); ?>">
    </label>
    <label class="em-site-admin-field--wide">
        <span><?php esc_html_e('Title Logo', 'em-site'); ?></span>
        <div class="em-site-admin-media-picker">
            <input type="text" id="em-site-stream-title-logo" name="<?php echo esc_attr($field); ?>[title_logo]" value="<?php echo esc_attr($options['title_logo']); ?>" class="regular-text em-site-admin-field-input--wide">
            <button type="button" class="button button-secondary em-site-admin-media-button em-site-top-bar-media-button" data-target="em-site-stream-title-logo" data-preview="em-site-stream-title-logo-preview" data-modal-title="<?php echo esc_attr__('Choisir le logo titre', 'em-site'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser cette image', 'em-site'); ?>"><?php esc_html_e('Modifier', 'em-site'); ?></button>
        </div>
        <p class="description"><?php esc_html_e('Logo image affichée à droite de « Stream » dans la section front.', 'em-site'); ?></p>
        <div id="em-site-stream-title-logo-preview" class="em-site-admin-media-preview em-site-admin-media-preview--checkerboard<?php echo empty($options['title_logo']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['title_logo'])) { ?><img src="<?php echo esc_url($options['title_logo']); ?>" alt=""><?php } ?></div>
    </label>
    <label>
        <span><?php esc_html_e('Availability Text', 'em-site'); ?></span>
        <input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[availability_text]" value="<?php echo esc_attr($options['availability_text']); ?>">
    </label>
    <label>
        <span><?php esc_html_e('Card Label', 'em-site'); ?></span>
        <input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[card_label]" value="<?php echo esc_attr($options['card_label']); ?>">
    </label>
    <?php
}
