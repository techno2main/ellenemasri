<?php
function em_wp_admin_rubrique_render_edit_navbar(
    array $tabs,
    string $active_module_slug,
    string $list_page_slug,
    bool $show_add_rubrique_toggle = false
): void {
    $active_module_slug = sanitize_key($active_module_slug);
    $list_page_slug = sanitize_key($list_page_slug);
    $list_tab_label = __('Liste', 'em-wp');

    if ($tabs === [] && $list_page_slug === '') {
        return;
    }

    $rubrique_definitions = em_wp_admin_site_rubrique_definitions();
    ?>
    <nav class="em-wp-catalog-edit__nav em-wp-rubrique-edit__nav" aria-label="<?php echo esc_attr__('Navigation Rubriques', 'em-wp'); ?>">
        <ul class="em-wp-catalog-edit__nav-list">
            <?php if ($list_page_slug !== '') {
                $list_url = admin_url('admin.php?page=' . $list_page_slug);
                $is_list_active = $active_module_slug === '';
                ?>
                <li class="em-wp-catalog-edit__nav-item<?php echo $is_list_active ? ' is-active' : ''; ?>">
                    <a
                        class="em-wp-catalog-edit__nav-link em-wp-catalog-edit__nav-link--list"
                        href="<?php echo esc_url($list_url); ?>"
                        aria-label="<?php echo esc_attr($list_tab_label); ?>"
                        <?php echo $is_list_active ? ' aria-current="page"' : ''; ?>
                    >
                        <i class="fa-solid fa-list-ol em-wp-catalog-edit__nav-icon" aria-hidden="true"></i>
                    </a>
                </li>
            <?php } ?>
            <?php foreach ($tabs as $module_slug => $definition) {
                $page_slug = (string) ($definition['page_slug'] ?? '');

                if ($page_slug === '') {
                    continue;
                }

                $label = function_exists('em_wp_admin_rubrique_skeleton_label')
                    ? em_wp_admin_rubrique_skeleton_label((string) $module_slug)
                    : (string) ($definition['menu_title'] ?? $module_slug);
                $is_active = $active_module_slug === (string) $module_slug;
                $item_url = em_wp_admin_rubrique_open_url((string) $module_slug);
                $preview_zone = (string) ($rubrique_definitions[(string) $module_slug]['preview_zone'] ?? '');
                ?>
                <li class="em-wp-catalog-edit__nav-item<?php echo $is_active ? ' is-active' : ''; ?>">
                    <a
                        class="em-wp-catalog-edit__nav-link"
                        href="<?php echo esc_url($item_url); ?>"
                        style="<?php echo esc_attr(em_wp_admin_rubrique_tab_style_attr((string) $module_slug)); ?>"
                        <?php if ($preview_zone !== '') { ?>
                            data-preview-zone="<?php echo esc_attr($preview_zone); ?>"
                        <?php } ?>
                        <?php echo $is_active ? ' aria-current="page"' : ''; ?>
                    >
                        <?php echo esc_html($label); ?>
                    </a>
                </li>
            <?php } ?>
            <?php if ($show_add_rubrique_toggle && current_user_can('manage_options')) { ?>
                <li class="em-wp-catalog-edit__nav-item em-wp-catalog-edit__nav-item--add">
                    <button
                        type="button"
                        class="em-wp-catalog-edit__nav-link em-wp-catalog-edit__nav-link--add"
                        id="em-wp-rubrique-skeleton-add-toggle"
                        aria-label="<?php esc_attr_e('Ajouter une rubrique', 'em-wp'); ?>"
                        aria-controls="em-wp-rubrique-skeleton-add-panel"
                        aria-expanded="false"
                    >
                        <i class="fa-solid fa-plus em-wp-catalog-edit__nav-icon" aria-hidden="true"></i>
                    </button>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <?php
}

/**
 * Onglets Liste + rubriques (TOP-BAR, HEADER, STREAM…).
 */
function em_wp_admin_rubrique_render_entry_tabs(string $active_module_slug = ''): void
{
    $tabs = em_wp_admin_rubrique_nav_tab_definitions();

    if ($tabs === []) {
        return;
    }

    $resolved_module = em_wp_admin_rubrique_resolve_active_module($active_module_slug);
    $show_add_rubrique = $resolved_module === ''
        && function_exists('em_wp_admin_has_template_context')
        && em_wp_admin_has_template_context();

    em_wp_admin_rubrique_render_edit_navbar(
        $tabs,
        $resolved_module,
        em_wp_admin_rubriques_page_slug(),
        $show_add_rubrique
    );

    if ($show_add_rubrique && function_exists('em_wp_admin_render_template_skeleton_add_panel')) {
        em_wp_admin_render_template_skeleton_add_panel();
    }

    em_wp_admin_hub_sticky_head_close();
}

/**
 * En-tête hub (bandeau template + intro) pour une page rubrique en édition.
 */
function em_wp_admin_rubrique_render_editing_page_header(string $module_slug): void
{
    if (!function_exists('em_wp_admin_hub_render_sommaire_header')) {
        return;
    }

    em_wp_admin_hub_render_sommaire_header(
        '',
        'dashicons-admin-page',
        false,
        true,
        null,
        null,
        true
    );

    em_wp_admin_rubrique_render_entry_tabs($module_slug);
}

/**
 * Nom du champ options « enabled » pour une rubrique (vide si non applicable).
 */
function em_wp_admin_rubrique_enabled_field_name(string $module_slug): string
{
    $getters = [
        'top-bar' => 'em_wp_top_bar_form_option_key',
        'header'  => 'em_wp_header_form_option_key',
        'stream'  => 'em_wp_stream_form_option_key',
        'social'  => 'em_wp_social_form_option_key',
        'video'   => 'em_wp_video_form_option_key',
        'release' => 'em_wp_release_form_option_key',
        'cta'     => 'em_wp_cta_form_option_key',
        'footer'  => 'em_wp_footer_form_option_key',
    ];

    $getter = $getters[$module_slug] ?? '';

    if ($getter === '' && function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
        $getter = 'em_wp_custom_catalog_rubrique_form_option_key';
    }

    if ($getter === '' || !function_exists($getter)) {
        return '';
    }

    return (string) call_user_func($getter, $module_slug);
}

/**
 * Indique si la barre rubrique affiche un toggle « Afficher ».
 */
function em_wp_admin_rubrique_section_has_toggle(string $module_slug): bool
{
    return em_wp_admin_rubrique_enabled_field_name($module_slug) !== '';
}

/**
 * Toggle « Afficher » marron dans la barre titre rubrique.
 *
 * @param array<string, mixed> $options Options module (clé enabled pour les rubriques classiques).
 */
function em_wp_admin_rubrique_render_section_toggle(string $module_slug, array $options = []): void
{
    $field = em_wp_admin_rubrique_enabled_field_name($module_slug);

    if ($field === '') {
        return;
    }
    ?>
    <label class="em-wp-rubrique-section-bar__toggle">
        <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
        <?php if (in_array($module_slug, ['footer', 'header'], true)) { ?>
            <input type="hidden" name="<?php echo esc_attr($field); ?>[enabled]" value="0">
        <?php } ?>
        <input
            type="checkbox"
            name="<?php echo esc_attr($field); ?>[enabled]"
            value="1"
            <?php checked(!empty($options['enabled'])); ?>
        >
    </label>
    <?php
}

/**
 * Ouvre le bloc rubrique (barre titre + bordure haute) avant les panneaux.
 *
 * @param array<string, mixed> $options Options module (enabled…).
 */
function em_wp_admin_rubrique_open_section(string $module_slug, array $options = []): void
{
    ?>
    <div class="em-wp-rubrique-section">
        <div class="em-wp-rubrique-section-bar">
            <div class="em-wp-rubrique-section-bar__heading">
                <h2 class="em-wp-rubrique-section-bar__title">
                    <span class="em-wp-admin-module__section-module-pill"><?php echo esc_html(em_wp_admin_rubrique_label($module_slug)); ?></span>
                    <?php
                    $template_label = em_wp_admin_rubrique_editing_template_label();
                    if ($template_label !== '') {
                        ?>
                        <span class="em-wp-rubrique-section-bar__template"><?php echo esc_html($template_label); ?></span>
                        <?php
                    }
                    ?>
                </h2>
                <?php if (em_wp_admin_rubrique_section_has_toggle($module_slug)) {
                    em_wp_admin_rubrique_render_section_toggle($module_slug, $options);
                } ?>
            </div>
        </div>
        <div class="em-wp-rubrique-section__content">
    <?php
}

/**
 * Ferme le bloc rubrique ouvert par em_wp_admin_rubrique_open_section().
 */
function em_wp_admin_rubrique_close_section(): void
{
    ?>
        </div>
    </div>
    <?php
}

