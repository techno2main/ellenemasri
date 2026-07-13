<?php
/**
 * Scripts (une fois) de l'en-tête des items : renommage inline, suppression
 * confirmée, et ancre #section. Extraits de list.php pour rester sous 300 lignes.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Script (une fois) : persiste l'ancre (#section) d'un item à la volée (AJAX)
 * depuis le champ de l'en-tête, sans ouvrir/fermer la section.
 */
function em_site_render_anchor_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    (function () {
        var NONCE = '<?php echo esc_js(wp_create_nonce('em_site_set_anchor')); ?>';

        function save(input) {
            var body = new URLSearchParams();
            body.set('action', 'em_site_set_anchor');
            body.set('_ajax_nonce', NONCE);
            body.set('type', input.getAttribute('data-type') || '');
            body.set('item', input.getAttribute('data-item') || '');
            body.set('anchor', input.value);
            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (res && res.success && res.data) { input.value = res.data.anchor || ''; }
            }).catch(function () {});
        }

        document.addEventListener('change', function (e) {
            var input = e.target.closest('.em-site-item__anchorinput');
            if (input) { save(input); }
        });

        // Interagir avec le champ ne doit pas (dé)plier la section.
        document.addEventListener('click', function (e) {
            if (e.target.closest('.em-site-item__anchorinput')) { e.preventDefault(); e.stopPropagation(); }
            if (e.target.closest('.em-site-item__slug')) { e.preventDefault(); e.stopPropagation(); }
        });
        document.addEventListener('mousedown', function (e) {
            if (e.target.closest('.em-site-item__anchorinput')) { e.stopPropagation(); }
            if (e.target.closest('.em-site-item__slug')) { e.preventDefault(); e.stopPropagation(); }
        });
        document.addEventListener('keydown', function (e) {
            var input = e.target.closest('.em-site-item__anchorinput');
            if (input && e.key === 'Enter') { e.preventDefault(); input.blur(); }
        });
    })();
    </script>
    <?php
}

/**
 * Script (une fois) : édition inline du nom d'un footer depuis l'en-tête.
 *
 * Le crayon affiche un champ ; la saisie (forcée en MAJUSCULES) met à jour le
 * nom affiché et le champ caché du builder. L'enregistrement persiste le nom.
 */
