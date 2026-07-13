/**
 * PrÃ©pare le HTML d'une partie HERO/SLIDER pour la preview HEADER.
 * - Remplace <footer class="em-rubrique"> par <div> (pas de sÃ©mantique footer dans une colonne).
 * - Retire fond + padding rubrique (hÃ©ritÃ©s du shell ou configurÃ©s sur l'item SLIDER).
 */
function em_site_admin_header_preview_part_content(string $html): string
{
    if ($html === '') {
        return '';
    }

    if (!preg_match('/<footer\b([^>]*)>(.*)<\/footer>\s*$/is', $html, $match)) {
        return $html;
    }

    $attrs = (string) $match[1];
    $inner = (string) $match[2];

    if (preg_match('/\bstyle="([^"]*)"/i', $attrs, $style_match)) {
        $style = (string) $style_match[1];
        $style = preg_replace('/--em-rubrique-bg(?:-image|-size|-repeat|-position|-opacity|-transform)?\s*:[^;"]*;?/i', '', $style) ?? '';
        $style = preg_replace('/--em-rubrique-p[tlrb]\s*:[^;"]*;?/i', '', $style) ?? '';
        $style = preg_replace('/\s*;+\s*/', ';', $style) ?? '';
        $style = trim($style, '; ');
        if ($style !== '') {
            $style .= ';';
        }
        $style .= '--em-rubrique-bg:transparent;--em-rubrique-bg-image:none;--em-rubrique-pt:0;--em-rubrique-pb:0;--em-rubrique-pl:0;--em-rubrique-pr:0;';
        $attrs = (string) preg_replace('/\bstyle="[^"]*"/i', 'style="' . $style . '"', $attrs, 1);
    }

    return '<div' . $attrs . '>' . $inner . '</div>';
}

/**
 * Colonne preview HEADER (#hero ou #slider).
 */
function em_site_admin_header_preview_col(string $part, string $content): string
{
    if (trim($content) === '') {
        return '';
    }

    $part = $part === 'slider' ? 'slider' : 'hero';
    $id = $part === 'slider' ? 'slider' : 'hero';

    return '<div id="' . esc_attr($id) . '" class="em-header-shell__col em-header-shell__col--' . esc_attr($part) . '">'
        . $content
        . '</div>';
}

/**
 * Variables CSS du shell HEADER pour la preview admin (rubrique HEADER uniquement).
 *
 * @param array<string, mixed> $config
 * @return array<int, string>
 */
function em_site_admin_header_shell_style_vars(array $config): array
{
    if (!function_exists('em_site_header_bg_position_css')) {
        $compose = get_template_directory() . '/inc/front/modules/header/compose.php';
        if (is_readable($compose)) {
            require_once $compose;
        }
    }

    $appearance = is_array($config['appearance'] ?? null) ? $config['appearance'] : [];
    if (function_exists('em_site_admin_header_appearance_normalize')) {
        $appearance = em_site_admin_header_appearance_normalize($appearance);
    }

    $appearance_bg = sanitize_hex_color((string) ($appearance['bg'] ?? '')) ?: '';
    $bg = $appearance_bg !== '' ? $appearance_bg : 'transparent';

    $vars = [
        '--em-rubrique-bg:' . $bg,
        '--em-rubrique-pt:' . max(0, (int) ($appearance['pt'] ?? 0)) . 'px',
        '--em-rubrique-pb:' . max(0, (int) ($appearance['pb'] ?? 0)) . 'px',
        '--em-rubrique-pl:' . max(0, (int) ($appearance['pl'] ?? 0)) . 'px',
        '--em-rubrique-pr:' . max(0, (int) ($appearance['pr'] ?? 0)) . 'px',
        '--em-rubrique-bg-image:none',
    ];

    $bg_image_id = (int) ($appearance['bg_image_id'] ?? 0);
    $bg_url = $bg_image_id > 0 ? (string) wp_get_attachment_image_url($bg_image_id, 'full') : '';

    if ($bg_url !== '' && function_exists('em_site_header_bg_position_css')) {
        $bg_pos = em_site_header_bg_position_css((string) ($appearance['bg_image_pos'] ?? 'cover'));
        $opacity = max(0, min(100, (int) ($appearance['bg_image_opacity'] ?? 100)));
        $vars = array_filter($vars, static fn(string $line): bool => strpos($line, '--em-rubrique-bg-image:') !== 0);
        $vars[] = '--em-rubrique-bg-image:url(' . esc_url_raw($bg_url) . ')';
        $vars[] = '--em-rubrique-bg-size:' . $bg_pos['size'];
        $vars[] = '--em-rubrique-bg-repeat:' . $bg_pos['repeat'];
        $vars[] = '--em-rubrique-bg-position:' . $bg_pos['position'];
        $vars[] = '--em-rubrique-bg-opacity:' . round($opacity / 100, 2);
        $vars[] = '--em-rubrique-bg-transform:' . (!empty($appearance['bg_image_mirror']) ? 'scaleX(-1)' : 'none');
    }

    return array_values($vars);
}

