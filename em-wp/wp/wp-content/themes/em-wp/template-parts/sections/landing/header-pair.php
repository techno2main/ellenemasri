<?php
/**
 * Paire Hero + Slider dans HEADER (slider à gauche).
 *
 * @package em-wp
 */

$hero_slug = sanitize_key((string) ($args['hero_slug'] ?? ''));
$slider_slug = sanitize_key((string) ($args['slider_slug'] ?? ''));
?>
<section class="em-landing-hero-row em-landing-header-pair">
    <div class="em-landing-hero-row__inner is-slider-first">
        <?php
        if ($slider_slug !== '' && function_exists('em_wp_render_slider_section')) {
            em_wp_render_slider_section([
                'catalog_slug'          => $slider_slug,
                'wrapper'               => 'column',
                'skip_visibility_check' => true,
            ]);
        }
        ?>
        <div class="em-landing-hero-row__column em-landing-hero-row__column--hero">
            <?php
            if ($hero_slug !== '' && function_exists('em_wp_render_hero')) {
                em_wp_render_hero([
                    'catalog_slug' => $hero_slug,
                    'embed_slider' => false,
                    'layout'       => 'pair-column',
                    'in_header'    => true,
                ]);
            }
            ?>
        </div>
    </div>
</section>
