<?php
/**
 * Composants UI page Accueil (délègue aux cartes hub partagées).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL admin — ouvrir le panneau « Nouveau catalogue » sur le hub Catalogues.
 */
function em_site_admin_dashboard_new_catalog_admin_url(): string
{
    if (!function_exists('em_site_catalog_parent_page_url')) {
        return '';
    }

    return add_query_arg('em_site_open', 'catalog-create', em_site_catalog_parent_page_url());
}

/**
 * URL admin — formulaire « Nouveau template ».
 */
function em_site_admin_dashboard_new_template_admin_url(): string
{
    if (!function_exists('em_site_admin_template_create_admin_url')) {
        return '';
    }

    return em_site_admin_template_create_admin_url();
}

/**
 * Bouton + actif (cartes « Nouveau … » Accueil).
 */
function em_site_admin_dashboard_render_card_create_link(string $url, string $accessible_label): void
{
    if ($url === '') {
        em_site_admin_dashboard_render_card_disabled_gear();
        return;
    }

    em_site_admin_hub_render_action_link($url, '', 'dashicons-plus-alt2', true, $accessible_label);
}

/**
 * Icônes dashicons des boutons Accueil (alignées sur le menu admin).
 *
 * @return array<string, string>
 */
function em_site_admin_dashboard_action_icons(): array
{
    $template_icon = function_exists('em_site_site_icon') ? em_site_site_icon('template', 'dashicons-layout') : 'dashicons-layout';
    $rubriques_icon = function_exists('em_site_site_icon') ? em_site_site_icon('rubriques', 'dashicons-screenoptions') : 'dashicons-screenoptions';
    $medias_icon = function_exists('em_site_site_icon') ? em_site_site_icon('medias', 'dashicons-admin-media') : 'dashicons-admin-media';
    $settings_icon = function_exists('em_site_site_icon') ? em_site_site_icon('settings', 'dashicons-admin-settings') : 'dashicons-admin-settings';

    return [
        'templates'  => 'dashicons ' . $template_icon,
        'rubriques'  => 'dashicons ' . $rubriques_icon,
        'medias'     => 'dashicons ' . $medias_icon,
        'settings'   => 'dashicons ' . $settings_icon,
    ];
}

/**
 * Rendu du titre d'une carte Accueil (icône + libellé).
 */
function em_site_admin_dashboard_render_card_title(string $title, string $icon_key): void
{
    $icons = em_site_admin_dashboard_action_icons();
    $icon_class = (string) ($icons[$icon_key] ?? 'dashicons dashicons-admin-generic');
    em_site_admin_hub_render_card_title($title, $icon_class);
}

/**
 * Rendu d'un bouton d'action Accueil (icône + libellé).
 */
function em_site_admin_dashboard_render_action_link(string $url, string $label, string $icon_key): void
{
    $icons = em_site_admin_dashboard_action_icons();
    $icon_class = (string) ($icons[$icon_key] ?? 'dashicons dashicons-admin-generic');
    em_site_admin_hub_render_action_link($url, $label, $icon_class);
}

/**
 * Roue crantée en en-tête de carte (alignée sommaire Catalogues).
 */
function em_site_admin_dashboard_render_card_gear_link(string $url, string $accessible_label): void
{
    em_site_admin_hub_render_action_link($url, '', 'dashicons-admin-generic', true, $accessible_label);
}

/**
 * Bouton secondaire désactivé (cartes « Nouveau … »).
 */
function em_site_admin_dashboard_render_disabled_action(string $label): void
{
    em_site_admin_hub_render_disabled_action($label);
}

/**
 * Icône + désactivée en en-tête de carte (blocs « Nouveau … »).
 */
function em_site_admin_dashboard_render_card_disabled_gear(): void
{
    em_site_admin_hub_render_disabled_action('', 'dashicons dashicons-plus-alt2', true);
}

/**
 * Pastille badge générique (template actif, modules catalogues, …).
 */
function em_site_admin_dashboard_render_status_badge(string $text, string $color, bool $in_card = false, bool $uppercase = false, bool $compact = false): void
{
    em_site_admin_hub_render_status_badge($text, $color, $in_card, $uppercase, $compact);
}

/**
 * Pastille « template actif » (Accueil).
 */
