<?php
/**
 * Helpers catalogue → assets front.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug dossier CSS/JS depuis un slug catalogue (hero-mayami-default → mayami).
 */
function em_wp_catalog_resolve_style_slug(string $type, string $catalog_slug): string
{
    $catalog_slug = sanitize_key($catalog_slug);
    $type = sanitize_key($type);

    if ($catalog_slug === '') {
        return 'mayami';
    }

    if (preg_match('/^(hero|slider)-([^-]+)-/', $catalog_slug, $matches)) {
        $style = sanitize_key($matches[2]);

        if ($type === 'hero' && $style === 'ellene') {
            return 'mayami';
        }

        return $style !== '' ? $style : 'mayami';
    }

    if ($type === 'hero' && function_exists('em_wp_hero_normalize_catalog_slug')) {
        $normalized = em_wp_hero_normalize_catalog_slug($catalog_slug);

        if (preg_match('/^hero-([^-]+)-/', $normalized, $matches)) {
            $style = sanitize_key($matches[1]);

            return $style === 'ellene' ? 'mayami' : ($style !== '' ? $style : 'mayami');
        }
    }

    if ($type === 'slider' && function_exists('em_wp_slider_normalize_catalog_slug')) {
        $normalized = em_wp_slider_normalize_catalog_slug($catalog_slug);

        if (preg_match('/^slider-([^-]+)-/', $normalized, $matches)) {
            $style = sanitize_key($matches[1]);

            return $style !== '' ? $style : 'mayami';
        }
    }

    return 'mayami';
}
