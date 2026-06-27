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

    $layout = $item['layout'];
    [$style, $grid, $visited] = em_wp_rubrique_item_grid($fields, $content, $layout);
    $row_count = em_wp_rubrique_layout_row_count($layout);
    $uid = 'em-rubrique-' . sanitize_html_class($type_slug . '-' . $item_slug);
    // ":visited" ignore var() : on émet une couleur littérale scopée à cet item.
    $visited_css = $visited !== '' ? sanitize_hex_color($visited) : null;

    ob_start();
    ?>
    <?php if ($visited_css) : ?>
    <style>#<?php echo $uid; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> .em-rubrique__link:not(.em-rubrique__link--media):visited{color:<?php echo $visited_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;}</style>
    <?php endif; ?>
    <footer id="<?php echo esc_attr($uid); ?>" class="em-rubrique em-rubrique--<?php echo esc_attr($type_slug); ?>"<?php echo $style !== '' ? ' style="' . esc_attr($style) . '"' : ''; ?>>
        <?php for ($row = 1; $row <= $row_count; $row++) : ?>
            <?php $cols = em_wp_rubrique_layout_columns_for($layout, $row); ?>
            <div class="em-rubrique__row" style="grid-template-columns:repeat(<?php echo (int) $cols; ?>,minmax(0,1fr))">
                <?php for ($col = 1; $col <= $cols; $col++) : ?>
                    <div class="em-rubrique__col em-rubrique__col--<?php echo esc_attr(em_wp_rubrique_layout_align_for($layout, $row, $col)); ?>">
                        <?php echo implode('', $grid[$row][$col] ?? []); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
 * @param array<string, mixed>             $layout
 * @return array{0:string,1:array<int,array<int,array<int,string>>>,2:string}
 */
function em_wp_rubrique_item_grid(array $fields, array $content, array $layout): array
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
            $row = (int) $field['row'];
            $col = em_wp_rubrique_valid_col((int) $field['col'], em_wp_rubrique_layout_columns_for($layout, $row));
            $grid[$row][$col][] = $html;
        }
    }

    return [$style, $grid, $visited];
}
