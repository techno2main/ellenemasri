<?php
/**
 * Personnalisation de la page de connexion WordPress (alignée prod).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL du logo animé (moonwalk) au-dessus du formulaire.
 */
function em_wp_get_login_logo_url(): string
{
    $theme_asset = get_template_directory() . '/assets/img/moonwalk.gif';

    if (is_readable($theme_asset)) {
        return get_template_directory_uri() . '/assets/img/moonwalk.gif?v=' . (string) filemtime($theme_asset);
    }

    return 'https://deretourdufutur.fr/assets/img/Moonwalk.gif';
}

/**
 * Version cache-bust pour un asset login.
 */
function em_wp_login_asset_version(string $relative_path): string
{
    $absolute_path = get_template_directory() . '/' . ltrim($relative_path, '/');
    $version = wp_get_theme()->get('Version');

    if (is_readable($absolute_path)) {
        return $version . '.' . (string) filemtime($absolute_path);
    }

    return $version;
}

/**
 * Charge les styles de la page login.
 */
function em_wp_enqueue_login_assets(): void
{
    $theme_uri = get_template_directory_uri();
    $login_css_path = 'assets/css/login.css';

    wp_enqueue_style(
        'em-wp-login',
        $theme_uri . '/' . $login_css_path,
        [],
        em_wp_login_asset_version($login_css_path)
    );
}
add_action('login_enqueue_scripts', 'em_wp_enqueue_login_assets', 20);

/**
 * Moonwalk décoratif (sans lien — le lien WP h1 est retiré).
 */
function em_wp_render_login_logo_img(): void
{
    $logo_url = em_wp_get_login_logo_url();

    if ($logo_url === '') {
        return;
    }

    $alt = em_wp_customize_login_logo_text('');
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var h1 = document.querySelector('#login h1');

        if (!h1 || h1.querySelector('.em-wp-login-logo')) {
            return;
        }

        var link = h1.querySelector('a');
        var img = document.createElement('img');
        img.src = <?php echo wp_json_encode($logo_url); ?>;
        img.alt = <?php echo wp_json_encode($alt); ?>;
        img.className = 'em-wp-login-logo';
        img.width = 480;
        img.height = 480;

        h1.insertBefore(img, link || null);

        if (link) {
            link.remove();
        }
    });
    </script>
    <?php
}
add_action('login_footer', 'em_wp_render_login_logo_img', 5);

/**
 * Texte alternatif des visuels login.
 */
function em_wp_customize_login_logo_text(string $text): string
{
    return 'Ellene Masri';
}
add_filter('login_headertext', 'em_wp_customize_login_logo_text');

/**
 * Lien « retour au site » sous le formulaire.
 */
function em_wp_customize_login_site_link(string $html): string
{
    return sprintf(
        '<a href="%1$s" target="_blank" rel="noopener noreferrer">&larr; %2$s</a>',
        esc_url(home_url('/')),
        esc_html__('Aller sur Ellene Masri', 'em-wp')
    );
}
add_filter('login_site_html_link', 'em_wp_customize_login_site_link');

/**
 * Lien « mot de passe oublié » → nouvel onglet.
 */
function em_wp_customize_login_lost_password_link(string $html): string
{
    if ($html === '') {
        return $html;
    }

    return str_replace('<a ', '<a target="_blank" rel="noopener noreferrer" ', $html);
}
add_filter('lost_password_html_link', 'em_wp_customize_login_lost_password_link');

/**
 * Libellés français du formulaire login (install locale souvent en anglais).
 *
 * @param mixed $translation
 * @param mixed $text
 * @param mixed $domain
 * @return mixed
 */
function em_wp_login_french_labels($translation, $text, $domain)
{
    global $pagenow;

    if (($pagenow ?? '') !== 'wp-login.php' || $domain !== 'default' || !is_string($text)) {
        return $translation;
    }

    $labels = [
        'Username or Email Address' => 'Identifiant ou adresse e-mail',
        'Password'                  => 'Mot de passe',
        'Remember Me'               => 'Se souvenir de moi',
        'Log In'                    => 'Se connecter',
        'Lost your password?'       => 'Mot de passe oublié ?',
    ];

    return $labels[$text] ?? $translation;
}
add_filter('gettext', 'em_wp_login_french_labels', 20, 3);

/**
 * Après déconnexion, renvoie vers la page login locale.
 *
 * @param mixed $redirect_to
 * @param mixed $requested_redirect_to
 * @param mixed $user
 * @return mixed
 */
function em_wp_logout_redirect_to_login($redirect_to, $requested_redirect_to, $user)
{
    unset($requested_redirect_to, $user);

    return wp_login_url();
}
add_filter('logout_redirect', 'em_wp_logout_redirect_to_login', 20, 3);
