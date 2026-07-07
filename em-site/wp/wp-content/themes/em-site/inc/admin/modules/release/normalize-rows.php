<?php
/**
 * Normalisation lignes Release.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param mixed $raw
 * @return array<int, array{key:string,value:string,hidden:bool}>
 */
function em_site_release_normalize_rows($raw): array
{
    if (!is_array($raw)) {
        return [];
    }

    $rows = [];

    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }

        $key = sanitize_text_field((string) ($row['key'] ?? ''));
        $value = sanitize_text_field((string) ($row['value'] ?? ''));

        if ($key === '' && $value === '') {
            continue;
        }

        $rows[] = [
            'key'    => $key,
            'value'  => $value,
            'hidden' => !empty($row['hidden']),
        ];
    }

    return $rows;
}
