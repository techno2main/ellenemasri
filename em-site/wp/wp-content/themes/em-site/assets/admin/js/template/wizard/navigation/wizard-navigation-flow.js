(function () {
    'use strict';

    window.EmWpTemplateWizard = window.EmWpTemplateWizard || {};

    var State = EmWpTemplateWizard.State;
    var Confirm = EmWpTemplateWizard.Confirm;
    var Guide = EmWpTemplateWizard.Guide;
    var Draft = EmWpTemplateWizard.Draft;
    var Navigation = EmWpTemplateWizard.Navigation;

    if (!Navigation) {
        return;
    }

    Object.assign(Navigation, {
        requestClose: function () {
            var self = this;
            var msg = (State.config.i18n && State.config.i18n.quitDraft) || 'Quitter ?';

            if (!State.dirty) {
                this.close();
                return;
            }

            Confirm.ask(msg, { title: 'Assistant' }).then(function (ok) {
                if (ok) {
                    self.close();
                }
            });
        },

        close: function () {
            if (this.pageMode) {
                var hubUrl = State.config && State.config.createHubUrl;
                if (hubUrl) {
                    window.location.href = hubUrl;
                    return;
                }
            }

            if (this.wizard) {
                this.wizard.hidden = true;
                this.wizard.setAttribute('aria-hidden', 'true');
            }
            document.body.classList.remove('em-wp-template-wizard-open');

            if (this.identityPanel) {
                this.identityPanel.hidden = Guide && Guide.usesProgressIdentityPanel && Guide.usesProgressIdentityPanel();
            }
            if (Guide) {
                Guide.showOverview();
                Guide.showStep(0);
            }

            State.draft = null;
            State.dirty = false;
            this.currentStep = 0;
        },

        setStep: function (step) {
            if (step > 1) {
                step = 1;
            }

            this.currentStep = step;

            if (!this.wizard) {
                if (Guide) {
                    Guide.showStep(step);
                }
                return;
            }

            var indicators = this.wizard.querySelectorAll('[data-wizard-step-indicator]');
            var panels = this.wizard.querySelectorAll('[data-wizard-panel]');
            var nextBtn = this.wizard.querySelector('[data-wizard-next]');
            var prevBtns = this.wizard.querySelectorAll('[data-wizard-prev]');
            var submitBtns = this.wizard.querySelectorAll('[data-wizard-submit]');
            var wireframeActions = this.wizard.querySelector('[data-wizard-wireframe-actions]');
            var planErr = document.getElementById('em-wp-template-wizard-plan-error');
            var catErr = document.getElementById('em-wp-template-wizard-catalog-error');

            indicators.forEach(function (el) {
                var n = Number(el.getAttribute('data-wizard-step-indicator'));
                el.classList.toggle('is-done', n < step);
                el.classList.toggle('is-active', n === step);
            });

            panels.forEach(function (panel) {
                var panelKey = panel.getAttribute('data-wizard-panel');
                var panelStep = Number(panelKey);
                panel.hidden = Number.isNaN(panelStep) || panelStep !== step;
            });

            if (prevBtns.length) {
                prevBtns.forEach(function (prevBtn) {
                    prevBtn.hidden = step <= 0;
                });
            }
            if (nextBtn) {
                nextBtn.hidden = step >= 1;
            }
            if (submitBtns.length) {
                submitBtns.forEach(function (submitBtn) {
                    var inPanelActions = submitBtn.closest('[data-wizard-wireframe-actions]');
                    submitBtn.hidden = inPanelActions ? false : step !== 1;
                });
            }
            if (wireframeActions) {
                wireframeActions.hidden = step !== 1;
            }
            if (planErr) {
                planErr.hidden = true;
            }
            if (catErr) {
                catErr.hidden = true;
            }

            if (step === 1 && this.wireframe && this.wireframe.render) {
                this.wireframe.render();
            }

            if (Guide) {
                Guide.showStep(step);
            }
        },

        goNext: function (labelInput, colorInput) {
            var draft = State.getDraft();
            if (!draft) {
                return;
            }

            draft.label = labelInput ? String(labelInput.value || '').trim() : draft.label;
            draft.color = colorInput ? String(colorInput.value || '').trim() : draft.color;
            this.syncRecap(labelInput, colorInput);

            if (this.currentStep < 1) {
                this.advanceToStep(labelInput, colorInput, this.currentStep + 1);
            }
        },

        goPrev: function () {
            if (this.currentStep > 0) {
                this.setStep(this.currentStep - 1);

                if (this.currentStep === 0 && this.pageMode && this.identityPanel) {
                    this.identityPanel.hidden = false;
                    if (Guide) {
                        Guide.showOverview();
                    }
                }
            }
        },

        submit: function (labelInput, colorInput) {
            var draft = State.getDraft();
            var payloadInput = document.getElementById('em-wp-template-wizard-payload');
            var actionInput = document.getElementById('em-wp-template-wizard-action');
            var self = this;

            if (!draft || !this.form) {
                return;
            }

            draft.label = labelInput ? String(labelInput.value || '').trim() : draft.label;
            draft.color = colorInput ? String(colorInput.value || '').trim() : draft.color;

            var err = State.validateSkeleton() || State.validateIdentity(draft.label, draft.color);
            if (err) {
                Confirm.alert(err, { title: 'Validation' });
                return;
            }

            var confirmMsg = (State.config.i18n && State.config.i18n.createConfirm) || 'Creer %s ?';
            confirmMsg = confirmMsg.replace('%s', draft.label);

            Confirm.ask(confirmMsg, { title: 'Valider le template', confirmLabel: 'Valider' }).then(function (ok) {
                if (!ok) {
                    return;
                }
                if (Draft) {
                    Draft.removeActive(self);
                }
                if (payloadInput) {
                    payloadInput.value = JSON.stringify(State.getPayload());
                }
                if (actionInput) {
                    actionInput.value = 'create_wizard';
                }
                self.form.submit();
            });
        },
    });
})();
