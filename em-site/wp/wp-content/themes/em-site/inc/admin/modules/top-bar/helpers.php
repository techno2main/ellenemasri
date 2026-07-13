<?php
/**
 * Helpers visuels Top Bar (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retourne la position visuelle d'un item Top Bar.
 */
function em_site_top_bar_item_position(string $key): string
{
    $positions = [
        'logo'          => 'line_1_left',
        'line_1_center' => 'line_1_center',
        'line_1_right'  => 'line_1_right',
        'baseline'      => 'line_2_left',
        'cta'           => 'line_2_center',
        'stream_icons'  => 'line_2_right',
    ];

    return $positions[$key] ?? '';
}

/**
 * Rendu de l'indicateur visuel 2x3 d'un item Top Bar.
 */
function em_site_top_bar_render_position_indicator(string $position): void
{
    $cells = [
        'line_1_left',
        'line_1_center',
        'line_1_right',
        'line_2_left',
        'line_2_center',
        'line_2_right',
    ];
    ?>
    <span class="em-site-top-bar-position-indicator" aria-hidden="true">
        <?php foreach ($cells as $cell) { ?>
            <span class="em-site-top-bar-position-cell<?php echo $cell === $position ? ' is-active' : ''; ?>"></span>
        <?php } ?>
    </span>
    <?php
}
