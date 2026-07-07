<?php
/**
 * Page admin CTA (rubrique template — sélection catalogue).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_cta_render_admin_page(): void
{
    if (!function_exists('em_site_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_site_cta_get_options();
    $choices = function_exists('em_site_cta_catalog_choices') ? em_site_cta_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['cta_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_site_cta_normalize_catalog_slug')) {
        $selected = em_site_cta_normalize_catalog_slug($selected);
        $options['cta_slug'] = $selected;
    }

    em_site_admin_render_catalog_rubrique_page([
        'module_slug'       => 'cta',
        'page_slug'         => em_site_cta_page_slug(),
        'save_nonce_action' => 'em_site_cta_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'cta_slug',
        'field'             => em_site_cta_form_option_key(),
        'form_id'           => 'em-site-cta-form',
    ]);
}
