<?php
/**
 * Page admin Social (rubrique template — sélection catalogue).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_social_render_admin_page(): void
{
    if (!function_exists('em_wp_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_wp_social_get_options();
    $choices = function_exists('em_wp_social_catalog_choices') ? em_wp_social_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['social_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_wp_social_normalize_catalog_slug')) {
        $selected = em_wp_social_normalize_catalog_slug($selected);
        $options['social_slug'] = $selected;
    }

    em_wp_admin_render_catalog_rubrique_page([
        'module_slug'       => 'social',
        'page_slug'         => em_wp_social_page_slug(),
        'save_nonce_action' => 'em_wp_social_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'social_slug',
        'field'             => em_wp_social_form_option_key(),
        'form_id'           => 'em-wp-social-form',
        'wrap_class'        => 'em-wp-social-admin em-wp-header-admin em-wp-admin-module em-wp-hub-sommaire',
        'panel_class'       => 'em-wp-social-panel',
    ]);
}
