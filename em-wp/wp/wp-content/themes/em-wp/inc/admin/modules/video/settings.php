<?php
/**
 * Paramétrage du module Videos (admin) — hub multi-variantes.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug de la page hub Videos.
 */
function em_wp_video_hub_menu_slug(): string
{
    return 'em-wp-videos';
}

/**
 * Variantes Video disponibles (extensible).
 *
 * @return array<string, array{label:string,menu_title:string,page_slug:string}>
 */
function em_wp_video_style_definitions(): array
{
    $definitions = [];

    /**
     * Filtre pour enregistrer de nouvelles variantes Video.
     *
     * @param array<string, array{label:string,menu_title:string,page_slug:string}> $definitions
     */
    return apply_filters('em_wp_video_style_definitions', $definitions);
}

/**
 * Slug Video actif sur le front.
 */
function em_wp_video_active_style_slug(): string
{
    return em_wp_admin_variant_hub_active_style_slug(em_wp_video_hub_config());
}

/**
 * Nom d'option WordPress pour une variante Video.
 */
function em_wp_video_option_name(string $style_slug): string
{
    return 'em_wp_video_' . sanitize_key($style_slug) . '_options';
}

/**
 * Nom de groupe Settings API pour une variante Video.
 */
function em_wp_video_group_name(string $style_slug): string
{
    return 'em_wp_video_' . sanitize_key($style_slug) . '_group';
}

/**
 * Options par défaut d'une variante Video.
 */
function em_wp_video_default_options(string $style_slug = ''): array
{
    unset($style_slug);

    return [
        'enabled' => true,
    ];
}

/**
 * Options Video normalisées.
 */
function em_wp_video_get_options(string $style_slug): array
{
    $saved = get_option(em_wp_video_option_name($style_slug), []);

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, em_wp_video_default_options($style_slug));
}

/**
 * Sanitize callback Settings API Video.
 */
function em_wp_video_sanitize_options(array $input): array
{
    return [
        'enabled' => !empty($input['enabled']),
    ];
}

/**
 * Configuration hub Video.
 *
 * @return array<string, mixed>
 */
function em_wp_video_hub_config(): array
{
    return [
        'module_slug'              => 'video',
        'hub_menu_slug'            => em_wp_video_hub_menu_slug(),
        'menu_icon'                => 'dashicons-video-alt3',
        'menu_position'            => static fn(): int => em_wp_admin_menu_position_for_site_module('video'),
        'hub_title'                => __('VIDEOS', 'em-wp'),
        'eyebrow'                  => __('VIDEO', 'em-wp'),
        'hub_description'          => __('Liste des videos disponibles', 'em-wp'),
        'sidebar_title'            => __('Videos disponibles', 'em-wp'),
        'item_label_pattern'       => __('Video %s', 'em-wp'),
        'select_prompt'            => __('Sélectionnez une video dans la liste pour afficher sa configuration.', 'em-wp'),
        'empty_variants_message'   => __('Aucune variante video pour le moment. Les videos pourront être ajoutées ici, comme pour les heros et sliders.', 'em-wp'),
        'active_legend'            => __('Video affichée sur le site', 'em-wp'),
        'active_submit'            => __('Enregistrer la video active', 'em-wp'),
        'active_option'            => 'em_wp_video_active_style',
        'active_group'             => 'em_wp_video_global_group',
        'default_active_style'     => '',
        'style_definitions'        => 'em_wp_video_style_definitions',
        'option_name'              => 'em_wp_video_option_name',
        'group_name'               => 'em_wp_video_group_name',
        'default_options'          => 'em_wp_video_default_options',
        'sanitize_options'         => 'em_wp_video_sanitize_options',
        'get_options'              => 'em_wp_video_get_options',
        'setup_coming_soon_message'=> __('Configuration de cette video en cours de développement.', 'em-wp'),
    ];
}

em_wp_admin_boot_variant_hub(em_wp_video_hub_config());
