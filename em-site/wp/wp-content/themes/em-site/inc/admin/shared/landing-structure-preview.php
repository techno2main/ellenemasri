<?php
/**
 * Aperçu structurel Hero / Slider dans le plan landing admin.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Couleurs hero pour le wireframe plan.
 *
 * @return array{background:string,text:string}
 */
function em_wp_admin_landing_hero_structure_colors(string $catalog_slug): array
{
    unset($catalog_slug);

    if (function_exists('em_wp_header_get_options')) {
        $header = em_wp_header_get_options();
        $bg = trim((string) ($header['background_color'] ?? ''));
        $text = trim((string) ($header['text_color'] ?? ''));

        return [
            'background' => $bg !== '' ? (sanitize_hex_color($bg) ?: '#ff6f00') : '#ff6f00',
            'text'       => $text !== '' ? (sanitize_hex_color($text) ?: '#100421') : '#100421',
        ];
    }

    return ['background' => '#ff6f00', 'text' => '#100421'];
}

/**
 * Couleurs slider pour le wireframe plan.
 *
 * @return array{frame:string,footer:string,footer_text:string}
 */
function em_wp_admin_landing_slider_structure_colors(string $catalog_slug): array
{
    $catalog_slug = sanitize_key($catalog_slug);

    if ($catalog_slug !== '' && function_exists('em_wp_slider_get_options')) {
        $options = em_wp_slider_get_options($catalog_slug);
        $frame = trim((string) ($options['frame_bg_color'] ?? ''));
        $footer = trim((string) ($options['footer_bg_color'] ?? ''));
        $footer_text = trim((string) ($options['footer_text'] ?? ''));

        return [
            'frame'       => $frame !== '' ? (sanitize_hex_color($frame) ?: '#12338f') : '#12338f',
            'footer'      => $footer !== '' ? (sanitize_hex_color($footer) ?: '#f2ebd1') : '#f2ebd1',
            'footer_text' => $footer_text !== '' ? (sanitize_hex_color($footer_text) ?: '#100421') : '#100421',
        ];
    }

    return [
        'frame'       => '#12338f',
        'footer'      => '#f2ebd1',
        'footer_text' => '#100421',
    ];
}

/**
 * Libellé court style (MAYAMI, CLIENT…) depuis slug catalogue.
 */
function em_wp_admin_landing_structure_style_tag(string $type, string $catalog_slug): string
{
    if (function_exists('em_wp_catalog_resolve_style_slug')) {
        $style = sanitize_key(em_wp_catalog_resolve_style_slug($type, $catalog_slug));

        if ($style !== '') {
            return strtoupper($style);
        }
    }

    return strtoupper($type);
}

/**
 * HTML wireframe Hero pour une zone HEADER du plan.
 */
function em_wp_admin_landing_hero_structure_html(string $catalog_slug, string $label = ''): string
{
    $catalog_slug = sanitize_key($catalog_slug);
    $colors = em_wp_admin_landing_hero_structure_colors($catalog_slug);
    $style_tag = em_wp_admin_landing_structure_style_tag('hero', $catalog_slug);
    $label = $label !== '' ? $label : $style_tag;

    ob_start();
    ?>
    <div
        class="em-wp-admin-landing-map__structure em-wp-admin-landing-map__structure--hero"
        style="--em-structure-bg: <?php echo esc_attr($colors['background']); ?>; --em-structure-text: <?php echo esc_attr($colors['text']); ?>"
        aria-hidden="true"
    >
        <div class="em-wp-admin-landing-map__structure-inner">
            <span class="em-wp-admin-landing-map__structure-block is-scroll"></span>
            <span class="em-wp-admin-landing-map__structure-block is-badge"></span>
            <span class="em-wp-admin-landing-map__structure-block is-line is-sm"></span>
            <span class="em-wp-admin-landing-map__structure-block is-logo"></span>
            <span class="em-wp-admin-landing-map__structure-block is-line"></span>
            <span class="em-wp-admin-landing-map__structure-block is-line is-sm"></span>
            <div class="em-wp-admin-landing-map__structure-actions">
                <span class="em-wp-admin-landing-map__structure-block is-btn"></span>
                <span class="em-wp-admin-landing-map__structure-block is-btn"></span>
            </div>
        </div>
        <span class="em-wp-admin-landing-map__structure-tag"><?php echo esc_html($style_tag); ?></span>
    </div>
    <span class="screen-reader-text"><?php echo esc_html($label); ?></span>
    <?php
    return (string) ob_get_clean();
}

/**
 * HTML wireframe Slider pour une zone HEADER du plan.
 */
function em_wp_admin_landing_slider_structure_html(string $catalog_slug, string $label = ''): string
{
    $catalog_slug = sanitize_key($catalog_slug);
    $colors = em_wp_admin_landing_slider_structure_colors($catalog_slug);
    $style_tag = em_wp_admin_landing_structure_style_tag('slider', $catalog_slug);
    $label = $label !== '' ? $label : $style_tag;

    ob_start();
    ?>
    <div
        class="em-wp-admin-landing-map__structure em-wp-admin-landing-map__structure--slider"
        style="--em-structure-frame: <?php echo esc_attr($colors['frame']); ?>; --em-structure-footer: <?php echo esc_attr($colors['footer']); ?>; --em-structure-footer-text: <?php echo esc_attr($colors['footer_text']); ?>"
        aria-hidden="true"
    >
        <span class="em-wp-admin-landing-map__structure-block is-slider-frame">
            <span class="em-wp-admin-landing-map__structure-slider-nav" aria-hidden="true">
                <span class="em-wp-admin-landing-map__structure-slider-arrow is-prev"></span>
                <span class="em-wp-admin-landing-map__structure-slider-arrow is-next"></span>
            </span>
            <span class="em-wp-admin-landing-map__structure-block is-slider-media"></span>
            <span class="em-wp-admin-landing-map__structure-dots">
                <span></span><span></span><span></span>
            </span>
        </span>
        <span class="em-wp-admin-landing-map__structure-block is-slider-footer"></span>
        <span class="em-wp-admin-landing-map__structure-tag"><?php echo esc_html($style_tag); ?></span>
    </div>
    <span class="screen-reader-text"><?php echo esc_html($label); ?></span>
    <?php
    return (string) ob_get_clean();
}
