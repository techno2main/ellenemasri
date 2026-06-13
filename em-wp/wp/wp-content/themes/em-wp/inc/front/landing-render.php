<?php
/**
 * Rendu front de la landing selon l'ordre admin des rubriques.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Indique si deux slugs forment une paire HEROS / SLIDERS adjacente.
 */
function em_wp_landing_is_hero_slider_pair(string $first, string $second): bool
{
    return ($first === 'hero' && $second === 'slider') || ($first === 'slider' && $second === 'hero');
}

/**
 * Découpe l'ordre du milieu en segments (module seul ou paire hero+slider).
 *
 * @param string[] $order
 * @return array<int, array{type:string,slug?:string,order?:string[]}>
 */
function em_wp_landing_parse_middle_segments(array $order): array
{
    $segments = [];
    $count = count($order);
    $index = 0;

    while ($index < $count) {
        $current = $order[$index];
        $next = $order[$index + 1] ?? '';

        if ($next !== '' && em_wp_landing_is_hero_slider_pair($current, $next)) {
            $segments[] = [
                'type'  => 'hero-slider-pair',
                'order' => [$current, $next],
            ];
            $index += 2;
            continue;
        }

        $segments[] = [
            'type' => 'module',
            'slug' => $current,
        ];
        $index += 1;
    }

    return $segments;
}

/**
 * Ancre HTML d'une rubrique sans module front dédié (navigation #stream, etc.).
 */
function em_wp_landing_section_anchor_id(string $module_slug): string
{
    $anchors = [
        'stream'  => 'stream',
        'social'  => 'social',
        'video'   => 'video',
        'release' => 'release',
        'cta'     => 'cta',
        'hero'    => 'hero',
        'slider'  => 'slider',
        'footer'  => 'footer',
    ];

    return $anchors[$module_slug] ?? $module_slug;
}

/**
 * Indique si un module milieu est activé côté front.
 */
function em_wp_landing_module_is_enabled(string $module_slug): bool
{
    switch ($module_slug) {
        case 'hero':
            if (!function_exists('em_wp_get_hero_options_for_front')) {
                return true;
            }

            return !empty(em_wp_get_hero_options_for_front()['enabled']);
        case 'slider':
            if (!function_exists('em_wp_get_slider_options_for_front')) {
                return true;
            }

            return !empty(em_wp_get_slider_options_for_front()['enabled']);
        case 'stream':
            if (!function_exists('em_wp_stream_get_options')) {
                return true;
            }

            return !empty(em_wp_stream_get_options()['enabled']);
        case 'social':
        case 'video':
        case 'release':
        case 'cta':
            return true;
        default:
            return false;
    }
}

/**
 * Liste ordonnée des ancres # navigables sur la landing (sections visibles).
 *
 * @return string[]
 */
function em_wp_landing_build_nav_anchors(): array
{
    $anchors = [];

    if (!function_exists('em_wp_get_site_rubrique_middle_order')) {
        return ['hero', 'stream', 'social', 'footer'];
    }

    $segments = em_wp_landing_parse_middle_segments(em_wp_get_site_rubrique_middle_order());

    foreach ($segments as $segment) {
        if (($segment['type'] ?? '') === 'hero-slider-pair') {
            $order = is_array($segment['order'] ?? null) ? $segment['order'] : ['hero', 'slider'];

            if (($order[0] ?? '') === 'hero') {
                if (em_wp_landing_module_is_enabled('hero')) {
                    $anchors[] = 'hero';
                }
                continue;
            }

            foreach ($order as $slug) {
                $slug = sanitize_key((string) $slug);
                if ($slug === '' || !em_wp_landing_module_is_enabled($slug)) {
                    continue;
                }

                $anchor = em_wp_landing_section_anchor_id($slug);
                if ($anchor !== '' && !in_array($anchor, $anchors, true)) {
                    $anchors[] = $anchor;
                }
            }

            continue;
        }

        $slug = sanitize_key((string) ($segment['slug'] ?? ''));
        if ($slug === '' || !em_wp_landing_module_is_enabled($slug)) {
            continue;
        }

        $anchor = em_wp_landing_section_anchor_id($slug);
        if ($anchor !== '' && !in_array($anchor, $anchors, true)) {
            $anchors[] = $anchor;
        }
    }

    if (function_exists('em_wp_get_site_rubrique_visibility') && em_wp_get_site_rubrique_visibility('footer')) {
        $anchors[] = 'footer';
    }

    return $anchors;
}

/**
 * Liens prev/next (#…) pour une rubrique du milieu.
 *
 * @return array{prev:string,next:string}
 */
