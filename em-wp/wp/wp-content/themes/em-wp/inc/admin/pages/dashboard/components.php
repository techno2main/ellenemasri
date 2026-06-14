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
 * Bouton secondaire désactivé (cartes « Nouveau … »).
 */
function em_wp_admin_dashboard_render_disabled_action(string $label): void
{
    em_wp_admin_hub_render_disabled_action($label);
}

/**
 * Pastille badge générique (template actif, modules catalogues, …).
 */
function em_wp_admin_dashboard_render_status_badge(string $text, string $color, bool $in_card = false, bool $uppercase = false): void
{
    em_wp_admin_hub_render_status_badge($text, $color, $in_card, $uppercase);
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
    em_wp_admin_dashboard_render_status_badge(
        __('HEROS, SLIDERS, VIDÉOS, STREAMS, SOCIALS.', 'em-wp'),
        '#4e080e',
        true,
        false
    );
}

/**
 * Pastille carte Médias (texte modifiable).
 */
function em_wp_admin_dashboard_render_medias_badge(): void
{
    em_wp_admin_dashboard_render_status_badge(
        __('LIBRAIRIE, AJOUTER.', 'em-wp'),
        '#4e080e',
        true,
        false
    );
}

/**
 * Pastille carte Settings (texte modifiable).
 */
function em_wp_admin_dashboard_render_settings_badge(): void
{
    em_wp_admin_dashboard_render_status_badge(
        __('APPARENCE, GÉNÉRAL.', 'em-wp'),
        '#4e080e',
        true,
        false
    );
}
