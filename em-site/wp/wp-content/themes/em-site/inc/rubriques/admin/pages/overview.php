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

require_once __DIR__ . '/overview/parts/actions-create-and-css.php';

if (!function_exists('em_site_overview_type_label')) {
    /**
     * Libellé normalisé d'une rubrique pour l'overview (cartes + menu).
     *
     * @param array<string,mixed> $type
     */
    function em_site_overview_type_label(string $slug, array $type): string
    {
        $slug = sanitize_key($slug);

        $forced = [
            'about'           => 'ABOUT',
            'abouts'          => 'ABOUT',
            'custom-about'    => 'ABOUT',
            'custom-abouts'   => 'ABOUT',
            'contact'         => 'CONTACT',
            'contacts'        => 'CONTACT',
            'custom-contact'  => 'CONTACT',
            'custom-contacts' => 'CONTACT',
        ];

        if (isset($forced[$slug])) {
            return (string) __($forced[$slug], 'em-site');
        }

        return (string) ($type['label_plural'] ?? $type['label'] ?? strtoupper($slug));
    }
}

if (!function_exists('em_site_overview_type_icon')) {
    /**
     * Icône normalisée d'une rubrique pour l'overview (cartes + menu).
     *
     * @param array<string,mixed> $type
     */
    function em_site_overview_type_icon(string $slug, array $type): string
    {
        $fallback = (string) ($type['icon'] ?? 'dashicons-screenoptions');

        if (function_exists('em_site_rubrique_icon')) {
            $icon_key = sanitize_key($slug);

            if (function_exists('em_site_rubrique_icon_key_from_definition')) {
                $icon_key = em_site_rubrique_icon_key_from_definition($slug, $type);
            }

            return em_site_rubrique_icon($icon_key, $fallback !== '' ? $fallback : 'dashicons-screenoptions');
        }

        return $fallback !== '' ? $fallback : 'dashicons-screenoptions';
    }
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

    $rubriques_icon = function_exists('em_site_site_icon') ? em_site_site_icon('rubriques', 'dashicons-screenoptions') : 'dashicons-screenoptions';

    add_menu_page(
        __('RUBRIQUES', 'em-site'),
        __('RUBRIQUES', 'em-site'),
        'manage_options',
        'em-rubriques-overview',
        'em_site_overview_render',
        $rubriques_icon,
        $position
    );

    // Un sous-menu par rubrique (pas les items détaillés), dans l'ordre du site.
    // Le slug « …&type=<slug> » ouvre la carte correspondante de l'aperçu.
    // Le libellé porte une icône Dashicon (rendu HTML accepté par le menu).
    foreach (em_site_ordered_types() as $slug => $type) {
        $label = em_site_overview_type_label((string) $slug, $type);
        $icon = em_site_overview_type_icon((string) $slug, $type);
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
        $submenu['em-rubriques-overview'][0][0] = '<span class="dashicons ' . esc_attr($rubriques_icon) . ' em-site-rubrique-submenu__icon" aria-hidden="true"></span>'
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
    $label = em_site_overview_type_label((string) $active_slug, $type);
    ?>
    <div class="em-site-overview__focus-bar" data-overview-focusbar>
        <a href="<?php echo esc_url(em_site_overview_summary_url()); ?>" class="em-site-overview__focus-back" data-overview-back title="<?php esc_attr_e('Retour au sommaire', 'em-site'); ?>" aria-label="<?php esc_attr_e('Retour au sommaire', 'em-site'); ?>">
            <span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
            <span class="dashicons dashicons-screenoptions" aria-hidden="true"></span>
            <span aria-hidden="true"><?php esc_html_e('RUBRIQUES', 'em-site'); ?></span>
            <span class="screen-reader-text"><?php esc_html_e('Retour au sommaire', 'em-site'); ?></span>
        </a>
        <div class="em-site-overview__focus-titlewrap">
            <span class="em-site-overview__focus-kicker"><?php esc_html_e('ÉDITION CIBLÉE DE LA RUBRIQUE', 'em-site'); ?></span>
            <strong class="em-site-overview__focus-title">
                <button type="button" class="em-site-overview__focus-titlehome" data-overview-resetitems title="<?php esc_attr_e('Afficher la liste des items fermés', 'em-site'); ?>" aria-label="<?php esc_attr_e('Afficher la liste des items fermés', 'em-site'); ?>">
                    <span class="em-site-overview__focus-titleicon dashicons dashicons-screenoptions" aria-hidden="true"></span>
                    <span class="em-site-overview__focus-titlename"><?php echo esc_html($label); ?></span>
                </button>
                <input type="text" class="em-site-overview__focus-nameinput" value="<?php echo esc_attr($label); ?>" hidden>
                <button type="button" class="em-site-overview__focus-confirm" title="<?php esc_attr_e('Valider', 'em-site'); ?>" aria-label="<?php esc_attr_e('Valider', 'em-site'); ?>" hidden>
                    <span class="dashicons dashicons-yes" aria-hidden="true"></span>
                </button>
                <button type="button" class="em-site-overview__focus-cancel" title="<?php esc_attr_e('Annuler', 'em-site'); ?>" aria-label="<?php esc_attr_e('Annuler', 'em-site'); ?>" hidden>
                    <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                </button>
                <button type="button" class="em-site-overview__focus-titleedit" aria-label="<?php esc_attr_e('Renommer la rubrique', 'em-site'); ?>" title="<?php esc_attr_e('Renommer la rubrique', 'em-site'); ?>">
                    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                    <span class="screen-reader-text"><?php esc_html_e('Renommer la rubrique', 'em-site'); ?></span>
                </button>
                <span class="em-site-overview__focus-itemtabs" data-overview-itemtabs hidden></span>
                <button type="button" class="em-site-overview__focus-additem" data-overview-additem hidden>
                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                    <span><?php esc_html_e('Nouvel item', 'em-site'); ?></span>
                </button>
            </strong>
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
                $label = em_site_overview_type_label((string) $slug, $type);
                $icon = em_site_overview_type_icon((string) $slug, $type);
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
                            <strong class="em-site-overview__directory-label em-site-overview__directory-label--create"><?php esc_html_e('Ajouter', 'em-site'); ?></strong>
                        </span>
                    </span>
                    <span class="em-site-overview__directory-meta">
                        <span class="em-site-overview__directory-pill em-site-overview__directory-pill--createhint"><?php esc_html_e('+ NOUVELLE RUBRIQUE', 'em-site'); ?></span>
                    </span>
                </span>
                <span class="em-site-overview__directory-rail" aria-hidden="true">
                    <span class="em-site-overview__directory-arrow dashicons dashicons-plus-alt2"></span>
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

        function setupCreateTypeIconChooser() {
            if (!createTypePanel) { return; }

            var chooser = createTypePanel.querySelector('[data-iconchooser]');
            if (!chooser) { return; }

            var valueInput = chooser.querySelector('[data-iconchooser-value]');
            var trigger = chooser.querySelector('[data-iconchooser-trigger]');
            var preview = chooser.querySelector('[data-iconchooser-preview] .dashicons');
            var nameLabel = chooser.querySelector('[data-iconchooser-name]');
            var defaultIcon = valueInput ? (valueInput.getAttribute('data-default-icon') || 'dashicons-screenoptions') : 'dashicons-screenoptions';
            var placeholderText = nameLabel ? (nameLabel.getAttribute('data-iconchooser-placeholder') || 'Choisi une icône pour ta nouvelle rubrique') : 'Choisi une icône pour ta nouvelle rubrique';
            var panel = chooser.querySelector('[data-iconchooser-panel]');
            var items = Array.prototype.slice.call(chooser.querySelectorAll('[data-icon-value]'));
            var groups = Array.prototype.slice.call(chooser.querySelectorAll('[data-iconchooser-group]'));

            if (!valueInput || !trigger || !preview || !nameLabel || !panel || !items.length) {
                return;
            }

            function isOpen() {
                return !panel.hidden;
            }

            function formatIconLabel(iconName) {
                return (iconName || '').replace(/^dashicons-/, '');
            }

            function setPanelPlacement() {
                if (!isOpen()) { return; }

                var rect = trigger.getBoundingClientRect();
                var viewportH = window.innerHeight || document.documentElement.clientHeight || 0;
                var viewportPadding = 24;
                var panelGap = 12;
                var spaceBelow = Math.max(0, viewportH - rect.bottom - viewportPadding - panelGap);
                var spaceAbove = Math.max(0, rect.top - viewportPadding - panelGap);
                var minPreferred = 280;
                var openUp = spaceBelow < minPreferred && spaceAbove > spaceBelow;

                chooser.classList.toggle('is-dropup', openUp);

                var available = openUp ? spaceAbove : spaceBelow;
                var maxAllowed = Math.max(0, viewportH - (viewportPadding * 2));
                var panelMax = Math.floor(Math.min(maxAllowed, available));
                var groupsMax = Math.max(40, Math.max(0, panelMax - 74));

                panel.style.maxHeight = panelMax + 'px';

                var groupsWrap = chooser.querySelector('[data-iconchooser-groups]');
                if (groupsWrap) {
                    groupsWrap.style.maxHeight = groupsMax + 'px';
                }
            }

            function setOpen(open) {
                panel.hidden = !open;
                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                chooser.classList.toggle('is-open', open);

                if (open) {
                    setPanelPlacement();
                    window.addEventListener('resize', setPanelPlacement);
                    window.addEventListener('scroll', setPanelPlacement, true);
                } else {
                    window.removeEventListener('resize', setPanelPlacement);
                    window.removeEventListener('scroll', setPanelPlacement, true);
                }

                if (!open) {
                    return;
                }

                try {
                    if (items.length) {
                        items[0].focus();
                    }
                } catch (err) {}
            }

            function applySelection(iconName) {
                var selectedIcon = (iconName || '').trim();

                valueInput.value = selectedIcon;
                nameLabel.textContent = selectedIcon ? formatIconLabel(selectedIcon) : placeholderText;

                if (!selectedIcon) {
                    selectedIcon = defaultIcon;
                }

                var keep = ['dashicons'];
                selectedIcon.split(/\s+/).forEach(function (klass) {
                    if (klass) { keep.push(klass); }
                });
                preview.className = keep.join(' ');

                items.forEach(function (item) {
                    var selected = valueInput.value !== '' && item.getAttribute('data-icon-value') === valueInput.value;
                    item.classList.toggle('is-selected', selected);
                    item.setAttribute('aria-pressed', selected ? 'true' : 'false');
                });
            }

            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                setOpen(!isOpen());
            });

            items.forEach(function (item) {
                item.addEventListener('click', function (event) {
                    event.preventDefault();
                    var iconName = item.getAttribute('data-icon-value') || '';
                    applySelection(iconName);
                    setOpen(false);
                    trigger.focus();
                });
            });

            document.addEventListener('click', function (event) {
                if (!isOpen()) { return; }
                if (chooser.contains(event.target)) { return; }
                setOpen(false);
            });

            document.addEventListener('keydown', function (event) {
                if (!isOpen() || event.key !== 'Escape') { return; }
                event.preventDefault();
                setOpen(false);
                trigger.focus();
            });

            var form = chooser.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    if ((valueInput.value || '').trim() === '') {
                        valueInput.value = defaultIcon;
                    }
                });
            }

            applySelection(valueInput.value || '');
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
            var titleWrap = wrap.querySelector('.em-site-overview__focus-title');
            var titleHome = wrap.querySelector('[data-overview-resetitems]');
            var titleName = wrap.querySelector('.em-site-overview__focus-titlename');
            var titleIcon = wrap.querySelector('.em-site-overview__focus-titleicon');
            var titleCount = wrap.querySelector('.em-site-overview__focus-titlecount');
            var itemTabs = wrap.querySelector('[data-overview-itemtabs]');
            var addItemBtn = wrap.querySelector('[data-overview-additem]');
            var titleInput = wrap.querySelector('.em-site-overview__focus-nameinput');
            var titleEdit = wrap.querySelector('.em-site-overview__focus-titleedit');
            var titleConfirm = wrap.querySelector('.em-site-overview__focus-confirm');
            var titleCancel = wrap.querySelector('.em-site-overview__focus-cancel');
            if (!titleWrap || !titleName) { return; }

            if (titleInput) { titleInput.hidden = true; }
            if (titleConfirm) { titleConfirm.hidden = true; }
            if (titleCancel) { titleCancel.hidden = true; }
            if (titleEdit) { titleEdit.hidden = false; }
            if (titleHome) { titleHome.hidden = false; }
            titleName.hidden = false;

            if (!activeSlug) {
                titleName.textContent = '';
                if (titleInput) {
                    titleInput.value = '';
                    titleInput.setAttribute('data-original', '');
                    titleInput.setAttribute('data-slug', '');
                }
                if (titleCount) { titleCount.textContent = ''; }
                if (itemTabs) {
                    itemTabs.hidden = true;
                    itemTabs.innerHTML = '';
                }
                if (addItemBtn) {
                    addItemBtn.hidden = true;
                    addItemBtn.onclick = null;
                }
                if (titleHome) { titleHome.hidden = true; }
                return;
            }
            var card = getCardBySlug(activeSlug);
            var summary = card ? card.querySelector('.em-site-card__head') : null;
            var name = summary ? summary.querySelector('.em-site-card__name') : null;
            var icon = summary ? summary.querySelector('.em-site-card__icon') : null;
            var count = summary ? summary.querySelector('.em-site-card__count') : null;

            titleName.textContent = name ? name.textContent : activeSlug;

            if (titleInput) {
                titleInput.value = titleName.textContent;
                titleInput.setAttribute('data-original', titleName.textContent);
                titleInput.setAttribute('data-slug', activeSlug);
            }

            if (titleIcon && icon) {
                var iconName = Array.prototype.find.call(icon.classList, function (klass) {
                    return klass.indexOf('dashicons-') === 0;
                }) || 'dashicons-screenoptions';
                titleIcon.className = 'em-site-overview__focus-titleicon dashicons ' + iconName;
            }

            if (titleCount) {
                titleCount.textContent = count ? count.textContent : '';
            }

            if (addItemBtn) {
                var cardAddItem = card ? card.querySelector('.em-site-card__additem') : null;
                if (!cardAddItem) {
                    addItemBtn.hidden = true;
                    addItemBtn.onclick = null;
                } else {
                    addItemBtn.hidden = false;
                    addItemBtn.title = cardAddItem.getAttribute('title') || '';
                    addItemBtn.setAttribute('aria-label', cardAddItem.getAttribute('aria-label') || '');
                    addItemBtn.onclick = function () {
                        cardAddItem.click();
                    };
                }
            }

            if (!itemTabs || !card) { return; }

            var itemNodes = Array.prototype.slice.call(card.querySelectorAll('.em-site-items > .em-site-item'));
            var headerRadios = Array.prototype.slice.call(card.querySelectorAll('.em-site-header-picker__items[data-part] input[type="radio"]'));

            function normalizeKey(value) {
                var raw = (value || '').toString().trim().toLowerCase();
                if (!raw) { return ''; }
                if (raw.normalize) {
                    raw = raw.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                }
                return raw.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            }

            var tabs = itemNodes.map(function (item) {
                var summaryItem = item.querySelector('.em-site-collapse__summary');
                var titleItem = item.querySelector('.em-site-item__title');
                var glyph = summaryItem ? summaryItem.querySelector('span.dashicons') : null;
                var glyphClass = glyph ? Array.prototype.find.call(glyph.classList, function (klass) {
                    return klass.indexOf('dashicons-') === 0;
                }) : '';
                var label = titleItem ? titleItem.textContent.replace(/\s+/g, ' ').trim() : '';
                var key = normalizeKey(label);
                var itemSlug = (item.getAttribute('data-item-slug') || '').trim();

                return {
                    node: item,
                    label: label,
                    icon: glyphClass || 'dashicons-align-center',
                    key: key,
                    itemSlug: itemSlug,
                    active: !!item.open
                };
            });

            itemTabs.innerHTML = '';
            tabs.forEach(function (tab) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'em-site-overview__focus-tab' + (tab.active ? ' is-active' : '');
                btn.setAttribute('aria-pressed', tab.active ? 'true' : 'false');
                var iconEl = document.createElement('span');
                iconEl.className = 'em-site-overview__focus-tabicon dashicons ' + tab.icon;
                iconEl.setAttribute('aria-hidden', 'true');
                btn.appendChild(iconEl);
                var textEl = document.createElement('span');
                textEl.className = 'em-site-overview__focus-tablabel';
                textEl.textContent = tab.label;
                btn.appendChild(textEl);
                if (tab.active) {
                    var activeEye = document.createElement('span');
                    activeEye.className = 'em-site-overview__focus-tabactiveicon dashicons dashicons-yes-alt';
                    activeEye.setAttribute('aria-hidden', 'true');
                    btn.appendChild(activeEye);
                }
                btn.addEventListener('click', function () {
                    itemNodes.forEach(function (other) {
                        if (other !== tab.node && other.open) {
                            other.open = false;
                        }
                    });
                    tab.node.open = true;

                    if (headerRadios.length && tab.key) {
                        headerRadios.forEach(function (radio) {
                            if (normalizeKey(radio.value || '') !== tab.key || radio.checked) {
                                return;
                            }
                            radio.checked = true;
                            radio.dispatchEvent(new Event('input', { bubbles: true }));
                            radio.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    }

                    var focusedSlug = wrap.getAttribute('data-focus-slug') || activeSlug || '';
                    if (focusedSlug && window.history && window.history.replaceState) {
                        var itemPart = tab.itemSlug ? ('&item=' + encodeURIComponent(tab.itemSlug)) : '';
                        window.history.replaceState({}, document.title, summaryUrl + '&type=' + encodeURIComponent(focusedSlug) + itemPart);
                    }

                    syncFocusTitle(activeSlug);
                });
                itemTabs.appendChild(btn);
            });

            itemTabs.hidden = tabs.length === 0;
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
                var focusedSlug = wrap.getAttribute('data-focus-slug') || '';
                if (focusedSlug && card.id === ('em-site-card-' + focusedSlug)) {
                    syncFocusTitle(focusedSlug);
                }
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
        setupCreateTypeIconChooser();
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
                    var focusedSlug = wrap.getAttribute('data-focus-slug') || '';
                    if (focusedSlug && card && card.id === ('em-site-card-' + focusedSlug)) {
                        if (window.history && window.history.replaceState) {
                            var activeItem = card.querySelector('.em-site-items > .em-site-item[open]');
                            var activeItemSlug = activeItem ? String(activeItem.getAttribute('data-item-slug') || '').trim() : '';
                            var nextUrl = summaryUrl + '&type=' + encodeURIComponent(focusedSlug);
                            if (activeItemSlug) {
                                nextUrl += '&item=' + encodeURIComponent(activeItemSlug);
                            }
                            window.history.replaceState({}, document.title, nextUrl);
                        }
                        syncFocusTitle(focusedSlug);
                    }
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
        var focusTitleHome = wrap.querySelector('[data-overview-resetitems]');
        if (focusTitleHome) {
            focusTitleHome.addEventListener('click', function () {
                var focusedSlug = wrap.getAttribute('data-focus-slug') || '';
                var card = focusedSlug ? getCardBySlug(focusedSlug) : null;
                if (!card) { return; }
                resetItemsForFocus(card);
                syncCardAddButton(card);
                syncFocusTitle(focusedSlug);
            });
        }
        document.addEventListener('change', function (event) {
            var radio = event.target.closest('.em-site-header-picker__items[data-part] input[type="radio"]');
            if (!radio) { return; }
            var focusedSlug = wrap.getAttribute('data-focus-slug') || '';
            if (!focusedSlug) { return; }
            syncFocusTitle(focusedSlug);
        });
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
    $label = em_site_overview_type_label((string) $slug, $type);
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
            <span class="em-site-card__icon dashicons <?php echo esc_attr(em_site_overview_type_icon((string) $slug, $type)); ?>"></span>
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
    $icon_categories = function_exists('em_site_dashicons_categories')
        ? em_site_dashicons_categories()
        : ['Divers' => ['dashicons-screenoptions']];
    $default_icon = 'dashicons-screenoptions';
    $icon_placeholder = __('Choisi une icône pour ta nouvelle rubrique', 'em-site');
    ?>
    <div class="em-site-collapse em-site-create em-site-create--nochevron em-site-createtype" id="em-site-create-type-panel" hidden>
        <div class="em-site-collapse__body em-site-create__options">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-site-form em-site-create__row">
                <?php wp_nonce_field('em_site_create_type'); ?>
                <input type="hidden" name="action" value="em_site_create_type">
                <span class="em-site-create__label"><span class="dashicons dashicons-screenoptions" aria-hidden="true"></span> <?php esc_html_e('Nom de la rubrique', 'em-site'); ?></span>
                <input type="text" name="type_label" class="regular-text em-site-create__name" placeholder="<?php esc_attr_e('Ex. PARTENAIRES', 'em-site'); ?>" required>
                <div class="em-site-iconchooser" data-iconchooser>
                    <input type="hidden" name="type_icon" value="" data-iconchooser-value data-default-icon="<?php echo esc_attr($default_icon); ?>">
                    <button
                        type="button"
                        class="em-site-iconchooser__trigger"
                        data-iconchooser-trigger
                        aria-expanded="false"
                        aria-haspopup="dialog"
                        aria-label="<?php esc_attr_e('Choisir une icône', 'em-site'); ?>"
                    >
                        <span class="em-site-iconchooser__preview" data-iconchooser-preview>
                            <span class="dashicons <?php echo esc_attr($default_icon); ?>" aria-hidden="true"></span>
                        </span>
                        <span class="em-site-iconchooser__meta">
                            <span class="em-site-iconchooser__meta-label"><?php esc_html_e('Icône', 'em-site'); ?></span>
                            <span class="em-site-iconchooser__meta-name" data-iconchooser-name data-iconchooser-placeholder="<?php echo esc_attr($icon_placeholder); ?>"><?php echo esc_html($icon_placeholder); ?></span>
                        </span>
                        <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                    </button>

                    <div class="em-site-iconchooser__panel" data-iconchooser-panel hidden>
                        <div class="em-site-iconchooser__groups" data-iconchooser-groups>
                            <?php foreach ($icon_categories as $category_label => $category_icons) : ?>
                                <section class="em-site-iconchooser__group" data-iconchooser-group>
                                    <h4 class="em-site-iconchooser__group-title"><?php echo esc_html($category_label); ?></h4>
                                    <div class="em-site-iconchooser__items">
                                        <?php foreach ($category_icons as $icon_name) : ?>
                                            <?php $icon_display = preg_replace('/^dashicons-/', '', (string) $icon_name); ?>
                                            <button
                                                type="button"
                                                class="em-site-iconchooser__item"
                                                data-icon-value="<?php echo esc_attr($icon_name); ?>"
                                                title="<?php echo esc_attr($icon_name); ?>"
                                            >
                                                <span class="dashicons <?php echo esc_attr($icon_name); ?>" aria-hidden="true"></span>
                                                <span class="em-site-iconchooser__item-name"><?php echo esc_html((string) $icon_display); ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
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

