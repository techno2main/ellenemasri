<?php
/**
 * Données initiales — catalogue Contacts (Mayami + Ellene).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Crée le catalogue CONTACTS et ses deux entrées si absents.
 */
function em_wp_custom_catalog_maybe_bootstrap_contacts(): void
{
    if (!function_exists('em_wp_contacts_catalog_module_slug')) {
        return;
    }

    if (function_exists('em_wp_contacts_catalog_maybe_migrate_legacy_module_slug')) {
        em_wp_contacts_catalog_maybe_migrate_legacy_module_slug();
    }

    $module_slug = em_wp_contacts_catalog_module_slug();
    $modules_option = em_wp_custom_catalog_modules_option_name();
    $modules = get_option($modules_option, []);

    if (!is_array($modules)) {
        $modules = [];
    }

    if (!isset($modules[$module_slug])) {
        $modules[$module_slug] = [
            'label'                => 'Contacts',
            'menu_position_after'  => '__end__',
            'hub_menu_slug'        => em_wp_custom_catalog_hub_menu_slug($module_slug),
            'icon'                 => 'dashicons-email-alt',
            'description_item'     => 'Contacts',
            'description_rubrique' => 'CONTACT',
        ];

        update_option($modules_option, $modules, false);
    } else {
        $module_changed = false;

        if (trim((string) ($modules[$module_slug]['description_rubrique'] ?? '')) === '') {
            $modules[$module_slug]['description_rubrique'] = 'CONTACT';
            $module_changed = true;
        }

        if (trim((string) ($modules[$module_slug]['description_item'] ?? '')) === '') {
            $modules[$module_slug]['description_item'] = 'Contacts';
            $module_changed = true;
        }

        if ($module_changed) {
            update_option($modules_option, $modules, false);
        }
    }

    $entries_option = em_wp_custom_catalog_entries_option_name($module_slug);
    $entries = get_option($entries_option, []);

    if (!is_array($entries)) {
        $entries = [];
    }

    $seed_labels = [
        'Contact Mayami',
        'Contact Ellene',
    ];

    $entries_changed = false;

    foreach ($seed_labels as $label) {
        $existing_slug = '';

        foreach ($entries as $slug => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            if (strcasecmp(trim((string) ($entry['label'] ?? '')), $label) === 0) {
                $existing_slug = sanitize_key((string) $slug);
                break;
            }
        }

        if ($existing_slug !== '') {
            em_wp_custom_catalog_init_entry_options($module_slug, $existing_slug);
            continue;
        }

        $slug = em_wp_custom_catalog_unique_entry_slug(
            $module_slug,
            em_wp_custom_catalog_entry_slug_from_label($module_slug, $label)
        );

        $entries[$slug] = [
            'label'  => $label,
            'layout' => 'default',
        ];
        $entries_changed = true;

        em_wp_custom_catalog_init_entry_options($module_slug, $slug);
    }

    if ($entries_changed) {
        update_option($entries_option, $entries, false);
    }

    if (function_exists('em_wp_custom_catalog_maybe_normalize_all_entry_slugs')) {
        em_wp_custom_catalog_maybe_normalize_all_entry_slugs();
    }
}

add_action('init', 'em_wp_custom_catalog_maybe_bootstrap_contacts', 6);
