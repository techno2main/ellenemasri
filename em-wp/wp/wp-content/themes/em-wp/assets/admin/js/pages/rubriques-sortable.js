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
        var mapZone = map ? map.querySelector('[data-module-slug="' + moduleSlug + '"]') : null;
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
                if (zoneLabel) {
                    mapZone.insertBefore(mapBadge, zoneLabel);
                } else {
                    mapZone.appendChild(mapBadge);
                }
            } else if (visible && mapBadge) {
                mapBadge.remove();
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

            updateVisibilityUI(moduleSlug, nextVisible);
            saveVisibility(moduleSlug, nextVisible, toggle, previousVisible);
        });
    }

    if (!list || !mapBody || !window.EmWpSlideSortable) {
        return;
    }

    function isHeroPair(slugA, slugB) {
        return (slugA === 'hero' && slugB === 'slider') || (slugA === 'slider' && slugB === 'hero');
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
            if (child.classList.contains('em-wp-admin-landing-map__hero-group')) {
                Array.prototype.forEach.call(
                    child.querySelectorAll('[data-module-slug]'),
                    function (zone) {
                        order.push(zone.getAttribute('data-module-slug') || '');
                    }
                );
                return;
            }

            if (child.matches('[data-module-slug]')) {
                order.push(child.getAttribute('data-module-slug') || '');
            }
        });

        return order.filter(Boolean);
    }

    function unwrapHeroGroups() {
        var groups = mapBody.querySelectorAll('.em-wp-admin-landing-map__hero-group');

        Array.prototype.forEach.call(groups, function (group) {
            var inner = group.querySelector('.em-wp-admin-landing-map__hero-group-inner');
            var zones = inner
                ? Array.prototype.slice.call(inner.querySelectorAll('[data-module-slug]'))
                : Array.prototype.slice.call(group.querySelectorAll('[data-module-slug]'));

            zones.forEach(function (zone) {
                mapBody.insertBefore(zone, group);
            });

            group.remove();
        });
    }

    function createHeroGroup(firstZone, secondZone) {
        var group = document.createElement('div');
        group.className = 'em-wp-admin-landing-map__hero-group is-sortable';

        var toolbar = document.createElement('div');
        toolbar.className = 'em-wp-admin-landing-map__hero-group-toolbar';

        var handle = document.createElement('span');
        handle.className = 'em-wp-rubriques-sortable__handle';
        handle.setAttribute('aria-hidden', 'true');
        handle.innerHTML = '<i class="fa-solid fa-grip-vertical"></i>';

        var swapBtn = document.createElement('button');
        swapBtn.type = 'button';
        swapBtn.className = 'em-wp-admin-landing-map__swap-pair';
        swapBtn.setAttribute('aria-label', config.i18n.swapHeroSlider || 'Inverser HEROS et SLIDERS');
        swapBtn.innerHTML = '<i class="fa-solid fa-right-left" aria-hidden="true"></i>';
        swapBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            swapHeroInGroup(group);
        });

        toolbar.appendChild(handle);
        toolbar.appendChild(swapBtn);

        var inner = document.createElement('div');
        inner.className = 'em-wp-admin-landing-map__hero-group-inner';

        mapBody.insertBefore(group, firstZone);
        group.appendChild(toolbar);
        group.appendChild(inner);
        inner.appendChild(firstZone);
        inner.appendChild(secondZone);

        return group;
    }

    function wrapHeroGroups() {
        unwrapHeroGroups();

        var children = Array.prototype.slice.call(mapBody.children);
        var index = 0;

        while (index < children.length - 1) {
            var current = children[index];
            var next = children[index + 1];

            if (!current.matches('[data-module-slug]') || !next.matches('[data-module-slug]')) {
                index += 1;
                continue;
            }

            var slugA = current.getAttribute('data-module-slug') || '';
            var slugB = next.getAttribute('data-module-slug') || '';

            if (isHeroPair(slugA, slugB)) {
                createHeroGroup(current, next);
                children = Array.prototype.slice.call(mapBody.children);
                index += 1;
                continue;
            }

            index += 1;
        }

        reinitMapSortable();
    }

    function reorderMapFlat(order) {
        unwrapHeroGroups();

        order.forEach(function (slug) {
            var zone = mapBody.querySelector(':scope > [data-module-slug="' + slug + '"]');
            if (zone) {
                mapBody.appendChild(zone);
            }
        });

        wrapHeroGroups();
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

    function swapHeroInGroup(group) {
        var inner = group.querySelector('.em-wp-admin-landing-map__hero-group-inner');
        var zones = inner ? inner.querySelectorAll('[data-module-slug]') : [];

        if (zones.length < 2) {
            return;
        }

        inner.insertBefore(zones[1], zones[0]);
        applyMiddleOrderToList(getMiddleOrderFromMap());
        saveOrder();
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
            item: '.em-wp-admin-landing-map__hero-group.is-sortable, .em-wp-admin-landing-map__zone.is-sortable',
            onEnd: syncFromMap,
        });
    }

    new window.EmWpSlideSortable(list, {
        handle: '.em-wp-rubriques-sortable__handle',
        item: '.em-wp-rubriques-admin__list-item.is-sortable',
        onEnd: syncFromList,
    });

    wrapHeroGroups();
})(window, document);
