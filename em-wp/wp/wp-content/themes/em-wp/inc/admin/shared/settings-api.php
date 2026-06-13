<?php
/**
 * Sauvegarde admin em-wp (admin.php, sans options.php).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var array<string, array<string, mixed>>
 */
$GLOBALS['em_wp_admin_module_save_registry'] = [];

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
function em_wp_admin_register_module_save(string $save_key, array $config): void
{
    $GLOBALS['em_wp_admin_module_save_registry'][sanitize_key($save_key)] = $config;
}

/**
 * URL de soumission formulaire module.
 */
function em_wp_admin_module_form_action(string $page_slug): string
{
    return add_query_arg(['page' => sanitize_key($page_slug)], admin_url('admin.php'));
}

/**
 * Champs cachés + nonce pour sauvegarde module.
 *
 * @param array<string, string> $extra_hidden
 */
function em_wp_admin_render_form_save_fields(string $save_key, string $nonce_action, array $extra_hidden = []): void
{
    wp_nonce_field($nonce_action);
    wp_referer_field();
    ?>
    <input type="hidden" name="em_wp_module_save" value="<?php echo esc_attr(sanitize_key($save_key)); ?>">
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
function em_wp_admin_safe_redirect(string $url): void
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
    echo '<title>' . esc_html__('Redirection…', 'em-wp') . '</title>';
    echo '</head><body>';
    echo '<script>window.location.replace(' . $json . ');</script>';
    echo '<p><a href="' . esc_attr($escaped) . '">' . esc_html__('Continuer', 'em-wp') . '</a></p>';
    echo '</body></html>';
    exit;
}

/**
 * Redirect vers une page module après enregistrement.
 */
function em_wp_admin_redirect_after_module_save(string $page_slug): void
{
    em_wp_admin_safe_redirect(add_query_arg([
        'page'             => sanitize_key($page_slug),
        'settings-updated' => 'true',
    ], admin_url('admin.php')));
}

/**
 * Redirect vers referer ou page fallback.
 */
function em_wp_admin_redirect_after_save_to_referer(string $fallback_page_slug): void
{
    $referer = wp_get_referer();
    $base = is_string($referer) && $referer !== '' && !str_contains($referer, 'options.php')
        ? $referer
        : em_wp_admin_module_form_action($fallback_page_slug);

    em_wp_admin_safe_redirect(add_query_arg('settings-updated', 'true', $base));
}

/**
 * Résout une config statique ou callable.
 *
 * @param mixed $value
 * @return mixed
 */
function em_wp_admin_resolve_save_config_value($value)
{
    return is_callable($value) ? call_user_func($value) : $value;
}

/**
 * Traite toutes les sauvegardes modules (POST admin.php).
 */
function em_wp_admin_handle_module_saves(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) !== 'POST') {
        return;
    }

    $save_key = sanitize_key((string) ($_POST['em_wp_module_save'] ?? ''));
    if ($save_key === '') {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    $registry = $GLOBALS['em_wp_admin_module_save_registry'] ?? [];
    if (!isset($registry[$save_key]) || !is_array($registry[$save_key])) {
        return;
    }

    $config = $registry[$save_key];
    $nonce_action = (string) em_wp_admin_resolve_save_config_value($config['nonce_action'] ?? '');
    if ($nonce_action === '') {
        return;
    }

    check_admin_referer($nonce_action);

    $option_name = (string) em_wp_admin_resolve_save_config_value($config['option_name'] ?? '');
    $sanitize = $config['sanitize'] ?? null;
    if ($option_name === '' || !is_callable($sanitize)) {
        return;
    }

    $type = (string) ($config['type'] ?? 'options');

    if ($type === 'active_style') {
        $value_field = (string) em_wp_admin_resolve_save_config_value($config['value_field'] ?? $option_name);
        $raw = sanitize_key((string) ($_POST[$value_field] ?? ''));
        update_option($option_name, call_user_func($sanitize, $raw));
    } else {
        $input = isset($_POST[$option_name]) ? wp_unslash($_POST[$option_name]) : null;
        update_option($option_name, call_user_func($sanitize, $input));
    }

    $page_slug = em_wp_admin_resolve_save_config_value($config['page_slug'] ?? '');
    if ($page_slug === 'referer') {
        $fallback = (string) em_wp_admin_resolve_save_config_value($config['fallback_page'] ?? '');
        em_wp_admin_redirect_after_save_to_referer($fallback !== '' ? $fallback : 'em-wp-rubriques');
        return;
    }

    if ($page_slug !== '') {
        em_wp_admin_redirect_after_module_save((string) $page_slug);
    }
}

add_action('admin_init', 'em_wp_admin_handle_module_saves', 1);

/**
 * Affiche les notices après enregistrement.
 */
function em_wp_admin_render_settings_notices(): void
{
    em_wp_admin_render_save_notice();
    settings_errors();
}

/**
 * Bandeau succès après redirect admin.php?page=…&settings-updated=true.
 */
function em_wp_admin_render_save_notice(): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (empty($_GET['settings-updated'])) {
        return;
    }

    echo '<div class="notice notice-success is-dismissible"><p>'
        . esc_html__('Réglages enregistrés.', 'em-wp')
        . '</p></div>';
}
