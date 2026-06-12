<?php
/**
 * Vue dédiée de la page d'accueil.
 *
 * @package em-wp
 */
?>
<main class="site-main site-main--front">
    <?php
    if (function_exists('em_wp_render_hero')) {
        em_wp_render_hero();
    }
    ?>
</main>
