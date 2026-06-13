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
 * Slug page admin Templates.
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
 * Position menu Templates (entre Rubriques et Paramètres).
 */
function em_wp_admin_menu_templates_position(): float
{
    return (float) em_wp_admin_menu_separator_bottom_position() + 0.5;
}

/**
 * Enregistre la page menu Templates.
 */
function em_wp_admin_templates_register_menu(): void
{
    add_menu_page(
        __('Templates', 'em-wp'),
        __('Templates', 'em-wp'),
        'manage_options',
        em_wp_admin_templates_page_slug(),
        'em_wp_admin_render_templates_page',
        'dashicons-layout',
        em_wp_admin_menu_templates_position()
    );
}
add_action('admin_menu', 'em_wp_admin_templates_register_menu');

/**
 * Retire le sous-menu dupliqué WordPress.
 */
function em_wp_admin_templates_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_admin_templates_page_slug(), em_wp_admin_templates_page_slug());
}
add_action('admin_menu', 'em_wp_admin_templates_remove_duplicate_submenu', 999);

/**
 * Assets page Templates.
 */
function em_wp_admin_templates_enqueue(): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug !== em_wp_admin_templates_page_slug()) {
        return;
    }

    em_wp_admin_enqueue_shared_assets();

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
    ?>
    <div class="wrap em-wp-admin-module em-wp-templates-admin">
        <h1><?php esc_html_e('Templates', 'em-wp'); ?></h1>

        <p class="description em-wp-templates-admin__intro">
            <?php esc_html_e('Un template regroupe tout le contenu des rubriques. Un seul template est actif sur le site ; le bandeau en haut de l’admin choisit celui que vous éditez.', 'em-wp'); ?>
        </p>

        <div class="em-wp-templates-admin__grid">
            <section class="em-wp-templates-admin__panel">
                <h2><?php esc_html_e('Templates enregistrés', 'em-wp'); ?></h2>

                <table class="widefat striped em-wp-templates-admin__table">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Nom', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('Identifiant', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('Actif sur le site', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('En édition (vous)', 'em-wp'); ?></th>
                            <?php if ($can_manage) { ?>
                                <th scope="col"><?php esc_html_e('Actions', 'em-wp'); ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registry as $slug => $definition) { ?>
                            <?php
                            $label = (string) ($definition['label'] ?? $slug);
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