function em_wp_landing_get_section_nav_hrefs(string $module_slug): array
{
    $anchors = em_wp_landing_build_nav_anchors();
    $current = em_wp_landing_section_anchor_id($module_slug);
    $index = array_search($current, $anchors, true);

    return [
        'prev' => ($index !== false && $index > 0) ? '#' . $anchors[$index - 1] : '',
        'next' => ($index !== false && $index < count($anchors) - 1) ? '#' . $anchors[$index + 1] : '',
    ];
}

/**
 * Métadonnées visuelles d'une rubrique (label, couleur).
 *
 * @return array{label:string,accent_color:string}
 */
function em_wp_landing_rubrique_stub_meta(string $module_slug): array
{
    if (function_exists('em_wp_admin_site_rubrique_definitions')) {
        $definitions = em_wp_admin_site_rubrique_definitions();

        if (isset($definitions[$module_slug])) {
            return [
                'label'        => (string) ($definitions[$module_slug]['label'] ?? strtoupper($module_slug)),
                'accent_color' => (string) ($definitions[$module_slug]['accent_color'] ?? '#646970'),
            ];
        }
    }

    $fallbacks = [
        'stream'  => ['label' => 'STREAM', 'accent_color' => '#7c3aed'],
        'social'  => ['label' => 'SOCIAL', 'accent_color' => '#db2777'],
        'video'   => ['label' => 'VIDEOS', 'accent_color' => '#ca8a04'],
        'release' => ['label' => 'RELEASES', 'accent_color' => '#b8956a'],
        'cta'     => ['label' => 'CTA', 'accent_color' => '#0d9488'],
        'footer'  => ['label' => 'FOOTER', 'accent_color' => '#100421'],
    ];

    return $fallbacks[$module_slug] ?? [
        'label'        => strtoupper($module_slug),
        'accent_color' => '#646970',
    ];
}

/**
 * Placeholder visuel pour une rubrique pas encore implémentée côté front.
 */
function em_wp_render_landing_section_placeholder(string $module_slug): void
{
    $meta = em_wp_landing_rubrique_stub_meta($module_slug);
    $anchor_id = em_wp_landing_section_anchor_id($module_slug);
    $tag = $module_slug === 'footer' ? 'footer' : 'section';
    ?>
    <<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        id="<?php echo esc_attr($anchor_id); ?>"
        class="em-landing-section em-landing-section--placeholder em-landing-section--<?php echo esc_attr(sanitize_html_class($module_slug)); ?>"
        data-em-rubrique="<?php echo esc_attr($module_slug); ?>"
        style="--em-rubrique-accent: <?php echo esc_attr($meta['accent_color']); ?>"
        aria-label="<?php echo esc_attr($meta['label']); ?>"
    >
        <span class="em-landing-section__label"><?php echo esc_html($meta['label']); ?></span>
    </<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
    <?php
}

/**
 * Affiche une rubrique du milieu.
 */
function em_wp_render_landing_module(string $module_slug): void
{
    switch ($module_slug) {
        case 'hero':
            em_wp_render_hero(['embed_slider' => false]);
            break;

        case 'slider':
            em_wp_render_slider_section(['wrapper' => 'section']);
            break;

        case 'stream':
            if (function_exists('em_wp_render_stream')) {
                em_wp_render_stream();
            }
            break;

        case 'social':
        case 'video':
        case 'release':
        case 'cta':
            em_wp_render_landing_section_placeholder($module_slug);
            break;
    }
}

/**
 * Affiche un segment (paire ou module seul).
 *
 * @param array{type:string,slug?:string,order?:string[]} $segment
 */
function em_wp_render_landing_segment(array $segment): void
{
    if (($segment['type'] ?? '') === 'hero-slider-pair') {
        $order = is_array($segment['order'] ?? null) ? $segment['order'] : ['hero', 'slider'];

        if (($order[0] ?? '') === 'hero') {
            em_wp_render_hero(['embed_slider' => true]);
            return;
        }

        get_template_part('template-parts/sections/landing/hero-slider-pair', null, [
            'order' => $order,
        ]);

        return;
    }

    em_wp_render_landing_module((string) ($segment['slug'] ?? ''));
}

/**
 * Affiche toutes les sections du milieu selon l'ordre admin.
 */
function em_wp_render_landing_middle_sections(): void
{
    if (!function_exists('em_wp_get_site_rubrique_middle_order')) {
        em_wp_render_hero(['embed_slider' => true]);

        return;
    }

    $segments = em_wp_landing_parse_middle_segments(em_wp_get_site_rubrique_middle_order());

    foreach ($segments as $segment) {
        em_wp_render_landing_segment($segment);
    }
}

/**
 * Affiche le footer landing (placeholder ou futur module).
 */
function em_wp_render_landing_footer(): void
{
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('footer')) {
        return;
    }

    em_wp_render_landing_section_placeholder('footer');
}

/**
 * Affiche le contenu principal de la page d'accueil.
 */
function em_wp_render_landing_page(): void
{
    em_wp_render_landing_middle_sections();
    em_wp_render_landing_footer();
}
