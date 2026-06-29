<?php
/**
 * Sauvegarde des ITEMS V4 (admin-post) — création & contenu.
 *
 * Création d'un footer (nom → structure de départ) et enregistrement du CONTENU
 * d'un footer (valeurs des champs de sa structure). Nonce + capability ;
 * écriture en namespace `em_wp_v4_*` uniquement.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Crée un footer (item) avec la structure de départ du type.
 */
function em_wp_v4_handle_create_item(): void
{
    em_wp_v4_items_guard('em_wp_v4_create_item');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $label = sanitize_text_field(wp_unslash((string) ($_POST['item_label'] ?? '')));
    $slug = sanitize_key((string) ($_POST['item_slug'] ?? ''));

    if ($slug === '' && $label !== '') {
        $slug = sanitize_title($label);
    }

    if (!em_wp_rubrique_type_exists($type) || $slug === '') {
        em_wp_v4_builder_redirect(['v4_error' => 'create', 'type' => $type]);
    }

    $label = function_exists('mb_strtoupper') ? mb_strtoupper($label, 'UTF-8') : strtoupper($label);
    em_wp_v4_register_item($type, $slug, $label);
    em_wp_v4_builder_redirect(['v4_updated' => 'created', 'type' => $type, 'item' => $slug]);
}
add_action('admin_post_em_wp_v4_create_item', 'em_wp_v4_handle_create_item');

/**
 * Renomme un item à la volée (AJAX) : persiste immédiatement le nom saisi via le
 * crayon, sans attendre l'enregistrement du builder.
 */
function em_wp_v4_handle_ajax_rename_item(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('forbidden', 403);
    }

    check_ajax_referer('em_wp_v4_rename_item');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $item = sanitize_key((string) ($_POST['item'] ?? ''));
    $label = sanitize_text_field(wp_unslash((string) ($_POST['label'] ?? '')));
    $label = function_exists('mb_strtoupper') ? mb_strtoupper($label, 'UTF-8') : strtoupper($label);

    if (!em_wp_rubrique_type_exists($type) || $item === '' || $label === '') {
        wp_send_json_error('invalid', 400);
    }

    em_wp_v4_rename_item($type, $item, $label);
    wp_send_json_success(['label' => $label]);
}
add_action('wp_ajax_em_wp_v4_rename_item', 'em_wp_v4_handle_ajax_rename_item');

/**
 * Définit l'ancre (#section) d'un item à la volée (AJAX) depuis l'en-tête.
 *
 * L'ancre sert d'attribut id="" de la section au rendu (aperçu V4) et de cible
 * pour les liens #ancre (flèches de navigation). Persistée immédiatement, sans
 * attendre l'enregistrement du builder.
 */
function em_wp_v4_handle_ajax_set_anchor(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('forbidden', 403);
    }

    check_ajax_referer('em_wp_v4_set_anchor');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $item = sanitize_key((string) ($_POST['item'] ?? ''));
    $anchor = em_wp_v4_sanitize_anchor((string) wp_unslash($_POST['anchor'] ?? ''));

    if (!em_wp_rubrique_type_exists($type) || $item === '') {
        wp_send_json_error('invalid', 400);
    }

    $data = em_wp_v4_get_item($type, $item);
    $data['anchor'] = $anchor;
    em_wp_v4_save_item($type, $item, $data);

    wp_send_json_success(['anchor' => $anchor]);
}
add_action('wp_ajax_em_wp_v4_set_anchor', 'em_wp_v4_handle_ajax_set_anchor');

/**
 * Slug d'item unique pour un type (suffixe -2, -3… si déjà pris).
 */
function em_wp_v4_unique_item_slug(string $type, string $base): string
{
    $base = $base !== '' ? $base : 'item';
    $items = em_wp_v4_get_items($type);
    $slug = $base;
    $i = 2;

    while (isset($items[$slug])) {
        $slug = $base . '-' . $i;
        $i++;
    }

    return $slug;
}

