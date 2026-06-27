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

    // Un sous-menu par rubrique (pas les items détaillés), dans l'ordre du site.
    // Le slug « …&type=<slug> » ouvre la carte correspondante de l'aperçu.
    // Le libellé porte une icône Dashicon (rendu HTML accepté par le menu).
    foreach (em_wp_v4_ordered_types() as $slug => $type) {
        $label = (string) ($type['label_plural'] ?? $type['label']);
        $icon = (string) ($type['icon'] ?? 'dashicons-screenoptions');
        $menu_title = '<span class="dashicons ' . esc_attr($icon) . ' em-wp-rubrique-submenu__icon" aria-hidden="true"></span>'
            . '<span class="em-wp-rubrique-submenu__text">' . esc_html($label) . '</span>';

        add_submenu_page(
            'em-wp-v4-overview',
            $label,
            $menu_title,
            'manage_options',
            'em-wp-v4-overview&type=' . $slug,
            'em_wp_v4_overview_render'
        );
    }

    // Le 1er sous-menu auto a le même slug que le parent : NE PAS le supprimer
    // (sinon « RUBRIQUES » pointerait vers le 1er type, ex. TOP-BARS). On le
    // renomme en « Vue d'ensemble » → ouvre la page sans type (toutes fermées).
    global $submenu;
    if (isset($submenu['em-wp-v4-overview'][0])) {
        $submenu['em-wp-v4-overview'][0][0] = '<span class="dashicons dashicons-screenoptions em-wp-rubrique-submenu__icon" aria-hidden="true"></span>'
            . '<span class="em-wp-rubrique-submenu__text">' . esc_html__('Vue d’ensemble', 'em-wp') . '</span>';
    }
}
add_action('admin_menu', 'em_wp_v4_overview_menu', 100);

/**
 * Types triés dans l'ordre des rubriques du site (HEADER absent du V4).
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_v4_ordered_types(): array
{
    $types = em_wp_rubrique_type_registry();

    if (!function_exists('em_wp_get_site_rubrique_order')) {
        return $types;
    }

    $ordered = [];
    foreach (em_wp_get_site_rubrique_order() as $slug) {
        if (isset($types[$slug])) {
            $ordered[$slug] = $types[$slug];
            unset($types[$slug]);
        }
    }

    return $ordered + $types;
}

/**
 * Rendu de la page.
 */
function em_wp_v4_overview_render(): void
{
    $types = em_wp_v4_ordered_types();
    // Rubrique ciblée par le sous-menu de gauche (…&type=<slug>) : on ouvre sa carte.
    $open_type = sanitize_key((string) ($_GET['type'] ?? ''));
    ?>
    <div class="wrap em-v4-overview">
        <h1><?php esc_html_e('EM-WP V4 — Rubriques', 'em-wp'); ?></h1>
        <p class="description">
            <?php esc_html_e('Par rubrique, crée tes variantes. Compose la STRUCTURE et saisis le CONTENU au même endroit : ajoute un champ dans une zone (Gauche/Centre/Droite), nomme-le, remplis-le. Aperçu temps réel. Additif, sans impact sur le front.', 'em-wp'); ?>
        </p>

        <?php em_wp_v4_overview_notice(); ?>
        <?php em_wp_v4_overview_render_styles(); ?>

        <h2><?php esc_html_e('Rubriques', 'em-wp'); ?></h2>
        <?php if ($types === []) : ?>
            <p><?php esc_html_e('Aucune rubrique déclarée pour le moment.', 'em-wp'); ?></p>
        <?php else : ?>
            <?php foreach ($types as $slug => $type) : ?>
                <?php em_wp_v4_overview_render_type((string) $slug, $type, $open_type === (string) $slug); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php if ($open_type !== '' && isset($types[$open_type])) : ?>
        <script>
        (function () {
            var el = document.getElementById('em-v4-card-<?php echo esc_js($open_type); ?>');
            if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        })();
        </script>
    <?php endif; ?>
    <?php
}

/**
 * Notice de feedback.
 */
function em_wp_v4_overview_notice(): void
{
    $updated = sanitize_key((string) ($_GET['v4_updated'] ?? ''));
    $error = sanitize_key((string) ($_GET['v4_error'] ?? ''));

    $type_slug = sanitize_key((string) ($_GET['type'] ?? ''));
    $n = em_wp_rubrique_type_nouns($type_slug !== '' && em_wp_rubrique_type_exists($type_slug) ? $type_slug : '');
    $noun = ucfirst($n['singular'] !== '' ? $n['singular'] : __('élément', 'em-wp'));
    $e = $n['e'];

    $messages = [
        'saved'      => sprintf(__('%1$s enregistré%2$s.', 'em-wp'), $noun, $e),
        'created'    => sprintf(__('%1$s créé%2$s.', 'em-wp'), $noun, $e),
        'deleted'    => sprintf(__('%1$s supprimé%2$s.', 'em-wp'), $noun, $e),
        'duplicated' => sprintf(__('%1$s dupliqué%2$s.', 'em-wp'), $noun, $e),
        'structure'  => __('Structure enregistrée.', 'em-wp'),
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
    <details class="em-v4-collapse em-v4-card" id="em-v4-card-<?php echo esc_attr($slug); ?>" <?php echo $open ? 'open' : ''; ?>>
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
