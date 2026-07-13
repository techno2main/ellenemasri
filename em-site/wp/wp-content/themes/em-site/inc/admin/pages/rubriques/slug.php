<?php
/**
 * Slug et URL page Rubriques.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug de la page sommaire Rubriques.
 */
function em_site_admin_rubriques_page_slug(): string
{
    return 'em-rubriques';
}

/**
 * Slug du hub rubriques à utiliser dans le contexte courant.
 *
 * Si l'utilisateur est sur la page TEMPLATE, on conserve `em-template`
 * pour éviter tout changement d'URL pendant la navigation du hub.
 */
function em_site_admin_rubriques_context_page_slug(): string
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current_page = sanitize_key((string) ($_GET['page'] ?? ''));

    if (
        $current_page !== ''
        && function_exists('em_site_admin_template_parent_page_slug')
        && $current_page === em_site_admin_template_parent_page_slug()
    ) {
        return $current_page;
    }

    return em_site_admin_rubriques_page_slug();
}

/**
 * Indique si un slug correspond au hub Rubriques (URL Rubriques ou Template).
 */
function em_site_admin_is_rubriques_hub_page_slug(string $page_slug = ''): bool
{
    $page_slug = sanitize_key($page_slug);

    if ($page_slug === '') {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
    }

    if ($page_slug === em_site_admin_rubriques_page_slug()) {
        return true;
    }

    return function_exists('em_site_admin_template_parent_page_slug')
        && $page_slug === em_site_admin_template_parent_page_slug();
}

/**
 * URL admin du sommaire Rubriques Template.
 */
function em_site_admin_rubriques_admin_url(): string
{
    return admin_url('admin.php?page=' . em_site_admin_rubriques_context_page_slug());
}

