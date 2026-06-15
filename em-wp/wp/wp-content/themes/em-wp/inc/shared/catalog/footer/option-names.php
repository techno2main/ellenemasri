<?php
/**
 * Noms d'options contenu Footer catalogue.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_footer_catalog_item_option_name(string $catalog_slug): string
{
    $catalog_slug = em_wp_footer_normalize_catalog_slug($catalog_slug);

    return 'em_wp_footer_' . $catalog_slug . '_options';
}