function em_site_render_rename_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    (function () {
        var NONCE = '<?php echo esc_js(wp_create_nonce('em_site_rename_item')); ?>';
        function stop(e) { e.preventDefault(); e.stopPropagation(); }

        function parts(item) {
            return {
                title:   item.querySelector('.em-site-item__title'),
                name:    item.querySelector('.em-site-item__name'),
                pen:     item.querySelector('.em-site-item__edit'),
                input:   item.querySelector('.em-site-item__nameinput'),
                confirm: item.querySelector('.em-site-item__confirm'),
                cancel:  item.querySelector('.em-site-item__cancel')
            };
        }

        function open(item) {
            var p = parts(item);
            if (!p.input) { return; }
            item.classList.add('is-renaming-item');
            if (p.title) { p.title.hidden = true; }
            if (p.name) { p.name.hidden = true; }
            if (p.pen) { p.pen.hidden = true; }
            p.input.hidden = false;
            if (p.confirm) { p.confirm.hidden = false; }
            if (p.cancel) { p.cancel.hidden = false; }
            p.input.focus();
            p.input.select();
        }

        function close(item) {
            var p = parts(item);
            item.classList.remove('is-renaming-item');
            if (p.input) { p.input.hidden = true; }
            if (p.confirm) { p.confirm.hidden = true; }
            if (p.cancel) { p.cancel.hidden = true; }
            if (p.title) { p.title.hidden = false; }
            if (p.name) { p.name.hidden = false; }
            if (p.pen) { p.pen.hidden = false; }
        }

        function reflect(input) {
            var item = input.closest('.em-site-item');
            var name = item ? item.querySelector('.em-site-item__name') : null;
            if (name) { name.textContent = input.value; }
            var target = document.getElementById(input.getAttribute('data-target'));
            if (target) { target.value = input.value; }

            var slugValue = item ? item.querySelector('.em-site-item__slug-value') : null;
            if (slugValue) {
                slugValue.textContent = buildSlugPreview(
                    input.getAttribute('data-type') || '',
                    input.value,
                    input.getAttribute('data-item') || ''
                );
            }
        }

        function sanitizeSlugPart(value) {
            var normalized = (value || '').toString().toLowerCase();
            if (normalized.normalize) {
                normalized = normalized.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            normalized = normalized
                .replace(/[^a-z0-9_-]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');

            return normalized;
        }

        function buildSlugPreview(type, label, currentItem) {
            var typeSlug = sanitizeSlugPart(type);
            var prefixMap = { header: 'hero', contacts: 'contact', sliders: 'slider' };
            var slugPrefix = prefixMap[typeSlug] || typeSlug;
            var valueSlug = sanitizeSlugPart(label);

            if (!slugPrefix) {
                return valueSlug || sanitizeSlugPart(currentItem) || 'item';
            }

            if (!valueSlug || valueSlug === slugPrefix || valueSlug === typeSlug) {
                return slugPrefix;
            }

            if (valueSlug.indexOf(slugPrefix + '-') === 0) {
                return valueSlug;
            }

            return slugPrefix + '-' + valueSlug;
        }

        function confirm(item) {
            var p = parts(item);
            if (!p.input) { return; }
            var val = p.input.value.trim();
            if (val === '' || val === p.input.getAttribute('data-original')) { close(item); return; }
            var body = new URLSearchParams();
            body.set('action', 'em_site_rename_item');
            body.set('_ajax_nonce', NONCE);
            body.set('type', p.input.getAttribute('data-type') || '');
            body.set('item', p.input.getAttribute('data-item') || '');
            body.set('label', val);
            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (res && res.success && res.data && res.data.label) {
                    p.input.value = res.data.label;
                    p.input.setAttribute('data-original', res.data.label);
                    reflect(p.input);

                    var currentItem = p.input.getAttribute('data-item') || '';
                    var nextItem = res.data.item || currentItem;

                    if (nextItem && nextItem !== currentItem) {
                        var type = p.input.getAttribute('data-type') || '';
                        var url = new URL(window.location.href);
                        if (type) { url.searchParams.set('type', type); }
                        url.searchParams.set('item', nextItem);
                        window.location.assign(url.toString());
                        return;
                    }

                    p.input.setAttribute('data-item', nextItem);
                    var slugValue = item.querySelector('.em-site-item__slug-value');
                    if (slugValue) { slugValue.textContent = nextItem; }
                }
            }).catch(function () {});
            close(item);
        }

        function cancel(item) {
            var p = parts(item);
            if (p.input) {
                p.input.value = p.input.getAttribute('data-original') || '';
                reflect(p.input);

                var slugValue = item.querySelector('.em-site-item__slug-value');
                if (slugValue) {
                    slugValue.textContent = p.input.getAttribute('data-item') || '';
                }
            }
            close(item);
        }

        document.addEventListener('click', function (e) {
            var pen = e.target.closest('.em-site-item__edit');
            if (pen) { stop(e); open(pen.closest('.em-site-item')); return; }
            var ok = e.target.closest('.em-site-item__confirm');
            if (ok) { stop(e); confirm(ok.closest('.em-site-item')); return; }
            var no = e.target.closest('.em-site-item__cancel');
            if (no) { stop(e); cancel(no.closest('.em-site-item')); return; }
            if (e.target.closest('.em-site-item__nameinput')) { e.preventDefault(); e.stopPropagation(); }
        });

        document.addEventListener('input', function (e) {
            var input = e.target.closest('.em-site-item__nameinput');
            if (!input) { return; }
            input.value = input.value.toUpperCase();
            reflect(input);
        });

        // Plus de validation au clavier (Entrée) ni au blur : on neutralise Entrée.
        document.addEventListener('keydown', function (e) {
            var input = e.target.closest('.em-site-item__nameinput');
            if (input && e.key === 'Enter') { e.preventDefault(); }
        });

        document.addEventListener('mousedown', function (e) {
            if (e.target.closest('.em-site-item__nameinput')) { e.stopPropagation(); }
        });
    })();
    </script>
    <?php
}

/**
 * Script (une fois) : confirme la suppression d'un footer puis soumet.
 *
 * Le bouton (corbeille) vit dans l'en-tête ; il cible le formulaire caché du
 * corps via data-deleteform. stopPropagation évite d'ouvrir/fermer la section.
 */
