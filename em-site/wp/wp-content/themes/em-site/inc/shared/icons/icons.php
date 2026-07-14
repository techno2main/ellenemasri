<?php
/**
 * Registre central des icônes du site (source unique).
 *
 * IMPORTANT:
 * - Modifier uniquement ce fichier pour changer une icône admin/site.
 * - Les rubriques ET les hubs admin réutilisent ces helpers.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Icône de fallback unique pour les types/rubriques.
 */
function em_site_rubrique_type_fallback_icon(): string
{
    static $fallback = null;

    if (is_string($fallback) && $fallback !== '') {
        return $fallback;
    }

    $fallback = 'dashicons-warning';
    $list_file = __DIR__ . '/dashicons-list.txt';

    if (!is_readable($list_file)) {
        return $fallback;
    }

    $lines = file($list_file, FILE_IGNORE_NEW_LINES);

    if (!is_array($lines)) {
        return $fallback;
    }

    $first_icon = '';

    foreach ($lines as $line) {
        $line = trim((string) $line);

        if ($first_icon === '' && preg_match('/^(dashicons-[a-z0-9-]+)$/i', $line, $icon_match) === 1) {
            $first_icon = strtolower((string) $icon_match[1]);
        }

        if (preg_match('/fallback[^=]*=\s*(dashicons-[a-z0-9-]+)/i', $line, $matches) === 1) {
            $fallback = (string) $matches[1];
            return $fallback;
        }
    }

    if ($first_icon !== '') {
        $fallback = $first_icon;
        return $fallback;
    }

    return $fallback;
}

/**
 * Mapping central slug rubrique => dashicon.
 *
 * @return array<string, string>
 */
function em_site_rubrique_icons_map(): array
{
    return [
        'top-bar'         => 'dashicons-align-wide',
        'header'          => 'dashicons-columns',
        'headers'         => 'dashicons-columns',
        'hero'            => 'dashicons-format-image',
        'heros'           => 'dashicons-format-image',
        'heroes'          => 'dashicons-format-image',
        'slider'          => 'dashicons-images-alt2',
        'sliders'         => 'dashicons-images-alt2',
        'stream'          => 'dashicons-format-audio',
        'streams'         => 'dashicons-format-audio',
        'social'          => 'dashicons-share',
        'socials'         => 'dashicons-share',
        'video'           => 'dashicons-video-alt3',
        'videos'          => 'dashicons-video-alt3',
        'release'         => 'dashicons-album',
        'releases'        => 'dashicons-album',
        'cta'             => 'dashicons-megaphone',
        'ctas'            => 'dashicons-megaphone',
        'about'           => 'dashicons-star-filled',
        'abouts'          => 'dashicons-star-filled',
        'contact'         => 'dashicons-email-alt',
        'contacts'        => 'dashicons-email-alt',
        'footer'          => 'dashicons-align-center',
        'newsletters'     => 'dashicons-list-view',
        'custom-about'    => 'dashicons-star-filled',
        'custom-abouts'   => 'dashicons-star-filled',
        'custom-contact'  => 'dashicons-email-alt',
        'custom-contacts' => 'dashicons-email-alt',
    ];
}

/**
 * Mapping central des icônes des hubs/pages admin du site.
 *
 * @return array<string, string>
 */
function em_site_site_icons_map(): array
{
    return [
        'dashboard'      => 'dashicons-dashboard',
        'template'       => 'dashicons-layout',
        'rubriques'      => 'dashicons-screenoptions',
        'medias'         => 'dashicons-admin-media',
        'settings'       => 'dashicons-admin-settings',
        'appearance'     => 'dashicons-admin-appearance',
        'catalogues'     => 'dashicons-index-card',
        'vlb'            => 'dashicons-format-image',
        'media-add'      => 'dashicons-plus-alt',
        'generic'        => 'dashicons-admin-generic',
    ];
}

/**
 * Retourne l'icône d'un hub/page admin du site.
 */
function em_site_site_icon(string $key, string $fallback = 'dashicons-admin-generic'): string
{
    $map = em_site_site_icons_map();
    $normalized_key = sanitize_key($key);

    if ($normalized_key !== '' && isset($map[$normalized_key]) && $map[$normalized_key] !== '') {
        $candidate = (string) $map[$normalized_key];

        if (em_site_dashicon_is_allowed($candidate)) {
            return $candidate;
        }
    }

    if (em_site_dashicon_is_allowed($fallback)) {
        return $fallback;
    }

    return em_site_rubrique_type_fallback_icon();

}

/**
 * Vérifie qu'une Dashicon fait partie de la liste autorisée du projet.
 */
function em_site_dashicon_is_allowed(string $icon): bool
{
    $icon = trim($icon);

    if ($icon === '' || strpos($icon, 'dashicons-') !== 0) {
        return false;
    }

    return in_array($icon, em_site_dashicons_all(), true);
}

/**
 * Retourne l'icône normalisée pour un slug rubrique.
 */
function em_site_rubrique_icon(string $slug, string $fallback = 'dashicons-screenoptions'): string
{
    $slug = sanitize_key($slug);
    $map = em_site_rubrique_icons_map();

    if ($slug !== '' && isset($map[$slug]) && $map[$slug] !== '') {
        $candidate = (string) $map[$slug];

        if (em_site_dashicon_is_allowed($candidate)) {
            return $candidate;
        }
    }

    if (em_site_dashicon_is_allowed($fallback)) {
        return $fallback;
    }

    return em_site_rubrique_type_fallback_icon();
}

/**
 * Déduit une clé d'icône rubrique à partir du slug et des libellés.
 *
 * Permet de découpler les cas legacy où un slug historique (ex: "header")
 * peut porter un libellé visuel différent (ex: "HEROS").
 *
 * @param array<string, mixed> $definition
 */
