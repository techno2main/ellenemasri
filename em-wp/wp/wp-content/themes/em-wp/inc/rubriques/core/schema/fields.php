<?php
/**
 * Schéma des CHAMPS (V4) — modèle simplifié (sans « modèles »).
 *
 * Un item (ex. « Footer Default ») porte directement sa STRUCTURE : une liste de
 * champs, chacun positionné par LIGNE (row) et COLONNE (gauche/centre/droite).
 * Ce fichier normalise les champs, calcule les valeurs par défaut et sanitise un
 * contenu saisi.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Types de champ décoratifs : ni libellé ni valeur (séparateurs, flèches).
 *
 * @return array<int, string>
 */
function em_wp_rubrique_decorative_types(): array
{
    return ['sep_line', 'sep_blank', 'arrow_up', 'arrow_down'];
}

/**
 * Indique si un type de champ est décoratif (sans libellé).
 */
function em_wp_rubrique_field_is_decorative(string $type): bool
{
    return in_array($type, em_wp_rubrique_decorative_types(), true);
}

/**
 * Types décoratifs avec choix de couleur. @return array<int, string>
 */
function em_wp_rubrique_decorative_color_types(): array
{
    return ['sep_line', 'arrow_up', 'arrow_down'];
}

/**
 * Polices proposées pour une rubrique (clé => [label, pile CSS]).
 * La police du site actuel est volontairement placée en premier.
 *
 * @return array<string, array{label:string, stack:string}>
 */
function em_wp_rubrique_font_choices(): array
{
    return [
        'archivo_black' => ['label' => __('Archivo Black (site)', 'em-wp'), 'stack' => '"Archivo Black", system-ui, sans-serif'],
        'system'        => ['label' => __('Système', 'em-wp'), 'stack' => 'system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif'],
        'inter'         => ['label' => 'Inter', 'stack' => 'Inter, system-ui, sans-serif'],
        'montserrat'    => ['label' => 'Montserrat', 'stack' => 'Montserrat, system-ui, sans-serif'],
        'poppins'       => ['label' => 'Poppins', 'stack' => 'Poppins, system-ui, sans-serif'],
        'oswald'        => ['label' => 'Oswald', 'stack' => 'Oswald, system-ui, sans-serif'],
        'roboto'        => ['label' => 'Roboto', 'stack' => 'Roboto, system-ui, sans-serif'],
        'playfair'      => ['label' => 'Playfair Display', 'stack' => '"Playfair Display", Georgia, serif'],
        'serif'         => ['label' => __('Serif', 'em-wp'), 'stack' => 'Georgia, "Times New Roman", serif'],
        'mono'          => ['label' => __('Monospace', 'em-wp'), 'stack' => 'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace'],
    ];
}

/**
 * Pile CSS d'une police par sa clé ('' si inconnue).
 */
function em_wp_rubrique_font_stack(string $key): string
{
    $choices = em_wp_rubrique_font_choices();

    return isset($choices[$key]) ? $choices[$key]['stack'] : '';
}

/**
 * Normalise une liste de champs (structure d'un item).
 *
 * @param array<int, mixed> $raw
 * @return array<int, array<string, mixed>>
 */
function em_wp_rubrique_normalize_fields(array $raw): array
{
    $fields = [];

    foreach ($raw as $field) {
        if (!is_array($field)) {
            continue;
        }

        $key = sanitize_key((string) ($field['key'] ?? ''));
        $type = sanitize_key((string) ($field['type'] ?? 'text'));

        if ($key === '' || $type === '') {
            continue;
        }

        $fields[] = [
            'key'     => $key,
            'type'    => $type,
            'label'   => (string) ($field['label'] ?? $key),
            'default' => $field['default'] ?? em_wp_field_type_default($type),
            'options' => is_array($field['options'] ?? null) ? $field['options'] : [],
            'row'     => max(1, (int) ($field['row'] ?? 1)),
            'col'     => em_wp_rubrique_valid_col(em_wp_rubrique_col_to_int($field['col'] ?? 1), em_wp_rubrique_max_columns()),
            'hidden'  => !empty($field['hidden']),
        ];
    }

    return $fields;
}

/**
 * Carte des valeurs par défaut d'une liste de champs (key => default).
 *
 * @param array<int, array<string, mixed>> $fields
 * @return array<string, mixed>
 */
function em_wp_rubrique_fields_defaults(array $fields): array
{
    $defaults = [];

    foreach ($fields as $field) {
        $defaults[$field['key']] = $field['default'] ?? '';
    }

    return $defaults;
}

/**
 * Sanitise un contenu saisi selon une liste de champs.
 *
 * @param array<int, array<string, mixed>> $fields
 * @param array<string, mixed>             $raw
 * @return array<string, mixed>
 */
function em_wp_rubrique_sanitize_content(array $fields, array $raw): array
{
    $clean = [];

    foreach ($fields as $field) {
        $key = $field['key'];
        $value = $raw[$key] ?? ($field['default'] ?? '');
        $clean[$key] = em_wp_field_type_sanitize((string) $field['type'], $value);
    }

    return $clean;
}

/**
 * Un champ est-il un réglage GLOBAL du bloc (couleur fond/texte) ?
 * Ces champs ne se positionnent pas en lignes/colonnes : ils stylent tout le bloc.
 *
 * @param array<string, mixed> $field
 */
function em_wp_rubrique_field_is_global(array $field): bool
{
    $type = (string) ($field['type'] ?? '');
    $role = (string) ($field['options']['role'] ?? '');

    if ($type === 'color') {
        return in_array($role, ['background', 'text', 'link', 'link_hover', 'link_visited'], true);
    }

    if ($type === 'toggle') {
        return $role === 'link_underline';
    }

    if ($type === 'select') {
        return $role === 'font';
    }

    if ($type === 'number') {
        return in_array($role, ['space_top', 'space_bottom', 'space_left', 'space_right'], true);
    }

    return false;
}

/**
 * Sépare une liste de champs en [globaux, positionnables].
 *
 * @param array<int, array<string, mixed>> $fields
 * @return array{0:array<int,array<string,mixed>>,1:array<int,array<string,mixed>>}
 */
function em_wp_rubrique_split_global_fields(array $fields): array
{
    $global = [];
    $content = [];

    foreach ($fields as $field) {
        if (em_wp_rubrique_field_is_global($field)) {
            $global[] = $field;
        } else {
            $content[] = $field;
        }
    }

    return [$global, $content];
}
