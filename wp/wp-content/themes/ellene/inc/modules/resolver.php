<?php

/**
 * Ellene module resolver.
 *
 * @package Mayami
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalize a module slug list.
 *
 * @param mixed $value Raw value from options.
 * @return string[]
 */
function ellene_normalize_module_slug_list($value) {
    if (is_string($value)) {
        $value = explode(',', $value);
    }

    if (!is_array($value)) {
        return array();
    }

    $slugs = array();
    foreach ($value as $item) {
        $slug = sanitize_key((string) $item);
        if ($slug !== '') {
            $slugs[] = $slug;
        }
    }

    return array_values(array_unique($slugs));
}

/**
 * Resolve enabled modules from options with sane defaults.
 *
 * @param array<string, array<string, mixed>> $registry Registry.
 * @return string[]
 */
function ellene_get_enabled_modules($registry) {
    $raw_enabled = mayami_get_landing_option('modules_enabled', array());
    $enabled = ellene_normalize_module_slug_list($raw_enabled);

    if (empty($enabled)) {
        foreach ($registry as $slug => $config) {
            if (!empty($config['default_enabled'])) {
                $enabled[] = sanitize_key((string) $slug);
            }
        }
    }

    return array_values(array_unique($enabled));
}

/**
 * Resolve module display order from options.
 *
 * @return string[]
 */
function ellene_get_module_order() {
    $raw_order = mayami_get_landing_option('modules_order', array());
    return ellene_normalize_module_slug_list($raw_order);
}

/**
 * Resolve shared modules from options, constrained by registry support.
 *
 * @param array<string, array<string, mixed>> $registry Registry.
 * @return string[]
 */
function ellene_get_shared_modules($registry) {
    $raw_shared = mayami_get_landing_option('modules_shared', array());
    $shared = ellene_normalize_module_slug_list($raw_shared);
    $allowed_shared = array();

    foreach ($registry as $slug => $config) {
        if (!empty($config['supports_shared'])) {
            $allowed_shared[] = sanitize_key((string) $slug);
        }
    }

    return array_values(array_intersect($shared, $allowed_shared));
}

/**
 * Resolve ordered module definitions for a given context.
 *
 * @param string $context Context slug (home, release, etc.).
 * @return array<int, array<string, string>>
 */
function ellene_resolve_modules($context = 'home') {
    $registry = ellene_get_module_registry();
    $enabled = ellene_get_enabled_modules($registry);
    $order = ellene_get_module_order();
    $shared = ellene_get_shared_modules($registry);

    $ordered_slugs = array();

    foreach ($order as $ordered_slug) {
        if (in_array($ordered_slug, $enabled, true) && isset($registry[$ordered_slug])) {
            $ordered_slugs[] = $ordered_slug;
        }
    }

    foreach ($enabled as $enabled_slug) {
        if (isset($registry[$enabled_slug]) && !in_array($enabled_slug, $ordered_slugs, true)) {
            $ordered_slugs[] = $enabled_slug;
        }
    }

    $modules = array();
    foreach ($ordered_slugs as $slug) {
        $config = $registry[$slug];
        $modules[] = array(
            'slug' => $slug,
            'template' => (string) $config['template'],
            'mode' => in_array($slug, $shared, true) ? 'shared' : 'local',
            'context' => sanitize_key((string) $context),
        );
    }

    /**
     * Filter resolved modules before rendering.
     */
    return apply_filters('ellene_resolved_modules', $modules, $context);
}