/**
 * Padding du conteneur grille preview (HEADER appearance + dÃ©fauts front-like).
 *
 * @param array<string, mixed> $appearance
 */
function em_site_admin_header_preview_inner_style_vars(array $appearance): string
{
    if (function_exists('em_site_admin_header_appearance_normalize')) {
        $appearance = em_site_admin_header_appearance_normalize($appearance);
    }

    $pt = max(0, (int) ($appearance['pt'] ?? 0));
    $pb = max(0, (int) ($appearance['pb'] ?? 0));
    $pl = max(0, (int) ($appearance['pl'] ?? 0));
    $pr = max(0, (int) ($appearance['pr'] ?? 0));

    if ($pt === 0) {
        $pt = 44;
    }
    if ($pb === 0) {
        $pb = 68;
    }
    if ($pl === 0) {
        $pl = 24;
    }
    if ($pr === 0) {
        $pr = 24;
    }

    return sprintf(
        'padding:%dpx %dpx %dpx %dpx;gap:28px;box-sizing:border-box;',
        $pt,
        $pr,
        $pb,
        $pl
    );
}

/**
 * Colonnes CSS preview HEADER â€” mÃªme logique que le front (em_site_header_ratio_columns).
 */
function em_site_admin_header_preview_grid_columns(string $ratio, bool $slider_left): string
{
    if (function_exists('em_site_header_ratio_columns')) {
        return em_site_header_ratio_columns($ratio, $slider_left);
    }

    return em_site_admin_header_ratio_columns($ratio, $slider_left);
}

/**
 * @deprecated Utiliser em_site_admin_header_preview_part_content().
 */
function em_site_admin_header_embed_part_html(string $html): string
{
    return em_site_admin_header_preview_part_content($html);
}

/**
 * Enveloppe le markup composite HEADER (alignÃ© sur inc/front/modules/header/render.php).
 *
 * @param array<int, string>|string $shell_style
 */
function em_site_admin_header_wrap_composite_html($shell_style, string $inner_class, string $cols, string $inner_html, array $appearance = []): string
{
    if ($inner_html === '') {
        return '';
    }

    $shell_attr = is_array($shell_style)
        ? esc_attr(implode(';', $shell_style))
        : esc_attr((string) $shell_style);

    $box = em_site_admin_header_preview_inner_style_vars($appearance);
    $is_pair = strpos($inner_class, 'is-pair') !== false;
    $inner_style = '--em-header-ratio-cols:' . esc_attr($cols) . ';' . $box;
    if ($is_pair) {
        $inner_style .= 'display:grid!important;grid-template-columns:' . esc_attr($cols) . '!important;';
    } else {
        $inner_style .= 'grid-template-columns:' . esc_attr($cols) . ';';
    }

    return '<section class="em-section em-section--header" data-em-rubrique="header">'
        . '<div class="em-rubrique em-header-shell" style="' . $shell_attr . '">'
        . '<div class="' . esc_attr($inner_class) . '" data-em-header-ratio="' . esc_attr($cols) . '" style="' . esc_attr($inner_style) . '">'
        . $inner_html
        . '</div></div></section>';
}

