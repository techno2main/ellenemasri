<?php
/**
 * Builder de STRUCTURE d'un item (admin-post) — EM-SITE.
 *
 * Enregistre en un seul appel la structure complète d'un footer composée côté
 * client : champs (type + libellé) positionnés en lignes × colonnes + nom du
 * footer (forcé en MAJUSCULES). Les couleurs globales (fond/texte) ne sont pas
 * dans la grille : elles sont conservées telles quelles. `manage_options`.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL retour vers la page EM-SITE (avec ancre type/item).
 *
 * @param array<string, string> $args
 */
function em_site_overview_redirect_url(array $args = []): string
{
    return add_query_arg($args, admin_url('admin.php?page=em-rubriques-overview'));
}

/**
 * Vérifie capacité + nonce.
 */
function em_site_items_guard(string $nonce_action): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Accès refusé.', 'em-site'));
    }

    check_admin_referer($nonce_action);
}

/**
 * Redirection retour.
 *
 * @param array<string, string> $args
 */
function em_site_builder_redirect(array $args): void
{
    wp_safe_redirect(em_site_overview_redirect_url($args));
    exit;
}

/**
 * Génère une clé de champ unique.
 *
 * @param array<int, array<string, mixed>> $fields
 */
function em_site_unique_field_key(array $fields, string $label): string
{
    $base = sanitize_key(str_replace('-', '_', sanitize_title($label)));
    $base = $base !== '' ? $base : 'champ';

    $existing = array_column($fields, 'key');
    $key = $base;
    $i = 2;

    while (in_array($key, $existing, true)) {
        $key = $base . '_' . $i;
        $i++;
    }

    return $key;
}

/**
 * Enregistre un item complet : lay-out (colonnes + alignement) + structure
 * (champs positionnés) + contenu (valeurs) + couleurs globales + nom (forcé
 * MAJUSCULES), en un seul appel.
 */
function em_site_handle_save_item(): void
{
    em_site_items_guard('em_site_save_item');

    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $item = sanitize_key((string) ($_POST['item'] ?? ''));

    if (!em_site_rubrique_type_exists($type) || $item === '') {
        em_site_builder_redirect(['error' => 'save']);
    }

    $payload = em_site_decode_payload();
    $rows_raw = is_array($payload['rows'] ?? null) ? array_values($payload['rows']) : [];

    [$global] = em_site_rubrique_split_global_fields(em_site_get_item_fields($type, $item));

    $current = [];
    foreach (em_site_get_item_fields($type, $item) as $field) {
        $current[(string) $field['key']] = $field;
    }

    $fields = $global;
    $raw_content = [];

    foreach (is_array($payload['fields'] ?? null) ? $payload['fields'] : [] as $entry) {
        $erow = max(1, (int) (is_array($entry) ? ($entry['row'] ?? 1) : 1));
        $clamp = em_site_payload_row_columns($rows_raw, $erow);
        $built = em_site_build_structure_field((array) $entry, $current, $fields, $clamp);

        if ($built === null) {
            continue;
        }

        $fields[] = $built;
        $raw_content[$built['key']] = is_array($entry) ? ($entry['value'] ?? '') : '';
    }

    $layout = em_site_rubrique_normalize_layout(['rows' => $rows_raw], $fields);

    $posted = isset($_POST['fields']) && is_array($_POST['fields']) ? wp_unslash($_POST['fields']) : [];
    $content = em_site_rubrique_sanitize_content($fields, array_merge($posted, $raw_content));

    $data = em_site_get_item($type, $item);
    $data['fields'] = $fields;
    $data['layout'] = $layout;
    $data['content'] = $content;
    em_site_save_item($type, $item, $data);

    $label = sanitize_text_field(wp_unslash((string) ($_POST['item_label'] ?? '')));
    $label = function_exists('mb_strtoupper') ? mb_strtoupper($label, 'UTF-8') : strtoupper($label);
    $renamed = em_site_rename_item($type, $item, $label);
    $target_item = (string) ($renamed['item'] ?? $item);

    em_site_builder_redirect(['updated' => 'saved', 'type' => $type, 'item' => $target_item]);
}
add_action('admin_post_em_site_save_item', 'em_site_handle_save_item');

/**
 * Décode le payload JSON du builder : { rows:[{columns,align}], fields }.
 *
 * @return array<string, mixed>
 */
function em_site_decode_payload(): array
{
    $json = (string) wp_unslash($_POST['structure'] ?? '');
    $decoded = json_decode($json, true);

    return is_array($decoded) ? $decoded : [];
}

/**
 * Nombre de colonnes d'une ligne dans un payload brut (repli = max).
 *
 * @param array<int, mixed> $rows_raw
 */
function em_site_payload_row_columns(array $rows_raw, int $row): int
{
    $entry = $rows_raw[$row - 1] ?? null;
    $cols = is_array($entry) ? (int) ($entry['columns'] ?? 0) : 0;

    return $cols > 0 ? min(em_site_rubrique_max_columns(), max(1, $cols)) : em_site_rubrique_max_columns();
}

/**
 * Construit un champ de contenu depuis une entrée du builder.
 *
 * @param array<string, mixed> $entry
 * @param array<string, array<string, mixed>> $current
 * @param array<int, array<string, mixed>> $fields déjà placés (pour clé unique)
 * @return array<string, mixed>|null
 */
function em_site_build_structure_field(array $entry, array $current, array $fields, int $columns): ?array
{
    $ftype = sanitize_key((string) ($entry['type'] ?? ''));
    $label = sanitize_text_field((string) ($entry['label'] ?? ''));

    // Les couleurs (fond/texte) sont globales : jamais insérées dans la grille.
    // Le libellé n'est plus requis : un champ inséré est conservé tel quel, on le
    // personnalise (libellé, contenu, lien…) ensuite, même sur plusieurs passages.
    if (!em_site_field_type_exists($ftype) || $ftype === 'color') {
        return null;
    }

    $row = max(1, (int) ($entry['row'] ?? 1));
    $col = em_site_rubrique_valid_col((int) ($entry['col'] ?? 1), $columns);
    $key = sanitize_key((string) ($entry['key'] ?? ''));

    if ($key !== '' && isset($current[$key]) && (string) ($current[$key]['type'] ?? '') === $ftype) {
        $field = $current[$key];
        $field['label'] = $label;
    } else {
        $field = [
            'key'     => em_site_unique_field_key($fields, $label !== '' ? $label : $ftype),
            'type'    => $ftype,
            'label'   => $label,
            'default' => em_site_field_type_default($ftype),
            'options' => [],
        ];
    }

    $field['row'] = $row;
    $field['col'] = $col;
    $field['hidden'] = !empty($entry['hidden']);

    // Style de texte propre au champ (taille/typo/couleur) conservé dans options.
    $field['options'] = is_array($field['options'] ?? null) ? $field['options'] : [];
    if (em_site_rubrique_field_supports_text_style($ftype)) {
        $field['options']['style'] = em_site_rubrique_normalize_text_style((array) ($entry['style'] ?? []));
    }

    return $field;
}


