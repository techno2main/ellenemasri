<?php
/**
 * Front Page Template - ellene-wp Landing Page
 * 
 * @package ElleneWp
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
    <!-- Sticky Marquee Top -->
    <div class="sticky top-0 z-60">
        <?php get_template_part('template-parts/sections/hero-marquee'); ?>
    </div>
    
    <!-- Hero Section -->
    <?php get_template_part('template-parts/sections/hero'); ?>
    
    <!-- Stream Section -->
    <?php get_template_part('template-parts/sections/stream'); ?>
    
    <!-- Social Section -->
    <?php get_template_part('template-parts/sections/social'); ?>
    
    <!-- Video Section -->
    <?php get_template_part('template-parts/sections/video'); ?>
    
    <!-- Release Info Section -->
    <?php get_template_part('template-parts/sections/release-info'); ?>

    <!-- Visual Links Section -->
    <?php get_template_part('template-parts/sections/visual-links'); ?>
    
    <!-- CTA Section -->
    <?php get_template_part('template-parts/sections/cta'); ?>
    
    <!-- Footer Section -->
    <?php get_template_part('template-parts/sections/footer-section'); ?>
    
    <!-- Sticky Bar Mobile -->
    <?php get_template_part('template-parts/sections/sticky-bar'); ?>
</main>

<?php wp_footer(); ?>
</body>
</html>
