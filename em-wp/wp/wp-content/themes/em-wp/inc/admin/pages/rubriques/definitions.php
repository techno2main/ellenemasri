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
            'page_slug'    => function_exists('em_wp_top_bar_page_slug') ? em_wp_top_bar_page_slug() : 'em-wp-top-bar',
            'preview_zone' => 'top_bar',
            'accent_color' => '#100421',
        ],
        'header' => [
            'label'        => __('HEADER', 'em-wp'),
            'menu_title'   => __('HEADER', 'em-wp'),
            'description'  => __('Section HEADER (Hero et/ou Slider)', 'em-wp'),
            'page_slug'    => function_exists('em_wp_header_page_slug') ? em_wp_header_page_slug() : 'em-wp-header',
            'preview_zone' => 'header',
            'accent_color' => '#d94a2d',
        ],
        'stream' => [
            'label'        => __('STREAM', 'em-wp'),
            'menu_title'   => __('STREAM', 'em-wp'),
            'description'  => __('Section 01 / LISTEN', 'em-wp'),
            'page_slug'    => function_exists('em_wp_stream_page_slug') ? em_wp_stream_page_slug() : 'em-wp-stream',
            'preview_zone' => 'section_stream',
            'accent_color' => '#7c3aed',
        ],
        'social' => [
            'label'        => __('SOCIAL', 'em-wp'),
            'menu_title'   => __('SOCIAL', 'em-wp'),
            'description'  => __('Section 02 / FOLLOW', 'em-wp'),
            'page_slug'    => 'em-wp-social',
            'preview_zone' => 'section_social',
            'accent_color' => '#db2777',
            'coming_soon'  => true,
        ],
        'video' => [
            'label'        => __('VIDEOS', 'em-wp'),
            'menu_title'   => __('VIDEOS', 'em-wp'),
            'description'  => __('Section 03 / WATCH', 'em-wp'),
            'page_slug'    => function_exists('em_wp_video_page_slug') ? em_wp_video_page_slug() : 'em-wp-videos',
            'preview_zone' => 'section_video',
            'accent_color' => '#ca8a04',
        ],
        'release' => [
            'label'        => __('RELEASES', 'em-wp'),
            'menu_title'   => __('RELEASES', 'em-wp'),
            'description'  => __('Section 04 / RELEASE INFOS', 'em-wp'),
            'page_slug'    => function_exists('em_wp_release_page_slug') ? em_wp_release_page_slug() : 'em-wp-releases',
            'preview_zone' => 'section_release',
            'accent_color' => '#b8956a',
        ],
        'cta' => [
            'label'        => __('CTA', 'em-wp'),
            'menu_title'   => __('CTA', 'em-wp'),
            'description'  => __('Section 05 / DON\'T SLEEP ON IT', 'em-wp'),
            'page_slug'    => 'em-wp-cta',
            'preview_zone' => 'section_cta',
            'accent_color' => '#0d9488',
            'coming_soon'  => true,
        ],
        'footer' => [
            'label'        => __('FOOTER', 'em-wp'),
            'menu_title'   => __('FOOTER', 'em-wp'),
            'description'  => __('Section FOOTER', 'em-wp'),
            'page_slug'    => 'em-wp-footer',
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
 * Toutes les rubriques connues (intégrées + catalogues).
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_admin_site_rubrique_all_definitions(): array
{
    return array_merge(
        em_wp_admin_site_rubrique_static_definitions(),
        em_wp_admin_catalog_rubrique_definitions()
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
function em_wp_admin_template_proposable_rubrique_definitions(?string $template_slug = null): array
{
    if ($template_slug === null && function_exists('em_wp_get_editing_template_slug')) {
        $template_slug = em_wp_get_editing_template_slug();
    }

    $template_slug = em_wp_template_sanitize_slug((string) $template_slug);
    $proposable = [];

    foreach (em_wp_admin_site_rubrique_all_definitions() as $rubrique_slug => $definition) {
        if (em_wp_rubrique_is_proposable_for_template($rubrique_slug, $template_slug)) {
            $proposable[$rubrique_slug] = $definition;
        }
    }

    return $proposable;
}

/**
 * Positions d'insertion proposées pour une rubrique dans le squelette template.
 *
 * @return array<int, array{value:string,label:string}>
 */
function em_wp_admin_template_skeleton_insert_positions(?string $template_slug = null): array
{
    if ($template_slug === null && function_exists('em_wp_get_editing_template_slug')) {
        $template_slug = em_wp_get_editing_template_slug();
    }

    $template_slug = em_wp_template_sanitize_slug((string) $template_slug);
    $order = $template_slug !== '' && function_exists('em_wp_get_template_skeleton_order')
        ? em_wp_get_template_skeleton_order($template_slug)
        : [];

    if (!is_array($order) || $order === []) {
        return [
            [
                'value' => '__start__',
                'label' => __('Au début', 'em-wp'),
            ],
        ];
    }

    $positions = [];

    if (in_array('top-bar', $order, true)) {
        $positions[] = [
            'value' => 'top-bar',
            'label' => __('Après TOP-BAR', 'em-wp'),
        ];
    } else {
        $positions[] = [
            'value' => '__start__',
            'label' => __('Au début', 'em-wp'),
        ];
    }

    foreach ($order as $slug) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || $slug === 'top-bar' || $slug === 'footer') {
            continue;
        }

        $label = function_exists('em_wp_admin_rubrique_skeleton_label')
            ? em_wp_admin_rubrique_skeleton_label($slug)
            : mb_strtoupper($slug);

        $positions[] = [
            'value' => $slug,
            'label' => sprintf(
                /* translators: %s: rubrique label */
                __('Après %s', 'em-wp'),
                $label
            ),
        ];
    }

    if (in_array('footer', $order, true)) {
        $positions[] = [
            'value' => '__before_footer__',
            'label' => __('Avant FOOTER', 'em-wp'),
        ];
    }

    return $positions;
}

/**
 * Une rubrique peut-elle être ajoutée au squelette du template ?
 */
function em_wp_rubrique_is_proposable_for_template(string $rubrique_slug, ?string $template_slug = null): bool
{
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($rubrique_slug === '') {
        return false;
    }

    $all = em_wp_admin_site_rubrique_all_definitions();

    if (!isset($all[$rubrique_slug])) {
        return false;
    }

    if ($template_slug === null && function_exists('em_wp_get_editing_template_slug')) {
        $template_slug = em_wp_get_editing_template_slug();
    }

    $template_slug = em_wp_template_sanitize_slug((string) $template_slug);
    $skeleton = $template_slug !== ''
        ? em_wp_get_template_skeleton_order($template_slug)
        : em_wp_get_site_rubrique_order();

    if (in_array($rubrique_slug, $skeleton, true)) {
        return false;
    }

    if (em_wp_admin_rubrique_is_catalog_linked($rubrique_slug)) {
        $catalog_slug = sanitize_key((string) ($all[$rubrique_slug]['catalog_module'] ?? $rubrique_slug));

        if ($catalog_slug === '') {
            return false;
        }

        if (function_exists('em_wp_catalog_hub_entries')) {
            return em_wp_catalog_hub_entries($catalog_slug) !== [];
        }

        if (!function_exists('em_wp_custom_catalog_entries')) {
            return false;
        }

        return em_wp_custom_catalog_entries($catalog_slug) !== [];
    }

    return true;
}

/**
 * Définitions des rubriques affichées dans le sommaire et le menu latéral.
 *
 * @return array<string, array{
 *     label:string,
 *     description:string,
 *     page_slug:string,
 *     menu_title:string,
 *     preview_zone:string,
 *     accent_color:string,
 *     coming_soon?:bool,
 *     catalog_module?:string
 * }>
 */
function em_wp_admin_site_rubrique_definitions(): array
{
    $definitions = em_wp_admin_site_rubrique_all_definitions();
    $ordered = [];

    foreach (em_wp_admin_site_rubrique_modules_for_context() as $module_slug) {
        if (isset($definitions[$module_slug])) {
            $ordered[$module_slug] = $definitions[$module_slug];
        }
    }

    return $ordered;
}

/**
 * Définitions rubriques ordonnées pour un template (squelette).
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_admin_site_rubrique_definitions_for_template(string $template_slug): array
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);
    $definitions = em_wp_admin_site_rubrique_all_definitions();
    $ordered = [];

    if ($template_slug === '') {
        return em_wp_admin_site_rubrique_definitions();
    }

    foreach (em_wp_get_template_skeleton_order($template_slug) as $module_slug) {
        if (isset($definitions[$module_slug])) {
            $ordered[$module_slug] = $definitions[$module_slug];
        }
    }

    return $ordered;
}

/**
 * Slug admin à ouvrir pour une rubrique (variante active si hub multi-choix).
 */
function em_wp_admin_site_rubrique_entry_page_slug(string $module_slug): string
{
    $definitions = em_wp_admin_site_rubrique_definitions();
    $definition = $definitions[$module_slug] ?? null;

    if (!is_array($definition)) {
        return '';
    }

    if ($module_slug === 'header') {
        return function_exists('em_wp_header_page_slug') ? em_wp_header_page_slug() : 'em-wp-header';
    }

    return (string) ($definition['page_slug'] ?? '');
}

/**
 * URL admin d'entrée d'une rubrique (alignée sur le menu latéral).
 */
function em_wp_admin_site_rubrique_entry_url(string $module_slug): string
{
    $page_slug = em_wp_admin_site_rubrique_entry_page_slug($module_slug);

    if ($page_slug === '') {
        return '';
    }

    return add_query_arg(['page' => $page_slug], admin_url('admin.php'));
}

/**
 * Libellés des rubriques visibles pour un template (ordre sommaire).
 *
 * @return string[]
 */
function em_wp_admin_template_active_rubrique_labels(string $template_slug): array
{
    if (!function_exists('em_wp_is_template_rubrique_visible')) {
        return [];
    }

    $template_slug = em_wp_template_sanitize_slug($template_slug);

    if ($template_slug === '') {
        return [];
    }

    $labels = [];

    foreach (em_wp_admin_site_rubrique_definitions_for_template($template_slug) as $module_slug => $definition) {
        if (!empty($definition['coming_soon'])) {
            continue;
        }

        if (!em_wp_is_template_rubrique_visible($template_slug, $module_slug)) {
            continue;
        }

        $labels[] = mb_strtoupper((string) ($definition['label'] ?? $module_slug));
    }

    return $labels;
}

/**
 * Résumé texte « Rubriques actives : TOP-BAR, HEADER, … ».
 */
function em_wp_admin_template_active_rubriques_summary(string $template_slug): string
{
    $labels = em_wp_admin_template_active_rubrique_labels($template_slug);

    if ($labels === []) {
        return __('Rubriques actives : aucune.', 'em-wp');
    }

    return sprintf(
        /* translators: %s: comma-separated rubrique labels */
        __('Rubriques actives : %s.', 'em-wp'),
        implode(', ', $labels)
    );
}

/**
 * Libellé affiché d'une rubrique (TOP-BAR, HEADER, …).
 */
function em_wp_admin_rubrique_label(string $module_slug): string
{
    $definition = em_wp_admin_site_rubrique_definitions()[$module_slug] ?? null;

    if (!is_array($definition)) {
        return mb_strtoupper($module_slug);
    }

    return (string) ($definition['label'] ?? mb_strtoupper($module_slug));
}

/**
 * Libellé neutre au singulier pour le squelette template (liste + wireframe + onglets).
 */
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

    $definition = em_wp_admin_site_rubrique_all_definitions()[$module_slug] ?? null;

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

    if ($page_slug === '' || $page_slug === em_wp_admin_rubriques_page_slug()) {
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

    wp_enqueue_style(
        'em-wp-admin-rubrique-nav',
        get_template_directory_uri() . '/assets/admin/css/catalog/sommaire.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/catalog/sommaire.css')
    );
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
function em_wp_admin_rubrique_render_edit_navbar(
    array $tabs,
    string $active_module_slug,
    string $list_page_slug,
    bool $show_add_rubrique_toggle = false
): void {
    $active_module_slug = sanitize_key($active_module_slug);
    $list_page_slug = sanitize_key($list_page_slug);
    $list_tab_label = __('Liste', 'em-wp');

    if ($tabs === [] && $list_page_slug === '') {
        return;
    }

    $rubrique_definitions = em_wp_admin_site_rubrique_definitions();
    ?>
    <nav class="em-wp-catalog-edit__nav em-wp-rubrique-edit__nav" aria-label="<?php echo esc_attr__('Navigation Rubriques', 'em-wp'); ?>">
        <ul class="em-wp-catalog-edit__nav-list">
            <?php if ($list_page_slug !== '') {
                $list_url = admin_url('admin.php?page=' . $list_page_slug);
                $is_list_active = $active_module_slug === '';
                ?>
                <li class="em-wp-catalog-edit__nav-item<?php echo $is_list_active ? ' is-active' : ''; ?>">
                    <a
                        class="em-wp-catalog-edit__nav-link em-wp-catalog-edit__nav-link--list"
                        href="<?php echo esc_url($list_url); ?>"
                        aria-label="<?php echo esc_attr($list_tab_label); ?>"
                        <?php echo $is_list_active ? ' aria-current="page"' : ''; ?>
                    >
                        <i class="fa-solid fa-list-ol em-wp-catalog-edit__nav-icon" aria-hidden="true"></i>
                    </a>
                </li>
            <?php } ?>
            <?php foreach ($tabs as $module_slug => $definition) {
                $page_slug = (string) ($definition['page_slug'] ?? '');

                if ($page_slug === '') {
                    continue;
                }

                $label = function_exists('em_wp_admin_rubrique_skeleton_label')
                    ? em_wp_admin_rubrique_skeleton_label((string) $module_slug)
                    : (string) ($definition['menu_title'] ?? $module_slug);
                $is_active = $active_module_slug === (string) $module_slug;
                $item_url = add_query_arg(['page' => $page_slug], admin_url('admin.php'));
                $preview_zone = (string) ($rubrique_definitions[(string) $module_slug]['preview_zone'] ?? '');
                ?>
                <li class="em-wp-catalog-edit__nav-item<?php echo $is_active ? ' is-active' : ''; ?>">
                    <a
                        class="em-wp-catalog-edit__nav-link"
                        href="<?php echo esc_url($item_url); ?>"
                        style="<?php echo esc_attr(em_wp_admin_rubrique_tab_style_attr((string) $module_slug)); ?>"
                        <?php if ($preview_zone !== '') { ?>
                            data-preview-zone="<?php echo esc_attr($preview_zone); ?>"
                        <?php } ?>
                        <?php echo $is_active ? ' aria-current="page"' : ''; ?>
                    >
                        <?php echo esc_html($label); ?>
                    </a>
                </li>
            <?php } ?>
            <?php if ($show_add_rubrique_toggle && current_user_can('manage_options')) { ?>
                <li class="em-wp-catalog-edit__nav-item em-wp-catalog-edit__nav-item--add">
                    <button
                        type="button"
                        class="em-wp-catalog-edit__nav-link em-wp-catalog-edit__nav-link--add"
                        id="em-wp-rubrique-skeleton-add-toggle"
                        aria-label="<?php esc_attr_e('Ajouter une rubrique', 'em-wp'); ?>"
                        aria-controls="em-wp-rubrique-skeleton-add-panel"
                        aria-expanded="false"
                    >
                        <i class="fa-solid fa-plus em-wp-catalog-edit__nav-icon" aria-hidden="true"></i>
                    </button>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <?php
}

/**
 * Onglets Liste + rubriques (TOP-BAR, HEADER, STREAM…).
 */
function em_wp_admin_rubrique_render_entry_tabs(string $active_module_slug = ''): void
{
    $tabs = em_wp_admin_rubrique_nav_tab_definitions();

    if ($tabs === []) {
        return;
    }

    $resolved_module = em_wp_admin_rubrique_resolve_active_module($active_module_slug);
    $show_add_rubrique = $resolved_module === ''
        && function_exists('em_wp_admin_has_template_context')
        && em_wp_admin_has_template_context();

    em_wp_admin_rubrique_render_edit_navbar(
        $tabs,
        $resolved_module,
        em_wp_admin_rubriques_page_slug(),
        $show_add_rubrique
    );

    if ($show_add_rubrique && function_exists('em_wp_admin_render_template_skeleton_add_panel')) {
        em_wp_admin_render_template_skeleton_add_panel();
    }

    em_wp_admin_hub_sticky_head_close();
}

/**
 * En-tête hub (Hello + bandeau template + intro) pour une page rubrique en édition.
 */
function em_wp_admin_rubrique_render_editing_page_header(string $module_slug): void
{
    if (!function_exists('em_wp_admin_hub_render_sommaire_header')) {
        return;
    }

    em_wp_admin_hub_render_sommaire_header(
        '',
        'dashicons-admin-page',
        false,
        true,
        null,
        null,
        true
    );

    em_wp_admin_rubrique_render_entry_tabs($module_slug);
}

/**
 * Nom du champ options « enabled » pour une rubrique (vide si non applicable).
 */
function em_wp_admin_rubrique_enabled_field_name(string $module_slug): string
{
    $getters = [
        'top-bar' => 'em_wp_top_bar_form_option_key',
        'header'  => 'em_wp_header_form_option_key',
        'stream'  => 'em_wp_stream_form_option_key',
        'social'  => 'em_wp_social_form_option_key',
        'video'   => 'em_wp_video_form_option_key',
        'release' => 'em_wp_release_form_option_key',
        'cta'     => 'em_wp_cta_form_option_key',
        'footer'  => 'em_wp_footer_form_option_key',
    ];

    $getter = $getters[$module_slug] ?? '';

    if ($getter === '' && function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
        $getter = 'em_wp_custom_catalog_rubrique_form_option_key';
    }

    if ($getter === '' || !function_exists($getter)) {
        return '';
    }

    return (string) call_user_func($getter, $module_slug);
}

/**
 * Indique si la barre rubrique affiche un toggle « Afficher ».
 */
function em_wp_admin_rubrique_section_has_toggle(string $module_slug): bool
{
    return em_wp_admin_rubrique_enabled_field_name($module_slug) !== '';
}

/**
 * Toggle « Afficher » marron dans la barre titre rubrique.
 *
 * @param array<string, mixed> $options Options module (clé enabled pour les rubriques classiques).
 */
function em_wp_admin_rubrique_render_section_toggle(string $module_slug, array $options = []): void
{
    $field = em_wp_admin_rubrique_enabled_field_name($module_slug);

    if ($field === '') {
        return;
    }
    ?>
    <label class="em-wp-rubrique-section-bar__toggle">
        <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
        <?php if (in_array($module_slug, ['footer', 'header'], true)) { ?>
            <input type="hidden" name="<?php echo esc_attr($field); ?>[enabled]" value="0">
        <?php } ?>
        <input
            type="checkbox"
            name="<?php echo esc_attr($field); ?>[enabled]"
            value="1"
            <?php checked(!empty($options['enabled'])); ?>
        >
    </label>
    <?php
}

/**
 * Ouvre le bloc rubrique (barre titre + bordure haute) avant les panneaux.
 *
 * @param array<string, mixed> $options Options module (enabled…).
 */
function em_wp_admin_rubrique_open_section(string $module_slug, array $options = []): void
{
    ?>
    <div class="em-wp-rubrique-section">
        <div class="em-wp-rubrique-section-bar">
            <div class="em-wp-rubrique-section-bar__heading">
                <h2 class="em-wp-rubrique-section-bar__title">
                    <span class="em-wp-admin-module__section-module-pill"><?php echo esc_html(em_wp_admin_rubrique_label($module_slug)); ?></span>
                    <?php
                    $template_label = em_wp_admin_rubrique_editing_template_label();
                    if ($template_label !== '') {
                        ?>
                        <span class="em-wp-rubrique-section-bar__template"><?php echo esc_html($template_label); ?></span>
                        <?php
                    }
                    ?>
                </h2>
                <?php if (em_wp_admin_rubrique_section_has_toggle($module_slug)) {
                    em_wp_admin_rubrique_render_section_toggle($module_slug, $options);
                } ?>
            </div>
        </div>
        <div class="em-wp-rubrique-section__content">
    <?php
}

/**
 * Ferme le bloc rubrique ouvert par em_wp_admin_rubrique_open_section().
 */
function em_wp_admin_rubrique_close_section(): void
{
    ?>
        </div>
    </div>
    <?php
}
