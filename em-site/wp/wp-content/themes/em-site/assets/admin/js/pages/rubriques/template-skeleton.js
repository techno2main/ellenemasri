(function (window, document) {
    'use strict';

    var config = window.emWpTemplateSkeleton || {};
    var adminRoot = document.querySelector('.em-site-rubriques-admin');
    var hasAjaxConfig = !!(config.ajaxUrl && config.nonce);

    var statusDismissTimer = null;
    var lastStatusEl = null;
    var STATUS_DISMISS_MS = 3000;
    var listRoot = adminRoot ? adminRoot.querySelector('.em-site-rubriques-admin__list') : null;
    var pickerCache = Object.create(null);
    var activePickerRequestToken = 0;
    var initialRubriqueOrder = listRoot ? Array.prototype.map.call(
        listRoot.querySelectorAll('.em-site-rubriques-admin__list-item[data-module-slug]'),
        function (item) {
            return item.getAttribute('data-module-slug') || '';
        }
    ).filter(Boolean) : [];

    function setStatus(message, isError) {
        var statusEl = document.getElementById('em-site-rubrique-skeleton-add-status')
            || document.getElementById('em-site-rubriques-sort-status');

        if (!statusEl) {
            return;
        }

        if (statusDismissTimer) {
            window.clearTimeout(statusDismissTimer);
            statusDismissTimer = null;
        }

        statusEl.textContent = message || '';
        statusEl.hidden = message === '';
        statusEl.classList.toggle('is-error', !!isError);
        lastStatusEl = statusEl;

        if (message && !isError) {
            statusDismissTimer = window.setTimeout(function () {
                statusDismissTimer = null;

                if (lastStatusEl) {
                    lastStatusEl.textContent = '';
                    lastStatusEl.hidden = true;
                    lastStatusEl.classList.remove('is-error');
                }
            }, STATUS_DISMISS_MS);
        }
    }

    function resolveAddPanelFromToggle(toggleButton) {
        if (!toggleButton) {
            return null;
        }

        var controlsId = toggleButton.getAttribute('aria-controls') || '';

        if (controlsId !== '') {
            return document.getElementById(controlsId);
        }

        if (adminRoot) {
            return adminRoot.querySelector('#em-site-rubrique-skeleton-add-panel');
        }

        return document.getElementById('em-site-rubrique-skeleton-add-panel');
    }

    function closeAddPanel(toggleButton, panel) {
        if (!toggleButton || !panel) {
            return;
        }

        panel.hidden = true;
        toggleButton.setAttribute('aria-expanded', 'false');
    }

    function openAddPanel(toggleButton, panel) {
        if (!toggleButton || !panel) {
            return;
        }

        panel.hidden = false;
        toggleButton.setAttribute('aria-expanded', 'true');
    }

    if (!adminRoot) {
        return;
    }

    adminRoot.addEventListener('click', function (event) {
        var addToggleButton = event.target.closest('.em-site-rubriques-admin__add-rubrique-toggle');

        if (!addToggleButton) {
            return;
        }

        event.preventDefault();

        var addPanel = resolveAddPanelFromToggle(addToggleButton);

        if (!addPanel) {
            return;
        }

        if (addPanel.hidden) {
            openAddPanel(addToggleButton, addPanel);
        } else {
            closeAddPanel(addToggleButton, addPanel);
        }
    });

    function postSkeletonAction(action, templateSlug, rubriqueSlug, button, options) {
        options = options || {};

        if (!hasAjaxConfig) {
            setStatus((config.i18n && config.i18n.error) || 'Impossible de mettre a jour le squelette.', true);
            return Promise.resolve();
        }

        if (button) {
            button.disabled = true;
        }

        var body = new window.FormData();
        body.append('action', action);
        body.append('nonce', config.nonce);
        body.append('template_slug', templateSlug);
        body.append('rubrique_slug', rubriqueSlug);

        if (options.insertAfter) {
            body.append('insert_after', options.insertAfter);
        }

        return window.fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                return parseJsonPayload(response);
            })
            .then(function (payload) {
                if (!payload || !payload.success) {
                    throw new Error((payload && payload.data && payload.data.message) || config.i18n.error);
                }

                setStatus((payload.data && payload.data.message) || config.i18n.saved, false);

                var currentOrder = listRoot ? Array.prototype.map.call(
                    listRoot.querySelectorAll('.em-site-rubriques-admin__list-item[data-module-slug]'),
                    function (item) {
                        return item.getAttribute('data-module-slug') || '';
                    }
                ).filter(Boolean) : [];

                document.dispatchEvent(new window.CustomEvent('emSiteDraftChanged', {
                    detail: {
                        source: action,
                        rubriqueSlug: rubriqueSlug,
                        draftKey: action + ':' + (templateSlug || 'default'),
                        hasPendingChanges: JSON.stringify(currentOrder) !== JSON.stringify(initialRubriqueOrder),
                    },
                }));

                if (payload.data && payload.data.reload) {
                    window.location.reload();
                }
            })
            .catch(function (error) {
                setStatus(error.message || config.i18n.error, true);
            })
            .finally(function () {
                if (button) {
                    button.disabled = false;
                }
            });
    }

    function parseJsonPayload(response) {
        return response.text().then(function (raw) {
            var text = String(raw || '').trim();

            if (text === '') {
                throw new Error((config.i18n && config.i18n.error) || 'Empty JSON response');
            }

            try {
                return JSON.parse(text);
            } catch (_firstError) {
                var firstBrace = text.indexOf('{');
                var lastBrace = text.lastIndexOf('}');

                if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
                    return JSON.parse(text.slice(firstBrace, lastBrace + 1));
                }

                throw _firstError;
            }
        });
    }

    function currentUrl() {
        return new window.URL(window.location.href);
    }

    function syncOpenQueryParam(moduleSlug) {
        var url = currentUrl();

        if (moduleSlug) {
            url.searchParams.set('open', moduleSlug);
        } else {
            url.searchParams.delete('open');
        }

        window.history.replaceState({}, '', url.toString());
    }

    function removeExistingPicker() {
        if (!listRoot) {
            return;
        }

        var picker = listRoot.querySelector('.em-site-rubriques-admin__picker');
        if (picker && picker.parentNode) {
            picker.parentNode.removeChild(picker);
        }
    }

    function closeOpenRubrique() {
        if (!listRoot) {
            return;
        }

        listRoot.querySelectorAll('.em-site-rubriques-admin__list-item.is-open').forEach(function (item) {
            item.classList.remove('is-open');
        });

        removeExistingPicker();
        syncOpenQueryParam('');

        if (window.EmWpSkeletonPreview && typeof window.EmWpSkeletonPreview.restoreAll === 'function') {
            window.EmWpSkeletonPreview.restoreAll();
        }
    }

    function notifyPickerMounted(container, moduleSlug) {
        document.dispatchEvent(new window.CustomEvent('emWpRubriquePickerMounted', {
            detail: {
                container: container,
                moduleSlug: moduleSlug || '',
            },
        }));
    }

    function mountPickerHtml(listItem, moduleSlug, html) {
        if (!listRoot || !listItem || !html) {
            return false;
        }

        var mount = document.createElement('ul');
        mount.innerHTML = html;
        var picker = mount.querySelector('.em-site-rubriques-admin__picker');

        if (!picker) {
            return false;
        }

        if (listItem.nextSibling) {
            listRoot.insertBefore(picker, listItem.nextSibling);
        } else {
            listRoot.appendChild(picker);
        }

        notifyPickerMounted(picker, moduleSlug);
        return true;
    }

    function fetchRubriquePicker(moduleSlug) {
        if (!hasAjaxConfig) {
            return Promise.reject(new Error((config.i18n && config.i18n.pickerLoadError) || 'Picker load error'));
        }

        var body = new window.FormData();
        body.append('action', 'em_site_load_rubrique_picker');
        body.append('nonce', config.nonce);
        body.append('module_slug', moduleSlug);

        return window.fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            return parseJsonPayload(response);
        }).then(function (payload) {
            if (!payload || !payload.success || !payload.data || !payload.data.html) {
                throw new Error((payload && payload.data && payload.data.message) || (config.i18n && config.i18n.pickerLoadError) || 'Picker load error');
            }

            return payload.data.html;
        });
    }

    function openRubriquePanel(listItem, moduleSlug) {
        if (!listRoot || !listItem || !moduleSlug) {
            return;
        }

        closeOpenRubrique();
        listItem.classList.add('is-open');
        syncOpenQueryParam(moduleSlug);

        if (pickerCache[moduleSlug]) {
            mountPickerHtml(listItem, moduleSlug, pickerCache[moduleSlug]);
            return;
        }

        activePickerRequestToken += 1;
        var requestToken = activePickerRequestToken;
        setStatus((config.i18n && config.i18n.loadingPicker) || '', false);

        fetchRubriquePicker(moduleSlug)
            .then(function (html) {
                if (requestToken !== activePickerRequestToken) {
                    return;
                }

                pickerCache[moduleSlug] = html;

                if (!listItem.classList.contains('is-open')) {
                    return;
                }

                if (!mountPickerHtml(listItem, moduleSlug, html)) {
                    throw new Error((config.i18n && config.i18n.pickerLoadError) || 'Picker mount error');
                }

                setStatus('', false);
            })
            .catch(function (error) {
                if (requestToken !== activePickerRequestToken) {
                    return;
                }

                listItem.classList.remove('is-open');
                syncOpenQueryParam('');
                setStatus((error && error.message) || (config.i18n && config.i18n.pickerLoadError) || '', true);
            });
    }

    function getRubriqueLabelFromButton(button) {
        var listItem = button.closest('.em-site-rubriques-admin__list-item');

        if (listItem) {
            var listLabel = listItem.querySelector('.em-site-rubriques-admin__list-label');

            if (listLabel) {
                var listClone = listLabel.cloneNode(true);
                var listBadge = listClone.querySelector('.em-site-rubriques-admin__hidden-badge');

                if (listBadge) {
                    listBadge.remove();
                }

                return listClone.textContent.replace(/\s+/g, ' ').trim();
            }
        }

        var panelItem = button.closest('.em-site-rubrique-skeleton-add-panel__item');

        if (panelItem) {
            var panelLabel = panelItem.querySelector('.em-site-rubrique-skeleton-add-panel__item-title');

            if (panelLabel) {
                return panelLabel.textContent.replace(/\s+/g, ' ').trim();
            }
        }

        return '';
    }

    function replaceAll(template, search, value) {
        return String(template).split(search).join(value);
    }

    function confirmRemoveRubrique(button, rubriqueSlug) {
        var i18n = config.i18n || {};
        var label = getRubriqueLabelFromButton(button) || rubriqueSlug;
        var confirmApi = window.EmWpAdminConfirm;
        var step1Message = replaceAll(i18n.confirmRemove || 'Retirer « %s » du squelette ?', '%s', label);

        if (!confirmApi || typeof confirmApi.ask !== 'function') {
            return Promise.resolve(window.confirm(step1Message));
        }

        return confirmApi.ask(step1Message, {
            title: i18n.confirmRemoveTitle || 'Retirer du squelette',
            confirmLabel: i18n.confirmRemoveLabel || 'Retirer du squelette',
            cancelLabel: i18n.cancelLabel || 'Annuler',
            confirmClass: 'button-primary',
        }).then(function (confirmed) {
            if (!confirmed || !config.isLiveTemplate) {
                return confirmed;
            }

            var templateLabel = config.templateLabel || config.templateSlug || '';
            var step2Message = replaceAll(
                replaceAll(
                    i18n.confirmRemoveLive || '',
                    '%1$s',
                    templateLabel
                ),
                '%2$s',
                label
            );

            return confirmApi.ask(step2Message, {
                title: i18n.confirmRemoveLiveTitle || 'Template en ligne — attention',
                confirmLabel: i18n.confirmRemoveLiveLabel || 'Oui, modifier le site',
                cancelLabel: i18n.cancelLabel || 'Annuler',
                confirmClass: 'button-primary',
                multiline: true,
                danger: true,
                requireAcknowledge: true,
                acknowledgeLabel: i18n.confirmRemoveLiveAck
                    || 'J\'ai bien compris que cette modification sera visible immédiatement sur le site public.',
            });
        });
    }

    adminRoot.addEventListener('click', function (event) {
        var listLink = event.target.closest('.em-site-rubriques-admin__list-link');

        if (listLink && listRoot && listRoot.contains(listLink)) {
            var listItem = listLink.closest('.em-site-rubriques-admin__list-item[data-module-slug]');
            var moduleSlug = listItem ? (listItem.getAttribute('data-module-slug') || '') : '';

            if (moduleSlug !== '') {
                if (!hasAjaxConfig) {
                    return;
                }

                event.preventDefault();

                if (listItem.classList.contains('is-open')) {
                    closeOpenRubrique();
                } else {
                    openRubriquePanel(listItem, moduleSlug);
                }

                return;
            }
        }

        var addButton = event.target.closest('.em-site-rubriques-admin__add-button');

        if (addButton) {
            event.preventDefault();

            var addTemplate = addButton.getAttribute('data-template-slug') || config.templateSlug || '';
            var addRubrique = addButton.getAttribute('data-rubrique-slug') || '';

            if (addTemplate === '' || addRubrique === '') {
                return;
            }

            var panelItem = addButton.closest('.em-site-rubrique-skeleton-add-panel__item');
            var positionSelect = panelItem
                ? panelItem.querySelector('.em-site-rubrique-skeleton-add-panel__position')
                : null;
            var insertAfter = positionSelect ? positionSelect.value : '';

            postSkeletonAction(
                'em_site_template_skeleton_add_rubrique',
                addTemplate,
                addRubrique,
                addButton,
                {
                    insertAfter: insertAfter,
                }
            );
            return;
        }

        var removeButton = event.target.closest('.em-site-rubriques-admin__remove-button');

        if (!removeButton) {
            return;
        }

        event.preventDefault();

        var removeTemplate = removeButton.getAttribute('data-template-slug') || config.templateSlug || '';
        var removeRubrique = removeButton.getAttribute('data-rubrique-slug') || '';

        if (removeTemplate === '' || removeRubrique === '') {
            return;
        }

        confirmRemoveRubrique(removeButton, removeRubrique).then(function (confirmed) {
            if (!confirmed) {
                return;
            }

            postSkeletonAction('em_site_template_skeleton_remove_rubrique', removeTemplate, removeRubrique, removeButton);
        });
    });
})(window, document);
