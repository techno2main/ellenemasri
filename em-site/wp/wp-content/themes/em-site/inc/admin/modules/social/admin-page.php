<?php
/**
 * Page admin Social (rubrique template — sélection catalogue).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_social_render_admin_page(): void
{
    if (!function_exists('em_site_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_site_social_get_options();
    $choices = function_exists('em_site_social_catalog_choices') ? em_site_social_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['social_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_site_social_normalize_catalog_slug')) {
        $selected = em_site_social_normalize_catalog_slug($selected);
        $options['social_slug'] = $selected;
    }

    em_site_admin_render_catalog_rubrique_page([
        'module_slug'       => 'social',
        'page_slug'         => em_site_social_page_slug(),
        'save_nonce_action' => 'em_site_social_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'social_slug',
        'field'             => em_site_social_form_option_key(),
        'form_id'           => 'em-site-social-form',
        'wrap_class'        => 'em-site-social-admin em-site-header-admin em-site-admin-module em-site-hub-sommaire',
        'panel_class'       => 'em-site-social-panel',
    ]);
}
