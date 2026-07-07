<?php
/**
 * Page Rubriques EM-SITE (admin) — modèle simplifié.
 *
 * Par rubrique : la liste des footers (items). Chaque footer s'édite en une
 * seule étape (structure + contenu + couleurs + aperçu temps réel) via le
 * builder. Plus de notion de « modèle ». Additif, sans impact sur le front.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre la page (menu top-level dédié).
 */
function em_site_overview_menu(): void
{
    // Placé sous le bloc « Rubriques du site » (après son séparateur bas) pour
    // ne pas se mélanger aux modules de rubriques (qui occupent la plage 55→62).
    $position = 64;
    if (function_exists('em_site_admin_menu_separator_bottom_position')) {
        $position = em_site_admin_menu_separator_bottom_position() + 1;
    }

    add_menu_page(
        __('RUBRIQUES', 'em-site'),
        __('RUBRIQUES', 'em-site'),
        'manage_options',
        'em-rubriques-overview',
        'em_site_overview_render',
        'dashicons-screenoptions',
        $position
    );

    // Un sous-menu par rubrique (pas les items détaillés), dans l'ordre du site.
    // Le slug « …&type=<slug> » ouvre la carte correspondante de l'aperçu.
    // Le libellé porte une icône Dashicon (rendu HTML accepté par le menu).
    foreach (em_site_ordered_types() as $slug => $type) {
        $label = (string) ($type['label_plural'] ?? $type['label']);
        $icon = (string) ($type['icon'] ?? 'dashicons-screenoptions');
        $menu_title = '<span class="dashicons ' . esc_attr($icon) . ' em-site-rubrique-submenu__icon" aria-hidden="true"></span>'
            . '<span class="em-site-rubrique-submenu__text">' . esc_html($label) . '</span>';

        add_submenu_page(
            'em-rubriques-overview',
            $label,
            $menu_title,
            'manage_options',
            'em-rubriques-overview&type=' . $slug,
            'em_site_overview_render'
        );
    }

    // Le 1er sous-menu auto a le même slug que le parent : NE PAS le supprimer
    // (sinon « RUBRIQUES » pointerait vers le 1er type, ex. TOP-BARS). On le
    // renomme en « Vue d'ensemble » → ouvre la page sans type (toutes fermées).
    global $submenu;
    if (isset($submenu['em-rubriques-overview'][0])) {
        $submenu['em-rubriques-overview'][0][0] = '<span class="dashicons dashicons-screenoptions em-site-rubrique-submenu__icon" aria-hidden="true"></span>'
            . '<span class="em-site-rubrique-submenu__text">' . esc_html__('Vue d’ensemble', 'em-site') . '</span>';
    }
}
add_action('admin_menu', 'em_site_overview_menu', 100);

/**
 * Assets de la page Rubriques EM-SITE (inclut le header admin partage).
 */
function em_site_overview_enqueue_assets(string $hook_suffix): void
{
    unset($hook_suffix);
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page = sanitize_key((string) ($_GET['page'] ?? ''));
    if ($page !== 'em-rubriques-overview') {
        return;
    }

    if (function_exists('em_site_admin_hub_cards_enqueue_assets')) {
        em_site_admin_hub_cards_enqueue_assets();
        return;
    }

    if (function_exists('em_site_admin_enqueue_shared_assets')) {
        em_site_admin_enqueue_shared_assets();
    }
}
add_action('admin_enqueue_scripts', 'em_site_overview_enqueue_assets');

/**
 * Types triés dans l'ordre des rubriques du site (HEADER absent du EM-SITE).
 *
 * @return array<string, array<string, mixed>>
 */
