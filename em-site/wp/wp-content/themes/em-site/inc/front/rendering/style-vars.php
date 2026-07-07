<?php
/**
 * Helpers de variables CSS front pour aligner tous les rendus sur les memes
 * regles d'apparence V4 (fond, transparent, texte, liens, espacements, typo,
 * image de fond).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, mixed>
 */
function em_site_front_rubrique_appearance_defaults(): array
{
    $defaults = [];

    if (function_exists('em_wp_rubrique_default_appearance_fields')) {
        $fields = em_wp_rubrique_default_appearance_fields();
        if (is_array($fields)) {
            foreach ($fields as $field) {
                if (!is_array($field)) {
                    continue;
                }
                $key = sanitize_key((string) ($field['key'] ?? ''));
                if ($key === '') {
                    continue;
                }
                $defaults[$key] = $field['default'] ?? '';
            }
        }
    }

    return $defaults;
}

/**
 * @return array<string, mixed>
 */
function em_site_front_decode_json_field($value): array
{
    if (!is_string($value) || $value === '') {
        return [];
    }

    $decoded = json_decode($value, true);

    return is_array($decoded) ? $decoded : [];
}

/**
 * @return array<int, string>
 */
function em_site_front_rubrique_style_vars(array $content, ?callable $font_stack_resolver = null): array
{
    $defaults = em_site_front_rubrique_appearance_defaults();

    $bg_default = sanitize_hex_color((string) ($defaults['bg_color'] ?? '')) ?: '#0f172a';
    $text_default = sanitize_hex_color((string) ($defaults['text_color'] ?? '')) ?: '#e2e8f0';
    $link_default = sanitize_hex_color((string) ($defaults['link_color'] ?? '')) ?: '#38bdf8';
    $hover_default = sanitize_hex_color((string) ($defaults['link_hover_color'] ?? '')) ?: '#7dd3fc';

    $bg = sanitize_hex_color((string) ($content['bg_color'] ?? '')) ?: $bg_default;
    if (!empty($content['bg_transparent'])) {
        $bg = 'transparent';
    }

    $text = sanitize_hex_color((string) ($content['text_color'] ?? '')) ?: $text_default;
    $link = sanitize_hex_color((string) ($content['link_color'] ?? '')) ?: $link_default;
    $hover = sanitize_hex_color((string) ($content['link_hover_color'] ?? '')) ?: $hover_default;

    $space_top = max(0, (int) ($content['space_top'] ?? (int) ($defaults['space_top'] ?? 40)));
    $space_bottom = max(0, (int) ($content['space_bottom'] ?? (int) ($defaults['space_bottom'] ?? 40)));
    $space_left = max(0, (int) ($content['space_left'] ?? (int) ($defaults['space_left'] ?? 180)));
    $space_right = max(0, (int) ($content['space_right'] ?? (int) ($defaults['space_right'] ?? 180)));

    $font_slug = sanitize_key((string) ($content['font_family'] ?? ($defaults['font_family'] ?? 'archivo_black')));
    $font_stack = '';
    if ($font_stack_resolver !== null) {
        $font_stack = (string) $font_stack_resolver($font_slug);
    } elseif (function_exists('em_wp_rubrique_font_stack')) {
        $font_stack = (string) em_wp_rubrique_font_stack($font_slug);
    }

    $vars = [
        '--em-rubrique-bg:' . $bg,
        '--em-rubrique-text:' . $text,
        '--em-rubrique-link:' . $link,
        '--em-rubrique-link-hover:' . $hover,
        '--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
        '--em-rubrique-pt:' . $space_top . 'px',
        '--em-rubrique-pb:' . $space_bottom . 'px',
        '--em-rubrique-pl:' . $space_left . 'px',
        '--em-rubrique-pr:' . $space_right . 'px',
    ];

    if ($font_stack !== '') {
        $vars[] = '--em-rubrique-font:' . $font_stack;
    }

    $bg_image = em_site_front_decode_json_field((string) ($content['bg_image'] ?? ''));
    $bg_id = (int) ($bg_image['id'] ?? 0);
    $bg_url = $bg_id > 0 ? (string) wp_get_attachment_image_url($bg_id, 'full') : '';
    if ($bg_url !== '') {
        $position = (string) ($content['bg_image_pos'] ?? ($defaults['bg_image_pos'] ?? 'cover'));
        $opacity = max(0, min(100, (int) ($content['bg_image_opacity'] ?? ($defaults['bg_image_opacity'] ?? 100))));

        if (function_exists('em_wp_rubrique_bg_position_css')) {
            $bp = em_wp_rubrique_bg_position_css($position);
            $size = (string) ($bp['size'] ?? 'cover');
            $repeat = (string) ($bp['repeat'] ?? 'no-repeat');
            $bg_position = (string) ($bp['position'] ?? 'center');
        } else {
            $size = 'cover';
            $repeat = 'no-repeat';
            $bg_position = 'center';
        }

        $vars[] = '--em-rubrique-bg-image:url(' . esc_url_raw($bg_url) . ')';
        $vars[] = '--em-rubrique-bg-size:' . $size;
        $vars[] = '--em-rubrique-bg-repeat:' . $repeat;
        $vars[] = '--em-rubrique-bg-position:' . $bg_position;
        $vars[] = '--em-rubrique-bg-opacity:' . round($opacity / 100, 2);
        $vars[] = '--em-rubrique-bg-transform:' . (!empty($content['bg_image_mirror']) ? 'scaleX(-1)' : 'none');
    }

    return $vars;
}
