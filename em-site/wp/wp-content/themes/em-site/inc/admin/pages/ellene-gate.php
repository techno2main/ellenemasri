<?php
/**
 * Verrou admin-ellene (gate) pour em-site.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_ellene_admin_gate_page_slug(): string
{
    return 'em-ellene-admin-gate';
}

function em_site_ellene_admin_gate_option_name(): string
{
    return 'em_site_ellene_admin_gate';
}

function em_site_ellene_admin_gate_settings_page_slug(): string
{
    return 'em-ellene-admin-gate-settings';
}

function em_site_ellene_admin_gate_default_message(): string
{
    return "Bonjour Ellene,\n\n"
        . "Une intervention technique est en cours. Merci de ne pas modifier le back-office pour le moment.\n\n"
        . "Tu peux revenir plus tard, ou contacter l'equipe si besoin.";
}

function em_site_ellene_admin_gate_config(): array
{
    $saved = get_option(em_site_ellene_admin_gate_option_name(), []);

    if (!is_array($saved)) {
        $saved = [];
    }

    $message = trim((string) ($saved['message'] ?? ''));
    if ($message === '') {
        $message = em_site_ellene_admin_gate_default_message();
    }

    return [
        'enabled' => !empty($saved['enabled']),
        'message' => $message,
    ];
}

function em_site_ellene_admin_gate_is_enabled(): bool
{
    $cfg = em_site_ellene_admin_gate_config();

    return (bool) $cfg['enabled'];
}

function em_site_ellene_admin_gate_settings_admin_url(): string
{
    return admin_url('admin.php?page=' . em_site_ellene_admin_gate_settings_page_slug());
}

function em_site_ellene_admin_is_gate_screen(): bool
{
    global $pagenow;

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current_page = sanitize_key((string) ($_GET['page'] ?? ''));

    return $pagenow === 'admin.php' && $current_page === em_site_ellene_admin_gate_page_slug();
}

function em_site_ellene_admin_gate_admin_url(): string
{
    return admin_url('admin.php?page=' . em_site_ellene_admin_gate_page_slug());
}

function em_site_ellene_admin_gate_message_html(): string
{
    $cfg = em_site_ellene_admin_gate_config();
    $safe = esc_html((string) $cfg['message']);

    return (string) wpautop($safe);
}

function em_site_ellene_admin_register_gate_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    add_menu_page(
        __('Information', 'em-wp'),
        __('Information', 'em-wp'),
        'manage_options',
        em_site_ellene_admin_gate_page_slug(),
        'em_site_ellene_admin_render_gate_page',
        'dashicons-info-outline',
        2
    );
}
add_action('admin_menu', 'em_site_ellene_admin_register_gate_page', 21);

function em_site_ellene_admin_register_gate_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    add_submenu_page(
        'options-general.php',
        __('Verrou admin-ellene', 'em-wp'),
        __('Verrou admin-ellene', 'em-wp'),
        'manage_options',
        em_site_ellene_admin_gate_settings_page_slug(),
        'em_site_ellene_admin_render_gate_settings_page'
    );
}
add_action('admin_menu', 'em_site_ellene_admin_register_gate_settings_page', 30);

function em_site_ellene_admin_hide_gate_menu_entry(): void
{
    remove_menu_page(em_site_ellene_admin_gate_page_slug());
}
add_action('admin_menu', 'em_site_ellene_admin_hide_gate_menu_entry', 1000);

function em_site_ellene_admin_render_gate_page(): void
{
    if (!em_site_admin_should_limit_ellene_client() || !em_site_ellene_admin_gate_is_enabled()) {
        wp_safe_redirect(admin_url());
        exit;
    }

    $lost_password_url = wp_lostpassword_url();
    $site_url = home_url('/');
    $logout_url = wp_logout_url(home_url('/'));
    ?>
    <div id="login" class="em-wp-ellene-gate-login">
        <h1><a href="<?php echo esc_url($site_url); ?>" aria-label="Ellene Masri">Ellene Masri</a></h1>

        <form id="loginform" class="em-wp-ellene-gate-form" action="#" method="post">
            <p><label><?php esc_html_e('Information', 'em-wp'); ?></label></p>
            <div class="em-wp-ellene-gate-message"><?php echo wp_kses_post(em_site_ellene_admin_gate_message_html()); ?></div>
            <p class="submit"><a class="button button-primary" href="<?php echo esc_url($logout_url); ?>"><?php esc_html_e('Se deconnecter', 'em-wp'); ?></a></p>
        </form>

        <p id="nav"><a href="<?php echo esc_url($lost_password_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Mot de passe oublie ?', 'em-wp'); ?></a></p>
        <p id="backtoblog"><a href="<?php echo esc_url($site_url); ?>" target="_blank" rel="noopener noreferrer">&larr; <?php esc_html_e('Aller sur Ellene Masri', 'em-wp'); ?></a></p>
    </div>
    <?php
}

function em_site_ellene_admin_render_gate_settings_page(): void
{
    if (!current_user_can('manage_options') || !em_site_admin_is_power_user()) {
        wp_die(esc_html__('Acces refuse.', 'em-wp'));
    }

    $cfg = em_site_ellene_admin_gate_config();
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $updated = sanitize_key((string) ($_GET['updated'] ?? '')) === '1';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Verrou admin-ellene', 'em-wp'); ?></h1>

        <?php if ($updated) { ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Reglages enregistres.', 'em-wp'); ?></p></div>
        <?php } ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('em_site_ellene_gate_save'); ?>
            <input type="hidden" name="action" value="em_site_ellene_gate_save">

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('Statut', 'em-wp'); ?></th>
                        <td>
                            <label><input type="checkbox" name="enabled" value="1" <?php checked(!empty($cfg['enabled'])); ?>> <?php esc_html_e('Activer la page intermediaire pour admin-ellene', 'em-wp'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="em-site-ellene-gate-message"><?php esc_html_e('Message', 'em-wp'); ?></label></th>
                        <td>
                            <textarea id="em-site-ellene-gate-message" name="message" rows="10" class="large-text"><?php echo esc_textarea((string) $cfg['message']); ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}

function em_site_ellene_admin_save_gate_settings(): void
{
    if (!current_user_can('manage_options') || !em_site_admin_is_power_user()) {
        wp_die(esc_html__('Acces refuse.', 'em-wp'));
    }

    check_admin_referer('em_site_ellene_gate_save');

    $enabled = !empty($_POST['enabled']); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $message = sanitize_textarea_field((string) wp_unslash($_POST['message'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

    if (trim($message) === '') {
        $message = em_site_ellene_admin_gate_default_message();
    }

    update_option(em_site_ellene_admin_gate_option_name(), [
        'enabled' => $enabled ? 1 : 0,
        'message' => $message,
    ], false);

    wp_safe_redirect(add_query_arg(['page' => em_site_ellene_admin_gate_settings_page_slug(), 'updated' => '1'], admin_url('admin.php')));
    exit;
}
add_action('admin_post_em_site_ellene_gate_save', 'em_site_ellene_admin_save_gate_settings');

function em_site_ellene_admin_login_redirect_to_gate($redirect_to, $requested_redirect_to, $user)
{
    unset($requested_redirect_to);

    if (!($user instanceof WP_User)) {
        return $redirect_to;
    }

    if (strtolower((string) $user->user_login) !== 'admin-ellene') {
        return $redirect_to;
    }

    if (!user_can($user, 'manage_options') || !em_site_ellene_admin_gate_is_enabled()) {
        return $redirect_to;
    }

    return em_site_ellene_admin_gate_admin_url();
}
add_filter('login_redirect', 'em_site_ellene_admin_login_redirect_to_gate', 5, 3);

function em_site_ellene_admin_lock_to_gate_page(): void
{
    if (!em_site_admin_should_limit_ellene_client() || !em_site_ellene_admin_gate_is_enabled()) {
        return;
    }

    global $pagenow;

    if ($pagenow === 'admin-ajax.php' || em_site_ellene_admin_is_gate_screen()) {
        return;
    }

    wp_safe_redirect(em_site_ellene_admin_gate_admin_url());
    exit;
}
add_action('admin_init', 'em_site_ellene_admin_lock_to_gate_page', 0);

function em_site_ellene_admin_disable_admin_bar(bool $show): bool
{
    if (em_site_admin_should_limit_ellene_client() && em_site_ellene_admin_gate_is_enabled()) {
        return false;
    }

    return $show;
}
add_filter('show_admin_bar', 'em_site_ellene_admin_disable_admin_bar', 100);

function em_site_ellene_admin_gate_body_class($classes)
{
    if (!em_site_admin_should_limit_ellene_client() || !em_site_ellene_admin_gate_is_enabled() || !em_site_ellene_admin_is_gate_screen()) {
        return $classes;
    }

    return trim((string) $classes . ' login wp-core-ui');
}
add_filter('admin_body_class', 'em_site_ellene_admin_gate_body_class');

function em_site_ellene_admin_gate_chrome_css(): void
{
    if (!em_site_admin_should_limit_ellene_client() || !em_site_ellene_admin_gate_is_enabled() || !em_site_ellene_admin_is_gate_screen()) {
        return;
    }
    ?>
    <style id="em-site-ellene-gate-only">
        #adminmenumain,#wpfooter,#screen-meta-links,#contextual-help-link-wrap,#screen-options-link-wrap,#wpadminbar,.update-nag,.notice,.error,.updated,#screen-meta{display:none!important}
        #wpcontent,#wpfooter,#wpbody,#wpbody-content{margin-left:0!important}
        #wpbody-content{padding-bottom:24px!important;min-height:100vh;background:#4f080e}
        .em-wp-ellene-gate-login{margin:32px auto;max-width:360px}
        .em-wp-ellene-gate-message,.em-wp-ellene-gate-form label{color:#fff!important}
        .em-wp-ellene-gate-form .submit{margin-top:18px}
    </style>
    <?php
}
add_action('admin_head', 'em_site_ellene_admin_gate_chrome_css', 1000);
