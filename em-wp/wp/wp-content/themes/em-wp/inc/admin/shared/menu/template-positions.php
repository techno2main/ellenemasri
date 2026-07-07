<?php
/**
 * Positions menu Templates.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Position menu Templates (parent TEMPLATES, après Catalogues).
 */
function em_wp_admin_menu_templates_position(): float
{
    if (function_exists('em_wp_admin_template_parent_page_slug')) {
        return em_wp_admin_menu_position_for_slug(em_wp_admin_template_parent_page_slug());
    }

    return (float) (em_wp_admin_menu_main_nav_base() + 8);
}

/**
 * Position menu d'un template enregistré (MAYAMI, ELLENE, …).
 */
function em_wp_admin_menu_position_for_template(string $template_slug): int
{
    if (!function_exists('em_wp_admin_template_entry_page_slug')) {
        return (int) em_wp_admin_menu_templates_position() + 1;
    }

    return (int) em_wp_admin_menu_position_for_slug(em_wp_admin_template_entry_page_slug($template_slug));
}

/**
 * Filet sous le bloc Template.
 */
function em_wp_admin_menu_templates_separator_bottom_position(): int
{
    return (int) em_wp_admin_menu_position_for_slug('separator-em-wp-after-templates');
}
