<?php
/**
 * Gabarit footer dédié.
 *
 * @package em-wp
 */
?>
<?php
if (!is_front_page() && (!function_exists('em_wp_get_site_rubrique_visibility') || em_wp_get_site_rubrique_visibility('footer'))) {
    ?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <p>&copy; <?php echo esc_html(gmdate('Y')); ?> <?php bloginfo('name'); ?>. Theme em-wp local.</p>
    </div>
</footer>
    <?php
}
?>
<?php wp_footer(); ?>
</body>
</html>
