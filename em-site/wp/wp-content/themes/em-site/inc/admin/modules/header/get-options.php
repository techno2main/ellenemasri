<?php
/**
 * Lecture options HEADER par template.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nom d'option WP pour HEADER × template.
 */
function em_site_header_option_name(?string $template_slug = null): string
{
    if ($template_slug === null || $template_slug === '') {
        $template_slug = is_admin() && function_exists('em_site_get_editing_template_slug')
            ? em_site_get_editing_template_slug()
            : em_site_get_active_template_slug();
    }

    return em_site_template_resolve_option_name('header', $template_slug);
}

/**
 * Template cible pour lecture / écriture HEADER (POST admin, édition, live).
 */
function em_site_header_resolve_template_slug(?string $preferred = null): string
{
    $preferred = sanitize_key((string) ($preferred ?? ''));

    if ($preferred !== '' && function_exists('em_site_template_exists') && em_site_template_exists($preferred)) {
        return $preferred;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $from_post = sanitize_key((string) ($_POST['em_site_template_context'] ?? ''));

    if ($from_post !== '' && function_exists('em_site_template_exists') && em_site_template_exists($from_post)) {
        return $from_post;
    }

    if (is_admin() && function_exists('em_site_get_editing_template_slug')) {
        return em_site_get_editing_template_slug();
    }

    return function_exists('em_site_front_get_live_template_slug')
        ? em_site_front_get_live_template_slug()
        : em_site_get_active_template_slug();
}

/**
 * Enregistre les options HEADER pour un template.
 *
 * @param array<string, mixed> $options
 */
function em_site_header_persist_options(array $options, ?string $template_slug = null): bool
{
    $template_slug = em_site_header_resolve_template_slug($template_slug);

    return (bool) update_option(
        em_site_header_option_name($template_slug),
        wp_parse_args($options, em_site_header_default_options()),
        false
    );
}

/**
 * Reprend couleurs / image de fond depuis le hero catalogue si HEADER vide (migration douce).
 *
 * @param array<string, mixed> $options
 * @return array<string, mixed>
 */
function em_site_header_migrate_style_from_hero_catalog(array $options): array
{
    $hero_slug = sanitize_key((string) ($options['hero_slug'] ?? ''));

    if ($hero_slug === '' || !function_exists('em_site_hero_get_options')) {
        return $options;
    }

    $has_style = trim((string) ($options['background_color'] ?? '')) !== ''
        || trim((string) ($options['text_color'] ?? '')) !== ''
        || trim((string) ($options['background_image'] ?? '')) !== '';

    if ($has_style) {
        return $options;
    }

    $hero = em_site_hero_get_options($hero_slug);

    if (trim((string) ($options['background_color'] ?? '')) === '' && trim((string) ($hero['background_color'] ?? '')) !== '') {
        $options['background_color'] = (string) $hero['background_color'];
    }

    if (trim((string) ($options['text_color'] ?? '')) === '' && trim((string) ($hero['text_color'] ?? '')) !== '') {
        $options['text_color'] = (string) $hero['text_color'];
    }

    if (trim((string) ($options['background_image'] ?? '')) === '' && trim((string) ($hero['background_image'] ?? '')) !== '') {
        $options['background_image'] = (string) $hero['background_image'];
        $options['background_image_hidden'] = !empty($hero['background_image_hidden']);
    }

    return $options;
}

/**
 * Résout le slug catalogue d'une sous-rubrique HEADER (hero ou slider).
 *
 * Reprend la logique des autres rubriques : si aucun item n'est choisi (ou si
 * le slug enregistré n'existe plus), on retombe sur l'item Default du catalogue
 * correspondant. Retourne '' si aucun défaut n'est disponible.
 */
function em_site_header_resolve_catalog_entry_slug(string $part, string $slug): string
{
    $part = $part === 'slider' ? 'slider' : 'hero';
    $slug = sanitize_key($slug);

    $normalize = 'em_site_' . $part . '_normalize_catalog_slug';
    $has = 'em_site_' . $part . '_catalog_has';
    $entries = 'em_site_' . $part . '_catalog_entries';

    if ($slug !== '') {
        if (function_exists($normalize)) {
            $slug = call_user_func($normalize, $slug);
        }

        // Slug valide → on le garde tel quel.
        if (!function_exists($has) || call_user_func($has, $slug)) {
            return $slug;
        }
    }

    // Aucun item (ou item disparu) : repli sur le Default du catalogue.
    if (function_exists('em_site_catalog_default_entry_slug_if_present') && function_exists($entries)) {
        return em_site_catalog_default_entry_slug_if_present(call_user_func($entries));
    }

    return '';
}

/**
 * Options HEADER brutes enregistrées (sans migration runtime).
 *
 * @return array<string, mixed>
 */
function em_site_header_get_saved_options(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_site_header_resolve_template_slug();
    }

    return wp_parse_args(
        em_site_get_template_rubrique_options('header', $template_slug),
        em_site_header_default_options()
    );
}

/**
 * Options HEADER normalisées.
 *
 * @return array<string, mixed>
 */
function em_site_header_get_options(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        $template_slug = em_site_header_resolve_template_slug();
    }

    $options = em_site_header_get_saved_options($template_slug);

    if (function_exists('em_site_rubrique_sync_enabled_for_admin')) {
        $options = em_site_rubrique_sync_enabled_for_admin('header', $options);
    }

    // Repli Default (comme les autres rubriques) : quand AUCUN hero ni slider
    // n'est choisi, on affiche le hero Default ET le slider Default du catalogue.
    // On ne touche pas les templates qui ont déjà un choix explicite (ex. live).
    $hero_slug = sanitize_key((string) ($options['hero_slug'] ?? ''));
    $slider_slug = sanitize_key((string) ($options['slider_slug'] ?? ''));

    if ($hero_slug === '' && $slider_slug === '') {
        $options['hero_slug'] = em_site_header_resolve_catalog_entry_slug('hero', '');
        $options['slider_slug'] = em_site_header_resolve_catalog_entry_slug('slider', '');
    }

    return em_site_header_migrate_style_from_hero_catalog($options);
}

/**
 * Options HEADER pour le front (template live).
 */
function em_site_header_get_options_for_front(): array
{
    $template_slug = function_exists('em_site_front_get_live_template_slug')
        ? em_site_front_get_live_template_slug()
        : em_site_get_active_template_slug();

    return em_site_header_get_options($template_slug);
}
