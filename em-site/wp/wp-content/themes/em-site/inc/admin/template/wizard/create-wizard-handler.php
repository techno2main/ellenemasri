<?php
/**
 * Handler POST create_wizard (persistance atomique).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Décode le payload wizard depuis POST.
 *
 * @return array<string, mixed>|WP_Error
 */
function em_wp_admin_template_wizard_decode_payload()
{
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $raw = (string) ($_POST['em_wp_template_wizard_payload'] ?? '');

    if ($raw === '') {
        return new WP_Error('em_wp_wizard_empty_payload', __('Configuration du template manquante.', 'em-wp'));
    }

    $payload = json_decode(wp_unslash($raw), true);

    if (!is_array($payload)) {
        return new WP_Error('em_wp_wizard_invalid_payload', __('Configuration du template invalide.', 'em-wp'));
    }

    return $payload;
}

/**
 * Persiste squelette + options depuis le payload wizard.
 *
 * @param array<string, mixed> $payload
 * @return true|WP_Error
 */
function em_wp_admin_template_wizard_persist_configuration(string $template_slug, array $payload)
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);
    $skeleton = is_array($payload['skeleton'] ?? null) ? $payload['skeleton'] : [];
    $order = is_array($skeleton['order'] ?? null) ? $skeleton['order'] : [];
    $rubriques = is_array($skeleton['rubriques'] ?? null) ? $skeleton['rubriques'] : [];
    $catalog = is_array($payload['catalog'] ?? null) ? $payload['catalog'] : [];

    $order = array_values(array_filter(array_map('sanitize_key', $order)));

    if ($order === [] || !in_array('header', $order, true)) {
        return new WP_Error('em_wp_wizard_invalid_skeleton', __('Squelette invalide : HEADER requis.', 'em-wp'));
    }

    if (!function_exists('em_wp_save_template_skeleton_order')) {
        return new WP_Error('em_wp_wizard_missing_plan', __('Module squelette indisponible.', 'em-wp'));
    }

    $saved_order = em_wp_save_template_skeleton_order($template_slug, $order);

    if ($saved_order === []) {
        return new WP_Error('em_wp_wizard_skeleton_save', __('Impossible d’enregistrer le squelette.', 'em-wp'));
    }

    foreach ($saved_order as $rubrique_slug) {
        $rubrique_slug = sanitize_key((string) $rubrique_slug);
        $style = is_array($rubriques[$rubrique_slug] ?? null) ? $rubriques[$rubrique_slug] : [];
        $catalog_row = is_array($catalog[$rubrique_slug] ?? null) ? $catalog[$rubrique_slug] : [];
        $options = function_exists('em_wp_admin_rubrique_default_template_options')
            ? em_wp_admin_rubrique_default_template_options($rubrique_slug, $template_slug)
            : ['enabled' => true, 'background_color' => '', 'text_color' => ''];

        if (isset($style['enabled'])) {
            $options['enabled'] = (bool) $style['enabled'];
        }

        $background = sanitize_hex_color((string) ($style['background_color'] ?? ''));
        $text = sanitize_hex_color((string) ($style['text_color'] ?? ''));

        if ($background) {
            $options['background_color'] = $background;
        }

        if ($text) {
            $options['text_color'] = $text;
        }

        if ($rubrique_slug === 'header') {
            $options['hero_slug'] = sanitize_key((string) ($catalog_row['hero_slug'] ?? ''));
            $options['slider_slug'] = sanitize_key((string) ($catalog_row['slider_slug'] ?? ''));
            $layout = sanitize_key((string) ($catalog_row['layout'] ?? 'hero_left'));
            $options['layout'] = in_array($layout, ['hero_left', 'slider_left'], true) ? $layout : 'hero_left';
        } elseif ($catalog_row !== [] && function_exists('em_wp_admin_rubrique_catalog_pointer_key')) {
            $pointer = em_wp_admin_rubrique_catalog_pointer_key($rubrique_slug);
            $options[$pointer] = sanitize_key((string) ($catalog_row[$pointer] ?? ''));
        }

        if (function_exists('em_wp_save_template_rubrique_options')) {
            em_wp_save_template_rubrique_options($template_slug, $rubrique_slug, $options);
        }
    }

    return true;
}

