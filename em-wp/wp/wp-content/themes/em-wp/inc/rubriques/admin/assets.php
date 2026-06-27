<?php
/**
 * Assets de la page Rubriques V4 (admin).
 *
 * Réutilise les composants mutualisés du thème : modale de confirmation
 * (EmWpAdminConfirm), color picker + modale couleur, etc. La modale couleur
 * elle-même est injectée par color-modal.php au admin_footer dès que son script
 * est enqueué sur un écran em-wp.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug de la page V4.
 */
function em_wp_v4_page_slug(): string
{
    return 'em-wp-v4-overview';
}

/**
 * Charge les assets partagés sur la page V4 uniquement.
 */
function em_wp_v4_enqueue_assets(): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page !== em_wp_v4_page_slug()) {
        return;
    }

    if (function_exists('em_wp_admin_enqueue_shared_assets')) {
        em_wp_admin_enqueue_shared_assets();
    }

    // Webfonts du sélecteur « Typos » (Archivo Black = police du site) afin que
    // l'aperçu temps réel affiche réellement la police choisie, pas un repli.
    wp_enqueue_style(
        'em-wp-v4-fonts',
        'https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;700&family=Montserrat:wght@400;700&family=Oswald&family=Playfair+Display&family=Poppins:wght@400;600&family=Roboto&display=swap',
        [],
        null
    );

    // Médiathèque WordPress pour le choix d'image dans le builder.
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'em_wp_v4_enqueue_assets');
