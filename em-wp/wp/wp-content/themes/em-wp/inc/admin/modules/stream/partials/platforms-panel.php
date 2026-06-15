<?php
/**
 * Partial : panneau Plateformes stream (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<int, array<string, mixed>> $platforms
 * @param array<string, array{label:string,icon:string,color:string}> $definitions
 */
function em_wp_stream_render_platforms_panel_body(array $platforms, array $definitions, string $top_bar_url, ?string $field = null): void
{
    $field = $field ?? em_wp_stream_form_option_key();
    ?>
    <p class="description">
        <?php esc_html_e('Gestion des plateformes dans la section Stream (ordre, liens, libellés et activation).', 'em-wp'); ?>
        <br>
        <?php
        printf(
            /* translators: %s: link to TOP-BAR admin page */
            esc_html__('(L\'affichage des icônes dans la barre du haut peut être masqué dans %s)', 'em-wp'),
            '<a href="' . esc_url($top_bar_url) . '">TOP-BAR</a>'
        );
        ?>
    </p>
    <div class="em-wp-admin-nested-list em-wp-top-bar-platform-list" id="em-wp-stream-platform-list" data-option-name="<?php echo esc_attr($field); ?>" data-field-key="platforms">
        <?php foreach ($platforms as $list_index => $item) {
            em_wp_stream_render_platform_item((int) $list_index, $item, $definitions, $field);
        } ?>
    </div>
    <?php
}
