<?php
/**
 * Moteur de rendu des rubriques (EM-SITE) — simplifié.
 *
 * Chaîne : Instance (template+type) → Item (footer nommé) → Rendu lignes/colonnes.
 * Plus de notion de « modèle » : un item porte sa propre structure et son contenu.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rend une rubrique en HTML. Vide si non résoluble.
 *
 * @param array<string, mixed> $args  item, template, content (override)
 */
function em_site_rubrique_render(string $type_slug, array $args = []): string
{
    $type_slug = sanitize_key($type_slug);

    if (!em_site_rubrique_type_exists($type_slug)) {
        return '';
    }

    $item_slug = em_site_rubrique_resolve_item_slug($type_slug, $args);

    if ($item_slug === '') {
        return '';
    }

    return em_site_rubrique_render_item(
        $type_slug,
        $item_slug,
        is_array($args['content'] ?? null) ? $args['content'] : null
    );
}

/**
 * Résout l'item à rendre : argument explicite > instance de template > Default.
 *
 * @param array<string, mixed> $args
 */
function em_site_rubrique_resolve_item_slug(string $type_slug, array $args): string
{
    $item = sanitize_key((string) ($args['item'] ?? ''));

    if ($item !== '') {
        return $item;
    }

    $template = sanitize_key((string) ($args['template'] ?? ''));

    if ($template !== '') {
        $instance = em_site_get_instance($template, $type_slug);
        $item = sanitize_key((string) ($instance['item'] ?? ''));

        if ($item !== '') {
            return $item;
        }
    }

    return em_site_rubrique_default_item_slug($type_slug);
}

/**
 * Slug de l'item « Default » d'un type ('' si aucun).
 */
function em_site_rubrique_default_item_slug(string $type_slug): string
{
    $items = em_site_get_items($type_slug);

    if (isset($items['default'])) {
        return 'default';
    }

    return $items === [] ? '' : (string) array_key_first($items);
}
