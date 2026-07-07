<?php
/**
 * Assets de la page Rubriques EM-SITE (admin).
 *
 * Réutilise les composants mutualisés du thème : modale de confirmation
 * (EmWpAdminConfirm), color picker + modale couleur, etc. La modale couleur
 * elle-même est injectée par color-modal.php au admin_footer dès que son script
 * est enqueué sur un écran em-site.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug de la page EM-SITE.
 */
function em_site_page_slug(): string
{
    return 'em-rubriques-overview';
}

/**
 * Charge les assets partagés sur la page EM-SITE uniquement.
 */
function em_site_enqueue_assets(): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page !== em_site_page_slug()) {
        return;
    }

    if (function_exists('em_site_admin_enqueue_shared_assets')) {
        em_site_admin_enqueue_shared_assets();
    }

    // Webfonts du sélecteur « Typos » (Archivo Black = police du site) afin que
    // l'aperçu temps réel affiche réellement la police choisie, pas un repli.
    wp_enqueue_style(
        'em-site-v4-fonts',
        'https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;700&family=Montserrat:wght@400;700&family=Oswald&family=Playfair+Display&family=Poppins:wght@400;600&family=Roboto&display=swap',
        [],
        null
    );

    // CSS du SLIDER front (mayami) pour que l'aperçu temps réel du champ
    // « Slider » ait le LOOK COMPLET du site (cadre, scotch, flèches, bandeau,
    // pastilles…) au lieu d'un simple visuel.
    $slider_css_rel = '/assets/front/shared/css/slider.css';
    $slider_css_path = get_template_directory() . $slider_css_rel;
    if (file_exists($slider_css_path)) {
        wp_enqueue_style(
            'em-site-v4-slider-mayami',
            get_template_directory_uri() . $slider_css_rel,
            [],
            (string) filemtime($slider_css_path)
        );
    }

    // NB : le CSS de rendu Rubriques (.em-rubrique… : base, grille, champs,
    // médias, cartes plateforme/réseau, cadre vidéo + scotchs) est inliné par
    // la page d'aperçu admin locale → source UNIQUE partagée par tous les
    // aperçus admin (builder, squelette, instance-picker, header). Pas
    // d'enqueue ici.

    // Médiathèque WordPress pour le choix d'image dans le builder.
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'em_site_enqueue_assets');

