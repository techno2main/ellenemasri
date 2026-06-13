<?php
/**
 * Vue dédiée de la page d'accueil.
 *
 * @package em-wp
 */
?>
<main class="site-main site-main--front">
    <?php
    if (function_exists('em_wp_render_landing_page')) {
        em_wp_render_landing_page();
    } elseif (function_exists('em_wp_render_hero')) {
        em_wp_render_hero(['embed_slider' => true]);
    }
    ?>
</main>
