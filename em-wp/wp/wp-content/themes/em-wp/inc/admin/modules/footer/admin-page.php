<?php
/**
 * Page admin Footer (rubrique template — sélection catalogue).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_footer_render_admin_page(): void
{
    if (!function_exists('em_wp_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_wp_footer_get_options();
    $choices = function_exists('em_wp_footer_catalog_choices') ? em_wp_footer_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['footer_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_wp_footer_normalize_catalog_slug')) {
        $selected = em_wp_footer_normalize_catalog_slug($selected);
        $options['footer_slug'] = $selected;
    }

    em_wp_admin_render_catalog_rubrique_page([
        'module_slug'       => 'footer',
        'page_slug'         => em_wp_footer_page_slug(),
        'save_nonce_action' => 'em_wp_footer_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'footer_slug',
        'field'             => em_wp_footer_form_option_key(),
        'form_id'           => 'em-wp-footer-form',
    ]);
}
