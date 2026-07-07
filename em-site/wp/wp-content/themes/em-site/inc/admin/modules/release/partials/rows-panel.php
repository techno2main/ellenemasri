<?php
/**
 * Partial : panneau Infos release (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<int, array{key:string,value:string,hidden?:bool}> $rows
 */
function em_site_release_render_rows_panel_body(array $rows, ?string $field = null): void
{
    $field = $field ?? em_site_release_form_option_key();
    ?>
    <div class="em-site-release-rows-list" id="em-site-release-rows-list" data-option-name="<?php echo esc_attr($field); ?>" data-field-key="rows">
        <?php foreach ($rows as $index => $row) {
            em_site_release_render_row_item((int) $index, $row, $field);
        } ?>
    </div>
    <p><button type="button" class="button button-secondary" id="em-site-release-add-row"><?php esc_html_e('+ Ajouter une info', 'em-site'); ?></button></p>
    <?php
}
