(function () {
    'use strict';

    window.EmWpTemplateWizard = window.EmWpTemplateWizard || {};

    var State = EmWpTemplateWizard.State;
    var Confirm = EmWpTemplateWizard.Confirm;

    EmWpTemplateWizard.Draft = {
        listEl: null,
        emptyEl: null,
        newBtn: null,
        navigation: null,
        legacyKeySuffix: '_legacy_single',
        isEditingDraftName: false,
        restoreGrantKey: 'em_site_wizard_restore_grant',
        newLaunchGrantKey: 'em_site_wizard_new_launch',
        workspaceLaunchGrantKey: 'em_site_wizard_workspace_launch',
        serverStore: null,

        getKey: function () {
            return (State.config && State.config.draftStorageKey) || 'em_site_template_wizard_drafts';
        },

        getLegacyKey: function () {
            return this.getKey() + this.legacyKeySuffix;
        },

        getNamePlaceholder: function () {
            var onboarding = State.config.onboarding || {};
            var i18n = State.config.i18n || {};

            return onboarding.draftContextNamePlaceholder
                || i18n.labelPlaceholder
                || 'Nom à définir';
        },

        applyLabelInputPlaceholder: function (labelInput) {
            if (!labelInput) {
                return;
            }

            labelInput.setAttribute('placeholder', this.getNamePlaceholder());
        },

        slugify: function (label) {
            return String(label || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .substring(0, 48) || '';
        },

        resolveTemplateSlug: function (label) {
            var base = this.slugify(label);
            var existing = (State.config && State.config.existingTemplateSlugs) || [];
            var slug = base;
            var suffix = 2;

            if (base === '') {
                return {
                    slug: '',
                    reserved: false,
                };
            }

            while (existing.indexOf(slug) !== -1) {
                slug = base + '-' + String(suffix);
                suffix += 1;
            }

            return {
                slug: slug,
                reserved: slug !== base,
            };
        },

        migrateLegacy: function () {
            try {
                var newKey = this.getKey();
                var oldKey = newKey.replace('_drafts_', '_draft_');
                var legacyRaw = window.localStorage.getItem(oldKey);
                if (!legacyRaw) {
                    return;
                }
                var legacy = JSON.parse(legacyRaw);
                if (!legacy || !legacy.label) {
                    window.localStorage.removeItem(oldKey);
                    return;
                }
                var store = this.readAllWithoutMigrate();
                legacy.id = legacy.id || (this.slugify(legacy.label) + '-' + String(legacy.savedAt || Date.now()));
                var exists = store.drafts.some(function (item) {
                    return item.id === legacy.id
                        || String(item.label || '').toLowerCase() === String(legacy.label || '').toLowerCase();
                });
                if (!exists) {
                    store.drafts.push(legacy);
                    this.writeAll(store);
                }
                window.localStorage.removeItem(oldKey);
            } catch (err) {
                /* noop */
            }
        },

        readAllWithoutMigrate: function () {
            try {
                var raw = window.localStorage.getItem(this.getKey());
                if (!raw) {
                    return { drafts: [] };
                }
                var data = JSON.parse(raw);
                if (Array.isArray(data)) {
                    return { drafts: data };
                }
                if (data && Array.isArray(data.drafts)) {
                    return data;
                }
                if (data && data.label) {
                    data.id = data.id || (this.slugify(data.label) + '-' + String(data.savedAt || Date.now()));
                    return { drafts: [data] };
                }
                return { drafts: [] };
            } catch (err) {
                return { drafts: [] };
            }
        },

        readAll: function () {
            if (!this.serverStore) {
                this.hydrateStore();
            }

            return this.serverStore;
        },

        hydrateStore: function (drafts) {
            var list = Array.isArray(drafts)
                ? drafts
                : ((State.config && State.config.serverDrafts) || []);

            this.serverStore = {
                drafts: JSON.parse(JSON.stringify(list)),
            };

            this.mirrorLocalStore(this.serverStore);
        },

        persistStore: function (drafts) {
            this.serverStore = {
                drafts: Array.isArray(drafts) ? JSON.parse(JSON.stringify(drafts)) : [],
            };

            if (State.config) {
                State.config.serverDrafts = JSON.parse(JSON.stringify(this.serverStore.drafts));
            }

            this.mirrorLocalStore(this.serverStore);
        },

        mirrorLocalStore: function (store) {
            try {
                window.localStorage.setItem(this.getKey(), JSON.stringify(store));
                return true;
            } catch (err) {
                return false;
            }
        },

        writeAll: function (store) {
            this.serverStore = store;
            return this.mirrorLocalStore(store);
        },

        saveToServer: function (snapshot) {
            var config = State.config || {};
            var body = new URLSearchParams();

            body.append('action', 'em_site_template_wizard_save_draft');
            body.append('nonce', config.draftNonce || '');
            body.append('snapshot', JSON.stringify(snapshot));

            return fetch(config.ajaxUrl || '', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: body.toString(),
            }).then(function (response) {
                return response.json();
            });
        },

        deleteFromServer: function (draftId) {
            var config = State.config || {};
            var body = new URLSearchParams();

            body.append('action', 'em_site_template_wizard_delete_draft');
            body.append('nonce', config.draftNonce || '');
            body.append('draft_id', String(draftId || ''));

            return fetch(config.ajaxUrl || '', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: body.toString(),
            }).then(function (response) {
                return response.json();
            });
        },

        syncWorkspaceDraftUrl: function (draftId) {
            var page = document.querySelector('[data-wizard-view]');

            if (!page || page.getAttribute('data-wizard-view') !== 'edit' || !draftId) {
                return;
            }

            try {
                var url = new URL(window.location.href);
                url.searchParams.set('em_site_mode', 'edit');
                url.searchParams.set('em_site_draft', String(draftId));
                window.history.replaceState({}, '', url.toString());
                page.setAttribute('data-wizard-resume-id', String(draftId));
            } catch (err) {
                /* noop */
            }
        },

        findById: function (id) {
            var store = this.readAll();
            var i;
            for (i = 0; i < store.drafts.length; i++) {
                if (store.drafts[i].id === id) {
                    return store.drafts[i];
                }
            }
            return null;
        },

        removeById: function (id) {
            var self = this;
            var i18n = State.config.i18n || {};

            return this.deleteFromServer(id).then(function (response) {
                if (!response || !response.success) {
                    var msg = response && response.data && response.data.message
                        ? response.data.message
                        : (i18n.draftDeleteError || 'Impossible de supprimer le brouillon.');

                    Confirm.alert(msg, { title: i18n.draftDelete || 'Supprimer' });
                    return false;
                }

                self.persistStore(response.data.drafts || []);

                if (self.navigation && self.navigation.activeDraftId === id) {
                    self.navigation.activeDraftId = null;
                }

                self.renderList();
                return true;
            }).catch(function () {
                Confirm.alert(i18n.draftDeleteError || 'Impossible de supprimer le brouillon.', {
                    title: i18n.draftDelete || 'Supprimer',
                });
                return false;
            });
        },

        stepLabel: function (step) {
            var labels = (State.config.onboarding && State.config.onboarding.stepLabels) || [];
            var i18n = State.config.i18n || {};
            var name = labels[step] || '';
            if (name === '') {
                return i18n.draftStepFallback
                    ? i18n.draftStepFallback.replace('%s', String(step + 1))
                    : ('Étape ' + String(step + 1));
            }
            return i18n.draftStepProgress
                ? i18n.draftStepProgress.replace('%1$s', String(step + 1)).replace('%2$s', name)
                : ('Étape ' + String(step + 1) + ' — ' + name);
        },

        formatSavedAt: function (timestamp) {
            if (!timestamp) {
                return '';
            }
            try {
                return new Intl.DateTimeFormat(undefined, {
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit',
                }).format(new Date(timestamp));
            } catch (err) {
                return '';
            }
        },

        editUrl: function (id) {
            var base = (State.config && State.config.createHubUrl) || window.location.pathname + window.location.search.split('&em_site')[0];
            var join = base.indexOf('?') >= 0 ? '&' : '?';
            return base + join + 'em_site_mode=edit&em_site_draft=' + encodeURIComponent(String(id || ''));
        },

        hubUrl: function () {
            return (State.config && State.config.createHubUrl) || '';
        },

        workspaceUrl: function () {
            return (State.config && State.config.createWorkspaceUrl) || this.hubUrl();
        },

        grantRestore: function (draftId) {
            try {
                sessionStorage.setItem(this.restoreGrantKey, String(draftId || ''));
            } catch (err) {
                /* noop */
            }
        },

        consumeRestoreGrant: function (draftId) {
            try {
                var granted = sessionStorage.getItem(this.restoreGrantKey);

                if (granted && granted === String(draftId || '')) {
                    sessionStorage.removeItem(this.restoreGrantKey);
                    return true;
                }
            } catch (err) {
                /* noop */
            }

            return false;
        },

        grantWorkspaceLaunch: function () {
            try {
                sessionStorage.setItem(this.workspaceLaunchGrantKey, '1');
            } catch (err) {
                /* noop */
            }
        },

        consumeWorkspaceLaunchGrant: function () {
            var keys = [this.workspaceLaunchGrantKey, this.newLaunchGrantKey];
            var i;

            try {
                for (i = 0; i < keys.length; i += 1) {
                    if (sessionStorage.getItem(keys[i]) === '1') {
                        sessionStorage.removeItem(keys[i]);
                        return true;
                    }
                }
            } catch (err) {
                /* noop */
            }

            return false;
        },

        grantNewLaunch: function () {
            this.grantWorkspaceLaunch();
        },

        consumeNewLaunchGrant: function () {
            return this.consumeWorkspaceLaunchGrant();
        },

        clearResumeUrl: function () {
            var page = document.querySelector('[data-wizard-view]');

            try {
                var url = new URL(window.location.href);
                url.searchParams.delete('em_site_draft');
                window.history.replaceState({}, '', url.toString());
            } catch (err) {
                /* noop */
            }

            if (page) {
                page.removeAttribute('data-wizard-resume-id');
            }
        },

        resetLaunchState: function (navigation) {
            var Guide = EmWpTemplateWizard.Guide;
            var labelInput = document.getElementById('em-site-template-new-label');
            var colorInput = document.getElementById('em-site-template-new-color');

            if (navigation) {
                navigation.activeDraftId = null;
                navigation.currentStep = 0;

                if (navigation.wizard) {
                    navigation.wizard.hidden = true;
                    navigation.wizard.setAttribute('aria-hidden', 'true');
                }

                if (navigation.identityPanel) {
                    navigation.identityPanel.hidden = Guide
                        && Guide.usesProgressIdentityPanel
                        && Guide.usesProgressIdentityPanel();
                }
            }

            if (labelInput) {
                labelInput.value = '';
                this.applyLabelInputPlaceholder(labelInput);
            }

            if (colorInput) {
                if (window.emWpAdminColorFieldApi) {
                    window.emWpAdminColorFieldApi.setValue(colorInput, '');
                } else {
                    colorInput.value = '';
                }
            }

            State.draft = null;
            State.dirty = false;

            if (Guide) {
                if (Guide.setValidatedActions) {
                    Guide.setValidatedActions({});
                }
                if (Guide.showOverview) {
                    Guide.showOverview();
                }
                if (Guide.showStep) {
                    Guide.showStep(0);
                }
                if (Guide.clearFocus) {
                    Guide.clearFocus();
                }
            }

            this.syncDraftContextName('');
            this.syncDraftContextKicker(0);
            this.syncDraftContextProgress(0);

            if (Guide && Guide.syncResetButton) {
                Guide.syncResetButton(0);
            }
        },

        handleRestoreDenied: function (navigation) {
            this.clearResumeUrl();
            this.resetLaunchState(navigation);
        },

        requestReset: function () {
            var self = this;
            var i18n = State.config.i18n || {};
            var resetUrl = this.workspaceUrl();

            Confirm.ask(i18n.wizardResetConfirm || 'Réinitialiser l’assistant ? Les saisies non enregistrées seront perdues.', {
                title: i18n.wizardResetTitle || 'Reset',
                confirmLabel: i18n.wizardResetTitle || 'Reset',
                cancelLabel: i18n.draftLeaveCancel || 'Annuler',
            }).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                try {
                    sessionStorage.removeItem(self.restoreGrantKey);
                    sessionStorage.removeItem(self.newLaunchGrantKey);
                    sessionStorage.removeItem(self.workspaceLaunchGrantKey);
                } catch (err) {
                    /* noop */
                }

                if (resetUrl) {
                    window.location.href = resetUrl;
                }
            });
        },

        bindHubLaunchLinks: function () {
            var self = this;

            document.querySelectorAll('[data-wizard-workspace-launch], [data-wizard-new-launch]').forEach(function (link) {
                if (link.dataset.launchBound === '1') {
                    return;
                }

                link.dataset.launchBound = '1';
                link.addEventListener('click', function () {
                    self.grantWorkspaceLaunch();
                });
            });
        },

        bindResumeLinks: function (root) {
            var self = this;

            if (!root) {
                return;
            }

            root.querySelectorAll('a[href*="em_site_draft="]').forEach(function (link) {
                if (link.dataset.resumeBound === '1') {
                    return;
                }

                link.dataset.resumeBound = '1';
                link.addEventListener('click', function () {
                    var draftId = '';

                    try {
                        draftId = new URL(link.href, window.location.origin).searchParams.get('em_site_draft') || '';
                    } catch (err) {
                        draftId = '';
                    }

                    if (draftId !== '') {
                        self.grantRestore(draftId);
                    }
                });
            });
        },

        syncDraftContextKicker: function (step) {
            var kickerEl = document.querySelector('[data-wizard-draft-kicker]');
            var onboarding = State.config.onboarding || {};
            var tpl = onboarding.draftContextKicker
                || 'Création d\'un nouveau template — brouillon en cours — étape %1$s/%2$s';
            var labels = onboarding.stepLabels || [];
            var total = labels.length || 3;
            var navigation = this.navigation;
            var currentStep = typeof step === 'number'
                ? step
                : (navigation ? navigation.currentStep : 0);

            if (!kickerEl) {
                return;
            }

            kickerEl.textContent = tpl
                .replace('%1$s', String(currentStep + 1))
                .replace('%2$s', String(total));
        },

        computeProgressPercent: function (step) {
            var Guide = EmWpTemplateWizard.Guide;
            var labels = (State.config.onboarding && State.config.onboarding.stepLabels) || [];
            var totalSteps = labels.length || 3;
            var progressCfg = (State.config.onboarding && State.config.onboarding.progress) || {};
            var stepWeight = 100 / totalSteps;
            var completed = 0;
            var s;

            if (step >= totalSteps - 1) {
                return 100;
            }

            for (s = 0; s < step; s += 1) {
                completed += stepWeight;
            }

            if (Guide && Guide.requiresManualValidation(step)) {
                var actions = (progressCfg.steps && progressCfg.steps[step]) || [];

                if (actions.length > 0) {
                    var validated = 0;

                    actions.forEach(function (action) {
                        if (Guide.isActionValidated(step, action.key)) {
                            validated += 1;
                        }
                    });

                    completed += (validated / actions.length) * stepWeight;
                }
            }

            return Math.min(100, Math.round(completed));
        },

        syncDraftContextProgress: function (step) {
            var progressEl = document.querySelector('[data-wizard-draft-progress]');
            var onboarding = State.config.onboarding || {};
            var tpl = onboarding.draftContextProgress || 'Progression : %s';
            var navigation = this.navigation;
            var currentStep = typeof step === 'number'
                ? step
                : (navigation ? navigation.currentStep : 0);
            var percent = this.computeProgressPercent(currentStep);

            if (!progressEl) {
                return;
            }

            progressEl.textContent = tpl.replace('%s', String(percent) + ' %');
        },

        setDraftContext: function (stored, navigation) {
            var root = document.querySelector('[data-wizard-draft-context]');
            var label = stored && stored.label ? String(stored.label).trim() : '';
            var step = stored ? Number(stored.currentStep || 0) : 0;

            if (!root) {
                return;
            }

            root.hidden = false;
            this.syncDraftContextName(label);
            this.syncDraftContextKicker(step);
            this.syncDraftContextProgress(step);

            if (navigation) {
                navigation.activeDraftId = stored && stored.id ? stored.id : navigation.activeDraftId;
            }

            this.syncDraftContextEditVisibility(step);
        },

        syncDraftContextName: function (label) {
            var nameEl = document.querySelector('[data-wizard-draft-name]');
            var root = document.querySelector('[data-wizard-draft-context]');
            var trimmed = String(label || '').trim();
            var placeholder = this.getNamePlaceholder();

            if (root) {
                root.hidden = false;
            }

            if (!nameEl) {
                this.syncDraftContextIdentifier(trimmed);
                return;
            }

            if (trimmed === '') {
                nameEl.textContent = placeholder;
                nameEl.classList.add('is-placeholder');
            } else {
                nameEl.textContent = trimmed;
                nameEl.classList.remove('is-placeholder');
            }

            this.syncDraftContextIdentifier(trimmed);
        },

        syncLaunchContext: function (resumeId) {
            var page = document.querySelector('[data-wizard-view]');
            var view = page ? page.getAttribute('data-wizard-view') : 'hub';
            var labelInput = document.getElementById('em-site-template-new-label');

            this.applyLabelInputPlaceholder(labelInput);

            if (view !== 'edit' || resumeId) {
                return;
            }

            this.syncDraftContextName(labelInput ? labelInput.value : '');
            this.syncDraftContextKicker(0);
            this.syncDraftContextProgress(0);
        },

        syncDraftContextIdentifier: function (label) {
            var idEl = document.querySelector('[data-wizard-draft-id]');
            var slugEl = document.querySelector('[data-wizard-draft-slug]');
            var trimmed = String(label || '').trim();
            var resolved = this.resolveTemplateSlug(trimmed);

            if (!idEl || !slugEl) {
                return;
            }

            if (resolved.slug === '') {
                slugEl.textContent = '';
                idEl.hidden = true;
                idEl.classList.remove('is-reserved');
                return;
            }

            slugEl.textContent = resolved.slug;
            idEl.hidden = false;
            idEl.classList.toggle('is-reserved', resolved.reserved);
        },

        syncDraftContextStep: function (step) {
            this.syncDraftContextKicker(step);
            this.syncDraftContextProgress(step);
            this.syncDraftContextEditVisibility(step);
        },

        syncDraftContextEditVisibility: function (step) {
            var editBtn = document.querySelector('[data-wizard-draft-name-edit]');
            var editGroup = document.querySelector('[data-wizard-draft-name-edit-group]');
            var nameInput = document.querySelector('[data-wizard-draft-name-input]');
            var nameEl = document.querySelector('[data-wizard-draft-name]');
            var showEdit = Number(step) === 0;
            var isEditing = editGroup && !editGroup.hidden;

            if (editBtn) {
                editBtn.hidden = !showEdit || isEditing;
            }

            if (!showEdit) {
                if (editGroup) {
                    editGroup.hidden = true;
                }
                if (nameInput && nameEl && !nameInput.hidden) {
                    nameInput.hidden = true;
                    nameEl.hidden = false;
                }
            }
        },

        bindDraftNameEdit: function () {
            var editBtn = document.querySelector('[data-wizard-draft-name-edit]');
            var editGroup = document.querySelector('[data-wizard-draft-name-edit-group]');
            var confirmBtn = document.querySelector('[data-wizard-draft-name-confirm]');
            var cancelBtn = document.querySelector('[data-wizard-draft-name-cancel]');
            var nameEl = document.querySelector('[data-wizard-draft-name]');
            var nameInput = document.querySelector('[data-wizard-draft-name-input]');
            var labelInput = document.getElementById('em-site-template-new-label');
            var self = this;
            var originalName = '';

            if (!editBtn || !editGroup || !confirmBtn || !cancelBtn || !nameEl || !nameInput || editBtn.dataset.bound === '1') {
                return;
            }

            editBtn.dataset.bound = '1';

            function syncInlineName(value) {
                var next = String(value || '');

                if (labelInput) {
                    labelInput.value = next;
                }

                self.syncDraftContextIdentifier(next);

                if (EmWpTemplateWizard.Guide && EmWpTemplateWizard.Guide.invalidateAction) {
                    EmWpTemplateWizard.Guide.invalidateAction(0, 'label');
                }

                if (EmWpTemplateWizard.Guide && EmWpTemplateWizard.Guide.refreshIdentityGuide) {
                    EmWpTemplateWizard.Guide.refreshIdentityGuide();
                }
            }

            function setEditing(active) {
                self.isEditingDraftName = active;
                editGroup.hidden = !active;
                nameEl.hidden = active;
                editBtn.hidden = active || !self.shouldShowNameEdit();
            }

            function finishEdit(save) {
                var savedName = String(originalName || nameEl.textContent || '').trim();
                var nextLabel = save ? String(nameInput.value || '').trim() : savedName;

                if (save && nextLabel === '') {
                    nameInput.value = savedName;
                    nameInput.focus();
                    return;
                }

                setEditing(false);

                if (!save) {
                    nameEl.textContent = savedName;
                    if (labelInput) {
                        labelInput.value = savedName;
                    }
                    self.syncDraftContextIdentifier(savedName);
                    if (EmWpTemplateWizard.Guide && EmWpTemplateWizard.Guide.refreshIdentityGuide) {
                        EmWpTemplateWizard.Guide.refreshIdentityGuide();
                    }
                    return;
                }

                nameEl.textContent = nextLabel;
                if (labelInput) {
                    labelInput.value = nextLabel;
                }

                if (nextLabel !== savedName && EmWpTemplateWizard.Guide && EmWpTemplateWizard.Guide.invalidateAction) {
                    EmWpTemplateWizard.Guide.invalidateAction(0, 'label');
                }

                self.syncDraftContextName(nextLabel);
                State.markDirty();

                if (EmWpTemplateWizard.Guide && EmWpTemplateWizard.Guide.refreshIdentityGuide) {
                    EmWpTemplateWizard.Guide.refreshIdentityGuide();
                }
            }

            editBtn.addEventListener('click', function () {
                originalName = labelInput
                    ? String(labelInput.value || '').trim()
                    : String(nameEl.textContent || '').trim();
                nameInput.value = originalName;
                setEditing(true);
                window.requestAnimationFrame(function () {
                    nameInput.focus();
                    nameInput.select();
                });
            });

            [confirmBtn, cancelBtn].forEach(function (btn) {
                btn.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                });
            });

            confirmBtn.addEventListener('click', function () {
                finishEdit(true);
            });

            cancelBtn.addEventListener('click', function () {
                finishEdit(false);
            });

            nameInput.addEventListener('input', function () {
                syncInlineName(nameInput.value);
            });

            nameInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    finishEdit(true);
                }
                if (event.key === 'Escape') {
                    event.preventDefault();
                    finishEdit(false);
                }
            });
        },

        shouldShowNameEdit: function () {
            var navigation = this.navigation;

            return navigation && Number(navigation.currentStep) === 0;
        },

        isEditingDraftNameActive: function () {
            return this.isEditingDraftName === true;
        },

        initList: function (navigation) {
            this.navigation = navigation;
            this.listEl = document.querySelector('[data-wizard-drafts-list]');
            this.emptyEl = document.querySelector('[data-wizard-drafts-empty]');
            this.hydrateStore();
            this.bindHubLaunchLinks();
            this.renderList();
        },

        renderList: function () {
            var store = this.readAll();
            var drafts = store.drafts.slice().sort(function (a, b) {
                return Number(b.savedAt || 0) - Number(a.savedAt || 0);
            });
            var self = this;
            var i18n = State.config.i18n || {};

            if (!this.listEl || !this.emptyEl) {
                return;
            }

            this.listEl.innerHTML = '';

            if (drafts.length === 0) {
                this.listEl.hidden = true;
                this.emptyEl.hidden = false;
                return;
            }

            this.emptyEl.hidden = true;
            this.listEl.hidden = false;

            drafts.forEach(function (item) {
                var li = document.createElement('li');
                li.className = 'em-site-template-wizard-drafts__item';

                var color = item.color || '#cccccc';
                var step = Number(item.currentStep || 0);
                var savedLabel = self.formatSavedAt(item.savedAt);
                var resumeUrl = self.editUrl(item.id);

                li.innerHTML =
                    '<div class="em-site-template-wizard-drafts__meta">' +
                        '<span class="em-site-template-wizard-drafts__swatch" style="--em-template-swatch:' + color + ';"></span>' +
                        '<span class="em-site-template-wizard-drafts__name">' + self.escapeHtml(item.label || '—') + '</span>' +
                        '<span class="em-site-template-wizard-drafts__step">' + self.escapeHtml(self.stepLabel(step)) + '</span>' +
                        (savedLabel ? '<span class="em-site-template-wizard-drafts__date">' + self.escapeHtml(savedLabel) + '</span>' : '') +
                    '</div>' +
                    '<div class="em-site-template-wizard-drafts__actions">' +
                        '<a class="em-site-hub__action em-site-hub__action--compact" href="' + self.escapeAttr(resumeUrl) + '">' +
                            '<span class="em-site-hub__action-inner"><span class="em-site-hub__action-label">' +
                                self.escapeHtml(i18n.draftResume || 'Reprendre') +
                            '</span></span>' +
                        '</a>' +
                        '<button type="button" class="em-site-template-wizard-drafts__delete" data-wizard-draft-delete="' + self.escapeAttr(item.id) + '" title="' + self.escapeAttr(i18n.draftDelete || 'Supprimer') + '">' +
                            '<i class="fa-solid fa-trash-can" aria-hidden="true"></i>' +
                        '</button>' +
                    '</div>';

                self.listEl.appendChild(li);
            });

            this.listEl.querySelectorAll('[data-wizard-draft-delete]').forEach(function (btn) {
                btn.addEventListener('click', function (event) {
                    event.preventDefault();
                    self.requestDelete(btn.getAttribute('data-wizard-draft-delete'));
                });
            });

            this.bindResumeLinks(this.listEl);
        },

        escapeHtml: function (value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },

        escapeAttr: function (value) {
            return this.escapeHtml(value).replace(/'/g, '&#39;');
        },

        buildSnapshot: function (labelInput, colorInput, currentStep, activeId) {
            var label = labelInput ? String(labelInput.value || '').trim() : '';
            var color = colorInput ? String(colorInput.value || '').trim() : '';
            var Guide = EmWpTemplateWizard.Guide;
            var draft = State.getDraft();
            var step = typeof currentStep === 'number' ? currentStep : 0;
            var id = activeId || (this.slugify(label) + '-' + String(Date.now()));

            if (!color && Guide && Guide.getColorValue) {
                color = Guide.getColorValue();
            }

            if (draft) {
                draft.label = label || draft.label;
                draft.color = color || draft.color;
            }

            return {
                id: id,
                savedAt: Date.now(),
                currentStep: step,
                label: label,
                color: color,
                draft: draft ? State.getPayload() : null,
                validatedActions: EmWpTemplateWizard.Guide && EmWpTemplateWizard.Guide.getValidatedActionsExport
                    ? EmWpTemplateWizard.Guide.getValidatedActionsExport()
                    : {},
            };
        },

        save: function (labelInput, colorInput, currentStep, activeId, redirectUrl, stayOnPage, silent) {
            var i18n = State.config.i18n || {};
            var label = labelInput ? String(labelInput.value || '').trim() : '';
            var self = this;

            if (!label) {
                Confirm.alert(i18n.draftNameRequired || 'Saisis au minimum un nom.', { title: 'Brouillon' });
                if (labelInput) {
                    labelInput.focus();
                }
                return Promise.resolve(false);
            }

            var snapshot = this.buildSnapshot(labelInput, colorInput, currentStep, activeId);

            return this.saveToServer(snapshot).then(function (response) {
                if (!response || !response.success) {
                    var msg = response && response.data && response.data.message
                        ? response.data.message
                        : (i18n.draftSaveError || 'Impossible d’enregistrer le brouillon.');

                    Confirm.alert(msg, { title: 'Brouillon' });
                    return false;
                }

                var saved = response.data && response.data.draft ? response.data.draft : snapshot;

                self.persistStore(
                    response.data && Array.isArray(response.data.drafts)
                        ? response.data.drafts
                        : [saved]
                );

                if (self.navigation) {
                    self.navigation.activeDraftId = saved.id;
                }

                State.dirty = false;
                self.syncWorkspaceDraftUrl(saved.id);
                self.syncDraftContextName(label);
                self.syncDraftContextKicker(typeof currentStep === 'number' ? currentStep : 0);
                self.syncDraftContextProgress(typeof currentStep === 'number' ? currentStep : 0);

                if (redirectUrl) {
                    window.location.href = redirectUrl;
                    return true;
                }

                if (silent) {
                    return true;
                }

                if (stayOnPage) {
                    Confirm.alert(i18n.draftProgressSaved || 'Progression enregistrée.', { title: 'Brouillon' });
                    return true;
                }

                var hubUrl = self.hubUrl();
                if (hubUrl) {
                    window.location.href = hubUrl;
                    return true;
                }

                self.renderList();
                Confirm.alert(i18n.draftSaved || 'Brouillon enregistré.', { title: 'Brouillon' });
                return true;
            }).catch(function () {
                Confirm.alert(i18n.draftSaveError || 'Impossible d’enregistrer le brouillon.', { title: 'Brouillon' });
                return false;
            });
        },

        shouldConfirmLeave: function () {
            var page = document.querySelector('[data-wizard-view]');
            var view = page ? page.getAttribute('data-wizard-view') : 'hub';

            return view === 'new' || view === 'edit';
        },

        normalizeNavUrl: function (url) {
            try {
                var parsed = new URL(url, window.location.origin);
                parsed.hash = '';
                return parsed.href;
            } catch (err) {
                return String(url || '');
            }
        },

        bindNavTabs: function () {
            var self = this;

            document.querySelectorAll('[data-wizard-nav-tab]').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    var targetUrl = link.getAttribute('href') || '';

                    if (targetUrl === '' || self.normalizeNavUrl(targetUrl) === self.normalizeNavUrl(window.location.href)) {
                        return;
                    }

                    if (!self.shouldConfirmLeave()) {
                        return;
                    }

                    event.preventDefault();
                    self.requestLeave(targetUrl);
                }, true);
            });
        },

        requestLeave: function (targetUrl) {
            var self = this;
            var i18n = State.config.i18n || {};
            var navigation = this.navigation;
            var labelInput = document.getElementById('em-site-template-new-label');
            var colorInput = document.getElementById('em-site-template-new-color');
            var step = navigation ? navigation.currentStep : 0;
            var activeId = navigation ? navigation.activeDraftId : null;

            Confirm.ask(i18n.draftLeaveConfirm || 'Enregistrer l’avancement en cours avant de quitter ?', {
                title: i18n.draftLeaveTitle || 'Quitter l’assistant',
                confirmLabel: i18n.draftLeaveSave || 'Enregistrer',
                cancelLabel: i18n.draftLeaveCancel || 'Annuler',
            }).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                var label = labelInput ? String(labelInput.value || '').trim() : '';

                if (!label) {
                    Confirm.ask(i18n.draftLeaveWithoutSave || 'Quitter sans enregistrer ?', {
                        title: i18n.draftLeaveTitle || 'Quitter l’assistant',
                        confirmLabel: i18n.draftLeaveQuit || 'Quitter sans enregistrer',
                        cancelLabel: i18n.draftLeaveCancel || 'Annuler',
                    }).then(function (quit) {
                        if (quit) {
                            window.location.href = targetUrl;
                        }
                    });
                    return;
                }

                self.save(labelInput, colorInput, step, activeId, targetUrl);
            });
        },

        requestDelete: function (id) {
            var self = this;
            var item = this.findById(id);
            var i18n = State.config.i18n || {};
            var label = item ? item.label : '';
            var msg = (i18n.draftDeleteConfirm || 'Supprimer le brouillon « %s » ?').replace('%s', label);

            if (!Confirm || typeof Confirm.confirmDelete !== 'function') {
                Confirm.ask(msg, {
                    title: i18n.draftDelete || 'Supprimer',
                    confirmLabel: i18n.draftDelete || 'Supprimer',
                }).then(function (ok) {
                    if (ok) {
                        self.removeById(id);
                    }
                });
                return;
            }

            Confirm.confirmDelete(function () {
                self.removeById(id);
            }, {
                message: msg,
                secondMessage: 'La suppression du brouillon « ' + label + ' » est définitive.',
                acknowledgeLabel: 'Je confirme vouloir supprimer définitivement ce brouillon.',
                confirmLabel: i18n.draftDelete || 'Supprimer définitivement',
            });
        },

        applyRestore: function (stored, navigation) {
            var labelInput = document.getElementById('em-site-template-new-label');
            var colorInput = document.getElementById('em-site-template-new-color');
            var Guide = EmWpTemplateWizard.Guide;
            var self = this;

            this.setDraftContext(stored, navigation);

            if (EmWpTemplateWizard.Guide && EmWpTemplateWizard.Guide.setValidatedActions) {
                EmWpTemplateWizard.Guide.setValidatedActions(stored.validatedActions || {});
            }

            if (labelInput) {
                labelInput.value = stored.label || '';
                this.applyLabelInputPlaceholder(labelInput);
                this.syncDraftContextName(labelInput.value);
            }
            if (colorInput) {
                if (window.emWpAdminColorFieldApi) {
                    window.emWpAdminColorFieldApi.setValue(colorInput, stored.color || '');
                } else {
                    colorInput.value = stored.color || '';
                }
            }

            var step = Number(stored.currentStep || 0);
            if (step > 1) {
                step = 1;
            }
            State.draft = null;
            State.dirty = false;

            if (navigation.wizard) {
                navigation.wizard.hidden = true;
                navigation.wizard.setAttribute('aria-hidden', 'true');
            }
            if (navigation.identityPanel) {
                navigation.identityPanel.hidden = Guide && Guide.usesProgressIdentityPanel && Guide.usesProgressIdentityPanel();
            }
            if (Guide) {
                Guide.showOverview();
            }

            if (stored.draft && step >= 1) {
                State.draft = JSON.parse(JSON.stringify(stored.draft));
                navigation.syncRecap(labelInput, colorInput);
                navigation.wizard.hidden = false;
                navigation.wizard.setAttribute('aria-hidden', 'false');
                if (navigation.identityPanel) {
                    navigation.identityPanel.hidden = true;
                }
                if (Guide) {
                    Guide.hideOverview();
                }
                navigation.setStep(step);
                self.syncDraftContextStep(step);
            } else if (step >= 1 && State.createDraft) {
                State.createDraft(stored.label || '', stored.color || '');
                navigation.syncRecap(labelInput, colorInput);

                if (navigation.wizard) {
                    navigation.wizard.hidden = false;
                    navigation.wizard.setAttribute('aria-hidden', 'false');
                }
                if (navigation.identityPanel) {
                    navigation.identityPanel.hidden = Guide && Guide.usesProgressIdentityPanel && Guide.usesProgressIdentityPanel();
                }
                if (Guide) {
                    Guide.hideOverview();
                }

                navigation.setStep(step);
                self.syncDraftContextStep(step);
            } else {
                navigation.currentStep = 0;
                if (Guide) {
                    window.setTimeout(function () {
                        Guide.showStep(0);
                    }, 0);
                }
            }
        },

        removeActive: function (navigation) {
            if (navigation && navigation.activeDraftId) {
                this.removeById(navigation.activeDraftId);
            }
        },
    };
})();
