<?php
/**
 * Noms d'options contenu Video catalogue.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_video_catalog_item_option_name(string $catalog_slug): string
{
    $catalog_slug = em_wp_video_normalize_catalog_slug($catalog_slug);

    return 'em_wp_video_' . $catalog_slug . '_options';
}
