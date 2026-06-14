<?php
/**
 * Composants UI page Accueil (cartes, boutons, badges).
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
    ?>
    <h2 class="em-wp-dashboard__card-title">
        <span class="<?php echo esc_attr($icon_class); ?> em-wp-dashboard__card-title-icon" aria-hidden="true"></span>
        <span class="em-wp-dashboard__card-title-label"><?php echo esc_html($title); ?></span>
    </h2>
    <?php
}

/**
 * Rendu d'un bouton d'action Accueil (icône + libellé).
 */
function em_wp_admin_dashboard_render_action_link(string $url, string $label, string $icon_key): void
{
    $icons = em_wp_admin_dashboard_action_icons();
    $icon_class = (string) ($icons[$icon_key] ?? 'dashicons dashicons-admin-generic');
    ?>
    <a class="em-wp-dashboard__action" href="<?php echo esc_url($url); ?>">
        <span class="em-wp-dashboard__action-inner">
            <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
            <span class="em-wp-dashboard__action-label"><?php echo esc_html($label); ?></span>
        </span>
    </a>
    <?php
}

/**
 * Bouton secondaire désactivé (cartes « Nouveau … »).
 */
function em_wp_admin_dashboard_render_disabled_action(string $label): void
{
    ?>
    <button type="button" class="em-wp-dashboard__action em-wp-dashboard__action--secondary" disabled title="<?php esc_attr_e('Prochaine étape', 'em-wp'); ?>">
        <span class="em-wp-dashboard__action-inner">
            <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
            <span class="em-wp-dashboard__action-label"><?php echo esc_html($label); ?></span>
        </span>
    </button>
    <?php
}

/**
 * Pastille badge générique (template actif, modules catalogues, …).
 */
function em_wp_admin_dashboard_render_status_badge(string $text, string $color, bool $in_card = false, bool $uppercase = false): void
{
    $classes = 'em-wp-dashboard__live';

    if ($uppercase) {
        $classes .= ' em-wp-dashboard__live--uppercase';
    }

    if ($in_card) {
        $classes .= ' em-wp-dashboard__live--in-card';
    }
    ?>
    <p
        class="<?php echo esc_attr($classes); ?>"
        <?php echo $in_card ? 'role="status"' : ''; ?>
        style="--em-wp-live-color: <?php echo esc_attr($color); ?>;"
    >
        <span class="em-wp-dashboard__live-indicator" aria-hidden="true">
            <span class="em-wp-dashboard__live-dot"></span>
        </span>
        <span class="em-wp-dashboard__live-text">
            <strong class="em-wp-dashboard__live-template"><?php echo esc_html($text); ?></strong>
        </span>
    </p>
    <?php
}

/**
 * Pastille « template actif » (Accueil).
 */
function em_wp_admin_dashboard_render_live_template_badge(string $active_label, string $active_color, bool $in_card = false): void
{
    $classes = 'em-wp-dashboard__live em-wp-dashboard__live--uppercase';

    if ($in_card) {
        $classes .= ' em-wp-dashboard__live--in-card';
    }
    ?>
    <p
        class="<?php echo esc_attr($classes); ?>"
        role="status"
        style="--em-wp-live-color: <?php echo esc_attr($active_color); ?>;"
    >
        <span class="em-wp-dashboard__live-indicator" aria-hidden="true">
            <span class="em-wp-dashboard__live-dot"></span>
        </span>
        <span class="em-wp-dashboard__live-text">
            <?php esc_html_e('Ton site utilise actuellement le template :', 'em-wp'); ?>
            <strong class="em-wp-dashboard__live-template"><?php echo esc_html($active_label); ?></strong>
        </span>
    </p>
    <?php
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
        __('APPARENCE, PLUGINS, GÉNÉRAL.', 'em-wp'),
        '#4e080e',
        true,
        false
    );
}
