<?php
/**
 * Panneau mutualisé d’import catalogue (Release, Stream, Top-Bar…).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Libellés singuliers pour les panneaux catalogue (casse phrase, pas squelette).
 *
 * @return array<string, string>
 */
function em_wp_admin_rubrique_catalog_item_labels(): array
{
    return [
        'top-bar'  => __('Top-Bar', 'em-wp'),
        'stream'   => __('Stream', 'em-wp'),
        'social'   => __('Social', 'em-wp'),
        'video'    => __('Video', 'em-wp'),
        'release'  => __('Release', 'em-wp'),
        'cta'      => __('CTA', 'em-wp'),
        'footer'   => __('Footer', 'em-wp'),
        'contacts' => __('Contact', 'em-wp'),
    ];
}

/**
 * Première lettre majuscule, reste en minuscules (CONTACT → Contact).
 */
function em_wp_admin_rubrique_catalog_sentence_case(string $label): string
{
    $label = trim($label);

    if ($label === '') {
        return '';
    }

    return mb_strtoupper(mb_substr($label, 0, 1)) . mb_strtolower(mb_substr($label, 1));
}

/**
 * Nom singulier affiché dans « Release du catalogue », « Contact du catalogue »…
 */
function em_wp_admin_rubrique_catalog_item_label(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);
    $labels = em_wp_admin_rubrique_catalog_item_labels();

    if (isset($labels[$module_slug])) {
        return (string) $labels[$module_slug];
    }

    if (function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
        $module = em_wp_custom_catalog_module($module_slug);

        if (is_array($module)) {
            $rubrique = trim((string) ($module['description_rubrique'] ?? ''));

            if ($rubrique !== '') {
                return em_wp_admin_rubrique_catalog_sentence_case($rubrique);
            }

            $label = trim((string) ($module['description_item'] ?? $module['label'] ?? ''));

            if ($label !== '') {
                return em_wp_admin_rubrique_catalog_sentence_case($label);
            }
        }
    }

    if (function_exists('em_wp_admin_rubrique_skeleton_label')) {
        return em_wp_admin_rubrique_catalog_sentence_case(
            em_wp_admin_rubrique_skeleton_label($module_slug)
        );
    }

    return em_wp_admin_rubrique_catalog_sentence_case($module_slug);
}

/**
 * Titre panneau import catalogue : TOP-BAR, STREAM, RELEASE…
 */
function em_wp_admin_rubrique_catalog_panel_title(string $module_slug): string
{
    return em_wp_admin_rubrique_label($module_slug);
}

/**
 * Libellé switcher : « Importer TOP-BAR », « Importer STREAM »…
 */
function em_wp_admin_rubrique_catalog_import_switcher_label(string $module_slug): string
{
    return sprintf(
        /* translators: %s: rubrique label (TOP-BAR, STREAM, …) */
        __('Importer %s', 'em-wp'),
        em_wp_admin_rubrique_label($module_slug)
    );
}

/**
 * Libellé switcher pour un module catalogue (HERO, SLIDER…).
 */
function em_wp_admin_catalog_module_import_switcher_label(string $catalog_module_slug): string
{
    $catalog_module_slug = sanitize_key($catalog_module_slug);

    $labels = [
        'hero'   => 'HERO',
        'slider' => 'SLIDER',
    ];

    $label = $labels[$catalog_module_slug] ?? mb_strtoupper($catalog_module_slug);

    return sprintf(
        /* translators: %s: catalog module label (HERO, SLIDER, …) */
        __('Importer %s', 'em-wp'),
        $label
    );
}

/**
 * Libellé hub Catalogues → Releases / Contacts…
 */
function em_wp_admin_rubrique_catalog_hub_menu_label(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);

    if (function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
        $module = em_wp_custom_catalog_module($module_slug);

        if (is_array($module)) {
            $label = trim((string) ($module['label'] ?? ''));

            if ($label !== '') {
                return $label;
            }
        }
    }

    $hub_labels = [
        'top-bar' => __('Top-Bars', 'em-wp'),
        'stream'  => __('Streams', 'em-wp'),
        'social'  => __('Socials', 'em-wp'),
        'video'   => __('Videos', 'em-wp'),
        'release' => __('Releases', 'em-wp'),
        'cta'     => __('CTAs', 'em-wp'),
        'footer'  => __('Footers', 'em-wp'),
    ];

    return (string) ($hub_labels[$module_slug] ?? em_wp_admin_rubrique_catalog_item_label($module_slug));
}

/**
 * Segment « la release », « le stream », « le contact »…
 */
function em_wp_admin_rubrique_catalog_selection_phrase(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);

    $phrases = [
        'top-bar'  => __('la top-bar', 'em-wp'),
        'stream'   => __('le stream', 'em-wp'),
        'social'   => __('le bloc social', 'em-wp'),
        'video'    => __('la video', 'em-wp'),
        'release'  => __('la release', 'em-wp'),
        'cta'      => __('le CTA', 'em-wp'),
        'footer'   => __('le footer', 'em-wp'),
        'contacts' => __('le contact', 'em-wp'),
    ];

    if (isset($phrases[$module_slug])) {
        return (string) $phrases[$module_slug];
    }

    return sprintf(
        /* translators: %s: catalog item label */
        __('l’entrée %s', 'em-wp'),
        mb_strtolower(em_wp_admin_rubrique_catalog_item_label($module_slug))
    );
}

