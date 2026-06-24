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
        get_template_part('template-parts/sections/landing/header-pair', null, [
            'hero_slug'   => $hero_slug,
            'slider_slug' => $slider_slug,
            'layout'      => $layout,
        ]);
    }

    em_wp_render_header_close();
}
