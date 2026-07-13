<?php
/**
 * Bandeau sélecteur « Template en édition » (toutes pages em-site).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Indique si l'écran admin courant est une page catalogue Hero/Slider.
 *
 * Les catalogues ne sont pas rattachés à un template : pas de bandeau template.
 */
function em_site_admin_is_catalog_screen(): bool
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

    if ($page_slug === '') {
        return false;
    }

    if (function_exists('em_site_catalog_admin_page_slugs') && in_array($page_slug, em_site_catalog_admin_page_slugs(), true)) {
        return true;
    }

    if (function_exists('em_site_hero_catalog_slug_from_page') && em_site_hero_catalog_slug_from_page($page_slug) !== '') {
        return true;
    }

    if (function_exists('em_site_slider_catalog_slug_from_page') && em_site_slider_catalog_slug_from_page($page_slug) !== '') {
        return true;
    }

    if (function_exists('em_site_video_catalog_slug_from_page') && em_site_video_catalog_slug_from_page($page_slug) !== '') {
        return true;
    }

    if (function_exists('em_site_stream_catalog_slug_from_page') && em_site_stream_catalog_slug_from_page($page_slug) !== '') {
        return true;
    }

    if (function_exists('em_site_social_catalog_slug_from_page') && em_site_social_catalog_slug_from_page($page_slug) !== '') {
        return true;
    }

    if (function_exists('em_site_hero_legacy_page_slug_map') && isset(em_site_hero_legacy_page_slug_map()[$page_slug])) {
        return true;
    }

    if (function_exists('em_site_slider_legacy_page_slug_map') && isset(em_site_slider_legacy_page_slug_map()[$page_slug])) {
        return true;
    }

    return false;
}

/**
 * Indique si le bandeau template doit s'afficher sur l'écran courant.
 */
function em_site_admin_should_show_template_banner(): bool
{
    if (function_exists('em_site_admin_should_show_template_editing_banner')) {
        return em_site_admin_should_show_template_editing_banner();
    }

    return em_site_admin_is_em_site_screen() && !em_site_admin_is_catalog_screen();
}

/**
 * Indique si l'écran admin courant est une page em-site.
 */
function em_site_admin_is_em_site_screen(): bool
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
 * Enqueue assets bandeau template.
 */
