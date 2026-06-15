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
 * Icônes dashicons des boutons Accueil (alignées sur le menu admin).
 *
 * @return array<string, string>
 */
function em_wp_admin_dashboard_action_icons(): array
{
    return [
        'templates'  => 'dashicons dashicons-layout',
        'catalogues' => 'dashicons dashicons-index-card',
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
function em_wp_admin_dashboard_render_live_template_badge(string $active_label, string $active_color, bool $in_card = false): void
{
    em_wp_admin_hub_render_live_template_badge($active_label, $active_color, $in_card);
}

/**
 * Pastille modules catalogues (HEROS, SLIDERS, …).
 */
function em_wp_admin_dashboard_render_catalog_modules_badge(): void
{
    $entries = [];
    $module_slugs = ['heros', 'sliders', 'videos', 'streams', 'socials'];

    if (function_exists('em_wp_catalog_menu_definitions')) {
        $definitions = em_wp_catalog_menu_definitions();

        foreach ($module_slugs as $module_slug) {
            $definition = $definitions[$module_slug] ?? null;

            if (!is_array($definition) || empty($definition['available'])) {
                continue;
            }

            $url = trim((string) ($definition['url'] ?? ''));

            if ($url === '') {
                continue;
            }

            $entries[] = [
                'label' => (string) ($definition['label'] ?? $module_slug),
                'url'   => $url,
            ];
        }
    }

    em_wp_admin_hub_render_catalog_entry_links_badge($entries, '#4e080e');
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
    em_wp_admin_hub_render_catalog_entry_links_badge(
        [
            [
                'label' => __('APPARENCE', 'em-wp'),
                'url'   => admin_url('themes.php'),
            ],
            [
                'label' => __('GÉNÉRAL', 'em-wp'),
                'url'   => admin_url('options-general.php'),
            ],
        ],
        '#4e080e'
    );
}

/**
 * Onglets Accueil (CATALOGUES, TEMPLATES, MEDIAS, SETTINGS).
 *
 * @return array<string, array{menu_title:string, url:string}>
 */
function em_wp_admin_dashboard_nav_tab_definitions(): array
{
    return [
        'catalogues' => [
            'menu_title' => __('CATALOGUES', 'em-wp'),
            'url'        => function_exists('em_wp_catalog_parent_page_url') ? em_wp_catalog_parent_page_url() : '',
        ],
        'templates' => [
            'menu_title' => __('TEMPLATES', 'em-wp'),
            'url'        => function_exists('em_wp_admin_template_choice_admin_url') ? em_wp_admin_template_choice_admin_url() : '',
        ],
        'medias' => [
            'menu_title' => __('MEDIAS', 'em-wp'),
            'url'        => admin_url('upload.php'),
        ],
        'settings' => [
            'menu_title' => __('SETTINGS', 'em-wp'),
            'url'        => admin_url('options-general.php'),
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
