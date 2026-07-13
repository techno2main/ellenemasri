<?php
function em_site_admin_catalog_choice_switch_color(string $catalog_slug): string
{
    unset($catalog_slug);

    return em_site_admin_hub_brand_accent_color();
}

/**
 * Module catalogue associé à un identifiant rubrique (hero, slider, video…).
 */
function em_site_admin_catalog_part_to_module_slug(string $catalog_part): string
{
    $catalog_part = sanitize_key($catalog_part);

    if ($catalog_part !== ''
        && function_exists('em_site_custom_catalog_is_module')
        && em_site_custom_catalog_is_module($catalog_part)) {
        return $catalog_part;
    }

    static $map = [
        'hero'    => 'heros',
        'slider'  => 'sliders',
        'video'   => 'videos',
        'stream'  => 'streams',
        'social'  => 'socials',
        'top-bar' => 'top-bars',
        'release' => 'releases',
        'cta'     => 'ctas',
        'footer'  => 'footers',
    ];

    return (string) ($map[$catalog_part] ?? '');
}

/**
 * URL admin d'édition d'une entrée catalogue (depuis une rubrique template).
 */
function em_site_admin_catalog_entry_edit_url(string $catalog_part, string $catalog_slug): string
{
    $catalog_slug = sanitize_key($catalog_slug);

    if ($catalog_slug === '') {
        return '';
    }

    $module_slug = em_site_admin_catalog_part_to_module_slug($catalog_part);

    if ($module_slug === '' || !function_exists('em_site_catalog_hub_edit_page_slug_fn')) {
        return '';
    }

    $slug_fn = em_site_catalog_hub_edit_page_slug_fn($module_slug);

    if ($slug_fn === null) {
        return '';
    }

    $page_slug = $slug_fn($catalog_slug);

    if ($page_slug === '') {
        return '';
    }

    return add_query_arg(['page' => $page_slug], admin_url('admin.php'));
}

/**
 * Sélecteur catalogue hero/slider (toggles exclusifs, style template live).
 *
 * @param array<string, string> $choices slug => label
 */
function em_site_admin_render_catalog_slug_switcher(
    string $input_name,
    string $selected_slug,
    array $choices,
    string $group_label = '',
    string $catalog_part = ''
): void {
    $selected_slug = sanitize_key($selected_slug);
    $switch_group_id = wp_unique_id('em-site-catalog-slug-switches-');
    $catalog_part = sanitize_key($catalog_part);
    $field_attrs = 'class="em-site-header-admin__field em-site-header-admin__field--catalog"';

    if ($catalog_part !== '') {
        $field_attrs .= ' data-catalog-part="' . esc_attr($catalog_part) . '"';
    }
    ?>
    <div <?php echo $field_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
        <?php if ($group_label !== '') { ?>
            <span class="em-site-header-admin__catalog-switcher-label"><?php echo esc_html($group_label); ?></span>
        <?php } else { ?>
            <span class="em-site-header-admin__catalog-switcher-label" aria-hidden="true"></span>
        <?php } ?>

        <div class="em-site-header-admin__catalog-switcher-control">
        <?php if ($choices === []) { ?>
            <p class="description"><?php esc_html_e('Aucune entrée catalogue disponible.', 'em-site'); ?></p>
            <input type="hidden" name="<?php echo esc_attr($input_name); ?>" value="">
        <?php } else {
            $catalog_accent = em_site_admin_catalog_choice_switch_color('');
            ?>
            <div
                id="<?php echo esc_attr($switch_group_id); ?>"
                class="em-site-hub__live-switches em-site-admin-catalog-slug-switches"
                role="group"
                aria-label="<?php echo esc_attr($group_label !== '' ? $group_label : __('Sélection catalogue', 'em-site')); ?>"
                style="--em-site-live-color: <?php echo esc_attr($catalog_accent); ?>;"
            >
                <?php foreach ($choices as $slug => $label) {
                    $slug = sanitize_key((string) $slug);
                    if ($slug === '') {
                        continue;
                    }

                    $display_label = trim((string) $label);
                    if ($display_label === '') {
                        $display_label = $slug;
                    }

                    $wireframe_label = em_site_admin_catalog_choice_switch_label($slug, $display_label);

                    $is_selected = ($slug === $selected_slug);
                    $switch_id = $switch_group_id . '-' . sanitize_html_class($slug);
                    $entry_url = $catalog_part !== ''
                        ? em_site_admin_catalog_entry_edit_url($catalog_part, $slug)
                        : '';
                    $entry_open_label = sprintf(
                        /* translators: %s: catalog entry label */
                        __('Ouvrir %s dans le catalogue', 'em-site'),
                        $display_label
                    );
                    ?>
                    <div class="em-site-hub__live-switch">
                        <?php if ($entry_url !== '') { ?>
                            <a
                                href="<?php echo esc_url($entry_url); ?>"
                                class="em-site-catalog-entry-open"
                                aria-label="<?php echo esc_attr($entry_open_label); ?>"
                                title="<?php echo esc_attr($entry_open_label); ?>"
                                data-entry-label="<?php echo esc_attr($display_label); ?>"
                            >
                                <span class="dashicons dashicons-external" aria-hidden="true"></span>
                            </a>
                        <?php } ?>
                        <label class="em-site-hub__live-switch-control" for="<?php echo esc_attr($switch_id); ?>">
                            <span class="em-site-hub__live-switch-label"><?php echo esc_html($display_label); ?></span>
                            <input
                                type="checkbox"
                                class="em-site-hub__live-switch-input em-site-admin-catalog-slug-switch"
                                id="<?php echo esc_attr($switch_id); ?>"
                                role="switch"
                                data-choice-slug="<?php echo esc_attr($slug); ?>"
                                data-choice-label="<?php echo esc_attr($display_label); ?>"
                                data-choice-wireframe-label="<?php echo esc_attr($wireframe_label); ?>"
                                <?php checked($is_selected); ?>
                                aria-checked="<?php echo $is_selected ? 'true' : 'false'; ?>"
                            >
                            <span class="em-site-hub__live-switch-ui" aria-hidden="true"></span>
                        </label>
                    </div>
                <?php } ?>
            </div>

            <input
                type="hidden"
                class="em-site-admin-catalog-slug-input"
                name="<?php echo esc_attr($input_name); ?>"
                value="<?php echo esc_attr($selected_slug); ?>"
            >
        <?php } ?>
        </div>
    </div>
    <?php
}