function em_site_admin_template_banner_enqueue(): void
{
    if (!em_site_admin_should_show_template_banner()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    if (!wp_script_is('em-site-admin-confirm-modal', 'registered')) {
        wp_register_script(
            'em-site-admin-confirm-modal',
            $theme_uri . '/assets/admin/shared/js/modals/confirm-modal.js',
            [],
            em_site_admin_asset_version('assets/admin/shared/js/modals/confirm-modal.js'),
            true
        );
    }

    if (function_exists('em_site_admin_hub_cards_enqueue_assets')) {
        em_site_admin_hub_cards_enqueue_assets();
    }

    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );

    wp_enqueue_style(
        'em-site-admin-template-banner',
        $theme_uri . '/assets/admin/css/template/banner.css',
        ['em-site-admin-hub-cards'],
        em_site_admin_asset_version('assets/admin/css/template/banner.css')
    );

    wp_enqueue_script(
        'em-site-admin-module-form-dirty-engine',
        $theme_uri . '/assets/admin/shared/js/state/module-form-dirty/engine.js',
        ['em-site-admin-confirm-modal'],
        em_site_admin_asset_version('assets/admin/shared/js/state/module-form-dirty/engine.js'),
        true
    );

    wp_enqueue_script(
        'em-site-admin-module-form-dirty',
        $theme_uri . '/assets/admin/shared/js/state/module-form-dirty.js',
        ['em-site-admin-confirm-modal', 'em-site-admin-module-form-dirty-engine'],
        em_site_admin_asset_version('assets/admin/shared/js/state/module-form-dirty.js'),
        true
    );

    wp_localize_script(
        'em-site-admin-module-form-dirty',
        'EmWpModuleFormDirtyConfig',
        [
            'i18n' => [
                'saveConfirm' => __('Enregistrer la configuration actuelle et rester sur cette page ?', 'em-site'),
                'saveLabel'   => __('Enregistrer', 'em-site'),
                'cancelLabel' => __('Annuler', 'em-site'),
            ],
        ]
    );

    wp_enqueue_script(
        'em-site-admin-template-banner',
        $theme_uri . '/assets/admin/js/template/banner.js',
        ['em-site-admin-confirm-modal', 'em-site-admin-module-form-dirty'],
        em_site_admin_asset_version('assets/admin/js/template/banner.js'),
        true
    );

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current_page = sanitize_key((string) ($_GET['page'] ?? ''));
    $quit_config = em_site_admin_template_banner_quit_config($current_page);

    wp_localize_script(
        'em-site-admin-template-banner',
        'EmWpTemplateBanner',
        [
            'quitMode' => (string) ($quit_config['mode'] ?? 'redirect'),
            'quitUrl'  => (string) ($quit_config['url'] ?? ''),
            'i18n'     => [
                'saveConfirm'           => __('Enregistrer la configuration actuelle et rester sur cette page ?', 'em-site'),
                'saveLabel'             => __('Enregistrer', 'em-site'),
                'saveAutoMessage'       => __('Les modifications sont enregistrées automatiquement.', 'em-site'),
                'saveAutoOk'            => __('OK', 'em-site'),
                'quitConfirm'           => __('Quitter et retourner au sommaire Rubriques ?', 'em-site'),
                'quitExitConfirm'       => __('Quitter et retourner au sommaire Templates ?', 'em-site'),
                'quitLabel'             => __('Quitter', 'em-site'),
                'cancelLabel'           => __('Annuler', 'em-site'),
                'switchConfirmTemplate' => __('Tu vas basculer l\'édition vers le template « %s ».', 'em-site'),
                'switchConfirm'         => __('Basculer', 'em-site'),
                'switchConfirmSave'     => __('Enregistrer & Basculer', 'em-site'),
                'activateConfirm'       => __('Activer le template %s sur le site public ?', 'em-site'),
                'activateLabel'         => __('Activer', 'em-site'),
            ],
        ]
    );
}
add_action('admin_enqueue_scripts', 'em_site_admin_template_banner_enqueue');

/**
 * Classe body pour décaler le contenu sous le bandeau.
 */
function em_site_admin_template_banner_body_class(string $classes): string
{
    if (em_site_admin_should_show_template_banner()) {
        $classes .= ' em-site-has-template-banner';
    }

    return $classes;
}
add_filter('admin_body_class', 'em_site_admin_template_banner_body_class');

/**
 * Configuration du bouton « Quitter » selon la page courante.
 *
 * @return array{mode:string,url:string}
 */
function em_site_admin_template_banner_quit_config(string $current_page): array
{
    if (
        function_exists('em_site_admin_rubriques_page_slug')
        && $current_page === em_site_admin_rubriques_page_slug()
    ) {
        return [
            'mode' => 'quit_to_templates',
            'url'  => function_exists('em_site_admin_template_choice_admin_url')
                ? em_site_admin_template_choice_admin_url()
                : admin_url('admin.php'),
        ];
    }

    return [
        'mode' => 'redirect',
        'url'  => function_exists('em_site_admin_rubriques_admin_url')
            ? em_site_admin_rubriques_admin_url()
            : admin_url('admin.php'),
    ];
}

/**
 * Rendu HTML du bandeau (inline sous l'en-tête de page).
 */
