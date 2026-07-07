(function () {
    'use strict';

    window.EmWpTemplateWizard = window.EmWpTemplateWizard || {};

    var State = EmWpTemplateWizard.State;
    var Confirm = EmWpTemplateWizard.Confirm;
    var Guide = EmWpTemplateWizard.Guide;

    function canRemove(slug, def) {
        if (!def) {
            return false;
        }
        if (def.required) {
            return false;
        }
        if (def.pinned === 'top' || def.pinned === 'bottom') {
            return false;
        }
        return true;
    }

    function getAvailableRubriques(order, defs) {
        var available = [];

        Object.keys(defs).forEach(function (slug) {
            var def = defs[slug];

            if (order.indexOf(slug) !== -1) {
                return;
            }

            if (def.comingSoon && def.pinned) {
                return;
            }

            var hub = def.catalogHub;

            if (hub && (!State.config.catalogs[hub] || !State.config.catalogs[hub].length)) {
                return;
            }

            available.push({
                slug: slug,
                label: def.label || slug,
            });
        });

        return available;
    }

    function insertRubriqueAt(draft, slug, beforeSlug) {
        var def = State.getRubriqueDef(slug);

        if (!def) {
            return false;
        }

        var order = draft.skeleton.order;
        var insertIdx = -1;

        if (beforeSlug) {
            insertIdx = order.indexOf(beforeSlug);
        }

        if (insertIdx === -1) {
            var footerIdx = order.indexOf('footer');
            insertIdx = footerIdx === -1 ? order.length : footerIdx;
        }

        order.splice(insertIdx, 0, slug);

        draft.skeleton.rubriques[slug] = {
            background_color: def.defaultBg,
            text_color: def.defaultText,
            enabled: def.defaultEnabled !== false,
        };

        if (def.needsCatalogPick) {
            var row = {};
            row[def.pointerKey] = '';
            var choices = State.config.catalogs[def.catalogHub] || [];

            if (choices.length) {
                row[def.pointerKey] = choices[0].slug;
            }

            draft.catalog[slug] = row;
        }

        return true;
    }

    function insertRubrique(draft, slug) {
        return insertRubriqueAt(draft, slug, null);
    }

    function isValidSkeletonOrder(order) {
        if (!order || !order.length) {
            return false;
        }

        if (order[0] !== 'top-bar' || order[order.length - 1] !== 'footer') {
            return false;
        }

        return order.indexOf('header') > 0;
    }

    function getTemplateAccentColor() {
        var draft = State.getDraft();
        var colorInput = document.getElementById('em-wp-template-new-color');
        var color = draft && draft.color ? draft.color : '';

        if (!color && colorInput) {
            color = String(colorInput.value || '').trim();
        }

        if (!color && Guide && Guide.getColorValue) {
            color = Guide.getColorValue();
        }

        return color || '#751820';
    }

    function renderPicker(pickerListEl, pickerEmptyEl, addWrap, order, defs) {
        var available = getAvailableRubriques(order, defs);
        var pickerRow = addWrap ? addWrap.querySelector('.em-wp-template-wizard-skeleton__picker-row') : null;
        var accentColor = getTemplateAccentColor();

        if (!pickerListEl) {
            return available.length;
        }

        pickerListEl.innerHTML = '';

        if (pickerEmptyEl) {
            pickerEmptyEl.hidden = available.length > 0;
        }

        pickerListEl.hidden = available.length === 0;
        pickerListEl.style.setProperty('--em-wp-skeleton-accent', accentColor);

        if (pickerRow) {
            pickerRow.hidden = available.length === 0;
        }

        if (addWrap) {
            addWrap.hidden = available.length === 0;
        }

        available.forEach(function (item) {
            var li = document.createElement('li');
            li.className = 'em-wp-template-wizard-skeleton__picker-item';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'em-wp-template-wizard-skeleton__picker-add';
            btn.value = item.slug;
            btn.setAttribute('data-rubrique-slug', item.slug);
            btn.setAttribute('draggable', 'false');
            btn.setAttribute(
                'aria-label',
                ((State.config.i18n && State.config.i18n.addRubrique) || 'Ajouter %s').replace('%s', item.label)
            );

            var icon = document.createElement('span');
            icon.className = 'em-wp-template-wizard-skeleton__picker-add-icon';
            icon.setAttribute('aria-hidden', 'true');

            var text = document.createElement('span');
            text.className = 'em-wp-template-wizard-skeleton__picker-label';
            text.textContent = item.label;

            btn.appendChild(icon);
            btn.appendChild(text);
            li.appendChild(btn);
            pickerListEl.appendChild(li);
        });

        return available.length;
    }

    EmWpTemplateWizard.Wireframe = {
        host: null,
        pickerListEl: null,
        pickerEmptyEl: null,
        addWrap: null,
        sortable: null,
        rendering: false,
        pickerDragBound: false,
        pickerDragSuppressClick: false,
        pickerDrag: null,

        destroySortable: function () {
            if (this.sortable) {
                this.sortable.destroy();
                this.sortable = null;
            }
        },

        getMapBody: function () {
            return this.host ? this.host.querySelector('#em-wp-template-wizard-map-body') : null;
        },

        getMapRoot: function () {
            return this.host ? this.host.querySelector('.em-wp-admin-landing-map--wizard-edit') : null;
        },

        hasMapContent: function () {
            return !!this.getMapRoot();
        },

        getMiddleOrderFromMap: function () {
            var mapBody = this.getMapBody();
            var order = [];

            if (!mapBody) {
                return order;
            }

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
        },

        buildFullOrder: function (middleOrder) {
            var draft = State.getDraft();
            var previous = draft && draft.skeleton ? draft.skeleton.order || [] : [];
            var full = [];

            if (previous.indexOf('top-bar') !== -1) {
                full.push('top-bar');
            }

            middleOrder.forEach(function (slug) {
                if (slug && full.indexOf(slug) === -1) {
                    full.push(slug);
                }
            });

            if (previous.indexOf('footer') !== -1) {
                full.push('footer');
            }

            return full;
        },

        applyOrderFromMap: function () {
            var draft = State.getDraft();
            var mapBody = this.getMapBody();

            if (!draft || !mapBody) {
                return;
            }

            var middleOrder = this.getMiddleOrderFromMap();
            var order = this.buildFullOrder(middleOrder);

            if (!isValidSkeletonOrder(order)) {
                this.render();
                return;
            }

            var previous = (draft.skeleton.order || []).join(',');

            if (previous === order.join(',')) {
                return;
            }

            draft.skeleton.order = order;
            State.markDirty();
        },

        findMapZone: function (slug) {
            var mapBody = this.getMapBody();
            var mapRoot = this.getMapRoot();

            if (!slug || !mapBody) {
                return null;
            }

            if (slug === 'header') {
                return mapBody.querySelector('.em-wp-admin-landing-map__header-group');
            }

            return mapBody.querySelector(':scope > [data-module-slug="' + slug + '"]')
                || (mapRoot ? mapRoot.querySelector('[data-module-slug="' + slug + '"]:not([data-header-part])') : null);
        },

        injectRemoveButton: function (zone, slug) {
            var defs = State.config.rubriques || {};
            var def = defs[slug];
            var i18n = State.config.i18n || {};

            if (!zone || !canRemove(slug, def) || zone.querySelector('.em-wp-template-wizard-wireframe__remove')) {
                return;
            }

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'em-wp-template-wizard-wireframe__remove';
            btn.setAttribute(
                'aria-label',
                (i18n.removeRubriqueLabel || 'Retirer %s').replace('%s', def ? def.label : slug)
            );
            btn.innerHTML = '<i class="fa-solid fa-trash-can" aria-hidden="true"></i>';
            zone.appendChild(btn);
        },

        injectRemoveButtons: function () {
            var defs = State.config.rubriques || {};
            var map = this.getMapRoot();

            if (!map) {
                return;
            }

            map.querySelectorAll('[data-module-slug]').forEach(function (zone) {
                var slug = zone.getAttribute('data-module-slug') || '';

                if (zone.classList.contains('em-wp-admin-landing-map__header-group') && slug !== 'header') {
                    return;
                }

                if (zone.hasAttribute('data-header-part')) {
                    return;
                }

                EmWpTemplateWizard.Wireframe.injectRemoveButton(zone, slug);
            });
        },

        insertZoneNodeForSlug: function (zoneNode, slug) {
            var mapBody = this.getMapBody();
            var draft = State.getDraft();

            if (!mapBody || !zoneNode || !draft) {
                return false;
            }

            var order = draft.skeleton.order || [];
            var slugIndex = order.indexOf(slug);

            if (slugIndex === -1) {
                return false;
            }

            var insertBefore = null;
            var i;

            for (i = slugIndex + 1; i < order.length; i += 1) {
                var nextSlug = order[i];

                if (nextSlug === 'footer' || nextSlug === 'top-bar') {
                    continue;
                }

                insertBefore = this.findMapZone(nextSlug);

                if (insertBefore) {
                    break;
                }
            }

            if (insertBefore) {
                mapBody.insertBefore(zoneNode, insertBefore);
            } else {
                mapBody.appendChild(zoneNode);
            }

            this.injectRemoveButton(zoneNode, slug);
            return true;
        },

        mountHtml: function (html) {
            this.destroySortable();
            this.host.innerHTML = html || '';
            this.injectRemoveButtons();
            this.initSortable();
            this.renderPicker();

            if (Guide && Guide.syncWireframeActions) {
                Guide.syncWireframeActions(Guide.currentStep);
            }
        },

        fetchHtml: function () {
            var draft = State.getDraft();
            var cfg = State.config;

            if (!draft) {
                return Promise.reject(new Error('no draft'));
            }

            var payload = JSON.parse(JSON.stringify(draft));
            payload.wireframeEditable = true;

            var body = new FormData();
            body.append('action', 'em_wp_template_wizard_wireframe');
            body.append('nonce', cfg.wireframeNonce || '');
            body.append('payload', JSON.stringify(payload));

            return fetch(cfg.ajaxUrl || '', {
                method: 'POST',
                credentials: 'same-origin',
                body: body,
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (json) {
                    if (!json || !json.success) {
                        throw new Error((json && json.data && json.data.message) || 'error');
                    }

                    return json.data.html || '';
                });
        },

        syncAddedZone: function (slug) {
            var self = this;

            return this.fetchHtml().then(function (html) {
                var temp = document.createElement('div');
                temp.innerHTML = html;

                var sourceBody = temp.querySelector('#em-wp-template-wizard-map-body');

                if (!sourceBody) {
                    self.mountHtml(html);
                    return;
                }

                var sourceZone = null;

                if (slug === 'header') {
                    sourceZone = sourceBody.querySelector('.em-wp-admin-landing-map__header-group');
                } else {
                    sourceZone = sourceBody.querySelector(':scope > [data-module-slug="' + slug + '"]');
                }

                if (!sourceZone) {
                    self.mountHtml(html);
                    return;
                }

                if (!self.insertZoneNodeForSlug(sourceZone, slug)) {
                    self.mountHtml(html);
                    return;
                }

                self.initSortable();
            });
        },

        initSortable: function () {
            this.destroySortable();

            var mapBody = this.getMapBody();

            if (!mapBody || !window.EmWpSlideSortable) {
                return;
            }

            if (!mapBody.querySelector('.is-sortable')) {
                return;
            }

            var self = this;
            var mapRoot = mapBody.closest('.em-wp-admin-landing-map');
            var footerZone = mapRoot ? mapRoot.querySelector('[data-module-slug="footer"]') : null;

            this.sortable = new window.EmWpSlideSortable(mapBody, {
                handle: '.em-wp-rubriques-sortable__handle',
                item: '.em-wp-admin-landing-map__header-group.is-sortable, .em-wp-admin-landing-map__zone.is-sortable',
                onEnd: function () {
                    self.applyOrderFromMap();
                    self.renderPicker();
                },
            });

            var baseUpdate = this.sortable.updatePlaceholderPosition.bind(this.sortable);

            this.sortable.updatePlaceholderPosition = function (clientY) {
                baseUpdate(clientY);

                if (!this.placeholder || !this.placeholder.parentNode || !footerZone || !mapRoot) {
                    return;
                }

                var footerInMap = mapRoot.querySelector('[data-module-slug="footer"]');
                var children = Array.from(this.container.children);
                var placeholderIndex = children.indexOf(this.placeholder);

                if (footerInMap && placeholderIndex >= children.length - 1) {
                    var lastChild = this.container.lastElementChild;

                    if (lastChild && lastChild !== this.placeholder) {
                        this.container.insertBefore(this.placeholder, lastChild.nextSibling);
                    }
                }
            };
        },

        renderPicker: function () {
            var draft = State.getDraft();

            if (!draft) {
                return;
            }

            renderPicker(
                this.pickerListEl,
                this.pickerEmptyEl,
                this.addWrap,
                draft.skeleton.order || [],
                State.config.rubriques || {}
            );
        },

        addRubriqueFromPicker: function (slug, beforeSlug) {
            var draft = State.getDraft();

            if (!draft || !slug || draft.skeleton.order.indexOf(slug) !== -1) {
                return false;
            }

            if (!insertRubriqueAt(draft, slug, beforeSlug || null)) {
                return false;
            }

            State.markDirty();
            return true;
        },

        completePickerAdd: function (slug, beforeSlug) {
            if (!this.addRubriqueFromPicker(slug, beforeSlug)) {
                return;
            }

            this.renderPicker();

            if (this.hasMapContent()) {
                var self = this;
                this.syncAddedZone(slug).catch(function () {
                    self.render();
                });
            } else {
                this.render();
            }
        },

        getInsertBeforeSlugFromPlaceholder: function () {
            var mapBody = this.getMapBody();
            var placeholder = mapBody ? mapBody.querySelector('.em-wp-slide-sortable__placeholder') : null;

            if (!mapBody || !placeholder) {
                return null;
            }

            var next = placeholder.nextElementSibling;

            while (next) {
                if (next.classList.contains('em-wp-admin-landing-map__header-group')) {
                    return 'header';
                }

                if (next.matches('[data-module-slug]')) {
                    return next.getAttribute('data-module-slug') || null;
                }

                next = next.nextElementSibling;
            }

            return null;
        },

        isPointerOverMapBody: function (clientX, clientY) {
            var mapBody = this.getMapBody();

            if (!mapBody) {
                return false;
            }

            var rect = mapBody.getBoundingClientRect();

            return clientX >= rect.left
                && clientX <= rect.right
                && clientY >= rect.top
                && clientY <= rect.bottom;
        },

        updatePickerDropPlaceholder: function (clientY) {
            var mapBody = this.getMapBody();
            var drag = this.pickerDrag;

            if (!mapBody || !drag) {
                return;
            }

            if (!drag.placeholder) {
                drag.placeholder = document.createElement('div');
                drag.placeholder.className = 'em-wp-slide-sortable__placeholder em-wp-template-wizard-picker-drop__placeholder';
                drag.placeholder.style.height = '40px';
            }

            var items = Array.from(mapBody.children).filter(function (node) {
                return node !== drag.placeholder
                    && (node.classList.contains('em-wp-admin-landing-map__header-group')
                        || node.matches('.em-wp-admin-landing-map__zone.is-sortable'));
            });

            var target = null;
            var i;

            for (i = 0; i < items.length; i += 1) {
                var rect = items[i].getBoundingClientRect();
                var midpoint = rect.top + rect.height / 2;

                if (clientY < midpoint) {
                    target = items[i];
                    break;
                }
            }

            if (target) {
                mapBody.insertBefore(drag.placeholder, target);
            } else {
                mapBody.appendChild(drag.placeholder);
            }
        },

        clearPickerDropPlaceholder: function () {
            var drag = this.pickerDrag;
            var mapBody = this.getMapBody();

            if (mapBody) {
                mapBody.classList.remove('is-picker-drop-active');
            }

            if (drag && drag.placeholder && drag.placeholder.parentNode) {
                drag.placeholder.parentNode.removeChild(drag.placeholder);
            }
        },

        cleanupPickerDrag: function () {
            var drag = this.pickerDrag;

            if (!drag) {
                return;
            }

            if (drag.sourceBtn) {
                drag.sourceBtn.classList.remove('is-picker-dragging');
            }

            if (drag.ghost && drag.ghost.parentNode) {
                drag.ghost.parentNode.removeChild(drag.ghost);
            }

            this.clearPickerDropPlaceholder();

            drag.active = false;
            drag.slug = '';
            drag.sourceBtn = null;
            drag.ghost = null;
            drag.placeholder = null;
            drag.pointerId = null;
            drag.moved = false;

            document.body.classList.remove('em-wp-template-wizard-picker-drag-active');
            window.removeEventListener('pointermove', this.onPickerDragMove);
            window.removeEventListener('pointerup', this.onPickerDragEnd);
            window.removeEventListener('pointercancel', this.onPickerDragEnd);
        },

        onPickerDragMove: function (event) {
            var drag = this.pickerDrag;

            if (!drag || !drag.active || event.pointerId !== drag.pointerId) {
                return;
            }

            var deltaX = Math.abs(event.clientX - drag.startX);
            var deltaY = Math.abs(event.clientY - drag.startY);

            if (!drag.moved && (deltaX > 5 || deltaY > 5)) {
                drag.moved = true;
                this.pickerDragSuppressClick = true;

                if (drag.sourceBtn) {
                    drag.sourceBtn.classList.add('is-picker-dragging');
                }

                drag.ghost = document.createElement('div');
                drag.ghost.className = 'em-wp-template-wizard-picker-drag-ghost';
                drag.ghost.textContent = drag.label || drag.slug;
                document.body.appendChild(drag.ghost);

                document.body.classList.add('em-wp-template-wizard-picker-drag-active');
            }

            if (!drag.moved) {
                return;
            }

            event.preventDefault();

            if (drag.ghost) {
                drag.ghost.style.left = event.clientX + 12 + 'px';
                drag.ghost.style.top = event.clientY + 12 + 'px';
            }

            var mapBody = this.getMapBody();
            var overMap = this.isPointerOverMapBody(event.clientX, event.clientY);

            if (mapBody) {
                mapBody.classList.toggle('is-picker-drop-active', overMap);
            }

            if (overMap) {
                this.updatePickerDropPlaceholder(event.clientY);
            } else {
                this.clearPickerDropPlaceholder();
            }
        },

        onPickerDragEnd: function (event) {
            var drag = this.pickerDrag;
            var self = this;

            if (!drag || !drag.active || event.pointerId !== drag.pointerId) {
                return;
            }

            event.preventDefault();

            var slug = drag.slug;
            var moved = drag.moved;
            var overMap = moved && this.isPointerOverMapBody(event.clientX, event.clientY);
            var beforeSlug = overMap ? this.getInsertBeforeSlugFromPlaceholder() : null;

            this.pickerDragSuppressClick = true;
            this.cleanupPickerDrag();

            if (!slug) {
                return;
            }

            if (moved && overMap) {
                self.completePickerAdd(slug, beforeSlug);
                return;
            }

            if (!moved) {
                self.completePickerAdd(slug, null);
            }
        },

        initPickerDrag: function () {
            if (this.pickerDragBound || !this.pickerListEl) {
                return;
            }

            this.pickerDragBound = true;
            this.pickerDrag = {
                active: false,
                slug: '',
                label: '',
                sourceBtn: null,
                ghost: null,
                placeholder: null,
                pointerId: null,
                moved: false,
                startX: 0,
                startY: 0,
            };

            this.onPickerDragMove = this.onPickerDragMove.bind(this);
            this.onPickerDragEnd = this.onPickerDragEnd.bind(this);

            var self = this;

            this.pickerListEl.addEventListener('pointerdown', function (event) {
                var btn = event.target.closest('.em-wp-template-wizard-skeleton__picker-add');

                if (!btn || !self.hasMapContent()) {
                    return;
                }

                var slug = String(btn.getAttribute('data-rubrique-slug') || btn.value || '');

                if (slug === '') {
                    return;
                }

                if (event.pointerType === 'mouse' && event.button !== 0) {
                    return;
                }

                var labelEl = btn.querySelector('.em-wp-template-wizard-skeleton__picker-label');
                var drag = self.pickerDrag;

                drag.active = true;
                drag.slug = slug;
                drag.label = labelEl ? labelEl.textContent : slug;
                drag.sourceBtn = btn;
                drag.pointerId = event.pointerId;
                drag.moved = false;
                drag.startX = event.clientX;
                drag.startY = event.clientY;

                window.addEventListener('pointermove', self.onPickerDragMove, { passive: false });
                window.addEventListener('pointerup', self.onPickerDragEnd);
                window.addEventListener('pointercancel', self.onPickerDragEnd);
            });
        },

        removeRubrique: function (slug) {
            var draft = State.getDraft();
            var zone = this.findMapZone(slug);

            if (!draft || !slug) {
                return;
            }

            draft.skeleton.order = (draft.skeleton.order || []).filter(function (s) {
                return s !== slug;
            });
            delete draft.skeleton.rubriques[slug];
            delete draft.catalog[slug];
            State.markDirty();

            if (zone) {
                zone.remove();
            }

            this.renderPicker();
        },

        bind: function (root) {
            this.host = root.querySelector('#em-wp-template-wizard-wireframe-host');
            this.pickerListEl = root.querySelector('[data-wizard-plan-picker-list]');
            this.pickerEmptyEl = root.querySelector('[data-wizard-plan-picker-empty]');
            this.addWrap = root.querySelector('[data-wizard-plan-add]');

            var self = this;

            if (this.pickerListEl) {
                this.pickerListEl.addEventListener('click', function (event) {
                    if (self.pickerDragSuppressClick) {
                        self.pickerDragSuppressClick = false;
                        event.preventDefault();
                        return;
                    }

                    if (self.hasMapContent()) {
                        return;
                    }

                    var btn = event.target.closest('.em-wp-template-wizard-skeleton__picker-add');

                    if (!btn) {
                        return;
                    }

                    var slug = String(btn.getAttribute('data-rubrique-slug') || btn.value || '');

                    if (slug === '') {
                        return;
                    }

                    self.completePickerAdd(slug, null);
                });
            }

            this.initPickerDrag();

            if (this.host) {
                this.host.addEventListener('click', function (event) {
                    var removeBtn = event.target.closest('.em-wp-template-wizard-wireframe__remove');

                    if (!removeBtn) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    var zone = removeBtn.closest('[data-module-slug]');
                    var slug = zone ? zone.getAttribute('data-module-slug') : '';

                    if (slug) {
                        self.removeRubrique(slug);
                    }
                });
            }
        },

        render: function () {
            var self = this;
            var draft = State.getDraft();
            var cfg = State.config;

            if (!this.host || !draft || this.rendering) {
                return Promise.resolve();
            }

            var isInitial = !this.hasMapContent();

            this.rendering = true;

            if (isInitial) {
                this.host.innerHTML = '<p class="em-wp-template-wizard__loading">' + '…' + '</p>';
            }

            return this.fetchHtml()
                .then(function (html) {
                    self.mountHtml(html);
                })
                .catch(function () {
                    if (isInitial) {
                        self.host.innerHTML = '';
                    }

                    return Confirm.alert(
                        (cfg.i18n && cfg.i18n.wireframeError) || 'Erreur aperçu',
                        { title: 'Plan' }
                    );
                })
                .finally(function () {
                    self.rendering = false;
                });
        },
    };
})();
