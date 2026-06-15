(function (window, document) {
    'use strict';

    var config = window.emWpRubriquesSortable || {};
    var list = document.querySelector('.em-wp-rubriques-admin__list');
    var map = document.getElementById('em-wp-admin-landing-map');
    var mapBody = document.getElementById('em-wp-admin-landing-map-body');
    var statusEl = document.getElementById('em-wp-rubriques-sort-status');
    var adminRoot = document.querySelector('.em-wp-rubriques-admin');
    var saving = false;
    var mapSortable = null;
    var visibilitySaving = false;
    var layoutSaving = false;

    function setStatus(message, isError) {
        if (!statusEl) {
            return;
        }

        statusEl.textContent = message || '';
        statusEl.hidden = message === '';
        statusEl.classList.toggle('is-error', !!isError);
    }

    function updateVisibilityUI(moduleSlug, visible) {
        if (!list) {
            return;
        }

        var listItem = list.querySelector('.em-wp-rubriques-admin__list-item[data-module-slug="' + moduleSlug + '"]');
        var toggle = listItem ? listItem.querySelector('.em-wp-rubriques-visibility-toggle') : null;
        var label = listItem ? listItem.querySelector('.em-wp-rubriques-admin__list-label') : null;
        var mapZone = map ? map.querySelector('[data-module-slug="' + moduleSlug + '"]:not([data-header-part])') : null;
        var headerGroup = map ? map.querySelector('.em-wp-admin-landing-map__header-group[data-module-slug="' + moduleSlug + '"]') : null;

        // HEADER : le groupe entier matche aussi data-module-slug — ne pas le traiter comme une zone plate.
        if (
            mapZone
            && (
                mapZone === headerGroup
                || mapZone.classList.contains('em-wp-admin-landing-map__header-group')
                || mapZone.classList.contains('em-wp-admin-landing-map__header-group-link')
            )
        ) {
            mapZone = null;
        }
        var hiddenLabel = (config.i18n && config.i18n.visibilityHiddenLabel) || 'Masqué';

        if (listItem) {
            listItem.classList.toggle('is-rubrique-hidden', !visible);
        }

        if (toggle) {
            toggle.classList.toggle('is-hidden', !visible);
            toggle.setAttribute('aria-pressed', visible ? 'false' : 'true');
            toggle.setAttribute(
                'aria-label',
                visible
                    ? ((config.i18n && config.i18n.visibilityHidden) || 'Masquer sur le site')
                    : ((config.i18n && config.i18n.visibilityShown) || 'Afficher sur le site')
            );

            var icon = toggle.querySelector('i');
            if (icon) {
                icon.className = visible ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
            }
        }

        if (label) {
            var badge = label.querySelector('.em-wp-rubriques-admin__hidden-badge');

            if (!visible && !badge) {
                badge = document.createElement('span');
                badge.className = 'em-wp-rubriques-admin__hidden-badge';
                badge.textContent = hiddenLabel;
                label.appendChild(badge);
            } else if (visible && badge) {
                badge.remove();
            }
        }

        if (mapZone) {
            mapZone.classList.toggle('is-rubrique-hidden', !visible);

            var mapBadge = mapZone.querySelector('.em-wp-admin-landing-map__hidden-badge');
            if (!visible && !mapBadge) {
                mapBadge = document.createElement('span');
                mapBadge.className = 'em-wp-admin-landing-map__hidden-badge';
                mapBadge.textContent = hiddenLabel;
                var zoneLabel = mapZone.querySelector('.em-wp-admin-landing-map__zone-label');

                if (zoneLabel && zoneLabel.parentNode === mapZone) {
                    mapZone.insertBefore(mapBadge, zoneLabel);
                } else {
                    mapZone.appendChild(mapBadge);
                }
            } else if (visible && mapBadge) {
                mapBadge.remove();
            }
        }

        if (headerGroup) {
            headerGroup.classList.toggle('is-rubrique-hidden', !visible);

            var toolbar = headerGroup.querySelector('.em-wp-admin-landing-map__header-group-toolbar');
            var groupBadge = toolbar ? toolbar.querySelector('.em-wp-admin-landing-map__hidden-badge') : null;

            if (!visible && toolbar && !groupBadge) {
                groupBadge = document.createElement('span');
                groupBadge.className = 'em-wp-admin-landing-map__hidden-badge';
                groupBadge.textContent = hiddenLabel;
                toolbar.appendChild(groupBadge);
            } else if (visible && groupBadge) {
                groupBadge.remove();
            }
        }
    }

    function saveVisibility(moduleSlug, visible, toggle, previousVisible) {
        if (!config.ajaxUrl || !config.nonce || visibilitySaving) {
            return;
        }

        visibilitySaving = true;

        if (toggle) {
            toggle.disabled = true;
        }

        var body = new window.FormData();
        body.append('action', 'em_wp_save_site_rubrique_visibility');
        body.append('nonce', config.nonce);
        body.append('module_slug', moduleSlug);
        body.append('visible', visible ? '1' : '0');

        if (config.templateSlug) {
            body.append('template_slug', config.templateSlug);
        }

        window.fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success) {
                    throw new Error((payload && payload.data && payload.data.message) || (config.i18n && config.i18n.visibilityError));
                }

                setStatus((payload.data && payload.data.message) || (config.i18n && config.i18n.visibilitySaved), false);
            })
            .catch(function () {
                updateVisibilityUI(moduleSlug, previousVisible);
                setStatus((config.i18n && config.i18n.visibilityError) || 'Impossible d\'enregistrer la visibilité.', true);
            })
            .finally(function () {
                visibilitySaving = false;

                if (toggle) {
                    toggle.disabled = false;
                }
            });
    }

    if (adminRoot) {
        adminRoot.addEventListener('click', function (event) {
            var toggle = event.target.closest('.em-wp-rubriques-visibility-toggle');
            if (!toggle) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var moduleSlug = toggle.getAttribute('data-module-slug') || '';
            if (moduleSlug === '') {
                return;
            }

            var previousVisible = !toggle.classList.contains('is-hidden');
            var nextVisible = !previousVisible;

            try {
                updateVisibilityUI(moduleSlug, nextVisible);
            } catch (error) {
                window.console.error(error);
            }

            saveVisibility(moduleSlug, nextVisible, toggle, previousVisible);
        });
    }

    if (!list || !mapBody || !window.EmWpSlideSortable) {
        return;
    }

    function getFullOrderFromList() {
        return Array.prototype.map.call(
            list.querySelectorAll('.em-wp-rubriques-admin__list-item[data-module-slug]'),
            function (item) {
                return item.getAttribute('data-module-slug') || '';
            }
        ).filter(Boolean);
    }

    function getMiddleOrderFromList() {
        return Array.prototype.map.call(
            list.querySelectorAll('.em-wp-rubriques-admin__list-item.is-sortable[data-module-slug]'),
            function (item) {
                return item.getAttribute('data-module-slug') || '';
            }
        ).filter(Boolean);
    }

    function getMiddleOrderFromMap() {
        var order = [];

        Array.prototype.forEach.call(mapBody.children, function (child) {
            if (child.classList.contains('em-wp-admin-landing-map__header-group')) {
                order.push('header');
                return;
            }

            if (child.matches('[data-module-slug]')) {
                order.push(child.getAttribute('data-module-slug') || '');
            }
        });

        return order.filter(Boolean);
    }

    function reorderMapFlat(order) {
        order.forEach(function (slug) {
            if (slug === 'header') {
                var group = mapBody.querySelector('.em-wp-admin-landing-map__header-group');
                if (group) {
                    mapBody.appendChild(group);
                }
                return;
            }

            var zone = mapBody.querySelector(':scope > [data-module-slug="' + slug + '"]');
            if (zone) {
                mapBody.appendChild(zone);
            }
        });
    }

    function applyMiddleOrderToList(order) {
        var footerItem = list.querySelector('[data-module-slug="footer"]');

        order.forEach(function (slug) {
            var item = list.querySelector('.em-wp-rubriques-admin__list-item.is-sortable[data-module-slug="' + slug + '"]');
            if (!item) {
                return;
            }

            if (footerItem) {
                list.insertBefore(item, footerItem);
            } else {
                list.appendChild(item);
            }
        });
    }

    function saveOrder() {
        if (saving || !config.ajaxUrl || !config.nonce) {
            return;
        }

        saving = true;
        setStatus('', false);

        var body = new window.FormData();
        body.append('action', 'em_wp_save_site_rubrique_order');
        body.append('nonce', config.nonce);
        body.append('order', JSON.stringify(getFullOrderFromList()));

        window.fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success) {
                    throw new Error((payload && payload.data && payload.data.message) || config.i18n.error);
                }

                setStatus((payload.data && payload.data.message) || config.i18n.saved, false);
            })
            .catch(function () {
                setStatus(config.i18n.error, true);
            })
            .finally(function () {
                saving = false;
            });
    }

    function saveHeaderLayout(layout) {
        if (layoutSaving || !config.ajaxUrl || !config.nonce) {
            return;
        }

        layoutSaving = true;

        var body = new window.FormData();
        body.append('action', 'em_wp_save_header_layout');
        body.append('nonce', config.nonce);
        body.append('layout', layout);

        if (config.templateSlug) {
            body.append('template_slug', config.templateSlug);
        }

        window.fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success) {
                    throw new Error((payload && payload.data && payload.data.message) || (config.i18n && config.i18n.layoutError));
                }

                setStatus((payload.data && payload.data.message) || (config.i18n && config.i18n.layoutSaved), false);
            })
            .catch(function () {
                setStatus((config.i18n && config.i18n.layoutError) || 'Impossible d\'enregistrer le layout HEADER.', true);
            })
            .finally(function () {
                layoutSaving = false;
            });
    }

    function swapHeaderLayout(group) {
        var inner = group.querySelector('.em-wp-admin-landing-map__header-group-inner');
        if (!inner) {
            return;
        }

        var currentLayout = group.getAttribute('data-header-layout') || 'hero_left';
        var nextLayout = currentLayout === 'slider_left' ? 'hero_left' : 'slider_left';
        var hero = inner.querySelector('.em-wp-admin-landing-map__header-hero');
        var slider = inner.querySelector('.em-wp-admin-landing-map__header-slider');

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
        saveHeaderLayout(nextLayout);
    }

    function syncFromList() {
        reorderMapFlat(getMiddleOrderFromList());
        saveOrder();
    }

    function syncFromMap() {
        var order = getMiddleOrderFromMap();
        applyMiddleOrderToList(order);
        reorderMapFlat(order);
        saveOrder();
    }

    function reinitMapSortable() {
        if (mapSortable) {
            mapSortable.destroy();
        }

        mapSortable = new window.EmWpSlideSortable(mapBody, {
            handle: '.em-wp-rubriques-sortable__handle',
            item: '.em-wp-admin-landing-map__header-group.is-sortable, .em-wp-admin-landing-map__zone.is-sortable',
            onEnd: syncFromMap,
        });
    }

    if (mapBody) {
        mapBody.addEventListener('click', function (event) {
            var swapBtn = event.target.closest('.em-wp-admin-landing-map__swap-header');
            if (!swapBtn) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var group = swapBtn.closest('.em-wp-admin-landing-map__header-group');
            if (!group || group.getAttribute('data-header-can-swap') !== '1') {
                return;
            }

            swapHeaderLayout(group);
        });
    }

    new window.EmWpSlideSortable(list, {
        handle: '.em-wp-rubriques-sortable__handle',
        item: '.em-wp-rubriques-admin__list-item.is-sortable',
        onEnd: syncFromList,
    });

    reinitMapSortable();
})(window, document);
