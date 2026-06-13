<?php
/**
 * Paramétrage du module Releases (admin) — hub multi-variantes.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug de la page hub Releases.
 */
function em_wp_release_hub_menu_slug(): string
{
    return 'em-wp-releases';
}

/**
 * Variantes Release disponibles (extensible).
 *
 * @return array<string, array{label:string,menu_title:string,page_slug:string}>
 */
function em_wp_release_style_definitions(): array
{
    $definitions = [];

    /**
     * Filtre pour enregistrer de nouvelles variantes Release.
     *
     * @param array<string, array{label:string,menu_title:string,page_slug:string}> $definitions
     */
    return apply_filters('em_wp_release_style_definitions', $definitions);
}

/**
 * Slug Release actif sur le front.
 */
function em_wp_release_active_style_slug(): string
{
    return em_wp_admin_variant_hub_active_style_slug(em_wp_release_hub_config());
}

/**
 * Nom d'option WordPress pour une variante Release.
 */
function em_wp_release_option_name(string $style_slug): string
{
    return 'em_wp_release_' . sanitize_key($style_slug) . '_options';
}

/**
 * Nom de groupe Settings API pour une variante Release.
 */
function em_wp_release_group_name(string $style_slug): string
{
    return 'em_wp_release_' . sanitize_key($style_slug) . '_group';
}

/**
 * Options par défaut d'une variante Release.
 */
function em_wp_release_default_options(string $style_slug = ''): array
{
    unset($style_slug);

    return [
        'enabled' => true,
    ];
}

/**
 * Options Release normalisées.
 */
function em_wp_release_get_options(string $style_slug): array
{
    $saved = get_option(em_wp_release_option_name($style_slug), []);

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, em_wp_release_default_options($style_slug));
}

/**
 * Sanitize callback Settings API Release.
 */
function em_wp_release_sanitize_options(array $input): array
{
    return [
        'enabled' => !empty($input['enabled']),
    ];
}

/**
 * Configuration hub Release.
 *
 * @return array<string, mixed>
 */
function em_wp_release_hub_config(): array
{
    return [
        'module_slug'              => 'release',
        'hub_menu_slug'            => em_wp_release_hub_menu_slug(),
        'menu_icon'                => 'dashicons-album',
        'menu_position'            => static fn(): int => em_wp_admin_menu_position_for_site_module('release'),
        'hub_title'                => __('RELEASES', 'em-wp'),
        'eyebrow'                  => __('RELEASE', 'em-wp'),
        'hub_description'          => __('Liste des releases disponibles', 'em-wp'),
        'sidebar_title'            => __('Releases disponibles', 'em-wp'),
        'item_label_pattern'       => __('Release %s', 'em-wp'),
        'select_prompt'            => __('Sélectionnez une release dans la liste pour afficher sa configuration.', 'em-wp'),
        'empty_variants_message'   => __('Aucune variante release pour le moment. Les releases pourront être ajoutées ici, comme pour les heros et sliders.', 'em-wp'),
        'active_legend'            => __('Release affichée sur le site', 'em-wp'),
        'active_submit'            => __('Enregistrer la release active', 'em-wp'),
        'active_option'            => 'em_wp_release_active_style',
        'active_group'             => 'em_wp_release_global_group',
        'default_active_style'     => '',
        'style_definitions'        => 'em_wp_release_style_definitions',
        'option_name'              => 'em_wp_release_option_name',
        'group_name'               => 'em_wp_release_group_name',
        'default_options'          => 'em_wp_release_default_options',
        'sanitize_options'         => 'em_wp_release_sanitize_options',
        'get_options'              => 'em_wp_release_get_options',
        'setup_coming_soon_message'=> __('Configuration de cette release en cours de développement.', 'em-wp'),
    ];
}

em_wp_admin_boot_variant_hub(em_wp_release_hub_config());
