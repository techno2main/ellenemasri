<?php
/**
 * Handlers POST Templates (bandeau + page liste).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mode cible: template unique (wizard + multi-template désactivés).
 */
function em_wp_template_unique_mode_enabled(): bool
{
    return true;
}

/**
 * Indique si l'utilisateur peut gérer les templates (CRUD + actif live).
 *
 * client-admin et admin-my : tous deux manage_options sur em-wp.
 */
function em_wp_admin_can_manage_templates(): bool
{
    return current_user_can('manage_options');
}

/**
 * @deprecated Utiliser em_wp_admin_can_manage_templates().
 */
function em_wp_admin_can_manage_live_template(): bool
{
    return em_wp_admin_can_manage_templates();
}

/**
 * Traite les actions POST template.
 */
function em_wp_admin_template_handle_post_requests(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $action = sanitize_key((string) ($_POST['em_wp_template_action'] ?? ''));

    if ($action === '') {
        return;
    }

    switch ($action) {
        case 'set_editing':
            em_wp_admin_template_handle_set_editing();
            break;
        case 'create':
            em_wp_admin_template_handle_create();
            break;
        case 'create_wizard':
            em_wp_admin_template_handle_create_wizard();
            break;
        case 'rename':
            em_wp_admin_template_handle_rename();
            break;
        case 'delete':
            em_wp_admin_template_handle_delete();
            break;
        case 'set_active':
            em_wp_admin_template_handle_set_active();
            break;
        case 'clear_editing':
            em_wp_admin_template_handle_clear_editing();
            break;
        case 'quit_to_templates':
            em_wp_admin_template_handle_quit_to_templates();
            break;
        case 'duplicate':
            em_wp_admin_template_handle_duplicate();
            break;
        case 'set_color':
            em_wp_admin_template_handle_set_color();
            break;
    }
}
add_action('admin_init', 'em_wp_admin_template_handle_post_requests', 5);

/**
 * Change le template en édition (bandeau).
 */
