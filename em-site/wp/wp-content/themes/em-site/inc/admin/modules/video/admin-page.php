<?php
/**
 * Page admin Video (rubrique template — sélection catalogue).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_video_render_admin_page(): void
{
    if (!function_exists('em_site_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_site_video_get_options();
    $choices = function_exists('em_site_video_catalog_choices') ? em_site_video_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['video_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_site_video_normalize_catalog_slug')) {
        $selected = em_site_video_normalize_catalog_slug($selected);
        $options['video_slug'] = $selected;
    }

    em_site_admin_render_catalog_rubrique_page([
        'module_slug'       => 'video',
        'page_slug'         => em_site_video_page_slug(),
        'save_nonce_action' => 'em_site_video_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'video_slug',
        'field'             => em_site_video_form_option_key(),
        'form_id'           => 'em-site-video-form',
        'wrap_class'        => 'em-site-video-admin em-site-header-admin em-site-admin-module em-site-hub-sommaire',
        'panel_class'       => 'em-site-video-panel',
        'panels_wrap_class' => 'em-site-admin-module__panels',
    ]);
}
