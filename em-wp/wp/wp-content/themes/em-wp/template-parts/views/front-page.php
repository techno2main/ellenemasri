<?php
/**
 * Vue dédiée de la page d'accueil.
 *
 * @package em-wp
 */
?>
<main class="site-main">
    <section class="hero">
        <p class="hero__kicker">Environnement local prêt</p>
        <h1>Theme em-wp actif</h1>
        <p>Base fonctionnelle en place pour démarrer la structure front et les modules.</p>
    </section>

    <section class="content-block">
        <h2>Dernières publications</h2>
        <?php
        $recent_posts = new WP_Query([
            'posts_per_page' => 5,
            'post_status'    => 'publish',
        ]);

        if ($recent_posts->have_posts()) :
            echo '<ul class="post-list">';
            while ($recent_posts->have_posts()) :
                $recent_posts->the_post();
                echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
            endwhile;
            echo '</ul>';
            wp_reset_postdata();
        else :
            echo '<p>Aucun article pour le moment.</p>';
        endif;
        ?>
    </section>
</main>
