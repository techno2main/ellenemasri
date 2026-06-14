<?php
/**
 * Actions CRUD — sommaire Heros catalogue.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nonce des actions registre hero.
 */
function em_wp_hero_catalog_actions_nonce_action(): string
{
    return 'em_wp_hero_catalog_actions';
}

/**
 * Traite create / rename / delete depuis le sommaire MES HEROS.
 */
function em_wp_hero_catalog_handle_registry_actions(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $action = sanitize_key((string) ($_POST['em_wp_hero_catalog_action'] ?? ''));

    if ($action === '') {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug !== em_wp_hero_hub_menu_slug()) {
        return;
    }

    if (
        !isset($_POST['_wpnonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['_wpnonce'])), em_wp_hero_catalog_actions_nonce_action())
    ) {
        wp_die(esc_html__('Action non autorisée.', 'em-wp'));
    }

    $redirect_args = ['page' => em_wp_hero_hub_menu_slug()];
    $result_slug = '';
    $notice = 'error';

    if ($action === 'create') {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $label = sanitize_text_field((string) ($_POST['em_wp_hero_catalog_label'] ?? ''));
        $created = em_wp_hero_catalog_create($label);

        if (is_wp_error($created)) {
            $redirect_args['hero_catalog_error'] = $created->get_error_code();
            $redirect_args['hero_catalog_message'] = rawurlencode($created->get_error_message());
        } else {
            $result_slug = (string) $created;
            $notice = 'created';
            wp_safe_redirect(
                add_query_arg(
                    [
                        'page'              => em_wp_hero_catalog_edit_page_slug($result_slug),
                        'hero_catalog_notice' => $notice,
                    ],
                    admin_url('admin.php')
                )
            );
            exit;
        }
    } elseif ($action === 'rename') {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $old_slug = sanitize_key((string) ($_POST['em_wp_hero_catalog_slug'] ?? ''));
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $label = sanitize_text_field((string) ($_POST['em_wp_hero_catalog_label'] ?? ''));
        $renamed = em_wp_hero_catalog_rename($old_slug, $label);

        if (is_wp_error($renamed)) {
            $redirect_args['hero_catalog_error'] = $renamed->get_error_code();
            $redirect_args['hero_catalog_message'] = rawurlencode($renamed->get_error_message());
        } else {
            $result_slug = (string) $renamed;
            $notice = 'renamed';
            $redirect_args['hero_catalog_notice'] = $notice;
        }
    } elseif ($action === 'delete') {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $slug = sanitize_key((string) ($_POST['em_wp_hero_catalog_slug'] ?? ''));
        $deleted = em_wp_hero_catalog_delete($slug);

        if (is_wp_error($deleted)) {
            $redirect_args['hero_catalog_error'] = $deleted->get_error_code();
            $redirect_args['hero_catalog_message'] = rawurlencode($deleted->get_error_message());
        } else {
            $notice = 'deleted';
            $redirect_args['hero_catalog_notice'] = $notice;
        }
    }

    wp_safe_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
    exit;
}
add_action('admin_init', 'em_wp_hero_catalog_handle_registry_actions');

/**
 * Affiche les notices du sommaire hero.
 */
function em_wp_hero_catalog_render_admin_notices(): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $notice = sanitize_key((string) ($_GET['hero_catalog_notice'] ?? ''));
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $error = sanitize_key((string) ($_GET['hero_catalog_error'] ?? ''));
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $message = isset($_GET['hero_catalog_message'])
        ? sanitize_text_field(rawurldecode((string) wp_unslash($_GET['hero_catalog_message'])))
        : '';

    if ($notice === 'renamed') {
        echo '<div class="notice notice-success is-dismissible"><p>'
            . esc_html__('Hero renommé. L\'identifiant a été mis à jour si nécessaire.', 'em-wp')
            . '</p></div>';
    } elseif ($notice === 'deleted') {
        echo '<div class="notice notice-success is-dismissible"><p>'
            . esc_html__('Hero supprimé.', 'em-wp')
            . '</p></div>';
    } elseif ($notice === 'created') {
        echo '<div class="notice notice-success is-dismissible"><p>'
            . esc_html__('Hero créé.', 'em-wp')
            . '</p></div>';
    }

    if ($error !== '') {
        $text = $message !== '' ? $message : __('Une erreur est survenue.', 'em-wp');
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($text) . '</p></div>';
    }
}
