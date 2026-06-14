<?php
/**
 * Partial : panneau item fixe Top Bar (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu d'un panneau item fixe.
 *
 * @param array<string, mixed> $item
 */
function em_wp_top_bar_render_item_panel(string $key, string $title, array $item): void
{
    $field = em_wp_top_bar_form_option_key();
    $is_hidden = !empty($item['hidden']);
    ?>
    <section class="em-wp-top-bar-panel em-wp-admin-module__panel">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-top-bar-panel')); ?>" type="button" aria-expanded="false">
            <?php em_wp_admin_render_panel_edit_trigger(); ?>
            <span class="em-wp-admin-module__item-header-line"><span class="em-wp-top-bar-panel__visibility em-wp-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-label="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-wp') : esc_attr__('Visible', 'em-wp'); ?>" title="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-wp') : esc_attr__('Visible', 'em-wp'); ?>"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i></span><?php em_wp_top_bar_render_position_indicator(em_wp_top_bar_item_position($key)); ?><span><?php echo esc_html($title); ?></span></span>
        </button>
        <div class="em-wp-admin-module__panel-body em-wp-admin-panel-body--row">
            <label><span><?php esc_html_e('Label', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[items][<?php echo esc_attr($key); ?>][label]" value="<?php echo esc_attr($item['label'] ?? ''); ?>"></label>
            <label><span><?php esc_html_e('Lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[items][<?php echo esc_attr($key); ?>][href]" value="<?php echo esc_attr($item['href'] ?? ''); ?>"></label>
            <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($field); ?>[items][<?php echo esc_attr($key); ?>][hidden]" value="1" <?php checked(!empty($item['hidden'])); ?>></label>
        </div>
    </section>
    <?php
}
