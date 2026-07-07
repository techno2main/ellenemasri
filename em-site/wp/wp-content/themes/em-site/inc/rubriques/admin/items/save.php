<?php
/**
 * Sauvegarde des ITEMS EM-SITE (admin-post) — création & contenu.
 *
 * Création d'un footer (nom → structure de départ) et enregistrement du CONTENU
 * d'un footer (valeurs des champs de sa structure). Nonce + capability ;
 * écriture en namespace `em_site_*` uniquement.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rubriques verrouillées: création/duplication d'items désactivée.
 */
function em_site_is_fixed_single_item_type(string $type_slug): bool
{
    $type_slug = sanitize_key($type_slug);

    return in_array($type_slug, ['top-bar', 'footer'], true);
}

/**
 * Rubriques verrouillées au niveau type (non supprimables depuis overview).
 */
function em_site_is_locked_type_slug(string $type_slug): bool
{
    $type_slug = sanitize_key($type_slug);

    return in_array($type_slug, ['top-bar', 'footer', 'headers'], true);
}

/**
 * Crée un footer (item) avec la structure de départ du type.
 */
function em_site_handle_create_item(): void
{
    em_site_items_guard('em_site_create_item');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $label = sanitize_text_field(wp_unslash((string) ($_POST['item_label'] ?? '')));
    $slug = sanitize_key((string) ($_POST['item_slug'] ?? ''));

    if (em_site_is_fixed_single_item_type($type)) {
        em_site_builder_redirect(['error' => 'create', 'type' => $type]);
    }

    if ($slug === '' && $label !== '') {
        $slug = $label;
    }

    if ($slug !== '') {
        $slug = em_site_item_slug_base($type, $slug);
    }

    if (!em_site_rubrique_type_exists($type) || $slug === '') {
        em_site_builder_redirect(['error' => 'create', 'type' => $type]);
    }

    $label = function_exists('mb_strtoupper') ? mb_strtoupper($label, 'UTF-8') : strtoupper($label);
    em_site_register_item($type, $slug, $label);
    em_site_builder_redirect(['updated' => 'created', 'type' => $type, 'item' => $slug]);
}
add_action('admin_post_em_site_create_item', 'em_site_handle_create_item');

/**
 * Renomme un item à la volée (AJAX) : persiste immédiatement le nom saisi via le
 * crayon, sans attendre l'enregistrement du builder.
 */
function em_site_handle_ajax_rename_item(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('forbidden', 403);
    }

    check_ajax_referer('em_site_rename_item');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $item = sanitize_key((string) ($_POST['item'] ?? ''));
    $label = sanitize_text_field(wp_unslash((string) ($_POST['label'] ?? '')));
    $label = function_exists('mb_strtoupper') ? mb_strtoupper($label, 'UTF-8') : strtoupper($label);

    if (!em_site_rubrique_type_exists($type) || $item === '' || $label === '') {
        wp_send_json_error('invalid', 400);
    }

    $renamed = em_site_rename_item($type, $item, $label);

    if (($renamed['item'] ?? '') === '') {
        wp_send_json_error('rename_failed', 400);
    }

    wp_send_json_success([
        'label' => (string) ($renamed['label'] ?? $label),
        'item'  => (string) ($renamed['item'] ?? $item),
    ]);
}
add_action('wp_ajax_em_site_rename_item', 'em_site_handle_ajax_rename_item');

/**
 * Définit l'ancre (#section) d'un item à la volée (AJAX) depuis l'en-tête.
 *
 * L'ancre sert d'attribut id="" de la section au rendu (aperçu EM-SITE) et de cible
 * pour les liens #ancre (flèches de navigation). Persistée immédiatement, sans
 * attendre l'enregistrement du builder.
 */
