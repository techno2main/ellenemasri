<?php
function em_wp_admin_hub_render_live_template_badge(string $active_label, string $active_slug, bool $in_card = false): void
{
    $active_slug = sanitize_key($active_slug);

    if ($in_card) {
        ?>
        <div class="em-wp-hub__card-live-status">
            <p class="em-wp-hub__card-live-status-prefix">
                <?php esc_html_e('Ton site utilise actuellement le template :', 'em-wp'); ?>
            </p>
            <?php em_wp_admin_hub_render_template_active_pill($active_label, $active_slug); ?>
        </div>
        <?php
        return;
    }

    em_wp_admin_hub_render_template_active_pill($active_label, $active_slug);
}

/**
 * Pastille « template live » compacte (carte template individuelle).
 */
function em_wp_admin_hub_render_template_live_badge(string $label, string $template_slug): void
{
    em_wp_admin_hub_render_template_active_pill($label, $template_slug);
}

/**
 * Pastille template actif (fond couleur template, typo blanche, chip LIVE clignotante).
 */
function em_wp_admin_hub_render_template_active_pill(string $label, string $template_slug): void
{
    $label = trim($label);
    $template_slug = sanitize_key($template_slug);
    $style_attr = function_exists('em_wp_admin_template_tab_style_attr')
        ? em_wp_admin_template_tab_style_attr($template_slug)
        : '';
    ?>
    <div
        class="em-wp-hub__template-live-pill"
        role="status"
        <?php if ($style_attr !== '') { ?>
            style="<?php echo esc_attr($style_attr); ?>"
        <?php } ?>
    >
        <span class="em-wp-hub__template-live-pill-name"><?php echo esc_html(mb_strtoupper($label)); ?></span>
        <span class="em-wp-hub__template-live-pill-live">
            <span class="em-wp-hub__live-indicator" aria-hidden="true">
                <span class="em-wp-hub__live-dot"></span>
            </span>
            <?php esc_html_e('Live', 'em-wp'); ?>
        </span>
    </div>
    <?php
}

/**
 * Pastille « Activer » (fond couleur template, chip Activer).
 *
 * @param array{compact?:bool,table?:bool,block?:bool,id?:string,class?:string} $args
 */
function em_wp_admin_hub_render_template_activate_pill(string $slug, string $display_label, array $args = []): void
{
    $slug = sanitize_key($slug);
    $label_upper = mb_strtoupper(trim($display_label));
    $compact = !empty($args['compact']);
    $table = !empty($args['table']);
    $block = !empty($args['block']);
    $id = sanitize_html_class((string) ($args['id'] ?? ''));
    $extra_class = trim((string) ($args['class'] ?? ''));

    $classes = trim(
        'em-wp-hub__template-live-pill em-wp-hub__template-live-pill--activate em-wp-templates-sommaire__activate-live '
        . $extra_class
    );

    if ($compact || $table) {
        $classes .= ' em-wp-hub__template-live-pill--compact';
    }

    if ($block) {
        $classes .= ' em-wp-hub__template-live-pill--block';
    }

    $style_attr = function_exists('em_wp_admin_template_tab_style_attr')
        ? em_wp_admin_template_tab_style_attr($slug)
        : '';
    ?>
    <button
        type="button"
        class="<?php echo esc_attr($classes); ?>"
        <?php if ($id !== '') { ?>
            id="<?php echo esc_attr($id); ?>"
        <?php } ?>
        <?php if ($style_attr !== '') { ?>
            style="<?php echo esc_attr($style_attr); ?>"
        <?php } ?>
        data-template-slug="<?php echo esc_attr($slug); ?>"
        data-template-label="<?php echo esc_attr($label_upper); ?>"
        title="<?php esc_attr_e('Activer sur le site public', 'em-wp'); ?>"
    >
        <span class="em-wp-hub__template-live-pill-name"><?php echo esc_html($label_upper); ?></span>
        <span class="em-wp-hub__template-live-pill-live em-wp-hub__template-live-pill-action">
            <?php echo esc_html(mb_strtoupper(__('Activer', 'em-wp'))); ?>
        </span>
    </button>
    <?php
}

/**
 * Badge « Activer sur le site » (carte ou tableau).
 */
