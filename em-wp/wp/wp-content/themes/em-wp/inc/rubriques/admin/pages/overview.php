<?php
/**
 * Page Rubriques V4 (admin) — modèle simplifié.
 *
 * Par rubrique : la liste des footers (items). Chaque footer s'édite en une
 * seule étape (structure + contenu + couleurs + aperçu temps réel) via le
 * builder. Plus de notion de « modèle ». Additif, sans impact sur le front.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre la page (menu top-level dédié).
 */
function em_wp_v4_overview_menu(): void
{
    // Placé sous le bloc « Rubriques du site » (après son séparateur bas) pour
    // ne pas se mélanger aux modules de rubriques (qui occupent la plage 55→62).
    $position = 64;
    if (function_exists('em_wp_admin_menu_separator_bottom_position')) {
        $position = em_wp_admin_menu_separator_bottom_position() + 1;
    }

    add_menu_page(
        __('RUBRIQUES', 'em-wp'),
        __('RUBRIQUES', 'em-wp'),
        'manage_options',
        'em-wp-v4-overview',
        'em_wp_v4_overview_render',
        'dashicons-screenoptions',
        $position
    );
}
add_action('admin_menu', 'em_wp_v4_overview_menu', 100);

/**
 * Rendu de la page.
 */
function em_wp_v4_overview_render(): void
{
    $types = em_wp_rubrique_type_registry();
    ?>
    <div class="wrap em-v4-overview">
        <h1><?php esc_html_e('EM-WP V4 — Rubriques', 'em-wp'); ?></h1>
        <p class="description">
            <?php esc_html_e('Par rubrique, créez des footers. Composez la STRUCTURE et saisissez le CONTENU au même endroit : ajoutez un champ dans une zone (Gauche/Centre/Droite), nommez-le, remplissez-le. Aperçu temps réel. Additif, sans impact sur le front.', 'em-wp'); ?>
        </p>

        <?php em_wp_v4_overview_notice(); ?>
        <?php em_wp_v4_overview_render_styles(); ?>

        <h2><?php esc_html_e('Rubriques', 'em-wp'); ?></h2>
        <?php if ($types === []) : ?>
            <p><?php esc_html_e('Aucune rubrique déclarée pour le moment.', 'em-wp'); ?></p>
        <?php else : ?>
            <?php $open_type = sanitize_key((string) ($_GET['type'] ?? '')); ?>
            <?php foreach ($types as $slug => $type) : ?>
                <?php em_wp_v4_overview_render_type((string) $slug, $type, $open_type === $slug); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Notice de feedback.
 */
function em_wp_v4_overview_notice(): void
{
    $updated = sanitize_key((string) ($_GET['v4_updated'] ?? ''));
    $error = sanitize_key((string) ($_GET['v4_error'] ?? ''));

    $messages = [
        'saved'      => __('Footer enregistré.', 'em-wp'),
        'created'    => __('Footer créé.', 'em-wp'),
        'deleted'    => __('Footer supprimé.', 'em-wp'),
        'duplicated' => __('Footer dupliqué.', 'em-wp'),
        'structure' => __('Structure enregistrée.', 'em-wp'),
    ];

    if (isset($messages[$updated])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$updated]) . '</p></div>';
    } elseif ($error !== '') {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Action impossible (données invalides).', 'em-wp') . '</p></div>';
    }
}

/**
 * Carte repliable d'une rubrique : ses footers.
 *
 * @param array<string, mixed> $type
 */
function em_wp_v4_overview_render_type(string $slug, array $type, bool $open): void
{
    ?>
    <details class="em-v4-collapse em-v4-card" <?php echo $open ? 'open' : ''; ?>>
        <summary class="em-v4-collapse__summary em-v4-card__head">
            <span class="em-v4-collapse__chevron" aria-hidden="true"></span>
            <span class="dashicons <?php echo esc_attr((string) ($type['icon'] ?? 'dashicons-screenoptions')); ?>"></span>
            <strong><?php echo esc_html((string) ($type['label_plural'] ?? $type['label'])); ?></strong>
        </summary>
        <div class="em-v4-collapse__body">
            <?php em_wp_v4_render_items_section($slug); ?>
        </div>
    </details>
    <?php
}

/**
 * Styles inline (autonome).
 */
function em_wp_v4_overview_render_styles(): void
{
    require __DIR__ . '/overview-styles.php';
}
