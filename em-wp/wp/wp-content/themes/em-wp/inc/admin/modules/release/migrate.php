<?php
/**
 * Migration V1 em_wp_release_options → em_wp_release_mayami_options.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_maybe_migrate_template_options(): void
{
    $legacy = get_option('em_wp_release_options', null);

    if ($legacy === null || !is_array($legacy)) {
        return;
    }

    $target_name = em_wp_template_option_name('release', em_wp_template_default_slug());

    if (get_option($target_name, null) !== null) {
        return;
    }

    update_option($target_name, $legacy, false);
}
add_action('init', 'em_wp_release_maybe_migrate_template_options', 6);
