<?php
/**
 * Enregistrement page Rubriques + assets.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre la page sommaire dans le menu admin.
 */
function em_site_admin_rubriques_add_admin_page(): void
{
    add_menu_page(
        __('Rubriques', 'em-site'),
        __('RUBRIQUES', 'em-site'),
        'manage_options',
        em_site_admin_rubriques_page_slug(),
        'em_site_admin_render_rubriques_page',
        'dashicons-admin-home',
        em_site_admin_menu_section_label_position()
    );
}
add_action('admin_menu', 'em_site_admin_rubriques_add_admin_page');

/**
 * Retire le sous-menu dupliqué créé automatiquement par WordPress.
 */
function em_site_admin_rubriques_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_site_admin_rubriques_page_slug(), em_site_admin_rubriques_page_slug());
}
add_action('admin_menu', 'em_site_admin_rubriques_remove_duplicate_submenu', 999);

/**
 * Charge les assets de la page sommaire.
 */
function em_site_admin_rubriques_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug !== em_site_admin_rubriques_page_slug()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    em_site_admin_hub_cards_enqueue_assets();
    em_site_admin_rubrique_enqueue_nav_assets($page_slug);

    if (!em_site_admin_has_template_context()) {
        return;
    }

    // Médiathèque WP : choix de l'image de fond partagée du HEADER.
    wp_enqueue_media();

    // CSS du SLIDER front (mayami) : le wireframe du squelette rend de vraies
    // rubriques EM-SITE, dont le champ « Slider » (template mayami). Sans ce CSS, les
    // slides s'empilent en pleine hauteur au lieu d'occuper le cadre du slider.
    $slider_css_rel = '/assets/front/shared/css/slider.css';
    $slider_css_path = get_template_directory() . $slider_css_rel;
    if (file_exists($slider_css_path)) {
        wp_enqueue_style(
            'em-site-slider-mayami',
            $theme_uri . $slider_css_rel,
            [],
            (string) filemtime($slider_css_path)
        );
    }

    wp_enqueue_script(
        'em-site-admin-slide-sortable',
        $theme_uri . '/assets/admin/shared/js/media/slide-sortable.js',
        [],
        em_site_admin_asset_version('assets/admin/shared/js/media/slide-sortable.js'),
        true
    );

    wp_enqueue_script(
        'em-site-admin-rubriques',
        $theme_uri . '/assets/admin/js/pages/rubriques/rubriques.js',
        [],
        em_site_admin_asset_version('assets/admin/js/pages/rubriques/rubriques.js'),
        true
    );

    wp_enqueue_script(
        'em-site-admin-rubriques-sortable-state',
        $theme_uri . '/assets/admin/js/pages/rubriques/sortable/state.js',
        [],
        em_site_admin_asset_version('assets/admin/js/pages/rubriques/sortable/state.js'),
        true
    );

    wp_enqueue_script(
        'em-site-admin-rubriques-sortable-ordering',
        $theme_uri . '/assets/admin/js/pages/rubriques/sortable/ordering.js',
        ['em-site-admin-rubriques-sortable-state'],
        em_site_admin_asset_version('assets/admin/js/pages/rubriques/sortable/ordering.js'),
        true
    );

    wp_enqueue_script(
        'em-site-admin-rubriques-sortable',
        $theme_uri . '/assets/admin/js/pages/rubriques/rubriques-sortable.js',
        ['em-site-admin-slide-sortable', 'em-site-admin-rubriques-sortable-state', 'em-site-admin-rubriques-sortable-ordering'],
        em_site_admin_asset_version('assets/admin/js/pages/rubriques/rubriques-sortable.js'),
        true
    );

    wp_enqueue_script(
        'em-site-admin-template-skeleton',
        $theme_uri . '/assets/admin/js/pages/rubriques/template-skeleton.js',
        ['em-site-admin-confirm-modal', 'em-site-admin-color-picker'],
        em_site_admin_asset_version('assets/admin/js/pages/rubriques/template-skeleton.js'),
        true
    );

    wp_localize_script(
        'em-site-admin-rubriques-sortable',
        'emWpRubriquesSortable',
        [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('em_site_rubrique_order'),
            'templateSlug' => function_exists('em_site_get_editing_template_slug')
                ? em_site_get_editing_template_slug()
                : '',
            'i18n'    => [
                'saved'                 => __('Ordre enregistré.', 'em-site'),
                'error'                 => __('Impossible d\'enregistrer l\'ordre.', 'em-site'),
                'handle'                => __('Réordonner', 'em-site'),
                'swapHeroSlider'        => __('Inverser Hero et Slider dans HEADER', 'em-site'),
                'layoutSaved'           => __('Layout HEADER enregistré.', 'em-site'),
                'layoutError'           => __('Impossible d\'enregistrer le layout HEADER.', 'em-site'),
                'visibilityShown'       => __('Afficher sur le site', 'em-site'),
                'visibilityHidden'      => __('Masquer sur le site', 'em-site'),
                'visibilityHiddenLabel' => __('Masqué', 'em-site'),
                'visibilitySaved'       => __('Visibilité enregistrée.', 'em-site'),
                'visibilityError'       => __('Impossible d\'enregistrer la visibilité.', 'em-site'),
            ],
        ]
    );

    wp_localize_script(
        'em-site-admin-template-skeleton',
        'emWpTemplateSkeleton',
        [
            'ajaxUrl'        => admin_url('admin-ajax.php'),
            'nonce'          => wp_create_nonce('em_site_rubrique_order'),
            'templateSlug'   => function_exists('em_site_get_editing_template_slug')
                ? em_site_get_editing_template_slug()
                : '',
            'templateLabel'  => function_exists('em_site_get_editing_template_label')
                ? em_site_get_editing_template_label()
                : '',
            'isLiveTemplate' => function_exists('em_site_get_editing_template_slug')
                && function_exists('em_site_get_active_template_slug')
                && em_site_get_editing_template_slug() === em_site_get_active_template_slug(),
            'i18n'           => [
                'saved'                => __('Rubrique mise à jour.', 'em-site'),
                'error'                => __('Impossible de mettre à jour le squelette.', 'em-site'),
                'confirmRemove'          => __('Retirer « %s » du squelette de ce template ?', 'em-site'),
                'confirmRemoveTitle'     => __('Retirer du squelette', 'em-site'),
                'confirmRemoveLive'      => __(
                    "Le template %1\$s est actuellement en ligne.\n\nRetirer « %2\$s » du squelette modifiera le site public immédiatement.",
                    'em-site'
                ),
                'confirmRemoveLiveTitle' => __('Template en ligne — attention', 'em-site'),
                'confirmRemoveLiveAck'   => __('J\'ai bien compris que cette modification sera visible immédiatement sur le site public.', 'em-site'),
                'confirmRemoveLabel'     => __('Retirer du squelette', 'em-site'),
                'confirmRemoveLiveLabel' => __('Oui, modifier le site', 'em-site'),
                'cancelLabel'            => __('Annuler', 'em-site'),
                'loadingPicker'          => __('Chargement de la rubrique…', 'em-site'),
                'pickerLoadError'        => __('Impossible d\'ouvrir cette rubrique.', 'em-site'),
            ],
        ]
    );
}
add_action('admin_enqueue_scripts', 'em_site_admin_rubriques_enqueue');

/**
 * Onglets Rubriques sur les pages d'édition (TOP-BAR, HEADER, STREAM…).
 */
function em_site_admin_rubrique_modules_nav_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    em_site_admin_rubrique_enqueue_nav_assets($page_slug);
}
add_action('admin_enqueue_scripts', 'em_site_admin_rubrique_modules_nav_enqueue', 20);
