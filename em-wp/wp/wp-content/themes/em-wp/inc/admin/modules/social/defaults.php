<?php
/**
 * Defaults et identifiants admin Social.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_social_page_slug(): string
{
    return 'em-wp-social';
}

function em_wp_social_form_option_key(): string
{
    return 'em_wp_social_options';
}

function em_wp_social_default_options(): array
{
    $platforms = [];

    foreach (array_keys(em_wp_social_platform_definitions()) as $slug) {
        $platforms[] = em_wp_social_default_platform_item($slug);
    }

    return [
        'enabled'          => true,
        'background_color' => '',
        'text_color'       => '',
        'kicker'           => __('02 / Follow', 'em-wp'),
        'title_left'       => __('Join the', 'em-wp'),
        'title_right'      => __('journey', 'em-wp'),
        'description'      => __('Share clips, updates, and behind-the-scenes moments.', 'em-wp'),
        'platforms'        => $platforms,
    ];
}
