<?php
/**
 * Stockage V4 — noms d'options & accès items / instances.
 *
 * Namespace dédié `em_wp_v4_*` pour NE PAS écraser les options v3 (rollback sûr).
 *   - Item     : un footer nommé = STRUCTURE (champs + positions) + CONTENU.
 *                Forme : [ 'label', 'fields' => [...], 'content' => [...] ]
 *   - Instance : item choisi pour une rubrique dans un template.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Préfixe global des options V4.
 */
function em_wp_v4_option_prefix(): string
{
    return 'em_wp_v4_';
}

/**
 * Nom d'option de la LISTE des items d'un type (slug => label).
 */
function em_wp_v4_items_option_name(string $type_slug): string
{
    return em_wp_v4_option_prefix() . 'items_' . sanitize_key($type_slug);
}

/**
 * Nom d'option du CONTENU d'un item précis.
 */
function em_wp_v4_item_option_name(string $type_slug, string $item_slug): string
{
    return em_wp_v4_option_prefix() . 'item_' . sanitize_key($type_slug) . '_' . sanitize_key($item_slug);
}

/**
 * Nom d'option d'une INSTANCE (template + type de rubrique).
 */
function em_wp_v4_instance_option_name(string $template_slug, string $type_slug): string
{
    return em_wp_v4_option_prefix() . 'instance_' . sanitize_key($template_slug) . '_' . sanitize_key($type_slug);
}

/**
 * Liste des items d'un type (slug => label).
 *
 * @return array<string, string>
 */
function em_wp_v4_get_items(string $type_slug): array
{
    $items = get_option(em_wp_v4_items_option_name($type_slug), []);

    return em_wp_v4_sort_items(is_array($items) ? $items : []);
}

/**
 * Trie les items : ceux nommés « DEFAULT » d'abord, puis ordre alphabétique.
 *
 * @param array<string, string> $items slug => label
 * @return array<string, string>
 */
function em_wp_v4_sort_items(array $items): array
{
    uasort($items, static function ($a, $b): int {
        $da = preg_match('/\bdefault\b/i', (string) $a) ? 0 : 1;
        $db = preg_match('/\bdefault\b/i', (string) $b) ? 0 : 1;

        return $da !== $db ? $da - $db : strcasecmp((string) $a, (string) $b);
    });

    return $items;
}

/**
 * Enregistre la liste des items d'un type.
 *
 * @param array<string, string> $items
 */
function em_wp_v4_save_items(string $type_slug, array $items): bool
{
    return (bool) update_option(em_wp_v4_items_option_name($type_slug), $items);
}

/**
 * Item complet (label/fields/content/layout), valeurs sûres si absent.
 *
 * @return array{label:string, fields:array<int,array<string,mixed>>, content:array<string,mixed>, layout:array{columns:int,align:array<int,string>}}
 */
function em_wp_v4_get_item(string $type_slug, string $item_slug): array
{
    $data = get_option(em_wp_v4_item_option_name($type_slug, $item_slug), []);
    $data = is_array($data) ? $data : [];

    $fields = em_wp_rubrique_normalize_fields(is_array($data['fields'] ?? null) ? $data['fields'] : []);

    // Repli : un item sans structure démarre VIDE côté contenu (aucune ligne,
    // aucun champ) ; seuls les champs d'apparence (globaux) du type sont conservés.
    if ($fields === []) {
        [$starter_global] = em_wp_rubrique_split_global_fields(em_wp_rubrique_type_starter_fields($type_slug));
        $fields = $starter_global;
        $layout = em_wp_rubrique_normalize_layout([], $starter_global);
    } else {
        $fields = em_wp_v4_ensure_global_fields($type_slug, $fields);
        $layout = em_wp_rubrique_normalize_layout($data['layout'] ?? [], $fields);
    }

    return [
        'label'   => (string) ($data['label'] ?? $item_slug),
        'fields'  => $fields,
        'content' => is_array($data['content'] ?? null) ? $data['content'] : [],
        'layout'  => $layout,
        'anchor'  => em_wp_v4_sanitize_anchor((string) ($data['anchor'] ?? '')),
    ];
}

/**
 * Normalise une ancre (#section) en identifiant HTML sûr, SANS le « # ».
 *
 * Accepte « #stream », « Stream », « mon-ancre »… et renvoie un slug réutilisable
 * à la fois comme attribut id="" de la section et comme cible d'un lien #ancre.
 */
function em_wp_v4_sanitize_anchor(string $anchor): string
{
    return sanitize_title(ltrim(trim($anchor), '#'));
}

/**
 * Lay-out (colonnes + alignement) d'un item.
 *
 * @return array{columns:int, align:array<int,string>}
 */
function em_wp_v4_get_item_layout(string $type_slug, string $item_slug): array
{
    return em_wp_v4_get_item($type_slug, $item_slug)['layout'];
}

