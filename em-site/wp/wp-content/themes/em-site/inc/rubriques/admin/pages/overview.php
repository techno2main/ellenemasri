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
 * Option des rubriques masquées depuis l'overview (suppression safe).
 */
function em_site_hidden_rubriques_option_name(): string
{
    return 'em_site_hidden_rubriques';
}

/**
 * Slugs de rubriques masquées (suppression safe non destructive).
 *
 * @return array<int, string>
 */
function em_site_get_hidden_rubriques(): array
{
    $raw = get_option(em_site_hidden_rubriques_option_name(), []);

    if (!is_array($raw)) {
        return [];
    }

    $hidden = [];

    foreach ($raw as $slug) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || in_array($slug, ['top-bar', 'footer', 'headers'], true) || in_array($slug, $hidden, true)) {
            continue;
        }

        $hidden[] = $slug;
    }

    return $hidden;
}

/**
 * Types triés dans l'ordre des rubriques du site (HEADER absent du EM-SITE).
 *
 * @return array<string, array<string, mixed>>
 */
function em_site_ordered_types(): array
{
    $types = em_site_rubrique_type_registry();
    $ordered = [];
    $footer_type = null;
    $hidden_types = em_site_get_hidden_rubriques();

    foreach ($hidden_types as $hidden_slug) {
        unset($types[$hidden_slug]);
    }

    if (isset($types['footer'])) {
        $footer_type = $types['footer'];
        unset($types['footer']);
    }

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
    $result = $ordered + $types;

    // Footer reste systématiquement tout en bas.
    if (is_array($footer_type)) {
        $result['footer'] = $footer_type;
    }

    return $result;
}

/**
 * Types de rubriques créés via l'admin (custom).
 *
 * @return array<string, true>
 */
function em_site_overview_custom_type_map(): array
{
    static $map = null;

    if (is_array($map)) {
        return $map;
    }

    $raw = function_exists('em_site_rubrique_types_option_name')
        ? get_option(em_site_rubrique_types_option_name(), [])
        : [];

    $map = [];

    if (is_array($raw)) {
        foreach ($raw as $slug => $definition) {
            $slug = sanitize_key((string) $slug);

            if ($slug === '' || !is_array($definition)) {
                continue;
            }

            $map[$slug] = true;
        }
    }

    return $map;
}

/**
 * URL canonique du sommaire Rubriques.
 */
function em_site_overview_summary_url(): string
{
    return (string) admin_url('admin.php?page=em-rubriques-overview');
}

/**
 * Barre discrète de retour au sommaire en mode focus.
 *
 * @param array<string, mixed> $type
 */
function em_site_overview_render_focus_back(string $active_slug, array $type): void
{
    $label = (string) ($type['label_plural'] ?? $type['label'] ?? $active_slug);
    ?>
    <div class="em-site-overview__focus-bar" data-overview-focusbar>
        <a href="<?php echo esc_url(em_site_overview_summary_url()); ?>" class="em-site-overview__focus-back" data-overview-back>
            <span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
            <span><?php esc_html_e('Retour au sommaire', 'em-site'); ?></span>
        </a>
        <div class="em-site-overview__focus-titlewrap">
            <span class="em-site-overview__focus-kicker"><?php esc_html_e('Édition ciblée', 'em-site'); ?></span>
            <strong class="em-site-overview__focus-title"><?php echo esc_html($label); ?></strong>
        </div>
    </div>
    <?php
}

/**
 * Sommaire compact des rubriques.
 *
 * @param array<string, array<string, mixed>> $types
 */