function em_site_render_delete_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.em-site-delete');
        if (!btn) { return; }
        e.preventDefault();
        e.stopPropagation();
        if (!window.EmWpAdminConfirm) { return; }
        var form = document.getElementById(btn.getAttribute('data-deleteform'));
        if (!form) { return; }
        window.EmWpAdminConfirm.confirmDelete(function () { form.submit(); }, {
            title: btn.getAttribute('data-title') || '<?php echo esc_js(__('Supprimer', 'em-site')); ?>',
            message: '<?php echo esc_js(__('Supprimer définitivement « ', 'em-site')); ?>' + (btn.getAttribute('data-label') || '') + ' » ?',
            acknowledgeLabel: btn.getAttribute('data-ack') || '<?php echo esc_js(__('Je confirme la suppression.', 'em-site')); ?>',
            confirmLabel: '<?php echo esc_js(__('Supprimer définitivement', 'em-site')); ?>'
        });
    });
    </script>
    <?php
}

/**
 * Script (une fois) : onglets "Apparence / Contenu" dans l'en-tête item.
 *
 * Les onglets sont placés sur la ligne titre (après le slug) et pilotent les
 * sections du builder sans ouvrir/fermer l'item par inadvertance.
 */
function em_site_render_item_section_tabs_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    (function () {
        function itemSections(item) {
            if (!item) { return []; }
            return Array.prototype.slice.call(item.querySelectorAll('.em-site-builder__section[data-item-section]'));
        }

        function itemContainer(item) {
            if (!item) { return null; }
            return item.querySelector('.em-site-builder, .em-site-header-item-editor');
        }

        function normalizeTarget(item, target) {
            if (!item || !target) { return ''; }
            var sections = itemSections(item);
            var has = sections.some(function (section) {
                return section.getAttribute('data-item-section') === target;
            });
            if (has) { return target; }
            if (target === 'content') {
                has = sections.some(function (section) { return section.getAttribute('data-item-section') === 'composition'; });
                if (has) { return 'composition'; }
            }
            if (target === 'composition') {
                has = sections.some(function (section) { return section.getAttribute('data-item-section') === 'content'; });
                if (has) { return 'content'; }
            }
            return '';
        }

        function syncTabs(item) {
            if (!item) { return; }
            var tabs = item.querySelectorAll('.em-site-item__section-tab');
            if (!tabs.length) { return; }

            var active = '';
            var sections = itemSections(item);
            var container = itemContainer(item);

            tabs.forEach(function (tab) {
                var normalized = normalizeTarget(item, tab.getAttribute('data-item-section-target') || '');
                if (!normalized || active) { return; }
                var sec = sections.find(function (section) {
                    return section.getAttribute('data-item-section') === normalized;
                });
                if (sec && sec.open) {
                    active = normalized;
                }
            });

            if (container) {
                if (active) {
                    container.setAttribute('data-inline-tab-target', active);
                } else {
                    container.removeAttribute('data-inline-tab-target');
                }
            }

            tabs.forEach(function (tab) {
                var target = normalizeTarget(item, tab.getAttribute('data-item-section-target') || '');
                var isActive = (target !== '' && target === active);
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        }

        function openItemSection(item, target) {
            if (!item || !target) { return; }
            item.open = true;

            var normalized = normalizeTarget(item, target);
            if (!normalized) { return; }

            var sections = itemSections(item);
            sections.forEach(function (section) {
                section.open = section.getAttribute('data-item-section') === normalized;
            });

            var container = itemContainer(item);
            if (container) {
                container.setAttribute('data-inline-tab-target', normalized);
            }

            syncTabs(item);
        }

        function resetItemSections(item) {
            if (!item) { return; }
            itemSections(item).forEach(function (section) {
                section.open = false;
            });

            var container = itemContainer(item);
            if (container) {
                container.removeAttribute('data-inline-tab-target');
            }

            syncTabs(item);
        }

        document.addEventListener('click', function (e) {
            var tab = e.target.closest('.em-site-item__section-tab');
            if (!tab) { return; }

            e.preventDefault();
            e.stopPropagation();

            var target = tab.getAttribute('data-item-section-target') || '';
            var item = tab.closest('.em-site-item');
            openItemSection(item, target);
        });

        document.addEventListener('mousedown', function (e) {
            if (!e.target.closest('.em-site-item__section-tab')) { return; }
            e.stopPropagation();
        });

        document.addEventListener('toggle', function (e) {
            var itemNode = e.target;
            if (itemNode && itemNode.classList && itemNode.classList.contains('em-site-item')) {
                if (!itemNode.open) {
                    resetItemSections(itemNode);
                } else {
                    syncTabs(itemNode);
                }
            }

            var section = e.target;
            if (!section || !section.classList || !section.classList.contains('em-site-builder__section')) {
                return;
            }
            var item = section.closest('.em-site-item');
            syncTabs(item);
        }, true);

        document.querySelectorAll('.em-site-item').forEach(syncTabs);
    })();
    </script>
    <?php
}
