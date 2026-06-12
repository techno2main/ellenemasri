<?php
/**
 * Gabarit header dédié.
 *
 * @package em-wp
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
if (function_exists('em_wp_render_top_bar')) {
    em_wp_render_top_bar();
}
?>
<?php if (!is_front_page()) { ?>
    <header class="site-header">
        <div class="site-header__inner">
            <a class="site-header__brand" href="<?php echo esc_url(home_url('/')); ?>">
                <?php bloginfo('name'); ?>
            </a>
            <nav class="site-header__nav" aria-label="Navigation principale">
                <?php
                if (has_nav_menu('primary')) {
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'primary-menu',
                        'fallback_cb'    => false,
                    ]);
                } else {
                    wp_page_menu([
                        'menu_class' => 'primary-menu',
                    ]);
                }
                ?>
            </nav>
        </div>
    </header>
<?php } ?>