function em_site_ordered_types(): array
{
    $types = em_site_rubrique_type_registry();
    $ordered = [];

    // Priorité UX: TOP-BAR puis HEADER avant HERO/SLIDERS.
    foreach (['top-bar', 'headers', 'header', 'sliders'] as $priority_slug) {
        if (isset($types[$priority_slug])) {
            $ordered[$priority_slug] = $types[$priority_slug];
            unset($types[$priority_slug]);
        }
    }

    // 1) Ordre personnalisé enregistré (glisser-déposer de l'aperçu) — prioritaire.
    foreach (em_site_get_rubrique_order() as $slug) {
        if (isset($types[$slug])) {
            $ordered[$slug] = $types[$slug];
            unset($types[$slug]);
        }
    }

    // 2) Repli : ordre des rubriques du site pour les non classées.
    if (function_exists('em_site_get_site_rubrique_order')) {
        foreach (em_site_get_site_rubrique_order() as $slug) {
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
function em_site_overview_render(): void
{
    $types = em_site_ordered_types();
    // Rubrique ciblée par le sous-menu de gauche (…&type=<slug>) : on ouvre sa carte.
    $open_type = sanitize_key((string) ($_GET['type'] ?? ''));
    $breadcrumb = [];
    if (function_exists('em_site_admin_hub_breadcrumb_crumb')) {
        $breadcrumb[] = em_site_admin_hub_breadcrumb_crumb(__('Mes Rubriques', 'em-site'));
    }
    ?>
    <div class="wrap em-site-overview em-site-admin-module em-site-hub-sommaire">
        <?php
        if (function_exists('em_site_admin_hub_render_sommaire_header')) {
            em_site_admin_hub_render_sommaire_header('', 'dashicons-screenoptions', false, true, null, $breadcrumb, false);
        }
        ?>

        <?php em_site_overview_notice(); ?>
        <?php em_site_overview_render_styles(); ?>

        <?php em_site_overview_render_create_type(); ?>

        <?php if ($types === []) : ?>
            <p><?php esc_html_e('Aucune rubrique déclarée pour le moment.', 'em-site'); ?></p>
        <?php else : ?>
            <div class="em-site-cards" id="em-site-cards">
                <?php foreach ($types as $slug => $type) : ?>
                    <?php em_site_overview_render_type((string) $slug, $type, $open_type === (string) $slug); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php em_site_overview_render_reorder_script(); ?>
    <?php em_site_overview_render_rename_script(); ?>
    <script>
    (function () {
        function setCreateOpenState(btn, box, isOpen) {
            if (!box) { return; }
            box.hidden = !isOpen;
            if (!btn) { return; }
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            btn.classList.toggle('is-active', !!isOpen);
        }

        function syncCardAddButton(card) {
            if (!card) { return; }
            var hasOpenItem = !!card.querySelector('.em-site-items > .em-site-item[open]');
            card.classList.toggle('em-site-card--item-open', hasOpenItem);
            if (!hasOpenItem) { return; }
            var btn = card.querySelector('.em-site-card__additem');
            var box = card.querySelector('.em-site-create');
            setCreateOpenState(btn, box, false);
        }

        // Accordéon : une seule rubrique ouverte à la fois (focus sur l'édition).
        var cards = document.querySelectorAll('.em-site-card');
        cards.forEach(function (card) {
            card.addEventListener('toggle', function () {
                var ownCreate = card.querySelector('.em-site-create');
                var ownBtn = card.querySelector('.em-site-card__additem');
                setCreateOpenState(ownBtn, ownCreate, false);
                if (!card.open) { return; }
                cards.forEach(function (other) {
                    if (other !== card && other.open) { other.open = false; }
                    if (other !== card) {
                        var otherBtn = other.querySelector('.em-site-card__additem');
                        var otherCreate = other.querySelector('.em-site-create');
                        setCreateOpenState(otherBtn, otherCreate, false);
                    }
                });
                syncCardAddButton(card);
            });
        });
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.em-site-card__additem');
            if (!btn) { return; }
            e.preventDefault();
            e.stopPropagation();
            var card = btn.closest('.em-site-card');
            if (card && !card.open) { card.open = true; }
            var targetId = btn.getAttribute('data-create-target') || '';
            var createBox = targetId ? document.getElementById(targetId) : null;
            if (!createBox) { return; }
            var nextOpen = createBox.hidden;
            setCreateOpenState(btn, createBox, nextOpen);
            if (!nextOpen) { return; }
            try {
                var firstInput = createBox.querySelector('.em-site-create__name:not([disabled])');
                if (firstInput) { firstInput.focus(); }
            } catch (err) {}
        });
        // Accordéon : une seule section (item) ouverte à la fois, par rubrique.
        document.querySelectorAll('.em-site-items').forEach(function (group) {
            var items = group.querySelectorAll(':scope > .em-site-item');
            items.forEach(function (item) {
                item.addEventListener('toggle', function () {
                    var card = item.closest('.em-site-card');
                    if (item.open) {
                        items.forEach(function (other) {
                            if (other !== item && other.open) { other.open = false; }
                        });
                    }
                    syncCardAddButton(card);
                });
            });

            var anyItem = group.querySelector(':scope > .em-site-item');
            if (anyItem) {
                var card = anyItem.closest('.em-site-card');
                syncCardAddButton(card);
            }
        });

        // État initial ARIA/cohérence visuelle.
        cards.forEach(function (card) {
            var btn = card.querySelector('.em-site-card__additem');
            var box = card.querySelector('.em-site-create');
            if (!btn || !box) { return; }
            setCreateOpenState(btn, box, !box.hidden);
            syncCardAddButton(card);
        });
    })();
    </script>
    <?php if ($open_type !== '' && isset($types[$open_type])) : ?>
        <script>
        (function () {
            var el = document.getElementById('em-site-card-<?php echo esc_js($open_type); ?>');
            if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        })();
        </script>
    <?php endif; ?>
    <?php
}

/**
 * Notice de feedback.
 */
function em_site_overview_notice(): void
{
    $updated = sanitize_key((string) ($_GET['updated'] ?? ''));
    $error = sanitize_key((string) ($_GET['error'] ?? ''));

    $type_slug = sanitize_key((string) ($_GET['type'] ?? ''));
    $n = em_site_rubrique_type_nouns($type_slug !== '' && em_site_rubrique_type_exists($type_slug) ? $type_slug : '');
    $noun = ucfirst($n['singular'] !== '' ? $n['singular'] : __('élément', 'em-site'));
    $e = $n['e'];

    $messages = [
        'saved'      => sprintf(__('%1$s enregistré%2$s.', 'em-site'), $noun, $e),
        'created'    => sprintf(__('%1$s créé%2$s.', 'em-site'), $noun, $e),
        'deleted'    => sprintf(__('%1$s supprimé%2$s.', 'em-site'), $noun, $e),
        'duplicated' => sprintf(__('%1$s dupliqué%2$s.', 'em-site'), $noun, $e),
        'structure'  => __('Structure enregistrée.', 'em-site'),
        'type_created' => __('Rubrique créée.', 'em-site'),
    ];

    if (isset($messages[$updated])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$updated]) . '</p></div>';
    } elseif ($error === 'type_exists') {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Cette rubrique existe déjà.', 'em-site') . '</p></div>';
    } elseif ($error !== '') {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Action impossible (données invalides).', 'em-site') . '</p></div>';
    }
}

