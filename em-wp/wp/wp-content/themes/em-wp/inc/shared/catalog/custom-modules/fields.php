<?php
/**
 * Champs des entrées — modules catalogue personnalisés.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_contacts_catalog_module_slug(): string
{
    return 'contacts';
}

/**
 * @deprecated Utiliser em_wp_contacts_catalog_module_slug().
 */
function em_wp_custom_catalog_contacts_module_slug(): string
{
    return em_wp_contacts_catalog_module_slug();
}

function em_wp_contacts_catalog_legacy_module_slug(): string
{
    return 'custom-contacts';
}

/**
 * @return array<string, array{label:string,layout:string}>
 */
function em_wp_contacts_catalog_entries(): array
{
    return em_wp_custom_catalog_entries(em_wp_contacts_catalog_module_slug());
}

function em_wp_contacts_catalog_edit_page_slug(string $entry_slug): string
{
    return em_wp_custom_catalog_edit_page_slug(em_wp_contacts_catalog_module_slug(), $entry_slug);
}

function em_wp_contacts_catalog_hub_menu_slug(): string
{
    return em_wp_custom_catalog_hub_menu_slug(em_wp_contacts_catalog_module_slug());
}

/**
 * Migration idempotente : custom-contacts → contacts (module catalogue natif).
 */
function em_wp_contacts_catalog_maybe_migrate_legacy_module_slug(): void
{
    $old = em_wp_contacts_catalog_legacy_module_slug();
    $new = em_wp_contacts_catalog_module_slug();

    if ($old === $new) {
        return;
    }

    $modules_option = em_wp_custom_catalog_modules_option_name();
    $modules = get_option($modules_option, []);

    if (is_array($modules) && isset($modules[$old]) && !isset($modules[$new])) {
        $modules[$new] = $modules[$old];
        unset($modules[$old]);
        $modules[$new]['hub_menu_slug'] = em_wp_custom_catalog_hub_menu_slug($new);
        update_option($modules_option, $modules, false);
    }

    $old_entries_key = em_wp_custom_catalog_entries_option_name($old);
    $new_entries_key = em_wp_custom_catalog_entries_option_name($new);
    $entries = get_option($old_entries_key, null);

    if (is_array($entries) && get_option($new_entries_key, null) === null) {
        update_option($new_entries_key, $entries, false);
        delete_option($old_entries_key);
    } else {
        $entries = get_option($new_entries_key, []);
    }

    if (is_array($entries)) {
        foreach (array_keys($entries) as $entry_slug) {
            $entry_slug = sanitize_key((string) $entry_slug);

            if ($entry_slug === '') {
                continue;
            }

            $old_option = em_wp_custom_catalog_entry_option_name($old, $entry_slug);
            $new_option = em_wp_custom_catalog_entry_option_name($new, $entry_slug);
            $saved = get_option($old_option, null);

            if ($saved !== null && get_option($new_option, null) === null) {
                update_option($new_option, is_array($saved) ? $saved : [], false);
                delete_option($old_option);
            }
        }
    }

    if (function_exists('em_wp_site_rubrique_order_option_name')) {
        $order = get_option(em_wp_site_rubrique_order_option_name(), []);

        if (is_array($order)) {
            $changed = false;

            foreach ($order as $index => $slug) {
                if (sanitize_key((string) $slug) === $old) {
                    $order[$index] = $new;
                    $changed = true;
                }
            }

            if ($changed) {
                update_option(em_wp_site_rubrique_order_option_name(), array_values($order), false);
            }
        }
    }

    if (function_exists('em_wp_template_plans_option_name') && function_exists('em_wp_template_plans_store')) {
        $store = em_wp_template_plans_store();
        $updated = false;

        foreach ($store as $template_slug => $plan) {
            if (!is_array($plan) || !isset($plan['order']) || !is_array($plan['order'])) {
                continue;
            }

            $changed = false;

            foreach ($plan['order'] as $index => $slug) {
                if (sanitize_key((string) $slug) === $old) {
                    $plan['order'][$index] = $new;
                    $changed = true;
                }
            }

            if ($changed) {
                $store[$template_slug] = $plan;
                $updated = true;
            }
        }

        if ($updated) {
            update_option(em_wp_template_plans_option_name(), $store, false);
        }
    }
}

function em_wp_custom_catalog_entry_option_name(string $module_slug, string $entry_slug): string
{
    return 'em_wp_custom_catalog_item_' . sanitize_key($module_slug) . '_' . sanitize_key($entry_slug);
}

function em_wp_custom_catalog_entry_group_name(string $module_slug, string $entry_slug): string
{
    return 'em_wp_custom_catalog_' . sanitize_key($module_slug) . '_' . sanitize_key($entry_slug) . '_group';
}

function em_wp_custom_catalog_field_hidden_key(string $field_key): string
{
    return sanitize_key($field_key) . '_hidden';
}

function em_wp_custom_catalog_field_label_key(string $field_key): string
{
    return sanitize_key($field_key) . '_label';
}

/**
 * Libellé par défaut d'un champ (schéma module).
 */
function em_wp_custom_catalog_field_default_label(string $module_slug, string $field_key): string
{
    $module_slug = sanitize_key($module_slug);
    $field_key = sanitize_key($field_key);
    $definitions = em_wp_custom_catalog_module_field_definitions($module_slug);

    return (string) ($definitions[$field_key]['label'] ?? $field_key);
}

/**
 * Libellé affiché (sauvegardé ou défaut schéma).
 *
 * @param array<string, mixed> $options
 */
