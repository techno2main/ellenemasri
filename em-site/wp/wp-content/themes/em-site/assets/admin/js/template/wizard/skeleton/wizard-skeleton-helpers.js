(function () {
    'use strict';

    window.EmWpTemplateWizard = window.EmWpTemplateWizard || {};

    var State = EmWpTemplateWizard.State;
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

    function notifySkeletonChanged(type) {
        if (!Guide) {
            return;
        }

        if (type === 'structure') {
            Guide.invalidateAction(1, 'skeleton-rubriques');
            Guide.invalidateAction(1, 'skeleton-positions');
        } else if (type === 'order') {
            Guide.invalidateAction(1, 'skeleton-positions');
        }

        if (Guide.refreshStepGuide) {
            Guide.refreshStepGuide(1);
        }
    }

    function resolvePhase(phase) {
        if (phase && phase !== 'full') {
            return phase;
        }

        if (Guide && Guide.getSkeletonPhase) {
            return Guide.getSkeletonPhase();
        }

        return 'full';
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

    function insertRubrique(draft, slug) {
        var def = State.getRubriqueDef(slug);

        if (!def) {
            return false;
        }

        var order = draft.skeleton.order;
        var footerIdx = order.indexOf('footer');

        if (footerIdx === -1) {
            order.push(slug);
        } else {
            order.splice(footerIdx, 0, slug);
        }

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

    function isDefaultSkeletonSlug(slug) {
        var defaults = State.config.defaultSkeletonOrder || ['top-bar', 'header', 'footer'];
        return defaults.indexOf(slug) !== -1;
    }

    function canDragSort(slug) {
        return slug !== 'top-bar' && slug !== 'footer';
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

    function renderPicker(pickerListEl, pickerEmptyEl, order, defs) {
        var available = getAvailableRubriques(order, defs);

        if (!pickerListEl) {
            return available.length;
        }

        pickerListEl.innerHTML = '';

        if (pickerEmptyEl) {
            pickerEmptyEl.hidden = true;
        }

        pickerListEl.hidden = available.length === 0;

        available.forEach(function (item) {
            var li = document.createElement('li');
            li.className = 'em-wp-template-wizard-skeleton__picker-item';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'em-wp-template-wizard-skeleton__picker-add';
            btn.value = item.slug;
            btn.setAttribute('data-rubrique-slug', item.slug);
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

    function renderList(listEl, pickerListEl, pickerEmptyEl, addWrap, phase) {
        var draft = State.getDraft();
        var resolvedPhase = resolvePhase(phase);

        if (!draft || !listEl) {
            return;
        }

        var order = draft.skeleton.order || [];
        var defs = State.config.rubriques || {};
        var showMoves = resolvedPhase === 'order' || resolvedPhase === 'full';
        var showRemove = resolvedPhase === 'pick' || resolvedPhase === 'order' || resolvedPhase === 'full';
        var showAdd = resolvedPhase === 'pick' || resolvedPhase === 'full';
        var i18n = State.config.i18n || {};
        var accentColor = getTemplateAccentColor();

        listEl.innerHTML = '';
        listEl.classList.remove('em-wp-template-wizard-skeleton__list--readonly');
        listEl.style.setProperty('--em-wp-skeleton-accent', accentColor);

        order.forEach(function (slug, index) {
            var def = defs[slug];

            if (!def) {
                return;
            }

            var li = document.createElement('li');
            li.className = 'em-wp-template-wizard-skeleton__item';
            li.setAttribute('data-rubrique', slug);

            if (showMoves && canDragSort(slug)) {
                li.classList.add('is-sortable');

                var handle = document.createElement('span');
                handle.className = 'em-wp-template-wizard-skeleton__drag em-wp-slide-sortable__handle';
                handle.setAttribute('role', 'button');
                handle.setAttribute('tabindex', '0');
                handle.setAttribute('aria-label', i18n.dragRubrique || 'Glisser pour reordonner');
                handle.setAttribute('title', i18n.dragRubrique || 'Glisser pour reordonner');
                handle.innerHTML = '<i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>';
                li.appendChild(handle);
            }

            var main = document.createElement('span');
            main.className = 'em-wp-template-wizard-skeleton__item-main';

            var title = document.createElement('span');
            title.className = 'em-wp-template-wizard-skeleton__item-label';
            title.textContent = def.label || slug;

            var isDefault = isDefaultSkeletonSlug(slug);
            var badge = null;

            if (isDefault) {
                badge = document.createElement('span');
                badge.className = 'em-wp-template-wizard-skeleton__item-badge is-default';
                badge.textContent = i18n.skeletonBadgeDefault || '(Rubrique de base)';
                main.appendChild(title);
                main.appendChild(badge);
            } else {
                main.appendChild(title);
            }

            li.appendChild(main);

            if (showMoves || showRemove) {
                var actions = document.createElement('span');
                actions.className = 'em-wp-template-wizard-skeleton__item-actions';

                if (showMoves && index > 0 && order[index - 1] !== 'top-bar' && slug !== 'footer') {
                    var up = document.createElement('button');
                    up.type = 'button';
                    up.className = 'em-wp-template-wizard-skeleton__move';
                    up.setAttribute('data-move', 'up');
                    up.setAttribute('aria-label', 'Monter');
                    up.innerHTML = '<i class="fa-solid fa-arrow-up" aria-hidden="true"></i>';
                    actions.appendChild(up);
                }

                if (showMoves && index < order.length - 1 && slug !== 'top-bar' && order[index + 1] !== 'footer') {
                    var down = document.createElement('button');
                    down.type = 'button';
                    down.className = 'em-wp-template-wizard-skeleton__move';
                    down.setAttribute('data-move', 'down');
                    down.setAttribute('aria-label', 'Descendre');
                    down.innerHTML = '<i class="fa-solid fa-arrow-down" aria-hidden="true"></i>';
                    actions.appendChild(down);
                }

                if (showRemove && canRemove(slug, def)) {
                    if (!isDefault) {
                        var addedBadge = document.createElement('span');
                        addedBadge.className = 'em-wp-template-wizard-skeleton__item-badge is-added';
                        addedBadge.textContent = i18n.skeletonBadgeAdded || 'Rubrique ajoutee';
                        addedBadge.style.color = accentColor;
                        actions.appendChild(addedBadge);
                    }

                    var remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'em-wp-template-wizard-skeleton__remove';
                    remove.setAttribute(
                        'aria-label',
                        ((State.config.i18n && State.config.i18n.removeRubriqueLabel) || 'Retirer %s').replace('%s', def.label || slug)
                    );
                    remove.innerHTML = '<i class="fa-solid fa-trash-can" aria-hidden="true"></i>';
                    actions.appendChild(remove);
                }

                li.appendChild(actions);
            }

            listEl.appendChild(li);
        });

        var availableCount = renderPicker(pickerListEl, pickerEmptyEl, order, defs);
        var pickerRow = addWrap ? addWrap.querySelector('.em-wp-template-wizard-skeleton__picker-row') : null;

        if (pickerListEl) {
            pickerListEl.style.setProperty('--em-wp-skeleton-accent', accentColor);
        }

        if (pickerRow) {
            pickerRow.hidden = availableCount === 0;
        }

        if (addWrap) {
            addWrap.hidden = availableCount === 0 || !showAdd;
        }
    }

    EmWpTemplateWizard.SkeletonHelpers = {
        notifySkeletonChanged: notifySkeletonChanged,
        resolvePhase: resolvePhase,
        insertRubrique: insertRubrique,
        isValidSkeletonOrder: isValidSkeletonOrder,
        renderList: renderList,
    };
})();
