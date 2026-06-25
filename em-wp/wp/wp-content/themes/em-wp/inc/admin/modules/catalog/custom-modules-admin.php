<?php
/**
 * Admin — modules catalogue personnalisés.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, array{
 *     label:string,
 *     menu_title:string,
 *     slug:string,
 *     icon:string,
 *     available:bool,
 *     description_item:string,
 *     description_rubrique:string,
 *     url:string,
 *     callback:callable|string
 * }>
 */
function em_wp_custom_catalog_menu_definitions(): array
{
    $definitions = [];

    foreach (em_wp_custom_catalog_modules() as $module_slug => $module) {
        $label = trim((string) ($module['label'] ?? $module_slug));
        $menu_label = mb_strtoupper($label);
        $hub_slug = em_wp_custom_catalog_hub_menu_slug($module_slug);

        $definitions[$module_slug] = [
            'label'                => $menu_label,
            'menu_title'           => $menu_label,
            'slug'                 => $hub_slug,
            'icon'                 => em_wp_catalog_resolve_module_icon($module_slug, 'dashicons-admin-generic'),
            'available'            => true,
            'description_item'     => (string) ($module['description_item'] ?? $label),
            'description_rubrique' => (string) ($module['description_rubrique'] ?? ''),
            'url'                  => admin_url('admin.php?page=' . $hub_slug),
            'callback'             => 'em_wp_catalog_render_custom_module_hub_page',
        ];
    }

    return $definitions;
}

function em_wp_catalog_render_custom_module_hub_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
    $module_slug = em_wp_custom_catalog_module_slug_from_hub($page_slug);
    $module = em_wp_custom_catalog_module($module_slug);

    if ($module === null) {
        return;
    }

    $type = 'custom-' . $module_slug;
    $section_title = sprintf(
        /* translators: %s: custom catalog label */
        __('%s DISPONIBLES', 'em-wp'),
        mb_strtoupper((string) ($module['label'] ?? $module_slug))
    );

    em_wp_catalog_render_generic_crud_hub_page([
        'entries_fn'        => 'em_wp_custom_catalog_current_hub_entries',
        'notices_fn'        => 'em_wp_custom_catalog_render_entry_admin_notices',
        'catalog_label'     => (string) ($module['label'] ?? $module_slug),
        'icon'              => (string) ($module['icon'] ?? 'dashicons-admin-generic'),
        'type'              => $type,
        'section_title'     => $section_title,
        'hub_menu_slug'     => 'em_wp_custom_catalog_current_hub_menu_slug',
        'nonce_action'      => 'em_wp_custom_catalog_entry_actions_nonce_action',
        'post_prefix'       => 'em_wp_custom_catalog_entry',
        'slug_from_label'   => 'em_wp_custom_catalog_entry_slug_from_label_for_hub',
        'unique_slug'       => 'em_wp_custom_catalog_unique_entry_slug_for_hub',
        'edit_page_slug'    => 'em_wp_custom_catalog_edit_page_slug_for_module',
        'create_toggle_id'  => 'em-wp-custom-catalog-create-toggle-' . $module_slug,
        'create_panel_id'   => 'em-wp-custom-catalog-create-panel-' . $module_slug,
        'create_cancel_id'  => 'em-wp-custom-catalog-create-cancel-' . $module_slug,
        'name_field_label'  => __('Nom de l\'entrée', 'em-wp'),
        'rename_row_prefix' => 'em-wp-custom-catalog-rename-' . $module_slug,
    ]);
}

function em_wp_custom_catalog_render_entry_admin_notices(): void
{
    em_wp_catalog_render_registry_admin_notices([
        'notice_prefix' => 'custom_catalog',
        'labels'        => [
            'created' => __('Entrée créée.', 'em-wp'),
            'renamed' => __('Entrée renommée. L\'identifiant a été mis à jour si nécessaire.', 'em-wp'),
            'deleted' => __('Entrée supprimée.', 'em-wp'),
        ],
    ]);

    em_wp_custom_catalog_render_module_admin_notices();
}

