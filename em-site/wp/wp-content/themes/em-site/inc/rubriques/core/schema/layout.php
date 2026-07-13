<?php
/**
 * Schéma du LAY-OUT (EM-SITE) — grille par LIGNE.
 *
 * Chaque ligne définit son propre nombre de colonnes (1 à 4) et l'alignement de
 * chacune. Le lay-out est donc une liste de lignes :
 *   { rows: [ { columns:int, align:{1:str,2:str,…}, title:str, col_titles:{1:str,2:str,…} }, … ] }
 *
 * Les champs se positionnent par `row` (index de ligne) et `col` (colonne de la
 * ligne). Le rendu final utilise une grille CSS (repeat(columns, 1fr)) par ligne.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nombre maximal de colonnes d'une ligne.
 */
function em_site_rubrique_max_columns(): int
{
    return 4;
}

/**
 * Alignements de contenu possibles d'une colonne.
 *
 * @return array<string, string>
 */
function em_site_rubrique_alignments(): array
{
    return [
        'left'    => __('Aligné à gauche', 'em-site'),
        'center'  => __('Centré', 'em-site'),
        'right'   => __('Aligné à droite', 'em-site'),
        'justify' => __('Justifié', 'em-site'),
    ];
}

/**
 * Alignement valide (repli « left »).
 */
function em_site_rubrique_valid_align(string $align): string
{
    return isset(em_site_rubrique_alignments()[$align]) ? $align : 'left';
}

/**
 * Alignement par défaut d'une colonne selon sa position.
 */
function em_site_rubrique_default_align(int $index, int $columns): string
{
    if ($columns <= 1) {
        return 'center';
    }

    if ($index <= 1) {
        return 'left';
    }

    return $index >= $columns ? 'right' : 'center';
}

/**
 * Convertit une colonne en index entier (migre l'ancien left/center/right).
 *
 * @param mixed $col
 */
function em_site_rubrique_col_to_int($col): int
{
    if (is_string($col)) {
        $map = ['left' => 1, 'center' => 2, 'right' => 3];

        return $map[$col] ?? max(1, (int) $col);
    }

    return max(1, (int) $col);
}

/**
 * Index de colonne valide (1..columns).
 */
function em_site_rubrique_valid_col(int $col, int $columns): int
{
    $columns = max(1, $columns);

    return $col < 1 ? 1 : ($col > $columns ? $columns : $col);
}

/**
 * Nombre de lignes utilisées par une liste de champs (>= 1).
 *
 * @param array<int, array<string, mixed>> $fields
 */
function em_site_rubrique_fields_row_count(array $fields): int
{
    $max = 1;

    foreach ($fields as $field) {
        $max = max($max, (int) ($field['row'] ?? 1));
    }

    return $max;
}

/**
 * Normalise un lay-out en grille par ligne.
 *
 * Accepte le nouveau format { rows:[{columns,align}] } ou, en repli, l'ancien
 * format global { columns, align } appliqué à toutes les lignes.
 *
 * @param mixed                            $raw
 * @param array<int, array<string, mixed>> $fields champs (pour déduire le nombre de lignes)
 * @return array{rows:array<int,array{columns:int,align:array<int,string>,title:string,col_titles:array<int,string>}>}
 */
