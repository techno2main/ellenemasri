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
 * Génère une base de slug d'item lisible et distincte par rubrique.
 *
 * Exemple : type `top-bar` + label `MAYAMI` => `top-bar-mayami`.
 */
function em_wp_v4_item_slug_base(string $type_slug, string $label_or_slug): string
{
    $type_slug = sanitize_key($type_slug);
    $value_slug = sanitize_key(sanitize_title($label_or_slug));
    $slug_prefix = em_wp_v4_item_slug_prefix($type_slug);

    if ($slug_prefix === '') {
        return $value_slug !== '' ? $value_slug : 'item';
    }

    if ($value_slug === '' || $value_slug === $slug_prefix || $value_slug === $type_slug) {
        return $slug_prefix;
    }

    if (strpos($value_slug, $slug_prefix . '-') === 0) {
        return $value_slug;
    }

    return $slug_prefix . '-' . $value_slug;
}

/**
 * Préfixe de slug métier par rubrique (peut différer du slug technique).
 */
function em_wp_v4_item_slug_prefix(string $type_slug): string
{
    $type_slug = sanitize_key($type_slug);

    $map = [
        'header' => 'hero',
        'contacts' => 'contact',
        'sliders' => 'slider',
    ];

    return $map[$type_slug] ?? $type_slug;
}

/**
 * Génère un slug d'item unique pour un type (suffixe -2, -3... si déjà pris).
 */
function em_wp_v4_unique_item_slug(string $type_slug, string $base_slug, string $exclude_slug = ''): string
{
    $base_slug = sanitize_key($base_slug);
    $exclude_slug = sanitize_key($exclude_slug);

    if ($base_slug === '') {
        $base_slug = 'item';
    }

    $items = em_wp_v4_get_items($type_slug);
    $slug = $base_slug;
    $i = 2;

    while (isset($items[$slug]) && $slug !== $exclude_slug) {
        $slug = $base_slug . '-' . $i;
        $i++;
    }

    return $slug;
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
 * Renomme un item (label + slug) et migre ses références d'instance.
 *
 * @return array{item:string,label:string}
 */
function em_wp_v4_rename_item(string $type_slug, string $item_slug, string $label): array
{
    $type_slug = sanitize_key($type_slug);
    $item_slug = sanitize_key($item_slug);
    $label = sanitize_text_field($label);

    if ($type_slug === '' || $item_slug === '') {
        return ['item' => '', 'label' => $label];
    }

    $items = em_wp_v4_get_items($type_slug);

    if (!isset($items[$item_slug])) {
        return ['item' => '', 'label' => $label];
    }

    if ($label === '') {
        $label = (string) $items[$item_slug];
    }

    $desired_slug = em_wp_v4_item_slug_base($type_slug, $label);
    $new_slug = em_wp_v4_unique_item_slug($type_slug, $desired_slug, $item_slug);

    if ($new_slug === $item_slug) {
        $items[$item_slug] = $label;
        em_wp_v4_save_items($type_slug, $items);

        $data = em_wp_v4_get_item($type_slug, $item_slug);
        $data['label'] = $label;
        em_wp_v4_save_item($type_slug, $item_slug, $data);

        return ['item' => $item_slug, 'label' => $label];
    }

    $new_items = [];
    foreach ($items as $slug => $item_label) {
        if ((string) $slug === $item_slug) {
            $new_items[$new_slug] = $label;
            continue;
        }

        $new_items[(string) $slug] = (string) $item_label;
    }
    em_wp_v4_save_items($type_slug, $new_items);

    $old_option = em_wp_v4_item_option_name($type_slug, $item_slug);
    $new_option = em_wp_v4_item_option_name($type_slug, $new_slug);
    $raw_item = get_option($old_option, null);
    $item_data = is_array($raw_item) ? $raw_item : em_wp_v4_get_item($type_slug, $item_slug);
    $item_data['label'] = $label;
    update_option($new_option, $item_data);
    delete_option($old_option);

    if (function_exists('em_wp_template_registry')) {
        foreach (array_keys((array) em_wp_template_registry()) as $template_slug) {
            $template_slug = sanitize_key((string) $template_slug);

            if ($template_slug === '') {
                continue;
            }

            $instance = em_wp_v4_get_instance($template_slug, $type_slug);

            if (sanitize_key((string) ($instance['item'] ?? '')) !== $item_slug) {
                continue;
            }

            $instance['item'] = $new_slug;
            em_wp_v4_save_instance($template_slug, $type_slug, $instance);
        }
    }

    return ['item' => $new_slug, 'label' => $label];
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

/**
 * Réconcilie une fois les slugs d'items hérités (slug != sanitize_title(label)).
 *
 * Évite les cas historiques du type `default => MAYAMI` après déploiement de la
 * migration de renommage de slug.
 */
function em_wp_v4_maybe_reconcile_item_slugs(): void
{
    if (get_option('em_wp_v4_item_slugs_reconciled_v4', false)) {
        return;
    }

    if (!function_exists('em_wp_rubrique_type_registry')) {
        return;
    }

    $types = em_wp_rubrique_type_registry();

    foreach (array_keys($types) as $type_slug) {
        $type_slug = sanitize_key((string) $type_slug);

        if ($type_slug === '') {
            continue;
        }

        $items = em_wp_v4_get_items($type_slug);

        foreach ($items as $item_slug => $label) {
            $item_slug = sanitize_key((string) $item_slug);
            $label = sanitize_text_field((string) $label);

            if ($item_slug === '' || $label === '') {
                continue;
            }

            $desired_slug = em_wp_v4_item_slug_base($type_slug, $label);

            if ($desired_slug === '' || $desired_slug === $item_slug) {
                continue;
            }

            em_wp_v4_rename_item($type_slug, $item_slug, $label);
        }
    }

    update_option('em_wp_v4_item_slugs_reconciled_v4', '1', false);
}
add_action('admin_init', 'em_wp_v4_maybe_reconcile_item_slugs', 5);
