<?php
/**
 * Page admin Top Bar (rubrique template — sélection catalogue).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_top_bar_render_admin_page(): void
{
    if (!function_exists('em_site_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_site_top_bar_get_options();
    $choices = function_exists('em_site_top_bar_catalog_choices') ? em_site_top_bar_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['top_bar_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_site_top_bar_normalize_catalog_slug')) {
        $selected = em_site_top_bar_normalize_catalog_slug($selected);
        $options['top_bar_slug'] = $selected;
    }

    em_site_admin_render_catalog_rubrique_page([
        'module_slug'       => 'top-bar',
        'page_slug'         => em_site_top_bar_page_slug(),
        'save_nonce_action' => 'em_site_top_bar_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'top_bar_slug',
        'field'             => em_site_top_bar_form_option_key(),
        'form_id'           => 'em-site-top-bar-form',
        'wrap_class'        => 'em-site-top-bar-admin em-site-header-admin em-site-admin-module em-site-hub-sommaire',
        'panel_class'       => 'em-site-top-bar-panel',
        'panels_wrap_class' => 'em-site-top-bar-admin__panels em-site-admin-module__panels',
    ]);
}
