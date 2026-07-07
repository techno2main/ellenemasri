<?php
/**
 * Page dédiée d'information de connexion (wp-login-off).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Indique si la requête courante cible la page dédiée wp-login-off.
 */
function em_wp_is_login_off_request(): bool
{
    $request_path = wp_parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $target_path = wp_parse_url(
        function_exists('em_wp_ellene_admin_gate_admin_url') ? em_wp_ellene_admin_gate_admin_url() : home_url('/wp-login-off/'),
        PHP_URL_PATH
    );

    $request = trim((string) $request_path, '/');
    $target = trim((string) $target_path, '/');

    return $request !== '' && $request === $target;
}

/**
 * Rend la page dédiée si ellene-admin est verrouillée.
 */
function em_wp_maybe_render_login_off_page(): void
{
    if (!em_wp_is_login_off_request()) {
        return;
    }

    if (!function_exists('em_wp_ellene_admin_gate_is_enabled') || !em_wp_ellene_admin_gate_is_enabled()) {
        wp_safe_redirect(wp_login_url());
        exit;
    }

    if (!is_user_logged_in()) {
        wp_safe_redirect(wp_login_url(em_wp_ellene_admin_gate_admin_url()));
        exit;
    }

    $user = wp_get_current_user();

    if (!($user instanceof WP_User) || strtolower((string) $user->user_login) !== 'ellene-admin') {
        wp_safe_redirect(admin_url());
        exit;
    }

    $logo_url = function_exists('em_wp_get_login_logo_url') ? em_wp_get_login_logo_url() : '';
    $login_css_path = 'assets/css/login.css';
    $login_css_url = get_template_directory_uri() . '/' . $login_css_path;
    $version = function_exists('em_wp_login_asset_version') ? em_wp_login_asset_version($login_css_path) : wp_get_theme()->get('Version');
    $logout_url = wp_logout_url(home_url('/'));
    $site_url = home_url('/');
    $message_html = function_exists('em_wp_ellene_admin_gate_message_html')
        ? em_wp_ellene_admin_gate_message_html()
        : '<p>Information.</p>';

    nocache_headers();
    status_header(200);
    ?>
    <!doctype html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo esc_html(get_bloginfo('name')); ?> - <?php esc_html_e('Information', 'em-wp'); ?></title>
        <link rel="stylesheet" href="<?php echo esc_url($login_css_url); ?>?ver=<?php echo esc_attr((string) $version); ?>">
        <style>
            body.login {
                margin: 0;
            }

            body.login #login.em-wp-ellene-gate-login {
                width: 560px !important;
                max-width: 560px !important;
                min-width: 560px !important;
                padding: 5% 0 0;
                margin: 0 auto;
                box-sizing: border-box;
            }

            body.login #login h1 {
                margin: 0 0 20px;
                text-align: center;
            }

            body.login #login .em-wp-login-logo {
                display: block;
                margin: 0 auto;
                width: auto;
                max-width: 120px;
                height: auto;
            }

            body.login #loginform.em-wp-ellene-gate-form {
                width: 560px !important;
                max-width: 560px !important;
                min-width: 560px !important;
                box-sizing: border-box;
                padding-top: 26px;
                padding-right: 0;
                padding-bottom: 26px;
                padding-left: 0;
                margin: 0;
            }

            body.login #login .em-wp-ellene-gate-message {
                width: 100%;
                color: #ffffff;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                font-size: 16px;
                line-height: 1.5;
                font-weight: 400;
                padding: 4px 28px 8px;
                margin: 0;
                box-sizing: border-box;
            }

            body.login #login .em-wp-ellene-gate-message p {
                margin: 0 0 16px;
                font-family: inherit;
                font-size: 16px;
                line-height: 1.5;
                font-weight: 400;
            }

            body.login #login .em-wp-ellene-gate-message p:last-child {
                margin-bottom: 0;
            }

            body.login #login .em-wp-ellene-gate-form .submit {
                margin: 22px 28px 10px;
                padding: 0 0 10px;
            }

            body.login #login .em-wp-ellene-gate-form .button.button-primary {
                appearance: none;
                -webkit-appearance: none;
                display: inline-block;
                min-height: 32px;
                margin: 0;
                padding: 0 12px 2px;
                border: 1px solid #3c434a;
                border-radius: 3px;
                background: #3c434a;
                color: #ffffff;
                text-decoration: none;
                text-shadow: none;
                box-shadow: none;
                cursor: pointer;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                font-size: 13px;
                font-weight: 400;
                line-height: 2.15384615;
            }

            body.login #login .em-wp-ellene-gate-form .button.button-primary:hover,
            body.login #login .em-wp-ellene-gate-form .button.button-primary:focus {
                background: #50575e;
                border-color: #50575e;
                color: #ffffff;
            }

            body.login #login .em-wp-ellene-gate-form .button.button-primary:focus {
                outline: 2px solid transparent;
                box-shadow: 0 0 0 1px #ffffff;
            }

            body.login #backtoblog {
                width: 560px;
                max-width: 560px;
                min-width: 560px;
                margin: 16px 0 0;
                text-align: left;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                font-size: 13px;
                line-height: 1.4;
                box-sizing: border-box;
            }

            body.login #backtoblog a {
                display: inline-block;
                color: #ffffff;
                text-decoration: none;
                box-shadow: none;
            }

            body.login #backtoblog a:hover,
            body.login #backtoblog a:focus {
                color: #ffffff;
                text-decoration: none;
                box-shadow: none;
            }

            @media (max-width: 620px) {
                body.login #login.em-wp-ellene-gate-login,
                body.login #loginform.em-wp-ellene-gate-form,
                body.login #backtoblog {
                    width: calc(100% - 40px) !important;
                    max-width: calc(100% - 40px) !important;
                    min-width: auto !important;
                }
            }
        </style>
    </head>
    <body class="login wp-core-ui">
        <div id="login" class="em-wp-ellene-gate-login">
            <h1>
                <?php if ($logo_url !== '') { ?>
                    <img
                        src="<?php echo esc_url($logo_url); ?>"
                        alt="<?php esc_attr_e('Ellene Masri', 'em-wp'); ?>"
                        class="em-wp-login-logo"
                        width="480"
                        height="480"
                    >
                <?php } ?>
            </h1>

            <form id="loginform" class="em-wp-ellene-gate-form" action="#" method="post">
                <div class="em-wp-ellene-gate-message">
                    <?php echo wp_kses_post($message_html); ?>
                </div>

                <p class="submit">
                    <a class="button button-primary" href="<?php echo esc_url($logout_url); ?>">
                        <?php esc_html_e('Se déconnecter', 'em-wp'); ?>
                    </a>
                </p>
            </form>

            <p id="backtoblog">
                <a href="<?php echo esc_url($site_url); ?>" target="_blank" rel="noopener noreferrer">
                    &larr; <?php esc_html_e('Aller sur Ellene Masri', 'em-wp'); ?>
                </a>
            </p>
        </div>
    </body>
    </html>
    <?php
    exit;
}
add_action('template_redirect', 'em_wp_maybe_render_login_off_page', 0);