/**
 * Enqueue JS des toggles catalogue (hero/slider HEADER…).
 */
function em_site_admin_enqueue_catalog_slug_switch_assets(): void
{
    em_site_admin_hub_cards_enqueue_assets();

    if (!wp_script_is('em-site-admin-confirm-modal', 'registered')) {
        wp_register_script(
            'em-site-admin-confirm-modal',
            get_template_directory_uri() . '/assets/admin/shared/js/modals/confirm-modal.js',
            [],
            em_site_admin_asset_version('assets/admin/shared/js/modals/confirm-modal.js'),
            true
        );
    }

    if (!wp_script_is('em-site-admin-module-form-dirty', 'registered')) {
        wp_register_script(
            'em-site-admin-module-form-dirty-engine',
            get_template_directory_uri() . '/assets/admin/shared/js/state/module-form-dirty/engine.js',
            ['em-site-admin-confirm-modal'],
            em_site_admin_asset_version('assets/admin/shared/js/state/module-form-dirty/engine.js'),
            true
        );

        wp_register_script(
            'em-site-admin-module-form-dirty',
            get_template_directory_uri() . '/assets/admin/shared/js/state/module-form-dirty.js',
            ['em-site-admin-confirm-modal', 'em-site-admin-module-form-dirty-engine'],
            em_site_admin_asset_version('assets/admin/shared/js/state/module-form-dirty.js'),
            true
        );
    }

    wp_enqueue_script('em-site-admin-confirm-modal');
    wp_enqueue_script('em-site-admin-module-form-dirty');

    wp_enqueue_script(
        'em-site-admin-catalog-slug-switch',
        get_template_directory_uri() . '/assets/admin/shared/js/navigation/catalog-slug-switch.js',
        ['em-site-admin-confirm-modal', 'em-site-admin-module-form-dirty'],
        em_site_admin_asset_version('assets/admin/shared/js/navigation/catalog-slug-switch.js'),
        true
    );

    $template_label = function_exists('em_site_get_editing_template_label')
        ? (string) em_site_get_editing_template_label()
        : '';
    $has_template_context = function_exists('em_site_admin_has_template_context') && em_site_admin_has_template_context();

    wp_localize_script(
        'em-site-admin-catalog-slug-switch',
        'EmWpCatalogEntryOpen',
        [
            'hasTemplateContext' => $has_template_context,
            'quitEndpoint'       => admin_url('admin.php'),
            'quitNonce'          => wp_create_nonce('em_site_quit_editing_nav'),
            'templateLabel'      => $template_label,
            'strings'            => [
                'openConfirm'         => __('Tu vas quitter l\'édition en cours pour ouvrir « %s » dans le catalogue.', 'em-site'),
                'openConfirmTemplate' => __('Tu vas quitter l\'édition du template « %1$s » pour ouvrir « %2$s » dans le catalogue.', 'em-site'),
                'confirmOpen'         => __('Ouvrir le catalogue', 'em-site'),
                'confirmSaveOpen'     => __('Enregistrer & Ouvrir', 'em-site'),
                'stay'                => __('Rester', 'em-site'),
                'saveConfirm'         => __('Enregistrer la configuration actuelle et continuer ?', 'em-site'),
                'saveLabel'           => __('Enregistrer', 'em-site'),
                'saveCancel'          => __('Annuler', 'em-site'),
            ],
        ]
    );
}

