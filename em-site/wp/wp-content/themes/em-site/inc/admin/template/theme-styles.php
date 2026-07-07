<?php
/**
 * Styles dynamiques template (menu admin + variables CSS).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Injecte les variables CSS et le thème menu selon le template en édition.
 */
function em_site_admin_template_theme_styles(): void
{
    if (!function_exists('em_site_admin_is_em_site_screen') || !em_site_admin_is_em_site_screen()) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    $editing_color = em_site_get_template_color(em_site_get_editing_template_slug());
    $active_color = em_site_get_template_color(em_site_get_active_template_slug());
    $editing_soft = em_site_template_color_rgba($editing_color, 0.28);
    $active_soft = em_site_template_color_rgba($active_color, 0.28);
    $editing_dark = em_site_template_color_darken($editing_color, 0.55);
    $active_dark = em_site_template_color_darken($active_color, 0.72);
    ?>
    <style id="em-site-template-theme">
        body.em-site-has-template-banner {
            --em-template-editing-color: <?php echo esc_attr($editing_color); ?>;
            --em-template-editing-color-soft: <?php echo esc_attr($editing_soft); ?>;
            --em-template-editing-color-dark: <?php echo esc_attr($editing_dark); ?>;
            --em-template-active-color: <?php echo esc_attr($active_color); ?>;
            --em-template-active-color-soft: <?php echo esc_attr($active_soft); ?>;
            --em-template-active-color-dark: <?php echo esc_attr($active_dark); ?>;
        }

        body.em-site-has-template-banner #adminmenuback,
        body.em-site-has-template-banner #adminmenuwrap {
            background: var(--em-template-editing-color);
        }

        body.em-site-has-template-banner #adminmenu {
            background: transparent;
        }

        body.em-site-has-template-banner #adminmenu a.menu-top,
        body.em-site-has-template-banner #adminmenu .wp-submenu a {
            color: rgba(255, 255, 255, 0.9);
        }

        body.em-site-has-template-banner #adminmenu li.menu-top:hover,
        body.em-site-has-template-banner #adminmenu li.opensub > a.menu-top,
        body.em-site-has-template-banner #adminmenu li > a.menu-top:focus {
            background-color: var(--em-template-editing-color-soft);
            color: #ffffff;
        }

        body.em-site-has-template-banner #adminmenu li.current a.menu-top,
        body.em-site-has-template-banner #adminmenu .wp-has-current-submenu a.menu-top,
        body.em-site-has-template-banner #adminmenu .wp-has-current-submenu .wp-submenu .current a,
        body.em-site-has-template-banner #adminmenu li.em-site-menu-submenu-current > a.menu-top,
        body.em-site-has-template-banner #adminmenu li.em-site-menu-rubrique-current > a.menu-top,
        body.em-site-has-template-banner #adminmenu li.em-site-menu-template-editing > a.menu-top,
        body.em-site-has-template-banner #adminmenu li.em-site-menu-accordion-child.current > a.menu-top {
            background: rgba(255, 255, 255, 0.24);
            color: #ffffff;
        }

        body.em-site-has-template-banner #adminmenu li.em-site-menu-submenu-current:hover > a.menu-top,
        body.em-site-has-template-banner #adminmenu li.em-site-menu-rubrique-current:hover > a.menu-top,
        body.em-site-has-template-banner #adminmenu li.em-site-menu-template-editing:hover > a.menu-top,
        body.em-site-has-template-banner #adminmenu li.current:hover > a.menu-top,
        body.em-site-has-template-banner #adminmenu li.em-site-menu-accordion-child.current:hover > a.menu-top {
            background: rgba(255, 255, 255, 0.3);
            color: #ffffff;
        }

        body.em-site-has-template-banner #adminmenu .wp-submenu {
            background: var(--em-template-editing-color-dark);
        }

        body.em-site-has-template-banner #adminmenu .wp-submenu a:hover,
        body.em-site-has-template-banner #adminmenu .wp-submenu a:focus,
        body.em-site-has-template-banner #adminmenu .wp-has-current-submenu .wp-submenu.sub-open,
        body.em-site-has-template-banner #adminmenu .wp-has-current-submenu.opensub .wp-submenu {
            background: var(--em-template-editing-color-dark);
            color: #ffffff;
        }

        body.em-site-has-template-banner #adminmenu .wp-submenu a:hover,
        body.em-site-has-template-banner #adminmenu .wp-submenu a:focus {
            background: var(--em-template-editing-color-soft);
        }

        body.em-site-has-template-banner #adminmenu li.wp-menu-separator .separator {
            background: rgba(255, 255, 255, 0.38);
            opacity: 1;
        }

        body.em-site-has-template-banner #adminmenu li.em-site-menu-section-label .wp-menu-name,
        body.em-site-has-template-banner #adminmenu li.em-site-menu-wp-settings-label .wp-menu-name {
            color: rgba(255, 255, 255, 0.72);
        }

        body.em-site-has-template-banner #adminmenu .wp-menu-image::before {
            color: rgba(255, 255, 255, 0.82);
        }

        body.em-site-has-template-banner #adminmenu li.current .wp-menu-image::before,
        body.em-site-has-template-banner #adminmenu li.wp-has-current-submenu .wp-menu-image::before {
            color: #ffffff;
        }
    </style>
    <?php
}
add_action('admin_head', 'em_site_admin_template_theme_styles', 25);
