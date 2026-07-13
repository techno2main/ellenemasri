<?php
/**
 * Wireframe de disposition Hero / Slider (admin HEADER).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Libellé court d'une zone du wireframe disposition.
 */
function em_site_header_wireframe_part_label(string $part, string $name = ''): string
{
    $name = trim($name);

    if ($part === 'hero') {
        return $name !== ''
            ? sprintf(
                /* translators: %s: hero catalog entry short label */
                __('HERO %s', 'em-site'),
                $name
            )
            : __('HERO', 'em-site');
    }

    return $name !== ''
        ? sprintf(
            /* translators: %s: slider catalog entry short label */
            __('SLIDE %s', 'em-site'),
            $name
        )
        : __('SLIDE', 'em-site');
}

/**
 * Libellé de la disposition HEADER courante.
 */
function em_site_header_layout_hint(string $layout, string $hero_name = '', string $slider_name = ''): string
{
    $hero_name = trim($hero_name);
    $slider_name = trim($slider_name);

    if ($layout === 'slider_left') {
        if ($hero_name !== '' && $slider_name !== '') {
            return sprintf(
                /* translators: 1: slider name, 2: hero name */
                __('%1$s à gauche, %2$s à droite', 'em-site'),
                $slider_name,
                $hero_name
            );
        }

        return __('Slider à gauche, Hero à droite', 'em-site');
    }

    if ($hero_name !== '' && $slider_name !== '') {
        return sprintf(
            /* translators: 1: hero name, 2: slider name */
            __('%1$s à gauche, %2$s à droite', 'em-site'),
            $hero_name,
            $slider_name
        );
    }

    return __('Hero à gauche, Slider à droite', 'em-site');
}

/**
 * Sélecteur visuel hero_left / slider_left (mini wireframe + inversion).
 */
function em_site_header_render_layout_switcher(
    string $input_name,
    string $layout,
    string $hero_name = '',
    string $slider_name = '',
    string $hero_hint_name = '',
    string $slider_hint_name = ''
): void {
    $layout = $layout === 'slider_left' ? 'slider_left' : 'hero_left';
    $hero_name = trim($hero_name);
    $slider_name = trim($slider_name);
    $hero_hint_name = trim($hero_hint_name !== '' ? $hero_hint_name : $hero_name);
    $slider_hint_name = trim($slider_hint_name !== '' ? $slider_hint_name : $slider_name);
    ?>
    <div class="em-site-header-admin__field em-site-header-admin__field--layout">
        <span class="em-site-header-admin__layout-label"><?php esc_html_e('Disposition (Hero + Slider)', 'em-site'); ?></span>

        <div class="em-site-header-admin__layout-control">
            <div
                class="em-site-header-admin__layout-preview"
                data-header-layout="<?php echo esc_attr($layout); ?>"
                data-hint-hero_left="<?php echo esc_attr(em_site_header_layout_hint('hero_left', $hero_hint_name, $slider_hint_name)); ?>"
                data-hint-slider_left="<?php echo esc_attr(em_site_header_layout_hint('slider_left', $hero_hint_name, $slider_hint_name)); ?>"
            >
                <div class="em-site-header-admin__layout-wireframe" aria-hidden="true">
                    <span class="em-site-header-admin__layout-part is-hero<?php echo $hero_name === '' ? ' is-empty' : ''; ?>">
                        <span class="em-site-header-admin__layout-part-label" data-layout-part="hero"><?php echo esc_html(em_site_header_wireframe_part_label('hero', $hero_name)); ?></span>
                    </span>
                    <span class="em-site-header-admin__layout-part is-slide<?php echo $slider_name === '' ? ' is-empty' : ''; ?>">
                        <span class="em-site-header-admin__layout-part-label" data-layout-part="slider"><?php echo esc_html(em_site_header_wireframe_part_label('slider', $slider_name)); ?></span>
                        <span class="em-site-header-admin__layout-slide-hints">
                            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                        </span>
                    </span>
                </div>

                <button
                    type="button"
                    class="em-site-header-admin__layout-swap"
                    aria-label="<?php esc_attr_e('Inverser Hero et Slider', 'em-site'); ?>"
                >
                    <i class="fa-solid fa-right-left" aria-hidden="true"></i>
                </button>
            </div>

            <p class="em-site-header-admin__layout-hint description"><?php echo esc_html(em_site_header_layout_hint($layout, $hero_hint_name, $slider_hint_name)); ?></p>

            <input
                type="hidden"
                class="em-site-header-admin__layout-input"
                name="<?php echo esc_attr($input_name); ?>"
                value="<?php echo esc_attr($layout); ?>"
            >
        </div>
    </div>
    <?php
}
