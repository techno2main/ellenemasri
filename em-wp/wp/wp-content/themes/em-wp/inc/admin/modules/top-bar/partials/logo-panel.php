<?php
/**
 * Partial : panneau logo Top Bar (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu du panneau logo.
 *
 * @param array<string, mixed> $options
 */
function em_wp_top_bar_render_logo_panel(array $options, ?string $field = null): void
{
    $field = $field ?? em_wp_top_bar_form_option_key();
    $is_hidden = !empty($options['logo_hidden']);
    ?>
    <section class="em-wp-top-bar-panel em-wp-admin-module__panel">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-top-bar-panel')); ?>" type="button" aria-expanded="false">
            <?php em_wp_admin_render_panel_edit_trigger(); ?>
            <span class="em-wp-admin-module__item-header-line"><span class="em-wp-top-bar-panel__visibility em-wp-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-label="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-wp') : esc_attr__('Visible', 'em-wp'); ?>" title="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-wp') : esc_attr__('Visible', 'em-wp'); ?>"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i></span><?php em_wp_top_bar_render_position_indicator(em_wp_top_bar_item_position('logo')); ?><span><?php esc_html_e('Logo', 'em-wp'); ?></span></span>
        </button>
        <div class="em-wp-admin-module__panel-body">
            <div class="em-wp-admin-media-picker em-wp-top-bar-logo-picker" data-target="em-wp-top-bar-logo-url">
                <input type="text" id="em-wp-top-bar-logo-url" name="<?php echo esc_attr($field); ?>[logo_url]" value="<?php echo esc_attr($options['logo_url']); ?>" class="regular-text em-wp-admin-field-input--wide">
                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-top-bar-logo-url" data-preview="em-wp-top-bar-logo-preview" data-modal-title="<?php echo esc_attr__('Choisir le logo', 'em-wp'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce logo', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($field); ?>[logo_hidden]" value="1" <?php checked(!empty($options['logo_hidden'])); ?>></label>
            </div>
            <div id="em-wp-top-bar-logo-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['logo_url']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['logo_url'])) { ?><img src="<?php echo esc_url($options['logo_url']); ?>" alt=""><?php } ?></div>
        </div>
    </section>
    <?php
}
