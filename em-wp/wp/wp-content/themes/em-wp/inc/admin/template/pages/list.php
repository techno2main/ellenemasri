<?php
/**
 * Page admin « Templates » (CRUD + template actif live).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug page admin parent Template.
 */
function em_wp_admin_template_parent_page_slug(): string
{
    return 'em-wp-template';
}

/**
 * Slug page admin Choix du Template (alias parent).
 */
function em_wp_admin_template_choice_page_slug(): string
{
    return em_wp_admin_template_parent_page_slug();
}

/**
 * URL page admin Choix du Template.
 */
function em_wp_admin_template_choice_admin_url(): string
{
    return admin_url('admin.php?page=' . em_wp_admin_template_choice_page_slug());
}

/**
 * Slug page admin d'un template enregistré (MAYAMI, ELLENE, …).
 */
function em_wp_admin_template_entry_page_slug(string $template_slug): string
{
    return 'em-wp-template-' . em_wp_template_sanitize_slug($template_slug);
}

/**
 * Slugs des pages menu template enregistrées.
 *
 * @return string[]
 */
function em_wp_admin_template_entry_page_slugs(): array
{
    $slugs = [];

    foreach (array_keys(em_wp_template_registry()) as $template_slug) {
        $slugs[] = em_wp_admin_template_entry_page_slug((string) $template_slug);
    }

    return array_values(array_unique($slugs));
}

/**
 * Slugs réservés au bloc Template (parent + entrées).
 *
 * @return string[]
 */
function em_wp_admin_template_reserved_menu_slugs(): array
{
    $slugs = [em_wp_admin_template_parent_page_slug()];

    if (function_exists('em_wp_admin_templates_page_slug')) {
        $slugs[] = em_wp_admin_templates_page_slug();
    }

    return array_values(array_unique(array_merge($slugs, em_wp_admin_template_entry_page_slugs())));
}

/**
 * Retourne le slug template depuis une page menu dédiée.
 */
function em_wp_admin_template_slug_from_entry_page(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);
    $prefix = 'em-wp-template-';

    if (!str_starts_with($page_slug, $prefix)) {
        return '';
    }

    $template_slug = em_wp_template_sanitize_slug(substr($page_slug, strlen($prefix)));

    if ($template_slug === '' || !em_wp_template_exists($template_slug)) {
        return '';
    }

    return $template_slug;
}

/**
 * Rendu page Choix du Template (menu Templates + accueil).
 */
function em_wp_admin_render_template_choice_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    em_wp_admin_render_rubriques_template_picker();
}

/**
 * Slug page admin gestion Templates (CRUD, hors menu).
 */
function em_wp_admin_templates_page_slug(): string
{
    return 'em-wp-templates';
}

/**
 * URL page admin Templates.
 */
function em_wp_admin_templates_page_url(): string
{
    return admin_url('admin.php?page=' . em_wp_admin_templates_page_slug());
}

/**
 * Enregistre le bloc menu Templates (TEMPLATES + entrées registry) + page CRUD masquée.
 */
function em_wp_admin_templates_register_menu(): void
{
    add_menu_page(
        __('Templates', 'em-wp'),
        __('TEMPLATES', 'em-wp'),
        'manage_options',
        em_wp_admin_template_parent_page_slug(),
        'em_wp_admin_render_template_choice_page',
        'dashicons-layout',
        em_wp_admin_menu_templates_position()
    );

    foreach (em_wp_template_registry() as $slug => $definition) {
        $menu_label = mb_strtoupper((string) ($definition['label'] ?? $slug));

        add_menu_page(
            $menu_label,
            $menu_label,
            'manage_options',
            em_wp_admin_template_entry_page_slug($slug),
            'em_wp_admin_render_template_entry_page',
            'dashicons-admin-appearance',
            em_wp_admin_menu_position_for_template($slug)
        );
    }

    add_submenu_page(
        null,
        __('Gérer les templates', 'em-wp'),
        __('Gérer les templates', 'em-wp'),
        'manage_options',
        em_wp_admin_templates_page_slug(),
        'em_wp_admin_render_templates_page'
    );
}
add_action('admin_menu', 'em_wp_admin_templates_register_menu');

/**
 * Retire les sous-menus dupliqués WordPress.
 */
function em_wp_admin_templates_remove_duplicate_submenu(): void
{
    $pages = array_merge(
        [em_wp_admin_template_parent_page_slug()],
        em_wp_admin_template_entry_page_slugs()
    );

    foreach ($pages as $page_slug) {
        remove_submenu_page($page_slug, $page_slug);
    }
}
add_action('admin_menu', 'em_wp_admin_templates_remove_duplicate_submenu', 999);

/**
 * Démarre l'édition d'un template depuis son entrée menu (MAYAMI, ELLENE, …).
 */
