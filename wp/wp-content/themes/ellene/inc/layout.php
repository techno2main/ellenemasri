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
        ellene_render_layout_slot((string) $slot);
    }
}