function em_site_rubrique_normalize_layout($raw, array $fields = []): array
{
    $raw = is_array($raw) ? $raw : [];
    $rows_raw = is_array($raw['rows'] ?? null) ? array_values($raw['rows']) : null;

    // Repli ancien format (global) → appliqué à chaque ligne.
    $legacy_cols = (int) ($raw['columns'] ?? 0);
    $legacy_align = is_array($raw['align'] ?? null) ? $raw['align'] : [];

    $count = max(1, em_site_rubrique_fields_row_count($fields), $rows_raw !== null ? count($rows_raw) : 0);

    $rows = [];

    for ($i = 0; $i < $count; $i++) {
        $entry = is_array($rows_raw[$i] ?? null) ? $rows_raw[$i] : null;

        if ($entry !== null) {
            $cols = (int) ($entry['columns'] ?? 0);
            $align_src = is_array($entry['align'] ?? null) ? $entry['align'] : [];
            $title = sanitize_text_field((string) ($entry['title'] ?? ''));
            $col_titles_src = is_array($entry['col_titles'] ?? null) ? $entry['col_titles'] : [];
        } else {
            $cols = $legacy_cols;
            $align_src = $legacy_align;
            $title = '';
            $col_titles_src = [];
        }

        $cols = min(em_site_rubrique_max_columns(), max(1, $cols));
        $align = [];
        $col_titles = [];

        for ($c = 1; $c <= $cols; $c++) {
            $value = isset($align_src[$c]) ? (string) $align_src[$c] : '';
            $align[$c] = $value !== '' ? em_site_rubrique_valid_align($value) : em_site_rubrique_default_align($c, $cols);

            $raw_title = isset($col_titles_src[$c]) ? (string) $col_titles_src[$c] : '';
            $col_titles[$c] = sanitize_text_field($raw_title);
        }

        $rows[] = ['columns' => $cols, 'align' => $align, 'title' => $title, 'col_titles' => $col_titles];
    }

    return ['rows' => $rows];
}

/**
 * Lignes d'un lay-out normalisé.
 *
 * @param array<string, mixed> $layout
 * @return array<int, array{columns:int, align:array<int,string>, title:string, col_titles:array<int,string>}>
 */
function em_site_rubrique_layout_rows(array $layout): array
{
    return is_array($layout['rows'] ?? null) ? array_values($layout['rows']) : [];
}

/**
 * Titre personnalisé d'une colonne d'une ligne (1-indexées).
 *
 * @param array<string, mixed> $layout
 */
function em_site_rubrique_layout_col_title_for(array $layout, int $row, int $col): string
{
    $rows = em_site_rubrique_layout_rows($layout);
    $entry = $rows[$row - 1] ?? null;
    $col_titles = is_array($entry['col_titles'] ?? null) ? $entry['col_titles'] : [];
    $value = isset($col_titles[$col]) ? (string) $col_titles[$col] : '';

    return sanitize_text_field($value);
}

/**
 * Titre d'une ligne (1-indexee).
 *
 * @param array<string, mixed> $layout
 */
function em_site_rubrique_layout_title_for(array $layout, int $row): string
{
    $rows = em_site_rubrique_layout_rows($layout);
    $entry = $rows[$row - 1] ?? null;

    return is_array($entry) ? sanitize_text_field((string) ($entry['title'] ?? '')) : '';
}

/**
 * Nombre de lignes d'un lay-out (>= 1).
 *
 * @param array<string, mixed> $layout
 */
function em_site_rubrique_layout_row_count(array $layout): int
{
    return max(1, count(em_site_rubrique_layout_rows($layout)));
}

/**
 * Nombre de colonnes d'une ligne (1-indexée).
 *
 * @param array<string, mixed> $layout
 */
function em_site_rubrique_layout_columns_for(array $layout, int $row): int
{
    $rows = em_site_rubrique_layout_rows($layout);
    $entry = $rows[$row - 1] ?? null;

    return is_array($entry) ? min(em_site_rubrique_max_columns(), max(1, (int) ($entry['columns'] ?? 1))) : 1;
}

/**
 * Alignement d'une colonne d'une ligne (1-indexées).
 *
 * @param array<string, mixed> $layout
 */
function em_site_rubrique_layout_align_for(array $layout, int $row, int $col): string
{
    $rows = em_site_rubrique_layout_rows($layout);
    $entry = $rows[$row - 1] ?? null;
    $align = is_array($entry['align'] ?? null) ? $entry['align'] : [];
    $columns = em_site_rubrique_layout_columns_for($layout, $row);

    $value = isset($align[$col]) ? (string) $align[$col] : '';

    return $value !== '' ? em_site_rubrique_valid_align($value) : em_site_rubrique_default_align($col, $columns);
}
