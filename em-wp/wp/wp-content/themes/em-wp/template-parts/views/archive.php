<?php
/**
 * Vue des archives.
 *
 * @package em-wp
 */
?>
<main class="site-main">
    <section class="content-block">
        <h1><?php the_archive_title(); ?></h1>
        <p><?php the_archive_description(); ?></p>

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
            <p>Aucun contenu dans cette archive.</p>
        <?php endif; ?>
    </section>
</main>
