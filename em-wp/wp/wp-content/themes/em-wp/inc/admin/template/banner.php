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

    return $page_slug !== '' && str_starts_with($page_slug, 'em-wp-');
}

/**
 * Enqueue assets bandeau template.
 */
function em_wp_admin_template_banner_enqueue(): void
{
    if (!em_wp_admin_is_em_wp_screen()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );

    wp_enqueue_style(
        'em-wp-admin-template-banner',
        $theme_uri . '/assets/admin/css/template/banner.css',
        [],
        em_wp_admin_asset_version('assets/admin/css/template/banner.css')
    );

    wp_enqueue_script(
        'em-wp-admin-template-banner',
        $theme_uri . '/assets/admin/js/template/banner.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/template/banner.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-notice-autodismiss',
        $theme_uri . '/assets/admin/js/shared/notice-autodismiss.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/notice-autodismiss.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_admin_template_banner_enqueue');

/**
 * Classe body pour décaler le contenu sous le bandeau.
 */
function em_wp_admin_template_banner_body_class(string $classes): string
{
    if (em_wp_admin_is_em_wp_screen()) {
        $classes .= ' em-wp-has-template-banner';
    }

    return $classes;
}
add_filter('admin_body_class', 'em_wp_admin_template_banner_body_class');

/**
 * Rendu HTML du bandeau (sous la barre WP).
 */
function em_wp_admin_template_render_banner(): void
{
    if (!em_wp_admin_is_em_wp_screen() || !current_user_can('manage_options')) {
        return;
    }

    $registry = em_wp_template_registry();
    $editing_slug = em_wp_get_editing_template_slug();
    $active_slug = em_wp_get_active_template_slug();
    $editing_label = (string) ($registry[$editing_slug]['label'] ?? $editing_slug);
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    $differs = em_wp_template_editing_differs_from_live();

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current_page = sanitize_key((string) ($_GET['page'] ?? ''));
    ?>
    <div
        class="em-wp-template-banner<?php echo $differs ? ' is-editing-differs' : ''; ?>"
        role="region"
        aria-label="<?php esc_attr_e('Contexte template EM-WP', 'em-wp'); ?>"
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
            </div>

            <div class="em-wp-template-banner__block em-wp-template-banner__block--live">
                <span class="em-wp-template-banner__label">
                    <span class="em-wp-template-banner__live-dot" aria-hidden="true"></span>
                    <?php esc_html_e('Template actif sur le site', 'em-wp'); ?>
                </span>
                <form class="em-wp-template-banner__form em-wp-template-banner__form--live" method="post" action="">
                    <?php wp_nonce_field('em_wp_template_set_active'); ?>
                    <input type="hidden" name="em_wp_template_action" value="set_active">
                    <input type="hidden" name="em_wp_template_redirect_page" value="<?php echo esc_attr($current_page); ?>">
                    <label class="screen-reader-text" for="em-wp-template-active-select">
                        <?php esc_html_e('Choisir le template affiché sur le site', 'em-wp'); ?>
                    </label>
                    <select
                        id="em-wp-template-active-select"
                        name="em_wp_template_active_slug"
                        class="em-wp-template-banner__select"
                        data-current="<?php echo esc_attr($active_slug); ?>"
                    >
                        <?php foreach ($registry as $slug => $definition) { ?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php selected($active_slug, $slug); ?>>
                                <?php echo esc_html((string) ($definition['label'] ?? $slug)); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <button type="submit" class="em-wp-template-banner__confirm" disabled>
                        <?php esc_html_e('Valider', 'em-wp'); ?>
                    </button>
                </form>
            </div>

            <?php if ($differs) { ?>
                <p class="em-wp-template-banner__alert" role="status">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    <?php
                    printf(
                        /* translators: 1: editing template label, 2: live template label */
                        esc_html__('Édition « %1$s » — site public « %2$s »', 'em-wp'),
                        esc_html(mb_strtoupper($editing_label)),
                        esc_html(mb_strtoupper($active_label))
                    );
                    ?>
                </p>
            <?php } ?>

            <a class="em-wp-template-banner__link" href="<?php echo esc_url(em_wp_admin_templates_page_url()); ?>">
                <?php esc_html_e('Gérer les templates', 'em-wp'); ?>
            </a>
        </div>
    </div>
    <?php
}
add_action('in_admin_header', 'em_wp_admin_template_render_banner', 20);
