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
        function esc(value) {
            var d = document.createElement('div');
            d.textContent = value == null ? '' : String(value);
            return d.innerHTML;
        }

        function color(value) {
            return /^#[0-9a-fA-F]{3,8}$/.test(value || '') ? value : '';
        }

        function fieldHtml(item) {
            if (item.type === 'sep_line') { var sc = color(item.value); return '<hr class="em-rubrique__sep"' + (sc ? ' style="border-color:' + sc + '"' : '') + '>'; }
            if (item.type === 'sep_blank') { return '<span class="em-rubrique__spacer" aria-hidden="true"></span>'; }
            if (item.type === 'arrow_up' || item.type === 'arrow_down') {
                var ad = {}; try { ad = JSON.parse(item.value || '{}'); } catch (e) { ad = {}; }
                var ac = color(ad.color);
                var dir = item.type === 'arrow_up' ? 'up' : 'down';
                var glyph = item.type === 'arrow_up' ? '\u2191' : '\u2193';
                var arr = '<span class="em-rubrique__arrow em-rubrique__arrow--' + dir + '" aria-hidden="true"' + (ac ? ' style="color:' + ac + '"' : '') + '>' + glyph + '</span>';
                return ad.link ? '<a class="em-rubrique__link em-rubrique__link--media" href="#" onclick="return false;"' + (ac ? ' style="color:' + ac + '"' : '') + '>' + arr + '</a>' : arr;
            }
            if (item.type === 'image') {
                if (!item.url) { return '<span class="em-rubrique__field">[' + esc(item.label || 'image') + ']</span>'; }
                var iv = {}; try { iv = JSON.parse(item.value || '{}'); } catch (e) { iv = {}; }
                var w = parseInt(iv.w, 10) || 0, h = parseInt(iv.h, 10) || 0, st = '';
                if (w) { st += 'width:' + w + 'px;'; }
                if (h) { st += 'height:' + h + 'px;'; }
                if (w && h) { st += 'object-fit:cover;object-position:' + (iv.fx == null ? 50 : iv.fx) + '% ' + (iv.fy == null ? 50 : iv.fy) + '%;'; }
                var img = '<img class="em-rubrique__image" src="' + esc(item.url) + '"' + (st ? ' style="' + st + '"' : '') + ' alt="' + esc(item.label) + '">';
                return item.link ? '<a class="em-rubrique__link em-rubrique__link--media" href="#" onclick="return false;">' + img + '</a>' : img;
            }
            if (item.type === 'icon') {
                if (!item.icon) { return '<span class="em-rubrique__field">[' + esc(item.label || 'icon') + ']</span>'; }
                var ic = '<i class="em-rubrique__icon fa-brands ' + esc(item.icon) + '" title="' + esc(item.label) + '"></i>';
                return item.link ? '<a class="em-rubrique__link em-rubrique__link--media" href="#" onclick="return false;">' + ic + '</a>' : ic;
            }

            var v = item.value;
            if (v === '' || v == null) { return ''; }

            switch (item.type) {
                case 'url':
                case 'email':
                    return '<a class="em-rubrique__link" href="#" onclick="return false;">' + esc(item.label || v) + '</a>';
                default:
                    return '<p class="em-rubrique__field">' + esc(v) + '</p>';
            }
        }

        function render(target, layout, items, colors) {
            if (!target) { return; }

            var columns = Math.min(4, Math.max(1, parseInt((layout && layout.columns) || 1, 10)));
            var align = (layout && layout.align) || {};
            var maxRow = 1;
            items.forEach(function (it) { if (it.row > maxRow) { maxRow = it.row; } });

            var rows = '';
            for (var r = 1; r <= maxRow; r++) {
                rows += '<div class="em-rubrique__row">';
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
            footer.className = 'em-rubrique em-rubrique--footer em-rubrique--preview em-rubrique--cols-' + columns;
            footer.setAttribute('style', style);
            // :visited ignore var() → couleur littérale scopée au footer d'aperçu.
            // Les liens média (icônes/images) sont exclus : ils gardent la couleur de lien.
            var vis = linkVisited || link;
            footer.innerHTML = (vis ? '<style>.em-rubrique--preview .em-rubrique__link:not(.em-rubrique__link--media):visited{color:' + vis + ' !important;}.em-rubrique--preview .em-rubrique__link--media:visited{color:' + (link || 'inherit') + ' !important;}</style>' : '') + rows;

            target.innerHTML = '';
            target.appendChild(footer);
        }

        return { render: render };
    })();
    </script>
    <?php
}
