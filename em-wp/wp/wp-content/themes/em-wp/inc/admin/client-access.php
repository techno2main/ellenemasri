<?php
/**
 * Accès admin client (ellene-admin) vs accès total (admin-my).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Login de l'utilisateur admin courant.
 */
function em_wp_admin_user_login(): string
{
    $user = wp_get_current_user();

    if (!$user || empty($user->user_login)) {
        return '';
    }

    return (string) $user->user_login;
}

/**
 * Accès total sans restriction (TAD).
 */
function em_wp_admin_is_power_user(): bool
{
    return em_wp_admin_user_login() === 'admin-my';
}

/**
 * Compte client Ellene.
 */
function em_wp_admin_is_ellene_admin(): bool
{
    return strtolower(em_wp_admin_user_login()) === 'ellene-admin';
}

/**
 * Appliquer les restrictions ellene-admin.
 */
function em_wp_admin_should_limit_ellene_client(): bool
{
    return is_admin()
        && current_user_can('manage_options')
        && em_wp_admin_is_ellene_admin()
        && !em_wp_admin_is_power_user();
}

/**
 * Menus latéraux masqués pour ellene-admin.
 */
function em_wp_limit_admin_menu_for_ellene_admin(): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }

    $remove = [
        'edit.php',
        'edit.php?post_type=page',
        'edit-comments.php',
        'users.php',
        'tools.php',
        'plugins.php',
        'site-editor.php',
        'edit.php?post_type=wp_block',
    ];

    foreach ($remove as $slug) {
        remove_menu_page($slug);
    }
}
add_action('admin_menu', 'em_wp_limit_admin_menu_for_ellene_admin', 999);

/**
 * Apparence : ne garder que la liste des thèmes.
 */
function em_wp_limit_appearance_submenu_for_ellene_admin(): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }

    global $submenu;

    if (empty($submenu['themes.php']) || !is_array($submenu['themes.php'])) {
        return;
    }

    foreach ($submenu['themes.php'] as $index => $submenu_item) {
        $slug = isset($submenu_item[2]) ? (string) $submenu_item[2] : '';

        if ($slug !== 'themes.php') {
            unset($submenu['themes.php'][$index]);
        }
    }
}
add_action('admin_menu', 'em_wp_limit_appearance_submenu_for_ellene_admin', 1001);

/**
 * Paramètres : ne garder que Réglages généraux (options-general.php).
 */
function em_wp_limit_settings_submenu_for_ellene_admin(): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }

    global $submenu;

    if (empty($submenu['options-general.php']) || !is_array($submenu['options-general.php'])) {
        return;
    }

    foreach ($submenu['options-general.php'] as $index => $submenu_item) {
        $slug = isset($submenu_item[2]) ? (string) $submenu_item[2] : '';

        if ($slug !== 'options-general.php') {
            unset($submenu['options-general.php'][$index]);
        }
    }
}
add_action('admin_menu', 'em_wp_limit_settings_submenu_for_ellene_admin', 1000);

/**
 * Redirige les écrans WP natifs interdits vers Apparence → Thèmes.
 */
function em_wp_redirect_blocked_admin_pages_for_ellene_admin(): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }

    global $pagenow;

    $blocked_pagenow = [
        'customize.php',
        'site-editor.php',
        'theme-editor.php',
        'nav-menus.php',
        'edit.php',
        'edit-comments.php',
        'users.php',
        'tools.php',
        'plugins.php',
        'plugin-install.php',
        'plugin-editor.php',
        'theme-install.php',
        'options-writing.php',
        'options-reading.php',
        'options-discussion.php',
        'options-media.php',
        'options-permalink.php',
        'options-privacy.php',
    ];

    if (in_array((string) $pagenow, $blocked_pagenow, true)) {
        $redirect = str_starts_with((string) $pagenow, 'options-')
            ? admin_url('options-general.php')
            : admin_url('themes.php');
        wp_safe_redirect($redirect);
        exit;
    }

    $post_type = sanitize_key((string) ($_GET['post_type'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($post_type === 'page' || $post_type === 'wp_block') {
        wp_safe_redirect(admin_url('themes.php'));
        exit;
    }

    $page = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($page !== '' && in_array($page, ['gutenberg-fonts', 'fonts', 'customize'], true)) {
        wp_safe_redirect(admin_url('themes.php'));
        exit;
    }
}
add_action('admin_init', 'em_wp_redirect_blocked_admin_pages_for_ellene_admin', 20);

/**
 * Barre admin : masquer + New et menu WordPress (logo WP).
 */
function em_wp_limit_admin_bar_for_ellene_admin($wp_admin_bar): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }

    $remove = [
        'wp-logo',
        'about',
        'wporg',
        'documentation',
        'support-forums',
        'feedback',
        'new-content',
        'new-post',
        'new-page',
        'new-user',
        'new-media',
        'comments',
        'customize',
        'command-palette',
    ];

    foreach ($remove as $node_id) {
        $wp_admin_bar->remove_node($node_id);
    }
}
add_action('admin_bar_menu', 'em_wp_limit_admin_bar_for_ellene_admin', 999);