function em_site_overview_render_directory(array $types, string $active_slug): void
{
    ?>
    <section class="em-site-overview__summary" data-overview-summary>
        <div class="em-site-overview__summary-head">
            <div>
                <p class="em-site-overview__eyebrow"><?php esc_html_e('Sommaire des rubriques', 'em-site'); ?></p>
            </div>
        </div>

        <div class="em-site-overview__directory" role="list">
            <?php
            $slots_total = count($types);
            $slot_index = 0;
            ?>
            <?php foreach ($types as $slug => $type) : ?>
                <?php
                $slug = (string) $slug;
                $items = em_site_get_items($slug);
                $count = count($items);
                $label = (string) ($type['label_plural'] ?? $type['label'] ?? $slug);
                $icon = (string) ($type['icon'] ?? 'dashicons-screenoptions');
                $is_active = ($active_slug === $slug);
                ?>
                <a
                    href="<?php echo esc_url(add_query_arg('type', $slug, em_site_overview_summary_url())); ?>"
                    class="em-site-overview__directory-link<?php echo $is_active ? ' is-active' : ''; ?>"
                    data-focus-slug="<?php echo esc_attr($slug); ?>"
                    data-item-count="<?php echo esc_attr((string) $count); ?>"
                    role="listitem"
                    aria-current="<?php echo $is_active ? 'true' : 'false'; ?>"
                >
                    <span class="em-site-overview__directory-content">
                        <span class="em-site-overview__directory-topline">
                            <span class="em-site-overview__directory-heading">
                                <span class="em-site-overview__directory-icon dashicons <?php echo esc_attr($icon); ?>" aria-hidden="true"></span>
                                <strong class="em-site-overview__directory-label"><?php echo esc_html($label); ?></strong>
                            </span>
                        </span>
                        <span class="em-site-overview__directory-meta">
                            <?php if ($items === []) : ?>
                                <span class="em-site-overview__directory-pill is-empty"><?php esc_html_e('Aucun item', 'em-site'); ?></span>
                            <?php else : ?>
                                <?php foreach ($items as $item_label) : ?>
                                    <span class="em-site-overview__directory-pill"><?php echo esc_html((string) $item_label); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="em-site-overview__directory-rail" aria-hidden="true">
                        <span class="em-site-overview__directory-arrow dashicons dashicons-arrow-right-alt2"></span>
                        <span class="em-site-overview__directory-map">
                            <?php for ($i = 0; $i < $slots_total; $i++) : ?>
                                <span class="em-site-overview__directory-map-slot<?php echo $i === $slot_index ? ' is-current' : ''; ?>"></span>
                            <?php endfor; ?>
                        </span>
                    </span>
                </a>
                <?php $slot_index++; ?>
            <?php endforeach; ?>
            <button
                type="button"
                class="em-site-overview__directory-link em-site-overview__directory-link--create"
                data-overview-create-toggle
                aria-expanded="false"
                role="listitem"
            >
                <span class="em-site-overview__directory-content">
                    <span class="em-site-overview__directory-topline">
                        <span class="em-site-overview__directory-heading">
                            <span class="em-site-overview__directory-icon dashicons dashicons-welcome-add-page" aria-hidden="true"></span>
                            <strong class="em-site-overview__directory-label em-site-overview__directory-label--create"><?php esc_html_e('Nouvelle Rubrique', 'em-site'); ?></strong>
                        </span>
                    </span>
                </span>
                <span class="em-site-overview__directory-rail" aria-hidden="true">
                    <span class="em-site-overview__directory-arrow dashicons dashicons-arrow-right-alt2"></span>
                </span>
            </button>
        </div>

        <?php em_site_overview_render_create_type(); ?>
    </section>
    <?php
}

/**
 * Rendu de la page.
 */
