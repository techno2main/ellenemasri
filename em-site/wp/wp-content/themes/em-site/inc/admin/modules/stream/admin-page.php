<?php
/**
 * Page admin Stream (rubrique template — sélection catalogue).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_stream_render_admin_page(): void
{
    if (!function_exists('em_wp_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_wp_stream_get_options();
    $choices = function_exists('em_wp_stream_catalog_choices') ? em_wp_stream_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['stream_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_wp_stream_normalize_catalog_slug')) {
        $selected = em_wp_stream_normalize_catalog_slug($selected);
        $options['stream_slug'] = $selected;
    }

    em_wp_admin_render_catalog_rubrique_page([
        'module_slug'       => 'stream',
        'page_slug'         => em_wp_stream_page_slug(),
        'save_nonce_action' => 'em_wp_stream_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'stream_slug',
        'field'             => em_wp_stream_form_option_key(),
        'form_id'           => 'em-wp-stream-form',
        'wrap_class'        => 'em-wp-stream-admin em-wp-header-admin em-wp-admin-module em-wp-hub-sommaire',
        'panel_class'       => 'em-wp-stream-panel',
        'panels_wrap_class' => 'em-wp-stream-admin__panels em-wp-admin-module__panels',
    ]);
}
