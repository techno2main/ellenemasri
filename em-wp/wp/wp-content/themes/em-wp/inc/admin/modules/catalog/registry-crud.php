<?php
/**
 * Actions CRUD génériques — catalogues Video / Stream / Social.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array{
 *     hub_menu_slug:callable():string,
 *     nonce_action:callable():string,
 *     post_prefix:string,
 *     edit_page_slug:callable(string):string,
 *     create:callable(string):string|WP_Error,
 *     rename:callable(string,string):string|WP_Error,
 *     delete:callable(string):true|WP_Error,
 *     notice_prefix:string,
 *     labels:array{created:string,renamed:string,deleted:string}
 * } $config
 */
function em_wp_catalog_handle_registry_actions(array $config): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $post_prefix = sanitize_key((string) ($config['post_prefix'] ?? ''));

    if ($post_prefix === '') {
        return;
    }

    $action_key = $post_prefix . '_action';
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $action = sanitize_key((string) ($_POST[$action_key] ?? ''));

    if ($action === '') {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
    $hub_slug = is_callable($config['hub_menu_slug'] ?? null) ? (string) call_user_func($config['hub_menu_slug']) : '';

    if ($hub_slug === '' || $page_slug !== $hub_slug) {
        return;
    }

    $nonce_action = is_callable($config['nonce_action'] ?? null) ? (string) call_user_func($config['nonce_action']) : '';

    if (
        $nonce_action === ''
        || !isset($_POST['_wpnonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['_wpnonce'])), $nonce_action)
    ) {
        wp_die(esc_html__('Action non autorisée.', 'em-wp'));
    }

    $notice_prefix = sanitize_key((string) ($config['notice_prefix'] ?? 'catalog'));
    $redirect_args = ['page' => $hub_slug];
    $label_key = $post_prefix . '_label';
    $slug_key = $post_prefix . '_slug';

    if ($action === 'create') {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $label = sanitize_text_field((string) ($_POST[$label_key] ?? ''));
        $created = call_user_func($config['create'], $label);

        if (is_wp_error($created)) {
            $redirect_args[$notice_prefix . '_error'] = $created->get_error_code();
            $redirect_args[$notice_prefix . '_message'] = rawurlencode($created->get_error_message());
        } else {
            $result_slug = (string) $created;
            wp_safe_redirect(
                add_query_arg(
                    [
                        'page'                         => call_user_func($config['edit_page_slug'], $result_slug),
                        $notice_prefix . '_notice' => 'created',
                    ],
                    admin_url('admin.php')
                )
            );
            exit;
        }
    } elseif ($action === 'rename') {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $old_slug = sanitize_key((string) ($_POST[$slug_key] ?? ''));
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $label = sanitize_text_field((string) ($_POST[$label_key] ?? ''));
        $renamed = call_user_func($config['rename'], $old_slug, $label);

        if (is_wp_error($renamed)) {
            $redirect_args[$notice_prefix . '_error'] = $renamed->get_error_code();
            $redirect_args[$notice_prefix . '_message'] = rawurlencode($renamed->get_error_message());
        } else {
            $redirect_args[$notice_prefix . '_notice'] = 'renamed';
        }
    } elseif ($action === 'delete') {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $slug = sanitize_key((string) ($_POST[$slug_key] ?? ''));
        $deleted = call_user_func($config['delete'], $slug);

        if (is_wp_error($deleted)) {
            $redirect_args[$notice_prefix . '_error'] = $deleted->get_error_code();
            $redirect_args[$notice_prefix . '_message'] = rawurlencode($deleted->get_error_message());
        } else {
            $redirect_args[$notice_prefix . '_notice'] = 'deleted';
        }
    }

    wp_safe_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
    exit;
}

/**
 * @param array{
 *     notice_prefix:string,
 *     labels:array{created:string,renamed:string,deleted:string}
 * } $config
 */
function em_wp_catalog_render_registry_admin_notices(array $config): void
{
    $notice_prefix = sanitize_key((string) ($config['notice_prefix'] ?? 'catalog'));
    $labels = is_array($config['labels'] ?? null) ? $config['labels'] : [];

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $notice = sanitize_key((string) ($_GET[$notice_prefix . '_notice'] ?? ''));
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $error = sanitize_key((string) ($_GET[$notice_prefix . '_error'] ?? ''));
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $message = isset($_GET[$notice_prefix . '_message'])
        ? sanitize_text_field(rawurldecode((string) wp_unslash($_GET[$notice_prefix . '_message'])))
        : '';

    if ($notice === 'renamed' && !empty($labels['renamed'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html((string) $labels['renamed']) . '</p></div>';
    } elseif ($notice === 'deleted' && !empty($labels['deleted'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html((string) $labels['deleted']) . '</p></div>';
    } elseif ($notice === 'created' && !empty($labels['created'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html((string) $labels['created']) . '</p></div>';
    }

    if ($error !== '') {
        $text = $message !== '' ? $message : __('Une erreur est survenue.', 'em-wp');
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($text) . '</p></div>';
    }
}

/**
 * @param array{
 *     type:string,
 *     section_title:string,
 *     icon:string,
 *     hub_menu_slug:callable():string,
 *     nonce_action:callable():string,
 *     post_prefix:string,
 *     slug_from_label:callable(string):string,
 *     unique_slug:callable(string,string):string,
 *     edit_page_slug:callable(string):string,
 *     create_toggle_id:string,
 *     create_panel_id:string,
 *     create_cancel_id:string,
 *     name_field_label:string,
 *     rename_row_prefix:string
 * } $config
 * @param array<string, array{label:string,layout?:string}> $entries
 */
function em_wp_catalog_render_crud_sommaire_section(array $config, array $entries): void
{
    $type = sanitize_key((string) ($config['type'] ?? 'catalog'));
    $title_id = 'em-wp-catalog-sommaire-' . $type . '-title';
    $hub_slug = is_callable($config['hub_menu_slug'] ?? null) ? (string) call_user_func($config['hub_menu_slug']) : '';
    $post_prefix = sanitize_key((string) ($config['post_prefix'] ?? ''));
    $hub_url = admin_url('admin.php?page=' . $hub_slug);
    ?>
    <section class="em-wp-catalog-sommaire__section" aria-labelledby="<?php echo esc_attr($title_id); ?>">
        <header class="em-wp-catalog-sommaire__section-header">
            <div id="<?php echo esc_attr($title_id); ?>" class="em-wp-catalog-sommaire__section-title">
                <?php em_wp_admin_hub_render_card_title((string) ($config['section_title'] ?? ''), (string) ($config['icon'] ?? 'dashicons-admin-generic')); ?>
            </div>
            <button
                type="button"
                class="button button-primary em-wp-catalog-sommaire__new"
                id="<?php echo esc_attr((string) ($config['create_toggle_id'] ?? '')); ?>"
            >
                <?php esc_html_e('Nouveau', 'em-wp'); ?>
            </button>
        </header>

        <div class="em-wp-catalog-sommaire__section-body">
            <form
                method="post"
                action="<?php echo esc_url($hub_url); ?>"
                class="em-wp-catalog-sommaire__create-panel"
                id="<?php echo esc_attr((string) ($config['create_panel_id'] ?? '')); ?>"
                hidden
            >
                <?php wp_nonce_field((string) call_user_func($config['nonce_action'])); ?>
                <input type="hidden" name="<?php echo esc_attr($post_prefix . '_action'); ?>" value="create">
                <label class="em-wp-catalog-sommaire__field">
                    <span class="em-wp-catalog-sommaire__field-label"><?php echo esc_html((string) ($config['name_field_label'] ?? '')); ?></span>
                    <input
                        type="text"
                        name="<?php echo esc_attr($post_prefix . '_label'); ?>"
                        class="regular-text em-wp-catalog-sommaire__label-input"
                        required
                        data-em-wp-slug-preview
                    >
                </label>
                <p class="em-wp-catalog-sommaire__slug-hint">
                    <?php esc_html_e('Identifiant prévu :', 'em-wp'); ?>
                    <code class="em-wp-catalog-sommaire__slug-preview" data-em-wp-slug-preview-for="<?php echo esc_attr((string) ($config['create_panel_id'] ?? '')); ?>"></code>
                </p>
                <div class="em-wp-catalog-sommaire__inline-actions">
                    <?php submit_button(__('Créer', 'em-wp'), 'primary', 'submit', false); ?>
                    <button type="button" class="button" id="<?php echo esc_attr((string) ($config['create_cancel_id'] ?? '')); ?>">
                        <?php esc_html_e('Annuler', 'em-wp'); ?>
                    </button>
                </div>
            </form>

            <?php if ($entries === []) { ?>
                <p class="em-wp-catalog-sommaire__empty"><?php esc_html_e('Aucune entrée pour le moment.', 'em-wp'); ?></p>
            <?php } else { ?>
                <table class="widefat striped em-wp-catalog-sommaire__table em-wp-catalog-sommaire__table--inline-edit">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Nom', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('Identifiant', 'em-wp'); ?></th>
                            <th scope="col" class="em-wp-catalog-sommaire__actions-col">
                                <span class="screen-reader-text"><?php esc_html_e('Actions', 'em-wp'); ?></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $catalog_slug => $entry) {
                            $catalog_slug = sanitize_key((string) $catalog_slug);
                            $label = sanitize_text_field((string) ($entry['label'] ?? $catalog_slug));
                            $edit_page_slug = call_user_func($config['edit_page_slug'], $catalog_slug);
                            $edit_url = add_query_arg(['page' => $edit_page_slug], admin_url('admin.php'));
                            $preview_slug = call_user_func($config['unique_slug'], call_user_func($config['slug_from_label'], $label), $catalog_slug);
                            $rename_form_id = (string) ($config['rename_row_prefix'] ?? 'em-wp-rename') . '-' . $catalog_slug;

                            em_wp_catalog_render_sommaire_entry_row([
                                'catalog_slug'   => $catalog_slug,
                                'label'          => $label,
                                'preview_slug'   => $preview_slug,
                                'rename_form_id' => $rename_form_id,
                                'form_action'    => $hub_url,
                                'nonce_action'   => (string) call_user_func($config['nonce_action']),
                                'post_prefix'    => $post_prefix,
                                'edit_url'       => $edit_url,
                            ]);
                        } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </section>
    <?php
}

/**
 * Ligne tableau sommaire catalogue (nom en lecture seule + édition inline).
 *
 * @param array{
 *     catalog_slug:string,
 *     label:string,
 *     preview_slug:string,
 *     rename_form_id:string,
 *     form_action:string,
 *     nonce_action:string,
 *     post_prefix:string,
 *     edit_url:string,
 * } $args
 */
function em_wp_catalog_render_sommaire_entry_row(array $args): void
{
    $catalog_slug = sanitize_key((string) ($args['catalog_slug'] ?? ''));
    $label = sanitize_text_field((string) ($args['label'] ?? $catalog_slug));
    $preview_slug = sanitize_text_field((string) ($args['preview_slug'] ?? $catalog_slug));
    $rename_form_id = sanitize_html_class((string) ($args['rename_form_id'] ?? ''));
    $form_action = esc_url((string) ($args['form_action'] ?? ''));
    $nonce_action = (string) ($args['nonce_action'] ?? '');
    $post_prefix = sanitize_key((string) ($args['post_prefix'] ?? ''));
    $edit_url = trim((string) ($args['edit_url'] ?? ''));

    if ($catalog_slug === '' || $post_prefix === '') {
        return;
    }
    ?>
    <tr>
        <td class="em-wp-catalog-sommaire__name">
            <div class="em-wp-catalog-sommaire__inline-field" data-em-wp-catalog-inline-field="name">
                <button
                    type="button"
                    class="em-wp-catalog-sommaire__edit em-wp-catalog-sommaire__inline-edit"
                    data-em-wp-catalog-inline-edit="name"
                    title="<?php esc_attr_e('Modifier le nom', 'em-wp'); ?>"
                    aria-label="<?php echo esc_attr(sprintf(__('Modifier le nom de %s', 'em-wp'), $label)); ?>"
                >
                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                </button>
                <span class="em-wp-catalog-sommaire__inline-value"><?php echo esc_html($label); ?></span>
                <form
                    id="<?php echo esc_attr($rename_form_id); ?>"
                    method="post"
                    action="<?php echo esc_url($form_action); ?>"
                    class="em-wp-catalog-sommaire__rename-form em-wp-catalog-sommaire__inline-rename-form"
                    hidden
                >
                    <?php wp_nonce_field($nonce_action); ?>
                    <input type="hidden" name="<?php echo esc_attr($post_prefix . '_action'); ?>" value="rename">
                    <input type="hidden" name="<?php echo esc_attr($post_prefix . '_slug'); ?>" value="<?php echo esc_attr($catalog_slug); ?>">
                    <input
                        type="text"
                        name="<?php echo esc_attr($post_prefix . '_label'); ?>"
                        value="<?php echo esc_attr($label); ?>"
                        class="regular-text em-wp-catalog-sommaire__label-input em-wp-catalog-sommaire__inline-input"
                        required
                        autocomplete="off"
                        data-em-wp-slug-preview
                        data-em-wp-slug-current="<?php echo esc_attr($catalog_slug); ?>"
                    >
                    <button
                        type="submit"
                        class="em-wp-catalog-sommaire__save em-wp-catalog-sommaire__inline-save"
                        title="<?php esc_attr_e('Enregistrer le nom', 'em-wp'); ?>"
                        aria-label="<?php echo esc_attr(sprintf(__('Enregistrer le nom de %s', 'em-wp'), $label)); ?>"
                    >
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                    </button>
                    <button
                        type="button"
                        class="em-wp-catalog-sommaire__inline-cancel"
                        data-em-wp-catalog-inline-cancel="name"
                        title="<?php esc_attr_e('Annuler', 'em-wp'); ?>"
                        aria-label="<?php esc_attr_e('Annuler la modification du nom', 'em-wp'); ?>"
                    >
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
        </td>
        <td class="em-wp-catalog-sommaire__slug">
            <code
                class="em-wp-catalog-sommaire__slug-preview"
                data-em-wp-slug-preview-for="<?php echo esc_attr($rename_form_id); ?>"
                data-em-wp-slug-current="<?php echo esc_attr($catalog_slug); ?>"
            ><?php echo esc_html($preview_slug); ?></code>
        </td>
        <td class="em-wp-catalog-sommaire__actions">
            <?php if ($edit_url !== '') { ?>
                <a
                    class="em-wp-catalog-sommaire__edit em-wp-catalog-sommaire__content-edit"
                    href="<?php echo esc_url($edit_url); ?>"
                    title="<?php esc_attr_e('Éditer le contenu', 'em-wp'); ?>"
                    aria-label="<?php echo esc_attr(sprintf(__('Éditer le contenu de %s', 'em-wp'), $label)); ?>"
                >
                    <i class="fa-solid fa-gear" aria-hidden="true"></i>
                </a>
            <?php } ?>
            <form
                method="post"
                action="<?php echo esc_url($form_action); ?>"
                class="em-wp-catalog-sommaire__delete-form"
                data-em-wp-delete-label="<?php echo esc_attr($label); ?>"
            >
                <?php wp_nonce_field($nonce_action); ?>
                <input type="hidden" name="<?php echo esc_attr($post_prefix . '_action'); ?>" value="delete">
                <input type="hidden" name="<?php echo esc_attr($post_prefix . '_slug'); ?>" value="<?php echo esc_attr($catalog_slug); ?>">
                <button
                    type="submit"
                    class="em-wp-catalog-sommaire__delete"
                    title="<?php esc_attr_e('Supprimer', 'em-wp'); ?>"
                    aria-label="<?php echo esc_attr(sprintf(__('Supprimer %s', 'em-wp'), $label)); ?>"
                >
                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                </button>
            </form>
        </td>
    </tr>
    <?php
}
