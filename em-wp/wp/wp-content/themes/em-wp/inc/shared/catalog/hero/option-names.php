<?php
/**
 * Noms d'options contenu Hero catalogue.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nom d'option WordPress pour le contenu d'un hero catalogue.
 */
function em_wp_hero_catalog_item_option_name(string $catalog_slug): string
{
    $catalog_slug = em_wp_hero_normalize_catalog_slug($catalog_slug);

    return 'em_wp_hero_' . $catalog_slug . '_options';
}
