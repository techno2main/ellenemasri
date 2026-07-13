<?php
/**
 * Partial : panneau icônes stream Top Bar (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu du panneau icônes stream (affichage / masquage de la section uniquement).
 *
 * @param array<string, mixed> $options
 */
function em_site_top_bar_render_stream_icons_panel(array $options, ?string $field = null): void
{
    $field = $field ?? em_site_top_bar_form_option_key();
    $is_hidden = !empty($options['stream_icons_hidden']);
    $stream_url = admin_url('admin.php?page=' . (function_exists('em_site_stream_page_slug') ? em_site_stream_page_slug() : 'em-stream'));
    ?>
    <section class="em-site-top-bar-panel em-site-admin-module__panel">
        <button class="<?php echo esc_attr(em_site_admin_panel_header_class('em-site-top-bar-panel')); ?>" type="button" aria-expanded="false">
            <?php em_site_admin_render_panel_edit_trigger(); ?>
            <span class="em-site-admin-module__item-header-line"><span class="em-site-top-bar-panel__visibility em-site-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-label="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-site') : esc_attr__('Visible', 'em-site'); ?>" title="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-site') : esc_attr__('Visible', 'em-site'); ?>"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i></span><?php em_site_top_bar_render_position_indicator(em_site_top_bar_item_position('stream_icons')); ?><span><?php esc_html_e('Stream Icons', 'em-site'); ?></span></span>
        </button>
        <div class="em-site-admin-module__panel-body em-site-admin-panel-body--row">
            <p class="description">
                <?php
                printf(
                    /* translators: %s: link to STREAM admin page */
                    esc_html__('Icônes des plateformes actives dans la barre du haut. Ordre, liens et activation se configurent dans %s.', 'em-site'),
                    '<a href="' . esc_url($stream_url) . '">STREAM</a>'
                );
                ?>
            </p>
            <label class="em-site-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($field); ?>[stream_icons_hidden]" value="1" <?php checked($is_hidden); ?>></label>
        </div>
    </section>
    <?php
}

/**
 * @deprecated Liste par plateforme supprimée — utiliser em_site_top_bar_render_stream_icons_panel().
 *
 * @param array<string, mixed> $stream_links
 */
function em_site_top_bar_render_stream_links_panel(array $stream_links): void
{
    unset($stream_links);
    em_site_top_bar_render_stream_icons_panel(em_site_top_bar_get_options());
}
