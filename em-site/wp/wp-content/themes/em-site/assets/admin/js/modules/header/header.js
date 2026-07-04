(function () {
    'use strict';

    function getCatalogChoiceLabel(form, part, attributeName) {
        var field = form.querySelector('[data-catalog-part="' + part + '"]');

        if (!field) {
            return '';
        }

        var checked = field.querySelector('.em-wp-admin-catalog-slug-switch:checked');

        if (!checked) {
            return '';
        }

        attributeName = attributeName || 'data-choice-label';

        return (checked.getAttribute(attributeName) || '').trim();
    }

    function buildWireframePartLabel(part, name) {
        if (part === 'hero') {
            return name !== '' ? 'HERO ' + name : 'HERO';
        }

        return name !== '' ? 'SLIDE ' + name : 'SLIDE';
    }

    function buildLayoutHint(layout, heroName, sliderName) {
        if (layout === 'slider_left') {
            if (heroName !== '' && sliderName !== '') {
                return sliderName + ' à gauche, ' + heroName + ' à droite';
            }

            return 'Slider à gauche, Hero à droite';
        }

        if (heroName !== '' && sliderName !== '') {
            return heroName + ' à gauche, ' + sliderName + ' à droite';
        }

        return 'Hero à gauche, Slider à droite';
    }

    function syncHeaderLayoutWireframeLabels(form) {
        var preview = form.querySelector('.em-wp-header-admin__layout-preview');

        if (!preview) {
            return;
        }

        var heroWireframeName = getCatalogChoiceLabel(form, 'hero', 'data-choice-wireframe-label');
        var sliderWireframeName = getCatalogChoiceLabel(form, 'slider', 'data-choice-wireframe-label');
        var heroHintName = getCatalogChoiceLabel(form, 'hero', 'data-choice-label');
        var sliderHintName = getCatalogChoiceLabel(form, 'slider', 'data-choice-label');
        var heroLabel = preview.querySelector('[data-layout-part="hero"]');
        var sliderLabel = preview.querySelector('[data-layout-part="slider"]');
        var heroPart = heroLabel ? heroLabel.closest('.em-wp-header-admin__layout-part') : null;
        var sliderPart = sliderLabel ? sliderLabel.closest('.em-wp-header-admin__layout-part') : null;
        var hint = preview.parentElement
            ? preview.parentElement.querySelector('.em-wp-header-admin__layout-hint')
            : null;
        var layout = preview.getAttribute('data-header-layout') || 'hero_left';

        if (heroLabel) {
            heroLabel.textContent = buildWireframePartLabel('hero', heroWireframeName);
        }

        if (sliderLabel) {
            sliderLabel.textContent = buildWireframePartLabel('slider', sliderWireframeName);
        }

        if (heroPart) {
            heroPart.classList.toggle('is-empty', heroWireframeName === '');
        }

        if (sliderPart) {
            sliderPart.classList.toggle('is-empty', sliderWireframeName === '');
        }

        preview.setAttribute('data-hint-hero_left', buildLayoutHint('hero_left', heroHintName, sliderHintName));
        preview.setAttribute('data-hint-slider_left', buildLayoutHint('slider_left', heroHintName, sliderHintName));

        if (hint) {
            hint.textContent = buildLayoutHint(layout, heroHintName, sliderHintName);
        }
    }

    function initHeaderLayoutSwitcher() {
        var form = document.getElementById('em-wp-header-form');

        if (!form) {
            return;
        }

        document.querySelectorAll('.em-wp-header-admin__layout-preview').forEach(function (preview) {
            var swapButton = preview.querySelector('.em-wp-header-admin__layout-swap');
            var hiddenInput = preview.parentElement
                ? preview.parentElement.querySelector('.em-wp-header-admin__layout-input')
                : null;
            var hint = preview.parentElement
                ? preview.parentElement.querySelector('.em-wp-header-admin__layout-hint')
                : null;

            if (!swapButton || !hiddenInput) {
                return;
            }

            function applyLayout(layout) {
                var nextLayout = layout === 'slider_left' ? 'slider_left' : 'hero_left';

                preview.setAttribute('data-header-layout', nextLayout);
                hiddenInput.value = nextLayout;

                if (hint) {
                    var hintText = preview.getAttribute('data-hint-' + nextLayout) || '';
                    hint.textContent = hintText;
                }

                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            swapButton.addEventListener('click', function () {
                var currentLayout = preview.getAttribute('data-header-layout') || 'hero_left';
                var nextLayout = currentLayout === 'slider_left' ? 'hero_left' : 'slider_left';

                applyLayout(nextLayout);
            });

            var wireframe = preview.querySelector('.em-wp-header-admin__layout-wireframe');
            if (wireframe) {
                wireframe.addEventListener('click', function () {
                    swapButton.click();
                });
            }

            applyLayout(hiddenInput.value || preview.getAttribute('data-header-layout') || 'hero_left');
        });

        form.addEventListener('change', function (event) {
            if (!event.target.classList.contains('em-wp-admin-catalog-slug-input')) {
                return;
            }

            syncHeaderLayoutWireframeLabels(form);
        });

        syncHeaderLayoutWireframeLabels(form);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initHeaderLayoutSwitcher();

        document.querySelectorAll('.em-wp-header-media-button').forEach(function (button) {
            button.addEventListener('click', function () {
                var targetId = button.getAttribute('data-target');
                var previewId = button.getAttribute('data-preview');
                var input = targetId ? document.getElementById(targetId) : null;
                var preview = previewId ? document.getElementById(previewId) : null;

                if (!input || typeof wp === 'undefined' || !wp.media) {
                    return;
                }

                var frame = wp.media({
                    title: button.getAttribute('data-modal-title') || 'Choisir une image',
                    button: { text: button.getAttribute('data-modal-button') || 'Utiliser' },
                    multiple: false,
                });

                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var url = attachment && attachment.url ? attachment.url : '';

                    input.value = url;

                    if (preview) {
                        preview.classList.toggle('is-empty', url === '');
                        preview.innerHTML = url !== '' ? '<img src="' + url + '" alt="">' : '';
                    }
                });

                frame.open();
            });
        });
    });
})();
