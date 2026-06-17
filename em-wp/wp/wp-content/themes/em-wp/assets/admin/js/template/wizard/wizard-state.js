(function () {
    'use strict';

    window.EmWpTemplateWizard = window.EmWpTemplateWizard || {};

    var cfg = window.emWpTemplateWizardConfig || {};

    function cloneOrder(order) {
        return (order || []).slice();
    }

    function defaultRubriqueStyle(def) {
        return {
            background_color: def.defaultBg || '#100421',
            text_color: def.defaultText || '#ffffff',
            enabled: def.defaultEnabled !== false,
        };
    }

    function guessEntrySlug(hub, previewSlug) {
        var choices = (cfg.catalogs && cfg.catalogs[hub]) || [];
        var i;
        var slug;
        var candidates = [
            previewSlug,
            'contact-' + previewSlug,
            hub.replace(/s$/, '') + '-' + previewSlug,
        ];

        for (i = 0; i < candidates.length; i++) {
            slug = String(candidates[i] || '').toLowerCase();
            if (!slug) {
                continue;
            }
            for (var j = 0; j < choices.length; j++) {
                if (choices[j].slug === slug) {
                    return slug;
                }
            }
        }

        for (i = 0; i < choices.length; i++) {
            if (String(choices[i].slug).indexOf(previewSlug) !== -1) {
                return choices[i].slug;
            }
        }

        return choices.length ? choices[0].slug : '';
    }

    function previewSlugFromLabel(label) {
        return String(label || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .substring(0, 48);
    }

    EmWpTemplateWizard.State = {
        config: cfg,
        draft: null,
        dirty: false,

        createDraft: function (label, color) {
            var order = cloneOrder(cfg.defaultSkeletonOrder || ['top-bar', 'header', 'footer']);
            var rubriques = {};
            var catalog = {};
            var previewSlug = previewSlugFromLabel(label);
            var defs = cfg.rubriques || {};

            order.forEach(function (slug) {
                var def = defs[slug];
                if (def) {
                    rubriques[slug] = defaultRubriqueStyle(def);
                }
            });

            if (order.indexOf('header') !== -1) {
                catalog.header = {
                    hero_slug: guessEntrySlug('heroes', previewSlug),
                    slider_slug: guessEntrySlug('sliders', previewSlug),
                    layout: 'hero_left',
                };
            }

            Object.keys(defs).forEach(function (slug) {
                var def = defs[slug];
                if (!def || !def.needsCatalogPick || order.indexOf(slug) === -1) {
                    return;
                }
                var pointer = def.pointerKey;
                var row = {};
                row[pointer] = guessEntrySlug(def.catalogHub, previewSlug);
                catalog[slug] = row;
            });

            this.draft = {
                label: String(label || '').trim(),
                color: String(color || '').trim(),
                skeleton: {
                    order: order,
                    rubriques: rubriques,
                },
                catalog: catalog,
            };
            this.dirty = false;
            return this.draft;
        },

        getDraft: function () {
            return this.draft;
        },

        markDirty: function () {
            this.dirty = true;
        },

        getPayload: function () {
            return this.draft ? JSON.parse(JSON.stringify(this.draft)) : null;
        },

        getRubriqueDef: function (slug) {
            return (cfg.rubriques && cfg.rubriques[slug]) || null;
        },

        validateIdentity: function (label, color) {
            if (!String(label || '').trim()) {
                return cfg.i18n && cfg.i18n.nameRequired ? cfg.i18n.nameRequired : 'Nom requis';
            }
            if (!String(color || '').trim()) {
                return cfg.i18n && cfg.i18n.colorRequired ? cfg.i18n.colorRequired : 'Couleur requise';
            }
            return '';
        },

        validateSkeleton: function () {
            var order = this.draft && this.draft.skeleton ? this.draft.skeleton.order : [];
            if (order.indexOf('header') === -1) {
                return cfg.i18n && cfg.i18n.headerRequired ? cfg.i18n.headerRequired : 'HEADER requis';
            }
            return '';
        },

        validateCatalog: function () {
            var self = this;
            var order = this.draft && this.draft.skeleton ? this.draft.skeleton.order : [];
            var catalog = this.draft ? this.draft.catalog : {};
            var missing = false;

            order.forEach(function (slug) {
                var def = self.getRubriqueDef(slug);
                if (!def) {
                    return;
                }
                if (slug === 'header') {
                    var h = catalog.header || {};
                    if (!h.hero_slug || !h.slider_slug || !h.layout) {
                        missing = true;
                    }
                    return;
                }
                if (!def.needsCatalogPick) {
                    return;
                }
                var row = catalog[slug] || {};
                if (!row[def.pointerKey]) {
                    missing = true;
                }
            });

            if (missing) {
                return cfg.i18n && cfg.i18n.catalogRequired ? cfg.i18n.catalogRequired : 'Catalogue incomplet';
            }
            return '';
        },
    };
})();
