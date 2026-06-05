<?php

/**
 * Shared layout renderer for Ellene theme.
 *
 * @package Mayami
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render a layout slot from template-parts/layout.
 *
 * @param string $slot Layout slot slug.
 * @return void
 */
function ellene_render_layout_slot($slot) {
    if (!is_string($slot) || $slot === '') {
        return;
    }

    $normalized_slot = sanitize_key($slot);
    if ($normalized_slot === '') {
        return;
    }

    get_template_part('template-parts/layout/' . $normalized_slot);
}

/**
 * Check if a layout slot is enabled by module toggles.
 *
 * @param string $slot Layout slot slug.
 * @return bool
 */
function ellene_is_layout_slot_enabled($slot) {
    $slot = sanitize_key((string) $slot);
    if ($slot === '' || $slot === 'content') {
        return true;
    }

    if (!function_exists('ellene_get_enabled_modules') || !function_exists('ellene_get_module_registry')) {
        return true;
    }

    $enabled = ellene_get_enabled_modules(ellene_get_module_registry());
    return in_array($slot, $enabled, true);
}

/**
 * Render the default Ellene layout stack.
 *
 * @return void
 */
function ellene_render_layout() {
    $slots = apply_filters(
        'ellene_layout_slots',
        array('top-bar', 'header', 'hero', 'content', 'footer')
    );

    if (!is_array($slots)) {
        return;
    }

    foreach ($slots as $slot) {
        $slot_slug = (string) $slot;
        if (!ellene_is_layout_slot_enabled($slot_slug)) {
            continue;
        }

        ellene_render_layout_slot($slot_slug);
    }
}
