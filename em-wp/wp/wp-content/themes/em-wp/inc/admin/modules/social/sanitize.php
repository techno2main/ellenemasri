<?php

/**

 * Sanitize options Social.

 *

 * @package em-wp

 */



if (!defined('ABSPATH')) {

    exit;

}



/**

 * Sanitize options rubrique SOCIAL (template).

 *

 * @param mixed $input

 * @return array<string, mixed>

 */

function em_wp_social_sanitize_rubrique_options($input): array

{

    $template_slug = em_wp_social_resolve_template_slug();

    $existing = em_wp_social_get_saved_rubrique_options($template_slug);



    if (!is_array($input)) {

        return $existing;

    }



    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);

    $social_slug = sanitize_key((string) ($input['social_slug'] ?? ($existing['social_slug'] ?? '')));



    if ($social_slug !== '' && function_exists('em_wp_social_normalize_catalog_slug')) {

        $social_slug = em_wp_social_normalize_catalog_slug($social_slug);

    }



    if ($social_slug !== '' && function_exists('em_wp_social_catalog_has') && !em_wp_social_catalog_has($social_slug)) {

        $social_slug = sanitize_key((string) ($existing['social_slug'] ?? ''));

    }



    $background_color = sanitize_hex_color($input['background_color'] ?? '');

    $text_color = sanitize_hex_color($input['text_color'] ?? '');



    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {

        em_wp_admin_sync_rubrique_visibility_from_post('social');

    }



    return [

        'enabled'          => $enabled,

        'social_slug'      => $social_slug,

        'background_color' => $background_color !== null && $background_color !== false && $background_color !== ''

            ? $background_color

            : (string) ($existing['background_color'] ?? ''),

        'text_color'       => $text_color !== null && $text_color !== false && $text_color !== ''

            ? $text_color

            : (string) ($existing['text_color'] ?? ''),

    ];

}



/**

 * Sanitize options contenu catalogue Social.

 *

 * @param mixed $input

 * @return array<string, mixed>

 */

function em_wp_social_sanitize_catalog_options($input): array

{

    if (!is_array($input)) {

        return em_wp_social_catalog_default_options();

    }



    return [

        'kicker'           => sanitize_text_field($input['kicker'] ?? ''),

        'title_left'       => sanitize_text_field($input['title_left'] ?? ''),

        'title_right'      => sanitize_text_field($input['title_right'] ?? ''),

        'description'      => sanitize_textarea_field($input['description'] ?? ''),

        'platforms'        => em_wp_social_sanitize_platforms_from_input($input['platforms'] ?? []),

    ];

}



/**

 * @param mixed $input

 */

function em_wp_social_sanitize_options($input, bool $sync_rubrique = true): array

{

    if ($sync_rubrique) {

        return em_wp_social_sanitize_rubrique_options($input);

    }



    return em_wp_social_sanitize_catalog_options($input);

}


