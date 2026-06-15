<?php
/**
 * Actions admin — modules catalogue personnalisés.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_custom_catalog_module_actions_nonce_action(): string
{
    return 'em_wp_custom_catalog_module_actions';
}

function em_wp_custom_catalog_entry_actions_nonce_action(): string
{
    return 'em_wp_custom_catalog_entry_actions';
}

function em_wp_custom_catalog_is_catalog_admin_page(string $page_slug): bool
{
    $page_slug = sanitize_key($page_slug);

    if ($page_slug === '') {
        return false;
    }

    if ($page_slug === em_wp_catalog_parent_menu_slug()) {
        return true;
    }

    if (in_array($page_slug, em_wp_catalog_registered_hub_menu_slugs(), true)) {
        return true;
    }

    return em_wp_custom_catalog_module_slug_from_hub($page_slug) !== '';
}

function em_wp_custom_catalog_handle_module_actions(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $action = sanitize_key((string) ($_POST['em_wp_custom_catalog_module_action'] ?? ''));

    if (!in_array($action, ['create', 'update'], true)) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if (!em_wp_custom_catalog_is_catalog_admin_page($page_slug)) {
        return;
    }

    if (
        !isset($_POST['_wpnonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['_wpnonce'])), em_wp_custom_catalog_module_actions_nonce_action())
    ) {
        wp_die(esc_html__('Action non autorisée.', 'em-wp'));
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $label = sanitize_text_field((string) ($_POST['em_wp_custom_catalog_module_label'] ?? ''));
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $position = sanitize_key((string) ($_POST['em_wp_custom_catalog_module_position'] ?? '__end__'));

    if ($action === 'create') {
        $result = em_wp_custom_catalog_create_module($label, $position);
        $redirect_args = ['page' => em_wp_catalog_parent_menu_slug()];

        if (is_wp_error($result)) {
            $redirect_args['custom_module_error'] = $result->get_error_code();
            $redirect_args['custom_module_message'] = rawurlencode($result->get_error_message());
        } else {
            $module_slug = (string) $result;
            $redirect_args = [
                'page'                   => em_wp_custom_catalog_hub_menu_slug($module_slug),
                'custom_module_notice'   => 'created',
            ];
        }
    } else {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $module_slug = sanitize_key((string) ($_POST['em_wp_custom_catalog_module_slug'] ?? ''));
        $result = em_wp_catalog_update_module_settings($module_slug, $label, $position);
        $redirect_args = ['page' => em_wp_catalog_parent_menu_slug()];

        if (is_wp_error($result)) {
            $redirect_args['custom_module_error'] = $result->get_error_code();
            $redirect_args['custom_module_message'] = rawurlencode($result->get_error_message());
        } else {
            $redirect_args['custom_module_notice'] = 'updated';
        }
    }

    wp_safe_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
    exit;
}
add_action('admin_init', 'em_wp_custom_catalog_handle_module_actions');

function em_wp_custom_catalog_handle_entry_registry_actions(): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
    $module_slug = em_wp_custom_catalog_module_slug_from_hub($page_slug);

    if ($module_slug === '') {
        return;
    }

    em_wp_catalog_handle_registry_actions([
        'hub_menu_slug'  => 'em_wp_custom_catalog_current_hub_menu_slug',
        'nonce_action'   => 'em_wp_custom_catalog_entry_actions_nonce_action',
        'post_prefix'    => 'em_wp_custom_catalog_entry',
        'edit_page_slug' => 'em_wp_custom_catalog_edit_page_slug_for_module',
        'create'         => static function (string $label) use ($module_slug) {
            return em_wp_custom_catalog_entry_create($module_slug, $label);
        },
        'rename'         => static function (string $old_slug, string $label) use ($module_slug) {
            return em_wp_custom_catalog_entry_rename($module_slug, $old_slug, $label);
        },
        'delete'         => static function (string $slug) use ($module_slug) {
            return em_wp_custom_catalog_entry_delete($module_slug, $slug);
        },
        'notice_prefix'  => 'custom_catalog',
        'labels'         => [
            'created' => __('Entrée créée.', 'em-wp'),
            'renamed' => __('Entrée renommée. L\'identifiant a été mis à jour si nécessaire.', 'em-wp'),
            'deleted' => __('Entrée supprimée.', 'em-wp'),
        ],
    ]);
}
add_action('admin_init', 'em_wp_custom_catalog_handle_entry_registry_actions');

function em_wp_custom_catalog_register_entry_settings(): void
{
    foreach (array_keys(em_wp_custom_catalog_modules()) as $module_slug) {
        foreach (array_keys(em_wp_custom_catalog_entries($module_slug)) as $entry_slug) {
            $group = em_wp_custom_catalog_entry_group_name($module_slug, $entry_slug);
            $option = em_wp_custom_catalog_entry_option_name($module_slug, $entry_slug);

            register_setting(
                $group,
                $option,
                [
                    'type'              => 'array',
                    'sanitize_callback' => static function ($input) use ($module_slug): array {
                        return em_wp_custom_catalog_sanitize_entry_options($module_slug, $input);
                    },
                    'default'           => em_wp_custom_catalog_entry_default_options($module_slug),
                ]
            );
        }
    }
}
add_action('admin_init', 'em_wp_custom_catalog_register_entry_settings');

/**
 * Réaligne les identifiants d'entrées (contact-mayami…) à chaque chargement admin.
 */
