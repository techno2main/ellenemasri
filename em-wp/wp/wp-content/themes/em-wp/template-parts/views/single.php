<?php
/**
 * Vue des articles.
 *
 * @package em-wp
 */
?>
<main class="site-main">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article <?php post_class('content-block'); ?>>
            <h1><?php the_title(); ?></h1>
            <p class="post-meta">Publié le <?php echo esc_html(get_the_date()); ?></p>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
        <?php
    endwhile;
    ?>
</main>
