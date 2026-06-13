<?php
/**
 * Paire HEROS / SLIDERS (slider à gauche).
 *
 * @package em-wp
 */

$order = is_array($args['order'] ?? null) ? $args['order'] : ['slider', 'hero'];
$slider_first = ($order[0] ?? '') === 'slider';

if (!$slider_first) {
    if (function_exists('em_wp_render_hero')) {
        em_wp_render_hero(['embed_slider' => true]);
    }

    return;
}
?>
<section class="em-landing-hero-row">
    <div class="em-landing-hero-row__inner is-slider-first">
        <?php
        if (function_exists('em_wp_render_slider_section')) {
            em_wp_render_slider_section(['wrapper' => 'column']);
        }
        ?>
        <div class="em-landing-hero-row__column em-landing-hero-row__column--hero">
            <?php
            if (function_exists('em_wp_render_hero')) {
                em_wp_render_hero([
                    'embed_slider' => false,
                    'layout'       => 'pair-column',
                ]);
            }
            ?>
        </div>
    </div>
</section>
