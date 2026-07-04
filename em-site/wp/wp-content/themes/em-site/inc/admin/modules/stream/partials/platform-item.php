<?php
/**
 * Partial : item plateforme stream (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<string, array{label:string,icon:string,color:string}> $definitions
 * @param array<string, mixed> $item
 */
function em_wp_stream_render_platform_item(int $list_index, array $item, array $definitions, ?string $field = null): void
{
    $slug = sanitize_key((string) ($item['slug'] ?? ''));
    $platform = $definitions[$slug] ?? null;

    if (!is_array($platform)) {
        return;
    }

    $field_base = ($field ?? em_wp_stream_form_option_key()) . '[platforms][' . $list_index . ']';
    $label_value = (string) ($item['label'] ?? $platform['label']);
    $href_value = (string) ($item['href'] ?? '');
    $is_active = !empty($item['active']);
    ?>
    <details class="em-wp-admin-nested-item em-wp-top-bar-platform-item" data-stream-link-item data-list-index="<?php echo esc_attr((string) $list_index); ?>">
        <summary>
            <?php em_wp_admin_render_panel_edit_trigger(); ?>
            <span class="em-wp-top-bar-platform-item__label">
                <span class="em-wp-top-bar-panel__visibility em-wp-admin-module__item-visibility<?php echo $is_active ? '' : ' is-hidden'; ?>" aria-label="<?php echo $is_active ? esc_attr__('Actif', 'em-wp') : esc_attr__('Inactif', 'em-wp'); ?>" title="<?php echo $is_active ? esc_attr__('Actif', 'em-wp') : esc_attr__('Inactif', 'em-wp'); ?>"><i class="fa-solid <?php echo $is_active ? 'fa-eye' : 'fa-eye-slash'; ?>" aria-hidden="true"></i></span>
                <i class="fa-brands <?php echo esc_attr($platform['icon']); ?>" aria-hidden="true"></i>
                <span><?php echo esc_html($platform['label']); ?></span>
            </span>
        </summary>
        <div class="em-wp-top-bar-platform-item__summary-actions">
            <span class="em-wp-top-bar-platform-item__order">
                <button type="button" class="em-wp-top-bar-platform-item__move em-wp-top-bar-platform-item__move--up" aria-label="<?php esc_attr_e('Monter', 'em-wp'); ?>" title="<?php esc_attr_e('Monter', 'em-wp'); ?>"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i></button>
                <button type="button" class="em-wp-top-bar-platform-item__move em-wp-top-bar-platform-item__move--down" aria-label="<?php esc_attr_e('Descendre', 'em-wp'); ?>" title="<?php esc_attr_e('Descendre', 'em-wp'); ?>"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                <span class="em-wp-slide-sortable__handle em-wp-top-bar-platform-item__drag" role="button" tabindex="0" aria-label="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>" title="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
            </span>
        </div>
        <div class="em-wp-admin-nested-item__body em-wp-admin-panel-body--row em-wp-top-bar-platform-item__body">
            <input type="hidden" name="<?php echo esc_attr($field_base . '[slug]'); ?>" value="<?php echo esc_attr($slug); ?>">
            <label class="em-wp-admin-field--compact"><span><?php esc_html_e('Label', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[label]'); ?>" value="<?php echo esc_attr($label_value); ?>"></label>
            <label class="em-wp-admin-field--wide-inline"><span><?php esc_html_e('Lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[href]'); ?>" value="<?php echo esc_attr($href_value); ?>"></label>
            <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Actif', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($field_base . '[active]'); ?>" value="1" <?php checked($is_active); ?>></label>
        </div>
    </details>
    <?php
}
