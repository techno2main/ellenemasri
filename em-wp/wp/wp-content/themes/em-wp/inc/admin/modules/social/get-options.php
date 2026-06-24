<?php

/**

 * Lecture options Social par template.

 *

 * @package em-wp

 */



if (!defined('ABSPATH')) {

    exit;

}



function em_wp_social_option_name(?string $template_slug = null): string

{

    if ($template_slug === null || $template_slug === '') {

        $template_slug = em_wp_social_resolve_template_slug();

    }



    return em_wp_template_resolve_option_name('social', $template_slug);

}



function em_wp_social_admin_template_slug(): string

{

    return em_wp_get_editing_template_slug();

}



function em_wp_social_resolve_template_slug(?string $preferred = null): string

{

    $preferred = sanitize_key((string) ($preferred ?? ''));



    if ($preferred !== '' && function_exists('em_wp_template_exists') && em_wp_template_exists($preferred)) {

        return $preferred;

    }



    // phpcs:ignore WordPress.Security.NonceVerification.Missing

    $from_post = sanitize_key((string) ($_POST['em_wp_template_context'] ?? ''));



    if ($from_post !== '' && function_exists('em_wp_template_exists') && em_wp_template_exists($from_post)) {

        return $from_post;

    }



    if (is_admin() && function_exists('em_wp_get_editing_template_slug')) {

        return em_wp_get_editing_template_slug();

    }



    return function_exists('em_wp_front_get_live_template_slug')

        ? em_wp_front_get_live_template_slug()

        : em_wp_get_active_template_slug();

}



/**

 * @return array<string, mixed>

 */

function em_wp_social_get_saved_rubrique_options(?string $template_slug = null): array

{

    if ($template_slug === null || $template_slug === '') {

        $template_slug = em_wp_social_resolve_template_slug();

    }



    return wp_parse_args(

        em_wp_get_template_rubrique_options('social', $template_slug),

        em_wp_social_rubrique_default_options()

    );

}



function em_wp_social_resolve_catalog_slug(?string $template_slug, array $rubrique): string

{

    $slug = sanitize_key((string) ($rubrique['social_slug'] ?? ''));



    if ($slug !== '' && function_exists('em_wp_social_normalize_catalog_slug')) {

        return em_wp_social_normalize_catalog_slug($slug);

    }



    if ($template_slug === null || $template_slug === '') {

        $template_slug = em_wp_social_resolve_template_slug();

    }



    $map = function_exists('em_wp_social_v1_slug_map') ? em_wp_social_v1_slug_map() : [];

    $mapped = sanitize_key((string) ($map[sanitize_key($template_slug)] ?? ''));

    if ($mapped !== '') {

        return $mapped;

    }

    return function_exists('em_wp_catalog_default_entry_slug_if_present')
        ? em_wp_catalog_default_entry_slug_if_present(em_wp_social_catalog_entries())
        : '';

}



/**

 * @param array<string, mixed> $rubrique

 * @return array<string, mixed>

 */

function em_wp_social_merge_rubrique_with_catalog(array $rubrique, ?string $template_slug = null): array

{

    if ($template_slug === null || $template_slug === '') {

        $template_slug = em_wp_social_resolve_template_slug();

    }



    $catalog_slug = em_wp_social_resolve_catalog_slug($template_slug, $rubrique);

    $catalog = $catalog_slug !== '' && function_exists('em_wp_social_get_catalog_options')

        ? em_wp_social_get_catalog_options($catalog_slug)

        : em_wp_social_catalog_default_options();



    $merged = wp_parse_args($catalog, em_wp_social_catalog_default_options());

    $merged['enabled'] = !empty($rubrique['enabled']);

    $merged['social_slug'] = $catalog_slug;

    $merged['platforms'] = em_wp_social_get_platforms_list($merged);

    $merged['background_color'] = (string) ($rubrique['background_color'] ?? '');

    $merged['text_color'] = (string) ($rubrique['text_color'] ?? '');



    return $merged;

}



function em_wp_social_get_options(?string $template_slug = null): array

{

    if ($template_slug === null || $template_slug === '') {

        $template_slug = em_wp_social_resolve_template_slug();

    }



    $options = em_wp_social_get_saved_rubrique_options($template_slug);

    $options['social_slug'] = em_wp_social_resolve_catalog_slug($template_slug, $options);



    return function_exists('em_wp_rubrique_sync_enabled_for_admin')

        ? em_wp_rubrique_sync_enabled_for_admin('social', $options)

        : $options;

}



function em_wp_social_get_options_for_front(): array

{

    $template_slug = function_exists('em_wp_front_get_live_template_slug')

        ? em_wp_front_get_live_template_slug()

        : em_wp_get_active_template_slug();



    $rubrique = em_wp_social_get_saved_rubrique_options($template_slug);



    return em_wp_social_merge_rubrique_with_catalog($rubrique, $template_slug);

}


