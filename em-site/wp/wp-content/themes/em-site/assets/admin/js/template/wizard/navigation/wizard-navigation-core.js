(function () {
    'use strict';

    window.EmWpTemplateWizard = window.EmWpTemplateWizard || {};

    var State = EmWpTemplateWizard.State;
    var Confirm = EmWpTemplateWizard.Confirm;
    var Guide = EmWpTemplateWizard.Guide;
    var Draft = EmWpTemplateWizard.Draft;

    EmWpTemplateWizard.Navigation = {
        currentStep: 0,
        wizard: null,
        form: null,
        catalog: null,
        wireframe: null,
        pageMode: false,
        identityPanel: null,
        activeDraftId: null,

        init: function () {
            this.wizard = document.getElementById('em-wp-template-create-wizard');
            this.form = document.getElementById('em-wp-template-create-form');
            if (!this.form) {
                return;
            }

            this.pageMode = !this.wizard
                || this.wizard.getAttribute('data-wizard-page-mode') === '1'
                || !!(State.config && State.config.pageMode);

            this.identityPanel = document.getElementById('em-wp-template-wizard-identity');

            if (this.wizard) {
                this.catalog = EmWpTemplateWizard.Catalog;
                this.wireframe = EmWpTemplateWizard.Wireframe;
                this.catalog.bind(this.wizard);
                this.wireframe.bind(this.wizard);
            }

            if (Guide && Guide.init) {
                Guide.init();
            }

            var openBtn = document.getElementById('em-wp-template-wizard-open');
            var labelInput = document.getElementById('em-wp-template-new-label');
            var colorInput = document.getElementById('em-wp-template-new-color');
            var self = this;

            if (openBtn) {
                openBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    self.openFromGate(labelInput, colorInput);
                });
            }

            var advanceBtn = document.querySelector('[data-wizard-progress-advance]');
            if (advanceBtn) {
                advanceBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    if (Guide && Guide.isStepFullyValidated && !Guide.isStepFullyValidated(self.currentStep)) {
                        return;
                    }
                    if (self.currentStep < 1) {
                        self.advanceToStep(labelInput, colorInput, self.currentStep + 1);
                    }
                });
            }

            document.querySelectorAll('[data-wizard-save-draft]').forEach(function (btn) {
                btn.addEventListener('click', function (event) {
                    event.preventDefault();
                    var stayOnPage = btn.getAttribute('data-wizard-save-stay') === '1';
                    self.saveDraft(labelInput, colorInput, stayOnPage);
                });
            });

            if (this.wizard) {
                this.wizard.querySelectorAll('[data-wizard-close]').forEach(function (btn) {
                    btn.addEventListener('click', function (event) {
                        event.preventDefault();
                        self.requestClose();
                    });
                });

                var nextBtn = this.wizard.querySelector('[data-wizard-next]');
                var prevBtns = this.wizard.querySelectorAll('[data-wizard-prev]');
                var submitBtns = this.wizard.querySelectorAll('[data-wizard-submit]');
                var editCat = this.wizard.querySelector('[data-wizard-edit-catalog]');

                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        self.goNext(labelInput, colorInput);
                    });
                }
                prevBtns.forEach(function (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        self.goPrev();
                    });
                });
                submitBtns.forEach(function (submitBtn) {
                    submitBtn.addEventListener('click', function () {
                        self.submit(labelInput, colorInput);
                    });
                });
                if (editCat) {
                    editCat.addEventListener('click', function () {
                        self.setStep(1);
                    });
                }

                if (!this.pageMode) {
                    this.wizard.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            event.preventDefault();
                            self.requestClose();
                        }
                    });
                }
            }

            var page = document.querySelector('[data-wizard-view]');
            var resumeId = page ? page.getAttribute('data-wizard-resume-id') : '';
            var view = page ? page.getAttribute('data-wizard-view') : 'hub';

            if (Draft) {
                Draft.navigation = this;
                Draft.hydrateStore();
                Draft.bindNavTabs();
            }

            if (this.pageMode && Guide && !resumeId) {
                Guide.showStep(0);
            }

            if (resumeId && Draft) {
                var stored = Draft.findById(resumeId);

                if (stored) {
                    Draft.consumeRestoreGrant(resumeId);
                    this.activeDraftId = resumeId;
                    Draft.applyRestore(stored, this);
                } else {
                    Draft.handleRestoreDenied(this);
                }
            } else if (view === 'edit' && Draft) {
                if (Draft.consumeWorkspaceLaunchGrant()) {
                    Draft.syncLaunchContext('');
                } else {
                    Draft.resetLaunchState(this);
                }
             }

            document.querySelectorAll('[data-wizard-reset-workspace]').forEach(function (btn) {
                btn.addEventListener('click', function (event) {
                    event.preventDefault();
                    if (Draft && Draft.requestReset) {
                        Draft.requestReset();
                    }
                });
            });
        },

        saveDraft: function (labelInput, colorInput, stayOnPage) {
            if (!Draft) {
                return Promise.resolve(false);
            }
            return Draft.save(labelInput, colorInput, this.currentStep, this.activeDraftId, null, stayOnPage);
        },

        persistDraftForStep: function (labelInput, colorInput, nextStep) {
            if (!Draft || !Guide || !Guide.isProgressMode) {
                return Promise.resolve(true);
            }

            return Draft.save(labelInput, colorInput, nextStep, this.activeDraftId, null, false, true);
        },

        advanceToStep: function (labelInput, colorInput, nextStep, afterAdvance) {
            var self = this;
            return this.persistDraftForStep(labelInput, colorInput, nextStep).then(function (saved) {
                if (saved === false) {
                    return false;
                }
                self.setStep(nextStep);
                if (typeof afterAdvance === 'function') {
                    afterAdvance();
                }
                return true;
            });
        },

        resetToIdentity: function () {
            var newUrl = State.config && State.config.createNewUrl;
            if (newUrl) {
                window.location.href = newUrl;
                return;
            }

            if (this.wizard) {
                this.wizard.hidden = true;
                this.wizard.setAttribute('aria-hidden', 'true');
            }
            if (this.identityPanel) {
                this.identityPanel.hidden = Guide && Guide.usesProgressIdentityPanel && Guide.usesProgressIdentityPanel();
            }
            if (Guide) {
                Guide.showOverview();
                Guide.showStep(0);
                Guide.clearFocus();
            }

            var labelInput = document.getElementById('em-wp-template-new-label');
            var colorInput = document.getElementById('em-wp-template-new-color');
            if (labelInput) {
                labelInput.value = '';
                if (Draft && Draft.applyLabelInputPlaceholder) {
                    Draft.applyLabelInputPlaceholder(labelInput);
                }
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
            this.currentStep = 0;
            this.activeDraftId = null;
        },

        syncRecap: function (labelInput, colorInput) {
            if (!this.wizard) {
                return;
            }

            var nameEl = this.wizard.querySelector('[data-wizard-recap-name]');
            var swatch = this.wizard.querySelector('[data-wizard-recap-swatch]');
            var label = labelInput ? String(labelInput.value || '').trim() : '';
            var color = colorInput ? String(colorInput.value || '').trim() : '';

            if (!color && Guide && Guide.getColorValue) {
                color = Guide.getColorValue();
            }
            if (!color) {
                color = '#751820';
            }

            if (nameEl) {
                nameEl.textContent = label !== '' ? label.toUpperCase() : '—';
            }
            if (swatch) {
                swatch.style.setProperty('--em-template-swatch', color || '#cccccc');
            }
        },

        openFromGate: function (labelInput, colorInput) {
            var label = labelInput ? String(labelInput.value || '').trim() : '';
            var color = colorInput ? String(colorInput.value || '').trim() : '';
            var err = State.validateIdentity(label, color);

            if (err) {
                if (labelInput && !label) {
                    if (Guide) {
                        Guide.showStep(0);
                    } else {
                        labelInput.focus();
                    }
                    labelInput.reportValidity();
                    return;
                }
                Confirm.alert(err, { title: 'Identite' });
                if (Guide) {
                    Guide.showStep(0);
                } else if (colorInput) {
                    colorInput.focus();
                }
                return;
            }

            State.createDraft(label, color);
            this.syncRecap(labelInput, colorInput);

            var self = this;
            var enterPlan = function () {
                if (self.wireframe && self.wireframe.render) {
                    self.wireframe.render();
                }

                if (self.wizard) {
                    self.wizard.hidden = false;
                    self.wizard.setAttribute('aria-hidden', 'false');
                }

                if (self.pageMode) {
                    if (self.identityPanel) {
                        self.identityPanel.hidden = true;
                    }
                    if (Guide) {
                        Guide.hideOverview();
                    }
                    if (self.wizard) {
                        self.wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } else if (self.wizard) {
                    document.body.classList.add('em-wp-template-wizard-open');
                }
            };

            this.advanceToStep(labelInput, colorInput, 1, enterPlan);
        },
    };
})();