function em_site_rubrique_icon_key_from_definition(string $slug, array $definition): string
{
    $normalized_slug = sanitize_key($slug);

    $label_parts = [
        (string) ($definition['label'] ?? ''),
        (string) ($definition['label_plural'] ?? ''),
        (string) ($definition['noun'] ?? ''),
    ];

    $label_blob = strtolower(trim(remove_accents(implode(' ', $label_parts))));

    if ($label_blob !== '') {
        if (strpos($label_blob, 'hero') !== false) {
            return 'hero';
        }

        if (strpos($label_blob, 'slider') !== false) {
            return 'slider';
        }

        if (strpos($label_blob, 'header') !== false) {
            return 'headers';
        }
    }

    return $normalized_slug;
}

/**
 * Applique le mapping central d'icônes au registre des rubriques code.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_site_rubrique_apply_icons_map(array $types): array
{
    foreach ($types as $slug => $definition) {
        if (!is_array($definition)) {
            continue;
        }

        $icon_key = em_site_rubrique_icon_key_from_definition((string) $slug, $definition);
        $fallback = (string) ($definition['icon'] ?? 'dashicons-screenoptions');
        $definition['icon'] = em_site_rubrique_icon($icon_key, $fallback);
        $types[$slug] = $definition;
    }

    return $types;
}
add_filter('em_site_rubrique_types', 'em_site_rubrique_apply_icons_map', 999);

/**
 * Retourne la liste complète des Dashicons disponibles (source locale).
 *
 * @return array<int, string>
 */
function em_site_dashicons_all(): array
{
    static $cache = null;

    if (is_array($cache)) {
        return $cache;
    }

    $list_file = __DIR__ . '/dashicons-list.txt';
    $icons = [];

    if (is_readable($list_file)) {
        $lines = file($list_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (is_array($lines)) {
            foreach ($lines as $line) {
                $value = trim((string) $line);

                if (strpos($value, 'dashicons-') === 0) {
                    $icons[] = $value;
                }
            }
        }
    }

    if ($icons === []) {
        $icons = ['dashicons-screenoptions'];
    }

    $icons = array_values(array_unique($icons));

    $fallback = em_site_rubrique_type_fallback_icon();
    if (!in_array($fallback, $icons, true)) {
        array_unshift($icons, $fallback);
    }

    usort($icons, static function (string $a, string $b) use ($fallback): int {
        if ($a === $fallback && $b !== $fallback) {
            return -1;
        }

        if ($b === $fallback && $a !== $fallback) {
            return 1;
        }

        return strcmp($a, $b);
    });

    $cache = $icons;

    return $cache;
}

/**
 * Détermine la catégorie UX d'une Dashicon.
 */
function em_site_dashicons_category_key(string $icon): string
{
    if (strpos($icon, 'dashicons-admin-') === 0) {
        return 'admin';
    }

    if (strpos($icon, 'dashicons-editor-') === 0 || strpos($icon, 'dashicons-text') === 0 || strpos($icon, 'dashicons-heading') === 0) {
        return 'editor';
    }

    if (preg_match('/^dashicons-(media-|format-|video-|images-|camera|playlist-|embedded-|slides|cover-image)/', $icon) === 1) {
        return 'media';
    }

    if (preg_match('/^dashicons-(arrow-|menu|leftright|sort|randomize|controls-|plus|minus|no|yes|dismiss|move|redo|undo|update|upload|download)/', $icon) === 1) {
        return 'navigation';
    }

    if (preg_match('/^dashicons-(email|phone|share|facebook|twitter|instagram|youtube|linkedin|pinterest|reddit|whatsapp|rss|feedback|megaphone|groups|networking)/', $icon) === 1) {
        return 'social';
    }

    if (preg_match('/^dashicons-(chart-|analytics|performance|database|portfolio|products|cart|money|store|tickets|clipboard|calendar|clock|marker|location)/', $icon) === 1) {
        return 'business';
    }

    if (preg_match('/^dashicons-(wordpress|site|dashboard|layout|screenoptions|welcome-|rest-api|shield|lock|unlock|privacy|visibility|hidden|search|filter)/', $icon) === 1) {
        return 'system';
    }

    return 'misc';
}

/**
 * Libellé de catégorie UX (affichage interface).
 */
function em_site_dashicons_category_label(string $key): string
{
    $labels = [
        'admin'      => 'Administration',
        'editor'     => 'Édition',
        'media'      => 'Média',
        'navigation' => 'Navigation & actions',
        'social'     => 'Communication & social',
        'business'   => 'Données & business',
        'system'     => 'Système WordPress',
        'misc'       => 'Divers',
    ];

    return $labels[$key] ?? 'Divers';
}

/**
 * Retourne les Dashicons regroupées par catégories UX.
 *
 * @return array<string, array<int, string>>
 */
function em_site_dashicons_categories(): array
{
    $icons = em_site_dashicons_all();

    $ordered_keys = ['admin', 'editor', 'media', 'navigation', 'social', 'business', 'system', 'misc'];
    $groups = [];

    foreach ($ordered_keys as $key) {
        $groups[em_site_dashicons_category_label($key)] = [];
    }

    foreach ($icons as $icon) {
        $key = em_site_dashicons_category_key($icon);
        $label = em_site_dashicons_category_label($key);
        $groups[$label][] = $icon;
    }

    foreach ($groups as $label => $items) {
        if ($items === []) {
            unset($groups[$label]);
            continue;
        }

        sort($items, SORT_STRING);
        $groups[$label] = $items;
    }

    return $groups;
}
