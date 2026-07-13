<?php
/**
 * Fallback front mutualisé des rubriques.
 *
 * Rendu volontairement minimal: uniquement le nom de rubrique en majuscules.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Libellé fallback d'une rubrique (majuscules).
 */
function em_site_front_fallback_rubrique_label(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);

    $labels = [
        'top-bar' => 'TOP-BAR',
        'header'  => 'HEADER',
        'stream'  => 'STREAM',
        'social'  => 'SOCIAL',
        'video'   => 'VIDEO',
        'release' => 'RELEASE',
        'cta'     => 'CTA',
        'about'   => 'ABOUT',
        'contact' => 'CONTACT',
        'footer'  => 'FOOTER',
    ];

    if (isset($labels[$module_slug])) {
        return $labels[$module_slug];
    }

    return strtoupper(str_replace('-', ' ', $module_slug));
}

/**
 * Rendu du fallback rubrique.
 */
function em_site_render_front_rubrique_fallback(string $module_slug): void
{
    $label = em_site_front_fallback_rubrique_label($module_slug);

    if ($label === '') {
        return;
    }

    ?>
    <section data-em-site-rubrique-fallback="<?php echo esc_attr($module_slug); ?>">
        <?php echo esc_html($label); ?>
    </section>
    <?php
}
