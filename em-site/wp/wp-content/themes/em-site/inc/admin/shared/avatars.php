<?php
/**
 * Avatars admin personnalisés (alignés prod : client-admin, admin-my).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Résout le login WordPress depuis la cible get_avatar().
 */
function em_wp_admin_resolve_user_login_from_avatar_subject($id_or_email): string
{
    if ($id_or_email instanceof WP_User) {
        return (string) $id_or_email->user_login;
    }

    if ($id_or_email instanceof WP_Comment) {
        if (!empty($id_or_email->user_id)) {
            $user = get_user_by('id', (int) $id_or_email->user_id);

            return $user ? (string) $user->user_login : '';
        }

        if (!empty($id_or_email->comment_author_email)) {
            $user = get_user_by('email', (string) $id_or_email->comment_author_email);

            return $user ? (string) $user->user_login : '';
        }

        return '';
    }

    if (is_numeric($id_or_email)) {
        $user = get_user_by('id', (int) $id_or_email);

        return $user ? (string) $user->user_login : '';
    }

    if (is_string($id_or_email) && is_email($id_or_email)) {
        $user = get_user_by('email', $id_or_email);

        return $user ? (string) $user->user_login : '';
    }

    return '';
}

/**
 * URL locale de l'avatar admin-my (TAD).
 */
function em_wp_admin_my_avatar_url(int $size = 96): string
{
    unset($size);

    $relative_path = 'uploads/2026/06/TAD.jpg';
    $absolute_path = WP_CONTENT_DIR . '/uploads/2026/06/TAD.jpg';

    if (!is_readable($absolute_path)) {
        return '';
    }

    return content_url($relative_path);
}

/**
 * Personnalise les avatars des comptes admin em-wp.
 *
 * @param array<string, mixed> $args
 * @param mixed                $id_or_email
 * @return array<string, mixed>
 */
function em_wp_admin_customize_account_avatars(array $args, $id_or_email): array
{
    $user_login = strtolower(em_wp_admin_resolve_user_login_from_avatar_subject($id_or_email));

    if ($user_login === '') {
        return $args;
    }

    $custom_url = '';

    if (in_array($user_login, ['admin-ellene'], true)) {
        $custom_url = get_site_icon_url((int) ($args['size'] ?? 96));

        if (!$custom_url) {
            $custom_url = get_site_icon_url(96);
        }
    } elseif (in_array($user_login, ['admin-tyson'], true)) {
        $custom_url = em_wp_admin_my_avatar_url((int) ($args['size'] ?? 96));

        if ($custom_url === '') {
            $custom_url = get_site_icon_url((int) ($args['size'] ?? 96));

            if (!$custom_url) {
                $custom_url = get_site_icon_url(96);
            }
        }
    }

    if ($custom_url === '') {
        return $args;
    }

    $args['url'] = esc_url_raw($custom_url);
    $args['found_avatar'] = true;

    return $args;
}
add_filter('pre_get_avatar_data', 'em_wp_admin_customize_account_avatars', 20, 2);
