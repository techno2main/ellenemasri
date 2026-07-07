<?php
/**
 * Scripts (une fois) de l'en-tête des items : renommage inline, suppression
 * confirmée, et ancre #section. Extraits de list.php pour rester sous 300 lignes.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Script (une fois) : persiste l'ancre (#section) d'un item à la volée (AJAX)
 * depuis le champ de l'en-tête, sans ouvrir/fermer la section.
 */
function em_wp_v4_render_anchor_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    (function () {
        var NONCE = '<?php echo esc_js(wp_create_nonce('em_wp_v4_set_anchor')); ?>';

        function save(input) {
            var body = new URLSearchParams();
            body.set('action', 'em_wp_v4_set_anchor');
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
            var input = e.target.closest('.em-v4-item__anchorinput');
            if (input) { save(input); }
        });

        // Interagir avec le champ ne doit pas (dé)plier la section.
        document.addEventListener('click', function (e) {
            if (e.target.closest('.em-v4-item__anchorinput')) { e.preventDefault(); e.stopPropagation(); }
        });
        document.addEventListener('mousedown', function (e) {
            if (e.target.closest('.em-v4-item__anchorinput')) { e.stopPropagation(); }
        });
        document.addEventListener('keydown', function (e) {
            var input = e.target.closest('.em-v4-item__anchorinput');
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
function em_wp_v4_render_rename_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    (function () {
        var NONCE = '<?php echo esc_js(wp_create_nonce('em_wp_v4_rename_item')); ?>';
        function stop(e) { e.preventDefault(); e.stopPropagation(); }

        function parts(item) {
            return {
                name:    item.querySelector('.em-v4-item__name'),
                pen:     item.querySelector('.em-v4-item__edit'),
                input:   item.querySelector('.em-v4-item__nameinput'),
                confirm: item.querySelector('.em-v4-item__confirm'),
                cancel:  item.querySelector('.em-v4-item__cancel')
            };
        }

        function open(item) {
            var p = parts(item);
            if (!p.input) { return; }
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
            if (p.input) { p.input.hidden = true; }
            if (p.confirm) { p.confirm.hidden = true; }
            if (p.cancel) { p.cancel.hidden = true; }
            if (p.name) { p.name.hidden = false; }
            if (p.pen) { p.pen.hidden = false; }
        }

        function reflect(input) {
            var item = input.closest('.em-v4-item');
            var name = item ? item.querySelector('.em-v4-item__name') : null;
            if (name) { name.textContent = input.value; }
            var target = document.getElementById(input.getAttribute('data-target'));
            if (target) { target.value = input.value; }

            var slugValue = item ? item.querySelector('.em-v4-item__slug-value') : null;
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
            body.set('action', 'em_wp_v4_rename_item');
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
                    var slugValue = item.querySelector('.em-v4-item__slug-value');
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

                var slugValue = item.querySelector('.em-v4-item__slug-value');
                if (slugValue) {
                    slugValue.textContent = p.input.getAttribute('data-item') || '';
                }
            }
            close(item);
        }

        document.addEventListener('click', function (e) {
            var pen = e.target.closest('.em-v4-item__edit');
            if (pen) { stop(e); open(pen.closest('.em-v4-item')); return; }
            var ok = e.target.closest('.em-v4-item__confirm');
            if (ok) { stop(e); confirm(ok.closest('.em-v4-item')); return; }
            var no = e.target.closest('.em-v4-item__cancel');
            if (no) { stop(e); cancel(no.closest('.em-v4-item')); return; }
            if (e.target.closest('.em-v4-item__nameinput')) { e.preventDefault(); e.stopPropagation(); }
        });

        document.addEventListener('input', function (e) {
            var input = e.target.closest('.em-v4-item__nameinput');
            if (!input) { return; }
            input.value = input.value.toUpperCase();
            reflect(input);
        });

        // Plus de validation au clavier (Entrée) ni au blur : on neutralise Entrée.
        document.addEventListener('keydown', function (e) {
            var input = e.target.closest('.em-v4-item__nameinput');
            if (input && e.key === 'Enter') { e.preventDefault(); }
        });

        document.addEventListener('mousedown', function (e) {
            if (e.target.closest('.em-v4-item__nameinput')) { e.stopPropagation(); }
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
function em_wp_v4_render_delete_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.em-v4-delete');
        if (!btn) { return; }
        e.preventDefault();
        e.stopPropagation();
        if (!window.EmWpAdminConfirm) { return; }
        var form = document.getElementById(btn.getAttribute('data-deleteform'));
        if (!form) { return; }
        window.EmWpAdminConfirm.confirmDelete(function () { form.submit(); }, {
            title: btn.getAttribute('data-title') || '<?php echo esc_js(__('Supprimer', 'em-wp')); ?>',
            message: '<?php echo esc_js(__('Supprimer définitivement « ', 'em-wp')); ?>' + (btn.getAttribute('data-label') || '') + ' » ?',
            acknowledgeLabel: btn.getAttribute('data-ack') || '<?php echo esc_js(__('Je confirme la suppression.', 'em-wp')); ?>',
            confirmLabel: '<?php echo esc_js(__('Supprimer définitivement', 'em-wp')); ?>'
        });
    });
    </script>
    <?php
}
