<?php
/**
 * Partial : panneau Infos release (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<int, array{key:string,value:string,hidden?:bool}> $rows
 */
function em_wp_release_render_rows_panel_body(array $rows): void
{
    $field = em_wp_release_form_option_key();
    ?>
    <div class="em-wp-release-rows-list" id="em-wp-release-rows-list" data-option-name="<?php echo esc_attr($field); ?>" data-field-key="rows">
        <?php foreach ($rows as $index => $row) {
            em_wp_release_render_row_item((int) $index, $row);
        } ?>
    </div>
    <p><button type="button" class="button button-secondary" id="em-wp-release-add-row"><?php esc_html_e('+ Ajouter une info', 'em-wp'); ?></button></p>
    <?php
}
