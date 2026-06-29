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
 * Assets de la page Rubriques V4 (inclut le header admin partage).
 */
function em_wp_v4_overview_enqueue_assets(string $hook_suffix): void
{
    unset($hook_suffix);
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page = sanitize_key((string) ($_GET['page'] ?? ''));
    if ($page !== 'em-wp-v4-overview') {
        return;
    }

    if (function_exists('em_wp_admin_hub_cards_enqueue_assets')) {
        em_wp_admin_hub_cards_enqueue_assets();
        return;
    }

    if (function_exists('em_wp_admin_enqueue_shared_assets')) {
        em_wp_admin_enqueue_shared_assets();
    }
}
add_action('admin_enqueue_scripts', 'em_wp_v4_overview_enqueue_assets');

/**
 * Types triés dans l'ordre des rubriques du site (HEADER absent du V4).
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_v4_ordered_types(): array
{
    $types = em_wp_rubrique_type_registry();
    $ordered = [];

    // 1) Ordre personnalisé enregistré (glisser-déposer de l'aperçu) — prioritaire.
    foreach (em_wp_v4_get_rubrique_order() as $slug) {
        if (isset($types[$slug])) {
            $ordered[$slug] = $types[$slug];
            unset($types[$slug]);
        }
    }

    // 2) Repli : ordre des rubriques du site pour les non classées.
    if (function_exists('em_wp_get_site_rubrique_order')) {
        foreach (em_wp_get_site_rubrique_order() as $slug) {
            if (isset($types[$slug])) {
                $ordered[$slug] = $types[$slug];
                unset($types[$slug]);
            }
        }
    }

    // 3) Reste éventuel (types personnalisés non classés) en fin.
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
    $breadcrumb = [];
    if (function_exists('em_wp_admin_hub_breadcrumb_crumb')) {
        $breadcrumb[] = em_wp_admin_hub_breadcrumb_crumb(__('Mes Rubriques', 'em-wp'));
    }
    ?>
    <div class="wrap em-v4-overview em-wp-admin-module em-wp-hub-sommaire">
        <?php
        if (function_exists('em_wp_admin_hub_render_sommaire_header')) {
            em_wp_admin_hub_render_sommaire_header('', 'dashicons-screenoptions', false, true, null, $breadcrumb, false);
        }
        ?>

        <?php em_wp_v4_overview_notice(); ?>
        <?php em_wp_v4_overview_render_styles(); ?>

        <?php em_wp_v4_overview_render_create_type(); ?>

        <?php if ($types === []) : ?>
            <p><?php esc_html_e('Aucune rubrique déclarée pour le moment.', 'em-wp'); ?></p>
        <?php else : ?>
            <div class="em-v4-cards" id="em-v4-cards">
                <?php foreach ($types as $slug => $type) : ?>
                    <?php em_wp_v4_overview_render_type((string) $slug, $type, $open_type === (string) $slug); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php em_wp_v4_overview_render_reorder_script(); ?>
    <?php em_wp_v4_overview_render_rename_script(); ?>
    <script>
    (function () {
        // Accordéon : une seule rubrique ouverte à la fois (focus sur l'édition).
        var cards = document.querySelectorAll('.em-v4-card');
        cards.forEach(function (card) {
            card.addEventListener('toggle', function () {
                if (!card.open) { return; }
                cards.forEach(function (other) {
                    if (other !== card && other.open) { other.open = false; }
                });
            });
        });
        // Accordéon : une seule section (item) ouverte à la fois, par rubrique.
        document.querySelectorAll('.em-v4-items').forEach(function (group) {
            var items = group.querySelectorAll(':scope > .em-v4-item');
            items.forEach(function (item) {
                item.addEventListener('toggle', function () {
                    if (!item.open) { return; }
                    items.forEach(function (other) {
                        if (other !== item && other.open) { other.open = false; }
                    });
                });
            });
        });
    })();
    </script>
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
        'type_created' => __('Rubrique créée.', 'em-wp'),
    ];

    if (isset($messages[$updated])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$updated]) . '</p></div>';
    } elseif ($error === 'type_exists') {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Cette rubrique existe déjà.', 'em-wp') . '</p></div>';
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
    $count = count(em_wp_v4_get_items($slug));
    $label = (string) ($type['label_plural'] ?? $type['label']);
    ?>
    <details class="em-v4-collapse em-v4-card" id="em-v4-card-<?php echo esc_attr($slug); ?>" data-slug="<?php echo esc_attr($slug); ?>" <?php echo $open ? 'open' : ''; ?>>
        <summary class="em-v4-collapse__summary em-v4-card__head">
            <span class="em-v4-card__drag dashicons dashicons-menu" title="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>" aria-hidden="true"></span>
            <span class="em-v4-collapse__chevron" aria-hidden="true"></span>
            <span class="em-v4-card__icon dashicons <?php echo esc_attr((string) ($type['icon'] ?? 'dashicons-screenoptions')); ?>"></span>
            <strong class="em-v4-card__name"><?php echo esc_html($label); ?></strong>
            <input type="text" class="em-v4-card__nameinput" data-slug="<?php echo esc_attr($slug); ?>" data-original="<?php echo esc_attr($label); ?>" value="<?php echo esc_attr($label); ?>" hidden>
            <button type="button" class="em-v4-card__edit" title="<?php esc_attr_e('Renommer la rubrique', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Renommer la rubrique', 'em-wp'); ?>">
                <span class="dashicons dashicons-edit" aria-hidden="true"></span>
            </button>
            <button type="button" class="em-v4-card__confirm" title="<?php esc_attr_e('Valider', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Valider', 'em-wp'); ?>" hidden>
                <span class="dashicons dashicons-yes" aria-hidden="true"></span>
            </button>
            <button type="button" class="em-v4-card__cancel" title="<?php esc_attr_e('Annuler', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Annuler', 'em-wp'); ?>" hidden>
                <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
            </button>
            <span class="em-v4-card__count" title="<?php echo esc_attr(sprintf(_n('%d section', '%d sections', $count, 'em-wp'), $count)); ?>"><?php echo esc_html((string) $count); ?></span>
        </summary>
        <div class="em-v4-collapse__body">
            <?php em_wp_v4_render_items_section($slug); ?>
        </div>
    </details>
    <?php
}

/**
 * Bouton/formulaire « + Nouvelle Rubrique » (fin de liste).
 *
 * Permet de créer une rubrique personnalisée (nom + icône) sans code. La
 * rubrique démarre vide (apparence par défaut) et apparaît dans la liste.
 */
