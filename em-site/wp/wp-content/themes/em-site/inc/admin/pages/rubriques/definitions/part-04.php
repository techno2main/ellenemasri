<?php
function em_wp_admin_rubrique_skeleton_label(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);

    $static = [
        'top-bar'         => __('TOP-BAR', 'em-wp'),
        'header'          => __('HEADER', 'em-wp'),
        'stream'          => __('STREAM', 'em-wp'),
        'social'          => __('SOCIAL', 'em-wp'),
        'video'           => __('VIDEO', 'em-wp'),
        'release'         => __('RELEASE', 'em-wp'),
        'cta'             => __('CTA', 'em-wp'),
        'footer'          => __('FOOTER', 'em-wp'),
        'custom-contacts' => __('CONTACT', 'em-wp'),
        'contacts'        => __('CONTACT', 'em-wp'),
    ];

    if (isset($static[$module_slug])) {
        return (string) $static[$module_slug];
    }

    // Modules catalogue personnalisés : lire le libellé du module directement.
    // NE PAS passer par em_wp_admin_site_rubrique_all_definitions() ici : cette
    // fonction reconstruit les rubriques custom en rappelant skeleton_label(),
    // ce qui provoquerait une récursion infinie pour tout slug non statique.
    if (function_exists('em_wp_custom_catalog_module')) {
        $module = em_wp_custom_catalog_module($module_slug);

        if (is_array($module)) {
            $label = trim((string) ($module['label'] ?? ''));

            if ($label !== '') {
                return mb_strtoupper($label);
            }
        }
    }

    // Rubriques intégrées uniquement (pas de modules custom ici → pas de récursion).
    $definition = em_wp_admin_site_rubrique_static_definitions()[$module_slug] ?? null;

    if (is_array($definition)) {
        return (string) ($definition['label'] ?? mb_strtoupper($module_slug));
    }

    return mb_strtoupper($module_slug);
}

/**
 * Libellé du template en cours d'édition (majuscules, barre rubrique + intro).
 */
function em_wp_admin_rubrique_editing_template_label(): string
{
    $label = function_exists('em_wp_get_editing_template_label')
        ? trim((string) em_wp_get_editing_template_label())
        : '';

    return $label !== '' ? mb_strtoupper($label) : '';
}

/**
 * Description de page pour une rubrique en édition template (HTML autorisé : strong).
 */
function em_wp_admin_rubrique_editing_page_description_html(string $module_slug): string
{
    $rubrique_label = em_wp_admin_rubrique_label($module_slug);
    $template_label = em_wp_admin_rubrique_editing_template_label();

    if ($template_label !== '') {
        $template_markup = '<strong class="em-wp-hub__template-name">' . esc_html($template_label) . '</strong>';

        return sprintf(
            /* translators: 1: rubrique label, 2: template label markup */
            __('Tu es dans la rubrique %1$s de %2$s.', 'em-wp'),
            esc_html($rubrique_label),
            $template_markup
        );
    }

    return sprintf(
        /* translators: %s: rubrique label */
        esc_html__('Tu es dans la rubrique %s.', 'em-wp'),
        esc_html($rubrique_label)
    );
}

/**
 * Indique si la barre d'onglets Rubriques doit s'afficher (contexte template actif).
 */
function em_wp_admin_rubrique_should_show_nav(string $page_slug = ''): bool
{
    if (!function_exists('em_wp_admin_has_template_context') || !em_wp_admin_has_template_context()) {
        return false;
    }

    if ($page_slug === '') {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
    }

    if ($page_slug === '') {
        return false;
    }

    if ($page_slug === em_wp_admin_rubriques_page_slug()) {
        return true;
    }

    foreach (em_wp_admin_site_rubrique_definitions() as $module_slug => $definition) {
        unset($definition);

        if (in_array($page_slug, em_wp_admin_rubrique_module_admin_page_slugs($module_slug), true)) {
            return true;
        }
    }

    return false;
}

/**
 * Définitions des onglets Rubriques (slug module => page admin).
 *
 * @return array<string, array{menu_title:string,page_slug:string}>
 */
function em_wp_admin_rubrique_nav_tab_definitions(): array
{
    $tabs = [];

    foreach (em_wp_admin_site_rubrique_definitions() as $module_slug => $definition) {
        $page_slug = em_wp_admin_site_rubrique_entry_page_slug($module_slug);

        if ($page_slug === '') {
            continue;
        }

        $tabs[$module_slug] = [
            'menu_title' => (string) ($definition['menu_title'] ?? $definition['label'] ?? $module_slug),
            'page_slug'  => $page_slug,
        ];
    }

    return $tabs;
}

/**
 * Module rubrique actif pour la page admin courante (vide = sommaire Liste).
 */
function em_wp_admin_rubrique_resolve_active_module(string $module_slug = ''): string
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug !== '') {
        return $module_slug;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug === em_wp_admin_rubriques_page_slug()) {
        // Sur le squelette, l'onglet actif suit la rubrique ouverte en dessous.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return sanitize_key((string) ($_GET['open'] ?? ''));
    }

    if ($page_slug === '') {
        return '';
    }

    foreach (em_wp_admin_site_rubrique_definitions() as $slug => $definition) {
        unset($definition);

        if (in_array($page_slug, em_wp_admin_rubrique_module_admin_page_slugs($slug), true)) {
            return (string) $slug;
        }
    }

    return '';
}

/**
 * Charge le CSS des onglets Rubriques (réutilise les pastilles catalogue).
 */
function em_wp_admin_rubrique_enqueue_nav_assets(string $page_slug = ''): void
{
    if (!em_wp_admin_rubrique_should_show_nav($page_slug)) {
        return;
    }

    wp_enqueue_style('em-wp-admin-module-common');
}

/**
 * Variables CSS inline pour un onglet rubrique (Couleurs Rubrique).
 */
function em_wp_admin_rubrique_tab_style_attr(string $module_slug): string
{
    $colors = function_exists('em_wp_admin_module_style_colors_for_preview')
        ? em_wp_admin_module_style_colors_for_preview($module_slug)
        : ['background' => '#100421', 'text' => '#ffffff'];

    return sprintf(
        '--em-rubrique-accent:%1$s;--em-rubrique-text:%2$s;',
        esc_attr((string) ($colors['background'] ?? '#100421')),
        esc_attr((string) ($colors['text'] ?? '#ffffff'))
    );
}

/**
 * Navbar horizontale Rubriques (couleurs par rubrique).
 *
 * @param array<string, array{menu_title:string,page_slug:string}> $tabs
 */