function em_site_handle_ajax_set_anchor(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('forbidden', 403);
    }

    check_ajax_referer('em_site_set_anchor');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $item = sanitize_key((string) ($_POST['item'] ?? ''));
    $anchor = em_site_sanitize_anchor((string) wp_unslash($_POST['anchor'] ?? ''));

    if (!em_site_rubrique_type_exists($type) || $item === '') {
        wp_send_json_error('invalid', 400);
    }

    $data = em_site_get_item($type, $item);
    $data['anchor'] = $anchor;
    em_site_save_item($type, $item, $data);

    wp_send_json_success(['anchor' => $anchor]);
}
add_action('wp_ajax_em_site_set_anchor', 'em_site_handle_ajax_set_anchor');

/**
 * Duplique un footer (structure + contenu + lay-out) sous un nouveau nom.
 */
function em_site_handle_duplicate_item(): void
{
    em_site_items_guard('em_site_duplicate_item');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $source = sanitize_key((string) ($_POST['item'] ?? ''));
    $label = sanitize_text_field(wp_unslash((string) ($_POST['item_label'] ?? '')));
    if (em_site_is_fixed_single_item_type($type)) {
        em_site_builder_redirect(['error' => 'duplicate', 'type' => $type]);
    }

    $items = em_site_get_items($type);

    if (!em_site_rubrique_type_exists($type) || !isset($items[$source])) {
        em_site_builder_redirect(['error' => 'duplicate', 'type' => $type]);
    }

    if ($label === '') {
        $label = (string) $items[$source] . ' COPIE';
    }

    $label = function_exists('mb_strtoupper') ? mb_strtoupper($label, 'UTF-8') : strtoupper($label);
    $slug = em_site_unique_item_slug($type, em_site_item_slug_base($type, $label));

    $data = em_site_get_item($type, $source);
    $data['label'] = $label;

    $items[$slug] = $label;
    em_site_save_items($type, $items);
    em_site_save_item($type, $slug, $data);

    em_site_builder_redirect(['updated' => 'duplicated', 'type' => $type, 'item' => $slug]);
}
add_action('admin_post_em_site_duplicate_item', 'em_site_handle_duplicate_item');

/**
 * Crée une nouvelle RUBRIQUE (type) personnalisée, stockée en option.
 *
 * La rubrique démarre avec les champs d'apparence mutualisés (section
 * « Apparence ») et aucun contenu. Elle apparaît ensuite dans la liste et le
 * sous-menu comme les types intégrés.
 */
