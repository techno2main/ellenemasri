<?php

/**

 * Front Page Template - Mayami Landing Page

 * 

 * @package Mayami

 */



// Prevent direct access

if (!defined('ABSPATH')) {

    exit;

}

?>

<!DOCTYPE html>

<html <?php language_attributes(); ?>>

<head>

    <meta charset="<?php bloginfo('charset'); ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php wp_head(); ?>

</head>

<body id="page-top" <?php body_class(); ?>>

<?php wp_body_open(); ?>



<?php if (mayami_is_visual_links_preview_request()): ?>

    <div class="mayami-preview-banner">

        Prévisualisation privée Visual Links active. Seul votre compte admin voit ce brouillon.

    </div>

<?php endif; ?>



<main class="relative overflow-x-clip">
    <?php if (function_exists('ellene_is_mayami_landing_request') && ellene_is_mayami_landing_request()): ?>
        <?php
        if (function_exists('ellene_render_layout')) {
            ellene_render_layout();
        } else {
            get_template_part('template-parts/sections/top-bar');
            get_template_part('template-parts/sections/hero');
            get_template_part('template-parts/sections/stream');
            get_template_part('template-parts/sections/social');
            get_template_part('template-parts/sections/video');
            get_template_part('template-parts/sections/release-info');
            get_template_part('template-parts/sections/cta');
            get_template_part('template-parts/sections/footer-section');
            get_template_part('template-parts/sections/sticky-bar');
        }
        ?>
    <?php else: ?>
        <?php get_template_part('template-parts/home/top-bar'); ?>
        <?php get_template_part('template-parts/home/hero'); ?>
        <?php get_template_part('template-parts/home/contact'); ?>
        <?php get_template_part('template-parts/home/footer'); ?>
    <?php endif; ?>

</main>



<?php wp_footer(); ?>

</body>

</html>

