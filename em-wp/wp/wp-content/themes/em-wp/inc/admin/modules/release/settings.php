<?php
/**
 * Paramétrage du module Release (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_release_page_slug(): string
{
    return 'em-wp-releases';
}

function em_wp_release_default_options(): array
{
    return [
        'enabled'          => true,
        'background_color' => '',
        'text_color'       => '',
        'kicker'           => __('04 / Release', 'em-wp'),
        'title_left'       => __('The', 'em-wp'),
        'title_highlight'  => __('credits', 'em-wp'),
        'cover_image'      => '',
        'rows'             => [],
    ];
}

function em_wp_release_get_options(): array
{
    $saved = get_option('em_wp_release_options', []);
    if (!is_array($saved)) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_release_default_options());
    $options['rows'] = em_wp_release_normalize_rows($options['rows'] ?? []);

    return function_exists('em_wp_rubrique_sync_enabled_for_admin')
        ? em_wp_rubrique_sync_enabled_for_admin('release', $options)
        : $options;
}

/**
 * @param mixed $raw
 * @return array<int, array{key:string,value:string,hidden:bool}>
 */
function em_wp_release_normalize_rows($raw): array
{
    if (!is_array($raw)) {
        return [];
    }

    $rows = [];
    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }

        $key = sanitize_text_field((string) ($row['key'] ?? ''));
        $value = sanitize_text_field((string) ($row['value'] ?? ''));
        if ($key === '' && $value === '') {
            continue;
        }

        $rows[] = [
            'key'    => $key,
            'value'  => $value,
            'hidden' => !empty($row['hidden']),
        ];
    }

    return $rows;
}

function em_wp_release_sanitize_options($input): array
{
    if (!is_array($input)) {
        return em_wp_release_get_options();
    }

    $enabled = !empty($input['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('release', $enabled);
    }

    return [
        'enabled'          => $enabled,
        'background_color' => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'       => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'kicker'           => sanitize_text_field($input['kicker'] ?? ''),
        'title_left'       => sanitize_text_field($input['title_left'] ?? ''),
        'title_highlight'  => sanitize_text_field($input['title_highlight'] ?? ''),
        'cover_image'      => esc_url_raw($input['cover_image'] ?? ''),
        'rows'             => em_wp_release_normalize_rows($input['rows'] ?? []),
    ];
}

function em_wp_release_register_settings(): void
{
    register_setting(
        'em_wp_release_group',
        'em_wp_release_options',
        [
            'type'              => 'array',
            'sanitize_callback' => 'em_wp_release_sanitize_options',
            'default'           => em_wp_release_default_options(),
        ]
    );
}
add_action('admin_init', 'em_wp_release_register_settings');

function em_wp_release_register_admin(): void
{
    add_menu_page(
        __('RELEASES', 'em-wp'),
        __('RELEASES', 'em-wp'),
        'manage_options',
        em_wp_release_page_slug(),
        'em_wp_release_render_admin_page',
        'dashicons-album',
        em_wp_admin_menu_position_for_site_module('release')
    );
}
add_action('admin_menu', 'em_wp_release_register_admin');

function em_wp_release_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_release_page_slug(), em_wp_release_page_slug());
}
add_action('admin_menu', 'em_wp_release_remove_duplicate_submenu', 999);

