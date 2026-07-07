(function () {
    'use strict';

    window.EmWpTemplateWizard = window.EmWpTemplateWizard || {};

    var State = EmWpTemplateWizard.State;

    EmWpTemplateWizard.Guide = {
        root: null,
        stepEl: null,
        textEl: null,
        progressRoot: null,
        progressBadge: null,
        progressTitle: null,
        progressChecklist: null,
        progressWorkspace: null,
        progressFieldsEl: null,
        identityStashEl: null,
        identityRowEl: null,
        isProgressMode: false,
        focusClass: 'em-wp-template-wizard-guide__target--focus',
        progressFieldActiveClass: 'em-wp-template-wizard-progress__field-active',
        doneClass: 'em-wp-template-wizard-guide__target--done',
        identityTargets: null,
        currentStep: 0,
        validatedActions: {},

        init: function () {
            var page = document.querySelector('[data-wizard-view]');
            this.isProgressMode = page && page.getAttribute('data-wizard-view') === 'edit';

            if (this.isProgressMode) {
                this.progressRoot = document.querySelector('[data-wizard-progress]');
                this.progressBadge = this.progressRoot
                    ? this.progressRoot.querySelector('[data-wizard-progress-badge]')
                    : null;
                this.progressTitle = this.progressRoot
                    ? this.progressRoot.querySelector('[data-wizard-progress-title]')
                    : null;
                this.progressChecklist = this.progressRoot
                    ? this.progressRoot.querySelector('[data-wizard-progress-checklist]')
                    : null;
                this.progressWorkspace = this.progressRoot
                    ? this.progressRoot.querySelector('[data-wizard-progress-workspace]')
                    : null;
                this.progressFieldsEl = this.progressRoot
                    ? this.progressRoot.querySelector('[data-wizard-progress-fields]')
                    : null;
                this.identityStashEl = document.getElementById('em-wp-template-identity-stash');
                this.identityRowEl = document.getElementById('em-wp-template-wizard-identity');
            } else {
                this.root = document.querySelector('[data-wizard-guide]');
                this.stepEl = this.root ? this.root.querySelector('[data-wizard-guide-step]') : null;
                this.textEl = this.root ? this.root.querySelector('[data-wizard-guide-text]') : null;
            }

            this.identityTargets = document.querySelectorAll('[data-wizard-guide-target]');
            this.applyLabelInputPlaceholder();
            this.bindIdentityHints();
            this.bindProgressValidation();
        },

        applyLabelInputPlaceholder: function () {
            var Draft = EmWpTemplateWizard.Draft;
            var inputs = this.getIdentityInputs();

            if (Draft && Draft.applyLabelInputPlaceholder) {
                Draft.applyLabelInputPlaceholder(inputs.labelInput);
            }
        },

        requiresManualValidation: function (step) {
            return step === 0;
        },

        getStepActions: function (step) {
            var cfg = State.config.onboarding || {};
            var progress = cfg.progress || {};

            return (progress.steps && progress.steps[step]) || [];
        },

        getCurrentStepAction: function (step) {
            var actions = this.getStepActions(step);
            var i;

            if (!this.requiresManualValidation(step)) {
                return actions[0] || null;
            }

            for (i = 0; i < actions.length; i++) {
                if (!this.isActionValidated(step, actions[i].key)) {
                    return actions[i];
                }
            }

            return null;
        },

        getSkeletonPhase: function (step) {
            var currentStep = typeof step === 'number' ? step : this.currentStep;

            if (!this.isProgressMode || currentStep !== 1) {
                return 'full';
            }

            if (!this.isActionValidated(1, 'skeleton-rubriques')) {
                return 'pick';
            }

            if (!this.isActionValidated(1, 'skeleton-positions')) {
                return 'order';
            }

            return 'full';
        },

        getValidatedActionsForStep: function (step) {
            var key = String(step);

            if (!this.validatedActions[key]) {
                this.validatedActions[key] = {};
            }

            return this.validatedActions[key];
        },

        isActionValidated: function (step, actionKey) {
            if (!this.requiresManualValidation(step)) {
                return false;
            }

            return this.getValidatedActionsForStep(step)[actionKey] === true;
        },

        canValidateAction: function (step, actionKey) {
            if (step === 0) {
                var state = this.getIdentityState();

                if (actionKey === 'label') {
                    return state.hasLabel;
                }
                if (actionKey === 'color') {
                    return state.hasColor;
                }
            }

            if (step === 1) {
                var draft = State.getDraft();
                var order = draft && draft.skeleton ? draft.skeleton.order || [] : [];

                if (actionKey === 'skeleton-rubriques') {
                    return order.indexOf('header') !== -1 && !this.isActionValidated(1, 'skeleton-rubriques');
                }

                if (actionKey === 'skeleton-positions') {
                    return this.isActionValidated(1, 'skeleton-rubriques')
                        && order.indexOf('header') !== -1
                        && !this.isActionValidated(1, 'skeleton-positions');
                }
            }

            return false;
        },

        validateAction: function (step, actionKey) {
            if (!this.canValidateAction(step, actionKey)) {
                return;
            }

            this.getValidatedActionsForStep(step)[actionKey] = true;
            State.markDirty();

            var self = this;
            var Navigation = EmWpTemplateWizard.Navigation;
            var inputs = this.getIdentityInputs();

            if (this.isProgressMode && step === 1 && Navigation && Navigation.persistDraftForStep) {
                Navigation.persistDraftForStep(inputs.labelInput, inputs.colorInput, step).then(function (saved) {
                    if (saved === false) {
                        delete self.getValidatedActionsForStep(step)[actionKey];
                        return;
                    }

                    self.showStep(step);
                });
                return;
            }

            this.showStep(step);
        },

        invalidateAction: function (step, actionKey) {
            if (!this.requiresManualValidation(step)) {
                return;
            }

            var actions = this.getValidatedActionsForStep(step);

            if (!actions[actionKey]) {
                return;
            }

            delete actions[actionKey];
            State.markDirty();
            this.showStep(step);
        },

        returnToSkeletonAction: function (actionKey) {
            var actions = this.getValidatedActionsForStep(1);
            var Navigation = EmWpTemplateWizard.Navigation;

            if (actionKey === 'skeleton-rubriques') {
                delete actions['skeleton-rubriques'];
                delete actions['skeleton-positions'];
            } else if (actionKey === 'skeleton-positions') {
                delete actions['skeleton-positions'];
            }

            State.markDirty();

            if (Navigation) {
                Navigation.setStep(1);
            } else {
                this.showStep(1);
            }
        },

        isStepFullyValidated: function (step) {
            if (!this.requiresManualValidation(step)) {
                return false;
            }

            var cfg = State.config.onboarding || {};
            var progress = cfg.progress || {};
            var actionList = (progress.steps && progress.steps[step]) || [];
            var i;

            if (!actionList.length) {
                return false;
            }

            for (i = 0; i < actionList.length; i++) {
                if (!this.isActionValidated(step, actionList[i].key)) {
                    return false;
                }
            }

            return true;
        },

        getValidatedActionsExport: function () {
            return JSON.parse(JSON.stringify(this.validatedActions));
        },

        setValidatedActions: function (data) {
            this.validatedActions = data && typeof data === 'object'
                ? JSON.parse(JSON.stringify(data))
                : {};
        },

        bindProgressValidation: function () {
            if (!this.progressRoot || this.progressRoot.dataset.validationBound === '1') {
                return;
            }

            this.progressRoot.dataset.validationBound = '1';

            var self = this;

            this.progressRoot.addEventListener('click', function (event) {
                var button = event.target.closest('[data-wizard-action-validate]');

                if (!button || button.disabled) {
                    return;
                }

                event.preventDefault();

                var actionKey = button.getAttribute('data-wizard-action-validate');
                var step = Number(button.getAttribute('data-wizard-action-step'));

                if (actionKey === '' || isNaN(step)) {
                    return;
                }

                self.validateAction(step, actionKey);
            });

            document.addEventListener('click', function (event) {
                var commitBtn = event.target.closest('[data-wizard-step-commit]');

                if (!commitBtn || commitBtn.disabled) {
                    return;
                }

                var current = self.getCurrentStepAction(self.currentStep);

                if (!current) {
                    return;
                }

                event.preventDefault();
                self.validateAction(self.currentStep, current.key);
            });
        },

        getIdentityInputs: function () {
            return {
                labelInput: document.getElementById('em-wp-template-new-label'),
                colorInput: document.getElementById('em-wp-template-new-color'),
            };
        },

        getColorValue: function () {
            var input = this.getIdentityInputs().colorInput;

            if (!input) {
                return '';
            }

            var value = String(input.value || '').trim();

            if (value !== '') {
                return value;
            }

            if (window.jQuery && window.emWpAdminColorFieldApi && window.emWpAdminColorFieldApi.isReady(input)) {
                try {
                    value = String(window.jQuery(input).wpColorPicker('color') || '').trim();
                } catch (err) {
                    value = '';
                }
            }

            return value;
        },

        getIdentityState: function () {
            var inputs = this.getIdentityInputs();
            var label = inputs.labelInput ? String(inputs.labelInput.value || '').trim() : '';
            var color = this.getColorValue();

            return {
                label: label,
                color: color,
                hasLabel: label !== '',
                hasColor: color !== '',
            };
        },

        resolveIdentityGuide: function () {
            var onboarding = State.config.onboarding || {};
            var identityGuides = onboarding.identityGuides || {};
            var defaultGuide = (onboarding.guides && onboarding.guides[0]) || '';
            var state = this.getIdentityState();

            if (this.isStepFullyValidated(0)) {
                return {
                    text: identityGuides.bothFilled || defaultGuide,
                    focus: 'continue',
                };
            }

            if (!state.hasLabel) {
                return {
                    text: identityGuides.empty || defaultGuide,
                    focus: 'label',
                };
            }

            if (!this.isActionValidated(0, 'label')) {
                return {
                    text: identityGuides.nameFilled || defaultGuide,
                    focus: 'label',
                };
            }

            if (!state.hasColor) {
                return {
                    text: identityGuides.nameFilled || defaultGuide,
                    focus: 'color',
                };
            }

            if (!this.isActionValidated(0, 'color')) {
                return {
                    text: identityGuides.colorFilled || defaultGuide,
                    focus: 'color',
                };
            }

            return {
                text: identityGuides.bothFilled || defaultGuide,
                focus: 'continue',
            };
        },

        syncIdentityFieldStates: function () {
            var state = this.getIdentityState();
            var labelWrap = document.querySelector('[data-wizard-guide-target="label"]');
            var colorWrap = document.querySelector('[data-wizard-guide-target="color"]');

            if (labelWrap) {
                labelWrap.classList.toggle(this.doneClass, this.isActionValidated(0, 'label'));
            }
            if (colorWrap) {
                colorWrap.classList.toggle(this.doneClass, this.isActionValidated(0, 'color'));
            }
        },

        bindIdentityHints: function () {
            var inputs = this.getIdentityInputs();
            var self = this;

            if (inputs.labelInput) {
                inputs.labelInput.addEventListener('input', function () {
                    if (EmWpTemplateWizard.Draft && EmWpTemplateWizard.Draft.syncDraftContextName) {
                        EmWpTemplateWizard.Draft.syncDraftContextName(inputs.labelInput.value);
                    }
                    self.invalidateAction(0, 'label');
                    self.refreshIdentityGuide();
                });
            }

            if (inputs.colorInput) {
                inputs.colorInput.addEventListener('input', function () {
                    self.invalidateAction(0, 'color');
                    self.refreshIdentityGuide();
                });
                inputs.colorInput.addEventListener('change', function () {
                    self.invalidateAction(0, 'color');
                    self.refreshIdentityGuide();
                });
            }

            if (window.jQuery && inputs.colorInput) {
                window.jQuery(inputs.colorInput).on('colorchange', function () {
                    self.invalidateAction(0, 'color');
                    self.refreshIdentityGuide();
                });
            }

            document.addEventListener('emWpAdminColorFieldChanged', function () {
                if (inputs.colorInput && !String(inputs.colorInput.value || '').trim()) {
                    var picked = self.getColorValue();
                    if (picked) {
                        inputs.colorInput.value = picked;
                    }
                }
                self.invalidateAction(0, 'color');
                self.refreshIdentityGuide();
            });
        },

        refreshIdentityGuide: function () {
            if (this.currentStep !== 0) {
                return;
            }
            this.showStep(0);
        },

        refreshStepGuide: function (step) {
            if (this.currentStep !== step) {
                return;
            }
            this.showStep(step);
        },

        renderGuideText: function (step, label, text, identityFocus) {
            var labels = (State.config.onboarding && State.config.onboarding.stepLabels) || [];
            var i18n = State.config.i18n || {};
            var total = labels.length || 3;

            if (this.isProgressMode) {
                if (this.progressBadge) {
                    this.progressBadge.textContent = 'ÉTAPE '
                        + String(step + 1) + '/' + String(total)
                        + ' - '
                        + String(label || '').toUpperCase();
                }

                if (step === 1 && !this.getStepActions(1).length) {
                    if (this.progressTitle) {
                        this.progressTitle.textContent = text || '';
                    }
                    if (this.progressChecklist) {
                        this.progressChecklist.hidden = true;
                        this.progressChecklist.textContent = '';
                    }
                    return;
                }

                this.renderProgressChecklist(step, label, total, identityFocus);
                return;
            }

            var stepLine = i18n.guideStep || 'Étape %1$s sur %2$s — %3$s';

            if (this.stepEl) {
                this.stepEl.textContent = stepLine
                    .replace('%1$s', String(step + 1))
                    .replace('%2$s', String(total))
                    .replace('%3$s', label);
            }

            if (this.textEl) {
                this.textEl.textContent = text;
            }

            if (this.root) {
                this.root.hidden = text === '' && label === '';
            }
        },

        escapeHtml: function (value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },

        resolveProgressActionStatus: function (step, action) {
            var progress = (State.config.onboarding && State.config.onboarding.progress) || {};
            var draft = State.getDraft();

            if (step === 0) {
                var state = this.getIdentityState();
                var pendingValidate = progress.statusPendingValidate || 'à valider';

                if (action.key === 'label') {
                    return {
                        done: this.isActionValidated(0, 'label'),
                        pending: !state.hasLabel
                            ? (progress.statusPendingInput || progress.statusPending || 'à saisir')
                            : pendingValidate,
                        canValidate: state.hasLabel && !this.isActionValidated(0, 'label'),
                    };
                }
                if (action.key === 'color') {
                    return {
                        done: this.isActionValidated(0, 'color'),
                        pending: !state.hasColor
                            ? (progress.statusPending || 'à choisir')
                            : pendingValidate,
                        canValidate: state.hasColor && !this.isActionValidated(0, 'color'),
                    };
                }
            }

            if (step === 1) {
                var pendingValidate = progress.statusPendingValidate || 'à valider';
                var draftOrder = draft && draft.skeleton ? draft.skeleton.order || [] : [];
                var hasHeader = draftOrder.indexOf('header') !== -1;

                if (action.key === 'skeleton-rubriques') {
                    return {
                        done: this.isActionValidated(1, 'skeleton-rubriques'),
                        pending: !hasHeader
                            ? (progress.statusPendingVerify || 'à vérifier')
                            : pendingValidate,
                        canValidate: this.canValidateAction(1, 'skeleton-rubriques'),
                    };
                }

                if (action.key === 'skeleton-positions') {
                    var rubriquesDone = this.isActionValidated(1, 'skeleton-rubriques');

                    return {
                        done: this.isActionValidated(1, 'skeleton-positions'),
                        pending: !rubriquesDone
                            ? (progress.statusPending || 'en attente')
                            : pendingValidate,
                        canValidate: this.canValidateAction(1, 'skeleton-positions'),
                    };
                }
            }

            if (step === 2) {
                if (action.key === 'wireframe') {
                    return {
                        done: false,
                        pending: progress.statusPendingVerify || 'à vérifier',
                    };
                }
                if (action.key === 'submit') {
                    return {
                        done: false,
                        pending: progress.statusPending || 'à faire',
                    };
                }
            }

            return {
                done: false,
                pending: progress.statusPending || 'à faire',
            };
        },

        getProgressActionSummary: function (action) {
            return String((action && action.summary) || (action && action.label) || '');
        },

        formatProgressActionContent: function (step, action, statusInfo) {
            var progress = (State.config.onboarding && State.config.onboarding.progress) || {};
            var state = this.getIdentityState();
            var summary = this.getProgressActionSummary(action);

            if (step === 0 && action.key === 'label') {
                if (!state.hasLabel) {
                    return '<span class="em-wp-template-wizard-progress__action-text">'
                        + this.escapeHtml(summary)
                        + '</span>';
                }

                return '<span class="em-wp-template-wizard-progress__action-text">Nom du template : '
                    + '<strong>' + this.escapeHtml(state.label) + '</strong></span>';
            }

            if (step === 0 && action.key === 'color') {
                if (!state.hasColor) {
                    return '<span class="em-wp-template-wizard-progress__action-text">'
                        + this.escapeHtml(summary)
                        + '</span>';
                }

                return '<span class="em-wp-template-wizard-progress__action-text">Couleur d\'identification : '
                    + '<span class="em-wp-template-wizard-progress__checklist-swatch" style="background-color:'
                    + this.escapeHtml(state.color)
                    + ';" aria-hidden="true"></span></span>';
            }

            if (step === 1 && action.summary) {
                return '<span class="em-wp-template-wizard-progress__action-text">'
                    + this.escapeHtml(action.summary)
                    + '</span>';
            }

            var status = statusInfo.done ? (progress.statusDone || 'OK') : statusInfo.pending;

            return '<strong>' + this.escapeHtml(status) + '</strong>';
        },

        formatProgressStepCompleteLead: function (step, currentStepLabel) {
            var cfg = State.config.onboarding || {};
            var progress = cfg.progress || {};
            var labels = cfg.stepLabels || [];
            var stepLeads = progress.actionsLeadByStep && progress.actionsLeadByStep[step];
            var currentName = String(currentStepLabel || labels[step] || '');
            var nextName = String(labels[step + 1] || '');
            var tpl;

            if (stepLeads && stepLeads.lead) {
                return String(stepLeads.lead);
            }

            if (nextName === '') {
                tpl = (stepLeads && stepLeads.completeLast)
                    || progress.actionsStepCompleteLast
                    || 'Tu as terminé l\'étape %1$s.';
                return String(tpl).replace('%1$s', currentName);
            }

            tpl = (stepLeads && stepLeads.complete)
                || progress.actionsStepComplete
                || 'Tu as terminé l\'étape %1$s. Tu peux passer à l\'étape suivante : %2$s';

            return String(tpl)
                .replace('%1$s', currentName)
                .replace('%2$s', nextName);
        },

        formatProgressActionsLead: function (step, actions, totalSteps, currentStepLabel) {
            var cfg = State.config.onboarding || {};
            var progress = cfg.progress || {};
            var stepLeads = progress.actionsLeadByStep && progress.actionsLeadByStep[step];
            var remainingCount = 0;
            var validatedCount = 0;
            var i;
            var tpl;
            var stepNum = String(step + 1);
            var stepsTotal = String(totalSteps || 3);

            for (i = 0; i < actions.length; i += 1) {
                if (this.isActionValidated(step, actions[i].key)) {
                    validatedCount += 1;
                } else {
                    remainingCount += 1;
                }
            }

            if (remainingCount === 0 && validatedCount > 0) {
                return this.formatProgressStepCompleteLead(step, currentStepLabel);
            }

            if (stepLeads) {
                if (validatedCount === 0) {
                    tpl = actions.length === 1 ? stepLeads.initialOne : stepLeads.initialMany;
                    return String(tpl || '').replace('%s', String(actions.length));
                }

                tpl = remainingCount === 1 ? stepLeads.remainingOne : stepLeads.remainingMany;
                return String(tpl || '').replace('%s', String(remainingCount));
            }

            if (validatedCount === 0) {
                tpl = actions.length === 1
                    ? (progress.actionsLeadInitialOne || 'Pour l\'étape %1$s/%2$s, tu as %3$s action')
                    : (progress.actionsLeadInitialMany || 'Pour l\'étape %1$s/%2$s, tu as %3$s actions');
                return String(tpl)
                    .replace('%1$s', stepNum)
                    .replace('%2$s', stepsTotal)
                    .replace('%3$s', String(actions.length));
            }

            tpl = remainingCount === 1
                ? (progress.actionsLeadRemainingOne || 'Pour l\'étape %1$s/%2$s, il te reste %3$s action')
                : (progress.actionsLeadRemainingMany || 'Pour l\'étape %1$s/%2$s, il te reste %3$s actions');
            return String(tpl)
                .replace('%1$s', stepNum)
                .replace('%2$s', stepsTotal)
                .replace('%3$s', String(remainingCount));
        },

        renderProgressTitleLine: function (step, label, totalSteps, actions) {
            if (!this.progressTitle) {
                return;
            }

            if (!actions || !actions.length) {
                this.progressTitle.textContent = '';
                return;
            }

            var lead = this.formatProgressActionsLead(step, actions, totalSteps, label);
            var stepComplete = this.isStepFullyValidated(step);

            this.progressTitle.innerHTML = '<span class="em-wp-template-wizard-progress__title-lead">'
                + this.escapeHtml(lead)
                + (stepComplete ? '' : ' :')
                + '</span>';
        },

        formatProgressActionCode: function (step, action, progressCfg) {
            var prefix = (progressCfg && progressCfg.actionCodePrefix) || 'Action';

            return prefix + ' ' + String(step + 1) + action.id;
        },

        getActionProgressIndex: function (step) {
            var actions = this.getStepActions(step);
            var current = this.getCurrentStepAction(step);
            var total = actions.length;
            var currentIndex = 0;
            var i;

            if (!total) {
                return null;
            }

            if (current) {
                for (i = 0; i < actions.length; i += 1) {
                    if (actions[i].key === current.key) {
                        currentIndex = i + 1;
                        break;
                    }
                }
            }

            if (!currentIndex) {
                return null;
            }

            return {
                current: currentIndex,
                total: total,
            };
        },

        clearProgressActionCounter: function () {
            document.querySelectorAll('[data-wizard-progress-action-counter]').forEach(function (node) {
                node.remove();
            });
        },

        syncProgressActionCounter: function (step) {
            this.clearProgressActionCounter();

            if (!this.isProgressMode || step !== 0) {
                return;
            }

            var progress = this.getActionProgressIndex(step);
            var currentAction = this.getCurrentStepAction(step);
            var activeWrap;
            var cfg;
            var progressCfg;
            var tpl;
            var counter;

            if (!progress || !currentAction) {
                return;
            }

            activeWrap = document.querySelector('[data-wizard-guide-target="' + currentAction.key + '"]');

            if (!activeWrap || activeWrap.hidden) {
                return;
            }

            cfg = State.config.onboarding || {};
            progressCfg = cfg.progress || {};
            tpl = progressCfg.actionsProgressCounter || 'Action %1$s/%2$s';
            counter = document.createElement('span');
            counter.className = 'em-wp-template-wizard-progress__action-counter';
            counter.setAttribute('data-wizard-progress-action-counter', '');
            counter.textContent = tpl
                .replace('%1$s', String(progress.current))
                .replace('%2$s', String(progress.total));

            activeWrap.appendChild(counter);
        },

        getProgressActionPreviewText: function (step, action) {
            return this.getProgressActionSummary(action);
        },

        renderProgressChecklist: function (step, label, total, identityFocus) {
            var cfg = State.config.onboarding || {};
            var progress = cfg.progress || {};
            var actions = this.getStepActions(step);
            var currentAction = this.getCurrentStepAction(step);

            this.renderProgressTitleLine(step, label, total, actions);

            if (!this.progressChecklist) {
                return;
            }

            if (!actions.length) {
                this.progressChecklist.hidden = true;
                this.progressChecklist.textContent = '';
                return;
            }

            var parts = [];
            var i18n = State.config.i18n || {};
            var validateLabel = i18n.validateAction || 'Valider';

            actions.forEach(function (action) {
                var statusInfo = this.resolveProgressActionStatus(step, action);
                var done = this.requiresManualValidation(step)
                    ? this.isActionValidated(step, action.key)
                    : statusInfo.done;
                var isCurrent = !!(currentAction && currentAction.key === action.key);
                var isUpcoming = !done && !isCurrent;
                var mod = isUpcoming ? 'is-upcoming' : (done ? 'is-done' : 'is-pending');
                var showValidate = this.requiresManualValidation(step);
                var canValidate = !!statusInfo.canValidate;
                var useCustomContent = (step === 0 && (action.key === 'label' || action.key === 'color'))
                    || (step === 1 && !!action.summary);

                if (isCurrent) {
                    mod += ' is-current';
                }

                parts.push(
                    '<span class="em-wp-template-wizard-progress__checklist-item '
                    + mod
                    + '" data-progress-action="'
                    + this.escapeHtml(action.key)
                    + '">'
                    + '<span class="em-wp-template-wizard-progress__checklist-code">'
                    + this.escapeHtml(this.formatProgressActionCode(step, action, progress))
                    + '</span>'
                    + ' — '
                );

                if (isUpcoming) {
                    parts.push(
                        '<span class="em-wp-template-wizard-progress__action-text">'
                        + this.escapeHtml(this.getProgressActionPreviewText(step, action))
                        + '</span>'
                    );
                } else if (useCustomContent) {
                    parts.push(this.formatProgressActionContent(step, action, statusInfo));
                } else {
                    parts.push(
                        this.escapeHtml(action.label)
                        + ' : '
                        + this.formatProgressActionContent(step, action, statusInfo)
                    );
                }

                if (showValidate) {
                    if (done) {
                        parts.push(
                            '<span class="em-wp-template-wizard-progress__action-validate is-validated" aria-hidden="true">'
                            + '<i class="fa-solid fa-check" aria-hidden="true"></i>'
                            + '</span>'
                        );
                    } else if (isUpcoming) {
                        parts.push(
                            '<span class="em-wp-template-wizard-progress__action-validate is-upcoming" aria-hidden="true">'
                            + '<i class="fa-solid fa-check" aria-hidden="true"></i>'
                            + '</span>'
                        );
                    } else {
                        parts.push(
                            '<button type="button" class="em-wp-template-wizard-progress__action-validate'
                            + (canValidate ? ' is-ready' : '')
                            + '" data-wizard-action-validate="'
                            + this.escapeHtml(action.key)
                            + '" data-wizard-action-step="'
                            + String(step)
                            + '" aria-label="'
                            + this.escapeHtml(validateLabel + ' ' + action.label)
                            + '"'
                            + (canValidate ? '' : ' disabled')
                            + '>'
                            + '<i class="fa-solid fa-check" aria-hidden="true"></i>'
                            + '</button>'
                        );
                    }
                }

                parts.push('</span>');
            }.bind(this));

            this.progressChecklist.innerHTML = parts.join(' ');
            this.progressChecklist.hidden = false;
        },

        showStep: function (step) {
            var cfg = State.config.onboarding || {};
            var guides = cfg.guides || {};
            var labels = cfg.stepLabels || [];
            var label = labels[step] || '';
            var text = guides[step] || '';
            var identityFocus = null;

            this.currentStep = step;

            if (step === 0) {
                var identity = this.resolveIdentityGuide();
                text = identity.text;
                identityFocus = identity.focus;
                this.syncIdentityFieldStates();
            }

            if (step === 1 && EmWpTemplateWizard.Wireframe && EmWpTemplateWizard.Wireframe.render) {
                EmWpTemplateWizard.Wireframe.render();

                if (EmWpTemplateWizard.Navigation && EmWpTemplateWizard.Navigation.syncRecap) {
                    var recapInputs = this.getIdentityInputs();
                    EmWpTemplateWizard.Navigation.syncRecap(recapInputs.labelInput, recapInputs.colorInput);
                }
            }

            this.renderGuideText(step, label, text, identityFocus);

            var editingDraftName = EmWpTemplateWizard.Draft
                && EmWpTemplateWizard.Draft.isEditingDraftNameActive
                && EmWpTemplateWizard.Draft.isEditingDraftNameActive();

            if (!editingDraftName) {
                this.syncProgressLayout();
                this.focusForStep(step, identityFocus);
            }

            this.syncProgressBannerColor(step);

            if (this.isProgressMode) {
                this.syncProgressStepComplete(step);

                if (step === 0) {
                    var identityState = this.getIdentityState();
                    this.syncProgressColorPreview(identityState);
                    this.syncContinueButton(step, identityState);
                }

                if (step === 1) {
                    this.syncResetButton(step);
                }

                this.syncResetButton(step);
            }

            this.syncWizardFooter(step);
            this.syncWireframeActions(step);
            this.syncWizardPlanWorkspace(step);

            if (this.isProgressMode && EmWpTemplateWizard.Draft && EmWpTemplateWizard.Draft.syncDraftContextStep) {
                EmWpTemplateWizard.Draft.syncDraftContextStep(step);
            }
        },

        syncWizardFooter: function (step) {
            var wizard = document.getElementById('em-wp-template-create-wizard');
            var footer = wizard ? wizard.querySelector('.em-wp-template-wizard__actions') : null;

            if (!footer) {
                return;
            }

            if (this.isProgressMode) {
                footer.hidden = true;
                return;
            }

            footer.hidden = false;
        },

        syncWizardPlanWorkspace: function (step) {
            var wizard = document.getElementById('em-wp-template-create-wizard');
            var body = wizard ? wizard.querySelector('.em-wp-template-wizard__body') : null;
            var planSection = document.querySelector('.em-wp-templates-create-page--edit .em-wp-catalog-sommaire__section');

            if (this.isProgressMode && planSection) {
                planSection.hidden = step !== 1;
            }

            if (!wizard || !body) {
                return;
            }

            if (!this.isProgressMode) {
                body.hidden = false;
                wizard.hidden = false;
                if (planSection) {
                    planSection.hidden = false;
                }
                return;
            }

            body.hidden = step !== 1;
            wizard.hidden = step !== 1;
        },

        syncWireframeActions: function (step) {
            var wizard = document.getElementById('em-wp-template-create-wizard');
            var actions = wizard ? wizard.querySelector('[data-wizard-wireframe-actions]') : null;
            var submitBtn = actions ? actions.querySelector('[data-wizard-submit]') : null;
            var state = this.getIdentityState();

            if (!actions) {
                return;
            }

            actions.hidden = step !== 1;

            if (step === 1) {
                this.applyTemplateColorToButton(submitBtn, state);
            } else {
                this.applyTemplateColorToButton(submitBtn, null);
            }
        },

        usesProgressIdentityPanel: function () {
            return this.isProgressMode;
        },

        getIdentityActionsWrap: function () {
            return this.identityRowEl
                ? this.identityRowEl.querySelector('.em-wp-templates-admin__create-actions')
                : null;
        },

        getContinueButton: function () {
            return document.getElementById('em-wp-template-wizard-open');
        },

        isProgressContinueButton: function (button) {
            return !!(button && button.hasAttribute('data-wizard-progress-continue'));
        },

        shadeColor: function (hex, amount) {
            var normalized = String(hex || '').trim().replace('#', '');

            if (normalized.length === 3) {
                normalized = normalized.split('').map(function (char) {
                    return char + char;
                }).join('');
            }

            if (normalized.length !== 6) {
                return '#751820';
            }

            var num = parseInt(normalized, 16);
            var r = (num >> 16) & 0xff;
            var g = (num >> 8) & 0xff;
            var b = num & 0xff;
            var factor = 1 + amount;

            if (amount < 0) {
                r = Math.round(r * factor);
                g = Math.round(g * factor);
                b = Math.round(b * factor);
            } else {
                r = Math.round(r + ((255 - r) * amount));
                g = Math.round(g + ((255 - g) * amount));
                b = Math.round(b + ((255 - b) * amount));
            }

            return '#'
                + [r, g, b].map(function (channel) {
                    var part = Math.max(0, Math.min(255, channel)).toString(16);
                    return part.length === 1 ? '0' + part : part;
                }).join('');
        },

        syncProgressBannerColor: function (step) {
            if (!this.isProgressMode || !this.progressRoot) {
                return;
            }

            var inner = this.progressRoot.querySelector('.em-wp-template-wizard-progress__inner');

            if (!inner) {
                return;
            }

            var state = this.getIdentityState();
            var stepComplete = this.requiresManualValidation(step) && this.isStepFullyValidated(step);
            var useTemplateColor = state.hasColor && (step >= 1 || stepComplete);

            if (useTemplateColor) {
                inner.style.setProperty('--em-wp-wizard-banner-from', this.shadeColor(state.color, -0.28));
                inner.style.setProperty('--em-wp-wizard-banner-to', state.color);
                inner.classList.add('is-template-colored');
                return;
            }

            inner.style.removeProperty('--em-wp-wizard-banner-from');
            inner.style.removeProperty('--em-wp-wizard-banner-to');
            inner.classList.remove('is-template-colored');
        },

        syncProgressColorPreview: function (state) {
            var preview = document.querySelector('[data-wizard-progress-color-preview]');
            var swatch = document.querySelector('[data-wizard-progress-color-swatch]');
            var show = this.isProgressMode
                && state.hasColor
                && this.isActionValidated(0, 'color');

            if (!preview) {
                return;
            }

            if (show) {
                preview.hidden = false;
                preview.setAttribute('aria-hidden', 'false');

                if (swatch) {
                    swatch.style.backgroundColor = state.color;
                }
                return;
            }

            preview.hidden = true;
            preview.setAttribute('aria-hidden', 'true');
        },

        syncProgressStepComplete: function (step) {
            var icon = document.querySelector('[data-wizard-progress-step-complete]');
            var show = this.isProgressMode && this.isStepFullyValidated(step);

            if (!icon) {
                return;
            }

            icon.hidden = !show;
            icon.setAttribute('aria-hidden', show ? 'false' : 'true');
        },

        syncContinueButton: function (step, state) {
            var button = this.getContinueButton();
            var labelEl = button ? button.querySelector('.em-wp-hub__action-label') : null;
            var i18n = State.config.i18n || {};
            var labels = (State.config.onboarding && State.config.onboarding.stepLabels) || [];
            var total = labels.length || 3;
            var tpl = i18n.progressNextStep || 'Étape %1$s/%2$s';

            if (!button || !this.isProgressContinueButton(button)) {
                return;
            }

            var ready = step === 0 && this.isStepFullyValidated(0);
            button.hidden = !ready;

            if (!ready) {
                button.classList.remove(this.focusClass);
                button.style.removeProperty('--em-wp-wizard-continue-from');
                button.style.removeProperty('--em-wp-wizard-continue-to');
                button.style.removeProperty('border-color');
            } else if (state.hasColor) {
                button.style.setProperty('--em-wp-wizard-continue-from', this.shadeColor(state.color, -0.28));
                button.style.setProperty('--em-wp-wizard-continue-to', state.color);
                button.style.borderColor = this.shadeColor(state.color, -0.35);
            }

            if (labelEl) {
                labelEl.textContent = tpl
                    .replace('%1$s', String(step + 2))
                    .replace('%2$s', String(total));
            }

            this.syncResetButton(step);
        },

        syncResetButton: function (step) {
            var resetBtn = document.querySelector('[data-wizard-reset-workspace]');
            var continueBtn = this.getContinueButton();
            var advanceBtn = document.querySelector('[data-wizard-progress-advance]');
            var currentStep = typeof step === 'number' ? step : this.currentStep;
            var hideReset = false;

            if (!resetBtn || !this.isProgressMode) {
                return;
            }

            if (currentStep > 0) {
                hideReset = true;
            } else if (currentStep === 0 && this.isStepFullyValidated(0)) {
                hideReset = true;
            } else if (continueBtn && !continueBtn.hidden) {
                hideReset = true;
            } else if (advanceBtn && !advanceBtn.hidden) {
                hideReset = true;
            }

            resetBtn.hidden = hideReset;
        },

        applyTemplateColorToButton: function (button, state) {
            if (!button) {
                return;
            }

            if (state && state.hasColor) {
                button.style.setProperty('--em-wp-wizard-continue-from', this.shadeColor(state.color, -0.28));
                button.style.setProperty('--em-wp-wizard-continue-to', state.color);
                button.style.borderColor = this.shadeColor(state.color, -0.35);
                return;
            }

            button.style.removeProperty('--em-wp-wizard-continue-from');
            button.style.removeProperty('--em-wp-wizard-continue-to');
            button.style.removeProperty('border-color');
        },

        syncProgressAdvanceButton: function (step) {
            var button = document.querySelector('[data-wizard-progress-advance]');
            var labelEl = button ? button.querySelector('.em-wp-hub__action-label') : null;
            var i18n = State.config.i18n || {};
            var labels = (State.config.onboarding && State.config.onboarding.stepLabels) || [];
            var total = labels.length || 3;
            var tpl = i18n.progressNextStep || 'Étape %1$s/%2$s';
            var state = this.getIdentityState();

            if (!button) {
                return;
            }

            var ready = this.requiresManualValidation(step) && this.isStepFullyValidated(step);
            button.hidden = !ready;

            if (!ready) {
                this.applyTemplateColorToButton(button, null);
                return;
            }

            this.applyTemplateColorToButton(button, state);

            if (labelEl) {
                labelEl.textContent = tpl
                    .replace('%1$s', String(step + 2))
                    .replace('%2$s', String(total));
            }

            this.syncResetButton(step);
        },

        moveNodeTo: function (node, parent) {
            if (!node || !parent) {
                return;
            }
            parent.appendChild(node);
            node.hidden = false;
        },

        moveNodeToStash: function (node) {
            if (!node || !this.identityStashEl) {
                return;
            }
            this.identityStashEl.appendChild(node);
            node.hidden = true;
        },

        restoreIdentityLayout: function () {
            var labelWrap = document.querySelector('[data-wizard-guide-target="label"]');
            var colorWrap = document.querySelector('[data-wizard-guide-target="color"]');
            var actionsWrap = this.getIdentityActionsWrap();
            var continueBtn = this.getContinueButton();

            if (labelWrap && this.identityRowEl && labelWrap.parentElement !== this.identityRowEl) {
                this.identityRowEl.insertBefore(labelWrap, actionsWrap || null);
                labelWrap.hidden = false;
            }
            if (colorWrap && this.identityRowEl && colorWrap.parentElement !== this.identityRowEl) {
                this.identityRowEl.insertBefore(colorWrap, actionsWrap || null);
                colorWrap.hidden = false;
            }
            if (continueBtn && actionsWrap && !this.isProgressContinueButton(continueBtn)) {
                continueBtn.classList.remove('em-wp-template-wizard-progress__continue');
                actionsWrap.appendChild(continueBtn);
            }
            if (actionsWrap && this.identityRowEl && actionsWrap.parentElement !== this.identityRowEl) {
                this.identityRowEl.appendChild(actionsWrap);
                actionsWrap.hidden = false;
            }
        },

        syncProgressLayout: function () {
            if (!this.isProgressMode) {
                return;
            }

            var labelWrap = document.querySelector('[data-wizard-guide-target="label"]');
            var colorWrap = document.querySelector('[data-wizard-guide-target="color"]');
            var actionsWrap = this.getIdentityActionsWrap();
            var continueBtn = this.getContinueButton();

            if (this.currentStep !== 0) {
                this.restoreIdentityLayout();

                if (this.currentStep === 1) {
                    var advanceReady = this.requiresManualValidation(1) && this.isStepFullyValidated(1);

                    if (this.progressWorkspace) {
                        this.progressWorkspace.hidden = !advanceReady;
                    }
                    if (this.progressFieldsEl) {
                        this.progressFieldsEl.hidden = false;
                    }
                    if (continueBtn) {
                        continueBtn.hidden = true;
                    }
                    var preview = document.querySelector('[data-wizard-progress-color-preview]');
                    if (preview) {
                        preview.hidden = true;
                    }
                    this.syncProgressAdvanceButton(this.currentStep);
                    this.syncProgressStepComplete(this.currentStep);
                    return;
                }

                if (this.currentStep === 2) {
                    if (this.progressWorkspace) {
                        this.progressWorkspace.hidden = true;
                    }
                    if (this.identityRowEl) {
                        this.identityRowEl.hidden = true;
                    }
                    return;
                }

                if (this.progressWorkspace) {
                    this.progressWorkspace.hidden = true;
                }
                if (this.identityRowEl) {
                    this.identityRowEl.hidden = true;
                }
                return;
            }

            var state = this.getIdentityState();
            var currentAction = this.getCurrentStepAction(0);
            var currentKey = currentAction ? currentAction.key : null;
            var stepComplete = this.isStepFullyValidated(0);

            if (stepComplete) {
                this.moveNodeToStash(labelWrap);
                this.moveNodeToStash(colorWrap);
            } else if (currentKey === 'label') {
                this.moveNodeTo(labelWrap, this.progressFieldsEl);
                this.moveNodeToStash(colorWrap);
            } else if (currentKey === 'color') {
                this.moveNodeToStash(labelWrap);
                this.moveNodeTo(colorWrap, this.progressFieldsEl);
            } else {
                this.moveNodeToStash(labelWrap);
                this.moveNodeToStash(colorWrap);
            }

            if (continueBtn && this.progressFieldsEl) {
                continueBtn.classList.add('em-wp-template-wizard-progress__continue');

                if (!this.isProgressContinueButton(continueBtn)) {
                    this.progressFieldsEl.appendChild(continueBtn);
                }
            }

            this.syncProgressColorPreview(state);
            this.syncContinueButton(this.currentStep, state);
            this.syncProgressStepComplete(this.currentStep);

            if (actionsWrap && this.identityRowEl && actionsWrap.parentElement !== this.identityRowEl) {
                this.identityRowEl.appendChild(actionsWrap);
            }

            if (this.progressFieldsEl) {
                this.progressFieldsEl.hidden = false;
            }
            if (this.progressWorkspace) {
                this.progressWorkspace.hidden = false;
            }
            if (this.identityRowEl) {
                this.identityRowEl.hidden = true;
            }

            if (window.emWpAdminColorFieldApi && typeof window.emWpAdminColorFieldApi.initAll === 'function') {
                if (currentKey === 'color') {
                    window.emWpAdminColorFieldApi.initAll();
                }
            }
        },

        focusForStep: function (step, identityFocus) {
            var cfg = State.config.onboarding || {};
            var selector = (cfg.focus && cfg.focus[step]) || '';
            var self = this;

            if (EmWpTemplateWizard.Draft
                && EmWpTemplateWizard.Draft.isEditingDraftNameActive
                && EmWpTemplateWizard.Draft.isEditingDraftNameActive()) {
                return;
            }

            window.setTimeout(function () {
                if (EmWpTemplateWizard.Draft
                    && EmWpTemplateWizard.Draft.isEditingDraftNameActive
                    && EmWpTemplateWizard.Draft.isEditingDraftNameActive()) {
                    return;
                }

                if (step === 0) {
                    var focus = identityFocus || self.resolveIdentityGuide().focus;
                    if (focus === 'continue') {
                        if (!self.isStepFullyValidated(0)) {
                            return;
                        }
                        self.clearFocus();
                        self.syncIdentityFieldStates();
                        var continueBtn = document.getElementById('em-wp-template-wizard-open');
                        if (continueBtn && !continueBtn.hidden) {
                            if (typeof continueBtn.focus === 'function') {
                                continueBtn.focus({ preventScroll: true });
                            }
                        }
                        return;
                    }
                    self.focusIdentityField(focus === 'color' ? 'color' : 'label');
                    return;
                }

                if (!selector) {
                    return;
                }

                var el = document.querySelector(selector);
                if (el) {
                    self.applyFocus(el);
                    if (typeof el.focus === 'function') {
                        el.focus({ preventScroll: true });
                    }
                }
            }, step === 1 ? 120 : 60);
        },

        focusIdentityField: function (which) {
            var target = document.querySelector('[data-wizard-guide-target="' + which + '"]');
            var inputs = this.getIdentityInputs();
            var input = which === 'label' ? inputs.labelInput : inputs.colorInput;

            this.clearFocus();
            this.syncIdentityFieldStates();

            if (target) {
                if (this.isProgressMode) {
                    target.classList.add(this.progressFieldActiveClass);
                } else {
                    target.classList.add(this.focusClass);
                }
            }

            if (input && typeof input.focus === 'function') {
                input.focus({ preventScroll: true });
            }

            this.syncProgressActionCounter(0);

            if (which === 'color') {
                var editButton = target ? target.querySelector('[data-em-wp-color-modal-open]') : null;

                if (editButton && typeof editButton.focus === 'function') {
                    editButton.focus({ preventScroll: true });
                }
            }
        },

        applyFocus: function (el) {
            this.clearFocus();

            if (el.closest('[data-wizard-plan-add]')) {
                return;
            }

            var wrap = el.closest('[data-wizard-guide-target]')
                || el.closest('.em-wp-templates-admin__create-field')
                || el.closest('.em-wp-template-wizard-catalog__field')
                || el;

            if (wrap) {
                wrap.classList.add(this.focusClass);
            }
        },

        clearFocus: function () {
            this.clearProgressActionCounter();
            document.querySelectorAll('.' + this.focusClass).forEach(function (node) {
                node.classList.remove(this.focusClass);
            }.bind(this));
            document.querySelectorAll('.' + this.progressFieldActiveClass).forEach(function (node) {
                node.classList.remove(this.progressFieldActiveClass);
            }.bind(this));
        },

        hideOverview: function () {
            var overview = document.getElementById('em-wp-template-wizard-overview');
            if (overview) {
                overview.hidden = true;
            }
        },

        showOverview: function () {
            var overview = document.getElementById('em-wp-template-wizard-overview');
            if (overview) {
                overview.hidden = false;
            }
        },
    };
})();
