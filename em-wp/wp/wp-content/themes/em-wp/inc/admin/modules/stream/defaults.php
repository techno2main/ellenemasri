<?php
/**
 * Defaults et identifiants admin Stream.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug page admin Stream.
 */
function em_wp_stream_page_slug(): string
{
    return 'em-wp-stream';
}

/**
 * Clé POST / champs formulaire Stream (fixe, indépendante du template).
 */
function em_wp_stream_form_option_key(): string
{
    return 'em_wp_stream_options';
}

/**
 * Options par défaut Stream.
 */
function em_wp_stream_default_options(): array
{
    $platforms = [];

    foreach (array_keys(em_wp_stream_platform_definitions()) as $slug) {
        $platforms[] = em_wp_stream_default_platform_item($slug);
    }

    return [
        'enabled'           => true,
        'background_color'  => '',
        'text_color'        => '',
        'kicker'            => __('01 / Listen', 'em-wp'),
        'title_prefix'      => __('Stream', 'em-wp'),
        'title_logo'        => '',
        'availability_text' => __('Available everywhere', 'em-wp'),
        'card_label'        => __('Listen on', 'em-wp'),
        'platforms'         => $platforms,
    ];
}
