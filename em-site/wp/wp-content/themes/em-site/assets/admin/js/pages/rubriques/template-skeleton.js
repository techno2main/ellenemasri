(function (window, document) {
    'use strict';

    var config = window.emWpTemplateSkeleton || {};
    var adminRoot = document.querySelector('.em-wp-rubriques-admin');
    var addToggle = document.getElementById('em-wp-rubrique-skeleton-add-toggle');
    var addPanel = document.getElementById('em-wp-rubrique-skeleton-add-panel');

    if (!adminRoot || !config.ajaxUrl || !config.nonce) {
        return;
    }

    var statusDismissTimer = null;
    var lastStatusEl = null;
    var STATUS_DISMISS_MS = 3000;
    var listRoot = adminRoot.querySelector('.em-wp-rubriques-admin__list');
    var pickerCache = Object.create(null);
    var activePickerRequestToken = 0;

    function setStatus(message, isError) {
        var statusEl = document.getElementById('em-wp-rubrique-skeleton-add-status')
            || document.getElementById('em-wp-rubriques-sort-status');

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

    function closeAddPanel() {
        if (!addPanel || !addToggle) {
            return;
        }

        addPanel.hidden = true;
        addToggle.setAttribute('aria-expanded', 'false');
    }

    function openAddPanel() {
        if (!addPanel || !addToggle) {
            return;
        }

        addPanel.hidden = false;
        addToggle.setAttribute('aria-expanded', 'true');
    }

    function postSkeletonAction(action, templateSlug, rubriqueSlug, button, options) {
        options = options || {};

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

                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.success) {
                    throw new Error((payload && payload.data && payload.data.message) || config.i18n.error);
                }

                setStatus((payload.data && payload.data.message) || config.i18n.saved, false);

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

        var picker = listRoot.querySelector('.em-wp-rubriques-admin__picker');
        if (picker && picker.parentNode) {
            picker.parentNode.removeChild(picker);
        }
    }

    function closeOpenRubrique() {
        if (!listRoot) {
            return;
        }

        listRoot.querySelectorAll('.em-wp-rubriques-admin__list-item.is-open').forEach(function (item) {
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
        var picker = mount.querySelector('.em-wp-rubriques-admin__picker');

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
        var body = new window.FormData();
        body.append('action', 'em_wp_load_rubrique_picker');
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

            return response.json();
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

    if (addToggle && addPanel) {
        addToggle.addEventListener('click', function () {
            if (addPanel.hidden) {
                openAddPanel();
            } else {
                closeAddPanel();
            }
        });
    }

    function getRubriqueLabelFromButton(button) {
        var listItem = button.closest('.em-wp-rubriques-admin__list-item');

        if (listItem) {
            var listLabel = listItem.querySelector('.em-wp-rubriques-admin__list-label');

            if (listLabel) {
                var listClone = listLabel.cloneNode(true);
                var listBadge = listClone.querySelector('.em-wp-rubriques-admin__hidden-badge');

                if (listBadge) {
                    listBadge.remove();
                }

                return listClone.textContent.replace(/\s+/g, ' ').trim();
            }
        }

        var panelItem = button.closest('.em-wp-rubrique-skeleton-add-panel__item');

        if (panelItem) {
            var panelLabel = panelItem.querySelector('.em-wp-rubrique-skeleton-add-panel__item-title');

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
        var listLink = event.target.closest('.em-wp-rubriques-admin__list-link');

        if (listLink && listRoot && listRoot.contains(listLink)) {
            var listItem = listLink.closest('.em-wp-rubriques-admin__list-item[data-module-slug]');
            var moduleSlug = listItem ? (listItem.getAttribute('data-module-slug') || '') : '';

            if (moduleSlug !== '') {
                event.preventDefault();

                if (listItem.classList.contains('is-open')) {
                    closeOpenRubrique();
                } else {
                    openRubriquePanel(listItem, moduleSlug);
                }

                return;
            }
        }

        var addButton = event.target.closest('.em-wp-rubriques-admin__add-button');

        if (addButton) {
            event.preventDefault();

            var addTemplate = addButton.getAttribute('data-template-slug') || config.templateSlug || '';
            var addRubrique = addButton.getAttribute('data-rubrique-slug') || '';

            if (addTemplate === '' || addRubrique === '') {
                return;
            }

            var panelItem = addButton.closest('.em-wp-rubrique-skeleton-add-panel__item');
            var positionSelect = panelItem
                ? panelItem.querySelector('.em-wp-rubrique-skeleton-add-panel__position')
                : null;
            var insertAfter = positionSelect ? positionSelect.value : '';

            postSkeletonAction(
                'em_wp_template_skeleton_add_rubrique',
                addTemplate,
                addRubrique,
                addButton,
                {
                    insertAfter: insertAfter,
                }
            );
            return;
        }

        var removeButton = event.target.closest('.em-wp-rubriques-admin__remove-button');

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

            postSkeletonAction('em_wp_template_skeleton_remove_rubrique', removeTemplate, removeRubrique, removeButton);
        });
    });
})(window, document);