/**
 * Barre admin : retirer le préfixe « Howdy, » du menu compte (conserve l'avatar).
 */
function em_wp_strip_admin_bar_howdy($wp_admin_bar): void
{
    $node = $wp_admin_bar->get_node('my-account');
    if (!$node || !is_string($node->title) || $node->title === '') {
        return;
    }

    $node->title = preg_replace('#^(?:Howdy|Bonjour),\s*#iu', '', $node->title);
    $wp_admin_bar->add_node($node);
}
add_action('admin_bar_menu', 'em_wp_strip_admin_bar_howdy', 9999);

/**
 * ellene-admin : désactiver la recherche Ctrl+K (Command Palette).
 *
 * @param mixed $load
 * @return mixed
 */
function em_wp_ellene_admin_disable_command_palette($load)
{
    if (em_wp_admin_should_limit_ellene_client()) {
        return false;
    }

    return $load;
}
add_filter('should_load_command_palette', 'em_wp_ellene_admin_disable_command_palette');

/**
 * ellene-admin : retirer les onglets Help (profil, apparence…).
 */
function em_wp_ellene_admin_remove_help_tabs(): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }

    global $pagenow;

    if (!in_array((string) $pagenow, ['profile.php', 'themes.php'], true)) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if ($screen instanceof WP_Screen) {
        $screen->remove_help_tabs();
    }
}
add_action('admin_head', 'em_wp_ellene_admin_remove_help_tabs', 999);

/**
 * Filet CSS admin (menus résiduels + barre du haut).
 */
function em_wp_ellene_admin_access_fallback_css(): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }
    ?>
    <style id="em-wp-ellene-admin-access">
        #wpadminbar #wp-admin-bar-wp-logo,
        #wpadminbar #wp-admin-bar-new-content,
        #wpadminbar #wp-admin-bar-new-post,
        #wpadminbar #wp-admin-bar-new-page,
        #wpadminbar #wp-admin-bar-new-user,
        #wpadminbar #wp-admin-bar-new-media,
        #wpadminbar #wp-admin-bar-command-palette {
            display: none !important;
        }

        #adminmenu a[href*="customize.php"],
        #adminmenu a[href*="site-editor.php"],
        #adminmenu a[href*="nav-menus.php"],
        #adminmenu a[href*="theme-editor.php"],
        #adminmenu a[href*="post_type=wp_block"],
        #adminmenu a[href*="fonts"],
        #adminmenu a[href="edit.php"],
        #adminmenu a[href="edit.php?post_type=page"],
        #adminmenu a[href="edit-comments.php"],
        #adminmenu a[href="users.php"],
        #adminmenu a[href="tools.php"],
        #adminmenu a[href="plugins.php"],
        #adminmenu a[href*="plugin-install.php"],
        #adminmenu a[href="options-writing.php"],
        #adminmenu a[href="options-reading.php"],
        #adminmenu a[href="options-discussion.php"],
        #adminmenu a[href="options-media.php"],
        #adminmenu a[href="options-permalink.php"],
        #adminmenu a[href="options-privacy.php"] {
            display: none !important;
        }
    </style>
    <?php
}
add_action('admin_head', 'em_wp_ellene_admin_access_fallback_css', 100);
add_action('wp_head', 'em_wp_ellene_admin_access_fallback_css', 100);

/**
 * Profil ellene-admin : retirer Application Passwords (PHP).
 */
function em_wp_ellene_admin_remove_profile_sections(): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'profile.php') {
        return;
    }

    remove_action('show_user_profile', 'wp_application_passwords_profile_section');
    remove_action('edit_user_profile', 'wp_application_passwords_profile_section');
}
add_action('admin_init', 'em_wp_ellene_admin_remove_profile_sections');

/**
 * Profil ellene-admin : masquer options personnelles avancées (CSS).
 */
