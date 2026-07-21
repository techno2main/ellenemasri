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
 * Familles Google disponibles pour les typos de rubriques.
 *
 * @return array<string, string> clé typo => fragment family=...
 */
function em_site_admin_google_font_families_map(): array
{
    return [
        'archivo_black' => 'Archivo+Black',
        'inter'         => 'Inter:wght@400;700',
        'montserrat'    => 'Montserrat:wght@400;700',
        'poppins'       => 'Poppins:wght@400;600',
        'oswald'        => 'Oswald',
        'roboto'        => 'Roboto',
        'playfair'      => 'Playfair+Display',
    ];
}

/**
 * Collecte récursive des clés de typo présentes dans une valeur.
 *
 * @param mixed               $value
 * @param array<string, true> $allowed_keys
 * @param array<string, true> $found_keys
 */
function em_site_admin_collect_font_keys_from_value($value, array $allowed_keys, array &$found_keys): void
{
    if (is_array($value)) {
        foreach ($value as $child) {
            em_site_admin_collect_font_keys_from_value($child, $allowed_keys, $found_keys);
        }
        return;
    }

    if (!is_string($value)) {
        return;
    }

    $candidate = sanitize_key($value);

    if ($candidate !== '' && isset($allowed_keys[$candidate])) {
        $found_keys[$candidate] = true;
    }
}

/**
 * Clés de polices réellement utilisées dans les rubriques du site.
 *
 * @return array<int, string>
 */
function em_site_admin_used_font_keys(): array
{
    static $cache = null;

    if (is_array($cache)) {
        return $cache;
    }

    $choices = function_exists('em_site_rubrique_font_choices')
        ? em_site_rubrique_font_choices()
        : [];

    $google_map = em_site_admin_google_font_families_map();
    $allowed = [];

    foreach ($google_map as $font_key => $_family) {
        if (isset($choices[$font_key])) {
            $allowed[$font_key] = true;
        }
    }

    // Police du site toujours disponible, même sans occurrence explicite.
    $found = ['archivo_black' => true];

    if (!function_exists('em_site_rubrique_type_registry')
        || !function_exists('em_site_get_items')
        || !function_exists('em_site_get_item')) {
        $cache = array_values(array_keys($found));
        return $cache;
    }

    foreach ((array) em_site_rubrique_type_registry() as $type_slug => $_type_def) {
        $type_slug = sanitize_key((string) $type_slug);

        if ($type_slug === '') {
            continue;
        }

        foreach (array_keys((array) em_site_get_items($type_slug)) as $item_slug) {
            $item_slug = sanitize_key((string) $item_slug);

            if ($item_slug === '') {
                continue;
            }

            $item = em_site_get_item($type_slug, $item_slug);
            $content = is_array($item['content'] ?? null) ? $item['content'] : [];
            $fields = is_array($item['fields'] ?? null) ? $item['fields'] : [];

            foreach ($fields as $field) {
                if (!is_array($field)) {
                    continue;
                }

                $field_type = sanitize_key((string) ($field['type'] ?? ''));
                $field_role = sanitize_key((string) ($field['options']['role'] ?? ''));

                if ($field_type !== 'select' || $field_role !== 'font') {
                    continue;
                }

                $field_key = sanitize_key((string) ($field['key'] ?? ''));

                if ($field_key === '') {
                    continue;
                }

                $font_key = sanitize_key((string) ($content[$field_key] ?? ($field['default'] ?? '')));

                if ($font_key !== '' && isset($allowed[$font_key])) {
                    $found[$font_key] = true;
                }
            }

            em_site_admin_collect_font_keys_from_value($content, $allowed, $found);
        }
    }

    $cache = array_values(array_keys($found));

    return $cache;
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

    // Webfonts utiles au contexte réel (pas toute la liste): on charge seulement
    // les familles effectivement utilisées par les rubriques existantes.
    $font_map = em_site_admin_google_font_families_map();
    $font_keys = em_site_admin_used_font_keys();
    $font_families = [];

    foreach ($font_keys as $font_key) {
        if (isset($font_map[$font_key])) {
            $font_families[] = $font_map[$font_key];
        }
    }

    $font_families = array_values(array_unique($font_families));

    if ($font_families !== []) {
        wp_enqueue_style(
            'em-site-fonts',
            'https://fonts.googleapis.com/css2?family=' . implode('&family=', $font_families) . '&display=swap',
            [],
            null
        );
    }

    // CSS du SLIDER front partagé pour que l'aperçu temps réel du champ
    // « Slider » ait le LOOK COMPLET du site (cadre, scotch, flèches, bandeau,
    // pastilles…) au lieu d'un simple visuel.
    $slider_css_rel = '/assets/front/shared/css/slider.css';
    $slider_css_path = get_template_directory() . $slider_css_rel;
    if (file_exists($slider_css_path)) {
        wp_enqueue_style(
            'em-site-slider-preview',
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

