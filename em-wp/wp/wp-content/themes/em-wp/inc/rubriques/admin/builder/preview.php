<?php
/**
 * Aperçu temps réel partagé (V4) — window.EmWpV4Preview.
 *
 * Rendu client identique au front : lignes × colonnes (gauche/centre/droite) +
 * couleurs globales. Utilisé par le builder (Étape 1, placeholders = libellés) et
 * par l'édition de contenu (Étape 2, vraies valeurs).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Imprime le script d'aperçu (une seule fois).
 */
function em_wp_v4_render_preview_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    window.EmWpV4Preview = (function () {
        var FONTS = <?php echo wp_json_encode(em_wp_rubrique_font_choices()); ?>;

        function esc(value) {
            var d = document.createElement('div');
            d.textContent = value == null ? '' : String(value);
            return d.innerHTML;
        }

        function color(value) {
            return /^#[0-9a-fA-F]{3,8}$/.test(value || '') ? value : '';
        }

        // CSS inline d'un style de texte par champ (taille/police/couleur).
        function textStyleCss(s) {
            if (!s) { return ''; }
            var css = '';
            var size = parseInt(s.size, 10) || 0;
            if (size) { css += 'font-size:' + size + 'px;'; }
            if (s.font && FONTS[s.font]) { css += 'font-family:' + FONTS[s.font].stack + ';'; }
            if (color(s.color)) { css += 'color:' + color(s.color) + ';'; }
            return css;
        }

        // Balise image (redimension/recadrage + lien éventuel). '' si pas d'URL.
        function imageMarkup(url, iv, alt, hasLink) {
            if (!url) { return ''; }
            iv = iv || {};
            var w = parseInt(iv.w, 10) || 0, h = parseInt(iv.h, 10) || 0, st = '';
            if (w) { st += 'width:' + w + 'px;'; }
            if (h) { st += 'height:' + h + 'px;'; }
            if (w && h) { st += 'object-fit:cover;object-position:' + (iv.fx == null ? 50 : iv.fx) + '% ' + (iv.fy == null ? 50 : iv.fy) + '%;'; }
            var img = '<img class="em-rubrique__image" src="' + esc(url) + '"' + (st ? ' style="' + st + '"' : '') + ' alt="' + esc(alt) + '">';
            return hasLink ? '<a class="em-rubrique__link em-rubrique__link--media" href="#" onclick="return false;">' + img + '</a>' : img;
        }

        // Détecte le fournisseur d'une URL vidéo (youtube/tiktok) + ID.
        function videoProvider(url) {
            url = String(url || '').trim();
            var m;
            if ((m = url.match(/youtu\.be\/([\w-]+)/))) { return { p: 'youtube', id: m[1] }; }
            if ((m = url.match(/youtube\.com\/(?:watch\?v=|shorts\/|embed\/|live\/)([\w-]+)/))) { return { p: 'youtube', id: m[1] }; }
            if ((m = url.match(/tiktok\.com\/(?:embed\/v2\/|player\/v1\/)?(?:.*?\/video\/)?(\d{6,})/))) { return { p: 'tiktok', id: m[1] }; }
            return { p: '', id: '' };
        }

        function autoThumb(url) {
            var info = videoProvider(url);
            return info.p === 'youtube' && info.id ? 'https://i.ytimg.com/vi/' + info.id + '/hqdefault.jpg' : '';
        }

        function videoEmbed(url) {
            var info = videoProvider(url);
            var src = '';
            if (info.p === 'youtube' && info.id) { src = 'https://www.youtube.com/embed/' + info.id; }
            else if (info.p === 'tiktok' && info.id) { src = 'https://www.tiktok.com/embed/v2/' + info.id; }
            if (!src) { return url ? '<a class="em-rubrique__link" href="#" onclick="return false;">' + esc(url) + '</a>' : ''; }
            return '<div class="em-rubrique__video-embed em-rubrique__video-embed--' + info.p + '"><iframe src="' + esc(src) + '" frameborder="0" allowfullscreen></iframe></div>';
        }

        function fieldHtml(item) {
            if (item.type === 'sep_line') { var sc = color(item.value); return '<hr class="em-rubrique__sep"' + (sc ? ' style="border-color:' + sc + '"' : '') + '>'; }
            if (item.type === 'sep_blank') { var bh = parseInt(item.value, 10) || 0; return '<span class="em-rubrique__spacer" aria-hidden="true"' + (bh ? ' style="display:block;height:' + bh + 'px;"' : '') + '></span>'; }
            if (item.type === 'arrow_up' || item.type === 'arrow_down') {
                var ad = {}; try { ad = JSON.parse(item.value || '{}'); } catch (e) { ad = {}; }
                var ac = color(ad.color);
                var dir = item.type === 'arrow_up' ? 'up' : 'down';
                var glyph = item.type === 'arrow_up' ? '\u2191' : '\u2193';
                var arr = '<span class="em-rubrique__arrow em-rubrique__arrow--' + dir + '" aria-hidden="true"' + (ac ? ' style="color:' + ac + '"' : '') + '>' + glyph + '</span>';
                return ad.link ? '<a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="#" onclick="return false;"' + (ac ? ' style="color:' + ac + '"' : '') + '>' + arr + '</a>' : arr;
            }
            if (item.type === 'image') {
                if (!item.url) { return '<span class="em-rubrique__field">[' + esc(item.label || 'image') + ']</span>'; }
                var iv = {}; try { iv = JSON.parse(item.value || '{}'); } catch (e) { iv = {}; }
                return imageMarkup(item.url, iv, item.label, !!item.link);
            }
            if (item.type === 'text_image') {
                var ti = {}; try { ti = JSON.parse(item.value || '{}'); } catch (e) { ti = {}; }
                var tiText = ti.text || '';
                var tiStyle = textStyleCss(ti.style);
                var tiTextHtml = tiText ? '<p class="em-rubrique__field"' + (tiStyle ? ' style="' + tiStyle + '"' : '') + '>' + esc(tiText) + '</p>' : '';
                var tiImg = ti.image || {};
                var tiImgHtml = imageMarkup(item.imageUrl, tiImg, item.label, !!tiImg.link);
                if (!tiTextHtml && !tiImgHtml) { return '<span class="em-rubrique__field">[' + esc('texte + image') + ']</span>'; }
                return '<div class="em-rubrique__textimg">' + tiTextHtml + tiImgHtml + '</div>';
            }
            if (item.type === 'text_text') {
                var tt = {}; try { tt = JSON.parse(item.value || '{}'); } catch (e) { tt = {}; }
                var s1 = textStyleCss(tt.style), s2 = textStyleCss(tt.style2);
                var t1 = tt.text ? '<p class="em-rubrique__field"' + (s1 ? ' style="' + s1 + '"' : '') + '>' + esc(tt.text) + '</p>' : '';
                var t2 = tt.text2 ? '<p class="em-rubrique__field"' + (s2 ? ' style="' + s2 + '"' : '') + '>' + esc(tt.text2) + '</p>' : '';
                if (!t1 && !t2) { return '<span class="em-rubrique__field">[' + esc('texte + texte') + ']</span>'; }
                return '<div class="em-rubrique__texttext">' + t1 + t2 + '</div>';
            }
            if (item.type === 'icon') {
                if (!item.icon) { return '<span class="em-rubrique__field">[' + esc(item.label || 'icon') + ']</span>'; }
                var ic = '<i class="em-rubrique__icon fa-brands ' + esc(item.icon) + '" title="' + esc(item.label) + '"></i>';
                return item.link ? '<a class="em-rubrique__link em-rubrique__link--media" href="#" onclick="return false;">' + ic + '</a>' : ic;
            }
            if (item.type === 'platform_block' || item.type === 'network_block') { return platformCardHtml(item); }
            if (item.type === 'video_url') {
                var vd = {}; try { vd = JSON.parse(item.value || '{}'); } catch (e) { vd = {}; }
                var vurl = (typeof vd.url === 'string') ? vd.url : (item.url || '');
                var clickable = item.clickable !== undefined ? item.clickable : !!vd.clickable;
                var custom = item.thumbUrl || '';
                var poster = clickable ? (custom || autoThumb(vurl)) : custom;
                if (!poster) { return videoEmbed(vurl); }
                var facade = '<span class="em-rubrique__video-facade"><img class="em-rubrique__video-poster" src="' + esc(poster) + '" alt=""><span class="em-rubrique__video-play" aria-hidden="true"></span></span>';
                if (clickable && vurl) { return '<a class="em-rubrique__videourl em-rubrique__link--media" href="#" onclick="return false;">' + facade + '</a>'; }
                return '<span class="em-rubrique__videourl">' + facade + '</span>';
            }
            if (item.type === 'video_file') { return item.url ? '<video class="em-rubrique__video" controls preload="metadata" src="' + esc(item.url) + '"></video>' : '<span class="em-rubrique__field">[' + esc('vidéo') + ']</span>'; }
            if (item.type === 'audio_file') { return item.url ? '<audio class="em-rubrique__audio" controls preload="none" src="' + esc(item.url) + '"></audio>' : '<span class="em-rubrique__field">[' + esc('son') + ']</span>'; }
            if (item.type === 'audio_url') { return item.value ? '<audio class="em-rubrique__audio" controls preload="none" src="' + esc(item.value) + '"></audio>' : ''; }
            if (item.type === 'slider') {
                var surls = item.sliderUrls || [];
                if (!surls.length) { return '<span class="em-rubrique__field">[' + esc('slider') + ']</span>'; }
                var s = '';
                surls.forEach(function (u) { s += '<div class="em-rubrique__slide"><img class="em-rubrique__slide-img" src="' + esc(u) + '" alt=""></div>'; });
                return '<div class="em-rubrique__slider">' + s + '</div>';
            }

            var v = item.value;
            if (v === '' || v == null) { return ''; }

            switch (item.type) {
                case 'url':
                case 'email':
                    return '<a class="em-rubrique__link" href="#" onclick="return false;">' + esc(item.label || v) + '</a>';
                default:
                    var ts = textStyleCss(item.style);
                    return '<p class="em-rubrique__field"' + (ts ? ' style="' + ts + '"' : '') + '>' + esc(v) + '</p>';
            }
        }

        // Carte « Bloc Plateforme » (rendu identique à la section Stream du site).
        function platformCardHtml(item) {
            var pv = {}; try { pv = JSON.parse(item.value || '{}'); } catch (e) { pv = {}; }
            var top = pv.label || '';
            if (!pv.platform && !top) { return '<span class="em-rubrique__field">[' + esc('plateforme') + ']</span>'; }
            var pc = color(item.color);
            var iconHtml = item.icon ? '<span class="em-rubrique__platform-card-icon"' + (pc ? ' style="color:' + pc + '"' : '') + '><i class="fa-brands ' + esc(item.icon) + '"></i></span>' : '';
            var topLabel = top ? '<span class="em-rubrique__platform-card-label">' + esc(top) + '</span>' : '';
            return '<a class="em-rubrique__platform-card" href="#" onclick="return false;"><span class="em-rubrique__platform-card-body">' + topLabel +
                '<span class="em-rubrique__platform-card-title">' + iconHtml + '<span>' + esc(item.name) + '</span></span></span>' +
                '<span class="em-rubrique__platform-card-arrow" aria-hidden="true">\u2192</span></a>';
        }

        function render(target, layout, items, colors) {
            if (!target) { return; }

            var rowsLayout = (layout && layout.rows) || [];
            var maxRow = rowsLayout.length;
            items.forEach(function (it) { if (it.row > maxRow) { maxRow = it.row; } });
            if (maxRow < 1) { maxRow = 1; }

            var rows = '';
            for (var r = 1; r <= maxRow; r++) {
                var rl = rowsLayout[r - 1] || {};
                var columns = Math.min(4, Math.max(1, parseInt(rl.columns || 1, 10)));
                var align = rl.align || {};
                rows += '<div class="em-rubrique__row" style="grid-template-columns:repeat(' + columns + ',minmax(0,1fr))">';
                for (var c = 1; c <= columns; c++) {
                    rows += '<div class="em-rubrique__col em-rubrique__col--' + (align[c] || 'left') + '">';
                    items.forEach(function (it) {
                        if (it.hidden) { return; }
                        if (it.row === r && Math.min(columns, Math.max(1, it.col)) === c) { rows += fieldHtml(it); }
                    });
                    rows += '</div>';
                }
                rows += '</div>';
            }

            var style = '';
            var bg = color(colors && colors.bg);
            var text = color(colors && colors.text);
            var link = color(colors && colors.link);
            var linkHover = color(colors && colors.linkHover);
            var linkVisited = color(colors && colors.linkVisited);
            if (bg) { style += '--em-rubrique-bg:' + bg + ';'; }
            if (text) { style += '--em-rubrique-text:' + text + ';'; }
            if (link) { style += '--em-rubrique-link:' + link + ';'; }
            if (linkHover) { style += '--em-rubrique-link-hover:' + linkHover + ';'; }
            if (linkVisited) { style += '--em-rubrique-link-visited:' + linkVisited + ';'; }
            style += '--em-rubrique-underline:' + (colors && colors.underline ? 'underline' : 'none') + ';';
            if (colors && colors.font) { style += '--em-rubrique-font:' + colors.font + ';'; }
            var pad = { padTop: 'pt', padBottom: 'pb', padLeft: 'pl', padRight: 'pr' };
            Object.keys(pad).forEach(function (k) {
                if (colors && colors[k] !== undefined && colors[k] !== '') { style += '--em-rubrique-' + pad[k] + ':' + (parseInt(colors[k], 10) || 0) + 'px;'; }
            });

            var footer = document.createElement('footer');
            footer.className = 'em-rubrique em-rubrique--footer em-rubrique--preview';
            footer.setAttribute('style', style);
            // :visited ignore var() → couleur littérale scopée au footer d'aperçu.
            // Les liens média (icônes/images) sont exclus : ils gardent la couleur de lien.
            var vis = linkVisited || link;
            footer.innerHTML = (vis ? '<style>.em-rubrique--preview .em-rubrique__link:not(.em-rubrique__link--media):visited{color:' + vis + ' !important;}.em-rubrique--preview .em-rubrique__link--media:visited{color:' + (link || 'inherit') + ' !important;}</style>' : '') + rows;

            target.innerHTML = '';
            target.appendChild(footer);
        }

        // Ouvre/ferme la pastille d'aperçu (sticky). Toute la barre est cliquable.
        function toggle(btn) {
            var wrap = btn.closest('.em-v4-preview');
            var body = wrap ? wrap.querySelector('.em-v4-livepreview') : null;
            if (!body) { return; }
            var wasOpen = !body.hidden;
            body.hidden = wasOpen;
            btn.setAttribute('aria-pressed', wasOpen ? 'false' : 'true');
            var i = btn.querySelector('.dashicons');
            if (i) { i.className = 'dashicons dashicons-' + (wasOpen ? 'visibility' : 'hidden'); }
        }

        // Fenêtre détachée (onglet) — recopie les feuilles de style de la page.
        var winRef = null;

        function popoutStyles() {
            var css = '';
            document.querySelectorAll('link[rel="stylesheet"], style').forEach(function (node) {
                css += node.outerHTML;
            });
            return css;
        }

        function writeWindow(win, previewNode) {
            var inner = previewNode ? previewNode.innerHTML : '';
            win.document.open();
            win.document.write(
                '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">' +
                '<meta name="viewport" content="width=device-width, initial-scale=1">' +
                '<title>' + esc('<?php echo esc_js(__('Aperçu', 'em-wp')); ?>') + '</title>' +
                popoutStyles() +
                '<style>html,body{margin:0;padding:0;background:#f0f0f1;}' +
                '.em-rubrique-popout{padding:24px;}' +
                '.em-rubrique-popout .em-v4-livepreview{display:block!important;border:0;margin:0;}</style>' +
                '</head><body><div class="em-rubrique-popout"><div class="em-v4-livepreview">' + inner + '</div></div></body></html>'
            );
            win.document.close();
        }

        function openWindow(previewNode) {
            winRef = window.open('', 'emWpV4PreviewWin');
            if (!winRef) { return; }
            writeWindow(winRef, previewNode);
            winRef.focus();
        }

        // Rafraîchit la fenêtre détachée si elle est ouverte (sync temps réel).
        function syncWindow(previewNode) {
            if (!winRef || winRef.closed) { return; }
            var stage = winRef.document.querySelector('.em-rubrique-popout .em-v4-livepreview');
            if (stage) { stage.innerHTML = previewNode ? previewNode.innerHTML : ''; }
        }

        return { render: render, toggle: toggle, openWindow: openWindow, syncWindow: syncWindow };
    })();
    </script>
    <?php
}