/**
 * Crée un template via le wizard (POST atomique).
 */
function em_wp_admin_template_handle_create_wizard(): void
{
    if (function_exists('em_wp_template_unique_mode_enabled') && em_wp_template_unique_mode_enabled()) {
        $redirect = function_exists('em_wp_admin_template_choice_admin_url')
            ? em_wp_admin_template_choice_admin_url()
            : em_wp_admin_templates_manage_admin_url();

        em_wp_admin_template_redirect_with_notice(
            $redirect,
            'warning',
            __('Mode template unique actif : assistant de création désactivé.', 'em-wp')
        );
    }

    check_admin_referer('em_wp_template_create_wizard');

    $payload = em_wp_admin_template_wizard_decode_payload();

    $error_redirect = function_exists('em_wp_admin_template_create_admin_url')
        ? em_wp_admin_template_create_admin_url()
        : em_wp_admin_templates_manage_admin_url();

    if (is_wp_error($payload)) {
        em_wp_admin_template_redirect_with_notice(
            $error_redirect,
            'error',
            $payload->get_error_message()
        );
    }

    $label = sanitize_text_field((string) ($payload['label'] ?? ''));
    $color = sanitize_text_field((string) ($payload['color'] ?? ''));

    if ($label === '') {
        em_wp_admin_template_redirect_with_notice(
            $error_redirect,
            'error',
            __('Le nom du template est requis.', 'em-wp')
        );
    }

    if ($color === '') {
        em_wp_admin_template_redirect_with_notice(
            $error_redirect,
            'error',
            __('La couleur du template est requise.', 'em-wp')
        );
    }

    $created = em_wp_template_create($label, $color);

    if (is_wp_error($created)) {
        em_wp_admin_template_redirect_with_notice(
            $error_redirect,
            'error',
            $created->get_error_message()
        );
    }

    $template_slug = sanitize_key((string) ($created['slug'] ?? ''));
    $persist = em_wp_admin_template_wizard_persist_configuration($template_slug, $payload);

    if (is_wp_error($persist)) {
        em_wp_template_delete($template_slug);
        em_wp_admin_template_redirect_with_notice(
            $error_redirect,
            'error',
            $persist->get_error_message()
        );
    }

    if (function_exists('em_wp_set_editing_template_slug')) {
        em_wp_set_editing_template_slug($template_slug);
    }

    $redirect = function_exists('em_wp_admin_rubriques_admin_url')
        ? em_wp_admin_rubriques_admin_url()
        : em_wp_admin_templates_manage_admin_url();

    em_wp_admin_template_redirect_with_notice(
        $redirect,
        'success',
        __('Template créé.', 'em-wp')
    );
}

/**
 * AJAX — aperçu wireframe depuis brouillon.
 */
function em_wp_ajax_template_wizard_wireframe(): void
{
    check_ajax_referer('em_wp_template_wizard_wireframe', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Accès refusé.', 'em-wp')], 403);
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $raw = (string) ($_POST['payload'] ?? '');
    $payload = json_decode(wp_unslash($raw), true);

    if (!is_array($payload)) {
        wp_send_json_error(['message' => __('Payload invalide.', 'em-wp')], 400);
    }

    try {
        $html = em_wp_admin_template_wizard_render_wireframe_html($payload);
    } catch (Throwable $e) {
        wp_send_json_error(
            [
                'message' => __('Impossible de charger l’aperçu du plan.', 'em-wp'),
            ],
            500
        );
    }

    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_em_wp_template_wizard_wireframe', 'em_wp_ajax_template_wizard_wireframe');