/**
 * Aligne les champs globaux (apparence) sur la définition du type.
 *
 * Les champs globaux sont pilotés par le type (libellés, rôles, défauts, ordre) :
 * on les reprend systématiquement depuis le starter et on conserve les champs de
 * CONTENU de l'item. Les VALEURS restent dans `content` (indexées par clé).
 *
 * @param array<int, array<string, mixed>> $fields
 * @return array<int, array<string, mixed>>
 */
function em_wp_v4_ensure_global_fields(string $type_slug, array $fields): array
{
    [$starter_global] = em_wp_rubrique_split_global_fields(em_wp_rubrique_type_starter_fields($type_slug));
    $content = array_values(array_filter(
        $fields,
        static fn(array $field): bool => !em_wp_rubrique_field_is_global($field)
    ));

    return array_merge($starter_global, $content);
}

/**
 * Supprime un item (sa liste + son option de données).
 */
function em_wp_v4_delete_item(string $type_slug, string $item_slug): void
{
    $item_slug = sanitize_key($item_slug);

    if ($item_slug === '') {
        return;
    }

    $items = em_wp_v4_get_items($type_slug);

    if (isset($items[$item_slug])) {
        unset($items[$item_slug]);
        em_wp_v4_save_items($type_slug, $items);
    }

    delete_option(em_wp_v4_item_option_name($type_slug, $item_slug));
}

/**
 * Champs (structure) d'un item.
 *
 * @return array<int, array<string, mixed>>
 */
function em_wp_v4_get_item_fields(string $type_slug, string $item_slug): array
{
    return em_wp_v4_get_item($type_slug, $item_slug)['fields'];
}

/**
 * Contenu fusionné (défauts de la structure + valeurs enregistrées).
 *
 * @return array<string, mixed>
 */
function em_wp_v4_get_item_content(string $type_slug, string $item_slug): array
{
    $item = em_wp_v4_get_item($type_slug, $item_slug);

    return array_merge(
        em_wp_rubrique_fields_defaults($item['fields']),
        $item['content']
    );
}

/**
 * Enregistre un item complet.
 *
 * @param array<string, mixed> $item
 */
function em_wp_v4_save_item(string $type_slug, string $item_slug, array $item): bool
{
    $item_slug = sanitize_key($item_slug);

    if ($item_slug === '') {
        return false;
    }

    return (bool) update_option(em_wp_v4_item_option_name($type_slug, $item_slug), $item);
}

/**
 * Met à jour uniquement la structure (champs) d'un item.
 *
 * @param array<int, array<string, mixed>> $fields
 */
function em_wp_v4_save_item_fields(string $type_slug, string $item_slug, array $fields): bool
{
    $item = em_wp_v4_get_item($type_slug, $item_slug);
    $item['fields'] = array_values($fields);

    return em_wp_v4_save_item($type_slug, $item_slug, $item);
}

/**
 * Met à jour uniquement le contenu d'un item.
 *
 * @param array<string, mixed> $content
 */
function em_wp_v4_save_item_content(string $type_slug, string $item_slug, array $content): bool
{
    $item = em_wp_v4_get_item($type_slug, $item_slug);
    $item['content'] = $content;

    return em_wp_v4_save_item($type_slug, $item_slug, $item);
}

/**
 * Déclare un item dans la liste (et l'initialise avec la structure de départ).
 */
function em_wp_v4_register_item(string $type_slug, string $item_slug, string $label): void
{
    $item_slug = sanitize_key($item_slug);

    if ($item_slug === '') {
        return;
    }

    $items = em_wp_v4_get_items($type_slug);
    $items[$item_slug] = $label !== '' ? $label : $item_slug;
    em_wp_v4_save_items($type_slug, $items);

    if (get_option(em_wp_v4_item_option_name($type_slug, $item_slug), null) === null) {
        $fields = em_wp_rubrique_type_starter_fields($type_slug);
        // On ne fige QUE les valeurs des champs de contenu. Les valeurs globales
        // (apparence) ne sont pas persistées à la création : elles suivent ainsi
        // toujours les défauts courants (mutualisation), tant que l'utilisateur
        // ne les modifie pas explicitement via le builder.
        [, $content_fields] = em_wp_rubrique_split_global_fields($fields);
        em_wp_v4_save_item($type_slug, $item_slug, [
            'label'   => $items[$item_slug],
            'fields'  => $fields,
            'content' => em_wp_rubrique_fields_defaults($content_fields),
            'layout'  => em_wp_rubrique_type_starter_layout($type_slug),
        ]);
    }
}

/**
 * Instance d'une rubrique pour un template, [] si absente.
 *
 * @return array<string, mixed>
 */
function em_wp_v4_get_instance(string $template_slug, string $type_slug): array
{
    $data = get_option(em_wp_v4_instance_option_name($template_slug, $type_slug), []);

    return is_array($data) ? $data : [];
}

/**
 * Enregistre une instance.
 *
 * @param array<string, mixed> $instance
 */
function em_wp_v4_save_instance(string $template_slug, string $type_slug, array $instance): bool
{
    return (bool) update_option(em_wp_v4_instance_option_name($template_slug, $type_slug), $instance);
}
