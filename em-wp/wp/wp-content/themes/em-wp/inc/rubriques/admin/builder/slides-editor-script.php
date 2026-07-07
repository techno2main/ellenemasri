<?php
/**
 * Éditeur de slides riche du builder (V4) : bandeau titre + liste de slides
 * (image / TikTok / vidéo YouTube). Sérialise toute la configuration en JSON
 * dans l'input caché `.em-v4-chip__value` du champ « Slider », et déclenche la
 * mise à jour du builder (aperçu + savebar) via un événement `input`.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
window.EmWpV4Slides = (function () {
    var TXT = {
        pickImage: '<?php echo esc_js(__('Choisir une image', 'em-wp')); ?>',
        pickVideo: '<?php echo esc_js(__('Choisir une vidéo', 'em-wp')); ?>'
    };

    function v(scope, sel) { var el = scope.querySelector(sel); return el ? el.value : ''; }
    function ck(scope, sel) { var el = scope.querySelector(sel); return el ? !!el.checked : false; }

    // Lit l'éditeur (DOM) → configuration → input caché JSON.
    function serialize(editor) {
        var cfg = {
            title: v(editor, '.em-v4-slides__title'),
            title_hidden: ck(editor, '.em-v4-slides__title-hidden'),
            frame_bg: v(editor, '.em-v4-slides__frame'),
            footer_bg: v(editor, '.em-v4-slides__footerbg'),
            footer_text: v(editor, '.em-v4-slides__footertext'),
            slides: []
        };
        editor.querySelectorAll('.em-v4-slide').forEach(function (row) {
            var eye = row.querySelector('.em-v4-slide__eye');
            cfg.slides.push({
                type: v(row, '.em-v4-slide__type') || 'image',
                name: v(row, '.em-v4-slide__name'),
                image: v(row, '.em-v4-slide__image'),
                video_url: v(row, '.em-v4-slide__videourl'),
                tiktok_url: v(row, '.em-v4-slide__tiktokurl'),
                tiktok_video_url: v(row, '.em-v4-slide__tiktokvideo'),
                alt_text: '',
                duration: parseInt(v(row, '.em-v4-slide__duration'), 10) || 5,
                hidden: eye ? eye.getAttribute('data-hidden') === '1' : false
            });
        });
        var hidden = editor.querySelector('.em-v4-chip__value');
        if (hidden) { hidden.value = JSON.stringify(cfg); }
    }

    // Sérialise puis notifie le builder (aperçu + détection de modif).
    function commit(editor) {
        serialize(editor);
        var hidden = editor.querySelector('.em-v4-chip__value');
        if (hidden) { hidden.dispatchEvent(new Event('input', { bubbles: true })); }
    }

    function openPick(pick) {
        if (!window.wp || !window.wp.media) { return; }
        var target = pick.getAttribute('data-target') || 'image';
        var row = pick.closest('.em-v4-slide');
        var editor = pick.closest('.em-v4-slides');
        if (!row || !editor) { return; }
        var lib = target === 'ttvid' ? 'video' : 'image';
        var frame = window.wp.media({ title: lib === 'video' ? TXT.pickVideo : TXT.pickImage, multiple: false, library: { type: lib } });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var sizes = att.sizes || {};
            if (target === 'image') {
                var url = sizes.large ? sizes.large.url : att.url;
                var hid = row.querySelector('.em-v4-slide__image');
                var img = row.querySelector('.em-v4-slide__thumb');
                if (hid) { hid.value = url; }
                if (img) { img.src = sizes.thumbnail ? sizes.thumbnail.url : url; img.hidden = false; }
            } else {
                var hid2 = row.querySelector('.em-v4-slide__tiktokvideo');
                var name = row.querySelector('.em-v4-slide__media--ttvid .em-v4-slide__medianame');
                if (hid2) { hid2.value = att.url; }
                if (name) { name.textContent = att.filename || att.title || ''; }
            }
            commit(editor);
        });
        frame.open();
    }

    // Capture : sérialise AVANT que le builder (bulle) ne lise la valeur.
    document.addEventListener('input', function (e) {
        var editor = e.target.closest ? e.target.closest('.em-v4-slides') : null;
        if (editor) { serialize(editor); }
    }, true);

    document.addEventListener('change', function (e) {
        var t = e.target;
        if (!t.closest) { return; }
        var row = t.closest('.em-v4-slide');
        if (row && t.classList && t.classList.contains('em-v4-slide__type')) {
            row.setAttribute('data-type', t.value);
        }
        var editor = t.closest('.em-v4-slides');
        if (editor) { serialize(editor); }
    }, true);

    document.addEventListener('click', function (e) {
        var t = e.target;
        if (!t.closest) { return; }

        var add = t.closest('.em-v4-slides__add');
        if (add) {
            e.preventDefault();
            var ed = add.closest('.em-v4-slides');
            var list = ed ? ed.querySelector('.em-v4-slides__list') : null;
            if (list && window.EmWpV4Chip && window.EmWpV4Chip.slideRowHtml) {
                list.insertAdjacentHTML('beforeend', window.EmWpV4Chip.slideRowHtml());
                commit(ed);
            }
            return;
        }

        var del = t.closest('.em-v4-slide__del');
        if (del) {
            e.preventDefault();
            var ed2 = del.closest('.em-v4-slides');
            var rowDel = del.closest('.em-v4-slide');
            if (rowDel) { rowDel.remove(); }
            if (ed2) { commit(ed2); }
            return;
        }

        var up = t.closest('.em-v4-slide__up');
        if (up) {
            e.preventDefault();
            var ru = up.closest('.em-v4-slide');
            var prev = ru ? ru.previousElementSibling : null;
            if (ru && prev && prev.classList.contains('em-v4-slide')) { ru.parentNode.insertBefore(ru, prev); }
            commit(up.closest('.em-v4-slides'));
            return;
        }

        var down = t.closest('.em-v4-slide__down');
        if (down) {
            e.preventDefault();
            var rd = down.closest('.em-v4-slide');
            var next = rd ? rd.nextElementSibling : null;
            if (rd && next && next.classList.contains('em-v4-slide')) { rd.parentNode.insertBefore(next, rd); }
            commit(down.closest('.em-v4-slides'));
            return;
        }

        var eye = t.closest('.em-v4-slide__eye');
        if (eye) {
            e.preventDefault();
            var wasHidden = eye.getAttribute('data-hidden') === '1';
            eye.setAttribute('data-hidden', wasHidden ? '0' : '1');
            var ic = eye.querySelector('.dashicons');
            if (ic) { ic.className = 'dashicons dashicons-' + (wasHidden ? 'visibility' : 'hidden'); }
            var rowEye = eye.closest('.em-v4-slide');
            if (rowEye) { rowEye.classList.toggle('is-hidden', !wasHidden); }
            commit(eye.closest('.em-v4-slides'));
            return;
        }

        var pick = t.closest('.em-v4-slide__pick');
        if (pick) { e.preventDefault(); openPick(pick); return; }
    });

    return { serialize: serialize };
})();
</script>
<?php
