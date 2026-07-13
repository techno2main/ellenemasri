<?php
/**
 * Sauvegarde admin em-site (admin.php, sans options.php).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var array<string, array<string, mixed>>
 */
$GLOBALS['em_site_admin_module_save_registry'] = [];

/**
 * Enregistre un handler de sauvegarde module.
 *
 * @param array{
 *     type?:string,
 *     nonce_action:callable|string,
 *     option_name:callable|string,
 *     page_slug:callable|string,
 *     sanitize:callable,
 *     value_field?:callable|string
 * } $config
 */
function em_site_admin_register_module_save(string $save_key, array $config): void
{
    $GLOBALS['em_site_admin_module_save_registry'][sanitize_key($save_key)] = $config;
}

/**
 * URL de soumission formulaire module.
 */
function em_site_admin_module_form_action(string $page_slug): string
{
    return add_query_arg(['page' => sanitize_key($page_slug)], admin_url('admin.php'));
}

/**
 * Champs cachés + nonce pour sauvegarde module.
 *
 * @param array<string, string> $extra_hidden
 */
function em_site_admin_render_form_save_fields(string $save_key, string $nonce_action, array $extra_hidden = []): void
{
    wp_nonce_field($nonce_action);
    wp_referer_field();
    ?>
    <input type="hidden" name="em_site_module_save" value="<?php echo esc_attr(sanitize_key($save_key)); ?>">
    <?php
    foreach ($extra_hidden as $name => $value) {
        $name = sanitize_key((string) $name);
        if ($name === '') {
            continue;
        }
        ?>
        <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr((string) $value); ?>">
        <?php
    }
}

/**
 * Redirect avec fallback HTML si les en-têtes sont déjà envoyés (évite page blanche).
 */
function em_site_admin_safe_redirect(string $url): void
{
    $url = esc_url_raw($url);
    if ($url === '') {
        return;
    }

    nocache_headers();

    if (!headers_sent()) {
        wp_safe_redirect($url);
        exit;
    }

    $escaped = esc_url($url);
    $json = wp_json_encode($url);

    echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
    echo '<meta http-equiv="refresh" content="0;url=' . esc_attr($escaped) . '">';
    echo '<title>' . esc_html__('Redirection…', 'em-site') . '</title>';
    echo '</head><body>';
    echo '<script>window.location.replace(' . $json . ');</script>';
    echo '<p><a href="' . esc_attr($escaped) . '">' . esc_html__('Continuer', 'em-site') . '</a></p>';
    echo '</body></html>';
    exit;
}

/**
 * Redirect vers une page module après enregistrement.
 */
function em_site_admin_redirect_after_module_save(string $page_slug): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $redirect_to = esc_url_raw(wp_unslash((string) ($_POST['em_site_redirect_after_save'] ?? '')));

    if ($redirect_to !== '' && wp_validate_redirect($redirect_to, false)) {
        em_site_admin_safe_redirect(add_query_arg('settings-updated', 'true', $redirect_to));
        return;
    }

    em_site_admin_safe_redirect(add_query_arg([
        'page'             => sanitize_key($page_slug),
        'settings-updated' => 'true',
    ], admin_url('admin.php')));
}

/**
 * Redirect vers referer ou page fallback.
 */
function em_site_admin_redirect_after_save_to_referer(string $fallback_page_slug): void
{
    $referer = wp_get_referer();
    $base = is_string($referer) && $referer !== '' && !str_contains($referer, 'options.php')
        ? $referer
        : em_site_admin_module_form_action($fallback_page_slug);

    em_site_admin_safe_redirect(add_query_arg('settings-updated', 'true', $base));
}

/**
 * Résout une config statique ou callable.
 *
 * @param mixed $value
 * @return mixed
 */
function em_site_admin_resolve_save_config_value($value)
{
    return is_callable($value) ? call_user_func($value) : $value;
}

/**
 * Traite toutes les sauvegardes modules (POST admin.php).
 */