function em_wp_admin_hub_render_template_activate_badge(
    string $slug,
    string $display_label,
    string $color,
    bool $compact = false,
    bool $table = false
): void {
    unset($color);

    em_wp_admin_hub_render_template_activate_pill($slug, $display_label, [
        'compact' => $compact,
        'table'   => $table,
        'block'   => !$table,
    ]);
}

/**
 * Pied de carte template : badge live ou action d’activation.
 */
function em_wp_admin_hub_render_template_card_live_footer(
    string $slug,
    string $display_label,
    string $color,
    bool $is_live,
    bool $can_manage
): void {
    if ($is_live) {
        em_wp_admin_hub_render_template_live_badge($display_label, $slug);
        return;
    }

    if (!$can_manage) {
        return;
    }

    em_wp_admin_hub_render_template_activate_badge($slug, $display_label, $color, false);
}

/**
 * Formulaire POST — bascule template live (sommaire Templates).
 */
function em_wp_admin_hub_render_template_set_live_form(string $redirect_page = ''): void
{
    if ($redirect_page === '' && function_exists('em_wp_admin_template_choice_page_slug')) {
        $redirect_page = em_wp_admin_template_choice_page_slug();
    }

    $active_slug = em_wp_get_active_template_slug();
    ?>
    <form
        id="em-wp-hub-set-live-template-form"
        class="em-wp-hub__live-switch-form"
        method="post"
        action=""
        hidden
    >
        <?php wp_nonce_field('em_wp_template_set_active'); ?>
        <input type="hidden" name="em_wp_template_action" value="set_active">
        <input type="hidden" name="em_wp_template_redirect_page" value="<?php echo esc_attr($redirect_page); ?>">
        <input type="hidden" name="em_wp_template_active_slug" value="<?php echo esc_attr($active_slug); ?>">
    </form>
    <?php
}

/**
 * Enqueue JS du switch template live (sommaire Templates).
 */
function em_wp_admin_hub_enqueue_template_live_switcher(): void
{
    wp_enqueue_script(
        'em-wp-admin-template-live-switch',
        get_template_directory_uri() . '/assets/admin/js/pages/template-live-switch.js',
        ['em-wp-admin-confirm-modal'],
        em_wp_admin_asset_version('assets/admin/js/pages/template-live-switch.js'),
        true
    );

    wp_localize_script(
        'em-wp-admin-template-live-switch',
        'emWpTemplateLiveSwitch',
        [
            'i18n' => [
                'confirm' => __('Activer le template %s sur le site public ?', 'em-wp'),
                'confirmLabel' => __('Activer', 'em-wp'),
                'cancelLabel' => __('Annuler', 'em-wp'),
            ],
        ]
    );
}

/**
 * Bandeau template actif sur le site public.
 */
function em_wp_admin_hub_render_live_template_switcher(string $redirect_page = ''): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_get_active_template_slug')) {
        return;
    }

    $registry = em_wp_template_registry();
    $active_slug = em_wp_get_active_template_slug();
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    ?>
    <div
        class="em-wp-hub__live-bar"
        data-active-slug="<?php echo esc_attr($active_slug); ?>"
    >
        <?php em_wp_admin_hub_render_live_template_badge($active_label, $active_slug, false); ?>
    </div>
    <?php
}

/**
 * Libellé court pour toggle catalogue (MAYAMI, CLIENT…).
 */
function em_wp_admin_catalog_choice_switch_label(string $catalog_slug, string $label): string
{
    $catalog_slug = sanitize_key($catalog_slug);

    foreach (['mayami', 'client'] as $token) {
        if ($catalog_slug !== '' && str_contains($catalog_slug, $token)) {
            return mb_strtoupper($token);
        }
    }

    $label = trim($label);

    return $label !== '' ? mb_strtoupper($label) : mb_strtoupper($catalog_slug);
}

/**
 * Couleur d'accent admin partagée (catalogues, toggles rubrique template…).
 */
function em_wp_admin_hub_brand_accent_color(): string
{
    return '#751820';
}

/**
 * Couleur d'accent pour un toggle catalogue (hero/slider…) en édition template.
 * Toujours la couleur catalogues — pas de couleur par entrée.
 */