function em_wp_custom_catalog_maybe_normalize_entry_slugs_on_admin(): void
{
    if (!is_admin()) {
        return;
    }

    if (function_exists('em_wp_contacts_catalog_maybe_migrate_legacy_module_slug')) {
        em_wp_contacts_catalog_maybe_migrate_legacy_module_slug();
    }

    if (function_exists('em_wp_custom_catalog_maybe_normalize_all_entry_slugs')) {
        em_wp_custom_catalog_maybe_normalize_all_entry_slugs();
    }
}
add_action('admin_init', 'em_wp_custom_catalog_maybe_normalize_entry_slugs_on_admin', 5);

/**
 * Redirige les anciennes URLs admin Contacts (custom-contacts → contacts).
 */
function em_wp_contacts_catalog_maybe_redirect_legacy_admin_pages(): void
{
    if (!is_admin()) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug === '') {
        return;
    }

    $legacy_module = em_wp_contacts_catalog_legacy_module_slug();
    $module_slug = em_wp_contacts_catalog_module_slug();
    $legacy_hub = em_wp_custom_catalog_hub_menu_slug($legacy_module);
    $hub = em_wp_contacts_catalog_hub_menu_slug();

    if ($page_slug === $legacy_hub) {
        wp_safe_redirect(add_query_arg(['page' => $hub], admin_url('admin.php')));
        exit;
    }

    $legacy_edit_prefix = 'em-wp-cced-' . $legacy_module . '-';
    $edit_prefix = 'em-wp-cced-' . $module_slug . '-';

    if (str_starts_with($page_slug, $legacy_edit_prefix)) {
        $suffix = substr($page_slug, strlen($legacy_edit_prefix));
        wp_safe_redirect(add_query_arg(['page' => $edit_prefix . $suffix], admin_url('admin.php')));
        exit;
    }
}
add_action('admin_init', 'em_wp_contacts_catalog_maybe_redirect_legacy_admin_pages', 11);

/**
 * Redirige les anciennes URLs d'édition vers le slug canonique de l'entrée.
 */
function em_wp_custom_catalog_maybe_redirect_canonical_entry_page(): void
{
    if (!is_admin()) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug === '' || !str_starts_with($page_slug, 'em-wp-cced-')) {
        return;
    }

    $resolved = em_wp_custom_catalog_entry_from_page($page_slug);
    $module_slug = (string) ($resolved['module_slug'] ?? '');
    $parsed_slug = (string) ($resolved['entry_slug'] ?? '');

    if ($module_slug === '' || $parsed_slug === '' || !function_exists('em_wp_custom_catalog_resolve_entry_slug')) {
        return;
    }

    $canonical = em_wp_custom_catalog_resolve_entry_slug($module_slug, $parsed_slug);

    if ($canonical === '' || $canonical === $parsed_slug) {
        return;
    }

    wp_safe_redirect(
        add_query_arg(
            ['page' => em_wp_custom_catalog_edit_page_slug($module_slug, $canonical)],
            admin_url('admin.php')
        )
    );
    exit;
}
add_action('admin_init', 'em_wp_custom_catalog_maybe_redirect_canonical_entry_page', 12);

/**
 * Corrige les icônes génériques des modules custom déjà enregistrés (ex. CONTACTS).
 */
function em_wp_custom_catalog_maybe_normalize_module_icons(): void
{
    if (!is_admin() || !function_exists('em_wp_catalog_guess_module_icon_from_label')) {
        return;
    }

    $saved = get_option(em_wp_custom_catalog_modules_option_name(), []);

    if (!is_array($saved) || $saved === []) {
        return;
    }

    $changed = false;

    foreach ($saved as $slug => $module) {
        if (!is_array($module)) {
            continue;
        }

        $slug = sanitize_key((string) $slug);
        $icon = sanitize_key((string) ($module['icon'] ?? ''));

        if ($slug === '' || $icon !== 'dashicons-admin-generic') {
            continue;
        }

        $guessed = em_wp_catalog_guess_module_icon_from_label(
            (string) ($module['label'] ?? ''),
            $slug
        );

        if ($guessed === 'dashicons-admin-generic') {
            continue;
        }

        $saved[$slug]['icon'] = $guessed;
        $changed = true;
    }

    if ($changed) {
        update_option(em_wp_custom_catalog_modules_option_name(), $saved, false);
    }
}
add_action('admin_init', 'em_wp_custom_catalog_maybe_normalize_module_icons', 8);
