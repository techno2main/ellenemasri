<?php
/**
 * Helpers noms d'options par rubrique × template.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nom d'option WordPress pour une rubrique et un template.
 *
 * Ex. em_wp_stream_mayami_options
 */
function em_wp_template_option_name(string $rubrique_slug, string $template_slug): string
{
    $rubrique_slug = sanitize_key($rubrique_slug);
    $template_slug = em_wp_template_sanitize_slug($template_slug);

    return 'em_wp_' . $rubrique_slug . '_' . $template_slug . '_options';
}
