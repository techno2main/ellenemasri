<?php
/**
 * Registre des TYPES DE RUBRIQUE (V4) — modèle simplifié.
 *
 * Un « type de rubrique » est une famille du catalogue (FOOTER, HEADER…). Il ne
 * porte plus de « modèles » : chaque item (footer nommé) possède sa propre
 * structure. Le type fournit seulement un libellé, une icône et une STRUCTURE DE
 * DÉPART (starter) utilisée à la création d'un nouvel item.
 *
 * Types intégrés : filtre `em_wp_rubrique_types` (types/<slug>/type.php).
 * Types créés via wizard : option `em_wp_v4_rubrique_types`.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nom d'option des types créés via l'admin.
 */
function em_wp_rubrique_types_option_name(): string
{
    return 'em_wp_v4_rubrique_types';
}

/**
 * Nom d'option des libellés personnalisés (renommage des rubriques).
 *
 * Map slug => libellé d'affichage. S'applique aussi bien aux types intégrés
 * (code) qu'aux types créés via l'admin, sans toucher leur structure.
 */
function em_wp_rubrique_labels_option_name(): string
{
    return 'em_wp_v4_rubrique_labels';
}

/**
 * Libellés personnalisés enregistrés (map slug => libellé).
 *
 * @return array<string, string>
 */
function em_wp_rubrique_labels(): array
{
    $labels = get_option(em_wp_rubrique_labels_option_name(), []);

    return is_array($labels) ? $labels : [];
}

/**
 * Dérive le singulier d'un libellé pluriel (heuristique simple, FR/EN).
 *
 * Retire un « S » final (ex. NAVBARS -> NAVBAR, TOP-BARS -> TOP-BAR). Sert au
 * renommage : l'utilisateur saisit le nom pluriel affiché, on en déduit le
 * singulier pour le préfixe des items et le nom « Section <…> ».
 */
function em_wp_rubrique_singularize(string $plural): string
{
    $plural = trim($plural);
    $len = function_exists('mb_strlen') ? mb_strlen($plural, 'UTF-8') : strlen($plural);

    if ($len > 1) {
        $last = function_exists('mb_substr') ? mb_substr($plural, -1, 1, 'UTF-8') : substr($plural, -1);
        if (in_array($last, ['s', 'S'], true)) {
            return function_exists('mb_substr') ? mb_substr($plural, 0, $len - 1, 'UTF-8') : substr($plural, 0, -1);
        }
    }

    return $plural;
}

/**
 * Met « Casse de Titre » à un libellé (pour le noun « Section <…> »).
 */
function em_wp_rubrique_title_case(string $text): string
{
    $text = trim($text);

    return function_exists('mb_convert_case')
        ? mb_convert_case(mb_strtolower($text, 'UTF-8'), MB_CASE_TITLE, 'UTF-8')
        : ucwords(strtolower($text));
}

/**
 * Registre complet des types (mémoïsé par requête).
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_rubrique_type_registry(): array
{
    static $cache = null;

    if (is_array($cache)) {
        return $cache;
    }

    $code_types = (array) apply_filters('em_wp_rubrique_types', []);
    $saved = get_option(em_wp_rubrique_types_option_name(), []);
    $option_types = is_array($saved) ? $saved : [];

    $merged = $code_types;

    foreach ($option_types as $slug => $definition) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || isset($merged[$slug]) || !is_array($definition)) {
            continue;
        }

        $merged[$slug] = $definition;
    }

    $normalized = [];
    $overrides = em_wp_rubrique_labels();

    foreach ($merged as $slug => $definition) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || !is_array($definition)) {
            continue;
        }

        $type = em_wp_rubrique_type_normalize($slug, $definition);

        // Renommage : un libellé personnalisé (nom pluriel saisi) remplace le nom
        // de la rubrique PARTOUT — carte + sous-menu (pluriel), préfixe des items
        // (singulier dérivé) et nom « Section <…> » (noun). La structure (champs)
        // n'est pas touchée.
        if (isset($overrides[$slug]) && is_string($overrides[$slug]) && $overrides[$slug] !== '') {
            $plural = $overrides[$slug];
            $singular = em_wp_rubrique_singularize($plural);
            $type['label_plural'] = $plural;
            $type['label'] = $singular;
            $type['noun'] = em_wp_rubrique_title_case($singular);
        }

        $normalized[$slug] = $type;
    }

    $cache = $normalized;

    return $cache;
}

/**
 * Champs d'APPARENCE mutualisés (réglages globaux) communs à toutes les rubriques.
 *
 * Couleurs (fond/texte/liens/survol/cliqués), soulignement, espacements et police.
 * Réutilisé par chaque type/<slug>/type.php pour éviter la duplication.
 *
 * @return array<int, array<string, mixed>>
 */
