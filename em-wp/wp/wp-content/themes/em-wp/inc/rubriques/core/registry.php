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
        'starter'      => $starter,
        'layout'       => em_wp_rubrique_normalize_layout($definition['layout'] ?? [], $starter),
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
