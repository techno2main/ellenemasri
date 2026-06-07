<?php

/**
 * Layout slot - Content modules.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('ellene_render_content_modules')) {
    ellene_render_content_modules('home');
    return;
}

$modules = apply_filters(
    'ellene_content_modules',
    array(
        'stream',
        'social',
        'video',
        'release-info',
        'cta',
    )
);

if (!is_array($modules)) {
    $modules = array();
}

foreach ($modules as $module) {
    $module_slug = sanitize_key((string) $module);
    if ($module_slug === '') {
        continue;
    }

    $module_template = $module_slug === 'release-info' ? 'release' : $module_slug;

    get_template_part('template-parts/sections/' . $module_template . '/index');
}

get_template_part('template-parts/sections/footer/sticky-bar');
