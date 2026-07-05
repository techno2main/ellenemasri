<?php
/**
 * Enregistrement page Rubriques + assets.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre la page sommaire dans le menu admin.
 */
function em_wp_admin_rubriques_add_admin_page(): void
{
    add_menu_page(
        __('Rubriques', 'em-wp'),
        __('RUBRIQUES', 'em-wp'),
        'manage_options',
        em_wp_admin_rubriques_page_slug(),
        'em_wp_admin_render_rubriques_page',
        'dashicons-admin-home',
        em_wp_admin_menu_section_label_position()
    );
}
add_action('admin_menu', 'em_wp_admin_rubriques_add_admin_page');

/**
 * Retire le sous-menu dupliquÃ© crÃ©Ã© automatiquement par WordPress.
 */
function em_wp_admin_rubriques_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_admin_rubriques_page_slug(), em_wp_admin_rubriques_page_slug());
}
add_action('admin_menu', 'em_wp_admin_rubriques_remove_duplicate_submenu', 999);

/**
 * Charge les assets de la page sommaire.
 */
function em_wp_admin_rubriques_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if ($page_slug !== em_wp_admin_rubriques_page_slug()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    em_wp_admin_hub_cards_enqueue_assets();
    em_wp_admin_rubrique_enqueue_nav_assets($page_slug);

    if (!em_wp_admin_has_template_context()) {
        return;
    }

    // MÃ©diathÃ¨que WP : choix de l'image de fond partagÃ©e du HEADER.
    wp_enqueue_media();

    // CSS du SLIDER front (mayami) : le wireframe du squelette rend de vraies
    // rubriques V4, dont le champ Â« Slider Â» (template mayami). Sans ce CSS, les
    // slides s'empilent en pleine hauteur au lieu d'occuper le cadre du slider.
    $slider_css_rel = '/assets/front/css/modules/slider/mayami/slider.css';
    $slider_css_path = get_template_directory() . $slider_css_rel;
    if (file_exists($slider_css_path)) {
        wp_enqueue_style(
            'em-wp-slider-mayami',
            $theme_uri . $slider_css_rel,
            [],
            (string) filemtime($slider_css_path)
        );
    }

    wp_enqueue_script(
        'em-wp-admin-slide-sortable',
        $theme_uri . '/assets/admin/js/shared/media/slide-sortable.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/media/slide-sortable.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-rubriques',
        $theme_uri . '/assets/admin/js/pages/rubriques/rubriques.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/pages/rubriques/rubriques.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-rubriques-sortable-state',
        $theme_uri . '/assets/admin/js/pages/rubriques/sortable/state.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/pages/rubriques/sortable/state.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-rubriques-sortable-ordering',
        $theme_uri . '/assets/admin/js/pages/rubriques/sortable/ordering.js',
        ['em-wp-admin-rubriques-sortable-state'],
        em_wp_admin_asset_version('assets/admin/js/pages/rubriques/sortable/ordering.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-rubriques-sortable',
        $theme_uri . '/assets/admin/js/pages/rubriques/rubriques-sortable.js',
        ['em-wp-admin-slide-sortable', 'em-wp-admin-rubriques-sortable-state', 'em-wp-admin-rubriques-sortable-ordering'],
        em_wp_admin_asset_version('assets/admin/js/pages/rubriques/rubriques-sortable.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-template-skeleton',
        $theme_uri . '/assets/admin/js/pages/rubriques/template-skeleton.js',
        ['em-wp-admin-confirm-modal', 'em-wp-admin-color-picker'],
        em_wp_admin_asset_version('assets/admin/js/pages/rubriques/template-skeleton.js'),
        true
    );

    wp_localize_script(
        'em-wp-admin-rubriques-sortable',
        'emWpRubriquesSortable',
        [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('em_wp_rubrique_order'),
            'templateSlug' => function_exists('em_wp_get_editing_template_slug')
                ? em_wp_get_editing_template_slug()
                : '',
            'i18n'    => [
                'saved'                 => __('Ordre enregistrÃ©.', 'em-wp'),
                'error'                 => __('Impossible d\'enregistrer l\'ordre.', 'em-wp'),
                'handle'                => __('RÃ©ordonner', 'em-wp'),
                'swapHeroSlider'        => __('Inverser Hero et Slider dans HEADER', 'em-wp'),
                'layoutSaved'           => __('Layout HEADER enregistrÃ©.', 'em-wp'),
                'layoutError'           => __('Impossible d\'enregistrer le layout HEADER.', 'em-wp'),
                'visibilityShown'       => __('Afficher sur le site', 'em-wp'),
                'visibilityHidden'      => __('Masquer sur le site', 'em-wp'),
                'visibilityHiddenLabel' => __('MasquÃ©', 'em-wp'),
                'visibilitySaved'       => __('VisibilitÃ© enregistrÃ©e.', 'em-wp'),
                'visibilityError'       => __('Impossible d\'enregistrer la visibilitÃ©.', 'em-wp'),
            ],
        ]
    );

    wp_localize_script(
        'em-wp-admin-template-skeleton',
        'emWpTemplateSkeleton',
        [
            'ajaxUrl'        => admin_url('admin-ajax.php'),
            'nonce'          => wp_create_nonce('em_wp_rubrique_order'),
            'templateSlug'   => function_exists('em_wp_get_editing_template_slug')
                ? em_wp_get_editing_template_slug()
                : '',
            'templateLabel'  => function_exists('em_wp_get_editing_template_label')
                ? em_wp_get_editing_template_label()
                : '',
            'isLiveTemplate' => function_exists('em_wp_get_editing_template_slug')
                && function_exists('em_wp_get_active_template_slug')
                && em_wp_get_editing_template_slug() === em_wp_get_active_template_slug(),
            'i18n'           => [
                'saved'                => __('Rubrique mise Ã  jour.', 'em-wp'),
                'error'                => __('Impossible de mettre Ã  jour le squelette.', 'em-wp'),
                'confirmRemove'          => __('Retirer Â« %s Â» du squelette de ce template ?', 'em-wp'),
                'confirmRemoveTitle'     => __('Retirer du squelette', 'em-wp'),
                'confirmRemoveLive'      => __(
                    "Le template %1\$s est actuellement en ligne.\n\nRetirer Â« %2\$s Â» du squelette modifiera le site public immÃ©diatement.",
                    'em-wp'
                ),
                'confirmRemoveLiveTitle' => __('Template en ligne â€” attention', 'em-wp'),
                'confirmRemoveLiveAck'   => __('J\'ai bien compris que cette modification sera visible immÃ©diatement sur le site public.', 'em-wp'),
                'confirmRemoveLabel'     => __('Retirer du squelette', 'em-wp'),
                'confirmRemoveLiveLabel' => __('Oui, modifier le site', 'em-wp'),
                'cancelLabel'            => __('Annuler', 'em-wp'),
            ],
        ]
    );
}
add_action('admin_enqueue_scripts', 'em_wp_admin_rubriques_enqueue');

/**
 * Onglets Rubriques sur les pages d'Ã©dition (TOP-BAR, HEADER, STREAMâ€¦).
 */
function em_wp_admin_rubrique_modules_nav_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    em_wp_admin_rubrique_enqueue_nav_assets($page_slug);
}
add_action('admin_enqueue_scripts', 'em_wp_admin_rubrique_modules_nav_enqueue', 20);
