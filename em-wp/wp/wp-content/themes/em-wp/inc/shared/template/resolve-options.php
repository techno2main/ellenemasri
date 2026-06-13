<?php
/**
 * Résolution des options rubrique × template (fallback V1 Phase 0).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mapping V1 → options legacy pour le template Mayami uniquement.
 *
 * Phase 1+ migrera vers em_wp_{rubrique}_{template}_options.
 *
 * @return array<string, string>
 */
function em_wp_template_legacy_option_map(string $template_slug): array
{
    if ($template_slug !== em_wp_template_default_slug()) {
        return [];
    }

    return [
        'top-bar' => 'em_wp_top_bar_options',
        'hero'    => 'em_wp_hero_mayami_options',
        'slider'  => 'em_wp_slider_mayami_options',
        'stream'  => 'em_wp_stream_options',
        'social'  => 'em_wp_social_options',
        'video'   => 'em_wp_video_options',
        'release' => 'em_wp_release_options',
        'cta'     => 'em_wp_cta_options',
        'footer'  => 'em_wp_footer_options',
    ];
}

/**
 * Nom d'option effectif (V2 ou fallback V1).
 */
function em_wp_template_resolve_option_name(string $rubrique_slug, string $template_slug): string
{
    $rubrique_slug = sanitize_key($rubrique_slug);
    $template_slug = em_wp_template_sanitize_slug($template_slug);
    $v2_name = em_wp_template_option_name($rubrique_slug, $template_slug);

    if ($template_slug === em_wp_template_default_slug()) {
        $legacy_map = em_wp_template_legacy_option_map($template_slug);
        $legacy_name = $legacy_map[$rubrique_slug] ?? '';

        if ($legacy_name !== '') {
            $v2_saved = get_option($v2_name, null);

            if ($v2_saved === null) {
                return $legacy_name;
            }
        }
    }

    return $v2_name;
}

/**
 * Options brutes pour une rubrique et un template.
 *
 * @return array<string, mixed>
 */
function em_wp_get_template_rubrique_options(string $rubrique_slug, ?string $template_slug = null): array
{
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($rubrique_slug === '') {
        return [];
    }

    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_wp_get_active_template_slug();
    } else {
        $template_slug = em_wp_template_sanitize_slug($template_slug);
    }

    if ($template_slug === '') {
        return [];
    }

    $option_name = em_wp_template_resolve_option_name($rubrique_slug, $template_slug);
    $saved = get_option($option_name, []);

    return is_array($saved) ? $saved : [];
}
