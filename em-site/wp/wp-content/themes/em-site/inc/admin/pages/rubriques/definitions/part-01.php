<?php
/**
 * Définitions des rubriques (sommaire + menu latéral).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Définitions statiques des rubriques intégrées au thème.
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_admin_site_rubrique_static_definitions(): array
{
    return [
        'top-bar' => [
            'label'        => __('TOP-BAR', 'em-wp'),
            'menu_title'   => __('TOP-BAR', 'em-wp'),
            'description'  => __('Section TOP-BAR / HEADER', 'em-wp'),
            'page_slug'    => function_exists('em_wp_top_bar_page_slug') ? em_wp_top_bar_page_slug() : 'em-top-bar',
            'preview_zone' => 'top_bar',
            'accent_color' => '#100421',
        ],
        'header' => [
            'label'        => __('HEADER', 'em-wp'),
            'menu_title'   => __('HEADER', 'em-wp'),
            'description'  => __('Section HEADER (Hero et/ou Slider)', 'em-wp'),
            'page_slug'    => function_exists('em_wp_header_page_slug') ? em_wp_header_page_slug() : 'em-header',
            'preview_zone' => 'header',
            'accent_color' => '#d94a2d',
        ],
        'stream' => [
            'label'        => __('STREAM', 'em-wp'),
            'menu_title'   => __('STREAM', 'em-wp'),
            'description'  => __('Section 01 / LISTEN', 'em-wp'),
            'page_slug'    => function_exists('em_wp_stream_page_slug') ? em_wp_stream_page_slug() : 'em-stream',
            'preview_zone' => 'section_stream',
            'accent_color' => '#7c3aed',
        ],
        'social' => [
            'label'        => __('SOCIAL', 'em-wp'),
            'menu_title'   => __('SOCIAL', 'em-wp'),
            'description'  => __('Section 02 / FOLLOW', 'em-wp'),
            'page_slug'    => 'em-social',
            'preview_zone' => 'section_social',
            'accent_color' => '#db2777',
            'coming_soon'  => true,
        ],
        'video' => [
            'label'        => __('VIDEO', 'em-wp'),
            'menu_title'   => __('VIDEO', 'em-wp'),
            'description'  => __('Section 03 / WATCH', 'em-wp'),
            'page_slug'    => function_exists('em_wp_video_page_slug') ? em_wp_video_page_slug() : 'em-videos',
            'preview_zone' => 'section_video',
            'accent_color' => '#ca8a04',
        ],
        'release' => [
            'label'        => __('RELEASE', 'em-wp'),
            'menu_title'   => __('RELEASE', 'em-wp'),
            'description'  => __('Section 04 / RELEASE INFOS', 'em-wp'),
            'page_slug'    => function_exists('em_wp_release_page_slug') ? em_wp_release_page_slug() : 'em-releases',
            'preview_zone' => 'section_release',
            'accent_color' => '#b8956a',
        ],
        'cta' => [
            'label'        => __('CTA', 'em-wp'),
            'menu_title'   => __('CTA', 'em-wp'),
            'description'  => __('Section 05 / DON\'T SLEEP ON IT', 'em-wp'),
            'page_slug'    => 'em-cta',
            'preview_zone' => 'section_cta',
            'accent_color' => '#0d9488',
            'coming_soon'  => true,
        ],
        'footer' => [
            'label'        => __('FOOTER', 'em-wp'),
            'menu_title'   => __('FOOTER', 'em-wp'),
            'description'  => __('Section FOOTER', 'em-wp'),
            'page_slug'    => 'em-footer',
            'preview_zone' => 'section_footer',
            'accent_color' => '#100421',
            'coming_soon'  => true,
        ],
    ];
}

/**
 * Rubriques dérivées des catalogues créés dans l'admin (CONTACTS, …).
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_admin_catalog_rubrique_definitions(): array
{
    if (!function_exists('em_wp_custom_catalog_modules')) {
        return [];
    }

    $definitions = [];

    foreach (em_wp_custom_catalog_modules() as $module_slug => $module) {
        $module_slug = sanitize_key((string) $module_slug);
        $label = trim((string) ($module['label'] ?? ''));

        if ($module_slug === '' || $label === '') {
            continue;
        }

        $display_label = function_exists('em_wp_admin_rubrique_skeleton_label')
            ? em_wp_admin_rubrique_skeleton_label($module_slug)
            : mb_strtoupper($label);

        $definitions[$module_slug] = [
            'label'          => $display_label,
            'menu_title'     => $display_label,
            'description'    => sprintf(
                /* translators: %s: catalogue label */
                __('Section %s', 'em-wp'),
                $label
            ),
            'page_slug'      => function_exists('em_wp_custom_catalog_rubrique_page_slug')
                ? em_wp_custom_catalog_rubrique_page_slug($module_slug)
                : (string) ($module['hub_menu_slug'] ?? em_wp_custom_catalog_hub_menu_slug($module_slug)),
            'preview_zone'   => 'section_' . $module_slug,
            'accent_color'   => '#751820',
            'catalog_module' => $module_slug,
        ];
    }

    return $definitions;
}

