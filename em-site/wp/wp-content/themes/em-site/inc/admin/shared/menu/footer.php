<?php
/**
 * Pied de page admin (tous les écrans).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Indique si l'écran admin courant appartient au bloc « Rubriques du site ».
 */
function em_site_admin_is_rubrique_screen(): bool
{
    if (!is_admin()) {
        return false;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return false;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    return $page_slug !== '' && str_starts_with($page_slug, 'em-');
}

/**
 * Texte pied de page admin.
 */
function em_site_admin_footer_text(): string
{
    return 'Made with ❤️ for Ellene Masri - © Tyson - 2026';
}

/**
 * Remplace « Thank you for creating with WordPress. » sur tous les écrans admin.
 */
function em_site_admin_filter_footer_text(string $text): string
{
    if (!is_admin()) {
        return $text;
    }

    return em_site_admin_footer_text();
}
add_filter('admin_footer_text', 'em_site_admin_filter_footer_text');

/**
 * Masque « Version X.X » à droite du pied de page sur les écrans Rubriques.
 */
function em_site_admin_filter_rubrique_update_footer(string $version): string
{
    if (!em_site_admin_is_rubrique_screen()) {
        return $version;
    }

    return '';
}
add_filter('update_footer', 'em_site_admin_filter_rubrique_update_footer');

