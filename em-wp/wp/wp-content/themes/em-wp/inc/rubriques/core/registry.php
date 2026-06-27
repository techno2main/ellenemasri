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

    foreach ($merged as $slug => $definition) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || !is_array($definition)) {
            continue;
        }

        $normalized[$slug] = em_wp_rubrique_type_normalize($slug, $definition);
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
        ['key' => 'link_visited_color', 'type' => 'color', 'label' => __('Cliqués', 'em-wp'), 'default' => '#818cf8', 'options' => ['role' => 'link_visited'], 'row' => 1, 'col' => 1],
        ['key' => 'link_underline', 'type' => 'toggle', 'label' => __('Soulignés', 'em-wp'), 'default' => true, 'options' => ['role' => 'link_underline'], 'row' => 1, 'col' => 1],
        ['key' => 'space_top', 'type' => 'number', 'label' => __('Haut', 'em-wp'), 'default' => 18, 'options' => ['role' => 'space_top'], 'row' => 1, 'col' => 1],
        ['key' => 'space_bottom', 'type' => 'number', 'label' => __('Bas', 'em-wp'), 'default' => 18, 'options' => ['role' => 'space_bottom'], 'row' => 1, 'col' => 1],
        ['key' => 'space_left', 'type' => 'number', 'label' => __('Gauche', 'em-wp'), 'default' => 20, 'options' => ['role' => 'space_left'], 'row' => 1, 'col' => 1],
        ['key' => 'space_right', 'type' => 'number', 'label' => __('Droite', 'em-wp'), 'default' => 20, 'options' => ['role' => 'space_right'], 'row' => 1, 'col' => 1],
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
    $starter = em_wp_rubrique_normalize_fields(
        is_array($definition['starter'] ?? null) ? $definition['starter'] : []
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
