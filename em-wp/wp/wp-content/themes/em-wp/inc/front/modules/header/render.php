<?php
/**
 * Rendu front rubrique HEADER (Hero et/ou Slider catalogue).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Attributs style inline pour le conteneur HEADER front.
 *
 * @param array<string, mixed> $header
 */
function em_wp_header_front_inline_style_attr(array $header): string
{
    $styles = [];
    $bg = trim((string) ($header['background_color'] ?? ''));
    $text = trim((string) ($header['text_color'] ?? ''));

    if ($bg !== '') {
        $color = sanitize_hex_color($bg);
        if ($color !== null && $color !== false) {
            $styles[] = '--em-header-bg:' . $color . ';';
        }
    }

    if ($text !== '') {
        $color = sanitize_hex_color($text);
        if ($color !== null && $color !== false) {
            $styles[] = '--em-header-text:' . $color . ';';
        }
    }

    return $styles !== [] ? implode('', $styles) : '';
}

/**
 * Indique si l'image de fond HEADER est affichée.
 *
 * @param array<string, mixed> $header
 */
function em_wp_header_background_image_is_visible(array $header): bool
{
    $url = trim((string) ($header['background_image'] ?? ''));

    return $url !== '' && empty($header['background_image_hidden']);
}

/**
 * Ouvre le conteneur HEADER (fond pleine largeur).
 *
 * @param array<string, mixed> $header
 */
function em_wp_render_header_open(array $header): void
{
    $classes = ['em-landing-header'];
    $bg_image = trim((string) ($header['background_image'] ?? ''));

    if ($bg_image !== '' && em_wp_header_background_image_is_visible($header)) {
        $classes[] = 'has-bg';
    }

    $style_attr = em_wp_header_front_inline_style_attr($header);
    ?>
    <section id="header" class="<?php echo esc_attr(implode(' ', $classes)); ?>"<?php echo $style_attr !== '' ? ' style="' . esc_attr($style_attr) . '"' : ''; ?>>
        <?php if ($bg_image !== '' && em_wp_header_background_image_is_visible($header)) { ?>
            <img class="em-landing-header__bg" src="<?php echo esc_url($bg_image); ?>" alt="" loading="eager" decoding="async" aria-hidden="true">
        <?php } ?>
        <div class="em-landing-header__grain" aria-hidden="true"></div>
        <div class="em-landing-header__inner">
    <?php
}

/**
 * Ferme le conteneur HEADER.
 */
function em_wp_render_header_close(): void
{
    ?>
        </div>
    </section>
    <?php
}

/**
 * Rendu paire Hero + Slider dans HEADER (layout hero_left ou slider_left).
 */
function em_wp_render_header_pair(string $hero_slug, string $slider_slug, string $layout): void
{
    $hero_slug = sanitize_key($hero_slug);
    $slider_slug = sanitize_key($slider_slug);
    $slider_first = $layout === 'slider_left';

    ?>
    <section class="em-landing-hero-row em-landing-header-pair">
        <div class="em-landing-hero-row__inner<?php echo $slider_first ? ' is-slider-first' : ''; ?>">
            <?php
            if ($slider_first) {
                if ($slider_slug !== '' && function_exists('em_wp_render_slider_section')) {
                    em_wp_render_slider_section([
                        'catalog_slug'          => $slider_slug,
                        'wrapper'               => 'column',
                        'skip_visibility_check' => true,
                    ]);
                }

                if ($hero_slug !== '' && function_exists('em_wp_render_hero')) {
                    echo '<div class="em-landing-hero-row__column em-landing-hero-row__column--hero">';
                    em_wp_render_hero([
                        'catalog_slug' => $hero_slug,
                        'embed_slider' => false,
                        'layout'       => 'pair-column',
                        'in_header'    => true,
                    ]);
                    echo '</div>';
                }
            } else {
                if ($hero_slug !== '' && function_exists('em_wp_render_hero')) {
                    echo '<div class="em-landing-hero-row__column em-landing-hero-row__column--hero">';
                    em_wp_render_hero([
                        'catalog_slug' => $hero_slug,
                        'embed_slider' => false,
                        'layout'       => 'pair-column',
                        'in_header'    => true,
                    ]);
                    echo '</div>';
                }

                if ($slider_slug !== '' && function_exists('em_wp_render_slider_section')) {
                    em_wp_render_slider_section([
                        'catalog_slug'          => $slider_slug,
                        'wrapper'               => 'column',
                        'skip_visibility_check' => true,
                    ]);
                }
            }
            ?>
        </div>
    </section>
    <?php
}

/**
 * Affiche la rubrique HEADER selon le template live.
 */
function em_wp_render_header(): void
{
    if (!function_exists('em_wp_header_get_options_for_front')) {
        return;
    }

    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('header')) {
        return;
    }

    $header = em_wp_header_get_options_for_front();

    if (empty($header['enabled'])) {
        return;
    }

    // hero_slug / slider_slug sont déjà résolus (repli Default inclus) en amont
    // par em_wp_header_get_options(), comme les autres rubriques.
    $hero_slug = sanitize_key((string) ($header['hero_slug'] ?? ''));
    $slider_slug = sanitize_key((string) ($header['slider_slug'] ?? ''));
    $layout = (string) ($header['layout'] ?? 'hero_left');

    if ($hero_slug === '' && $slider_slug === '') {
        return;
    }

    em_wp_render_header_open($header);

    if ($hero_slug !== '' && $slider_slug === '') {
        em_wp_render_hero([
            'catalog_slug' => $hero_slug,
            'embed_slider' => false,
            'in_header'    => true,
        ]);
    } elseif ($slider_slug !== '' && $hero_slug === '') {
        em_wp_render_slider_section([
            'catalog_slug'          => $slider_slug,
            'wrapper'               => 'section',
            'skip_visibility_check' => true,
        ]);
    } elseif ($hero_slug !== '' && $slider_slug !== '') {
        em_wp_render_header_pair($hero_slug, $slider_slug, $layout);
    }

    em_wp_render_header_close();
}
