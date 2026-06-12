<?php
/**
 * Gabarit footer dédié.
 *
 * @package em-wp
 */
?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <p>&copy; <?php echo esc_html(gmdate('Y')); ?> <?php bloginfo('name'); ?>. Theme em-wp local.</p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
