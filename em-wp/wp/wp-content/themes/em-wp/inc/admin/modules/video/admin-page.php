<?php
/**
 * Page admin Video (rubrique template — sélection catalogue).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_video_render_admin_page(): void
{
    if (!function_exists('em_wp_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_wp_video_get_options();
    $choices = function_exists('em_wp_video_catalog_choices') ? em_wp_video_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['video_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_wp_video_normalize_catalog_slug')) {
        $selected = em_wp_video_normalize_catalog_slug($selected);
        $options['video_slug'] = $selected;
    }

    em_wp_admin_render_catalog_rubrique_page([
        'module_slug'       => 'video',
        'page_slug'         => em_wp_video_page_slug(),
        'save_nonce_action' => 'em_wp_video_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'video_slug',
        'field'             => em_wp_video_form_option_key(),
        'form_id'           => 'em-wp-video-form',
        'wrap_class'        => 'em-wp-video-admin em-wp-header-admin em-wp-admin-module em-wp-hub-sommaire',
        'panel_class'       => 'em-wp-video-panel',
        'panels_wrap_class' => 'em-wp-admin-module__panels',
    ]);
}
