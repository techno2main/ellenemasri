<?php
/**
 * Noms d'options contenu Slider catalogue.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nom d'option WordPress pour le contenu d'un slider catalogue.
 */
function em_wp_slider_catalog_item_option_name(string $catalog_slug): string
{
    $catalog_slug = em_wp_slider_normalize_catalog_slug($catalog_slug);

    return 'em_wp_slider_' . $catalog_slug . '_options';
}