/**
 * Description sous le panneau import catalogue.
 */
function em_wp_admin_rubrique_catalog_selection_description(string $module_slug): string
{
    return sprintf(
        /* translators: %s: rubrique label (TOP-BAR, STREAM, …) */
        __('Choisis la rubrique %s à afficher depuis le Catalogue.', 'em-wp'),
        em_wp_admin_rubrique_label($module_slug)
    );
}

/**
 * Description panneau HEADER (hero + slider).
 */
function em_wp_admin_header_catalog_selection_description(): string
{
    return __('Choisis le HERO et/ou le SLIDER à afficher depuis le Catalogue.', 'em-wp');
}

/**
 * Panneau mutualisé de sélection d’entrée catalogue.
 *
 * @param array<string, string> $choices
 */
function em_wp_admin_render_catalog_rubrique_selection_panel(
    string $module_slug,
    string $field,
    string $pointer_field_name,
    string $selected_slug,
    array $choices,
    string $panel_class = 'em-wp-release-panel'
): void {
    $module_slug = sanitize_key($module_slug);
    $panel_title = em_wp_admin_rubrique_catalog_panel_title($module_slug);
    $switcher_label = em_wp_admin_rubrique_catalog_import_switcher_label($module_slug);

    em_wp_admin_render_module_panel(
        $panel_title,
        $panel_class,
        static function () use ($field, $pointer_field_name, $selected_slug, $choices, $module_slug, $switcher_label): void {
            ?>
            <p class="description"><?php echo esc_html(em_wp_admin_rubrique_catalog_selection_description($module_slug)); ?></p>
            <?php
            em_wp_admin_render_catalog_slug_switcher(
                $field . '[' . $pointer_field_name . ']',
                $selected_slug,
                $choices,
                $switcher_label,
                $module_slug
            );
        },
        'em-wp-admin-panel-body--stack em-wp-header-admin__selection',
        true
    );
}

/**
 * Page admin mutualisée d’une rubrique liée au catalogue.
 *
 * @param array{
 *     module_slug:string,
 *     page_slug:string,
 *     save_nonce_action:string,
 *     options:array<string, mixed>,
 *     choices:array<string, string>,
 *     pointer_key:string,
 *     field:string,
 *     form_id?:string,
 *     wrap_class?:string,
 *     panel_class?:string,
 *     panels_wrap_class?:string
 * } $config
 */
function em_wp_admin_render_catalog_rubrique_page(array $config): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $module_slug = sanitize_key((string) ($config['module_slug'] ?? ''));

    if ($module_slug === '') {
        return;
    }

    $page_slug = sanitize_key((string) ($config['page_slug'] ?? ''));
    $save_nonce_action = (string) ($config['save_nonce_action'] ?? '');
    $options = is_array($config['options'] ?? null) ? $config['options'] : [];
    $choices = is_array($config['choices'] ?? null) ? $config['choices'] : [];
    $pointer_key = sanitize_key((string) ($config['pointer_key'] ?? ''));
    $field = (string) ($config['field'] ?? '');
    $form_id = (string) ($config['form_id'] ?? 'em-wp-' . $module_slug . '-form');
    $wrap_class = (string) ($config['wrap_class'] ?? 'em-wp-release-admin em-wp-header-admin em-wp-admin-module em-wp-hub-sommaire');
    $panel_class = (string) ($config['panel_class'] ?? 'em-wp-release-panel');
    $panels_wrap_class = (string) ($config['panels_wrap_class'] ?? 'em-wp-admin-module__panels');
    $selected = sanitize_key((string) ($options[$pointer_key] ?? ''));
    $style_defaults = em_wp_admin_module_default_style_colors($module_slug);
    ?>
    <div class="wrap <?php echo esc_attr($wrap_class); ?>" <?php echo em_wp_admin_module_style_data_attributes_for_module($module_slug, $field, $options); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module($module_slug, $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <?php em_wp_admin_rubrique_render_editing_page_header($module_slug); ?>

        <form id="<?php echo esc_attr($form_id); ?>" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action($page_slug)); ?>">
            <?php
            em_wp_admin_render_form_save_fields(
                $module_slug,
                $save_nonce_action,
                ['em_wp_template_context' => em_wp_get_editing_template_slug()]
            );
            ?>

            <?php em_wp_admin_rubrique_open_section($module_slug, $options); ?>
            <div class="<?php echo esc_attr($panels_wrap_class); ?>">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        ['name' => 'background_color', 'label' => __('Couleur de fond', 'em-wp'), 'value' => (string) ($options['background_color'] ?? ''), 'placeholder' => $style_defaults['background']],
                        ['name' => 'text_color', 'label' => __('Couleur du texte', 'em-wp'), 'value' => (string) ($options['text_color'] ?? ''), 'placeholder' => $style_defaults['text']],
                    ],
                    $field,
                    $panel_class
                );

                em_wp_admin_render_catalog_rubrique_selection_panel(
                    $module_slug,
                    $field,
                    $pointer_key,
                    $selected,
                    $choices,
                    $panel_class
                );
                ?>
            </div>
            <?php em_wp_admin_rubrique_close_section(); ?>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}