function em_wp_custom_catalog_field_display_label(string $module_slug, string $field_key, array $options): string
{
    $label_key = em_wp_custom_catalog_field_label_key($field_key);
    $label = trim((string) ($options[$label_key] ?? ''));

    if ($label !== '') {
        return $label;
    }

    return em_wp_custom_catalog_field_default_label($module_slug, $field_key);
}

/**
 * Schéma de champs par module (extensible module par module).
 *
 * @return array<string, array{label:string,type:string}>
 */
function em_wp_custom_catalog_module_field_definitions(string $module_slug): array
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === em_wp_contacts_catalog_module_slug()) {
        return [
            'nom'       => [
                'label' => __('NOM', 'em-wp'),
                'type'  => 'text',
            ],
            'email'     => [
                'label' => __('Email', 'em-wp'),
                'type'  => 'email',
            ],
            'site'      => [
                'label' => __('Site', 'em-wp'),
                'type'  => 'url',
            ],
            'telephone' => [
                'label' => __('Tél.', 'em-wp'),
                'type'  => 'tel',
            ],
        ];
    }

    return [];
}

/**
 * @return array<string, string|bool>
 */
function em_wp_custom_catalog_entry_default_options(string $module_slug): array
{
    $defaults = [];

    foreach (em_wp_custom_catalog_module_field_definitions($module_slug) as $field_key => $definition) {
        $field_key = sanitize_key((string) $field_key);
        $defaults[$field_key] = '';
        $defaults[em_wp_custom_catalog_field_label_key($field_key)] = em_wp_custom_catalog_field_default_label($module_slug, $field_key);
        $defaults[em_wp_custom_catalog_field_hidden_key($field_key)] = false;
    }

    return $defaults;
}

/**
 * @return array<string, string|bool>
 */
function em_wp_custom_catalog_get_entry_options(string $module_slug, string $entry_slug): array
{
    $module_slug = sanitize_key($module_slug);
    $entry_slug = sanitize_key($entry_slug);

    if ($module_slug === '' || $entry_slug === '') {
        return [];
    }

    $saved = get_option(em_wp_custom_catalog_entry_option_name($module_slug, $entry_slug), []);

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, em_wp_custom_catalog_entry_default_options($module_slug));
}

function em_wp_custom_catalog_init_entry_options(string $module_slug, string $entry_slug): void
{
    $module_slug = sanitize_key($module_slug);
    $entry_slug = sanitize_key($entry_slug);

    if ($module_slug === '' || $entry_slug === '') {
        return;
    }

    $option_name = em_wp_custom_catalog_entry_option_name($module_slug, $entry_slug);

    if (get_option($option_name, null) !== null) {
        return;
    }

    update_option($option_name, em_wp_custom_catalog_entry_default_options($module_slug), false);
}

function em_wp_custom_catalog_delete_entry_options(string $module_slug, string $entry_slug): void
{
    $module_slug = sanitize_key($module_slug);
    $entry_slug = sanitize_key($entry_slug);

    if ($module_slug === '' || $entry_slug === '') {
        return;
    }

    delete_option(em_wp_custom_catalog_entry_option_name($module_slug, $entry_slug));
}

function em_wp_custom_catalog_migrate_entry_options(string $module_slug, string $old_slug, string $new_slug): void
{
    $module_slug = sanitize_key($module_slug);
    $old_slug = sanitize_key($old_slug);
    $new_slug = sanitize_key($new_slug);

    if ($module_slug === '' || $old_slug === '' || $new_slug === '' || $old_slug === $new_slug) {
        return;
    }

    $old_option = em_wp_custom_catalog_entry_option_name($module_slug, $old_slug);
    $new_option = em_wp_custom_catalog_entry_option_name($module_slug, $new_slug);
    $saved = get_option($old_option, null);

    if ($saved === null) {
        em_wp_custom_catalog_init_entry_options($module_slug, $new_slug);
        return;
    }

    update_option($new_option, is_array($saved) ? $saved : [], false);
    delete_option($old_option);
}

/**
 * @param mixed $input
 * @return array<string, string|bool>
 */
function em_wp_custom_catalog_sanitize_entry_options(string $module_slug, $input): array
{
    $module_slug = sanitize_key($module_slug);
    $definitions = em_wp_custom_catalog_module_field_definitions($module_slug);
    $defaults = em_wp_custom_catalog_entry_default_options($module_slug);
    $sanitized = $defaults;

    if (!is_array($input)) {
        return $sanitized;
    }

    foreach ($definitions as $field_key => $definition) {
        $field_key = sanitize_key((string) $field_key);
        $label_key = em_wp_custom_catalog_field_label_key($field_key);
        $hidden_key = em_wp_custom_catalog_field_hidden_key($field_key);
        $type = sanitize_key((string) ($definition['type'] ?? 'text'));
        $raw = isset($input[$field_key]) ? (string) $input[$field_key] : '';
        $default_label = em_wp_custom_catalog_field_default_label($module_slug, $field_key);
        $raw_label = isset($input[$label_key]) ? (string) $input[$label_key] : $default_label;
        $sanitized_label = sanitize_text_field($raw_label);

        $sanitized[$label_key] = $sanitized_label !== '' ? $sanitized_label : $default_label;

        if ($type === 'email') {
            $sanitized[$field_key] = sanitize_email($raw);
        } elseif ($type === 'url') {
            $sanitized[$field_key] = esc_url_raw($raw);
        } else {
            $sanitized[$field_key] = sanitize_text_field($raw);
        }

        $sanitized[$hidden_key] = !empty($input[$hidden_key]);
    }

    return $sanitized;
}