function em_wp_release_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    if (sanitize_key((string) ($_GET['page'] ?? '')) !== em_wp_release_page_slug()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }

    em_wp_admin_enqueue_shared_assets();
    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'em-wp-release-admin',
        $theme_uri . '/assets/admin/css/modules/release/release.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/release/release.css')
    );

    wp_enqueue_script(
        'em-wp-admin-slide-sortable',
        $theme_uri . '/assets/admin/js/shared/slide-sortable.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/slide-sortable.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-release-admin',
        $theme_uri . '/assets/admin/js/modules/release/release.js',
        ['em-wp-admin-slide-sortable', 'em-wp-admin-accordion', 'em-wp-admin-confirm-modal'],
        em_wp_admin_asset_version('assets/admin/js/modules/release/release.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-stream-admin',
        $theme_uri . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_release_admin_enqueue');

/**
 * @param array{key:string,value:string,hidden?:bool} $row
 */
function em_wp_release_render_row_item(int $index, array $row): void
{
    $field_base = 'em_wp_release_options[rows][' . $index . ']';
    $is_hidden = !empty($row['hidden']);
    ?>
    <div class="em-wp-release-row-item em-wp-admin-panel-body--row<?php echo $is_hidden ? ' is-row-hidden' : ''; ?>" data-release-row-item>
        <span class="em-wp-slide-sortable__handle em-wp-release-row-item__handle" role="button" tabindex="0" aria-label="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>" title="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
        <label class="em-wp-admin-field--compact">
            <span><?php esc_html_e('Label', 'em-wp'); ?></span>
            <input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[key]'); ?>" value="<?php echo esc_attr($row['key']); ?>">
        </label>
        <label class="em-wp-admin-field--wide-inline">
            <span><?php esc_html_e('Valeur', 'em-wp'); ?></span>
            <input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[value]'); ?>" value="<?php echo esc_attr($row['value']); ?>">
        </label>
        <div class="em-wp-release-row-item__actions">
            <label class="em-wp-admin-inline-check">
                <span><?php esc_html_e('Masquer', 'em-wp'); ?></span>
                <input type="checkbox" class="em-wp-release-row-hidden" name="<?php echo esc_attr($field_base . '[hidden]'); ?>" value="1" <?php checked($is_hidden); ?>>
            </label>
            <button type="button" class="button button-link-delete em-wp-release-row-delete"><?php esc_html_e('Supprimer', 'em-wp'); ?></button>
        </div>
    </div>
    <?php
}

function em_wp_release_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $options = em_wp_release_get_options();
    $rows = $options['rows'];
    if ($rows === []) {
        $rows = [['key' => '', 'value' => '', 'hidden' => false]];
    }

    $style_defaults = em_wp_admin_module_default_style_colors('release');
    ?>
    <div class="wrap em-wp-release-admin em-wp-admin-module" <?php echo em_wp_admin_module_style_data_attributes_for_module('release', 'em_wp_release_options', $options); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('release', $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="em-wp-admin-module__hero">
            <div>
                <p class="em-wp-admin-module__eyebrow"><?php esc_html_e('RELEASE', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php esc_html_e('Section 04 / RELEASE INFOS', 'em-wp'); ?></p>
            </div>
            <label class="em-wp-admin-module__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="em_wp_release_options[enabled]" value="1" form="em-wp-release-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>

        <form id="em-wp-release-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_release_page_slug())); ?>">
            <?php em_wp_admin_render_form_save_fields('release', 'em_wp_release_save'); ?>

            <div class="em-wp-admin-module__panels">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        ['name' => 'background_color', 'label' => __('Couleur de fond', 'em-wp'), 'value' => (string) ($options['background_color'] ?? ''), 'placeholder' => $style_defaults['background']],
                        ['name' => 'text_color', 'label' => __('Couleur du texte', 'em-wp'), 'value' => (string) ($options['text_color'] ?? ''), 'placeholder' => $style_defaults['text']],
                    ],
                    'em_wp_release_options',
                    'em-wp-release-panel'
                );

                em_wp_admin_render_module_items_section_title('release');

                em_wp_admin_render_module_panel(
                    __('Contenu', 'em-wp'),
                    'em-wp-release-panel',
                    static function () use ($options): void {
                        ?>
                        <label><span><?php esc_html_e('Kicker', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_release_options[kicker]" value="<?php echo esc_attr($options['kicker']); ?>"></label>
                        <label><span><?php esc_html_e('Titre gauche', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_release_options[title_left]" value="<?php echo esc_attr($options['title_left']); ?>"></label>
                        <label><span><?php esc_html_e('Titre surligné', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_release_options[title_highlight]" value="<?php echo esc_attr($options['title_highlight']); ?>"></label>
                        <label class="em-wp-admin-field--wide">
                            <span><?php esc_html_e('Cover', 'em-wp'); ?></span>
                            <div class="em-wp-admin-media-picker">
                                <input type="text" id="em-wp-release-cover" name="em_wp_release_options[cover_image]" value="<?php echo esc_attr($options['cover_image']); ?>" class="regular-text em-wp-admin-field-input--wide">
                                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-release-cover" data-preview="em-wp-release-cover-preview"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                            </div>
                            <div id="em-wp-release-cover-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['cover_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['cover_image'])) { ?><img src="<?php echo esc_url($options['cover_image']); ?>" alt=""><?php } ?></div>
                        </label>
                        <?php
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Infos release', 'em-wp'),
                    'em-wp-release-panel',
                    static function () use ($rows): void {
                        ?>
                        <div class="em-wp-release-rows-list" id="em-wp-release-rows-list" data-option-name="em_wp_release_options" data-field-key="rows">
                            <?php foreach ($rows as $index => $row) {
                                em_wp_release_render_row_item((int) $index, $row);
                            } ?>
                        </div>
                        <p><button type="button" class="button button-secondary" id="em-wp-release-add-row"><?php esc_html_e('+ Ajouter une info', 'em-wp'); ?></button></p>
                        <?php
                    }
                );
                ?>
            </div>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}
