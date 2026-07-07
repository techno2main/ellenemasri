<?php
/**
 * Page admin CTA (rubrique template — sélection catalogue).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_render_admin_page(): void
{
    if (!function_exists('em_wp_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_wp_cta_get_options();
    $choices = function_exists('em_wp_cta_catalog_choices') ? em_wp_cta_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['cta_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_wp_cta_normalize_catalog_slug')) {
        $selected = em_wp_cta_normalize_catalog_slug($selected);
        $options['cta_slug'] = $selected;
    }

    em_wp_admin_render_catalog_rubrique_page([
        'module_slug'       => 'cta',
        'page_slug'         => em_wp_cta_page_slug(),
        'save_nonce_action' => 'em_wp_cta_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'cta_slug',
        'field'             => em_wp_cta_form_option_key(),
        'form_id'           => 'em-wp-cta-form',
    ]);
}
