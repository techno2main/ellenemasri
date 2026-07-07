<?php
/**
 * Vue fallback globale.
 *
 * @package em-wp
 */
?>
<main class="site-main">
    <section class="content-block">
        <?php if (have_posts()) : ?>
            <ul class="post-list">
                <?php
                while (have_posts()) :
                    the_post();
                    ?>
                    <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                    <?php
                endwhile;
                ?>
            </ul>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p>Aucun contenu pour le moment.</p>
        <?php endif; ?>
    </section>
</main>
