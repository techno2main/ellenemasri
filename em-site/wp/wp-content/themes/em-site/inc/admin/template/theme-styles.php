<?php
/**
 * Styles dynamiques template (menu admin + variables CSS).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Injecte les variables CSS et le thème menu selon le template en édition.
 */
function em_wp_admin_template_theme_styles(): void
{
    if (!function_exists('em_wp_admin_is_em_wp_screen') || !em_wp_admin_is_em_wp_screen()) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    $editing_color = em_wp_get_template_color(em_wp_get_editing_template_slug());
    $active_color = em_wp_get_template_color(em_wp_get_active_template_slug());
    $editing_soft = em_wp_template_color_rgba($editing_color, 0.28);
    $active_soft = em_wp_template_color_rgba($active_color, 0.28);
    $editing_dark = em_wp_template_color_darken($editing_color, 0.55);
    $active_dark = em_wp_template_color_darken($active_color, 0.72);
    ?>
    <style id="em-wp-template-theme">
        body.em-wp-has-template-banner {
            --em-template-editing-color: <?php echo esc_attr($editing_color); ?>;
            --em-template-editing-color-soft: <?php echo esc_attr($editing_soft); ?>;
            --em-template-editing-color-dark: <?php echo esc_attr($editing_dark); ?>;
            --em-template-active-color: <?php echo esc_attr($active_color); ?>;
            --em-template-active-color-soft: <?php echo esc_attr($active_soft); ?>;
            --em-template-active-color-dark: <?php echo esc_attr($active_dark); ?>;
        }

        body.em-wp-has-template-banner #adminmenuback,
        body.em-wp-has-template-banner #adminmenuwrap {
            background: var(--em-template-editing-color);
        }

        body.em-wp-has-template-banner #adminmenu {
            background: transparent;
        }

        body.em-wp-has-template-banner #adminmenu a.menu-top,
        body.em-wp-has-template-banner #adminmenu .wp-submenu a {
            color: rgba(255, 255, 255, 0.9);
        }

        body.em-wp-has-template-banner #adminmenu li.menu-top:hover,
        body.em-wp-has-template-banner #adminmenu li.opensub > a.menu-top,
        body.em-wp-has-template-banner #adminmenu li > a.menu-top:focus {
            background-color: var(--em-template-editing-color-soft);
            color: #ffffff;
        }

        body.em-wp-has-template-banner #adminmenu li.current a.menu-top,
        body.em-wp-has-template-banner #adminmenu .wp-has-current-submenu a.menu-top,
        body.em-wp-has-template-banner #adminmenu .wp-has-current-submenu .wp-submenu .current a,
        body.em-wp-has-template-banner #adminmenu li.em-wp-menu-submenu-current > a.menu-top,
        body.em-wp-has-template-banner #adminmenu li.em-wp-menu-rubrique-current > a.menu-top,
        body.em-wp-has-template-banner #adminmenu li.em-wp-menu-template-editing > a.menu-top,
        body.em-wp-has-template-banner #adminmenu li.em-wp-menu-accordion-child.current > a.menu-top {
            background: rgba(255, 255, 255, 0.24);
            color: #ffffff;
        }

        body.em-wp-has-template-banner #adminmenu li.em-wp-menu-submenu-current:hover > a.menu-top,
        body.em-wp-has-template-banner #adminmenu li.em-wp-menu-rubrique-current:hover > a.menu-top,
        body.em-wp-has-template-banner #adminmenu li.em-wp-menu-template-editing:hover > a.menu-top,
        body.em-wp-has-template-banner #adminmenu li.current:hover > a.menu-top,
        body.em-wp-has-template-banner #adminmenu li.em-wp-menu-accordion-child.current:hover > a.menu-top {
            background: rgba(255, 255, 255, 0.3);
            color: #ffffff;
        }

        body.em-wp-has-template-banner #adminmenu .wp-submenu {
            background: var(--em-template-editing-color-dark);
        }

        body.em-wp-has-template-banner #adminmenu .wp-submenu a:hover,
        body.em-wp-has-template-banner #adminmenu .wp-submenu a:focus,
        body.em-wp-has-template-banner #adminmenu .wp-has-current-submenu .wp-submenu.sub-open,
        body.em-wp-has-template-banner #adminmenu .wp-has-current-submenu.opensub .wp-submenu {
            background: var(--em-template-editing-color-dark);
            color: #ffffff;
        }

        body.em-wp-has-template-banner #adminmenu .wp-submenu a:hover,
        body.em-wp-has-template-banner #adminmenu .wp-submenu a:focus {
            background: var(--em-template-editing-color-soft);
        }

        body.em-wp-has-template-banner #adminmenu li.wp-menu-separator .separator {
            background: rgba(255, 255, 255, 0.38);
            opacity: 1;
        }

        body.em-wp-has-template-banner #adminmenu li.em-wp-menu-section-label .wp-menu-name,
        body.em-wp-has-template-banner #adminmenu li.em-wp-menu-wp-settings-label .wp-menu-name {
            color: rgba(255, 255, 255, 0.72);
        }

        body.em-wp-has-template-banner #adminmenu .wp-menu-image::before {
            color: rgba(255, 255, 255, 0.82);
        }

        body.em-wp-has-template-banner #adminmenu li.current .wp-menu-image::before,
        body.em-wp-has-template-banner #adminmenu li.wp-has-current-submenu .wp-menu-image::before {
            color: #ffffff;
        }
    </style>
    <?php
}
add_action('admin_head', 'em_wp_admin_template_theme_styles', 25);