function em_wp_ellene_admin_profile_page_css(): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'profile.php') {
        return;
    }
    ?>
    <style id="em-wp-ellene-admin-profile">
        .user-syntax-highlighting-wrap,
        .user-admin-color-wrap,
        .user-comment-shortcuts-wrap,
        .user-admin-bar-front-wrap,
        #application-passwords-section,
        .application-passwords,
        #contextual-help-link-wrap {
            display: none !important;
        }
    </style>
    <?php
}
add_action('admin_head', 'em_wp_ellene_admin_profile_page_css', 100);

/**
 * ellene-admin : Apparence (themes.php) — sans Add Theme ni Help.
 */
function em_wp_ellene_admin_themes_page_css(): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'themes.php') {
        return;
    }
    ?>
    <style id="em-wp-ellene-admin-themes">
        .themes-php .page-title-action,
        .themes-php .wrap .page-title-action,
        #contextual-help-link-wrap,
        #screen-meta-links {
            display: none !important;
        }
    </style>
    <?php
}
add_action('admin_head', 'em_wp_ellene_admin_themes_page_css', 100);

/**
 * ellene-admin : Réglages généraux — masquer Membership et New User Default Role.
 */
function em_wp_ellene_admin_settings_page_css(): void
{
    if (!em_wp_admin_should_limit_ellene_client()) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'options-general.php') {
        return;
    }
    ?>
    <style id="em-wp-ellene-admin-settings">
        .options-general-php tr:has(#users_can_register),
        .options-general-php tr:has(#default_role) {
            display: none !important;
        }
    </style>
    <?php
}
add_action('admin_head', 'em_wp_ellene_admin_settings_page_css', 100);

/**
 * Slug de la page intermédiaire dédiée à ellene-admin.
 */
function em_wp_ellene_admin_gate_page_slug(): string
{
    return 'em-wp-ellene-admin-gate';
}

/**
 * Nom d'option stockant l'état + message du verrou ellene-admin.
 */
function em_wp_ellene_admin_gate_option_name(): string
{
    return 'em_wp_ellene_admin_gate';
}

/**
 * Slug page de réglages du verrou (réservée à admin-my).
 */
function em_wp_ellene_admin_gate_settings_page_slug(): string
{
    return 'em-wp-ellene-admin-gate-settings';
}

/**
 * Message par défaut du verrou ellene-admin.
 */
function em_wp_ellene_admin_gate_default_message(): string
{
    return "Bonjour Ellene,\n\n"
        . "Une intervention technique est en cours. Merci de ne pas modifier le back-office pour le moment.\n\n"
        . "Tu peux revenir plus tard, ou contacter l'équipe si besoin.";
}

/**
 * Configuration complète du verrou ellene-admin.
 *
 * @return array{enabled:bool,message:string}
 */
function em_wp_ellene_admin_gate_config(): array
{
    $saved = get_option(em_wp_ellene_admin_gate_option_name(), []);

    if (!is_array($saved)) {
        $saved = [];
    }

    $message = trim((string) ($saved['message'] ?? ''));

    if ($message === '') {
        $message = em_wp_ellene_admin_gate_default_message();
    }

    return [
        'enabled' => !empty($saved['enabled']),
        'message' => $message,
    ];
}

/**
 * Indique si le verrou ellene-admin est actif.
 */
function em_wp_ellene_admin_gate_is_enabled(): bool
{
    $cfg = em_wp_ellene_admin_gate_config();

    return (bool) $cfg['enabled'];
}

/**
 * URL page de réglages du verrou (admin-my).
 */
function em_wp_ellene_admin_gate_settings_admin_url(): string
{
    return admin_url('admin.php?page=' . em_wp_ellene_admin_gate_settings_page_slug());
}

/**
 * URL de la page intermédiaire dédiée à ellene-admin.
 */
function em_wp_ellene_admin_gate_admin_url(): string
{
    return admin_url('admin.php?page=' . em_wp_ellene_admin_gate_page_slug());
}

/**
 * Message principal affiché sur la page intermédiaire.
 *
 * Personnalise ce bloc librement selon le message à transmettre.
 */
function em_wp_ellene_admin_gate_message_html(): string
{
    $cfg = em_wp_ellene_admin_gate_config();
    $safe = esc_html((string) $cfg['message']);

    return (string) wpautop($safe);
}

/**
 * Enregistre la page intermédiaire (menu masqué).
 */