function em_wp_custom_catalog_render_module_admin_notices(): void
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $notice = sanitize_key((string) ($_GET['custom_module_notice'] ?? ''));
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $error = sanitize_key((string) ($_GET['custom_module_error'] ?? ''));
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $message = isset($_GET['custom_module_message'])
        ? sanitize_text_field(rawurldecode((string) wp_unslash($_GET['custom_module_message'])))
        : '';

    if ($notice === 'created') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Catalogue créé.', 'em-wp') . '</p></div>';
    } elseif ($notice === 'updated') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Catalogue mis à jour.', 'em-wp') . '</p></div>';
    }

    if ($error !== '') {
        $text = $message !== '' ? $message : __('Une erreur est survenue.', 'em-wp');
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($text) . '</p></div>';
    }
}

function em_wp_custom_catalog_register_entry_admin_pages(): void
{
    foreach (em_wp_custom_catalog_modules() as $module_slug => $module) {
        unset($module);

        foreach (em_wp_custom_catalog_style_definitions($module_slug) as $definition) {
            $page_slug = (string) ($definition['page_slug'] ?? '');

            if ($page_slug === '') {
                continue;
            }

            add_submenu_page(
                null,
                (string) ($definition['menu_title'] ?? __('Entrée catalogue', 'em-wp')),
                (string) ($definition['menu_title'] ?? __('Entrée catalogue', 'em-wp')),
                'manage_options',
                $page_slug,
                'em_wp_custom_catalog_render_entry_admin_page'
            );
        }
    }
}
add_action('admin_menu', 'em_wp_custom_catalog_register_entry_admin_pages', 21);

function em_wp_custom_catalog_render_entry_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
    $resolved = em_wp_custom_catalog_entry_from_page($page_slug);
    $module_slug = (string) ($resolved['module_slug'] ?? '');
    $parsed_slug = (string) ($resolved['entry_slug'] ?? '');
    $entry_slug = em_wp_custom_catalog_resolve_entry_slug($module_slug, $parsed_slug);
    $module = em_wp_custom_catalog_module($module_slug);
    $entries = em_wp_custom_catalog_entries($module_slug);
    $entry = $entries[$entry_slug] ?? null;

    if ($module === null || !is_array($entry)) {
        return;
    }

    $entry_label = (string) ($entry['label'] ?? $entry_slug);
    $hub_url = admin_url('admin.php?page=' . em_wp_custom_catalog_hub_menu_slug($module_slug));
    $option_name = em_wp_custom_catalog_entry_option_name($module_slug, $entry_slug);
    $group_name = em_wp_custom_catalog_entry_group_name($module_slug, $entry_slug);
    $field_definitions = em_wp_custom_catalog_module_field_definitions($module_slug);
    $options = em_wp_custom_catalog_get_entry_options($module_slug, $entry_slug);
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire em-wp-catalog-edit">
        <?php
        em_wp_admin_render_settings_notices();
        em_wp_custom_catalog_render_entry_admin_notices();
        em_wp_catalog_render_edit_sommaire_header(
            $module_slug,
            (string) ($module['icon'] ?? 'dashicons-admin-generic'),
            [
                'style_slug'  => $entry_slug,
                'label'       => $entry_label,
                'page_slug'   => $page_slug,
                'option_name' => '',
                'group'       => '',
            ],
            em_wp_custom_catalog_style_definitions($module_slug),
            $entry_slug,
            $hub_url
        );

        em_wp_catalog_render_module_entry_tabs(
            em_wp_custom_catalog_hub_menu_slug($module_slug),
            em_wp_custom_catalog_style_definitions($module_slug),
            $entry_slug,
            __('Navigation catalogue personnalisé', 'em-wp')
        );
        ?>

        <div class="em-wp-catalog-edit__body">
            <?php if ($field_definitions !== []) { ?>
                <div class="em-wp-catalog-edit__layout">
                    <div class="em-wp-catalog-edit__main">
                        <?php em_wp_catalog_render_edit_section_open((string) ($module['label'] ?? $module_slug), $entry_label); ?>
                        <form
                            id="em-wp-custom-catalog-entry-form"
                            method="post"
                            action="options.php"
                            class="em-wp-custom-catalog-form"
                        >
                            <?php
                            settings_fields($group_name);
                            em_wp_custom_catalog_render_entry_fields_panel($options, $field_definitions, $option_name, $module_slug);
                            em_wp_custom_catalog_render_entry_form_actions();
                            ?>
                        </form>
                        <?php em_wp_catalog_render_edit_section_close(); ?>
                    </div>
                </div>
            <?php } else { ?>
                <p class="em-wp-catalog-sommaire__empty">
                    <?php
                    printf(
                        /* translators: %s: entry label */
                        esc_html__('Configuration de « %s » — à venir.', 'em-wp'),
                        esc_html($entry_label)
                    );
                    ?>
                </p>
            <?php } ?>
        </div>
    </div>
    <?php
}

