(function () {
    'use strict';
    var moduleForm = null;
    var baseline = '';
    var isDirty = false;
    var saveBtn = null;
    var allowFormSubmit = false;
    var i18n = {};
    function findModuleForm() {
        var wrap = document.querySelector('.wrap.em-site-admin-module, .wrap.em-site-rubriques-admin');
        if (!wrap) {
            return null;
        }
        var forms = wrap.querySelectorAll('form[id^="em-site-"][method="post"]');
        for (var i = 0; i < forms.length; i++) {
            if (!forms[i].closest('.em-site-template-banner')) {
                return forms[i];
            }
        }
        return null;
    }
    function findFormSubmitButtons(form) {
        if (!form) {
            return [];
        }
        return Array.prototype.slice.call(
            form.querySelectorAll('input[type="submit"].button-primary, button[type="submit"].button-primary')
        );
    }
    function serializeForm(form) {
        var entries = [];
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            var name = el.name;
            if (!name || el.disabled || el.closest('.em-site-template-banner')) {
                return;
            }
            if (el.type === 'checkbox') {
                entries.push(name + '=' + (el.checked ? (el.value || '1') : '0'));
                return;
            }
            if (el.type === 'radio') {
                if (el.checked) {
                    entries.push(name + '=' + el.value);
                }
                return;
            }
            if (el.tagName === 'SELECT' && el.multiple) {
                Array.prototype.forEach.call(el.selectedOptions, function (option) {
                    entries.push(name + '=' + option.value);
                });
                return;
            }
            if (el.type === 'submit' || el.type === 'button' || el.type === 'file' || (el.type === 'hidden' && name === '_wpnonce')) {
                return;
            }
            entries.push(name + '=' + el.value);
        });
        entries.sort();
        return entries.join('&');
    }
    function setRedirectAfterSave(form, redirectTo) {
        var input = form.querySelector('input[name="em-site_redirect_after_save"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'em-site_redirect_after_save';
            form.appendChild(input);
        }
        input.value = redirectTo || '';
    }
    function clearRedirectAfterSave(form) {
        var input = form.querySelector('input[name="em-site_redirect_after_save"]');
        if (input) {
            input.value = '';
        }
    }
    function setSaveControlsEnabled(enabled) {
        if (saveBtn) {
            saveBtn.disabled = !enabled;
            saveBtn.setAttribute('aria-disabled', enabled ? 'false' : 'true');
        }
        findFormSubmitButtons(moduleForm).forEach(function (button) {
            button.disabled = !enabled;
            button.setAttribute('aria-disabled', enabled ? 'false' : 'true');
        });
    }
    function refreshDirtyState() {
        if (!moduleForm) {
            isDirty = false;
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.setAttribute('aria-disabled', 'true');
            }
            return;
        }
        isDirty = serializeForm(moduleForm) !== baseline;
        setSaveControlsEnabled(isDirty);
    }
    function askConfirm(message, confirmLabel, cancelLabel) {
        var confirmApi = window.EmWpAdminConfirm;
        if (!confirmApi || typeof confirmApi.ask !== 'function') {
            return Promise.resolve(window.confirm(message));
        }
        return confirmApi.ask(message, {
            confirmLabel: confirmLabel || i18n.saveLabel || 'Enregistrer',
            cancelLabel: cancelLabel || i18n.cancelLabel || 'Annuler',
        });
    }
    function submitFormNative(redirectTo) {
        if (!moduleForm) {
            return Promise.resolve(false);
        }
        if (redirectTo) {
            setRedirectAfterSave(moduleForm, redirectTo);
        } else {
            clearRedirectAfterSave(moduleForm);
        }
        allowFormSubmit = true;
        if (typeof moduleForm.requestSubmit === 'function') {
            moduleForm.requestSubmit();
        } else {
            moduleForm.submit();
        }
        allowFormSubmit = false;
        return Promise.resolve(true);
    }
    function saveViaFetch(redirectTo) {
        if (!moduleForm) {
            return Promise.resolve(false);
        }
        var formData = new FormData(moduleForm);
        if (redirectTo) {
            formData.set('em-site_redirect_after_save', redirectTo);
        }
        return fetch(moduleForm.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            redirect: 'manual',
        }).then(function (response) {
            if (response.type === 'opaqueredirect' || (response.status >= 200 && response.status < 400) || response.status === 0) {
                baseline = serializeForm(moduleForm);
                isDirty = false;
                setSaveControlsEnabled(false);
                return true;
            }
            return false;
        }).catch(function () {
            return false;
        });
    }
    function init(options) {
        options = options || {};
        i18n = options.i18n || {};
        saveBtn = options.saveButton || document.getElementById('em-site-template-banner-save');
        moduleForm = findModuleForm();
        baseline = moduleForm ? serializeForm(moduleForm) : '';
        if (!moduleForm) {
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.setAttribute('aria-disabled', 'true');
            }
            return;
        }
        moduleForm.addEventListener('input', refreshDirtyState);
        moduleForm.addEventListener('change', refreshDirtyState);
        document.addEventListener('emWpAdminColorFieldChanged', refreshDirtyState);
        if (window.jQuery) {
            window.jQuery(document).on('emWpAdminColorFieldChanged.emWpDirty', refreshDirtyState);
            window.jQuery(moduleForm).on('change.emWpDirty input.emWpDirty', 'input, select, textarea', refreshDirtyState);
        }
        moduleForm.addEventListener('submit', function (event) {
            if (!isDirty) {
                event.preventDefault();
                return;
            }
            if (allowFormSubmit) {
                return;
            }
            event.preventDefault();
            window.EmWpModuleFormDirty.requestSave();
        });
        refreshDirtyState();
    }
    window.EmWpModuleFormDirty = {
        init: init,
        refresh: refreshDirtyState,
        isDirty: function () {
            return isDirty;
        },
        hasForm: function () {
            return moduleForm !== null;
        },
        requestSave: function (options) {
            options = options || {};
            if (!moduleForm || !isDirty) {
                return Promise.resolve(false);
            }
            var message = options.message || i18n.saveConfirm || 'Enregistrer la configuration actuelle ?';
            var confirmLabel = options.confirmLabel || i18n.saveLabel || 'Enregistrer';
            var cancelLabel = options.cancelLabel || i18n.cancelLabel || 'Annuler';
            var redirectTo = options.redirectTo || '';
            var useFetch = Boolean(options.useFetch);
            return askConfirm(message, confirmLabel, cancelLabel).then(function (confirmed) {
                if (!confirmed) {
                    return false;
                }
                if (useFetch) {
                    return saveViaFetch(redirectTo).then(function (saved) {
                        if (saved && redirectTo) {
                            window.location.href = redirectTo;
                        }
                        return saved;
                    });
                }
                return submitFormNative(redirectTo);
            });
        },
        saveSilentlyThen: function (callback) {
            if (!moduleForm || !isDirty) {
                if (typeof callback === 'function') {
                    callback(true);
                }
                return Promise.resolve(true);
            }
            return saveViaFetch('').then(function (saved) {
                if (typeof callback === 'function') {
                    callback(saved);
                }
                return saved;
            });
        },
    };
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.EmWpModuleFormDirty._autoInitDone) {
            init(window.EmWpModuleFormDirtyConfig || {});
        }
    });
})();