function em_site_admin_dashboard_render_live_template_badge(string $active_label, string $active_slug, bool $in_card = false): void
{
    em_site_admin_hub_render_live_template_badge($active_label, $active_slug, $in_card);
}

/**
 * Badge template dédié au Dashboard (bloc Mon Template).
 */
function em_site_admin_dashboard_render_template_context_badge(string $active_label, string $active_slug): void
{
    $template_url = function_exists('em_site_admin_template_choice_admin_url')
        ? em_site_admin_template_choice_admin_url()
        : add_query_arg(['page' => 'em-template'], admin_url('admin.php'));
    $active_slug = sanitize_key($active_slug);
    $style_attr = function_exists('em_site_admin_template_tab_style_attr')
        ? em_site_admin_template_tab_style_attr($active_slug)
        : '';

    ?>
    <div class="em-site-hub__card-live-status">
        <p class="em-site-hub__card-live-status-prefix">
            <?php esc_html_e("C'est ici que tu définis le squelette de ton site", 'em-site'); ?>
        </p>
        <a
            class="em-hub__template-live-pill"
            href="<?php echo esc_url($template_url); ?>"
            aria-label="<?php esc_attr_e('Ouvrir la page Template', 'em-site'); ?>"
            <?php if ($style_attr !== '') { ?>
                style="<?php echo esc_attr($style_attr); ?>"
            <?php } ?>
        >
            <span class="em-hub__template-live-pill-name"><?php echo esc_html(mb_strtoupper(trim($active_label))); ?></span>
            <span class="em-hub__template-live-pill-live">
                <span class="em-hub__live-indicator" aria-hidden="true">
                    <span class="em-hub__live-dot"></span>
                </span>
                <?php esc_html_e('Live', 'em-site'); ?>
            </span>
        </a>
    </div>
    <?php
}

/**
 * Pastille liste des templates existants (MAYAMI, CLIENT, …).
 */
function em_site_admin_dashboard_render_templates_badge(): void
{
    $entries = [];

    if (function_exists('em_site_template_registry') && function_exists('em_site_admin_template_entry_page_slug')) {
        foreach (em_site_template_registry() as $slug => $definition) {
            $slug = (string) $slug;
            $label = (string) ($definition['label'] ?? $slug);

            $entries[] = [
                'label' => $label,
                'url'   => add_query_arg(
                    ['page' => em_site_admin_template_entry_page_slug($slug)],
                    admin_url('admin.php')
                ),
            ];
        }
    }

    $see_all_url = function_exists('em_site_admin_template_choice_admin_url')
        ? em_site_admin_template_choice_admin_url()
        : '';

    em_site_admin_hub_render_catalog_entry_links_badge(
        $entries,
        '#4e080e',
        '',
        true,
        5,
        $see_all_url,
        __('Voir tout', 'em-site'),
        false,
        true
    );
}

/**
 * URL admin du hub RUBRIQUES (EM-SITE).
 */
function em_site_admin_dashboard_rubriques_overview_url(): string
{
    return add_query_arg(['page' => 'em-rubriques-overview'], admin_url('admin.php'));
}

/**
 * Pastille des rubriques EM-SITE (TOP-BARS, HEROS, SLIDERS, …).
 *
 * Remplace l'ancienne pastille « modules catalogues » : on liste désormais les
 * types de rubriques EM-SITE (registre), chaque entrée ouvre la carte correspondante
 * du hub RUBRIQUES.
 */
function em_site_admin_dashboard_render_rubriques_badge(): void
{
    $entries = [];

    if (function_exists('em_site_ordered_types')) {
        foreach (em_site_ordered_types() as $slug => $type) {
            $slug = (string) $slug;
            $label = (string) ($type['label_plural'] ?? $type['label'] ?? $slug);

            if ($slug === '' || $label === '') {
                continue;
            }

            $entries[] = [
                'label' => $label,
                'url'   => add_query_arg(
                    ['page' => 'em-rubriques-overview', 'type' => $slug],
                    admin_url('admin.php')
                ),
            ];
        }
    }

    em_site_admin_hub_render_catalog_entry_links_badge(
        $entries,
        '#4e080e',
        '',
        false,
        5,
        em_site_admin_dashboard_rubriques_overview_url(),
        __('Voir tout', 'em-site')
    );
}

