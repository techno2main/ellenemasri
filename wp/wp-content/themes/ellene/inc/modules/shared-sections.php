<?php

/**
 * Ellene shared sections helpers.
 *
 * @package Mayami
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

/**
 * Check whether shared source is explicitly enabled for a module.
 *
 * @param string $module_slug Module slug.
 * @return bool
 */
function ellene_is_shared_source_enabled($module_slug) {
    $module_slug = sanitize_key((string) $module_slug);
    if ($module_slug === '') {
        return false;
    }

    $toggle_key = 'shared_' . $module_slug . '_enabled';
    return !empty(mayami_get_landing_option($toggle_key, ''));
}

/**
 * Return the option key for a module/field pair depending on render mode.
 *
 * @param string $module_slug Module slug.
 * @param string $field_id Field id without prefix.
 * @return string
 */
function ellene_get_module_option_key($module_slug, $field_id) {
    $module_slug = sanitize_key((string) $module_slug);
    $field_id = sanitize_key((string) $field_id);

    if ($module_slug === '' || $field_id === '') {
        return '';
    }

    if (ellene_is_module_shared($module_slug) && ellene_is_shared_source_enabled($module_slug)) {
        return 'shared_' . $module_slug . '_' . $field_id;
    }

    return $module_slug . '_' . $field_id;
}

/**
 * Read a module option from the selected source (local or shared).
 *
 * @param string $module_slug Module slug.
 * @param string $field_id Field id without prefix.
 * @param mixed  $default Default value.
 * @return mixed
 */
function ellene_get_module_option($module_slug, $field_id, $default = '') {
    $key = ellene_get_module_option_key($module_slug, $field_id);
    if ($key === '') {
        return $default;
    }

    $value = mayami_get_landing_option($key, null);
    if ($value !== null && $value !== '') {
        return $value;
    }

    return $default;
}
