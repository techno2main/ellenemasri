<?php
/**
 * Partial : item ligne release (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array{key:string,value:string,hidden?:bool} $row
 */
function em_site_release_render_row_item(int $index, array $row, ?string $field = null): void
{
    $field = $field ?? em_site_release_form_option_key();
    $field_base = $field . '[rows][' . $index . ']';
    $is_hidden = !empty($row['hidden']);
    ?>
    <div class="em-site-release-row-item em-site-admin-panel-body--row<?php echo $is_hidden ? ' is-row-hidden' : ''; ?>" data-release-row-item>
        <span class="em-site-slide-sortable__handle em-site-release-row-item__handle" role="button" tabindex="0" aria-label="<?php esc_attr_e('Glisser pour réordonner', 'em-site'); ?>" title="<?php esc_attr_e('Glisser pour réordonner', 'em-site'); ?>"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
        <label class="em-site-admin-field--compact">
            <span><?php esc_html_e('Label', 'em-site'); ?></span>
            <input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[key]'); ?>" value="<?php echo esc_attr($row['key']); ?>">
        </label>
        <label class="em-site-admin-field--wide-inline">
            <span><?php esc_html_e('Valeur', 'em-site'); ?></span>
            <input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[value]'); ?>" value="<?php echo esc_attr($row['value']); ?>">
        </label>
        <div class="em-site-release-row-item__actions">
            <label class="em-site-admin-inline-check">
                <span><?php esc_html_e('Masquer', 'em-site'); ?></span>
                <input type="checkbox" class="em-site-release-row-hidden" name="<?php echo esc_attr($field_base . '[hidden]'); ?>" value="1" <?php checked($is_hidden); ?>>
            </label>
            <button type="button" class="button button-link-delete em-site-release-row-delete"><?php esc_html_e('Supprimer', 'em-site'); ?></button>
        </div>
    </div>
    <?php
}