/**
 * Pastille carte Médias (liens cliquables).
 */
function em_site_admin_dashboard_render_medias_badge(): void
{
    em_site_admin_hub_render_catalog_entry_links_badge(
        [
            [
                'label' => __('LIBRAIRIE', 'em-site'),
                'url'   => admin_url('upload.php'),
            ],
            [
                'label' => __('AJOUTER', 'em-site'),
                'url'   => admin_url('media-new.php'),
            ],
        ],
        '#4e080e'
    );
}

/**
 * Pastille carte Settings (liens cliquables).
 */
function em_site_admin_dashboard_render_settings_badge(): void
{
    $entries = [
        [
            'label' => __('ICÔNES BO', 'em-site'),
            'url'   => function_exists('em_site_admin_dashicons_manager_admin_url')
                ? em_site_admin_dashicons_manager_admin_url()
                : admin_url('admin.php?page=' . em_site_admin_dashicons_manager_page_slug()),
        ],
        [
            'label' => __('APPARENCE', 'em-site'),
            'url'   => admin_url('themes.php'),
        ],
        [
            'label' => __('GÉNÉRAL', 'em-site'),
            'url'   => admin_url('options-general.php'),
        ],
    ];

    if (function_exists('em_site_admin_is_power_user')
        && function_exists('em_site_client_admin_gate_settings_admin_url')
        && em_site_admin_is_power_user()) {
        $entries[] = [
            'label' => __('VERROU', 'em-site'),
            'url'   => em_site_client_admin_gate_settings_admin_url(),
        ];
    }

    em_site_admin_hub_render_catalog_entry_links_badge(
        $entries,
        '#4e080e'
    );

    if (function_exists('em_site_admin_is_power_user') && em_site_admin_is_power_user()) {
        em_site_admin_dashboard_render_vlb_ellene_visibility_toggle();
    }
}

/**
 * Mini toggle VLB pour admin-tyson : état admin-ellene (Affiché/Masqué).
 */