/**
 * Duplique un footer (structure + contenu + lay-out) sous un nouveau nom.
 */
function em_wp_v4_handle_duplicate_item(): void
{
    em_wp_v4_items_guard('em_wp_v4_duplicate_item');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $source = sanitize_key((string) ($_POST['item'] ?? ''));
    $label = sanitize_text_field(wp_unslash((string) ($_POST['item_label'] ?? '')));
    $items = em_wp_v4_get_items($type);

    if (!em_wp_rubrique_type_exists($type) || !isset($items[$source])) {
        em_wp_v4_builder_redirect(['v4_error' => 'duplicate', 'type' => $type]);
    }

    if ($label === '') {
        $label = (string) $items[$source] . ' COPIE';
    }

    $label = function_exists('mb_strtoupper') ? mb_strtoupper($label, 'UTF-8') : strtoupper($label);
    $slug = em_wp_v4_unique_item_slug($type, sanitize_title($label));

    $data = em_wp_v4_get_item($type, $source);
    $data['label'] = $label;

    $items[$slug] = $label;
    em_wp_v4_save_items($type, $items);
    em_wp_v4_save_item($type, $slug, $data);

    em_wp_v4_builder_redirect(['v4_updated' => 'duplicated', 'type' => $type, 'item' => $slug]);
}
add_action('admin_post_em_wp_v4_duplicate_item', 'em_wp_v4_handle_duplicate_item');

/**
 * Crée une nouvelle RUBRIQUE (type) personnalisée, stockée en option.
 *
 * La rubrique démarre avec les champs d'apparence mutualisés (section
 * « Apparence ») et aucun contenu. Elle apparaît ensuite dans la liste et le
 * sous-menu comme les types intégrés.
 */
function em_wp_v4_handle_create_type(): void
{
    em_wp_v4_items_guard('em_wp_v4_create_type');

    $label = sanitize_text_field(wp_unslash((string) ($_POST['type_label'] ?? '')));
    $icon = sanitize_html_class((string) ($_POST['type_icon'] ?? ''));
    $slug = sanitize_key(sanitize_title($label));

    if ($label === '' || $slug === '') {
        em_wp_v4_builder_redirect(['v4_error' => 'type_create']);
    }

    if (em_wp_rubrique_type_exists($slug)) {
        em_wp_v4_builder_redirect(['v4_error' => 'type_exists']);
    }

    $label_uc = function_exists('mb_strtoupper') ? mb_strtoupper($label, 'UTF-8') : strtoupper($label);
    $types = get_option(em_wp_rubrique_types_option_name(), []);
    $types = is_array($types) ? $types : [];
    $types[$slug] = [
        'label'        => $label_uc,
        'label_plural' => $label_uc,
        'icon'         => $icon !== '' ? $icon : 'dashicons-screenoptions',
        'starter'      => em_wp_rubrique_default_appearance_fields(),
        'layout'       => [],
    ];
    update_option(em_wp_rubrique_types_option_name(), $types);

    em_wp_v4_builder_redirect(['v4_updated' => 'type_created', 'type' => $slug]);
}
add_action('admin_post_em_wp_v4_create_type', 'em_wp_v4_handle_create_type');

/**
 * Supprime un footer (item).
 */
function em_wp_v4_handle_delete_item(): void
{
    em_wp_v4_items_guard('em_wp_v4_delete_item');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $item = sanitize_key((string) ($_POST['item'] ?? ''));

    if (!em_wp_rubrique_type_exists($type) || $item === '') {
        em_wp_v4_builder_redirect(['v4_error' => 'delete', 'type' => $type]);
    }

    em_wp_v4_delete_item($type, $item);
    em_wp_v4_builder_redirect(['v4_updated' => 'deleted', 'type' => $type]);
}
add_action('admin_post_em_wp_v4_delete_item', 'em_wp_v4_handle_delete_item');