function em_site_admin_handle_module_saves(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) !== 'POST') {
        return;
    }

    $save_key = sanitize_key((string) ($_POST['em_site_module_save'] ?? ''));
    if ($save_key === '') {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    $registry = $GLOBALS['em_site_admin_module_save_registry'] ?? [];
    if (!isset($registry[$save_key]) || !is_array($registry[$save_key])) {
        return;
    }

    $config = $registry[$save_key];
    $nonce_action = (string) em_site_admin_resolve_save_config_value($config['nonce_action'] ?? '');
    if ($nonce_action === '') {
        return;
    }

    check_admin_referer($nonce_action);

    $type = (string) ($config['type'] ?? 'options');
    $option_name = (string) em_site_admin_resolve_save_config_value($config['option_name'] ?? '');
    $sanitize = $config['sanitize'] ?? null;

    if ($type === 'rubrique_visibility') {
        $module_slug = sanitize_key((string) ($config['module_slug'] ?? ''));
        if ($module_slug !== '' && function_exists('em_site_admin_sync_rubrique_visibility_from_post')) {
            em_site_admin_sync_rubrique_visibility_from_post($module_slug);
        }
    } elseif ($type === 'active_style') {
        if ($option_name === '' || !is_callable($sanitize)) {
            return;
        }

        $value_field = (string) em_site_admin_resolve_save_config_value($config['value_field'] ?? $option_name);
        $raw = sanitize_key((string) ($_POST[$value_field] ?? ''));
        update_option($option_name, call_user_func($sanitize, $raw));
    } else {
        if ($option_name === '' || !is_callable($sanitize)) {
            return;
        }

        $value_field = (string) em_site_admin_resolve_save_config_value($config['value_field'] ?? $option_name);
        $input = isset($_POST[$value_field]) ? wp_unslash($_POST[$value_field]) : null;
        update_option($option_name, call_user_func($sanitize, $input));
    }

    $page_slug = em_site_admin_resolve_save_config_value($config['page_slug'] ?? '');
    if ($page_slug === 'referer') {
        $fallback = (string) em_site_admin_resolve_save_config_value($config['fallback_page'] ?? '');
        em_site_admin_redirect_after_save_to_referer($fallback !== '' ? $fallback : 'em-site-rubriques');
        return;
    }

    if ($page_slug !== '') {
        em_site_admin_redirect_after_module_save((string) $page_slug);
    }
}

add_action('admin_init', 'em_site_admin_handle_module_saves', 1);

/**
 * Evite la page blanche admin-post.php quand un submit part sans action.
 *
 * Dans ce cas on revient au referer avec un flag d'erreur exploitable.
 */
function em_site_admin_guard_empty_admin_post_action(): void
{
    if (!is_admin()) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $script = sanitize_key((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script === '' || !str_ends_with($script, '/wp-admin/admin-post.php')) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $action = sanitize_key((string) ($_REQUEST['action'] ?? ''));
    if ($action !== '') {
        return;
    }

    $referer = wp_get_referer();
    $fallback = function_exists('em_site_overview_redirect_url')
        ? em_site_overview_redirect_url(['error' => 'missing_action'])
        : admin_url('admin.php?page=em-rubriques-overview&error=missing_action');

    $target = is_string($referer) && $referer !== '' ? $referer : $fallback;
    em_site_admin_safe_redirect(add_query_arg('error', 'missing_action', $target));
}
add_action('admin_init', 'em_site_admin_guard_empty_admin_post_action', 0);

/**
 * Affiche les notices après enregistrement.
 */
function em_site_admin_render_settings_notices(): void
{
    em_site_admin_render_save_notice();
    settings_errors();
}

/**
 * Bandeau « template en édition » (sous l'en-tête de page).
 */
function em_site_admin_render_template_editing_banner(): void
{
    if (function_exists('em_site_admin_template_render_banner')) {
        em_site_admin_template_render_banner();
    }
}

/**
 * Bandeau succès après redirect admin.php?page=…&settings-updated=true.
 */
function em_site_admin_render_save_notice(): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (empty($_GET['settings-updated'])) {
        return;
    }

    echo '<div class="notice notice-success is-dismissible"><p>'
        . esc_html__('Réglages enregistrés.', 'em-site')
        . '</p></div>';
}