function em_wp_catalog_render_module_edit_panel(string $module_slug): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $module_slug = sanitize_key($module_slug);
    $settings = function_exists('em_wp_catalog_get_module_edit_settings')
        ? em_wp_catalog_get_module_edit_settings($module_slug)
        : null;

    if ($settings === null) {
        return;
    }

    $parent_url = em_wp_catalog_parent_page_url();
    $position_options = em_wp_catalog_menu_position_options($module_slug);
    $current_position = sanitize_key((string) ($settings['menu_position_after'] ?? '__end__'));
    ?>
    <form
        method="post"
        action="<?php echo esc_url($parent_url); ?>"
        class="em-wp-catalog-sommaire__create-panel em-wp-catalog-sommaire__create-panel--module em-wp-catalog-sommaire__create-panel--module-edit"
        id="em-wp-catalog-module-edit-panel-<?php echo esc_attr($module_slug); ?>"
        hidden
    >
        <?php wp_nonce_field('em_wp_custom_catalog_module_actions'); ?>
        <input type="hidden" name="em_wp_custom_catalog_module_action" value="update">
        <input type="hidden" name="em_wp_custom_catalog_module_slug" value="<?php echo esc_attr($module_slug); ?>">

        <div class="em-wp-catalog-sommaire__create-panel-inner">
            <header class="em-wp-catalog-sommaire__create-panel-head">
                <h3 class="em-wp-catalog-sommaire__create-panel-title"><?php esc_html_e('Modifier le catalogue', 'em-wp'); ?></h3>
            </header>

            <div class="em-wp-catalog-sommaire__create-panel-fields">
                <label class="em-wp-catalog-sommaire__field">
                    <span class="em-wp-catalog-sommaire__field-label"><?php esc_html_e('Nom du catalogue', 'em-wp'); ?></span>
                    <input
                        type="text"
                        name="em_wp_custom_catalog_module_label"
                        class="em-wp-catalog-sommaire__label-input em-wp-catalog-sommaire__control"
                        value="<?php echo esc_attr((string) ($settings['label'] ?? '')); ?>"
                        required
                        autocomplete="off"
                    >
                </label>
                <label class="em-wp-catalog-sommaire__field">
                    <span class="em-wp-catalog-sommaire__field-label"><?php esc_html_e('Position dans le menu', 'em-wp'); ?></span>
                    <select name="em_wp_custom_catalog_module_position" class="em-wp-catalog-sommaire__select em-wp-catalog-sommaire__control">
                        <?php foreach ($position_options as $value => $option_label) { ?>
                            <option value="<?php echo esc_attr((string) $value); ?>"<?php selected($current_position, $value); ?>>
                                <?php echo esc_html((string) $option_label); ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
            </div>

            <div class="em-wp-catalog-sommaire__inline-actions em-wp-catalog-sommaire__create-panel-actions">
                <?php submit_button(__('Enregistrer', 'em-wp'), 'primary', 'submit', false); ?>
                <button type="button" class="button button-secondary em-wp-catalog-module-edit-cancel" data-em-wp-edit-cancel-for="<?php echo esc_attr($module_slug); ?>">
                    <?php esc_html_e('Annuler', 'em-wp'); ?>
                </button>
            </div>
        </div>
    </form>
    <?php
}