/**
 * Carte repliable d'une rubrique : ses footers.
 *
 * @param array<string, mixed> $type
 */
function em_site_overview_render_type(string $slug, array $type, bool $open): void
{
    $count = count(em_site_get_items($slug));
    $label = (string) ($type['label_plural'] ?? $type['label']);
    $label_singular = (string) ($type['label'] ?? $label);
    $add_label = sprintf(__('Ajouter une Section %s', 'em-site'), $label_singular);
    $is_special_fixed = function_exists('em_site_is_fixed_single_item_type')
        && em_site_is_fixed_single_item_type($slug);
    $can_add_items = !$is_special_fixed;
    $label_for_match = strtolower(remove_accents($label));
    $is_header_linked = in_array($slug, ['hero', 'heros', 'heroes', 'slider', 'sliders'], true)
        || strpos($slug, 'hero') !== false
        || strpos($slug, 'slider') !== false
        || strpos($label_for_match, 'hero') !== false
        || strpos($label_for_match, 'slider') !== false;
    $card_classes = 'em-site-collapse em-site-card'
        . ($is_special_fixed ? ' em-site-card--fixed-single' : '')
        . ($is_header_linked ? ' em-site-card--header-linked' : '');
    ?>
    <details class="<?php echo esc_attr($card_classes); ?>" id="em-site-card-<?php echo esc_attr($slug); ?>" data-slug="<?php echo esc_attr($slug); ?>" <?php echo $open ? 'open' : ''; ?>>
        <summary class="em-site-collapse__summary em-site-card__head">
            <span class="em-site-card__drag dashicons dashicons-menu" title="<?php esc_attr_e('Glisser pour réordonner', 'em-site'); ?>" aria-hidden="true"></span>
            <span class="em-site-collapse__chevron" aria-hidden="true"></span>
            <span class="em-site-card__icon dashicons <?php echo esc_attr((string) ($type['icon'] ?? 'dashicons-screenoptions')); ?>"></span>
            <button type="button" class="em-site-card__edit" title="<?php esc_attr_e('Renommer la rubrique', 'em-site'); ?>" aria-label="<?php esc_attr_e('Renommer la rubrique', 'em-site'); ?>">
                <span class="dashicons dashicons-edit" aria-hidden="true"></span>
            </button>
            <strong class="em-site-card__name"><?php echo esc_html($label); ?></strong>
            <input type="text" class="em-site-card__nameinput" data-slug="<?php echo esc_attr($slug); ?>" data-original="<?php echo esc_attr($label); ?>" value="<?php echo esc_attr($label); ?>" hidden>
            <button type="button" class="em-site-card__confirm" title="<?php esc_attr_e('Valider', 'em-site'); ?>" aria-label="<?php esc_attr_e('Valider', 'em-site'); ?>" hidden>
                <span class="dashicons dashicons-yes" aria-hidden="true"></span>
            </button>
            <button type="button" class="em-site-card__cancel" title="<?php esc_attr_e('Annuler', 'em-site'); ?>" aria-label="<?php esc_attr_e('Annuler', 'em-site'); ?>" hidden>
                <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
            </button>
            <span class="em-site-card__count" title="<?php echo esc_attr(sprintf(_n('%d section', '%d sections', $count, 'em-site'), $count)); ?>"><?php echo esc_html((string) $count); ?></span>
            <?php if ($can_add_items) { ?>
                <button type="button" class="em-site-card__additem" data-create-target="em-site-create-<?php echo esc_attr($slug); ?>" title="<?php echo esc_attr($add_label); ?>" aria-label="<?php echo esc_attr($add_label); ?>" aria-expanded="false">
                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                    <span><?php echo esc_html($add_label); ?></span>
                </button>
            <?php } ?>
        </summary>
        <div class="em-site-collapse__body">
            <?php em_site_render_items_section($slug); ?>
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
function em_site_overview_render_create_type(): void
{
    $icons = [
        'dashicons-screenoptions', 'dashicons-menu-alt3', 'dashicons-format-audio',
        'dashicons-share', 'dashicons-video-alt3', 'dashicons-album', 'dashicons-megaphone',
        'dashicons-star-filled', 'dashicons-heart', 'dashicons-images-alt2',
        'dashicons-list-view', 'dashicons-admin-links',
    ];
    ?>
    <details class="em-site-collapse em-site-create em-site-create--nochevron em-site-createtype">
        <summary class="em-site-collapse__summary">
            <span class="dashicons dashicons-plus-alt2"></span>
            <strong><?php esc_html_e('Nouvelle Rubrique', 'em-site'); ?></strong>
        </summary>
        <div class="em-site-collapse__body em-site-create__options">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-site-form em-site-create__row">
                <?php wp_nonce_field('em_site_create_type'); ?>
                <input type="hidden" name="action" value="em_site_create_type">
                <span class="em-site-create__label"><span class="dashicons dashicons-screenoptions" aria-hidden="true"></span> <?php esc_html_e('Nom de la rubrique', 'em-site'); ?></span>
                <input type="text" name="type_label" class="regular-text em-site-create__name" placeholder="<?php esc_attr_e('Ex. PARTENAIRES', 'em-site'); ?>" required>
                <span class="em-site-iconpick" role="radiogroup" aria-label="<?php esc_attr_e('Icône de la rubrique', 'em-site'); ?>">
                    <?php foreach ($icons as $i => $ic) : ?>
                        <label class="em-site-iconpick__opt" title="<?php echo esc_attr($ic); ?>">
                            <input type="radio" name="type_icon" value="<?php echo esc_attr($ic); ?>" <?php checked($i, 0); ?>>
                            <span class="dashicons <?php echo esc_attr($ic); ?>" aria-hidden="true"></span>
                        </label>
                    <?php endforeach; ?>
                </span>
                <button type="submit" class="button button-primary"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Créer la rubrique', 'em-site'); ?></button>
            </form>
        </div>
    </details>
    <?php
}

/**
 * Styles inline (autonome).
 */
function em_site_overview_render_styles(): void
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
 * CSS de PREVIEW ADMIN Rubriques lu depuis une source dédiée et explicitement
 * nommée "rubriques-preview", afin de séparer clairement les
 * responsabilités (admin preview vs styles des modules front).
 *
 * @return string CSS concaténé (sans balise <style>).
 */
function em_site_admin_rubriques_preview_css(): string
{
    static $css = null;

    if ($css !== null) {
        return $css;
    }

    $css = '';
    $base = get_template_directory() . '/assets/admin/css/rubriques-preview/';
    foreach (
        [
            'admin-preview-render-base.css',
            'admin-preview-render-media.css',
            'admin-preview-render-components.css',
            'admin-preview-render-header.css',
            'admin-preview-render-layout.css',
        ] as $file
    ) {
        $path = $base . $file;
        if (is_readable($path)) {
            $css .= (string) file_get_contents($path) . "\n";
        }
    }

    return $css;
}

/**
 * Compatibilité rétroactive: ancien nom conservé comme alias.
 *
 * @return string
 */
function em_site_rubriques_admin_render_css(): string
{
    return em_site_admin_rubriques_preview_css();
}

