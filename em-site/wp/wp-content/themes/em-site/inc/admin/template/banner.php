<?php
/**
 * Bandeau sélecteur « Template en édition » (toutes pages em-wp).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Indique si l'écran admin courant est une page catalogue Hero/Slider.
 *
 * Les catalogues ne sont pas rattachés à un template : pas de bandeau template.
 */
function em_wp_admin_is_catalog_screen(): bool
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

    if (function_exists('em_wp_catalog_admin_page_slugs') && in_array($page_slug, em_wp_catalog_admin_page_slugs(), true)) {
        return true;
    }

    if (function_exists('em_wp_hero_catalog_slug_from_page') && em_wp_hero_catalog_slug_from_page($page_slug) !== '') {
        return true;
    }

    if (function_exists('em_wp_slider_catalog_slug_from_page') && em_wp_slider_catalog_slug_from_page($page_slug) !== '') {
        return true;
    }

    if (function_exists('em_wp_video_catalog_slug_from_page') && em_wp_video_catalog_slug_from_page($page_slug) !== '') {
        return true;
    }

    if (function_exists('em_wp_stream_catalog_slug_from_page') && em_wp_stream_catalog_slug_from_page($page_slug) !== '') {
        return true;
    }

    if (function_exists('em_wp_social_catalog_slug_from_page') && em_wp_social_catalog_slug_from_page($page_slug) !== '') {
        return true;
    }

    if (function_exists('em_wp_hero_legacy_page_slug_map') && isset(em_wp_hero_legacy_page_slug_map()[$page_slug])) {
        return true;
    }

    if (function_exists('em_wp_slider_legacy_page_slug_map') && isset(em_wp_slider_legacy_page_slug_map()[$page_slug])) {
        return true;
    }

    return false;
}

/**
 * Indique si le bandeau template doit s'afficher sur l'écran courant.
 */
function em_wp_admin_should_show_template_banner(): bool
{
    if (function_exists('em_wp_admin_should_show_template_editing_banner')) {
        return em_wp_admin_should_show_template_editing_banner();
    }

    return em_wp_admin_is_em_wp_screen() && !em_wp_admin_is_catalog_screen();
}

/**
 * Indique si l'écran admin courant est une page em-wp.
 */
