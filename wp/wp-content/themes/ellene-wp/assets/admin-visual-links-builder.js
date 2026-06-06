(function () {
    function parsePayload(value) {
        if (!value) {
            return null;
        }

        try {
            return JSON.parse(value);
        } catch (error) {
            return null;
        }
    }

    function defaultPayload() {
        return {
            kicker: 'VISUAL LINKS',
            title: 'Electronic Press Kit',
            description: '',
            imageUrl: '',
            imageAlt: '',
            zones: [],
            updatedAt: '',
            publishedAt: ''
        };
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function normalizePayload(payload) {
        const state = Object.assign(defaultPayload(), payload || {});
        state.zones = Array.isArray(state.zones) ? state.zones : [];
        state.zones = state.zones.map(function (zone, index) {
            return {
                id: zone.id || 'zone_' + index + '_' + Date.now(),
                label: zone.label || '',
                hrefType: zone.hrefType === 'anchor' ? 'anchor' : 'url',
                hrefValue: zone.hrefValue || '',
                x: clamp(Number(zone.x) || 0, 0, 100),
                y: clamp(Number(zone.y) || 0, 0, 100),
                width: clamp(Number(zone.width) || 0, 0, 100),
                height: clamp(Number(zone.height) || 0, 0, 100)
            };
        }).filter(function (zone) {
            return zone.width > 0 && zone.height > 0;
        });

        return state;
    }

    function formatTimestamp(value) {
        if (!value) {
            return 'Non défini';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return 'Non défini';
        }

        return date.toLocaleString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function getHref(zone) {
        const value = String(zone.hrefValue || '').trim();
        if (!value) {
            return '';
        }

        if (zone.hrefType === 'anchor') {
            return value.charAt(0) === '#' ? value : '#' + value;
        }

        return value;
    }

    function initBuilder(root) {
        if (root.dataset.ready === '1') {
            return;
        }

        root.dataset.ready = '1';

        const draftInput = document.querySelector('textarea[name="mayami_landing_options[epk_draft_payload]"]');
        const publishedInput = document.querySelector('textarea[name="mayami_landing_options[epk_published_payload]"]');
        const validationInput = document.querySelector('input[name="mayami_landing_options[epk_validation_ready]"]');
        const validationRow = validationInput ? validationInput.closest('.cmb-row') : null;
        const nativeMediaInput = document.querySelector('input[name="mayami_landing_options[epk_draft_image_source]"]');
        const nativeMediaRow = nativeMediaInput ? nativeMediaInput.closest('.cmb-row') : null;

        if (!draftInput || !publishedInput || !validationInput) {
            return;
        }

        const publishedPayload = normalizePayload(parsePayload(publishedInput.value));
        let state = normalizePayload(parsePayload(draftInput.value) || (publishedInput.value ? publishedPayload : defaultPayload()));
        const initialDraftValue = draftInput.value || JSON.stringify(state);
        const initialValidationState = !!validationInput.checked;
        let activeZoneId = null;
        let isDrawing = false;
        let startX = 0;
        let startY = 0;
        let currentBox = null;

        const elements = {
            draftStatus: root.querySelector('.mayami-vlb-draft-status'),
            publishedStatus: root.querySelector('.mayami-vlb-published-status'),
            zoneCount: root.querySelector('.mayami-vlb-zone-count'),
            linkedCount: root.querySelector('.mayami-vlb-linked-count'),
            kicker: root.querySelector('[data-vlb-field="kicker"]'),
            title: root.querySelector('[data-vlb-field="title"]'),
            description: root.querySelector('[data-vlb-field="description"]'),
            imageAlt: root.querySelector('[data-vlb-field="imageAlt"]'),
            nativeMediaHost: root.querySelector('.mayami-vlb-native-media-host'),
            validationHost: root.querySelector('.mayami-vlb-validation-host'),
            clearImage: root.querySelector('.mayami-vlb-clear-image'),
            canvasEmpty: root.querySelector('.mayami-vlb-canvas-empty'),
            canvasWrapper: root.querySelector('.mayami-vlb-canvas-wrapper'),
            canvasImage: root.querySelector('.mayami-vlb-canvas-image'),
            canvasOverlay: root.querySelector('.mayami-vlb-canvas-overlay'),
            zonesList: root.querySelector('.mayami-vlb-zones-list'),
            resetZones: root.querySelector('.mayami-vlb-reset-zones'),
            previewLink: root.querySelector('.mayami-vlb-preview-link'),
            publishButton: root.querySelector('.mayami-vlb-publish-button'),
            unpublishButton: root.querySelector('.mayami-vlb-unpublish-button')
        };

        function getNativeMediaUploadButton() {
            return nativeMediaRow ? nativeMediaRow.querySelector('.cmb2-upload-button') : null;
        }

        function getNativeMediaRemoveButton() {
            return nativeMediaRow ? nativeMediaRow.querySelector('.cmb2-remove-file-button') : null;
        }

        function mountNativeMediaControls() {
            if (!nativeMediaRow || !elements.nativeMediaHost) {
                return;
            }

            if (nativeMediaRow.parentNode !== elements.nativeMediaHost) {
                elements.nativeMediaHost.appendChild(nativeMediaRow);
            }

            nativeMediaRow.classList.add('mayami-vlb-native-media-row');

            const uploadButton = getNativeMediaUploadButton();
            if (uploadButton) {
                const label = state.imageUrl ? 'Modifier le visuel' : 'Choisir le visuel';
                if ('value' in uploadButton) {
                    uploadButton.value = label;
                }
                uploadButton.textContent = label;
                uploadButton.setAttribute('aria-label', label);
            }
        }

        function mountValidationControl() {
            if (!validationRow || !elements.validationHost) {
                return;
            }

            if (validationRow.parentNode !== elements.validationHost) {
                elements.validationHost.appendChild(validationRow);
            }

            validationRow.classList.add('mayami-vlb-validation-row');
        }

        function syncHiddenValue(markUpdated) {
            if (markUpdated) {
                state.updatedAt = new Date().toISOString();
            }

            draftInput.value = JSON.stringify(state);

            if (elements.draftStatus) {
                elements.draftStatus.textContent = formatTimestamp(state.updatedAt);
            }
        }

        function hasUnsavedChanges() {
            return draftInput.value !== initialDraftValue || !!validationInput.checked !== initialValidationState;
        }

        function setFieldValues() {
            elements.kicker.value = state.kicker || '';
            elements.title.value = state.title || '';
            elements.description.value = state.description || '';
            elements.imageAlt.value = state.imageAlt || '';
        }

        function zoneToPixels(zone) {
            const width = elements.canvasOverlay.clientWidth || elements.canvasImage.clientWidth || 0;
            const height = elements.canvasOverlay.clientHeight || elements.canvasImage.clientHeight || 0;

            return {
                x: Math.round((zone.x / 100) * width),
                y: Math.round((zone.y / 100) * height),
                width: Math.round((zone.width / 100) * width),
                height: Math.round((zone.height / 100) * height)
            };
        }

        function updateStats() {
            if (elements.zoneCount) {
                elements.zoneCount.textContent = String(state.zones.length);
            }

            if (elements.linkedCount) {
                elements.linkedCount.textContent = String(state.zones.filter(function (zone) {
                    return !!getHref(zone);
                }).length);
            }
        }

        function syncFromNativeMediaField() {
            if (!nativeMediaInput) {
                return;
            }

            const nextUrl = String(nativeMediaInput.value || '').trim();
            if (nextUrl === state.imageUrl) {
                return;
            }

            updateState(function (draft) {
                const hadPreviousImage = !!draft.imageUrl;
                draft.imageUrl = nextUrl;

                if (!nextUrl || hadPreviousImage) {
                    draft.zones = [];
                }
            });
        }

        function setActiveZone(zoneId) {
            activeZoneId = zoneId;
            renderZones();
            renderCanvas();
        }

        function renderCanvas() {
            elements.canvasOverlay.innerHTML = '';

            if (!state.imageUrl) {
                elements.canvasEmpty.hidden = false;
                elements.canvasWrapper.hidden = true;
                updateStats();
                return;
            }

            elements.canvasEmpty.hidden = true;
            elements.canvasWrapper.hidden = false;

            if (elements.canvasImage.getAttribute('src') !== state.imageUrl) {
                elements.canvasImage.setAttribute('src', state.imageUrl);
            }

            elements.canvasImage.setAttribute('alt', state.imageAlt || 'Visuel Visual Links');

            state.zones.forEach(function (zone) {
                const box = document.createElement('button');
                box.type = 'button';
                box.className = 'mayami-vlb-zone-box' + (activeZoneId === zone.id ? ' is-active' : '');
                box.style.left = zone.x + '%';
                box.style.top = zone.y + '%';
                box.style.width = zone.width + '%';
                box.style.height = zone.height + '%';
                box.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    setActiveZone(zone.id);
                });
                elements.canvasOverlay.appendChild(box);
            });

            updateStats();
        }

        function renderZones() {
            if (!state.zones.length) {
                elements.zonesList.innerHTML = '<p class="mayami-vlb-zone-empty">Aucune zone créée.<br>Dessinez sur l\'image pour commencer.</p>';
                updateStats();
                return;
            }

            elements.zonesList.innerHTML = state.zones.map(function (zone, index) {
                const pixels = zoneToPixels(zone);
                return '' +
                    '<div class="mayami-vlb-zone-item' + (activeZoneId === zone.id ? ' is-active' : '') + '" data-zone-id="' + escapeHtml(zone.id) + '">' +
                        '<div class="mayami-vlb-zone-header">' +
                            '<span class="mayami-vlb-zone-title">Zone ' + (index + 1) + '</span>' +
                            '<button type="button" class="mayami-vlb-zone-delete" data-zone-action="delete">X</button>' +
                        '</div>' +
                        '<div class="mayami-vlb-zone-coords">x:' + pixels.x + ', y:' + pixels.y + ', w:' + pixels.width + ', h:' + pixels.height + '</div>' +
                        '<div class="mayami-vlb-zone-controls">' +
                            '<select data-zone-field="hrefType">' +
                                '<option value="url"' + (zone.hrefType === 'url' ? ' selected' : '') + '>Lien</option>' +
                                '<option value="anchor"' + (zone.hrefType === 'anchor' ? ' selected' : '') + '>Ancre</option>' +
                            '</select>' +
                            '<input type="text" data-zone-field="hrefValue" value="' + escapeHtml(zone.hrefValue) + '" placeholder="' + escapeHtml(zone.hrefType === 'anchor' ? '#section' : 'https://exemple.com') + '">' +
                        '</div>' +
                    '</div>';
            }).join('');

            updateStats();
        }

        function refreshWorkflowState() {
            const canPublish = !!state.imageUrl && !!validationInput.checked;
            const hasPublishedPayload = !!String(publishedInput.value || '').trim();

            if (elements.publishButton) {
                elements.publishButton.disabled = !canPublish;
                elements.publishButton.classList.toggle('is-disabled', !canPublish);
            }

            if (elements.unpublishButton) {
                elements.unpublishButton.disabled = !hasPublishedPayload;
                elements.unpublishButton.classList.toggle('is-disabled', !hasPublishedPayload);
            }

            if (elements.publishedStatus) {
                const published = normalizePayload(parsePayload(publishedInput.value));
                elements.publishedStatus.textContent = formatTimestamp(published.publishedAt);
            }
        }

        function redrawAll() {
            mountNativeMediaControls();
            mountValidationControl();
            setFieldValues();
            renderCanvas();
            renderZones();
            refreshWorkflowState();
        }

        function updateState(mutator) {
            mutator(state);
            state = normalizePayload(state);
            syncHiddenValue(true);
            redrawAll();
        }

        function buildActionRequest(action, nonce) {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = root.dataset.actionEndpoint;

            const actionField = document.createElement('input');
            actionField.type = 'hidden';
            actionField.name = 'action';
            actionField.value = action;
            form.appendChild(actionField);

            const nonceField = document.createElement('input');
            nonceField.type = 'hidden';
            nonceField.name = '_wpnonce';
            nonceField.value = nonce;
            form.appendChild(nonceField);

            document.body.appendChild(form);
            form.submit();
        }

        elements.kicker.addEventListener('input', function () {
            updateState(function (draft) {
                draft.kicker = elements.kicker.value;
            });
        });

        elements.title.addEventListener('input', function () {
            updateState(function (draft) {
                draft.title = elements.title.value;
            });
        });

        elements.description.addEventListener('input', function () {
            updateState(function (draft) {
                draft.description = elements.description.value;
            });
        });

        elements.imageAlt.addEventListener('input', function () {
            updateState(function (draft) {
                draft.imageAlt = elements.imageAlt.value;
            });
        });

        elements.clearImage.addEventListener('click', function () {
            activeZoneId = null;

            const removeButton = getNativeMediaRemoveButton();
            if (removeButton) {
                removeButton.click();
            }

            updateState(function (draft) {
                draft.imageUrl = '';
                draft.imageAlt = '';
                draft.zones = [];
            });
        });

        if (nativeMediaInput) {
            nativeMediaInput.addEventListener('change', syncFromNativeMediaField);
            nativeMediaInput.addEventListener('input', syncFromNativeMediaField);
        }

        document.addEventListener('click', function (event) {
            if (!event.target) {
                return;
            }

            if (nativeMediaRow && event.target.closest('.cmb2-id-visual-links-draft-image-source .cmb2-upload-button, .cmb2-id-visual-links-draft-image-source .cmb2-remove-file-button')) {
                window.setTimeout(syncFromNativeMediaField, 300);
            }
        });

        if (!state.imageUrl && nativeMediaInput && nativeMediaInput.value) {
            state.imageUrl = String(nativeMediaInput.value || '').trim();
        }

        elements.resetZones.addEventListener('click', function () {
            if (!state.zones.length) {
                return;
            }

            if (!window.confirm('Êtes-vous sûr de vouloir supprimer toutes les zones ?')) {
                return;
            }

            activeZoneId = null;
            updateState(function (draft) {
                draft.zones = [];
            });
        });

        elements.zonesList.addEventListener('click', function (event) {
            const item = event.target.closest('[data-zone-id]');
            if (!item) {
                return;
            }

            const zoneId = item.getAttribute('data-zone-id');
            if (event.target.matches('[data-zone-action="delete"]')) {
                event.preventDefault();
                updateState(function (draft) {
                    draft.zones = draft.zones.filter(function (zone) {
                        return zone.id !== zoneId;
                    });
                });

                if (activeZoneId === zoneId) {
                    activeZoneId = null;
                }
                return;
            }

            setActiveZone(zoneId);
        });

        elements.zonesList.addEventListener('input', function (event) {
            const item = event.target.closest('[data-zone-id]');
            if (!item) {
                return;
            }

            const zoneId = item.getAttribute('data-zone-id');
            const field = event.target.getAttribute('data-zone-field');
            if (!field) {
                return;
            }

            updateState(function (draft) {
                const zone = draft.zones.find(function (entry) {
                    return entry.id === zoneId;
                });

                if (!zone) {
                    return;
                }

                zone[field] = event.target.value;
                if (field === 'hrefValue' && !zone.label) {
                    zone.label = event.target.value;
                }
            });
        });

        validationInput.addEventListener('change', refreshWorkflowState);

        if (elements.previewLink) {
            elements.previewLink.addEventListener('click', function (event) {
                if (hasUnsavedChanges() && !window.confirm('Le brouillon a changé dans cette page mais n’est pas encore enregistré. Ouvrir malgré tout la dernière version sauvegardée ?')) {
                    event.preventDefault();
                }
            });
        }

        if (elements.publishButton) {
            elements.publishButton.addEventListener('click', function () {
                if (hasUnsavedChanges()) {
                    window.alert('Enregistrez d’abord la page Mayami Landing pour publier le dernier brouillon Visual Links.');
                    return;
                }

                if (!validationInput.checked) {
                    window.alert('Cochez d’abord la validation finale Visual Links puis enregistrez la page.');
                    return;
                }

                buildActionRequest('mayami_publish_epk_draft', root.dataset.publishNonce);
            });
        }

        if (elements.unpublishButton) {
            elements.unpublishButton.addEventListener('click', function () {
                if (!window.confirm('Retirer Visual Links du front public ?')) {
                    return;
                }

                buildActionRequest('mayami_unpublish_epk', root.dataset.unpublishNonce);
            });
        }

        elements.canvasOverlay.addEventListener('mousedown', function (event) {
            if (!state.imageUrl) {
                return;
            }

            if (event.target.closest('.mayami-vlb-zone-box')) {
                return;
            }

            const rect = elements.canvasOverlay.getBoundingClientRect();
            startX = clamp(event.clientX - rect.left, 0, rect.width);
            startY = clamp(event.clientY - rect.top, 0, rect.height);
            isDrawing = true;

            currentBox = document.createElement('span');
            currentBox.className = 'mayami-vlb-zone-box';
            currentBox.style.left = startX + 'px';
            currentBox.style.top = startY + 'px';
            currentBox.style.width = '0px';
            currentBox.style.height = '0px';
            elements.canvasOverlay.appendChild(currentBox);
        });

        window.addEventListener('mousemove', function (event) {
            if (!isDrawing || !currentBox) {
                return;
            }

            const rect = elements.canvasOverlay.getBoundingClientRect();
            const currentX = clamp(event.clientX - rect.left, 0, rect.width);
            const currentY = clamp(event.clientY - rect.top, 0, rect.height);
            const left = Math.min(startX, currentX);
            const top = Math.min(startY, currentY);
            const width = Math.abs(currentX - startX);
            const height = Math.abs(currentY - startY);

            currentBox.style.left = left + 'px';
            currentBox.style.top = top + 'px';
            currentBox.style.width = width + 'px';
            currentBox.style.height = height + 'px';
        });

        window.addEventListener('mouseup', function (event) {
            if (!isDrawing || !currentBox) {
                return;
            }

            const rect = elements.canvasOverlay.getBoundingClientRect();
            const currentX = clamp(event.clientX - rect.left, 0, rect.width);
            const currentY = clamp(event.clientY - rect.top, 0, rect.height);
            const left = Math.min(startX, currentX);
            const top = Math.min(startY, currentY);
            const width = Math.abs(currentX - startX);
            const height = Math.abs(currentY - startY);

            currentBox.remove();
            currentBox = null;
            isDrawing = false;

            if (width < 10 || height < 10) {
                return;
            }

            const newZone = {
                id: 'zone_' + Date.now(),
                label: '',
                hrefType: 'url',
                hrefValue: '',
                x: Number(((left / rect.width) * 100).toFixed(4)),
                y: Number(((top / rect.height) * 100).toFixed(4)),
                width: Number(((width / rect.width) * 100).toFixed(4)),
                height: Number(((height / rect.height) * 100).toFixed(4))
            };

            updateState(function (draft) {
                draft.zones.push(newZone);
            });
            setActiveZone(newZone.id);
        });

        syncHiddenValue(false);
        redrawAll();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.mayami-vlb-builder').forEach(initBuilder);
    });
})();