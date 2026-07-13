<?php
/**
 * Logique client du bandeau Apparence (EM-SITE) — EmSiteAppearance.
 *
 * collect(builder) : lit les réglages globaux (couleurs de liens par état,
 * soulignement, espaces haut/bas). updatePill(builder, style) : met à jour la
 * pastille « Aperçu » en temps réel (fond, texte, lien + survol/visité via vars).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
window.EmSiteAppearance = (function () {
    var COLOR = { background: 'bg', text: 'text', link: 'link', link_hover: 'linkHover', link_visited: 'linkVisited' };
    var NUM = { space_top: 'padTop', space_bottom: 'padBottom', space_left: 'padLeft', space_right: 'padRight' };

    function collect(builder) {
        var c = {};
        builder.querySelectorAll('.em-site-appearance__item').forEach(function (it) {
            var role = it.getAttribute('data-role');
            var col = it.querySelector('.em-site-admin-color-value');
            var tg = it.querySelector('.em-site-appearance__toggle-input');
            var nb = it.querySelector('.em-site-appearance__num-input');
            var fn = it.querySelector('.em-site-appearance__font-input');
            var bgp = it.querySelector('.em-site-appearance__bgpos-input');
            var bgm = it.querySelector('.em-site-appearance__bgmedia');
            var bgo = it.querySelector('.em-site-appearance__bgopacity-input');
            if (col && COLOR[role]) { c[COLOR[role]] = col.value; }
            if (tg && role === 'link_underline') { c.underline = tg.checked; }
            if (tg && role === 'background_mirror') { c.bgMirror = tg.checked; }
            if (tg && role === 'background_transparent') { c.bgTransparent = tg.checked; }
            if (nb && NUM[role]) { c[NUM[role]] = nb.value; }
            if (fn && role === 'font') { var o = fn.options[fn.selectedIndex]; c.font = o ? (o.getAttribute('data-stack') || '') : ''; }
            if (bgp && role === 'background_pos') { c.bgPos = bgp.value; }
            if (bgm && role === 'background_image') { c.bgImage = bgm.getAttribute('data-url') || ''; }
            if (bgo && role === 'background_opacity') { c.bgOpacity = bgo.value; }
        });
        return c;
    }

    // Sélecteur d'image de fond (bibliothèque média). Met à jour l'ID caché,
    // l'URL pleine taille et la vignette, puis notifie le builder (change bubble).
    function openBgImage(media) {
        if (!window.wp || !window.wp.media) { return; }
        var frame = window.wp.media({ title: '<?php echo esc_js(__('Image de fond', 'em-site')); ?>', multiple: false, library: { type: 'image' } });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var sizes = att.sizes || {};
            var hidden = media.querySelector('.em-site-appearance__bgid');
            var thumb = media.querySelector('.em-site-appearance__bgthumb');
            var clear = media.querySelector('.em-site-appearance__bgclear');
            media.setAttribute('data-url', att.url || '');
            if (hidden) { hidden.value = att.id; }
            if (thumb) { thumb.src = (sizes.medium ? sizes.medium.url : att.url); thumb.hidden = false; }
            if (clear) { clear.hidden = false; }
            if (hidden) { hidden.dispatchEvent(new Event('change', { bubbles: true })); }
        });
        frame.open();
    }

    function clearBgImage(media) {
        var hidden = media.querySelector('.em-site-appearance__bgid');
        var thumb = media.querySelector('.em-site-appearance__bgthumb');
        var clear = media.querySelector('.em-site-appearance__bgclear');
        media.setAttribute('data-url', '');
        if (hidden) { hidden.value = ''; }
        if (thumb) { thumb.src = ''; thumb.hidden = true; }
        if (clear) { clear.hidden = true; }
        if (hidden) { hidden.dispatchEvent(new Event('change', { bubbles: true })); }
    }

    document.addEventListener('click', function (e) {
        var pick = e.target.closest('.em-site-appearance__bgpick');
        if (pick) { e.preventDefault(); var m = pick.closest('.em-site-appearance__bgmedia'); if (m) { openBgImage(m); } return; }
        var clr = e.target.closest('.em-site-appearance__bgclear');
        if (clr) { e.preventDefault(); var m2 = clr.closest('.em-site-appearance__bgmedia'); if (m2) { clearBgImage(m2); } return; }
    });

    function updatePill(builder, c) {
        var box = builder.querySelector('.em-site-appearance__preview-box');
        if (!box) { return; }
        var t = box.querySelector('.ap-text'), l = box.querySelector('.ap-link');
        box.style.fontFamily = c.font || '';
        if (c.bg) { box.style.background = c.bg; }
        if (t && c.text) { t.style.color = c.text; }
        if (l) {
            box.style.setProperty('--ap-link', c.link || '');
            box.style.setProperty('--ap-link-hover', c.linkHover || c.link || '');
            box.style.setProperty('--ap-link-visited', c.linkVisited || c.link || '');
            l.style.textDecoration = c.underline ? 'underline' : 'none';
            // ":visited" est bridé par les navigateurs : on simule l'état au clic.
            if (!l.dataset.visitedBound) {
                l.dataset.visitedBound = '1';
                l.addEventListener('click', function (e) { e.preventDefault(); l.classList.toggle('is-visited'); });
            }
        }
    }

    // Espacements liés : la chaîne synchronise les deux valeurs d'une paire.
    function syncPair(group, source) {
        var inputs = group.querySelectorAll('.em-site-appearance__num-input');
        if (inputs.length < 2) { return; }
        var other = inputs[0] === source ? inputs[1] : inputs[0];
        if (other.value !== source.value) {
            other.value = source.value;
            other.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.em-site-appearance__chain');
        if (!btn) { return; }
        e.preventDefault();
        var group = btn.closest('.em-site-appearance__group');
        if (!group) { return; }
        var on = btn.getAttribute('aria-pressed') !== 'true';
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        group.classList.toggle('is-linked', on);
        var icon = btn.querySelector('.dashicons');
        if (icon) { icon.className = 'dashicons dashicons-' + (on ? 'admin-links' : 'editor-unlink'); }
        if (on) {
            var first = group.querySelector('.em-site-appearance__num-input');
            if (first) { syncPair(group, first); }
        }
    });
    document.addEventListener('input', function (e) {
        var inp = e.target.closest('.em-site-appearance__num-input');
        if (!inp) { return; }
        var group = inp.closest('.em-site-appearance__group');
        if (group && group.classList.contains('is-linked')) { syncPair(group, inp); }
    });

    return { collect: collect, updatePill: updatePill };
})();
</script>
<?php
