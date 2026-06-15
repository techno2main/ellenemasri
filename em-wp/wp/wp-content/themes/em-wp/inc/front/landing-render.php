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
 * Ancres HTML d'une rubrique sans module front dédié (navigation #stream, etc.).
 */
function em_wp_landing_section_anchor_id(string $module_slug): string
{
    $anchors = [
        'stream'  => 'stream',
        'social'  => 'social',
        'video'   => 'video',
        'release' => 'release',
        'cta'     => 'cta',
        'contacts' => 'contact',
        'header'  => 'hero',
        'footer'  => 'footer',
    ];

    return $anchors[$module_slug] ?? $module_slug;
}

/**
 * Indique si un module milieu est activé côté front.
 */
function em_wp_landing_module_is_enabled(string $module_slug): bool
{
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility($module_slug)) {
        return false;
    }

    switch ($module_slug) {
        case 'header':
            if (!function_exists('em_wp_header_get_options_for_front')) {
                return true;
            }

            return !empty(em_wp_header_get_options_for_front()['enabled']);
        case 'stream':
            if (!function_exists('em_wp_stream_get_options_for_front')) {
                return !empty(em_wp_stream_get_options()['enabled']);
            }

            return !empty(em_wp_stream_get_options_for_front()['enabled']);
        case 'social':
            if (!function_exists('em_wp_social_get_options_for_front')) {
                return !empty(em_wp_social_get_options()['enabled']);
            }

            return !empty(em_wp_social_get_options_for_front()['enabled']);
        case 'video':
            if (!function_exists('em_wp_video_get_options_for_front')) {
                return !empty(em_wp_video_get_options()['enabled']);
            }

            return !empty(em_wp_video_get_options_for_front()['enabled']);
        case 'release':
            if (!function_exists('em_wp_release_get_options_for_front')) {
                return !empty(em_wp_release_get_options()['enabled']);
            }

            return !empty(em_wp_release_get_options_for_front()['enabled']);
        case 'cta':
            if (!function_exists('em_wp_cta_get_options_for_front')) {
                return !empty(em_wp_cta_get_options()['enabled']);
            }

            return !empty(em_wp_cta_get_options_for_front()['enabled']);
        default:
            if (function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
                if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility($module_slug)) {
                    return false;
                }

                if (function_exists('em_wp_custom_catalog_rubrique_get_options_for_front')) {
                    return !empty(em_wp_custom_catalog_rubrique_get_options_for_front($module_slug)['enabled']);
                }
            }

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

    if (!function_exists('em_wp_front_get_rubrique_middle_order')) {
        return ['hero', 'stream', 'social', 'footer'];
    }

    foreach (em_wp_front_get_rubrique_middle_order() as $module_slug) {
        $module_slug = sanitize_key((string) $module_slug);
        if ($module_slug === '' || !em_wp_landing_module_is_enabled($module_slug)) {
            continue;
        }

        $anchor = em_wp_landing_section_anchor_id($module_slug);
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
        'contacts' => ['label' => 'CONTACT', 'accent_color' => '#64748b'],
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
        case 'header':
            if (function_exists('em_wp_render_header')) {
                em_wp_render_header();
            }
            break;

        case 'stream':
            if (function_exists('em_wp_render_stream')) {
                em_wp_render_stream();
            }
            break;

        case 'social':
            if (function_exists('em_wp_render_social')) {
                em_wp_render_social();
            }
            break;

        case 'video':
            if (function_exists('em_wp_render_video')) {
                em_wp_render_video();
            }
            break;

        case 'release':
            if (function_exists('em_wp_render_release')) {
                em_wp_render_release();
            }
            break;

        case 'cta':
            if (function_exists('em_wp_render_cta')) {
                em_wp_render_cta();
            }
            break;

        default:
            if (function_exists('em_wp_custom_catalog_is_module')
                && em_wp_custom_catalog_is_module($module_slug)
                && function_exists('em_wp_render_custom_catalog_rubrique')) {
                em_wp_render_custom_catalog_rubrique($module_slug);
            }
            break;
    }
}

/**
 * Affiche toutes les sections du milieu selon l'ordre admin.
 */
function em_wp_render_landing_middle_sections(): void
{
    if (!function_exists('em_wp_front_get_rubrique_middle_order')) {
        if (function_exists('em_wp_render_header')) {
            em_wp_render_header();
        }

        return;
    }

    foreach (em_wp_front_get_rubrique_middle_order() as $module_slug) {
        em_wp_render_landing_module((string) $module_slug);
    }
}

/**
 * Affiche le contenu principal de la page d'accueil.
 */
function em_wp_render_landing_page(): void
{
    em_wp_render_landing_middle_sections();

    if (function_exists('em_wp_render_landing_footer')) {
        em_wp_render_landing_footer();
    }
}
