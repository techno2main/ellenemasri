<?php
/**
 * Rendu front — rubrique CONTACT (catalogue personnalisé).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_contacts_enqueue_front_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');
    $theme_uri = get_template_directory_uri();
    $css_path = 'assets/front/css/modules/contacts/contact.css';

    wp_enqueue_style(
        'em-wp-landing-ui',
        $theme_uri . '/assets/front/css/landing-ui.css',
        ['em-wp-theme'],
        file_exists(get_template_directory() . '/assets/front/css/landing-ui.css')
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/assets/front/css/landing-ui.css')
            : $theme_version
    );

    wp_enqueue_style(
        'em-wp-contacts',
        $theme_uri . '/' . $css_path,
        ['em-wp-landing-ui'],
        file_exists(get_template_directory() . '/' . $css_path)
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/' . $css_path)
            : $theme_version
    );
}

/**
 * Rendu d'une rubrique liée à un catalogue personnalisé (CONTACTS, …).
 */
function em_wp_render_custom_catalog_rubrique(string $module_slug): void
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '' || !function_exists('em_wp_custom_catalog_is_module') || !em_wp_custom_catalog_is_module($module_slug)) {
        return;
    }

    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility($module_slug)) {
        return;
    }

    $options = function_exists('em_wp_custom_catalog_rubrique_get_options_for_front')
        ? em_wp_custom_catalog_rubrique_get_options_for_front($module_slug)
        : [];

    if (empty($options['enabled'])) {
        return;
    }

    em_wp_contacts_enqueue_front_assets();

    get_template_part('template-parts/sections/contacts/contact', null, [
        'contact'      => $options,
        'module_slug'  => $module_slug,
    ]);
}

function em_wp_render_contacts(): void
{
    em_wp_render_custom_catalog_rubrique('contacts');
}
