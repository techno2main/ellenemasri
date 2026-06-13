<?php
/**
 * Contexte template live côté front (thin wrapper Phase 0).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug du template actif sur le site (front).
 */
function em_wp_front_get_live_template_slug(): string
{
    return em_wp_get_active_template_slug();
}
