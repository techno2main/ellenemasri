<?php

/**
 * Ellene module renderer.
 *
 * @package Mayami
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render resolved content modules.
 *
 * @param string $context Context slug.
 * @return void
 */
function ellene_render_content_modules($context = 'home') {
    $modules = ellene_resolve_modules($context);

    foreach ($modules as $module) {
        if (!is_array($module)) {
            continue;
        }

        $template = isset($module['template']) ? trim((string) $module['template']) : '';
        $slug = isset($module['slug']) ? sanitize_key((string) $module['slug']) : '';
        $mode = isset($module['mode']) ? sanitize_key((string) $module['mode']) : 'local';

        if ($template === '' || $slug === '') {
            continue;
        }

        set_query_var('ellene_module_slug', $slug);
        set_query_var('ellene_module_mode', $mode);
        get_template_part($template);
    }

    set_query_var('ellene_module_slug', null);
    set_query_var('ellene_module_mode', null);

    get_template_part('template-parts/sections/sticky-bar');
}

/**
 * Backward-compatible bridge for existing filters expecting slug lists.
 *
 * @param array $modules Legacy module slug list.
 * @return array
 */
function ellene_filter_content_modules($modules) {
    $resolved = ellene_resolve_modules('home');
    $slugs = array();

    foreach ($resolved as $module) {
        if (is_array($module) && !empty($module['slug'])) {
            $slugs[] = sanitize_key((string) $module['slug']);
        }
    }

    return !empty($slugs) ? $slugs : $modules;
}
add_filter('ellene_content_modules', 'ellene_filter_content_modules', 20);
