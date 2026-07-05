(function () {
    'use strict';

    window.EmWpTemplateWizard = window.EmWpTemplateWizard || {};

    var State = EmWpTemplateWizard.State;
    var Confirm = EmWpTemplateWizard.Confirm;
    var Helpers = EmWpTemplateWizard.SkeletonHelpers || {};

    EmWpTemplateWizard.Skeleton = {
        listEl: null,
        pickerListEl: null,
        pickerEmptyEl: null,
        addWrap: null,
        currentPhase: 'full',
        sortable: null,

        destroySortable: function () {
            if (this.sortable) {
                this.sortable.destroy();
                this.sortable = null;
            }
        },

        initSortable: function () {
            this.destroySortable();

            var phase = Helpers.resolvePhase(this.currentPhase);

            if (!this.listEl || (phase !== 'order' && phase !== 'full') || !window.EmWpSlideSortable) {
                return;
            }

            if (!this.listEl.querySelector('.em-wp-template-wizard-skeleton__item.is-sortable')) {
                return;
            }

            var self = this;
            var footerItem = this.listEl.querySelector('[data-rubrique="footer"]');
            var topBarItem = this.listEl.querySelector('[data-rubrique="top-bar"]');

            this.sortable = new window.EmWpSlideSortable(this.listEl, {
                handle: '.em-wp-template-wizard-skeleton__drag',
                item: '.em-wp-template-wizard-skeleton__item.is-sortable',
                onEnd: function () {
                    self.applyOrderFromDom();
                },
            });

            var baseUpdate = this.sortable.updatePlaceholderPosition.bind(this.sortable);

            this.sortable.updatePlaceholderPosition = function (clientY) {
                baseUpdate(clientY);

                if (!this.placeholder || !this.placeholder.parentNode) {
                    return;
                }

                if (footerItem && footerItem.parentNode === this.container) {
                    var children = Array.from(this.container.children);
                    var footerIndex = children.indexOf(footerItem);
                    var placeholderIndex = children.indexOf(this.placeholder);

                    if (placeholderIndex >= footerIndex) {
                        this.container.insertBefore(this.placeholder, footerItem);
                    }
                }

                if (topBarItem && topBarItem.parentNode === this.container) {
                    var listChildren = Array.from(this.container.children);
                    var topIndex = listChildren.indexOf(topBarItem);
                    var currentIndex = listChildren.indexOf(this.placeholder);

                    if (currentIndex <= topIndex) {
                        var insertAfterTop = topBarItem.nextElementSibling;

                        if (insertAfterTop) {
                            this.container.insertBefore(this.placeholder, insertAfterTop);
                        }
                    }
                }
            };
        },

        applyOrderFromDom: function () {
            var draft = State.getDraft();

            if (!draft || !this.listEl) {
                return;
            }

            var order = Array.from(this.listEl.querySelectorAll('[data-rubrique]')).map(function (el) {
                return String(el.getAttribute('data-rubrique') || '');
            }).filter(function (slug) {
                return slug !== '';
            });

            if (!Helpers.isValidSkeletonOrder(order)) {
                this.render(this.currentPhase);
                return;
            }

            var previous = (draft.skeleton.order || []).join(',');

            if (previous === order.join(',')) {
                this.render(this.currentPhase);
                return;
            }

            draft.skeleton.order = order;
            State.markDirty();
            Helpers.notifySkeletonChanged('order');
            this.render(this.currentPhase);
        },

        addRubriqueFromPicker: function (slug) {
            var draft = State.getDraft();

            if (!draft || !slug) {
                return false;
            }

            if (draft.skeleton.order.indexOf(slug) !== -1) {
                return false;
            }

            if (!Helpers.insertRubrique(draft, slug)) {
                return false;
            }

            State.markDirty();
            Helpers.notifySkeletonChanged('structure');
            return true;
        },

        bind: function (root) {
            this.listEl = root.querySelector('#em-wp-template-wizard-skeleton-list');
            this.pickerListEl = root.querySelector('[data-wizard-skeleton-picker-list]');
            this.pickerEmptyEl = root.querySelector('[data-wizard-skeleton-picker-empty]');
            this.addWrap = root.querySelector('[data-wizard-skeleton-add]');

            var self = this;

            if (this.listEl) {
                this.listEl.addEventListener('click', function (event) {
                    var btn = event.target.closest('button');

                    if (!btn) {
                        return;
                    }

                    var item = btn.closest('[data-rubrique]');

                    if (!item) {
                        return;
                    }

                    var slug = item.getAttribute('data-rubrique');
                    var draft = State.getDraft();

                    if (!draft) {
                        return;
                    }

                    var order = draft.skeleton.order;

                    if (btn.hasAttribute('data-move')) {
                        var idx = order.indexOf(slug);
                        var swap = btn.getAttribute('data-move') === 'up' ? idx - 1 : idx + 1;

                        if (swap < 0 || swap >= order.length) {
                            return;
                        }

                        order[idx] = order[swap];
                        order[swap] = slug;
                        State.markDirty();
                        Helpers.notifySkeletonChanged('order');
                        self.render(self.currentPhase);
                        return;
                    }

                    if (btn.classList.contains('em-wp-template-wizard-skeleton__remove')) {
                        var def = State.getRubriqueDef(slug);
                        var phase = Helpers.resolvePhase(self.currentPhase);

                        function applyRemove() {
                            draft.skeleton.order = order.filter(function (s) {
                                return s !== slug;
                            });

                            delete draft.skeleton.rubriques[slug];
                            delete draft.catalog[slug];
                            State.markDirty();
                            Helpers.notifySkeletonChanged('structure');
                            self.render(self.currentPhase);
                        }

                        if (phase === 'pick') {
                            applyRemove();
                            return;
                        }

                        var msg = (State.config.i18n && State.config.i18n.removeRubrique) || 'Retirer ?';

                        Confirm.ask(msg, { title: def ? def.label : slug }).then(function (ok) {
                            if (!ok) {
                                return;
                            }

                            applyRemove();
                        });
                    }
                });
            }

            if (this.pickerListEl) {
                this.pickerListEl.addEventListener('click', function (event) {
                    var btn = event.target.closest('.em-wp-template-wizard-skeleton__picker-add');

                    if (!btn) {
                        return;
                    }

                    var slug = String(btn.getAttribute('data-rubrique-slug') || btn.value || '');

                    if (slug === '') {
                        return;
                    }

                    if (self.addRubriqueFromPicker(slug)) {
                        self.render(self.currentPhase);
                    }
                });
            }
        },

        render: function (phase) {
            this.currentPhase = Helpers.resolvePhase(phase);

            Helpers.renderList(
                this.listEl,
                this.pickerListEl,
                this.pickerEmptyEl,
                this.addWrap,
                this.currentPhase
            );

            this.initSortable();
        },
    };
})();
