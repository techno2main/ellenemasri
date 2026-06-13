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
function em_wp_social_render_platforms_panel_body(array $platforms, array $definitions): void
{
    ?>
    <div class="em-wp-admin-nested-list em-wp-top-bar-platform-list">
        <?php foreach ($platforms as $list_index => $item) {
            em_wp_social_render_platform_item((int) $list_index, $item, $definitions);
        } ?>
    </div>
    <?php
}