function em_wp_ellene_admin_register_gate_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    add_menu_page(
        __('Information', 'em-wp'),
        __('Information', 'em-wp'),
        'manage_options',
        em_wp_ellene_admin_gate_page_slug(),
        'em_wp_ellene_admin_render_gate_page',
        'dashicons-info-outline',
        2
    );
}
add_action('admin_menu', 'em_wp_ellene_admin_register_gate_page', 2);

/**
 * Enregistre la page de réglages du verrou (admin-my uniquement).
 */
function em_wp_ellene_admin_register_gate_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    add_submenu_page(
        'options-general.php',
        __('Verrou ellene-admin', 'em-wp'),
        __('Verrou ellene-admin', 'em-wp'),
        'manage_options',
        em_wp_ellene_admin_gate_settings_page_slug(),
        'em_wp_ellene_admin_render_gate_settings_page'
    );
}
add_action('admin_menu', 'em_wp_ellene_admin_register_gate_settings_page', 30);

/**
 * Retire l'entrée menu de la page intermédiaire.
 */
function em_wp_ellene_admin_hide_gate_menu_entry(): void
{
    remove_menu_page(em_wp_ellene_admin_gate_page_slug());
}
add_action('admin_menu', 'em_wp_ellene_admin_hide_gate_menu_entry', 10002);

/**
 * Rendu de la page intermédiaire (bloc message personnalisable).
 */
function em_wp_ellene_admin_render_gate_page(): void
{
    if (!em_wp_admin_should_limit_ellene_client() || !em_wp_ellene_admin_gate_is_enabled()) {
        wp_safe_redirect(admin_url());
        exit;
    }

    $logout_url = wp_logout_url(home_url('/'));
    ?>
    <div class="wrap em-wp-ellene-gate-wrap">
        <div class="em-wp-ellene-gate-card">
            <h1><?php esc_html_e('Information', 'em-wp'); ?></h1>
            <div class="em-wp-ellene-gate-message"><?php echo wp_kses_post(em_wp_ellene_admin_gate_message_html()); ?></div>
            <p class="em-wp-ellene-gate-actions">
                <a class="button button-primary" href="<?php echo esc_url($logout_url); ?>"><?php esc_html_e('Se déconnecter', 'em-wp'); ?></a>
            </p>
        </div>
    </div>
    <?php
}

/**
 * Rendu page de réglages du verrou (admin-my).
 */
