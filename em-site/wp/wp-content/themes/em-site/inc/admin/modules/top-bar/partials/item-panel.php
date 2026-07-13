<?php
/**
 * Partial : panneau item fixe Top Bar (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu d'un panneau item fixe.
 *
 * @param array<string, mixed> $item
 */
function em_site_top_bar_render_item_panel(string $key, string $title, array $item, ?string $field = null): void
{
    $field = $field ?? em_site_top_bar_form_option_key();
    $is_hidden = !empty($item['hidden']);
    ?>
    <section class="em-site-top-bar-panel em-site-admin-module__panel">
        <button class="<?php echo esc_attr(em_site_admin_panel_header_class('em-site-top-bar-panel')); ?>" type="button" aria-expanded="false">
            <?php em_site_admin_render_panel_edit_trigger(); ?>
            <span class="em-site-admin-module__item-header-line"><span class="em-site-top-bar-panel__visibility em-site-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-label="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-site') : esc_attr__('Visible', 'em-site'); ?>" title="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-site') : esc_attr__('Visible', 'em-site'); ?>"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i></span><?php em_site_top_bar_render_position_indicator(em_site_top_bar_item_position($key)); ?><span><?php echo esc_html($title); ?></span></span>
        </button>
        <div class="em-site-admin-module__panel-body em-site-admin-panel-body--row">
            <label><span><?php esc_html_e('Label', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[items][<?php echo esc_attr($key); ?>][label]" value="<?php echo esc_attr($item['label'] ?? ''); ?>"></label>
            <label><span><?php esc_html_e('Lien', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field); ?>[items][<?php echo esc_attr($key); ?>][href]" value="<?php echo esc_attr($item['href'] ?? ''); ?>"></label>
            <?php if (em_site_top_bar_item_supports_style($key)) {
                $font_value = (string) ($item['font'] ?? '');
                $font_stack = em_site_top_bar_admin_font_stack($font_value);
                $text_color = (string) ($item['text_color'] ?? '');
                $preview_text = trim((string) ($item['label'] ?? '')) !== ''
                    ? (string) $item['label']
                    : __('Aperçu', 'em-site');
                $preview_style = '';
                if ($font_stack !== '') {
                    $preview_style .= 'font-family: ' . esc_attr($font_stack) . ';';
                }
                if ($text_color !== '') {
                    $preview_style .= 'color: ' . esc_attr($text_color) . ';';
                }
                ?>
                <label><span><?php esc_html_e('Typo', 'em-site'); ?></span><select class="em-site-top-bar-typo-select" data-em-site-topbar-font name="<?php echo esc_attr($field); ?>[items][<?php echo esc_attr($key); ?>][font]">
                    <?php foreach (em_site_top_bar_font_choices() as $font_slug => $font) { ?>
                        <option value="<?php echo esc_attr($font_slug); ?>" data-stack="<?php echo esc_attr($font['stack']); ?>" <?php selected($font_value, $font_slug); ?>><?php echo esc_html($font['label']); ?></option>
                    <?php } ?>
                </select></label>
                <label><span><?php esc_html_e('Aperçu', 'em-site'); ?></span><span class="em-site-top-bar-typo-preview" data-em-site-topbar-typo-preview style="<?php echo esc_attr($preview_style); ?>"><?php echo esc_html($preview_text); ?></span></label>
                <?php em_site_admin_render_color_field([
                    'id'          => $field . '-items-' . $key . '-text_color',
                    'name'        => $field . '[items][' . $key . '][text_color]',
                    'value'       => $text_color,
                    'field_label' => __('Couleur texte', 'em-site'),
                    'preview_label' => __('Texte', 'em-site'),
                    'modal_title' => __('Couleur du texte', 'em-site'),
                ]);
            } ?>
            <label class="em-site-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($field); ?>[items][<?php echo esc_attr($key); ?>][hidden]" value="1" <?php checked(!empty($item['hidden'])); ?>></label>
        </div>
    </section>
    <?php
}
