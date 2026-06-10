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
    return 'https://deretourdufutur.fr/assets/img/Moonwalk.gif';

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
    body.login {
        background: #f8d24a !important;
    }

    body.login #login {
        width: 320px !important;
    }

    body.login #loginform,
    body.login form#loginform,
    body.login #lostpasswordform,
    body.login form#lostpasswordform,
    body.login #resetpassform,
    body.login form#resetpassform,
    body.login .login form {
        background: #d94a2d !important;
        border: 1px solid #2f1114 !important;
        box-shadow: none !important;
        margin-top: 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    body.login #login h1,
    body.login .login h1 {
        margin-bottom: 0 !important;
    }

    body.login #nav,
    body.login #backtoblog,
    body.login #nav a,
    body.login #backtoblog a,
    body.login .language-switcher label,
    body.login .language-switcher .dashicons {
        color: #1f2937 !important;
        opacity: 1 !important;
    }

    body.login #nav a:hover,
    body.login #nav a:focus,
    body.login #backtoblog a:hover,
    body.login #backtoblog a:focus {
        color: #111827 !important;
    }

    body.login .language-switcher,
    body.login #language-switcher {
        display: none !important;
    }

    body.login .language-switcher .button,
    body.login .language-switcher .button-secondary,
    body.login #language-switcher input.button {
        background: #ffffff !important;
        border-color: #ffffff !important;
        color: #3858e9 !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }

    body.login .language-switcher .button:hover,
    body.login .language-switcher .button:focus,
    body.login .language-switcher .button-secondary:hover,
    body.login .language-switcher .button-secondary:focus,
    body.login #language-switcher input.button:hover,
    body.login #language-switcher input.button:focus {
        background: #ffffff !important;
        border-color: #ffffff !important;
        color: #3858e9 !important;
    }

    body.login #loginform label,
    body.login #lostpasswordform label,
    body.login #resetpassform label,
    body.login #loginform .forgetmenot label,
    body.login #loginform .user-pass-wrap .wp-pwd .button,
    body.login #loginform .dashicons-visibility,
    body.login #loginform .dashicons-hidden {
        color: #ffffff !important;
    }

    body.login #loginform .input,
    body.login #lostpasswordform .input,
    body.login #resetpassform .input,
    body.login #loginform input[type="text"],
    body.login #lostpasswordform input[type="text"],
    body.login #resetpassform input[type="text"],
    body.login #loginform input[type="password"],
    body.login #resetpassform input[type="password"] {
        background: #ffffff !important;
        color: #000000 !important;
        border-color: #d1d5db !important;
    }

    body.login #loginform .input:focus,
    body.login #lostpasswordform .input:focus,
    body.login #resetpassform .input:focus,
    body.login #loginform input[type="text"]:focus,
    body.login #lostpasswordform input[type="text"]:focus,
    body.login #resetpassform input[type="text"]:focus,
    body.login #loginform input[type="password"]:focus,
    body.login #resetpassform input[type="password"]:focus {
        border-color: #2f1114 !important;
        box-shadow: 0 0 0 1px #2f1114 !important;
        outline: none !important;
    }

    body.login #loginform .wp-pwd .button,
    body.login #loginform .wp-pwd .button.button-secondary {
        background: #2f1114 !important;
        border-color: #2f1114 !important;
        color: #ffffff !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }

    body.login #loginform .wp-pwd .button:hover,
    body.login #loginform .wp-pwd .button:focus,
    body.login #loginform .wp-pwd .button.button-secondary:hover,
    body.login #loginform .wp-pwd .button.button-secondary:focus {
        background: #2f1114 !important;
        border-color: #2f1114 !important;
        color: #ffffff !important;
    }

    body.login.wp-core-ui #loginform .button.button-primary,
    body.login.wp-core-ui #lostpasswordform .button.button-primary,
    body.login.wp-core-ui #resetpassform .button.button-primary,
    body.login .wp-core-ui #loginform .button.button-primary {
        background: #2f1114 !important;
        border-color: #2f1114 !important;
        color: #ffffff !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }

    body.login.wp-core-ui #loginform .button.button-primary:hover,
    body.login.wp-core-ui #loginform .button.button-primary:focus,
    body.login.wp-core-ui #loginform .button.button-primary:active,
    body.login.wp-core-ui #lostpasswordform .button.button-primary:hover,
    body.login.wp-core-ui #lostpasswordform .button.button-primary:focus,
    body.login.wp-core-ui #lostpasswordform .button.button-primary:active,
    body.login.wp-core-ui #resetpassform .button.button-primary:hover,
    body.login.wp-core-ui #resetpassform .button.button-primary:focus,
    body.login.wp-core-ui #resetpassform .button.button-primary:active,
    body.login .wp-core-ui #loginform .button.button-primary:hover,
    body.login .wp-core-ui #loginform .button.button-primary:focus,
    body.login .wp-core-ui #loginform .button.button-primary:active {
        background: #2f1114 !important;
        border-color: #2f1114 !important;
        color: #ffffff !important;
    }

    body.login .message,
    body.login #login_error,
    body.login .notice {
        border-left-color: #2f1114 !important;
    }

    <?php if ($logo_url !== '') : ?>
    .login h1 a {
        background-image: url('<?php echo esc_url($logo_url); ?>') !important;
        background-size: 100% 100% !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        width: 100% !important;
        aspect-ratio: 799 / 410 !important;
        height: auto !important;
        max-height: none !important;
        margin-bottom: 0 !important;
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

            var lostPasswordLink = document.querySelector('#nav a');
            if (lostPasswordLink) {
                lostPasswordLink.textContent = 'Mot de passe oublié ?';
                lostPasswordLink.href = 'https://www.ellenemasri.com/wp/wp-login.php?action=lostpassword';
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

function ellene_wp_customize_login_logo_text($text) {
    return 'Ellene Masri';
}

add_filter('login_headertext', 'ellene_wp_customize_login_logo_text');

function ellene_wp_customize_login_site_link($html) {
    return '<p id="backtoblog"><a href="https://ellenemasri.com/" target="_blank" rel="noopener noreferrer">&larr; Aller sur Ellene Masri</a></p>';
}

add_filter('login_site_html_link', 'ellene_wp_customize_login_site_link');

function ellene_wp_force_logout_redirect_to_admin($redirect_to, $requested_redirect_to, $user) {
    return 'https://www.ellenemasri.com/wp/wp-login.php';
}

add_filter('logout_redirect', 'ellene_wp_force_logout_redirect_to_admin', 20, 3);