function em_site_overview_render(): void
{
    $types = em_site_ordered_types();
    // Rubrique ciblée par le sous-menu de gauche (…&type=<slug>) : on ouvre sa carte.
    $open_type = sanitize_key((string) ($_GET['type'] ?? ''));
    $active_type = ($open_type !== '' && isset($types[$open_type])) ? $open_type : '';
    $breadcrumb = [];
    ?>
    <div class="wrap em-site-overview em-site-admin-module em-site-hub-sommaire<?php echo $active_type !== '' ? ' is-focus-mode' : ''; ?>" data-initial-focus="<?php echo esc_attr($active_type); ?>">
        <?php
        if (function_exists('em_site_admin_hub_render_sommaire_header')) {
            em_site_admin_hub_render_sommaire_header('', 'dashicons-screenoptions', false, true, null, $breadcrumb, false);
        }
        ?>

        <?php em_site_overview_notice(); ?>
        <?php em_site_overview_render_styles(); ?>
        <?php em_site_overview_render_focus_back($active_type, $active_type !== '' ? $types[$active_type] : []); ?>
        <?php em_site_overview_render_directory($types, $active_type); ?>

        <?php if ($types === []) : ?>
            <p><?php esc_html_e('Aucune rubrique déclarée pour le moment.', 'em-site'); ?></p>
        <?php else : ?>
            <div class="em-site-cards" id="em-site-cards" data-active-slug="<?php echo esc_attr($active_type); ?>">
                <?php foreach ($types as $slug => $type) : ?>
                    <?php em_site_overview_render_type((string) $slug, $type, $active_type === (string) $slug); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php em_site_overview_render_reorder_script(); ?>
    <?php em_site_overview_render_rename_script(); ?>
    <?php em_site_overview_render_delete_type_script(); ?>
    <script>
    (function () {
        var wrap = document.querySelector('.em-site-overview');
        if (!wrap) { return; }

        var summaryUrl = <?php echo wp_json_encode(em_site_overview_summary_url()); ?>;
        var initialFocus = wrap.getAttribute('data-initial-focus') || '';
        var cards = Array.prototype.slice.call(document.querySelectorAll('.em-site-card'));
        var focusLinks = Array.prototype.slice.call(document.querySelectorAll('[data-focus-slug]'));
        var createTypeToggle = wrap.querySelector('[data-overview-create-toggle]');
        var createTypePanel = document.getElementById('em-site-create-type-panel');

        function setCreateTypePanelOpen(isOpen) {
            if (!createTypePanel) { return; }
            createTypePanel.hidden = !isOpen;
            if (createTypeToggle) {
                createTypeToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                createTypeToggle.classList.toggle('is-active', !!isOpen);
            }
            if (!isOpen) { return; }
            try {
                var firstInput = createTypePanel.querySelector('.em-site-create__name:not([disabled])');
                if (firstInput) { firstInput.focus(); }
            } catch (err) {}
        }

        function setCreateOpenState(btn, box, isOpen) {
            if (!box) { return; }
            box.hidden = !isOpen;
            if (!btn) { return; }
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            btn.classList.toggle('is-active', !!isOpen);
        }

        function getCardBySlug(slug) {
            return document.getElementById('em-site-card-' + slug);
        }

        function closeCardUi(card) {
            if (!card) { return; }
            var btn = card.querySelector('.em-site-card__additem');
            var box = card.querySelector('.em-site-create');
            setCreateOpenState(btn, box, false);
            card.classList.remove('em-site-card--item-open');
        }

        function syncFocusLinks(activeSlug) {
            focusLinks.forEach(function (link) {
                var isActive = !!activeSlug && link.getAttribute('data-focus-slug') === activeSlug;
                link.classList.toggle('is-active', isActive);
                link.setAttribute('aria-current', isActive ? 'true' : 'false');
            });
        }

        function syncFocusTitle(activeSlug) {
            var title = wrap.querySelector('.em-site-overview__focus-title');
            if (!title) { return; }
            if (!activeSlug) {
                title.textContent = '';
                return;
            }
            var card = getCardBySlug(activeSlug);
            var name = card ? card.querySelector('.em-site-card__name') : null;
            title.textContent = name ? name.textContent : activeSlug;
        }

        function resetItemsForFocus(card) {
            if (!card) { return; }
            var items = Array.prototype.slice.call(card.querySelectorAll('.em-site-items > .em-site-item'));
            if (!items.length) { return; }
            items.forEach(function (item) {
                item.open = false;
            });
        }

        function setFocusMode(activeSlug, options) {
            var settings = options || {};
            var targetCard = activeSlug ? getCardBySlug(activeSlug) : null;

            if (!targetCard) {
                wrap.classList.remove('is-focus-mode');
                wrap.removeAttribute('data-focus-slug');
                cards.forEach(function (card) {
                    card.hidden = true;
                    card.open = false;
                    closeCardUi(card);
                });
                syncFocusLinks('');
                syncFocusTitle('');
                if (settings.updateHistory && window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, summaryUrl);
                }
                return;
            }

            wrap.classList.add('is-focus-mode');
            wrap.setAttribute('data-focus-slug', activeSlug);
            cards.forEach(function (card) {
                var isTarget = card === targetCard;
                card.hidden = !isTarget;
                if (!isTarget) {
                    card.open = false;
                    closeCardUi(card);
                    return;
                }
                card.open = true;
                closeCardUi(card);
                resetItemsForFocus(card);
            });
            syncFocusLinks(activeSlug);
            syncFocusTitle(activeSlug);

            if (settings.updateHistory && window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, activeSlug ? (summaryUrl + '&type=' + encodeURIComponent(activeSlug)) : summaryUrl);
            }

            if (settings.scroll !== false) {
                var focusBar = wrap.querySelector('[data-overview-focusbar]');
                if (focusBar && focusBar.scrollIntoView) {
                    focusBar.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
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
        focusLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                var slug = link.getAttribute('data-focus-slug') || '';
                if (!slug) { return; }
                event.preventDefault();
                setFocusMode(slug, { updateHistory: true });
            });
        });
        document.querySelectorAll('[data-overview-back]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                setFocusMode('', { updateHistory: true });
            });
        });
        if (createTypeToggle) {
            createTypeToggle.addEventListener('click', function (event) {
                event.preventDefault();
                setCreateTypePanelOpen(!createTypePanel || createTypePanel.hidden);
            });
        }
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
        if (createTypePanel) {
            setCreateTypePanelOpen(!createTypePanel.hidden);
        }
        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape' || !wrap.classList.contains('is-focus-mode')) { return; }
            var target = event.target;
            if (target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT' || target.isContentEditable)) {
                return;
            }
            setFocusMode('', { updateHistory: true, scroll: false });
        });

        if (initialFocus) {
            setFocusMode(initialFocus, { updateHistory: false, scroll: false });
        } else {
            setFocusMode('', { updateHistory: false, scroll: false });
        }
    })();
    </script>
    <?php if ($active_type !== '') : ?>
        <script>
        (function () {
            var el = document.getElementById('em-site-card-<?php echo esc_js($active_type); ?>');
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
        'type_deleted' => __('Rubrique supprimée.', 'em-site'),
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
    $add_label = sprintf(__('Ajouter un item %s', 'em-site'), $label_singular);
    $is_special_fixed = function_exists('em_site_is_fixed_single_item_type')
        && em_site_is_fixed_single_item_type($slug);
    $is_header_container = ($slug === 'headers');
    $is_reorderable = !$is_special_fixed && !$is_header_container;
    $can_add_items = !$is_special_fixed;
    $can_delete_type = !$is_special_fixed && !$is_header_container;
    $delete_form_id = 'em-site-delete-type-' . $slug;
    $delete_title = __('Supprimer la rubrique', 'em-site');
    $delete_tip = __('Supprimer cette rubrique', 'em-site');
    $delete_ack = __('Je confirme la suppression définitive de cette rubrique et de ses sections.', 'em-site');
    $label_for_match = strtolower(remove_accents($label));
    $is_header_linked = in_array($slug, ['hero', 'heros', 'heroes', 'slider', 'sliders'], true)
        || strpos($slug, 'hero') !== false
        || strpos($slug, 'slider') !== false
        || strpos($label_for_match, 'hero') !== false
        || strpos($label_for_match, 'slider') !== false;
    $card_classes = 'em-site-collapse em-site-card'
        . ($is_special_fixed ? ' em-site-card--fixed-single' : '')
        . (!$is_reorderable ? ' em-site-card--not-reorderable' : '')
        . ($is_header_linked ? ' em-site-card--header-linked' : '');
    ?>
    <details class="<?php echo esc_attr($card_classes); ?>" id="em-site-card-<?php echo esc_attr($slug); ?>" data-slug="<?php echo esc_attr($slug); ?>" data-item-count="<?php echo esc_attr((string) $count); ?>" data-reorderable="<?php echo $is_reorderable ? '1' : '0'; ?>" data-header-linked="<?php echo $is_header_linked ? '1' : '0'; ?>" <?php echo $open ? 'open' : ''; ?>>
        <summary class="em-site-collapse__summary em-site-card__head">
            <span class="em-site-card__drag dashicons dashicons-menu" title="<?php echo esc_attr($is_reorderable ? __('Glisser pour réordonner', 'em-site') : __('Ordre verrouillé', 'em-site')); ?>" aria-hidden="true"></span>
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
            <span class="em-site-card__count" title="<?php echo esc_attr(sprintf(_n('%d item', '%d items', $count, 'em-site'), $count)); ?>"><?php echo esc_html((string) $count); ?></span>
            <?php if ($can_add_items) { ?>
                <button type="button" class="em-site-card__additem" data-create-target="em-site-create-<?php echo esc_attr($slug); ?>" title="<?php echo esc_attr($add_label); ?>" aria-label="<?php echo esc_attr($add_label); ?>" aria-expanded="false">
                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                    <span><?php echo esc_html($add_label); ?></span>
                </button>
            <?php } ?>
            <?php if ($can_delete_type) { ?>
                <button type="button" class="em-site-card__delete em-site-type-delete" data-deleteform="<?php echo esc_attr($delete_form_id); ?>" data-label="<?php echo esc_attr($label); ?>" data-title="<?php echo esc_attr($delete_title); ?>" data-ack="<?php echo esc_attr($delete_ack); ?>" title="<?php echo esc_attr($delete_tip); ?>" aria-label="<?php echo esc_attr($delete_tip); ?>">
                    <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                </button>
            <?php } ?>
        </summary>
        <div class="em-site-collapse__body">
            <?php em_site_render_items_section($slug); ?>
        </div>
        <?php if ($can_delete_type) { ?>
            <form id="<?php echo esc_attr($delete_form_id); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-site-deleteform" hidden>
                <?php wp_nonce_field('em_site_delete_type'); ?>
                <input type="hidden" name="action" value="em_site_delete_type">
                <input type="hidden" name="type" value="<?php echo esc_attr($slug); ?>">
            </form>
        <?php } ?>
    </details>
    <?php
}

/**
 * Confirmation modale de suppression d'une rubrique (cartes overview).
 */
function em_site_overview_render_delete_type_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.em-site-type-delete');
        if (!btn) { return; }
        e.preventDefault();
        e.stopPropagation();
        if (!window.EmWpAdminConfirm || !window.EmWpAdminConfirm.confirmDelete) { return; }

        var form = document.getElementById(btn.getAttribute('data-deleteform'));
        if (!form) { return; }

        var label = btn.getAttribute('data-label') || '';
        var message = '<?php echo esc_js(__('Supprimer définitivement la rubrique « ', 'em-site')); ?>'
            + label
            + '<?php echo esc_js(__(' » et toutes ses sections ?', 'em-site')); ?>';

        window.EmWpAdminConfirm.confirmDelete(function () { form.submit(); }, {
            title: btn.getAttribute('data-title') || '<?php echo esc_js(__('Supprimer', 'em-site')); ?>',
            message: message,
            acknowledgeLabel: btn.getAttribute('data-ack') || '<?php echo esc_js(__('Je confirme la suppression.', 'em-site')); ?>',
            confirmLabel: '<?php echo esc_js(__('Supprimer définitivement', 'em-site')); ?>'
        });
    });
    </script>
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
    <div class="em-site-collapse em-site-create em-site-create--nochevron em-site-createtype" id="em-site-create-type-panel" hidden>
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
    </div>
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