function em_wp_admin_template_entry_start_editing(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $template_slug = em_wp_admin_template_slug_from_entry_page($page_slug);

    if ($template_slug === '') {
        return;
    }

    $result = em_wp_set_editing_template_slug($template_slug);

    if (is_wp_error($result)) {
        set_transient(
            'em_wp_template_admin_notice_' . get_current_user_id(),
            [
                'type'    => 'error',
                'message' => $result->get_error_message(),
            ],
            30
        );
        em_wp_admin_safe_redirect(em_wp_admin_template_choice_admin_url());
    }

    em_wp_admin_safe_redirect(em_wp_admin_rubriques_admin_url());
}
add_action('admin_init', 'em_wp_admin_template_entry_start_editing', 1);

/**
 * Redirige l'ancien slug em-wp-template-choice vers le parent TEMPLATES.
 */
function em_wp_admin_template_redirect_legacy_choice_slug(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug !== 'em-wp-template-choice') {
        return;
    }

    em_wp_admin_safe_redirect(em_wp_admin_template_choice_admin_url());
}
add_action('admin_init', 'em_wp_admin_template_redirect_legacy_choice_slug', 1);

/**
 * Callback placeholder pour les entrées template (redirection admin_init).
 */
function em_wp_admin_render_template_entry_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    em_wp_admin_safe_redirect(em_wp_admin_rubriques_admin_url());
}

/**
 * Assets page Templates.
 */
function em_wp_admin_templates_enqueue(): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if (!in_array($page_slug, [em_wp_admin_templates_page_slug(), em_wp_admin_template_choice_page_slug()], true)) {
        return;
    }

    em_wp_admin_enqueue_shared_assets();

    if ($page_slug === em_wp_admin_template_choice_page_slug()) {
        em_wp_admin_hub_cards_enqueue_assets();
        em_wp_admin_hub_enqueue_template_live_switcher();

        wp_enqueue_style(
            'font-awesome-6',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
            [],
            '6.5.1'
        );

        wp_enqueue_style(
            'em-wp-admin-catalog-sommaire',
            get_template_directory_uri() . '/assets/admin/css/catalog/sommaire.css',
            ['em-wp-admin-hub-cards'],
            em_wp_admin_asset_version('assets/admin/css/catalog/sommaire.css')
        );

        wp_enqueue_script(
            'em-wp-admin-templates-preview',
            get_template_directory_uri() . '/assets/admin/js/pages/templates-preview.js',
            [],
            em_wp_admin_asset_version('assets/admin/js/pages/templates-preview.js'),
            true
        );

        return;
    }

    wp_enqueue_style(
        'em-wp-admin-template-list',
        get_template_directory_uri() . '/assets/admin/css/template/list-page.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/template/list-page.css')
    );
}
add_action('admin_enqueue_scripts', 'em_wp_admin_templates_enqueue');

/**
 * Rendu page Templates.
 */