function em_site_handle_create_type(): void
{
    em_site_items_guard('em_site_create_type');

    $label = sanitize_text_field(wp_unslash((string) ($_POST['type_label'] ?? '')));
    $icon = sanitize_html_class((string) ($_POST['type_icon'] ?? ''));
    $slug = sanitize_key(sanitize_title($label));

    if ($label === '' || $slug === '') {
        em_site_builder_redirect(['error' => 'type_create']);
    }

    // Réactivation d'une rubrique intégrée masquée (suppression safe) :
    // + Nouvelle Rubrique avec le même nom la remet visible, sans créer un type custom.
    $hidden_option = function_exists('em_site_hidden_rubriques_option_name')
        ? em_site_hidden_rubriques_option_name()
        : 'em_site_hidden_rubriques';
    $hidden = get_option($hidden_option, []);
    $hidden = is_array($hidden) ? array_values(array_map('sanitize_key', $hidden)) : [];

    if (in_array($slug, $hidden, true)) {
            $hidden = array_values(array_filter($hidden, static fn(string $s): bool => $s !== $slug));
            update_option($hidden_option, $hidden, false);

            if (function_exists('em_site_get_rubrique_order') && function_exists('em_site_rubrique_order_option_name')) {
                $admin_order = em_site_get_rubrique_order();
                $admin_order = array_values(array_filter($admin_order, static fn(string $s): bool => $s !== $slug));
                $footer_index = array_search('footer', $admin_order, true);

                if ($footer_index === false) {
                    $admin_order[] = $slug;
                } else {
                    array_splice($admin_order, (int) $footer_index, 0, [$slug]);
                }

                update_option(em_site_rubrique_order_option_name(), $admin_order);
            }

            if (function_exists('em_site_get_site_rubrique_order') && function_exists('em_site_save_site_rubrique_order')) {
                $site_order = em_site_get_site_rubrique_order();
                $site_order = array_values(array_filter($site_order, static fn(string $s): bool => $s !== $slug));
                $footer_index = array_search('footer', $site_order, true);

                if ($footer_index === false) {
                    $site_order[] = $slug;
                } else {
                    array_splice($site_order, (int) $footer_index, 0, [$slug]);
                }

                em_site_save_site_rubrique_order($site_order);
            }

            em_site_builder_redirect(['updated' => 'type_created', 'type' => $slug]);
    }

    if (em_site_rubrique_type_exists($slug)) {
        em_site_builder_redirect(['error' => 'type_exists']);
    }

    $label_uc = function_exists('mb_strtoupper') ? mb_strtoupper($label, 'UTF-8') : strtoupper($label);
    $types = get_option(em_site_rubrique_types_option_name(), []);
    $types = is_array($types) ? $types : [];
    $types[$slug] = [
        'label'        => $label_uc,
        'label_plural' => $label_uc,
        'icon'         => $icon !== '' ? $icon : 'dashicons-screenoptions',
        'starter'      => em_site_rubrique_default_appearance_fields(),
        'layout'       => [],
    ];
    update_option(em_site_rubrique_types_option_name(), $types);

    if (function_exists('em_site_get_rubrique_order') && function_exists('em_site_rubrique_order_option_name')) {
        $admin_order = em_site_get_rubrique_order();
        $admin_order = array_values(array_filter($admin_order, static fn(string $s): bool => $s !== $slug));
        $footer_index = array_search('footer', $admin_order, true);

        if ($footer_index === false) {
            $admin_order[] = $slug;
        } else {
            array_splice($admin_order, (int) $footer_index, 0, [$slug]);
        }

        update_option(em_site_rubrique_order_option_name(), $admin_order);
    }

    if (function_exists('em_site_get_site_rubrique_order') && function_exists('em_site_save_site_rubrique_order')) {
        $site_order = em_site_get_site_rubrique_order();
        $site_order = array_values(array_filter($site_order, static fn(string $s): bool => $s !== $slug));
        $footer_index = array_search('footer', $site_order, true);

        if ($footer_index === false) {
            $site_order[] = $slug;
        } else {
            array_splice($site_order, (int) $footer_index, 0, [$slug]);
        }

        em_site_save_site_rubrique_order($site_order);
    }

    em_site_builder_redirect(['updated' => 'type_created', 'type' => $slug]);
}
add_action('admin_post_em_site_create_type', 'em_site_handle_create_type');

/**
 * Supprime une RUBRIQUE personnalisée (type) et ses données associées.
 */
