<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rend un footer de rubrique via le renderer EM-SITE dynamique.
 *
 * @param array<string, mixed>|null $content_override
 * @param array<string, string|bool|int> $attrs
 */
function em_site_front_render_rubrique_footer(
    string $type_slug,
    string $item_slug,
    string $extra_classes = '',
    array $attrs = [],
    ?array $content_override = null
): string {
    if (!function_exists('em_site_rubrique_render_item')) {
        return '';
    }

    $type_slug = sanitize_key($type_slug);
    $item_slug = sanitize_key($item_slug);
    if ($type_slug === '' || $item_slug === '') {
        return '';
    }

    $html = em_site_rubrique_render_item($type_slug, $item_slug, $content_override);
    if ($html === '') {
        return '';
    }

    if ($extra_classes !== '') {
        $html = (string) preg_replace_callback(
            '/<footer\b([^>]*)class="([^"]*)"([^>]*)>/i',
            static function (array $m) use ($extra_classes): string {
                $classes = trim((string) $m[2] . ' ' . $extra_classes);
                return '<footer' . (string) $m[1] . 'class="' . esc_attr($classes) . '"' . (string) $m[3] . '>';
            },
            $html,
            1
        );
    }

    if ($attrs !== []) {
        $parts = [];
        foreach ($attrs as $name => $value) {
            $name = strtolower(trim((string) $name));
            if ($name === '' || !preg_match('/^[a-z_:][a-z0-9_:\-\.]*$/', $name)) {
                continue;
            }

            if (is_bool($value)) {
                if ($value) {
                    $parts[] = $name;
                }
                continue;
            }

            $parts[] = $name . '="' . esc_attr((string) $value) . '"';
        }

        if ($parts !== []) {
            $html = (string) preg_replace('/<footer\b/i', '<footer ' . implode(' ', $parts), $html, 1);
        }
    }

    return $html;
}

function em_site_front_footer_append_html(string $footer_html, string $append_html): string
{
    if ($footer_html === '' || $append_html === '') {
        return $footer_html;
    }

    $pos = strripos($footer_html, '</footer>');
    if ($pos === false) {
        return $footer_html . $append_html;
    }

    return substr($footer_html, 0, $pos) . $append_html . substr($footer_html, $pos);
}
