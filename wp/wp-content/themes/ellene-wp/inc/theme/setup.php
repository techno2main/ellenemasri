<?php

/**
 * Theme setup helpers and shared utilities.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_get_landing_option($field_id, $default = '') {
    $primary_options = get_option('ellene-wp_landing_options', array());

    if (is_array($primary_options) && array_key_exists($field_id, $primary_options)) {
        return $primary_options[$field_id];
    }

    $legacy_options = get_option('ellene_wp_options', array());

    if (is_array($legacy_options) && array_key_exists($field_id, $legacy_options)) {
        return $legacy_options[$field_id];
    }

    return $default;
}

function ellene_wp_upload_size_limit($size) {
    $desired_limit = 128 * MB_IN_BYTES;

    return max((int) $size, $desired_limit);
}

add_filter('upload_size_limit', 'ellene_wp_upload_size_limit');

function ellene_wp_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    add_filter('use_block_editor_for_post', '__return_false');

    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}

add_action('after_setup_theme', 'ellene_wp_theme_setup');

function ellene_wp_output_theme_favicon() {
    // Prefer the WordPress Site Icon set in Settings/Customizer.
    if (function_exists('has_site_icon') && has_site_icon()) {
        return;
    }

    $favicon_svg_path = get_template_directory() . '/assets/favicon.svg';
    $favicon_png_32_path = get_template_directory() . '/assets/favicon-32.png';
    $favicon_png_180_path = get_template_directory() . '/assets/favicon-180.png';
    $favicon_svg_url = get_template_directory_uri() . '/assets/favicon.svg';
    $favicon_png_32_url = get_template_directory_uri() . '/assets/favicon-32.png';
    $favicon_png_180_url = get_template_directory_uri() . '/assets/favicon-180.png';

    $has_any_theme_favicon = file_exists($favicon_svg_path) || file_exists($favicon_png_32_path) || file_exists($favicon_png_180_path);

    if (!$has_any_theme_favicon) {
        return;
    }

    if (file_exists($favicon_svg_path)) {
        $favicon_svg_url .= '?v=' . filemtime($favicon_svg_path);
    }

    if (file_exists($favicon_png_32_path)) {
        $favicon_png_32_url .= '?v=' . filemtime($favicon_png_32_path);
    }

    if (file_exists($favicon_png_180_path)) {
        $favicon_png_180_url .= '?v=' . filemtime($favicon_png_180_path);
    }

    if (file_exists($favicon_svg_path)) {
        echo '<link rel="icon" type="image/svg+xml" href="' . esc_url($favicon_svg_url) . '" />' . "\n";
    }

    if (file_exists($favicon_png_32_path)) {
        echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url($favicon_png_32_url) . '" />' . "\n";
        echo '<link rel="shortcut icon" href="' . esc_url($favicon_png_32_url) . '" />' . "\n";
    }

    if (file_exists($favicon_png_180_path)) {
        echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url($favicon_png_180_url) . '" />' . "\n";
    }
}

add_action('wp_head', 'ellene_wp_output_theme_favicon', 1);
add_action('admin_head', 'ellene_wp_output_theme_favicon', 1);
add_action('login_head', 'ellene_wp_output_theme_favicon', 1);

function ellene_wp_get_login_logo_url() {
    if (function_exists('get_site_icon_url')) {
        $site_icon_url = get_site_icon_url(512);
        if (!empty($site_icon_url)) {
            return $site_icon_url;
        }
    }

    $fallback_png_180 = get_template_directory() . '/assets/favicon-180.png';
    $fallback_png_32 = get_template_directory() . '/assets/favicon-32.png';
    $fallback_svg = get_template_directory() . '/assets/favicon.svg';

    if (file_exists($fallback_png_180)) {
        return get_template_directory_uri() . '/assets/favicon-180.png?v=' . filemtime($fallback_png_180);
    }

    if (file_exists($fallback_png_32)) {
        return get_template_directory_uri() . '/assets/favicon-32.png?v=' . filemtime($fallback_png_32);
    }

    if (file_exists($fallback_svg)) {
        return get_template_directory_uri() . '/assets/favicon.svg?v=' . filemtime($fallback_svg);
    }

    return '';
}

function ellene_wp_minimal_customize_login_page() {
    $logo_url = ellene_wp_get_login_logo_url();
    ?>
    <style>
    <?php if ($logo_url !== '') : ?>
    .login h1 a {
        background-image: url('<?php echo esc_url($logo_url); ?>') !important;
        background-size: contain !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        width: 92px !important;
        height: 92px !important;
    }
    <?php endif; ?>
    </style>
    <script>
    (function() {
        function patchLoginLinks() {
            var loginLinks = document.querySelectorAll('#login a');

            loginLinks.forEach(function(link) {
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            });

            var backLink = document.querySelector('#backtoblog a');
            if (backLink) {
                backLink.textContent = '← Aller sur Ellene Masri';
                backLink.href = 'https://ellenemasri.com/';
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', patchLoginLinks, { once: true });
        } else {
            patchLoginLinks();
        }
    })();
    </script>
    <?php
}

add_action('login_enqueue_scripts', 'ellene_wp_minimal_customize_login_page', 20);

function ellene_wp_customize_login_logo_url($url) {
    return 'https://ellenemasri.com/';
}

add_filter('login_headerurl', 'ellene_wp_customize_login_logo_url');

function ellene_wp_customize_login_site_link($html) {
    return '<p id="backtoblog"><a href="https://ellenemasri.com/" target="_blank" rel="noopener noreferrer">&larr; Aller sur Ellene Masri</a></p>';
}

add_filter('login_site_html_link', 'ellene_wp_customize_login_site_link');