(function (window, document) {
    'use strict';

    var ns = window.EmWpRubriquesSortable = window.EmWpRubriquesSortable || {};

    function getFullOrderFromList(ctx) {
        return Array.prototype.map.call(
            ctx.list.querySelectorAll('.em-site-rubriques-admin__list-item[data-module-slug]'),
            function (item) {
                return item.getAttribute('data-module-slug') || '';
            }
        ).filter(Boolean);
    }

    function getMiddleOrderFromList(ctx) {
        return Array.prototype.map.call(
            ctx.list.querySelectorAll('.em-site-rubriques-admin__list-item.is-sortable[data-module-slug]'),
            function (item) {
                return item.getAttribute('data-module-slug') || '';
            }
        ).filter(Boolean);
    }

    function getMiddleOrderFromMap(ctx) {
        var order = [];

        Array.prototype.forEach.call(ctx.mapBody.children, function (child) {
            if (child.classList.contains('em-site-admin-landing-map__header-group')) {
                order.push('header');
                return;
            }

            if (child.matches('[data-module-slug]')) {
                order.push(child.getAttribute('data-module-slug') || '');
            }
        });

        return order.filter(Boolean);
    }

    function sameArray(left, right) {
        return JSON.stringify(left || []) === JSON.stringify(right || []);
    }

    function getCurrentHeaderLayout(ctx) {
        var group = ctx.mapBody ? ctx.mapBody.querySelector('.em-site-admin-landing-map__header-group') : null;
        return group ? (group.getAttribute('data-header-layout') || 'hero_left') : 'hero_left';
    }

    function reorderMapFlat(ctx, order) {
        order.forEach(function (slug) {
            if (slug === 'header') {
                var group = ctx.mapBody.querySelector('.em-site-admin-landing-map__header-group');
                if (group) {
                    ctx.mapBody.appendChild(group);
                }
                return;
            }

            var zone = ctx.mapBody.querySelector(':scope > [data-module-slug="' + slug + '"]');
            if (zone) {
                ctx.mapBody.appendChild(zone);
            }
        });
    }

    function applyMiddleOrderToList(ctx, order) {
        var footerItem = ctx.list.querySelector('[data-module-slug="footer"]');

        order.forEach(function (slug) {
            var item = ctx.list.querySelector('.em-site-rubriques-admin__list-item.is-sortable[data-module-slug="' + slug + '"]');
            if (!item) {
                return;
            }

            if (footerItem) {
                ctx.list.insertBefore(item, footerItem);
            } else {
                ctx.list.appendChild(item);
            }
        });
    }

    function saveOrder(ctx) {
        if (ctx.saving || !ctx.config.ajaxUrl || !ctx.config.nonce) {
            return;
        }

        ctx.saving = true;
        ns.setStatus(ctx, '', false);

        var body = new window.FormData();
        body.append('action', 'em_site_save_site_rubrique_order');
        body.append('nonce', ctx.config.nonce);
        body.append('order', JSON.stringify(getFullOrderFromList(ctx)));

        if (ctx.config.templateSlug) {
            body.append('template_slug', ctx.config.templateSlug);
        }

        window.fetch(ctx.config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success) {
                    throw new Error((payload && payload.data && payload.data.message) || ctx.config.i18n.error);
                }

                ns.setStatus(ctx, (payload.data && payload.data.message) || ctx.config.i18n.saved, false);

                var hasPendingChanges = (payload && payload.data && typeof payload.data.has_pending_changes === 'boolean')
                    ? payload.data.has_pending_changes
                    : !sameArray(getFullOrderFromList(ctx), ctx.initialOrder || []);

                document.dispatchEvent(new window.CustomEvent('emSiteDraftChanged', {
                    detail: {
                        source: 'rubrique-order',
                        rubriqueSlug: 'rubrique-order',
                        draftKey: 'rubrique-order:' + (ctx.config.templateSlug || 'default'),
                        hasPendingChanges: hasPendingChanges,
                    },
                }));
            })
            .catch(function () {
                ns.setStatus(ctx, ctx.config.i18n.error, true);
            })
            .finally(function () {
                ctx.saving = false;
            });
    }

    function saveHeaderLayout(ctx, layout) {
        if (ctx.layoutSaving || !ctx.config.ajaxUrl || !ctx.config.nonce) {
            return;
        }

        ctx.layoutSaving = true;

        var body = new window.FormData();
        body.append('action', 'em_site_save_header_layout');
        body.append('nonce', ctx.config.nonce);
        body.append('layout', layout);

        if (ctx.config.templateSlug) {
            body.append('template_slug', ctx.config.templateSlug);
        }

        window.fetch(ctx.config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success) {
                    throw new Error((payload && payload.data && payload.data.message) || (ctx.config.i18n && ctx.config.i18n.layoutError));
                }

                ns.setStatus(ctx, (payload.data && payload.data.message) || (ctx.config.i18n && ctx.config.i18n.layoutSaved), false);

                document.dispatchEvent(new window.CustomEvent('emSiteDraftChanged', {
                    detail: {
                        source: 'header-layout',
                        rubriqueSlug: 'header-layout',
                        draftKey: 'header-layout:' + (ctx.config.templateSlug || 'default'),
                        hasPendingChanges: getCurrentHeaderLayout(ctx) !== (ctx.initialHeaderLayout || 'hero_left'),
                    },
                }));
            })
            .catch(function () {
                ns.setStatus(ctx, (ctx.config.i18n && ctx.config.i18n.layoutError) || 'Impossible d\'enregistrer le layout HEADER.', true);
            })
            .finally(function () {
                ctx.layoutSaving = false;
            });
    }

    function swapHeaderLayout(ctx, group) {
        var inner = group.querySelector('.em-site-admin-landing-map__header-group-inner');
        if (!inner) {
            return;
        }

        var currentLayout = group.getAttribute('data-header-layout') || 'hero_left';
        var nextLayout = currentLayout === 'slider_left' ? 'hero_left' : 'slider_left';
        var hero = inner.querySelector('.em-site-admin-landing-map__header-hero');
        var slider = inner.querySelector('.em-site-admin-landing-map__header-slider');

        if (hero && slider) {
            if (nextLayout === 'slider_left') {
                inner.insertBefore(slider, hero);
            } else {
                inner.insertBefore(hero, slider);
            }
        } else {
            var zones = inner.querySelectorAll('[data-header-part]');

            if (zones.length < 2) {
                return;
            }

            inner.insertBefore(zones[1], zones[0]);
        }

        group.setAttribute('data-header-layout', nextLayout);
        saveHeaderLayout(ctx, nextLayout);
    }

    function syncFromList(ctx) {
        reorderMapFlat(ctx, getMiddleOrderFromList(ctx));
        saveOrder(ctx);
    }

    function syncFromMap(ctx) {
        var order = getMiddleOrderFromMap(ctx);
        applyMiddleOrderToList(ctx, order);
        reorderMapFlat(ctx, order);
        saveOrder(ctx);
    }

    function reinitMapSortable(ctx) {
        if (ctx.mapSortable) {
            ctx.mapSortable.destroy();
        }

        ctx.mapSortable = new window.EmWpSlideSortable(ctx.mapBody, {
            handle: '.em-site-rubriques-sortable__handle',
            item: '.em-site-admin-landing-map__header-group.is-sortable, .em-site-admin-landing-map__zone.is-sortable',
            onEnd: function () {
                syncFromMap(ctx);
            },
        });
    }

    ns.bindOrdering = function (ctx) {
        if (!ctx.list || !ctx.mapBody || !window.EmWpSlideSortable) {
            return;
        }

        ctx.initialOrder = getFullOrderFromList(ctx);
        ctx.initialHeaderLayout = getCurrentHeaderLayout(ctx);

        ctx.mapBody.addEventListener('click', function (event) {
            var swapBtn = event.target.closest('.em-site-admin-landing-map__swap-header');
            if (!swapBtn) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var group = swapBtn.closest('.em-site-admin-landing-map__header-group');
            if (!group || group.getAttribute('data-header-can-swap') !== '1') {
                return;
            }

            swapHeaderLayout(ctx, group);
        });

        new window.EmWpSlideSortable(ctx.list, {
            handle: '.em-site-rubriques-sortable__handle',
            item: '.em-site-rubriques-admin__list-item.is-sortable',
            onEnd: function () {
                syncFromList(ctx);
            },
        });

        reinitMapSortable(ctx);
    };
})(window, document);
