<?php
/**
 * Pont CSS preview admin Rubriques (+ actions futures extraites d'overview.php).
 *
 * Chargé via require depuis overview.php.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * CSS de PREVIEW ADMIN Rubriques lu depuis une source dédiée et explicitement
 * nommée "rubriques-preview", afin de séparer clairement les
 * responsabilités (admin preview vs styles des modules front).
 *
 * @return string CSS concaténé (sans balise <style>).
 */
if (!function_exists('em_site_admin_rubriques_preview_css')) {
    function em_site_admin_rubriques_preview_css(): string
    {
        static $css = null;

        if ($css !== null) {
            return $css;
        }

        $css = '';
        $base = get_template_directory() . '/assets/admin/css/rubriques-preview/';
        foreach (
            [
                'admin-preview-render-base.css',
                'admin-preview-render-media.css',
                'admin-preview-render-components.css',
                'admin-preview-render-layout.css',
            ] as $file
        ) {
            $path = $base . $file;
            if (is_readable($path)) {
                $css .= (string) file_get_contents($path) . "\n";
            }
        }

        // Styles front HERO/SLIDER (pas header/index.css : règles responsive qui
        // empilent HERO+SLIDER en preview admin).
        $front_assets = get_template_directory() . '/assets/front/css/modules/';
        foreach (
            [
                $front_assets . 'hero/index.css',
                $front_assets . 'slider/index.css',
            ] as $front_css
        ) {
            if (is_readable($front_css)) {
                $css .= (string) file_get_contents($front_css) . "\n";
            }
        }

        $slider_css = get_template_directory() . '/assets/front/shared/css/slider.css';
        if (is_readable($slider_css)) {
            $css .= (string) file_get_contents($slider_css) . "\n";
        }

        // HEADER composite : chargé en dernier pour primer sur tout le reste.
        $header_preview_css = $base . 'admin-preview-render-header.css';
        if (is_readable($header_preview_css)) {
            $css .= (string) file_get_contents($header_preview_css) . "\n";
        }

        return $css;
    }
}

/**
 * Compatibilité rétroactive: ancien nom conservé comme alias.
 *
 * @return string
 */
if (!function_exists('em_site_rubriques_admin_render_css')) {
    function em_site_rubriques_admin_render_css(): string
    {
        return em_site_admin_rubriques_preview_css();
    }
}