function em_wp_admin_is_em_wp_screen(): bool
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
function em_wp_admin_template_banner_enqueue(): void
{
    if (!em_wp_admin_should_show_template_banner()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    if (!wp_script_is('em-wp-admin-confirm-modal', 'registered')) {
        wp_register_script(
            'em-wp-admin-confirm-modal',
            $theme_uri . '/assets/admin/shared/js/modals/confirm-modal.js',
            [],
            em_wp_admin_asset_version('assets/admin/shared/js/modals/confirm-modal.js'),
            true
        );
    }

    if (function_exists('em_wp_admin_hub_cards_enqueue_assets')) {
        em_wp_admin_hub_cards_enqueue_assets();
    }

    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );

    wp_enqueue_style(
        'em-wp-admin-template-banner',
        $theme_uri . '/assets/admin/css/template/banner.css',
        ['em-wp-admin-hub-cards'],
        em_wp_admin_asset_version('assets/admin/css/template/banner.css')
    );

    wp_enqueue_script(
        'em-wp-admin-module-form-dirty-engine',
        $theme_uri . '/assets/admin/shared/js/state/module-form-dirty/engine.js',
        ['em-wp-admin-confirm-modal'],
        em_wp_admin_asset_version('assets/admin/shared/js/state/module-form-dirty/engine.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-module-form-dirty',
        $theme_uri . '/assets/admin/shared/js/state/module-form-dirty.js',
        ['em-wp-admin-confirm-modal', 'em-wp-admin-module-form-dirty-engine'],
        em_wp_admin_asset_version('assets/admin/shared/js/state/module-form-dirty.js'),
        true
    );

    wp_localize_script(
        'em-wp-admin-module-form-dirty',
        'EmWpModuleFormDirtyConfig',
        [
            'i18n' => [
                'saveConfirm' => __('Enregistrer la configuration actuelle et rester sur cette page ?', 'em-wp'),
                'saveLabel'   => __('Enregistrer', 'em-wp'),
                'cancelLabel' => __('Annuler', 'em-wp'),
            ],
        ]
    );

    wp_enqueue_script(
        'em-wp-admin-template-banner',
        $theme_uri . '/assets/admin/js/template/banner.js',
        ['em-wp-admin-confirm-modal', 'em-wp-admin-module-form-dirty'],
        em_wp_admin_asset_version('assets/admin/js/template/banner.js'),
        true
    );

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current_page = sanitize_key((string) ($_GET['page'] ?? ''));
    $quit_config = em_wp_admin_template_banner_quit_config($current_page);

    wp_localize_script(
        'em-wp-admin-template-banner',
        'EmWpTemplateBanner',
        [
            'quitMode' => (string) ($quit_config['mode'] ?? 'redirect'),
            'quitUrl'  => (string) ($quit_config['url'] ?? ''),
            'i18n'     => [
                'saveConfirm'           => __('Enregistrer la configuration actuelle et rester sur cette page ?', 'em-wp'),
                'saveLabel'             => __('Enregistrer', 'em-wp'),
                'saveAutoMessage'       => __('Les modifications sont enregistrées automatiquement.', 'em-wp'),
                'saveAutoOk'            => __('OK', 'em-wp'),
                'quitConfirm'           => __('Quitter et retourner au sommaire Rubriques ?', 'em-wp'),
                'quitExitConfirm'       => __('Quitter et retourner au sommaire Templates ?', 'em-wp'),
                'quitLabel'             => __('Quitter', 'em-wp'),
                'cancelLabel'           => __('Annuler', 'em-wp'),
                'switchConfirmTemplate' => __('Tu vas basculer l\'édition vers le template « %s ».', 'em-wp'),
                'switchConfirm'         => __('Basculer', 'em-wp'),
                'switchConfirmSave'     => __('Enregistrer & Basculer', 'em-wp'),
                'activateConfirm'       => __('Activer le template %s sur le site public ?', 'em-wp'),
                'activateLabel'         => __('Activer', 'em-wp'),
            ],
        ]
    );
}
add_action('admin_enqueue_scripts', 'em_wp_admin_template_banner_enqueue');

/**
 * Classe body pour décaler le contenu sous le bandeau.
 */
function em_wp_admin_template_banner_body_class(string $classes): string
{
    if (em_wp_admin_should_show_template_banner()) {
        $classes .= ' em-wp-has-template-banner';
    }

    return $classes;
}
add_filter('admin_body_class', 'em_wp_admin_template_banner_body_class');

/**
 * Configuration du bouton « Quitter » selon la page courante.
 *
 * @return array{mode:string,url:string}
 */
function em_wp_admin_template_banner_quit_config(string $current_page): array
{
    if (
        function_exists('em_wp_admin_rubriques_page_slug')
        && $current_page === em_wp_admin_rubriques_page_slug()
    ) {
        return [
            'mode' => 'quit_to_templates',
            'url'  => function_exists('em_wp_admin_template_choice_admin_url')
                ? em_wp_admin_template_choice_admin_url()
                : admin_url('admin.php'),
        ];
    }

    return [
        'mode' => 'redirect',
        'url'  => function_exists('em_wp_admin_rubriques_admin_url')
            ? em_wp_admin_rubriques_admin_url()
            : admin_url('admin.php'),
    ];
}

/**
 * Rendu HTML du bandeau (inline sous l'en-tête de page).
 */
function em_wp_admin_template_render_banner(): void
{
    if (!em_wp_admin_should_show_template_banner() || !current_user_can('manage_options')) {
        return;
    }

    $registry = em_wp_template_registry();
    $editing_slug = em_wp_get_editing_template_slug();
    $active_slug = em_wp_get_active_template_slug();
    $editing_label = (string) ($registry[$editing_slug]['label'] ?? $editing_slug);
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    $differs = em_wp_template_editing_differs_from_live();
    $is_live = !$differs;

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current_page = sanitize_key((string) ($_GET['page'] ?? ''));
    $quit_config = em_wp_admin_template_banner_quit_config($current_page);
    $quit_mode = (string) ($quit_config['mode'] ?? 'redirect');
    $quit_action = $quit_mode === 'quit_to_templates' ? 'quit_to_templates' : 'clear_editing';
    $quit_nonce_action = $quit_mode === 'quit_to_templates' ? 'em_wp_template_quit_to_templates' : 'em_wp_template_clear_editing';
    ?>
    <div
        class="em-wp-template-banner em-wp-template-banner--inline<?php echo $differs ? ' is-editing-differs' : ' is-editing-live'; ?>"
        role="region"
        aria-label="<?php esc_attr_e('Contexte template EM-WP', 'em-wp'); ?>"
        data-editing-slug="<?php echo esc_attr($editing_slug); ?>"
        data-active-slug="<?php echo esc_attr($active_slug); ?>"
    >
        <div class="em-wp-template-banner__inner">
            <div class="em-wp-template-banner__block em-wp-template-banner__block--editing">
                <span class="em-wp-template-banner__label">
                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                    <?php esc_html_e('Template en cours d\'édition', 'em-wp'); ?>
                </span>
                <form class="em-wp-template-banner__form em-wp-template-banner__form--editing" method="post" action="">
                    <?php wp_nonce_field('em_wp_template_set_editing'); ?>
                    <input type="hidden" name="em_wp_template_action" value="set_editing">
                    <input type="hidden" name="em_wp_template_redirect_page" value="<?php echo esc_attr($current_page); ?>">
                    <label class="screen-reader-text" for="em-wp-template-editing-select">
                        <?php esc_html_e('Choisir le template à éditer', 'em-wp'); ?>
                    </label>
                    <select
                        id="em-wp-template-editing-select"
                        name="em_wp_template_editing_slug"
                        class="em-wp-template-banner__select"
                    >
                        <?php foreach ($registry as $slug => $definition) { ?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php selected($editing_slug, $slug); ?>>
                                <?php echo esc_html((string) ($definition['label'] ?? $slug)); ?>
                            </option>
                        <?php } ?>
                    </select>
                </form>
                <div class="em-wp-template-banner__actions">
                    <a
                        class="em-wp-template-banner__preview"
                        href="<?php echo esc_url(em_wp_template_preview_url($editing_slug)); ?>"
                        target="_blank"
                        title="<?php echo esc_attr(sprintf(
                            /* translators: %s: template label */
                            __('Prévisualiser le template %s dans un nouvel onglet', 'em-wp'),
                            $editing_label
                        )); ?>"
                    >
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        <?php esc_html_e('Aperçu', 'em-wp'); ?>
                    </a>
                    <button type="button" class="em-wp-template-banner__save" id="em-wp-template-banner-save" disabled aria-disabled="true">
                        <?php esc_html_e('Enregistrer', 'em-wp'); ?>
                    </button>
                    <button type="button" class="em-wp-template-banner__quit" id="em-wp-template-banner-quit">
                        <?php esc_html_e('Quitter', 'em-wp'); ?>
                    </button>
                </div>
                <form class="em-wp-template-banner__quit-form" id="em-wp-template-banner-quit-form" method="post" action="" hidden>
                    <?php wp_nonce_field($quit_nonce_action); ?>
                    <input type="hidden" name="em_wp_template_action" value="<?php echo esc_attr($quit_action); ?>">
                </form>
            </div>

            <div class="em-wp-template-banner__block em-wp-template-banner__block--live">
                <?php em_wp_admin_hub_render_template_active_pill($is_live ? $editing_label : $active_label, $is_live ? $editing_slug : $active_slug); ?>
                <?php if (!$is_live) { ?>
                    <?php
                    em_wp_admin_hub_render_template_activate_pill($editing_slug, $editing_label, [
                        'id' => 'em-wp-template-banner-activate-live',
                    ]);
                    ?>
                <?php } ?>
            </div>
            <?php em_wp_admin_hub_render_template_set_live_form($current_page); ?>
        </div>
    </div>
    <?php
}

