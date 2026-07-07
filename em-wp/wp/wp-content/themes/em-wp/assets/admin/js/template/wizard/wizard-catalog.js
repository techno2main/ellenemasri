(function () {
    'use strict';

    window.EmWpTemplateWizard = window.EmWpTemplateWizard || {};

    var State = EmWpTemplateWizard.State;

    function buildSelect(id, choices, value, field) {
        var wrap = document.createElement('div');
        wrap.className = 'em-wp-template-wizard-catalog__field';

        var label = document.createElement('label');
        label.setAttribute('for', id);
        label.textContent = field.label;
        wrap.appendChild(label);

        var select = document.createElement('select');
        select.id = id;
        select.className = 'em-wp-template-wizard-catalog__select';
        select.setAttribute('data-catalog-field', field.key);

        (choices || []).forEach(function (choice) {
            var opt = document.createElement('option');
            opt.value = choice.slug;
            opt.textContent = choice.label;
            if (choice.slug === value) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });

        wrap.appendChild(select);
        return wrap;
    }

    EmWpTemplateWizard.Catalog = {
        bind: function (root) {
            var host = root.querySelector('#em-wp-template-wizard-catalog-panels');
            var self = this;

            if (host) {
                host.addEventListener('change', function (event) {
                    var select = event.target.closest('select[data-catalog-field]');
                    if (!select) {
                        return;
                    }
                    var panel = select.closest('[data-catalog-rubrique]');
                    var draft = State.getDraft();
                    if (!panel || !draft) {
                        return;
                    }
                    var slug = panel.getAttribute('data-catalog-rubrique');
                    var field = select.getAttribute('data-catalog-field');
                    if (slug === 'header') {
                        if (!draft.catalog.header) {
                            draft.catalog.header = {};
                        }
                        draft.catalog.header[field] = select.value;
                    } else {
                        if (!draft.catalog[slug]) {
                            draft.catalog[slug] = {};
                        }
                        draft.catalog[slug][field] = select.value;
                    }
                    State.markDirty();
                });
            }

            this.render = function () {
                var draft = State.getDraft();
                if (!host || !draft) {
                    return;
                }
                host.innerHTML = '';
                var order = draft.skeleton.order || [];
                var i18n = State.config.i18n || {};

                order.forEach(function (slug) {
                    var def = State.getRubriqueDef(slug);
                    if (!def) {
                        return;
                    }

                    var panel = document.createElement('section');
                    panel.className = 'em-wp-template-wizard-catalog__panel';
                    panel.setAttribute('data-catalog-rubrique', slug);

                    var title = document.createElement('h3');
                    title.className = 'em-wp-template-wizard-catalog__title';
                    title.textContent = def.label || slug;
                    panel.appendChild(title);

                    if (slug === 'header') {
                        var header = draft.catalog.header || {};
                        panel.appendChild(buildSelect(
                            'em-wp-wizard-header-hero',
                            State.config.catalogs.heroes || [],
                            header.hero_slug || '',
                            { key: 'hero_slug', label: 'Hero' }
                        ));
                        panel.appendChild(buildSelect(
                            'em-wp-wizard-header-slider',
                            State.config.catalogs.sliders || [],
                            header.slider_slug || '',
                            { key: 'slider_slug', label: 'Slider' }
                        ));
                        var layouts = State.config.headerLayouts || [];
                        var layoutChoices = layouts.map(function (l) {
                            return { slug: l.value, label: l.label };
                        });
                        panel.appendChild(buildSelect(
                            'em-wp-wizard-header-layout',
                            layoutChoices,
                            header.layout || 'hero_left',
                            { key: 'layout', label: 'Layout' }
                        ));
                    } else if (def.comingSoon) {
                        var hint = document.createElement('p');
                        hint.className = 'em-wp-template-wizard-catalog__hint';
                        hint.textContent = i18n.comingSoonHint || '';
                        panel.appendChild(hint);
                    } else if (def.needsCatalogPick) {
                        var row = draft.catalog[slug] || {};
                        var choices = State.config.catalogs[def.catalogHub] || [];
                        panel.appendChild(buildSelect(
                            'em-wp-wizard-catalog-' + slug,
                            choices,
                            row[def.pointerKey] || '',
                            { key: def.pointerKey, label: 'Entrée catalogue' }
                        ));
                    } else {
                        return;
                    }

                    host.appendChild(panel);
                });
            };
        },
    };
})();