/**
 * Rubriques dérivées des types V4 créés dans l'admin (ABOUT, …) absentes des
 * définitions statiques.
 *
 * Les sous-types consommés par le composite HEADER (HERO / SLIDER) sont exclus :
 * ils ne s'ajoutent pas seuls au squelette, ils se règlent dans la rubrique
 * HEADER. Sans cela une rubrique V4 créée par le client (ex. ABOUT) n'apparaît
 * jamais dans la liste « Ajouter une rubrique ».
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_admin_v4_extra_rubrique_definitions(): array
{
    if (!function_exists('em_wp_rubrique_type_registry')) {
        return [];
    }

    // Définitions déjà connues (statiques + catalogues). On ne réinjecte un type
    // V4 QUE s'il n'est pas déjà couvert : sinon une entrée synthétique
    // écraserait la définition existante (ex. le type V4 « contacts » écrasait le
    // module catalogue « contacts » et son page_slug « em-contacts », ce qui
    // empêchait le purge de retirer le lien isolé CONTACT du menu).
    $existing = array_merge(
        em_wp_admin_site_rubrique_static_definitions(),
        em_wp_admin_catalog_rubrique_definitions()
    );

    // Sous-types du composite HEADER à ne pas proposer seuls.
    $excluded = [];

    if (function_exists('em_wp_admin_header_part_type_slug')) {
        foreach (['hero', 'slider'] as $part) {
            $part_slug = em_wp_admin_header_part_type_slug($part);

            if ($part_slug !== '') {
                $excluded[$part_slug] = true;
            }
        }
    }

    $definitions = [];

    foreach (em_wp_rubrique_type_registry() as $slug => $type) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || isset($existing[$slug]) || isset($excluded[$slug])) {
            continue;
        }

        // Filet de sécurité : exclure tout type « hero »/« slider » même si la
        // détection ci-dessus n'a pas pu tourner (header-section.php non chargé).
        $label_raw = strtolower((string) ($type['label'] ?? '') . ' ' . (string) ($type['label_plural'] ?? ''));

        if ($slug === 'headers'
            || strpos($slug, 'hero') !== false || strpos($slug, 'slider') !== false
            || strpos($label_raw, 'hero') !== false || strpos($label_raw, 'slider') !== false) {
            continue;
        }

        $label = function_exists('em_wp_admin_rubrique_skeleton_label')
            ? em_wp_admin_rubrique_skeleton_label($slug)
            : mb_strtoupper((string) ($type['label_plural'] ?? $type['label'] ?? $slug));

        $definitions[$slug] = [
            'label'        => $label,
            'menu_title'   => $label,
            'description'  => sprintf(
                /* translators: %s: rubrique label */
                __('Section %s', 'em-wp'),
                (string) ($type['label'] ?? $slug)
            ),
            // IMPORTANT : pas de page_slug. Un page_slug 'em-rubriques-overview'
            // entrerait en collision avec le menu top-level RUBRIQUES et le ferait
            // PURGER par em_wp_admin_menu_layout_purge_out_of_context_rubriques().
            // La carte « Ajouter une rubrique » n'utilise que le slug + le label.
            'page_slug'    => '',
            'preview_zone' => 'section_' . $slug,
            'accent_color' => '#751820',
        ];
    }

    return $definitions;
}

/**
 * Toutes les rubriques connues (intégrées + catalogues + types V4 custom).
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_admin_site_rubrique_all_definitions(): array
{
    return array_merge(
        em_wp_admin_site_rubrique_static_definitions(),
        em_wp_admin_catalog_rubrique_definitions(),
        em_wp_admin_v4_extra_rubrique_definitions()
    );
}

/**
 * Indique si une rubrique est liée à un catalogue (pas une section intégrée seule).
 */
function em_wp_admin_rubrique_is_catalog_linked(string $rubrique_slug): bool
{
    $rubrique_slug = sanitize_key($rubrique_slug);
    $definition = em_wp_admin_site_rubrique_all_definitions()[$rubrique_slug] ?? null;

    return is_array($definition) && !empty($definition['catalog_module']);
}

/**
 * Ordre des modules rubriques pour le contexte courant (squelette template ou global).
 *
 * @return string[]
 */
function em_wp_admin_site_rubrique_modules_for_context(): array
{
    if (function_exists('em_wp_admin_has_template_context') && em_wp_admin_has_template_context()) {
        return em_wp_get_rubrique_order_for_template();
    }

    return em_wp_admin_site_rubrique_modules();
}

/**
 * Rubriques proposables à l'ajout au squelette d'un template.
 *
 * @return array<string, array<string, mixed>>
 */