/**
 * @deprecated Utiliser em_wp_catalog_render_module_edit_panel().
 */
function em_wp_catalog_render_custom_module_edit_panel(string $module_slug): void
{
    em_wp_catalog_render_module_edit_panel($module_slug);
}

function em_wp_catalog_render_new_catalog_module_panel(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $parent_url = em_wp_catalog_parent_page_url();
    $position_options = em_wp_catalog_menu_position_options();
    ?>
    <form
        method="post"
        action="<?php echo esc_url($parent_url); ?>"
        class="em-wp-catalog-sommaire__create-panel em-wp-catalog-sommaire__create-panel--module"
        id="em-wp-catalog-module-create-panel"
        hidden
    >
        <?php wp_nonce_field('em_wp_custom_catalog_module_actions'); ?>
        <input type="hidden" name="em_wp_custom_catalog_module_action" value="create">

        <div class="em-wp-catalog-sommaire__create-panel-inner">
            <header class="em-wp-catalog-sommaire__create-panel-head">
                <h3 class="em-wp-catalog-sommaire__create-panel-title"><?php esc_html_e('Nouveau catalogue', 'em-wp'); ?></h3>
                <p class="em-wp-catalog-sommaire__create-panel-desc"><?php esc_html_e('Ajoute un type de catalogue réutilisable dans le menu.', 'em-wp'); ?></p>
            </header>

            <div class="em-wp-catalog-sommaire__create-panel-fields">
                <label class="em-wp-catalog-sommaire__field">
                    <span class="em-wp-catalog-sommaire__field-label"><?php esc_html_e('Nom du catalogue', 'em-wp'); ?></span>
                    <input
                        type="text"
                        name="em_wp_custom_catalog_module_label"
                        class="em-wp-catalog-sommaire__label-input em-wp-catalog-sommaire__control"
                        required
                        autocomplete="off"
                    >
                </label>
                <label class="em-wp-catalog-sommaire__field">
                    <span class="em-wp-catalog-sommaire__field-label"><?php esc_html_e('Position dans le menu', 'em-wp'); ?></span>
                    <select name="em_wp_custom_catalog_module_position" class="em-wp-catalog-sommaire__select em-wp-catalog-sommaire__control">
                        <?php foreach ($position_options as $value => $label) { ?>
                            <option value="<?php echo esc_attr((string) $value); ?>"<?php selected($value, '__end__'); ?>>
                                <?php echo esc_html((string) $label); ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
            </div>

            <div class="em-wp-catalog-sommaire__inline-actions em-wp-catalog-sommaire__create-panel-actions">
                <?php submit_button(__('Créer le catalogue', 'em-wp'), 'primary', 'submit', false); ?>
                <button type="button" class="button button-secondary" id="em-wp-catalog-module-create-cancel">
                    <?php esc_html_e('Annuler', 'em-wp'); ?>
                </button>
            </div>
        </div>
    </form>
    <?php
}

function em_wp_custom_catalog_enqueue_module_create_assets(): void
{
    wp_enqueue_script(
        'em-wp-admin-catalog-module-create',
        get_template_directory_uri() . '/assets/admin/js/catalog/catalog-module-create.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/catalog/catalog-module-create.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-catalog-module-edit',
        get_template_directory_uri() . '/assets/admin/js/catalog/catalog-module-edit.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/catalog/catalog-module-edit.js'),
        true
    );
}

function em_wp_custom_catalog_enqueue_entry_admin_assets(string $hook_suffix): void
{
    unset($hook_suffix);

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
    $resolved = em_wp_custom_catalog_entry_from_page($page_slug);

    if (($resolved['module_slug'] ?? '') === '' || ($resolved['entry_slug'] ?? '') === '') {
        return;
    }

    wp_enqueue_style(
        'em-wp-release-admin',
        get_template_directory_uri() . '/assets/admin/css/modules/release/release.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/release/release.css')
    );
}
add_action('admin_enqueue_scripts', 'em_wp_custom_catalog_enqueue_entry_admin_assets', 20);
