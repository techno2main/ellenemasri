<?php
/**
 * Page admin Stream (rubrique template — sélection catalogue).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_stream_render_admin_page(): void
{
    if (!function_exists('em_site_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_site_stream_get_options();
    $choices = function_exists('em_site_stream_catalog_choices') ? em_site_stream_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['stream_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_site_stream_normalize_catalog_slug')) {
        $selected = em_site_stream_normalize_catalog_slug($selected);
        $options['stream_slug'] = $selected;
    }

    em_site_admin_render_catalog_rubrique_page([
        'module_slug'       => 'stream',
        'page_slug'         => em_site_stream_page_slug(),
        'save_nonce_action' => 'em_site_stream_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'stream_slug',
        'field'             => em_site_stream_form_option_key(),
        'form_id'           => 'em-site-stream-form',
        'wrap_class'        => 'em-site-stream-admin em-site-header-admin em-site-admin-module em-site-hub-sommaire',
        'panel_class'       => 'em-site-stream-panel',
        'panels_wrap_class' => 'em-site-stream-admin__panels em-site-admin-module__panels',
    ]);
}