/**
 * Rend un preview HEADER en rÃ©utilisant la logique front.
 *
 * @param array<string, mixed> $cfg
 */
function em_site_admin_header_preview_html_from_config(array $cfg): string
{
    if (!function_exists('em_site_header_hero_item_slug') || !function_exists('em_site_header_slider_item_slug')) {
        $compose = get_template_directory() . '/inc/front/modules/header/compose.php';
        if (is_readable($compose)) {
            require_once $compose;
        }
    }
    if (!function_exists('em_site_render_header_hero_html') || !function_exists('em_site_render_header_slider_html')) {
        $hero_render = get_template_directory() . '/inc/front/modules/hero/render.php';
        $slider_render = get_template_directory() . '/inc/front/modules/slider/render.php';
        if (is_readable($hero_render)) {
            require_once $hero_render;
        }
        if (is_readable($slider_render)) {
            require_once $slider_render;
        }
    }
    if (!function_exists('em_site_header_ratio_columns')) {
        return '';
    }

    $matrix = sanitize_key((string) ($cfg['matrix'] ?? 'hero'));
    $position = sanitize_key((string) ($cfg['position'] ?? 'hero_left'));
    $slider_left = $position === 'slider_left';

    $hero_item_slug = sanitize_key((string) ($cfg['hero'] ?? ''));
    if ($hero_item_slug === '') {
        $hero_item_slug = em_site_header_hero_item_slug($cfg);
    }
    $hero_item = $hero_item_slug !== '' && function_exists('em_site_header_hero_item') ? em_site_header_hero_item($hero_item_slug) : [];
    $hero_content = is_array($hero_item['content'] ?? null) ? $hero_item['content'] : [];
    $hero_html = ($matrix !== 'slider' && $hero_item_slug !== '')
        ? em_site_admin_header_preview_part_content(em_site_render_header_hero_html($hero_content, $hero_item_slug, true))
        : '';

    $slider_item_slug = sanitize_key((string) ($cfg['slider'] ?? ''));
    if ($slider_item_slug === '') {
        $slider_item_slug = em_site_header_slider_item_slug($cfg);
    }
    $slider_html = ($matrix !== 'hero' && $slider_item_slug !== '')
        ? em_site_admin_header_preview_part_content(em_site_render_header_slider_html($slider_item_slug))
        : '';

    $has_hero = trim($hero_html) !== '';
    $has_slider = trim($slider_html) !== '';
    if (!$has_hero && !$has_slider) {
        return '';
    }

    $is_pair = $has_hero && $has_slider;
    $cols = $is_pair
        ? em_site_admin_header_preview_grid_columns((string) ($cfg['ratio'] ?? '60-40'), $slider_left)
        : 'minmax(0,1fr)';
    $shell_style = em_site_admin_header_shell_style_vars($cfg);
    $inner_class = 'em-header-shell__inner';
    if ($slider_left) {
        $inner_class .= ' is-slider-first';
    }
    $inner_class .= $is_pair ? ' is-pair' : ' is-single';

    $inner = '';
    if ($is_pair && $slider_left) {
        $inner .= em_site_admin_header_preview_col('slider', $slider_html);
    }
    if ($has_hero) {
        $inner .= em_site_admin_header_preview_col('hero', $hero_html);
    }
    if ($is_pair && !$slider_left) {
        $inner .= em_site_admin_header_preview_col('slider', $slider_html);
    }
    if (!$is_pair && $has_slider) {
        $inner .= em_site_admin_header_preview_col('slider', $slider_html);
    }

    return em_site_admin_header_wrap_composite_html($shell_style, $inner_class, $cols, $inner, (array) ($cfg['appearance'] ?? []));
}