function em_wp_admin_render_templates_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $registry = em_wp_template_registry();
    $active_slug = em_wp_get_active_template_slug();
    $editing_slug = em_wp_get_editing_template_slug();
    $can_manage = em_wp_admin_can_manage_templates();
    $suggested_color = em_wp_template_suggest_new_color();
    ?>
    <div class="wrap em-wp-admin-module em-wp-templates-admin">
        <h1><?php esc_html_e('Templates', 'em-wp'); ?></h1>

        <p class="description em-wp-templates-admin__intro">
            <?php esc_html_e('Un template regroupe tout le contenu des rubriques. Assigne-lui une couleur pour te repérer dans le menu et le bandeau admin.', 'em-wp'); ?>
        </p>

        <div class="em-wp-templates-admin__grid">
            <section class="em-wp-templates-admin__panel">
                <h2><?php esc_html_e('Templates enregistrés', 'em-wp'); ?></h2>

                <table class="widefat striped em-wp-templates-admin__table">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Nom', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('Couleur', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('Identifiant', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('Actif sur le site', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('En édition (toi)', 'em-wp'); ?></th>
                            <?php if ($can_manage) { ?>
                                <th scope="col"><?php esc_html_e('Actions', 'em-wp'); ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registry as $slug => $definition) { ?>
                            <?php
                            $label = (string) ($definition['label'] ?? $slug);
                            $color = em_wp_get_template_color($slug);
                            $is_active = ($slug === $active_slug);
                            $is_editing = ($slug === $editing_slug);
                            ?>
                            <tr>
                                <td>
                                    <?php if ($can_manage) { ?>
                                        <form method="post" class="em-wp-templates-admin__inline-form">
                                            <?php wp_nonce_field('em_wp_template_rename'); ?>
                                            <input type="hidden" name="em_wp_template_action" value="rename">
                                            <input type="hidden" name="em_wp_template_slug" value="<?php echo esc_attr($slug); ?>">
                                            <input
                                                type="text"
                                                name="em_wp_template_label"
                                                value="<?php echo esc_attr($label); ?>"
                                                class="regular-text"
                                                required
                                            >
                                            <button type="submit" class="button button-small">
                                                <?php esc_html_e('Renommer', 'em-wp'); ?>
                                            </button>
                                        </form>
                                    <?php } else { ?>
                                        <strong><?php echo esc_html($label); ?></strong>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($can_manage) { ?>
                                        <form method="post" class="em-wp-templates-admin__inline-form em-wp-templates-admin__color-form">
                                            <?php wp_nonce_field('em_wp_template_set_color'); ?>
                                            <input type="hidden" name="em_wp_template_action" value="set_color">
                                            <input type="hidden" name="em_wp_template_slug" value="<?php echo esc_attr($slug); ?>">
                                            <span
                                                class="em-wp-templates-admin__color-swatch"
                                                style="--em-template-swatch: <?php echo esc_attr($color); ?>;"
                                                aria-hidden="true"
                                            ></span>
                                            <input
                                                type="text"
                                                name="em_wp_template_color"
                                                value="<?php echo esc_attr($color); ?>"
                                                class="em-wp-admin-color-field em-wp-templates-admin__color-field"
                                                data-default-color="<?php echo esc_attr(em_wp_template_default_color_for_slug($slug)); ?>"
                                            >
                                            <button type="submit" class="button button-small">
                                                <?php esc_html_e('Save', 'em-wp'); ?>
                                            </button>
                                        </form>
                                    <?php } else { ?>
                                        <span
                                            class="em-wp-templates-admin__color-swatch"
                                            style="--em-template-swatch: <?php echo esc_attr($color); ?>;"
                                            title="<?php echo esc_attr($color); ?>"
                                        ></span>
                                    <?php } ?>
                                </td>
                                <td><code><?php echo esc_html($slug); ?></code></td>
                                <td>
                                    <?php if ($is_active) { ?>
                                        <span class="em-wp-templates-admin__badge em-wp-templates-admin__badge--live">
                                            <?php esc_html_e('Live', 'em-wp'); ?>
                                        </span>
                                    <?php } elseif ($can_manage) { ?>
                                        <form method="post" class="em-wp-templates-admin__inline-form">
                                            <?php wp_nonce_field('em_wp_template_set_active'); ?>
                                            <input type="hidden" name="em_wp_template_action" value="set_active">
                                            <input type="hidden" name="em_wp_template_active_slug" value="<?php echo esc_attr($slug); ?>">
                                            <button type="submit" class="button button-secondary button-small">
                                                <?php esc_html_e('Activer sur le site', 'em-wp'); ?>
                                            </button>
                                        </form>
                                    <?php } else { ?>
                                        <span aria-hidden="true">—</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($is_editing) { ?>
                                        <span class="em-wp-templates-admin__badge">
                                            <?php esc_html_e('Bandeau', 'em-wp'); ?>
                                        </span>
                                    <?php } else { ?>
                                        <span aria-hidden="true">—</span>
                                    <?php } ?>
                                </td>
                                <?php if ($can_manage) { ?>
                                    <td>
                                        <?php if (!$is_active && count($registry) > 1) { ?>
                                            <form method="post" class="em-wp-templates-admin__inline-form">
                                                <?php wp_nonce_field('em_wp_template_delete'); ?>
                                                <input type="hidden" name="em_wp_template_action" value="delete">
                                                <input type="hidden" name="em_wp_template_slug" value="<?php echo esc_attr($slug); ?>">
                                                <button
                                                    type="submit"
                                                    class="button button-link-delete"
                                                    onclick="return confirm('<?php echo esc_js(__('Supprimer ce template ?', 'em-wp')); ?>');"
                                                >
                                                    <?php esc_html_e('Supprimer', 'em-wp'); ?>
                                                </button>
                                            </form>
                                        <?php } else { ?>
                                            <span aria-hidden="true">—</span>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>

            <?php if ($can_manage) { ?>
                <section class="em-wp-templates-admin__panel em-wp-templates-admin__panel--create">
                    <h2><?php esc_html_e('Nouveau template', 'em-wp'); ?></h2>
                    <form method="post" class="em-wp-templates-admin__create-form">
                        <?php wp_nonce_field('em_wp_template_create'); ?>
                        <input type="hidden" name="em_wp_template_action" value="create">
                        <p>
                            <label for="em-wp-template-new-label"><?php esc_html_e('Nom du template', 'em-wp'); ?></label>
                            <input
                                type="text"
                                id="em-wp-template-new-label"
                                name="em_wp_template_label"
                                class="regular-text"
                                required
                                placeholder="<?php esc_attr_e('Ex. Campagne été', 'em-wp'); ?>"
                            >
                        </p>
                        <p>
                            <label for="em-wp-template-new-color"><?php esc_html_e('Couleur du template', 'em-wp'); ?></label>
                            <input
                                type="text"
                                id="em-wp-template-new-color"
                                name="em_wp_template_color"
                                value="<?php echo esc_attr($suggested_color); ?>"
                                class="em-wp-admin-color-field"
                                data-default-color="<?php echo esc_attr($suggested_color); ?>"
                            >
                        </p>
                        <p>
                            <button type="submit" class="button button-primary">
                                <?php esc_html_e('Créer le template', 'em-wp'); ?>
                            </button>
                        </p>
                    </form>
                </section>
            <?php } ?>
        </div>
    </div>
    <?php
}