function em_site_admin_template_render_banner(): void
{
    if (!em_site_admin_should_show_template_banner() || !current_user_can('manage_options')) {
        return;
    }

    $registry = em_site_template_registry();
    $editing_slug = em_site_get_editing_template_slug();
    $active_slug = em_site_get_active_template_slug();
    $editing_label = (string) ($registry[$editing_slug]['label'] ?? $editing_slug);
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    $differs = em_site_template_editing_differs_from_live();
    $is_live = !$differs;

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current_page = sanitize_key((string) ($_GET['page'] ?? ''));
    $quit_config = em_site_admin_template_banner_quit_config($current_page);
    $quit_mode = (string) ($quit_config['mode'] ?? 'redirect');
    $quit_action = $quit_mode === 'quit_to_templates' ? 'quit_to_templates' : 'clear_editing';
    $quit_nonce_action = $quit_mode === 'quit_to_templates' ? 'em_site_template_quit_to_templates' : 'em_site_template_clear_editing';
    ?>
    <div
        class="em-site-template-banner em-site-template-banner--inline<?php echo $differs ? ' is-editing-differs' : ' is-editing-live'; ?>"
        role="region"
        aria-label="<?php esc_attr_e('Contexte template EM-SITE', 'em-site'); ?>"
        data-editing-slug="<?php echo esc_attr($editing_slug); ?>"
        data-active-slug="<?php echo esc_attr($active_slug); ?>"
    >
        <div class="em-site-template-banner__inner">
            <div class="em-site-template-banner__block em-site-template-banner__block--editing">
                <span class="em-site-template-banner__label">
                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                    <?php esc_html_e('Template en cours d\'édition', 'em-site'); ?>
                </span>
                <form class="em-site-template-banner__form em-site-template-banner__form--editing" method="post" action="">
                    <?php wp_nonce_field('em_site_template_set_editing'); ?>
                    <input type="hidden" name="em_site_template_action" value="set_editing">
                    <input type="hidden" name="em_site_template_redirect_page" value="<?php echo esc_attr($current_page); ?>">
                    <label class="screen-reader-text" for="em-site-template-editing-select">
                        <?php esc_html_e('Choisir le template à éditer', 'em-site'); ?>
                    </label>
                    <select
                        id="em-site-template-editing-select"
                        name="em_site_template_editing_slug"
                        class="em-site-template-banner__select"
                    >
                        <?php foreach ($registry as $slug => $definition) { ?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php selected($editing_slug, $slug); ?>>
                                <?php echo esc_html((string) ($definition['label'] ?? $slug)); ?>
                            </option>
                        <?php } ?>
                    </select>
                </form>
                <div class="em-site-template-banner__actions">
                    <a
                        class="em-site-template-banner__preview"
                        href="<?php echo esc_url(em_site_template_preview_url($editing_slug)); ?>"
                        target="_blank"
                        title="<?php echo esc_attr(sprintf(
                            /* translators: %s: template label */
                            __('Prévisualiser le template %s dans un nouvel onglet', 'em-site'),
                            $editing_label
                        )); ?>"
                    >
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        <?php esc_html_e('Aperçu', 'em-site'); ?>
                    </a>
                    <button type="button" class="em-site-template-banner__save" id="em-site-template-banner-save" disabled aria-disabled="true">
                        <?php esc_html_e('Enregistrer', 'em-site'); ?>
                    </button>
                    <button type="button" class="em-site-template-banner__quit" id="em-site-template-banner-quit">
                        <?php esc_html_e('Quitter', 'em-site'); ?>
                    </button>
                </div>
                <form class="em-site-template-banner__quit-form" id="em-site-template-banner-quit-form" method="post" action="" hidden>
                    <?php wp_nonce_field($quit_nonce_action); ?>
                    <input type="hidden" name="em_site_template_action" value="<?php echo esc_attr($quit_action); ?>">
                </form>
            </div>

            <div class="em-site-template-banner__block em-site-template-banner__block--live">
                <?php em_site_admin_hub_render_template_active_pill($is_live ? $editing_label : $active_label, $is_live ? $editing_slug : $active_slug); ?>
                <?php if (!$is_live) { ?>
                    <?php
                    em_site_admin_hub_render_template_activate_pill($editing_slug, $editing_label, [
                        'id' => 'em-site-template-banner-activate-live',
                    ]);
                    ?>
                <?php } ?>
            </div>
            <?php em_site_admin_hub_render_template_set_live_form($current_page); ?>
        </div>
    </div>
    <?php
}

