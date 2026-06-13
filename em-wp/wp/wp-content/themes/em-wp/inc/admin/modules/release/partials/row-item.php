<?php
/**
 * Partial : item ligne release (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array{key:string,value:string,hidden?:bool} $row
 */
function em_wp_release_render_row_item(int $index, array $row): void
{
    $field_base = em_wp_release_form_option_key() . '[rows][' . $index . ']';
    $is_hidden = !empty($row['hidden']);
    ?>
    <div class="em-wp-release-row-item em-wp-admin-panel-body--row<?php echo $is_hidden ? ' is-row-hidden' : ''; ?>" data-release-row-item>
        <span class="em-wp-slide-sortable__handle em-wp-release-row-item__handle" role="button" tabindex="0" aria-label="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>" title="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
        <label class="em-wp-admin-field--compact">
            <span><?php esc_html_e('Label', 'em-wp'); ?></span>
            <input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[key]'); ?>" value="<?php echo esc_attr($row['key']); ?>">
        </label>
        <label class="em-wp-admin-field--wide-inline">
            <span><?php esc_html_e('Valeur', 'em-wp'); ?></span>
            <input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[value]'); ?>" value="<?php echo esc_attr($row['value']); ?>">
        </label>
        <div class="em-wp-release-row-item__actions">
            <label class="em-wp-admin-inline-check">
                <span><?php esc_html_e('Masquer', 'em-wp'); ?></span>
                <input type="checkbox" class="em-wp-release-row-hidden" name="<?php echo esc_attr($field_base . '[hidden]'); ?>" value="1" <?php checked($is_hidden); ?>>
            </label>
            <button type="button" class="button button-link-delete em-wp-release-row-delete"><?php esc_html_e('Supprimer', 'em-wp'); ?></button>
        </div>
    </div>
    <?php
}