function em_wp_rubrique_default_appearance_fields(): array
{
    return [
        ['key' => 'bg_color', 'type' => 'color', 'label' => __('Fond', 'em-wp'), 'default' => '#0f172a', 'options' => ['role' => 'background'], 'row' => 1, 'col' => 1],
        ['key' => 'text_color', 'type' => 'color', 'label' => __('Texte', 'em-wp'), 'default' => '#e2e8f0', 'options' => ['role' => 'text'], 'row' => 1, 'col' => 1],
        ['key' => 'link_color', 'type' => 'color', 'label' => __('Liens', 'em-wp'), 'default' => '#38bdf8', 'options' => ['role' => 'link'], 'row' => 1, 'col' => 1],
        ['key' => 'link_hover_color', 'type' => 'color', 'label' => __('Survol', 'em-wp'), 'default' => '#7dd3fc', 'options' => ['role' => 'link_hover'], 'row' => 1, 'col' => 1],
        ['key' => 'link_underline', 'type' => 'toggle', 'label' => __('Soulignés', 'em-wp'), 'default' => false, 'options' => ['role' => 'link_underline'], 'row' => 1, 'col' => 1],
        ['key' => 'space_top', 'type' => 'number', 'label' => __('Haut', 'em-wp'), 'default' => 40, 'options' => ['role' => 'space_top'], 'row' => 1, 'col' => 1],
        ['key' => 'space_bottom', 'type' => 'number', 'label' => __('Bas', 'em-wp'), 'default' => 40, 'options' => ['role' => 'space_bottom'], 'row' => 1, 'col' => 1],
        ['key' => 'space_left', 'type' => 'number', 'label' => __('Gauche', 'em-wp'), 'default' => 180, 'options' => ['role' => 'space_left'], 'row' => 1, 'col' => 1],
        ['key' => 'space_right', 'type' => 'number', 'label' => __('Droite', 'em-wp'), 'default' => 180, 'options' => ['role' => 'space_right'], 'row' => 1, 'col' => 1],
        ['key' => 'font_family', 'type' => 'select', 'label' => __('Police', 'em-wp'), 'default' => 'archivo_black', 'options' => ['role' => 'font'], 'row' => 1, 'col' => 1],
    ];
}

/**
 * Normalise un type (slug/label/icon/starter).
 *
 * @param array<string, mixed> $definition
 * @return array<string, mixed>
 */
function em_wp_rubrique_type_normalize(string $slug, array $definition): array
{
    // Mutualisation forte : on impose TOUJOURS les champs d'apparence COURANTS et
    // on ne conserve du starter déclaré/stocké que les champs de CONTENU. Ainsi un
    // type personnalisé (instantané en option) suit les évolutions des réglages
    // globaux comme les types intégrés.
    $raw_starter = is_array($definition['starter'] ?? null) ? $definition['starter'] : [];
    $content_only = array_values(array_filter(
        $raw_starter,
        static fn($field): bool => is_array($field) && !em_wp_rubrique_field_is_global($field)
    ));
    $starter = em_wp_rubrique_normalize_fields(
        array_merge(em_wp_rubrique_default_appearance_fields(), $content_only)
    );

    $label = (string) ($definition['label'] ?? mb_strtoupper($slug));

    return [
        'slug'         => $slug,
        'label'        => $label,
        'label_plural' => (string) ($definition['label_plural'] ?? $label),
        'icon'         => (string) ($definition['icon'] ?? 'dashicons-screenoptions'),
        // Nom d'affichage de la rubrique dans le nom d'item « Section <nom> »
        // (ex. « Section Top-Bar »). Défaut : libellé en Casse de Titre.
        'noun'         => (string) ($definition['noun'] ?? ''),
        'starter'      => $starter,
        'layout'       => em_wp_rubrique_normalize_layout($definition['layout'] ?? [], $starter),
    ];
}

/**
 * Formes grammaticales pour le nom d'item d'une rubrique (UI builder).
 *
 * Un item est nommé « Section <Rubrique> » (ex. « Section Top-Bar »). Le mot
 * « Section » étant féminin, toutes les formes sont accordées au féminin.
 *
 * @return array{singular:string, indef:string, def:string, dem:string, none:string, of:string, e:string}
 */
function em_wp_rubrique_type_nouns(string $type_slug): array
{
    $type = em_wp_rubrique_type_get($type_slug);
    $name = (string) ($type['noun'] ?? '');

    if ($name === '') {
        $label = (string) ($type['label'] ?? $type_slug);
        $name = function_exists('mb_convert_case')
            ? mb_convert_case(mb_strtolower($label, 'UTF-8'), MB_CASE_TITLE, 'UTF-8')
            : ucwords(strtolower($label));
    }

    $singular = $name !== ''
        ? sprintf(__('Section %s', 'em-wp'), $name)
        : __('Section', 'em-wp');

    return [
        'singular' => $singular,
        'indef'    => __('une', 'em-wp'),
        'def'      => __('la', 'em-wp'),
        'dem'      => __('cette', 'em-wp'),
        'none'     => __('Aucune', 'em-wp'),
        'of'       => __('de la', 'em-wp'),
        'e'        => 'e',
    ];
}

/**
 * Un type existe-t-il ?
 */
function em_wp_rubrique_type_exists(string $slug): bool
{
    $slug = sanitize_key($slug);

    return $slug !== '' && isset(em_wp_rubrique_type_registry()[$slug]);
}

/**
 * Récupère un type (ou null).
 *
 * @return array<string, mixed>|null
 */
function em_wp_rubrique_type_get(string $slug): ?array
{
    $slug = sanitize_key($slug);

    return em_wp_rubrique_type_registry()[$slug] ?? null;
}

/**
 * Structure de départ d'un type (champs normalisés) pour un nouvel item.
 *
 * @return array<int, array<string, mixed>>
 */
function em_wp_rubrique_type_starter_fields(string $slug): array
{
    $type = em_wp_rubrique_type_get($slug);

    return is_array($type['starter'] ?? null) ? $type['starter'] : [];
}

/**
 * Lay-out de départ d'un type (colonnes + alignement) pour un nouvel item.
 *
 * @return array{columns:int, align:array<int,string>}
 */
function em_wp_rubrique_type_starter_layout(string $slug): array
{
    $type = em_wp_rubrique_type_get($slug);

    return is_array($type['layout'] ?? null)
        ? $type['layout']
        : em_wp_rubrique_normalize_layout([], []);
}