function em_wp_admin_template_handle_set_editing(): void
{
    check_admin_referer('em_wp_template_set_editing');

    $slug = em_wp_template_sanitize_slug((string) ($_POST['em_wp_template_editing_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $redirect = em_wp_admin_template_banner_redirect_url();

    if ($slug === '') {
        em_wp_admin_template_redirect_with_notice($redirect, 'error', __('Template invalide.', 'em-wp'));
    }

    $result = em_wp_set_editing_template_slug($slug);

    if (is_wp_error($result)) {
        em_wp_admin_template_redirect_with_notice($redirect, 'error', $result->get_error_message());
    }

    em_wp_admin_template_redirect_with_notice($redirect, 'success', __('Template en édition sélectionné.', 'em-wp'));
}

/**
 * Quitte l'édition rubriques et retourne au sommaire Templates.
 */
function em_wp_admin_template_handle_quit_to_templates(): void
{
    check_admin_referer('em_wp_template_quit_to_templates');

    em_wp_clear_editing_template_context();

    $redirect_url = function_exists('em_wp_admin_template_choice_admin_url')
        ? em_wp_admin_template_choice_admin_url()
        : admin_url('admin.php');

    em_wp_admin_template_redirect_with_notice(
        $redirect_url,
        'success',
        __('Retour au sommaire Templates.', 'em-wp')
    );
}

/**
 * Quitte le mode édition template (retour accueil neutre).
 */
function em_wp_admin_template_handle_clear_editing(): void
{
    check_admin_referer('em_wp_template_clear_editing');

    em_wp_clear_editing_template_context();

    em_wp_admin_template_redirect_with_notice(
        em_wp_admin_dashboard_admin_url(),
        'success',
        __('Édition terminée — tu es de retour à l’accueil.', 'em-wp')
    );
}

/**
 * Crée un template (page liste).
 */
function em_wp_admin_template_handle_create(): void
{
    if (em_wp_template_unique_mode_enabled()) {
        em_wp_admin_template_redirect_with_notice(
            em_wp_admin_template_choice_admin_url(),
            'warning',
            __('Mode template unique actif : création de template désactivée.', 'em-wp')
        );
    }

    check_admin_referer('em_wp_template_create');

    $label = sanitize_text_field((string) ($_POST['em_wp_template_label'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $color = sanitize_text_field((string) ($_POST['em_wp_template_color'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $result = em_wp_template_create($label, $color);

    if (is_wp_error($result)) {
        em_wp_admin_template_redirect_with_notice(em_wp_admin_templates_manage_admin_url(), 'error', $result->get_error_message());
    }

    em_wp_admin_template_redirect_with_notice(
        em_wp_admin_templates_manage_admin_url(),
        'success',
        __('Template créé.', 'em-wp')
    );
}

/**
 * Renomme un template (page liste).
 */
function em_wp_admin_template_handle_rename(): void
{
    check_admin_referer('em_wp_template_rename');

    $slug = em_wp_template_sanitize_slug((string) ($_POST['em_wp_template_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $label = sanitize_text_field((string) ($_POST['em_wp_template_label'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $result = em_wp_template_rename($slug, $label);

    if (is_wp_error($result)) {
        em_wp_admin_template_redirect_with_notice(em_wp_admin_templates_manage_admin_url(), 'error', $result->get_error_message());
    }

    em_wp_admin_template_redirect_with_notice(em_wp_admin_templates_manage_admin_url(), 'success', __('Template renommé.', 'em-wp'));
}

/**
 * Supprime un template (page liste).
 */
function em_wp_admin_template_handle_delete(): void
{
    check_admin_referer('em_wp_template_delete');

    $slug = em_wp_template_sanitize_slug((string) ($_POST['em_wp_template_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $result = em_wp_template_delete($slug);

    if (is_wp_error($result)) {
        em_wp_admin_template_redirect_with_notice(em_wp_admin_templates_manage_admin_url(), 'error', $result->get_error_message());
    }

    em_wp_admin_template_redirect_with_notice(em_wp_admin_templates_manage_admin_url(), 'success', __('Template supprimé.', 'em-wp'));
}

/**
 * Définit le template actif sur le site (page liste).
 */
function em_wp_admin_template_handle_set_active(): void
{
    check_admin_referer('em_wp_template_set_active');

    $slug = em_wp_template_sanitize_slug((string) ($_POST['em_wp_template_active_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $redirect_page = sanitize_key((string) ($_POST['em_wp_template_redirect_page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $redirect_url = ($redirect_page !== '' && str_starts_with($redirect_page, 'em-'))
        ? admin_url('admin.php?page=' . $redirect_page)
        : em_wp_admin_templates_manage_admin_url();
    $result = em_wp_set_active_template_slug($slug);

    if (is_wp_error($result)) {
        em_wp_admin_template_redirect_with_notice($redirect_url, 'error', $result->get_error_message());
    }

    em_wp_admin_template_redirect_with_notice(
        $redirect_url,
        'success',
        __('Template actif sur le site mis à jour.', 'em-wp')
    );
}

/**
 * Duplique un template existant (nom + couleur neufs).
 */
function em_wp_admin_template_handle_duplicate(): void
{
    if (em_wp_template_unique_mode_enabled()) {
        em_wp_admin_template_redirect_with_notice(
            em_wp_admin_template_choice_admin_url(),
            'warning',
            __('Mode template unique actif : duplication désactivée.', 'em-wp')
        );
    }

    check_admin_referer('em_wp_template_duplicate');

    $source_slug = em_wp_template_sanitize_slug((string) ($_POST['em_wp_template_source_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $label = sanitize_text_field((string) ($_POST['em_wp_template_label'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $color = sanitize_text_field((string) ($_POST['em_wp_template_color'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $redirect_url = function_exists('em_wp_admin_template_choice_admin_url')
        ? em_wp_admin_template_choice_admin_url()
        : em_wp_admin_templates_manage_admin_url();

    if ($label === '') {
        em_wp_admin_template_redirect_with_notice($redirect_url, 'error', __('Le nom du template est requis.', 'em-wp'));
    }

    if ($color === '') {
        em_wp_admin_template_redirect_with_notice($redirect_url, 'error', __('La couleur du template est requise.', 'em-wp'));
    }

    $result = em_wp_template_duplicate($source_slug, $label, $color);

    if (is_wp_error($result)) {
        em_wp_admin_template_redirect_with_notice($redirect_url, 'error', $result->get_error_message());
    }

    if (function_exists('em_wp_set_editing_template_slug')) {
        $new_slug = em_wp_template_sanitize_slug((string) ($result['slug'] ?? ''));

        if ($new_slug !== '') {
            em_wp_set_editing_template_slug($new_slug);
        }
    }

    em_wp_admin_template_redirect_with_notice(
        $redirect_url,
        'success',
        __('Template dupliqué.', 'em-wp')
    );
}

/**
 * Met à jour la couleur d'un template (page liste).
 */
function em_wp_admin_template_handle_set_color(): void
{
    check_admin_referer('em_wp_template_set_color');

    $slug = em_wp_template_sanitize_slug((string) ($_POST['em_wp_template_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $color = sanitize_text_field((string) ($_POST['em_wp_template_color'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $result = em_wp_template_set_color($slug, $color);

    if (is_wp_error($result)) {
        em_wp_admin_template_redirect_with_notice(em_wp_admin_templates_manage_admin_url(), 'error', $result->get_error_message());
    }

    em_wp_admin_template_redirect_with_notice(em_wp_admin_templates_manage_admin_url(), 'success', __('Couleur du template enregistrée.', 'em-wp'));
}

/**
 * URL de redirection après action bandeau (page courante).
 */
function em_wp_admin_template_banner_redirect_url(): string
{
    $page = sanitize_key((string) ($_POST['em_wp_template_redirect_page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

    if ($page !== '' && str_starts_with($page, 'em-')) {
        return admin_url('admin.php?page=' . $page);
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($current !== '' && str_starts_with($current, 'em-')) {
        return admin_url('admin.php?page=' . $current);
    }

    return em_wp_admin_dashboard_admin_url();
}

/**
 * Redirect admin avec notice transitoire.
 */
function em_wp_admin_template_redirect_with_notice(string $url, string $type, string $message): void
{
    $type = in_array($type, ['success', 'error', 'warning'], true) ? $type : 'success';
    set_transient('em_wp_template_admin_notice_' . get_current_user_id(), [
        'type'    => $type,
        'message' => $message,
    ], 30);

    em_wp_admin_safe_redirect($url);
}

/**
 * Affiche la notice transitoire template.
 */
function em_wp_admin_template_render_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = get_transient('em_wp_template_admin_notice_' . get_current_user_id());

    if (!is_array($notice)) {
        return;
    }

    delete_transient('em_wp_template_admin_notice_' . get_current_user_id());

    $type = (string) ($notice['type'] ?? 'success');
    $class = $type === 'error' ? 'notice-error' : ($type === 'warning' ? 'notice-warning' : 'notice-success');
    ?>
    <div class="notice <?php echo esc_attr($class); ?> is-dismissible">
        <p><?php echo esc_html((string) ($notice['message'] ?? '')); ?></p>
    </div>
    <?php
}
add_action('admin_notices', 'em_wp_admin_template_render_admin_notice', 3);

