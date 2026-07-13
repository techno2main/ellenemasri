(function (window, document) {
    'use strict';

    var ns = window.EmWpRubriquesSortable = window.EmWpRubriquesSortable || {};

    ns.createContext = function () {
        return {
            config: window.emWpRubriquesSortable || {},
            list: document.querySelector('.em-site-rubriques-admin__list'),
            map: document.getElementById('em-site-admin-landing-map'),
            mapBody: document.getElementById('em-site-admin-landing-map-body'),
            statusEl: document.getElementById('em-site-rubriques-sort-status'),
            adminRoot: document.querySelector('.em-site-rubriques-admin'),
            saving: false,
            mapSortable: null,
            visibilitySaving: false,
            layoutSaving: false,
            statusDismissTimer: null,
            STATUS_DISMISS_MS: 3000,
        };
    };

    ns.setStatus = function (ctx, message, isError) {
        if (!ctx.statusEl) {
            return;
        }

        if (ctx.statusDismissTimer) {
            window.clearTimeout(ctx.statusDismissTimer);
            ctx.statusDismissTimer = null;
        }

        ctx.statusEl.textContent = message || '';
        ctx.statusEl.hidden = message === '';
        ctx.statusEl.classList.toggle('is-error', !!isError);

        if (message && !isError) {
            ctx.statusDismissTimer = window.setTimeout(function () {
                ctx.statusDismissTimer = null;
                ns.setStatus(ctx, '', false);
            }, ctx.STATUS_DISMISS_MS);
        }
    };

    ns.updateVisibilityUI = function (ctx, moduleSlug, visible) {
        if (!ctx.list) {
            return;
        }

        var listItem = ctx.list.querySelector('.em-site-rubriques-admin__list-item[data-module-slug="' + moduleSlug + '"]');
        var toggle = listItem ? listItem.querySelector('.em-site-rubriques-visibility-toggle') : null;
        var label = listItem ? listItem.querySelector('.em-site-rubriques-admin__list-label') : null;
        var mapZone = ctx.map ? ctx.map.querySelector('[data-module-slug="' + moduleSlug + '"]:not([data-header-part])') : null;
        var headerGroup = ctx.map ? ctx.map.querySelector('.em-site-admin-landing-map__header-group[data-module-slug="' + moduleSlug + '"]') : null;

        if (
            mapZone
            && (
                mapZone === headerGroup
                || mapZone.classList.contains('em-site-admin-landing-map__header-group')
                || mapZone.classList.contains('em-site-admin-landing-map__header-group-link')
            )
        ) {
            mapZone = null;
        }

        var hiddenLabel = (ctx.config.i18n && ctx.config.i18n.visibilityHiddenLabel) || 'Masque';

        if (listItem) {
            listItem.classList.toggle('is-rubrique-hidden', !visible);
        }

        if (toggle) {
            toggle.classList.toggle('is-hidden', !visible);
            toggle.setAttribute('aria-pressed', visible ? 'false' : 'true');
            toggle.setAttribute(
                'aria-label',
                visible
                    ? ((ctx.config.i18n && ctx.config.i18n.visibilityHidden) || 'Masquer sur le site')
                    : ((ctx.config.i18n && ctx.config.i18n.visibilityShown) || 'Afficher sur le site')
            );

            var icon = toggle.querySelector('i');
            if (icon) {
                icon.className = visible ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
            }
        }

        if (label) {
            var badge = label.querySelector('.em-site-rubriques-admin__hidden-badge');

            if (!visible && !badge) {
                badge = document.createElement('span');
                badge.className = 'em-site-rubriques-admin__hidden-badge';
                badge.textContent = hiddenLabel;
                label.appendChild(badge);
            } else if (visible && badge) {
                badge.remove();
            }
        }

        if (mapZone) {
            mapZone.classList.toggle('is-rubrique-hidden', !visible);

            var mapBadge = mapZone.querySelector('.em-site-admin-landing-map__hidden-badge');
            if (!visible && !mapBadge) {
                mapBadge = document.createElement('span');
                mapBadge.className = 'em-site-admin-landing-map__hidden-badge';
                mapBadge.textContent = hiddenLabel;
                var zoneLabel = mapZone.querySelector('.em-site-admin-landing-map__zone-label');

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

            var toolbar = headerGroup.querySelector('.em-site-admin-landing-map__header-group-toolbar');
            var groupBadge = toolbar ? toolbar.querySelector('.em-site-admin-landing-map__hidden-badge') : null;

            if (!visible && toolbar && !groupBadge) {
                groupBadge = document.createElement('span');
                groupBadge.className = 'em-site-admin-landing-map__hidden-badge';
                groupBadge.textContent = hiddenLabel;
                toolbar.appendChild(groupBadge);
            } else if (visible && groupBadge) {
                groupBadge.remove();
            }
        }
    };

    ns.saveVisibility = function (ctx, moduleSlug, visible, toggle, previousVisible) {
        if (!ctx.config.ajaxUrl || !ctx.config.nonce || ctx.visibilitySaving) {
            return;
        }

        ctx.visibilitySaving = true;

        if (toggle) {
            toggle.disabled = true;
        }

        var body = new window.FormData();
        body.append('action', 'em_site_save_site_rubrique_visibility');
        body.append('nonce', ctx.config.nonce);
        body.append('module_slug', moduleSlug);
        body.append('visible', visible ? '1' : '0');

        if (ctx.config.templateSlug) {
            body.append('template_slug', ctx.config.templateSlug);
        }

        window.fetch(ctx.config.ajaxUrl, {
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
                    throw new Error((payload && payload.data && payload.data.message) || (ctx.config.i18n && ctx.config.i18n.visibilityError));
                }

                ns.setStatus(ctx, (payload.data && payload.data.message) || (ctx.config.i18n && ctx.config.i18n.visibilitySaved), false);

                if (window.EmSitePreviewButton && typeof window.EmSitePreviewButton.markReady === 'function') {
                    window.EmSitePreviewButton.markReady();
                }

                document.dispatchEvent(new window.CustomEvent('emSiteDraftChanged', {
                    detail: {
                        source: 'rubrique-visibility',
                        moduleSlug: moduleSlug,
                    },
                }));
            })
            .catch(function () {
                ns.updateVisibilityUI(ctx, moduleSlug, previousVisible);
                ns.setStatus(ctx, (ctx.config.i18n && ctx.config.i18n.visibilityError) || 'Impossible d\'enregistrer la visibilite.', true);
            })
            .finally(function () {
                ctx.visibilitySaving = false;

                if (toggle) {
                    toggle.disabled = false;
                }
            });
    };

    ns.bindVisibility = function (ctx) {
        if (!ctx.adminRoot) {
            return;
        }

        ctx.adminRoot.addEventListener('click', function (event) {
            var toggle = event.target.closest('.em-site-rubriques-visibility-toggle');
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
                ns.updateVisibilityUI(ctx, moduleSlug, nextVisible);
            } catch (error) {
                window.console.error(error);
            }

            ns.saveVisibility(ctx, moduleSlug, nextVisible, toggle, previousVisible);
        });
    };
})(window, document);