function em_wp_v4_overview_render_create_type(): void
{
    $icons = [
        'dashicons-screenoptions', 'dashicons-menu-alt3', 'dashicons-format-audio',
        'dashicons-share', 'dashicons-video-alt3', 'dashicons-album', 'dashicons-megaphone',
        'dashicons-star-filled', 'dashicons-heart', 'dashicons-images-alt2',
        'dashicons-list-view', 'dashicons-admin-links',
    ];
    ?>
    <details class="em-v4-collapse em-v4-create em-v4-create--nochevron em-v4-createtype">
        <summary class="em-v4-collapse__summary">
            <span class="dashicons dashicons-plus-alt2"></span>
            <strong><?php esc_html_e('Nouvelle Rubrique', 'em-wp'); ?></strong>
        </summary>
        <div class="em-v4-collapse__body em-v4-create__options">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-v4-form em-v4-create__row">
                <?php wp_nonce_field('em_wp_v4_create_type'); ?>
                <input type="hidden" name="action" value="em_wp_v4_create_type">
                <span class="em-v4-create__label"><span class="dashicons dashicons-screenoptions" aria-hidden="true"></span> <?php esc_html_e('Nom de la rubrique', 'em-wp'); ?></span>
                <input type="text" name="type_label" class="regular-text em-v4-create__name" placeholder="<?php esc_attr_e('Ex. PARTENAIRES', 'em-wp'); ?>" required>
                <span class="em-v4-iconpick" role="radiogroup" aria-label="<?php esc_attr_e('Icône de la rubrique', 'em-wp'); ?>">
                    <?php foreach ($icons as $i => $ic) : ?>
                        <label class="em-v4-iconpick__opt" title="<?php echo esc_attr($ic); ?>">
                            <input type="radio" name="type_icon" value="<?php echo esc_attr($ic); ?>" <?php checked($i, 0); ?>>
                            <span class="dashicons <?php echo esc_attr($ic); ?>" aria-hidden="true"></span>
                        </label>
                    <?php endforeach; ?>
                </span>
                <button type="submit" class="button button-primary"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Créer la rubrique', 'em-wp'); ?></button>
            </form>
        </div>
    </details>
    <?php
}

/**
 * Styles inline (autonome).
 */
function em_wp_v4_overview_render_styles(): void
{
    // Styles globaux : une seule émission par requête (plusieurs contextes les
    // demandent sur la page squelette : aperçu complet, instance-picker, header).
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    // Couche de base mutualisée des champs/contrôles (tokens + reset WP), puis
    // les styles spécifiques de la page (qui peuvent surcharger ponctuellement).
    require __DIR__ . '/overview-fields.php';
    require __DIR__ . '/overview-styles.php';
}

/**
 * CSS de RENDU front V4 (.em-rubrique…) lu depuis les fichiers du front, pour
 * que TOUS les aperçus admin (builder, squelette, instance-picker, header)
 * partagent la MÊME source de style que le site → aucun écart back/front.
 *
 * @return string CSS concaténé (sans balise <style>).
 */
function em_wp_v4_front_render_css(): string
{
    static $css = null;

    if ($css !== null) {
        return $css;
    }

    $css = '';
    $base = get_template_directory() . '/assets/front/css/rubriques-v4/';
    foreach (['render.css', 'media.css', 'components.css', 'header.css'] as $file) {
        $path = $base . $file;
        if (is_readable($path)) {
            $css .= (string) file_get_contents($path) . "\n";
        }
    }

    return $css;
}
