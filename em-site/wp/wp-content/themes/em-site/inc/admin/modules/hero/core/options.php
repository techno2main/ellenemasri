<?php
/**
 * Options et sanitization du module Hero (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Valeurs par defaut du module Hero.
 */
function em_wp_hero_default_options(): array
{
    return [
        'enabled'                  => true,
        'badge_text'               => __('New Unique · Available!', 'em-wp'),
        'badge_text_hidden'        => false,
        'badge_bg_color'           => '',
        'badge_text_color'         => '',
        'subtitle'                 => __('Mayami, My Miami', 'em-wp'),
        'subtitle_hidden'          => false,
        'main_title'               => __('Mayami, My Miami', 'em-wp'),
        'logo_image'               => '',
        'logo_hidden'              => false,
        'logo_alt'                 => __('Mayami, My Miami', 'em-wp'),
        'description'              => __('A sun-soaked love letter to the city. Stream it, watch it, share it and follow the journey from the painted walls of Miami.', 'em-wp'),
        'description_hidden'       => false,
        'stream_label'             => __('◉ Stream', 'em-wp'),
        'stream_hidden'            => false,
        'stream_href'              => '#stream',
        'stream_bg_color'          => '',
        'stream_text_color'        => '',
        'watch_label'              => __('▶ Watch', 'em-wp'),
        'watch_hidden'             => false,
        'watch_href'               => '#video',
        'watch_bg_color'           => '',
        'watch_text_color'         => '',
    ];
}

/**
 * Retourne les options Hero normalisees.
 */
function em_wp_hero_get_options(string $style_slug = 'hero-mayami-default'): array
{
    if (function_exists('em_wp_hero_normalize_catalog_slug')) {
        $style_slug = em_wp_hero_normalize_catalog_slug($style_slug);
    }

    $saved = get_option(em_wp_hero_option_name($style_slug), []);

    if ($style_slug === 'hero-mayami-default' && empty($saved)) {
        $saved = get_option('em_wp_hero_mayami_options', []);
    }

    if ($style_slug === 'hero-mayami-default' && empty($saved)) {
        $saved = get_option('em_wp_hero_options', []);
    }

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, em_wp_hero_default_options());
}

/**
 * Sanitize callback Settings API pour une variante Hero.
 *
 * @param mixed $input
 */
function em_wp_hero_sanitize_options_for_style($input, string $style_slug): array
{
    $existing = em_wp_hero_get_options($style_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        // Catalogue : pas de visibilite rubrique hero.
    }

    $sanitize_color = static function (string $key) use ($input, $existing): string {
        $color = sanitize_hex_color($input[$key] ?? '');

        if ($color !== null && $color !== false && $color !== '') {
            return $color;
        }

        // Champ vide explicitement -> on enregistre vide (retour au defaut design).
        if (array_key_exists($key, $input) && trim((string) $input[$key]) === '') {
            return '';
        }

        return (string) ($existing[$key] ?? '');
    };

    return [
        'enabled'                  => $enabled,
        'badge_text'               => sanitize_text_field($input['badge_text'] ?? ($existing['badge_text'] ?? '')),
        'badge_text_hidden'        => !empty($input['badge_text_hidden']),
        'badge_bg_color'           => $sanitize_color('badge_bg_color'),
        'badge_text_color'         => $sanitize_color('badge_text_color'),
        'subtitle'                 => sanitize_text_field($input['subtitle'] ?? ($existing['subtitle'] ?? '')),
        'subtitle_hidden'          => !empty($input['subtitle_hidden']),
        'main_title'               => sanitize_text_field($input['main_title'] ?? ($existing['main_title'] ?? '')),
        'logo_image'               => esc_url_raw($input['logo_image'] ?? ($existing['logo_image'] ?? '')),
        'logo_hidden'              => !empty($input['logo_hidden']),
        'logo_alt'                 => sanitize_text_field($input['logo_alt'] ?? ($existing['logo_alt'] ?? '')),
        'description'              => sanitize_textarea_field($input['description'] ?? ($existing['description'] ?? '')),
        'description_hidden'       => !empty($input['description_hidden']),
        'stream_label'             => sanitize_text_field($input['stream_label'] ?? ($existing['stream_label'] ?? '')),
        'stream_hidden'            => !empty($input['stream_hidden']),
        'stream_href'              => esc_url_raw($input['stream_href'] ?? ($existing['stream_href'] ?? '')),
        'stream_bg_color'          => $sanitize_color('stream_bg_color'),
        'stream_text_color'        => $sanitize_color('stream_text_color'),
        'watch_label'              => sanitize_text_field($input['watch_label'] ?? ($existing['watch_label'] ?? '')),
        'watch_hidden'             => !empty($input['watch_hidden']),
        'watch_href'               => esc_url_raw($input['watch_href'] ?? ($existing['watch_href'] ?? '')),
        'watch_bg_color'           => $sanitize_color('watch_bg_color'),
        'watch_text_color'         => $sanitize_color('watch_text_color'),
    ];
}
