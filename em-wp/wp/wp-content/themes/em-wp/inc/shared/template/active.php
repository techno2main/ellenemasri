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
 * Action du nonce utilisé pour l'aperçu d'un template (front).
 */
function em_wp_template_preview_nonce_action(): string
{
    return 'em_wp_preview_template';
}

/**
 * Slug du template demandé en aperçu sur le front.
 *
 * Permet de prévisualiser un template sans le passer en live. N'est honoré que
 * sur le front, pour un utilisateur autorisé (manage_options) et avec un nonce
 * valide. Retourne '' si aucun aperçu valide n'est demandé.
 */
function em_wp_get_preview_template_slug(): string
{
    static $resolved = null;

    if ($resolved !== null) {
        return $resolved;
    }

    // Tant que les fonctions « pluggable » ne sont pas disponibles, on ne met
    // pas en cache : la résolution sera retentée plus tard dans la requête.
    if (is_admin()
        || !function_exists('current_user_can')
        || !function_exists('wp_verify_nonce')
    ) {
        return '';
    }

    $resolved = '';

    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    if (empty($_GET['em_wp_preview_template']) || empty($_GET['em_wp_preview_nonce'])) {
        return $resolved;
    }

    if (!current_user_can('manage_options')) {
        return $resolved;
    }

    $nonce = sanitize_text_field(wp_unslash($_GET['em_wp_preview_nonce']));

    if (!wp_verify_nonce($nonce, em_wp_template_preview_nonce_action())) {
        return $resolved;
    }

    $preview = em_wp_template_sanitize_slug((string) wp_unslash($_GET['em_wp_preview_template']));
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    if ($preview !== '' && em_wp_template_exists($preview)) {
        $resolved = $preview;
    }

    return $resolved;
}

/**
 * Indique si la requête front courante est un aperçu de template.
 */
function em_wp_front_is_template_preview(): bool
{
    return em_wp_get_preview_template_slug() !== '';
}

/**
 * Retourne une URL en conservant le contexte d'aperçu (slug + nonce) si actif.
 *
 * Permet aux liens internes (ex. logo top-bar) de rester dans l'aperçu plutôt
 * que de renvoyer vers le site live.
 */
function em_wp_front_preview_aware_url(string $url): string
{
    $preview_slug = em_wp_get_preview_template_slug();

    if ($preview_slug === '') {
        return $url;
    }

    return add_query_arg(
        [
            'em_wp_preview_template' => $preview_slug,
            'em_wp_preview_nonce'    => wp_create_nonce(em_wp_template_preview_nonce_action()),
        ],
        $url
    );
}

/**
 * Slug du template actif sur le site (front live).
 */
function em_wp_get_active_template_slug(): string
{
    em_wp_template_maybe_bootstrap_options();

    $preview = em_wp_get_preview_template_slug();

    if ($preview !== '') {
        return $preview;
    }

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
 * Indique si l'utilisateur a explicitement choisi un template en édition.
 */
function em_wp_admin_has_template_context(): bool
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return false;
    }

    $saved = get_user_meta($user_id, em_wp_editing_template_user_meta_key(), true);

    if (!is_string($saved) || $saved === '') {
        return false;
    }

    $slug = em_wp_template_sanitize_slug($saved);

    return $slug !== '' && em_wp_template_exists($slug);
}

/**
 * Slug du template en édition enregistré explicitement (sans fallback).
 */
function em_wp_get_explicit_editing_template_slug(): string
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return '';
    }

    $saved = get_user_meta($user_id, em_wp_editing_template_user_meta_key(), true);

    if (!is_string($saved) || $saved === '') {
        return '';
    }

    $slug = em_wp_template_sanitize_slug($saved);

    if ($slug !== '' && em_wp_template_exists($slug)) {
        return $slug;
    }

    return '';
}

/**
 * Efface le contexte template en édition (retour zone neutre).
 */
function em_wp_clear_editing_template_context(): void
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return;
    }

    delete_user_meta($user_id, em_wp_editing_template_user_meta_key());
}

/**
 * Slug du template en édition (bandeau admin).
 *
 * Sans contexte explicite, retombe sur le template actif (saves / modules).
 */
function em_wp_get_editing_template_slug(): string
{
    $explicit = em_wp_get_explicit_editing_template_slug();

    if ($explicit !== '') {
        return $explicit;
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
