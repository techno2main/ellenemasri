<?php
/**
 * Rendu d'un ITEM par LIGNES × COLONNES (V4).
 *
 * Rend un footer (item) à partir de sa structure (champs positionnés en
 * lignes/colonnes) et de son contenu. Les couleurs à rôle (fond/texte) pilotent
 * le style du bloc ; les autres champs s'affichent à leur position.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rend un item en HTML.
 *
 * @param array<string, mixed>|null $content_override
 */
function em_wp_rubrique_render_item(string $type_slug, string $item_slug, ?array $content_override = null): string
{
    $item = em_wp_v4_get_item($type_slug, $item_slug);
    $fields = $item['fields'];

    if ($fields === []) {
        return '';
    }

    $content = $content_override === null
        ? em_wp_v4_get_item_content($type_slug, $item_slug)
        : array_merge(em_wp_rubrique_fields_defaults($fields), $content_override);

    $columns = (int) $item['layout']['columns'];
    $align = $item['layout']['align'];
    [$style, $grid, $visited] = em_wp_rubrique_item_grid($fields, $content, $columns);
    $row_count = em_wp_rubrique_fields_row_count($fields);
    $uid = 'em-rubrique-' . sanitize_html_class($type_slug . '-' . $item_slug);
    // ":visited" ignore var() : on émet une couleur littérale scopée à cet item.
    $visited_css = $visited !== '' ? sanitize_hex_color($visited) : null;

    ob_start();
    ?>
    <?php if ($visited_css) : ?>
    <style>#<?php echo $uid; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> .em-rubrique__link:not(.em-rubrique__link--media):visited{color:<?php echo $visited_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;}</style>
    <?php endif; ?>
    <footer id="<?php echo esc_attr($uid); ?>" class="em-rubrique em-rubrique--<?php echo esc_attr($type_slug); ?> em-rubrique--cols-<?php echo (int) $columns; ?>"<?php echo $style !== '' ? ' style="' . esc_attr($style) . '"' : ''; ?>>
        <?php for ($row = 1; $row <= $row_count; $row++) : ?>
            <div class="em-rubrique__row">
                <?php for ($col = 1; $col <= $columns; $col++) : ?>
                    <div class="em-rubrique__col em-rubrique__col--<?php echo esc_attr($align[$col] ?? 'left'); ?>">
                        <?php foreach (($grid[$row][$col] ?? []) as $html) : ?>
                            <?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endfor; ?>
    </footer>
    <?php

    return (string) ob_get_clean();
}

/**
 * Variables CSS pilotées par les couleurs globales (rôle => --variable).
 *
 * @return array<string, string>
 */
function em_wp_rubrique_color_role_vars(): array
{
    return [
        'background'    => '--em-rubrique-bg',
        'text'          => '--em-rubrique-text',
        'link'          => '--em-rubrique-link',
        'link_hover'    => '--em-rubrique-link-hover',
        'link_visited'  => '--em-rubrique-link-visited',
    ];
}

/**
 * Sépare le style (couleurs à rôle) et range les champs en grille [row][col].
 *
 * @param array<int, array<string, mixed>> $fields
 * @param array<string, mixed>             $content
 * @return array{0:string,1:array<int,array<int,array<int,string>>>,2:string}
 */
function em_wp_rubrique_item_grid(array $fields, array $content, int $columns): array
{
    $style = '';
    $grid = [];
    $visited = '';

    foreach ($fields as $field) {
        $value = $content[$field['key']] ?? ($field['default'] ?? '');
        $role = (string) ($field['options']['role'] ?? '');

        if ($field['type'] === 'color' && isset(em_wp_rubrique_color_role_vars()[$role])) {
            if ($value !== '') {
                $style .= em_wp_rubrique_color_role_vars()[$role] . ':' . $value . ';';
                if ($role === 'link_visited') {
                    $visited = (string) $value;
                }
            }
            continue;
        }

        if ($field['type'] === 'toggle' && $role === 'link_underline') {
            $style .= '--em-rubrique-underline:' . ($value ? 'underline' : 'none') . ';';
            continue;
        }

        if ($field['type'] === 'select' && $role === 'font') {
            $stack = em_wp_rubrique_font_stack((string) $value);
            if ($stack !== '') {
                $style .= '--em-rubrique-font:' . $stack . ';';
            }
            continue;
        }

        $space_vars = ['space_top' => '--em-rubrique-pt', 'space_bottom' => '--em-rubrique-pb', 'space_left' => '--em-rubrique-pl', 'space_right' => '--em-rubrique-pr'];
        if ($field['type'] === 'number' && isset($space_vars[$role])) {
            $style .= $space_vars[$role] . ':' . max(0, (int) $value) . 'px;';
            continue;
        }

        if (!empty($field['hidden'])) {
            continue;
        }

        $html = em_wp_rubrique_item_field_html($field, $value);

        if ($html !== '') {
            $col = em_wp_rubrique_valid_col((int) $field['col'], $columns);
            $grid[(int) $field['row']][$col][] = $html;
        }
    }

    return [$style, $grid, $visited];
}

/**
 * Style CSS d'une image (redimension + recadrage non destructif via object-fit).
 */
