<?php
/**
 * Partial : panneau logo Top Bar (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu du panneau logo.
 *
 * @param array<string, mixed> $options
 */
function em_site_top_bar_render_logo_panel(array $options, ?string $field = null): void
{
    $field = $field ?? em_site_top_bar_form_option_key();
    $is_hidden = !empty($options['logo_hidden']);
    ?>
    <section class="em-site-top-bar-panel em-site-admin-module__panel">
        <button class="<?php echo esc_attr(em_site_admin_panel_header_class('em-site-top-bar-panel')); ?>" type="button" aria-expanded="false">
            <?php em_site_admin_render_panel_edit_trigger(); ?>
            <span class="em-site-admin-module__item-header-line"><span class="em-site-top-bar-panel__visibility em-site-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-label="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-site') : esc_attr__('Visible', 'em-site'); ?>" title="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-site') : esc_attr__('Visible', 'em-site'); ?>"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i></span><?php em_site_top_bar_render_position_indicator(em_site_top_bar_item_position('logo')); ?><span><?php esc_html_e('Logo', 'em-site'); ?></span></span>
        </button>
        <div class="em-site-admin-module__panel-body">
            <div class="em-site-admin-media-picker em-site-top-bar-logo-picker" data-target="em-site-top-bar-logo-url">
                <input type="text" id="em-site-top-bar-logo-url" name="<?php echo esc_attr($field); ?>[logo_url]" value="<?php echo esc_attr($options['logo_url']); ?>" class="regular-text em-site-admin-field-input--wide">
                <button type="button" class="button button-secondary em-site-admin-media-button em-site-top-bar-media-button" data-target="em-site-top-bar-logo-url" data-preview="em-site-top-bar-logo-preview" data-modal-title="<?php echo esc_attr__('Choisir le logo', 'em-site'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce logo', 'em-site'); ?>"><?php esc_html_e('Modifier', 'em-site'); ?></button>
                <label class="em-site-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($field); ?>[logo_hidden]" value="1" <?php checked(!empty($options['logo_hidden'])); ?>></label>
            </div>
            <div id="em-site-top-bar-logo-preview" class="em-site-admin-media-preview em-site-admin-media-preview--checkerboard<?php echo empty($options['logo_url']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['logo_url'])) { ?><img src="<?php echo esc_url($options['logo_url']); ?>" alt=""><?php } ?></div>
        </div>
    </section>
    <?php
}