function em_site_handle_delete_type(): void
{
    em_site_items_guard('em_site_delete_type');

    $slug = sanitize_key((string) ($_POST['type'] ?? ''));

    if ($slug === '' || em_site_is_locked_type_slug($slug)) {
        em_site_builder_redirect(['error' => 'type_delete']);
    }

    $types_option = em_site_rubrique_types_option_name();
    $types = get_option($types_option, []);
    $types = is_array($types) ? $types : [];
    $is_custom_type = isset($types[$slug]);

    if (!$is_custom_type && !em_site_rubrique_type_exists($slug)) {
        em_site_builder_redirect(['error' => 'type_delete']);
    }

    if ($is_custom_type) {
        unset($types[$slug]);
        update_option($types_option, $types);
    } else {
        // Rubrique intégrée: suppression safe = masquage dans l'overview.
        $hidden_option = function_exists('em_site_hidden_rubriques_option_name')
            ? em_site_hidden_rubriques_option_name()
            : 'em_site_hidden_rubriques';
        $hidden = get_option($hidden_option, []);
        $hidden = is_array($hidden) ? array_values(array_map('sanitize_key', $hidden)) : [];

        if (!in_array($slug, $hidden, true)) {
            $hidden[] = $slug;
        }

        update_option($hidden_option, array_values(array_unique($hidden)), false);
    }

    if ($is_custom_type && function_exists('em_site_rubrique_labels_option_name')) {
        $labels_option = em_site_rubrique_labels_option_name();
        $labels = get_option($labels_option, []);
        if (is_array($labels) && isset($labels[$slug])) {
            unset($labels[$slug]);
            update_option($labels_option, $labels);
        }
    }

    if (function_exists('em_site_get_rubrique_order') && function_exists('em_site_rubrique_order_option_name')) {
        $admin_order = array_values(array_filter(
            em_site_get_rubrique_order(),
            static fn(string $s): bool => $s !== $slug
        ));
        update_option(em_site_rubrique_order_option_name(), $admin_order);
    }

    if (function_exists('em_site_get_site_rubrique_order') && function_exists('em_site_save_site_rubrique_order')) {
        $site_order = array_values(array_filter(
            em_site_get_site_rubrique_order(),
            static fn(string $s): bool => $s !== $slug
        ));
        em_site_save_site_rubrique_order($site_order);
    }

    if (function_exists('em_site_site_rubrique_visibility_option_name')) {
        $site_visibility_option = em_site_site_rubrique_visibility_option_name();
        $site_visibility = get_option($site_visibility_option, []);
        if (is_array($site_visibility) && isset($site_visibility[$slug])) {
            unset($site_visibility[$slug]);
            update_option($site_visibility_option, $site_visibility, false);
        }
    }

    if (function_exists('em_site_template_visibility_option_name')) {
        $template_visibility_option = em_site_template_visibility_option_name();
        $template_visibility = get_option($template_visibility_option, []);
        if (is_array($template_visibility)) {
            foreach ($template_visibility as $template_slug => $template_map) {
                if (!is_array($template_map) || !isset($template_map[$slug])) {
                    continue;
                }

                unset($template_map[$slug]);
                $template_visibility[$template_slug] = $template_map;
            }

            update_option($template_visibility_option, $template_visibility, false);
        }
    }

    if ($is_custom_type) {
        $items = function_exists('em_site_get_items') ? em_site_get_items($slug) : [];
        if (is_array($items)) {
            foreach (array_keys($items) as $item_slug) {
                if (!is_string($item_slug) || $item_slug === '') {
                    continue;
                }

                if (function_exists('em_site_item_option_name')) {
                    delete_option(em_site_item_option_name($slug, $item_slug));
                }
            }
        }

        if (function_exists('em_site_items_option_name')) {
            delete_option(em_site_items_option_name($slug));
        }
    }

    if (function_exists('em_site_template_registry')) {
        $templates = em_site_template_registry();

        foreach ($templates as $template_slug => $template_definition) {
            unset($template_definition);
            $template_slug = sanitize_key((string) $template_slug);

            if ($template_slug === '') {
                continue;
            }

            if (function_exists('em_site_template_option_name')) {
                delete_option(em_site_template_option_name($slug, $template_slug));
            }

            if (function_exists('em_site_instance_option_name')) {
                delete_option(em_site_instance_option_name($template_slug, $slug));
            }

            if (function_exists('em_site_template_skeleton_remove_rubrique')) {
                em_site_template_skeleton_remove_rubrique($template_slug, $slug);
            }
        }
    }

    em_site_builder_redirect(['updated' => 'type_deleted']);
}
add_action('admin_post_em_site_delete_type', 'em_site_handle_delete_type');

/**
 * Supprime un footer (item).
 */
function em_site_handle_delete_item(): void
{
    em_site_items_guard('em_site_delete_item');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $item = sanitize_key((string) ($_POST['item'] ?? ''));

    if (!em_site_rubrique_type_exists($type) || $item === '') {
        em_site_builder_redirect(['error' => 'delete', 'type' => $type]);
    }

    em_site_delete_item($type, $item);
    em_site_builder_redirect(['updated' => 'deleted', 'type' => $type]);
}
add_action('admin_post_em_site_delete_item', 'em_site_handle_delete_item');