function em_wp_rubrique_image_style(int $w, int $h, int $fx, int $fy): string
{
    $style = '';

    if ($w > 0) {
        $style .= 'width:' . $w . 'px;';
    }

    if ($h > 0) {
        $style .= 'height:' . $h . 'px;';
    }

    if ($w > 0 && $h > 0) {
        $style .= 'object-fit:cover;object-position:' . $fx . '% ' . $fy . '%;';
    }

    return $style;
}

/**
 * Rend un champ de contenu selon son type.
 *
 * @param array<string, mixed> $field
 * @param mixed                $value
 */
function em_wp_rubrique_item_field_html(array $field, $value): string
{
    $type = (string) $field['type'];
    $label = (string) $field['label'];
    $key = (string) $field['key'];

    switch ($type) {
        case 'sep_line':
            $sep_color = em_wp_field_sanitize_color((string) $value);
            return '<hr class="em-rubrique__sep"' . ($sep_color !== '' ? ' style="border-color:' . esc_attr($sep_color) . '"' : '') . '>';

        case 'sep_blank':
            return '<span class="em-rubrique__spacer" aria-hidden="true"></span>';

        case 'arrow_up':
            return em_wp_rubrique_arrow_html($value, 'up', '&uarr;');

        case 'arrow_down':
            return em_wp_rubrique_arrow_html($value, 'down', '&darr;');

        case 'textarea':
        case 'text':
            return $value === '' ? '' : '<p class="em-rubrique__field em-rubrique__field--' . esc_attr($key) . '">' . esc_html((string) $value) . '</p>';

        case 'url':
            if ($value === '') {
                return '';
            }
            // Lien externe → nouvel onglet ; ancre interne (#) → même page.
            $target = strpos((string) $value, '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';
            return '<a class="em-rubrique__link" href="' . esc_url((string) $value) . '"' . $target . '>' . esc_html($label !== '' ? $label : (string) $value) . '</a>';

        case 'email':
            return $value === '' ? '' : '<a class="em-rubrique__link" href="' . esc_attr('mailto:' . sanitize_email((string) $value)) . '">' . esc_html((string) $value) . '</a>';

        case 'image':
            $img_data = em_wp_rubrique_image_value($value);
            $url = $img_data['id'] ? wp_get_attachment_image_url($img_data['id'], 'large') : '';
            if ($url === '') {
                return '';
            }
            $img_style = em_wp_rubrique_image_style((int) $img_data['w'], (int) $img_data['h'], (int) $img_data['fx'], (int) $img_data['fy']);
            $img = '<img class="em-rubrique__image" src="' . esc_url($url) . '" alt="' . esc_attr($label) . '"' . ($img_style !== '' ? ' style="' . esc_attr($img_style) . '"' : '') . '>';
            if ($img_data['link'] === '') {
                return $img;
            }
            $img_target = strpos($img_data['link'], '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';
            return '<a class="em-rubrique__link em-rubrique__link--media" href="' . esc_url($img_data['link']) . '"' . $img_target . '>' . $img . '</a>';

        case 'icon':
            $icon_data = em_wp_rubrique_icon_value($value);
            $icon = $icon_data['platform'] !== '' ? em_wp_rubrique_platform_icon($icon_data['platform']) : '';
            if ($icon === '') {
                return '';
            }
            $glyph = '<i class="em-rubrique__icon fa-brands ' . esc_attr($icon) . '" title="' . esc_attr($label) . '" aria-hidden="true"></i>';
            return $icon_data['url'] === ''
                ? $glyph
                : '<a class="em-rubrique__link em-rubrique__link--media" href="' . esc_url($icon_data['url']) . '" target="_blank" rel="noopener noreferrer">' . $glyph . '</a>';

        case 'toggle':
            return '<span class="em-rubrique__chip">' . esc_html($label) . ' : ' . ($value ? esc_html__('oui', 'em-wp') : esc_html__('non', 'em-wp')) . '</span>';

        case 'color':
            return $value === '' ? '' : '<span class="em-rubrique__swatch" style="background:' . esc_attr((string) $value) . '" title="' . esc_attr($label) . '"></span>';

        case 'number':
        case 'select':
        default:
            return $value === '' ? '' : '<span class="em-rubrique__field">' . esc_html((string) $value) . '</span>';
    }
}

/**
 * HTML d'une flèche de navigation : glyphe coloré, ancre/URL optionnelle.
 *
 * @param mixed $value
 */
function em_wp_rubrique_arrow_html($value, string $dir, string $glyph): string
{
    $arrow = em_wp_rubrique_arrow_value($value);
    $style = $arrow['color'] !== '' ? ' style="color:' . esc_attr($arrow['color']) . '"' : '';
    $span = '<span class="em-rubrique__arrow em-rubrique__arrow--' . esc_attr($dir) . '" aria-hidden="true"' . $style . '>' . $glyph . '</span>';

    if ($arrow['link'] === '') {
        return $span;
    }

    // Ancre interne (#section) → même page ; lien externe → nouvel onglet.
    $target = strpos($arrow['link'], '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';

    return '<a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="' . esc_url($arrow['link']) . '"' . $target . $style . '>' . $span . '</a>';
}