function em_site_admin_dashboard_render_vlb_ellene_visibility_toggle(): void
{
    if (!function_exists('em_site_vlb_toggle_for_admin_ellene_url') || !function_exists('em_site_vlb_visible_for_admin_ellene')) {
        return;
    }

    $is_visible = em_site_vlb_visible_for_admin_ellene();
    $status_label = $is_visible ? __('Affiché', 'em-site') : __('Masqué', 'em-site');
    $status_class = $is_visible ? 'is-visible' : 'is-hidden';
    $toggle_class = $is_visible ? 'is-on' : 'is-off';

    static $toggle_styles_printed = false;

    if (!$toggle_styles_printed) {
        $toggle_styles_printed = true;
        ?>
        <style id="em-site-dashboard-vlb-toggle-styles">
            .em-site-dashboard__vlb-toggle-row {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin: 8px 0 0;
                font-size: 11px;
                line-height: 1;
            }

            .em-site-dashboard__vlb-toggle-title {
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: #3c434a;
            }

            .em-site-dashboard__vlb-toggle-status {
                display: inline-flex;
                align-items: center;
                padding: 2px 7px;
                border-radius: 999px;
                font-weight: 700;
                font-size: 10px;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            .em-site-dashboard__vlb-toggle-status.is-visible {
                color: #0f5132;
                background: #d1e7dd;
                box-shadow: inset 0 0 0 1px rgba(15, 81, 50, 0.2);
            }

            .em-site-dashboard__vlb-toggle-status.is-hidden {
                color: #842029;
                background: #f8d7da;
                box-shadow: inset 0 0 0 1px rgba(132, 32, 41, 0.2);
            }

            .em-site-dashboard__vlb-toggle-switch {
                position: relative;
                width: 30px;
                height: 18px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                padding: 2px;
                text-decoration: none;
                transition: background 0.2s ease;
            }

            .em-site-dashboard__vlb-toggle-switch.is-on {
                background: #198754;
            }

            .em-site-dashboard__vlb-toggle-switch.is-off {
                background: #dc3545;
            }

            .em-site-dashboard__vlb-toggle-knob {
                width: 14px;
                height: 14px;
                border-radius: 999px;
                background: #ffffff;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
                transition: transform 0.2s ease;
            }

            .em-site-dashboard__vlb-toggle-switch.is-on .em-site-dashboard__vlb-toggle-knob {
                transform: translateX(12px);
            }

            .em-site-dashboard__vlb-toggle-switch:focus-visible {
                outline: 2px solid rgba(117, 24, 32, 0.35);
                outline-offset: 2px;
            }
        </style>
        <?php
    }
    ?>
    <p class="em-site-dashboard__vlb-toggle-row">
        <span class="em-site-dashboard__vlb-toggle-title"><?php esc_html_e('VLB', 'em-site'); ?></span>
        <span class="em-site-dashboard__vlb-toggle-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
        <a
            class="em-site-dashboard__vlb-toggle-switch <?php echo esc_attr($toggle_class); ?>"
            href="<?php echo esc_url(em_site_vlb_toggle_for_admin_ellene_url()); ?>"
            aria-label="<?php esc_attr_e('Basculer la visibilité VLB pour admin-ellene', 'em-site'); ?>"
            title="<?php esc_attr_e('Basculer la visibilité VLB pour admin-ellene', 'em-site'); ?>"
        >
            <span class="em-site-dashboard__vlb-toggle-knob" aria-hidden="true"></span>
        </a>
    </p>
    <?php
}

/**
 * Onglets Accueil (MES RUBRIQUES, MES TEMPLATES, MEDIAS, SETTINGS).
 *
 * @return array<string, array{menu_title:string, url:string}>
 */
function em_site_admin_dashboard_nav_tab_definitions(): array
{
    return [
        'rubriques' => [
            'menu_title' => __('MES RUBRIQUES', 'em-site'),
            'url'        => em_site_admin_dashboard_rubriques_overview_url(),
        ],
        'templates' => [
            'menu_title' => __('MON TEMPLATE', 'em-site'),
            'url'        => function_exists('em_site_admin_template_choice_admin_url') ? em_site_admin_template_choice_admin_url() : '',
        ],
        'medias' => [
            'menu_title' => __('MEDIAS', 'em-site'),
            'url'        => admin_url('upload.php'),
        ],
        'settings' => [
            'menu_title' => __('SETTINGS', 'em-site'),
            'url'        => (function_exists('em_site_admin_is_power_user')
                && function_exists('em_site_client_admin_gate_settings_admin_url')
                && em_site_admin_is_power_user())
                ? em_site_client_admin_gate_settings_admin_url()
                : admin_url('options-general.php'),
        ],
    ];
}

/**
 * Navbar horizontale Accueil (pastille Liste + sections).
 */
function em_site_admin_dashboard_render_nav_tabs(): void
{
    $tabs = em_site_admin_dashboard_nav_tab_definitions();
    $list_url = em_site_admin_dashboard_admin_url();

    if ($tabs === []) {
        return;
    }
    ?>
    <nav class="em-site-catalog-edit__nav em-site-dashboard-edit__nav" aria-label="<?php echo esc_attr__('Navigation Accueil', 'em-site'); ?>">
        <ul class="em-site-catalog-edit__nav-list">
            <li class="em-site-catalog-edit__nav-item is-active">
                <a
                    class="em-site-catalog-edit__nav-link em-site-catalog-edit__nav-link--list"
                    href="<?php echo esc_url($list_url); ?>"
                    aria-label="<?php echo esc_attr__('Liste', 'em-site'); ?>"
                    aria-current="page"
                >
                    <i class="fa-solid fa-list-ol em-site-catalog-edit__nav-icon" aria-hidden="true"></i>
                </a>
            </li>
            <?php foreach ($tabs as $section_slug => $definition) {
                $url = (string) ($definition['url'] ?? '');

                if ($url === '') {
                    continue;
                }

                $label = (string) ($definition['menu_title'] ?? $section_slug);
                ?>
                <li class="em-site-catalog-edit__nav-item">
                    <a
                        class="em-site-catalog-edit__nav-link"
                        href="<?php echo esc_url($url); ?>"
                        data-dashboard-section="<?php echo esc_attr((string) $section_slug); ?>"
                    >
                        <?php echo esc_html($label); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <?php

    if (function_exists('em_site_admin_hub_sticky_head_close')) {
        em_site_admin_hub_sticky_head_close();
    }
}

