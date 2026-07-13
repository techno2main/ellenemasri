<?php
function em_site_admin_rubrique_render_edit_navbar(
    array $tabs,
    string $active_module_slug,
    string $list_page_slug,
    bool $show_add_rubrique_toggle = false
): void {
    $active_module_slug = sanitize_key($active_module_slug);
    $list_page_slug = sanitize_key($list_page_slug);
    $list_tab_label = __('Liste', 'em-site');

    if ($tabs === [] && $list_page_slug === '') {
        return;
    }

    $rubrique_definitions = em_site_admin_site_rubrique_definitions();
    ?>
    <nav class="em-site-catalog-edit__nav em-site-rubrique-edit__nav" aria-label="<?php echo esc_attr__('Navigation Rubriques', 'em-site'); ?>">
        <ul class="em-site-catalog-edit__nav-list">
            <?php if ($list_page_slug !== '') {
                $list_url = admin_url('admin.php?page=' . $list_page_slug);
                $is_list_active = $active_module_slug === '';
                ?>
                <li class="em-site-catalog-edit__nav-item<?php echo $is_list_active ? ' is-active' : ''; ?>">
                    <a
                        class="em-site-catalog-edit__nav-link em-site-catalog-edit__nav-link--list"
                        href="<?php echo esc_url($list_url); ?>"
                        aria-label="<?php echo esc_attr($list_tab_label); ?>"
                        <?php echo $is_list_active ? ' aria-current="page"' : ''; ?>
                    >
                        <i class="fa-solid fa-list-ol em-site-catalog-edit__nav-icon" aria-hidden="true"></i>
                    </a>
                </li>
            <?php } ?>
            <?php foreach ($tabs as $module_slug => $definition) {
                $page_slug = (string) ($definition['page_slug'] ?? '');

                if ($page_slug === '') {
                    continue;
                }

                $label = function_exists('em_site_admin_rubrique_skeleton_label')
                    ? em_site_admin_rubrique_skeleton_label((string) $module_slug)
                    : (string) ($definition['menu_title'] ?? $module_slug);
                $is_active = $active_module_slug === (string) $module_slug;
                $item_url = em_site_admin_rubrique_open_url((string) $module_slug);
                $preview_zone = (string) ($rubrique_definitions[(string) $module_slug]['preview_zone'] ?? '');
                ?>
                <li class="em-site-catalog-edit__nav-item<?php echo $is_active ? ' is-active' : ''; ?>">
                    <a
                        class="em-site-catalog-edit__nav-link"
                        href="<?php echo esc_url($item_url); ?>"
                        style="<?php echo esc_attr(em_site_admin_rubrique_tab_style_attr((string) $module_slug)); ?>"
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
                <li class="em-site-catalog-edit__nav-item em-site-catalog-edit__nav-item--add">
                    <button
                        type="button"
                        class="em-site-catalog-edit__nav-link em-site-catalog-edit__nav-link--add"
                        id="em-site-rubrique-skeleton-add-toggle"
                        aria-label="<?php esc_attr_e('Ajouter une rubrique', 'em-site'); ?>"
                        aria-controls="em-site-rubrique-skeleton-add-panel"
                        aria-expanded="false"
                    >
                        <i class="fa-solid fa-plus em-site-catalog-edit__nav-icon" aria-hidden="true"></i>
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
function em_site_admin_rubrique_render_entry_tabs(string $active_module_slug = ''): void
{
    $tabs = em_site_admin_rubrique_nav_tab_definitions();

    if ($tabs === []) {
        return;
    }

    $resolved_module = em_site_admin_rubrique_resolve_active_module($active_module_slug);
    $show_add_rubrique = $resolved_module === ''
        && function_exists('em_site_admin_has_template_context')
        && em_site_admin_has_template_context();

    em_site_admin_rubrique_render_edit_navbar(
        $tabs,
        $resolved_module,
        function_exists('em_site_admin_rubriques_context_page_slug')
            ? em_site_admin_rubriques_context_page_slug()
            : em_site_admin_rubriques_page_slug(),
        $show_add_rubrique
    );

    if ($show_add_rubrique && function_exists('em_site_admin_render_template_skeleton_add_panel')) {
        em_site_admin_render_template_skeleton_add_panel();
    }

    em_site_admin_hub_sticky_head_close();
}

/**
 * En-tête hub (bandeau template + intro) pour une page rubrique en édition.
 */
function em_site_admin_rubrique_render_editing_page_header(string $module_slug): void
{
    if (!function_exists('em_site_admin_hub_render_sommaire_header')) {
        return;
    }

    em_site_admin_hub_render_sommaire_header(
        '',
        'dashicons-admin-page',
        false,
        true,
        null,
        null,
        true
    );

    em_site_admin_rubrique_render_entry_tabs($module_slug);
}

/**
 * Nom du champ options « enabled » pour une rubrique (vide si non applicable).
 */
function em_site_admin_rubrique_enabled_field_name(string $module_slug): string
{
    $getters = [
        'top-bar' => 'em_site_top_bar_form_option_key',
        'header'  => 'em_site_header_form_option_key',
        'stream'  => 'em_site_stream_form_option_key',
        'social'  => 'em_site_social_form_option_key',
        'video'   => 'em_site_video_form_option_key',
        'release' => 'em_site_release_form_option_key',
        'cta'     => 'em_site_cta_form_option_key',
        'footer'  => 'em_site_footer_form_option_key',
    ];

    $getter = $getters[$module_slug] ?? '';

    if ($getter === '' && function_exists('em_site_custom_catalog_is_module') && em_site_custom_catalog_is_module($module_slug)) {
        $getter = 'em_site_custom_catalog_rubrique_form_option_key';
    }

    if ($getter === '' || !function_exists($getter)) {
        return '';
    }

    return (string) call_user_func($getter, $module_slug);
}

/**
 * Indique si la barre rubrique affiche un toggle « Afficher ».
 */
function em_site_admin_rubrique_section_has_toggle(string $module_slug): bool
{
    return em_site_admin_rubrique_enabled_field_name($module_slug) !== '';
}

/**
 * Toggle « Afficher » marron dans la barre titre rubrique.
 *
 * @param array<string, mixed> $options Options module (clé enabled pour les rubriques classiques).
 */
function em_site_admin_rubrique_render_section_toggle(string $module_slug, array $options = []): void
{
    $field = em_site_admin_rubrique_enabled_field_name($module_slug);

    if ($field === '') {
        return;
    }
    ?>
    <label class="em-site-rubrique-section-bar__toggle">
        <span><?php esc_html_e('Afficher', 'em-site'); ?></span>
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
function em_site_admin_rubrique_open_section(string $module_slug, array $options = []): void
{
    ?>
    <div class="em-site-rubrique-section">
        <div class="em-site-rubrique-section-bar">
            <div class="em-site-rubrique-section-bar__heading">
                <h2 class="em-site-rubrique-section-bar__title">
                    <span class="em-site-admin-module__section-module-pill"><?php echo esc_html(em_site_admin_rubrique_label($module_slug)); ?></span>
                    <?php
                    $template_label = em_site_admin_rubrique_editing_template_label();
                    if ($template_label !== '') {
                        ?>
                        <span class="em-site-rubrique-section-bar__template"><?php echo esc_html($template_label); ?></span>
                        <?php
                    }
                    ?>
                </h2>
                <?php if (em_site_admin_rubrique_section_has_toggle($module_slug)) {
                    em_site_admin_rubrique_render_section_toggle($module_slug, $options);
                } ?>
            </div>
        </div>
        <div class="em-site-rubrique-section__content">
    <?php
}

/**
 * Ferme le bloc rubrique ouvert par em_site_admin_rubrique_open_section().
 */
function em_site_admin_rubrique_close_section(): void
{
    ?>
        </div>
    </div>
    <?php
}

