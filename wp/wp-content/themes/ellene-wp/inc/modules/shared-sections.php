<?php

/**
 * Shared sections helpers.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if current module is rendered in shared mode.
 *
 * @param string $module_slug Module slug.
 * @return bool
 */
function ellene_is_module_shared($module_slug) {
    $requested_slug = sanitize_key((string) $module_slug);
    $current_slug = sanitize_key((string) get_query_var('ellene_module_slug', ''));
    $current_mode = sanitize_key((string) get_query_var('ellene_module_mode', 'local'));

    return ($requested_slug !== '' && $requested_slug === $current_slug && $current_mode === 'shared');
}
