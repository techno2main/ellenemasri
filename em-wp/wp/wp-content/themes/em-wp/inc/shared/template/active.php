<?php
/**
 * Template actif (live front) et template en édition (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress : slug du template live sur le site.
 */
function em_wp_active_template_option_name(): string
{
    return 'em_wp_active_template';
}

/**
 * Meta utilisateur : slug du template en cours d'édition dans l'admin.
 */
function em_wp_editing_template_user_meta_key(): string
{
    return 'em_wp_editing_template_slug';
}

/**
 * Slug du template actif sur le site (front live).
 */
function em_wp_get_active_template_slug(): string
{
    em_wp_template_maybe_bootstrap_options();

    $slug = em_wp_template_sanitize_slug((string) get_option(em_wp_active_template_option_name(), ''));

    if ($slug !== '' && em_wp_template_exists($slug)) {
        return $slug;
    }

    return em_wp_template_default_slug();
}

/**
 * Définit le template actif sur le site.
 *
 * @return true|WP_Error
 */
function em_wp_set_active_template_slug(string $slug)
{
    $slug = em_wp_template_sanitize_slug($slug);

    if ($slug === '' || !em_wp_template_exists($slug)) {
        return new WP_Error('em_wp_template_invalid_active', __('Template invalide.', 'em-wp'));
    }

    update_option(em_wp_active_template_option_name(), $slug, false);

    return true;
}

/**
 * Slug du template en édition (bandeau admin).
 */
function em_wp_get_editing_template_slug(): string
{
    $user_id = get_current_user_id();

    if ($user_id > 0) {
        $saved = get_user_meta($user_id, em_wp_editing_template_user_meta_key(), true);

        if (is_string($saved) && $saved !== '') {
            $slug = em_wp_template_sanitize_slug($saved);

            if ($slug !== '' && em_wp_template_exists($slug)) {
                return $slug;
            }
        }
    }

    return em_wp_get_active_template_slug();
}

/**
 * Définit le template en édition pour l'utilisateur courant.
 *
 * @return true|WP_Error
 */
function em_wp_set_editing_template_slug(string $slug)
{
    $slug = em_wp_template_sanitize_slug($slug);
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return new WP_Error('em_wp_template_no_user', __('Utilisateur non connecté.', 'em-wp'));
    }

    if ($slug === '' || !em_wp_template_exists($slug)) {
        return new WP_Error('em_wp_template_invalid_editing', __('Template invalide.', 'em-wp'));
    }

    update_user_meta($user_id, em_wp_editing_template_user_meta_key(), $slug);

    return true;
}

/**
 * Indique si le template en édition diffère du template live.
 */
function em_wp_template_editing_differs_from_live(): bool
{
    return em_wp_get_editing_template_slug() !== em_wp_get_active_template_slug();
}

/**
 * Libellé du template en cours d'édition (admin).
 */
function em_wp_get_editing_template_label(): string
{
    $slug = em_wp_get_editing_template_slug();
    $template = em_wp_template_get($slug);

    if ($template !== null) {
        $label = sanitize_text_field((string) ($template['label'] ?? ''));

        if ($label !== '') {
            return $label;
        }
    }

    return $slug;
}
