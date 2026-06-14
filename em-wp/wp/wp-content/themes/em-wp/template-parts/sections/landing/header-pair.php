<?php
/**
 * Paire Hero + Slider dans HEADER (layout hero_left ou slider_left).
 *
 * @package em-wp
 */

$hero_slug = sanitize_key((string) ($args['hero_slug'] ?? ''));
$slider_slug = sanitize_key((string) ($args['slider_slug'] ?? ''));
$layout = (string) ($args['layout'] ?? 'hero_left');
$slider_first = $layout === 'slider_left';

$render_slider = static function () use ($slider_slug): void {
    if ($slider_slug === '' || !function_exists('em_wp_render_slider_section')) {
        return;
    }

    em_wp_render_slider_section([
        'catalog_slug'          => $slider_slug,
        'wrapper'               => 'column',
        'skip_visibility_check' => true,
    ]);
};

$render_hero = static function () use ($hero_slug): void {
    if ($hero_slug === '' || !function_exists('em_wp_render_hero')) {
        return;
    }

    echo '<div class="em-landing-hero-row__column em-landing-hero-row__column--hero">';
    em_wp_render_hero([
        'catalog_slug' => $hero_slug,
        'embed_slider' => false,
        'layout'       => 'pair-column',
        'in_header'    => true,
    ]);
    echo '</div>';
};
?>
<section class="em-landing-hero-row em-landing-header-pair">
    <div class="em-landing-hero-row__inner<?php echo $slider_first ? ' is-slider-first' : ''; ?>">
        <?php
        if ($slider_first) {
            $render_slider();
            $render_hero();
        } else {
            $render_hero();
            $render_slider();
        }
        ?>
    </div>
</section>
