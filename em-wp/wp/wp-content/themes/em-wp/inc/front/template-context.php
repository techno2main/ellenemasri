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

/**
 * Ordre des rubriques milieu sur le site public (squelette du template actif).
 *
 * @return string[]
 */
function em_wp_front_get_rubrique_middle_order(): array
{
    if (function_exists('em_wp_get_rubrique_middle_order_for_template') && function_exists('em_wp_front_get_live_template_slug')) {
        $template_slug = em_wp_front_get_live_template_slug();

        if ($template_slug !== '') {
            return em_wp_get_rubrique_middle_order_for_template($template_slug);
        }
    }

    return function_exists('em_wp_get_site_rubrique_middle_order')
        ? em_wp_get_site_rubrique_middle_order()
        : [];
}
