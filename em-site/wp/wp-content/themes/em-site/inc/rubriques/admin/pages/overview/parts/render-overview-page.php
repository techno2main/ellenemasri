<?php
function em_site_overview_render(): void
{
    $types = em_site_ordered_types();
    // Rubrique ciblée par le sous-menu de gauche (…&type=<slug>) : on ouvre sa carte.
    $open_type = sanitize_key((string) ($_GET['type'] ?? ''));
    $active_type = ($open_type !== '' && isset($types[$open_type])) ? $open_type : '';
    $breadcrumb = [];
    if (function_exists('em_site_admin_hub_breadcrumb_crumb')) {
        $breadcrumb[] = em_site_admin_hub_breadcrumb_crumb(__('Mes Rubriques', 'em-site'));
    }
    $rubriques_icon = function_exists('em_site_site_icon') ? em_site_site_icon('rubriques', 'dashicons-screenoptions') : 'dashicons-screenoptions';
    ?>
    <div class="wrap em-site-overview em-site-admin-module em-site-hub-sommaire<?php echo $active_type !== '' ? ' is-focus-mode' : ''; ?>" data-initial-focus="<?php echo esc_attr($active_type); ?>">
        <?php
        if (function_exists('em_site_admin_hub_render_sommaire_header')) {
            em_site_admin_hub_render_sommaire_header('', $rubriques_icon, false, true, null, $breadcrumb, false);
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
