<?php
/**
 * Composants UI page Accueil (délègue aux cartes hub partagées).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL admin — ouvrir le panneau « Nouveau catalogue » sur le hub Catalogues.
 */
function em_wp_admin_dashboard_new_catalog_admin_url(): string
{
    if (!function_exists('em_wp_catalog_parent_page_url')) {
        return '';
    }

    return add_query_arg('em_wp_open', 'catalog-create', em_wp_catalog_parent_page_url());
}

/**
 * URL admin — formulaire « Nouveau template ».
 */
function em_wp_admin_dashboard_new_template_admin_url(): string
{
    if (!function_exists('em_wp_admin_template_create_admin_url')) {
        return '';
    }

    return em_wp_admin_template_create_admin_url();
}

/**
 * Bouton + actif (cartes « Nouveau … » Accueil).
 */
function em_wp_admin_dashboard_render_card_create_link(string $url, string $accessible_label): void
{
    if ($url === '') {
        em_wp_admin_dashboard_render_card_disabled_gear();
        return;
    }

    em_wp_admin_hub_render_action_link($url, '', 'dashicons-plus-alt2', true, $accessible_label);
}

/**
 * Icônes dashicons des boutons Accueil (alignées sur le menu admin).
 *
 * @return array<string, string>
 */
function em_wp_admin_dashboard_action_icons(): array
{
    return [
        'templates'  => 'dashicons dashicons-layout',
        'catalogues' => 'dashicons dashicons-index-card',
        'rubriques'  => 'dashicons dashicons-screenoptions',
        'medias'     => 'dashicons dashicons-admin-media',
        'settings'   => 'dashicons dashicons-admin-settings',
    ];
}

/**
 * Rendu du titre d'une carte Accueil (icône + libellé).
 */
function em_wp_admin_dashboard_render_card_title(string $title, string $icon_key): void
{
    $icons = em_wp_admin_dashboard_action_icons();
    $icon_class = (string) ($icons[$icon_key] ?? 'dashicons dashicons-admin-generic');
    em_wp_admin_hub_render_card_title($title, $icon_class);
}

/**
 * Rendu d'un bouton d'action Accueil (icône + libellé).
 */
function em_wp_admin_dashboard_render_action_link(string $url, string $label, string $icon_key): void
{
    $icons = em_wp_admin_dashboard_action_icons();
    $icon_class = (string) ($icons[$icon_key] ?? 'dashicons dashicons-admin-generic');
    em_wp_admin_hub_render_action_link($url, $label, $icon_class);
}

/**
 * Roue crantée en en-tête de carte (alignée sommaire Catalogues).
 */
function em_wp_admin_dashboard_render_card_gear_link(string $url, string $accessible_label): void
{
    em_wp_admin_hub_render_action_link($url, '', 'dashicons-admin-generic', true, $accessible_label);
}

/**
 * Bouton secondaire désactivé (cartes « Nouveau … »).
 */
function em_wp_admin_dashboard_render_disabled_action(string $label): void
{
    em_wp_admin_hub_render_disabled_action($label);
}

/**
 * Icône + désactivée en en-tête de carte (blocs « Nouveau … »).
 */
function em_wp_admin_dashboard_render_card_disabled_gear(): void
{
    em_wp_admin_hub_render_disabled_action('', 'dashicons dashicons-plus-alt2', true);
}

/**
 * Pastille badge générique (template actif, modules catalogues, …).
 */
function em_wp_admin_dashboard_render_status_badge(string $text, string $color, bool $in_card = false, bool $uppercase = false, bool $compact = false): void
{
    em_wp_admin_hub_render_status_badge($text, $color, $in_card, $uppercase, $compact);
}

/**
 * Pastille « template actif » (Accueil).
 */
function em_wp_admin_dashboard_render_live_template_badge(string $active_label, string $active_slug, bool $in_card = false): void
{
    em_wp_admin_hub_render_live_template_badge($active_label, $active_slug, $in_card);
}

/**
 * Pastille liste des templates existants (MAYAMI, CLIENT, …).
 */
function em_wp_admin_dashboard_render_templates_badge(): void
{
    $entries = [];

    if (function_exists('em_wp_template_registry') && function_exists('em_wp_admin_template_entry_page_slug')) {
        foreach (em_wp_template_registry() as $slug => $definition) {
            $slug = (string) $slug;
            $label = (string) ($definition['label'] ?? $slug);

            $entries[] = [
                'label' => $label,
                'url'   => add_query_arg(
                    ['page' => em_wp_admin_template_entry_page_slug($slug)],
                    admin_url('admin.php')
                ),
            ];
        }
    }

    $see_all_url = function_exists('em_wp_admin_template_choice_admin_url')
        ? em_wp_admin_template_choice_admin_url()
        : '';

    em_wp_admin_hub_render_catalog_entry_links_badge(
        $entries,
        '#4e080e',
        '',
        true,
        5,
        $see_all_url,
        __('Voir tout', 'em-wp'),
        false,
        true
    );
}

/**
 * URL admin du hub RUBRIQUES (V4).
 */
