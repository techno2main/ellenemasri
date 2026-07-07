(function ($) {
    'use strict';

    var runtime = window.EmAdminRuntime || null;

    function boot() {
        var modal = document.getElementById('em-wp-new-template-modal');

        if (!modal) {
            return;
        }

        var cfg = window.emWpNewTemplateLauncher || {};
        var i18n = cfg.i18n || {};
        var choicePanel = modal.querySelector('[data-new-template-panel="choice"]');
        var duplicatePanel = modal.querySelector('[data-new-template-panel="duplicate"]');
        var duplicateForm = document.getElementById('em-wp-new-template-duplicate-form');
        var sourceSelect = document.getElementById('em-wp-new-template-source');
        var labelInput = document.getElementById('em-wp-new-template-label');
        var colorInput = document.getElementById('em-wp-new-template-color');
        var openTriggers = document.querySelectorAll('[data-em-wp-new-template-open]');
        var colorInitialized = false;

        function grantWorkspaceLaunch() {
            var key = cfg.workspaceLaunchGrantKey || 'em_wp_wizard_workspace_launch';

            if (window.EmWpTemplateWizard
                && EmWpTemplateWizard.Draft
                && typeof EmWpTemplateWizard.Draft.grantWorkspaceLaunch === 'function') {
                EmWpTemplateWizard.Draft.grantWorkspaceLaunch();
                return;
            }

            try {
                sessionStorage.setItem(key, '1');
            } catch (err) {
                /* noop */
            }
        }

        function showPanel(name) {
            if (choicePanel) {
                choicePanel.hidden = name !== 'choice';
            }

            if (duplicatePanel) {
                duplicatePanel.hidden = name !== 'duplicate';
            }

            if (name === 'duplicate') {
                initColorPicker();
            }
        }

        function openModal() {
            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('em-wp-new-template-modal-open');
            showPanel('choice');

            var firstChoice = modal.querySelector('[data-em-wp-new-template-blank]');

            if (firstChoice) {
                firstChoice.focus();
            }
        }

        function closeModal() {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('em-wp-new-template-modal-open');
            showPanel('choice');

            if (duplicateForm) {
                duplicateForm.reset();
            }

            if (colorInput && window.emWpAdminColorFieldApi) {
                window.emWpAdminColorFieldApi.setValue(colorInput, '');
            }
        }

        function initColorPicker() {
            if (colorInitialized) {
                return;
            }

            if (window.emWpAdminColorFieldApi && typeof window.emWpAdminColorFieldApi.initAll === 'function') {
                window.emWpAdminColorFieldApi.initAll();
                colorInitialized = true;
            }
        }

        openTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                openModal();
            });
        });

        document.querySelectorAll('[data-em-wp-new-template-wizard]').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                grantWorkspaceLaunch();

                if (cfg.blankWizardUrl) {
                    window.location.href = cfg.blankWizardUrl;
                }
            });
        });

        document.querySelectorAll('[data-em-wp-new-template-duplicate]').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                openModal();
                showPanel('duplicate');

                if (sourceSelect) {
                    sourceSelect.focus();
                }
            });
        });

        modal.querySelectorAll('[data-em-wp-new-template-dismiss]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });

        modal.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });

        var blankBtn = modal.querySelector('[data-em-wp-new-template-blank]');

        if (blankBtn) {
            blankBtn.addEventListener('click', function () {
                grantWorkspaceLaunch();

                if (cfg.blankWizardUrl) {
                    window.location.href = cfg.blankWizardUrl;
                }
            });
        }

        var showDuplicateBtn = modal.querySelector('[data-em-wp-new-template-show-duplicate]');

        if (showDuplicateBtn) {
            showDuplicateBtn.addEventListener('click', function () {
                showPanel('duplicate');

                if (sourceSelect) {
                    sourceSelect.focus();
                }
            });
        }

        var showChoiceBtn = modal.querySelector('[data-em-wp-new-template-show-choice]');

        if (showChoiceBtn) {
            showChoiceBtn.addEventListener('click', function () {
                showPanel('choice');
            });
        }

        if (duplicateForm) {
            duplicateForm.addEventListener('submit', function (event) {
                var source = sourceSelect ? sourceSelect.value.trim() : '';
                var label = labelInput ? labelInput.value.trim() : '';
                var color = colorInput ? colorInput.value.trim() : '';

                if (source === '') {
                    event.preventDefault();
                    window.alert(i18n.sourceRequired || 'Choisissez le template à dupliquer.');
                    return;
                }

                if (label === '') {
                    event.preventDefault();
                    window.alert(i18n.nameRequired || 'Le nom du template est requis.');
                    if (labelInput) {
                        labelInput.focus();
                    }
                    return;
                }

                if (color === '') {
                    event.preventDefault();
                    window.alert(i18n.colorRequired || 'La couleur du template est requise.');
                    if (colorInput) {
                        colorInput.focus();
                    }
                }
            });
        }
    }

    if (runtime && typeof runtime.domReady === 'function') {
        runtime.domReady(boot);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}(jQuery));
