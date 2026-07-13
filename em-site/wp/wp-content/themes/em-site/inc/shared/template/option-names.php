<?php
/**
 * Helpers noms d'options par rubrique × template.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nom d'option WordPress pour une rubrique et un template.
 *
 * Ex. em_site_stream_mayami_options
 */
function em_site_template_option_name(string $rubrique_slug, string $template_slug): string
{
    $rubrique_slug = sanitize_key($rubrique_slug);
    $template_slug = em_site_template_sanitize_slug($template_slug);
    $option_name = 'em_site_' . $rubrique_slug . '_' . $template_slug . '_options';

    if (function_exists('em_site_option_channelize_name')) {
        return em_site_option_channelize_name($option_name);
    }

    return $option_name;
}