function em_wp_admin_dashboard_rubriques_overview_url(): string
{
    return add_query_arg(['page' => 'em-rubriques-overview'], admin_url('admin.php'));
}

/**
 * Pastille des rubriques V4 (TOP-BARS, HEROS, SLIDERS, …).
 *
 * Remplace l'ancienne pastille « modules catalogues » : on liste désormais les
 * types de rubriques V4 (registre), chaque entrée ouvre la carte correspondante
 * du hub RUBRIQUES.
 */
function em_wp_admin_dashboard_render_rubriques_badge(): void
{
    $entries = [];

    if (function_exists('em_wp_v4_ordered_types')) {
        foreach (em_wp_v4_ordered_types() as $slug => $type) {
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

    em_wp_admin_hub_render_catalog_entry_links_badge(
        $entries,
        '#4e080e',
        '',
        false,
        5,
        em_wp_admin_dashboard_rubriques_overview_url(),
        __('Voir tout', 'em-wp')
    );
}

/**
 * Pastille carte Médias (liens cliquables).
 */
function em_wp_admin_dashboard_render_medias_badge(): void
{
    em_wp_admin_hub_render_catalog_entry_links_badge(
        [
            [
                'label' => __('LIBRAIRIE', 'em-wp'),
                'url'   => admin_url('upload.php'),
            ],
            [
                'label' => __('AJOUTER', 'em-wp'),
                'url'   => admin_url('media-new.php'),
            ],
        ],
        '#4e080e'
    );
}

/**
 * Pastille carte Settings (liens cliquables).
 */
function em_wp_admin_dashboard_render_settings_badge(): void
{
    $entries = [
        [
            'label' => __('APPARENCE', 'em-wp'),
            'url'   => admin_url('themes.php'),
        ],
        [
            'label' => __('GÉNÉRAL', 'em-wp'),
            'url'   => admin_url('options-general.php'),
        ],
    ];

    if (function_exists('em_wp_admin_is_power_user')
        && function_exists('em_wp_client_admin_gate_settings_admin_url')
        && em_wp_admin_is_power_user()) {
        $entries[] = [
            'label' => __('VERROU CLIENT', 'em-wp'),
            'url'   => em_wp_client_admin_gate_settings_admin_url(),
        ];
    }

    em_wp_admin_hub_render_catalog_entry_links_badge(
        $entries,
        '#4e080e'
    );
}

/**
 * Onglets Accueil (MES RUBRIQUES, MES TEMPLATES, MEDIAS, SETTINGS).
 *
 * @return array<string, array{menu_title:string, url:string}>
 */
function em_wp_admin_dashboard_nav_tab_definitions(): array
{
    return [
        'rubriques' => [
            'menu_title' => __('MES RUBRIQUES', 'em-wp'),
            'url'        => em_wp_admin_dashboard_rubriques_overview_url(),
        ],
        'templates' => [
            'menu_title' => __('MES TEMPLATES', 'em-wp'),
            'url'        => function_exists('em_wp_admin_template_choice_admin_url') ? em_wp_admin_template_choice_admin_url() : '',
        ],
        'medias' => [
            'menu_title' => __('MEDIAS', 'em-wp'),
            'url'        => admin_url('upload.php'),
        ],
        'settings' => [
            'menu_title' => __('SETTINGS', 'em-wp'),
            'url'        => (function_exists('em_wp_admin_is_power_user')
                && function_exists('em_wp_client_admin_gate_settings_admin_url')
                && em_wp_admin_is_power_user())
                ? em_wp_client_admin_gate_settings_admin_url()
                : admin_url('options-general.php'),
        ],
    ];
}

/**
 * Navbar horizontale Accueil (pastille Liste + sections).
 */
function em_wp_admin_dashboard_render_nav_tabs(): void
{
    $tabs = em_wp_admin_dashboard_nav_tab_definitions();
    $list_url = em_wp_admin_dashboard_admin_url();

    if ($tabs === []) {
        return;
    }
    ?>
    <nav class="em-wp-catalog-edit__nav em-wp-dashboard-edit__nav" aria-label="<?php echo esc_attr__('Navigation Accueil', 'em-wp'); ?>">
        <ul class="em-wp-catalog-edit__nav-list">
            <li class="em-wp-catalog-edit__nav-item is-active">
                <a
                    class="em-wp-catalog-edit__nav-link em-wp-catalog-edit__nav-link--list"
                    href="<?php echo esc_url($list_url); ?>"
                    aria-label="<?php echo esc_attr__('Liste', 'em-wp'); ?>"
                    aria-current="page"
                >
                    <i class="fa-solid fa-list-ol em-wp-catalog-edit__nav-icon" aria-hidden="true"></i>
                </a>
            </li>
            <?php foreach ($tabs as $section_slug => $definition) {
                $url = (string) ($definition['url'] ?? '');

                if ($url === '') {
                    continue;
                }

                $label = (string) ($definition['menu_title'] ?? $section_slug);
                ?>
                <li class="em-wp-catalog-edit__nav-item">
                    <a
                        class="em-wp-catalog-edit__nav-link"
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

    if (function_exists('em_wp_admin_hub_sticky_head_close')) {
        em_wp_admin_hub_sticky_head_close();
    }
}

