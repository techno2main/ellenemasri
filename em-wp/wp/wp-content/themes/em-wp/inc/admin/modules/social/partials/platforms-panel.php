<?php
/**
 * Partial : panneau Plateformes social (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<int, array<string, mixed>> $platforms
 * @param array<string, array{label:string,icon:string,default_account:string}> $definitions
 */
function em_wp_social_render_platforms_panel_body(array $platforms, array $definitions, ?string $field = null): void
{
    $field = $field ?? em_wp_social_form_option_key();
    ?>
    <div class="em-wp-admin-nested-list em-wp-top-bar-platform-list" data-option-name="<?php echo esc_attr($field); ?>" data-field-key="platforms">
        <?php foreach ($platforms as $list_index => $item) {
            em_wp_social_render_platform_item((int) $list_index, $item, $definitions, $field);
        } ?>
    </div>
    <?php
}