function em_wp_ellene_admin_render_gate_settings_page(): void
{
    if (!current_user_can('manage_options') || !em_wp_admin_is_power_user()) {
        wp_die(esc_html__('Accès refusé.', 'em-wp'));
    }

    $cfg = em_wp_ellene_admin_gate_config();
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $updated = sanitize_key((string) ($_GET['updated'] ?? '')) === '1';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Verrou ellene-admin', 'em-wp'); ?></h1>

        <?php if ($updated) { ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Réglages enregistrés.', 'em-wp'); ?></p></div>
        <?php } ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('em_wp_ellene_gate_save'); ?>
            <input type="hidden" name="action" value="em_wp_ellene_gate_save">

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('Statut', 'em-wp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" <?php checked(!empty($cfg['enabled'])); ?>>
                                <?php esc_html_e('Activer la page intermédiaire pour ellene-admin', 'em-wp'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Activé: ellene-admin est bloquée sur la page message. Désactivé: connexion normale à son admin.', 'em-wp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="em-wp-ellene-gate-message"><?php esc_html_e('Message', 'em-wp'); ?></label></th>
                        <td>
                            <textarea id="em-wp-ellene-gate-message" name="message" rows="10" class="large-text"><?php echo esc_textarea((string) $cfg['message']); ?></textarea>
                            <p class="description"><?php esc_html_e('Texte affiché dans le bloc message de la page intermédiaire.', 'em-wp'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}

/**
 * Sauvegarde des réglages du verrou (admin-my).
 */
function em_wp_ellene_admin_save_gate_settings(): void
{
    if (!current_user_can('manage_options') || !em_wp_admin_is_power_user()) {
        wp_die(esc_html__('Accès refusé.', 'em-wp'));
    }

    check_admin_referer('em_wp_ellene_gate_save');

    $enabled = !empty($_POST['enabled']); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $message = sanitize_textarea_field((string) wp_unslash($_POST['message'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

    if (trim($message) === '') {
        $message = em_wp_ellene_admin_gate_default_message();
    }

    update_option(em_wp_ellene_admin_gate_option_name(), [
        'enabled' => $enabled ? 1 : 0,
        'message' => $message,
    ], false);

    wp_safe_redirect(add_query_arg(['page' => em_wp_ellene_admin_gate_settings_page_slug(), 'updated' => '1'], admin_url('admin.php')));
    exit;
}
add_action('admin_post_em_wp_ellene_gate_save', 'em_wp_ellene_admin_save_gate_settings');

/**
 * Après connexion, ellene-admin arrive toujours sur la page intermédiaire.
 *
 * @param mixed $redirect_to
 * @param mixed $requested_redirect_to
 * @param mixed $user
 * @return mixed
 */
function em_wp_ellene_admin_login_redirect_to_gate($redirect_to, $requested_redirect_to, $user)
{
    unset($requested_redirect_to);

    if (!($user instanceof WP_User)) {
        return $redirect_to;
    }

    if (strtolower((string) $user->user_login) !== 'ellene-admin') {
        return $redirect_to;
    }

    if (!user_can($user, 'manage_options')) {
        return $redirect_to;
    }

    if (!em_wp_ellene_admin_gate_is_enabled()) {
        return $redirect_to;
    }

    return em_wp_ellene_admin_gate_admin_url();
}
add_filter('login_redirect', 'em_wp_ellene_admin_login_redirect_to_gate', 5, 3);

/**
 * Verrouille ellene-admin sur la page intermédiaire (aucun autre écran admin).
 */
function em_wp_ellene_admin_lock_to_gate_page(): void
{
    if (!em_wp_admin_should_limit_ellene_client() || !em_wp_ellene_admin_gate_is_enabled()) {
        return;
    }

    global $pagenow;

    if ($pagenow === 'admin-ajax.php') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current_page = sanitize_key((string) ($_GET['page'] ?? ''));
    $is_gate = $pagenow === 'admin.php' && $current_page === em_wp_ellene_admin_gate_page_slug();

    if ($is_gate) {
        return;
    }

    wp_safe_redirect(em_wp_ellene_admin_gate_admin_url());
    exit;
}
add_action('admin_init', 'em_wp_ellene_admin_lock_to_gate_page', 0);

/**
 * Désactive l'admin bar pour ellene-admin.
 */
function em_wp_ellene_admin_disable_admin_bar(bool $show): bool
{
    if (em_wp_admin_should_limit_ellene_client() && em_wp_ellene_admin_gate_is_enabled()) {
        return false;
    }

    return $show;
}
add_filter('show_admin_bar', 'em_wp_ellene_admin_disable_admin_bar', 100);

/**
 * Masque complètement le chrome admin (menu gauche + footer) sur la page intermédiaire.
 */
function em_wp_ellene_admin_gate_chrome_css(): void
{
    if (!em_wp_admin_should_limit_ellene_client() || !em_wp_ellene_admin_gate_is_enabled()) {
        return;
    }

    global $pagenow;

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current_page = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($pagenow !== 'admin.php' || $current_page !== em_wp_ellene_admin_gate_page_slug()) {
        return;
    }
    ?>
    <style id="em-wp-ellene-gate-only">
        #adminmenumain,
        #wpfooter,
        #screen-meta-links,
        #contextual-help-link-wrap,
        #screen-options-link-wrap,
        #wpadminbar {
            display: none !important;
        }

        #wpcontent,
        #wpfooter,
        #wpbody,
        #wpbody-content {
            margin-left: 0 !important;
        }

        #wpbody-content {
            padding-bottom: 24px !important;
        }

        .em-wp-ellene-gate-wrap {
            min-height: calc(100vh - 40px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            box-sizing: border-box;
        }

        .em-wp-ellene-gate-card {
            width: 100%;
            max-width: 760px;
            background: #ffffff;
            border: 1px solid #d9dee7;
            border-radius: 12px;
            box-shadow: 0 14px 34px rgba(16, 24, 40, 0.08);
            padding: 28px;
        }

        .em-wp-ellene-gate-card h1 {
            margin: 0 0 14px;
            font-size: 28px;
            line-height: 1.2;
        }

        .em-wp-ellene-gate-message p {
            margin: 0 0 10px;
            font-size: 15px;
            line-height: 1.6;
        }

        .em-wp-ellene-gate-actions {
            margin: 16px 0 0;
        }
    </style>
    <?php
}
add_action('admin_head', 'em_wp_ellene_admin_gate_chrome_css', 1000);
