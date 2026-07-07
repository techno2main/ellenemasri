<?php
/**
 * Page admin Release (rubrique template — sélection catalogue).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_render_admin_page(): void
{
    if (!function_exists('em_wp_admin_render_catalog_rubrique_page')) {
        return;
    }

    $options = em_wp_release_get_options();
    $choices = function_exists('em_wp_release_catalog_choices') ? em_wp_release_catalog_choices() : [];
    $selected = sanitize_key((string) ($options['release_slug'] ?? ''));

    if ($selected !== '' && function_exists('em_wp_release_normalize_catalog_slug')) {
        $selected = em_wp_release_normalize_catalog_slug($selected);
        $options['release_slug'] = $selected;
    }

    em_wp_admin_render_catalog_rubrique_page([
        'module_slug'       => 'release',
        'page_slug'         => em_wp_release_page_slug(),
        'save_nonce_action' => 'em_wp_release_save',
        'options'           => $options,
        'choices'           => $choices,
        'pointer_key'       => 'release_slug',
        'field'             => em_wp_release_form_option_key(),
        'form_id'           => 'em-wp-release-form',
    ]);
}
