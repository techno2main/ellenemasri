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
        var MASKED = '<?php echo esc_js(__('Masqué', 'em-wp')); ?>';

        function esc(value) {
            var d = document.createElement('div');
            d.textContent = value == null ? '' : String(value);
            return d.innerHTML;
        }

        // Texte échappé, enveloppé d'un lien factice (preview) si un lien existe.
        function textLink(text, link) {
            var html = esc(text);
            return link ? '<a class="em-rubrique__link" href="#" onclick="return false;">' + html + '</a>' : html;
        }

        // Rendu preview du texte enrichi: HTML simple autorisé, fallback nl2br.
        function richTextHtml(raw) {
            var str = String(raw || '');
            if (!str) { return ''; }
            if (!/[<][^>]+[>]/.test(str)) {
                return esc(str).replace(/\n/g, '<br>');
            }
            var tpl = document.createElement('template');
            tpl.innerHTML = str;
            tpl.content.querySelectorAll('script,style').forEach(function (node) { node.remove(); });
            tpl.content.querySelectorAll('*').forEach(function (el) {
                Array.prototype.slice.call(el.attributes).forEach(function (attr) {
                    if (/^on/i.test(attr.name)) {
                        el.removeAttribute(attr.name);
                    }
                });
            });
            return tpl.innerHTML;
        }

        function color(value) {
            return /^#[0-9a-fA-F]{3,8}$/.test(value || '') ? value : '';
        }

        // Position d'une image de fond -> { size, repeat, position } (cf. PHP).
        function bgPosCss(pos) {
            switch (pos) {
                case 'contain': return { size: 'contain', repeat: 'no-repeat', position: 'center' };
                case 'center': return { size: 'auto', repeat: 'no-repeat', position: 'center' };
                case 'repeat': return { size: 'auto', repeat: 'repeat', position: 'top left' };
                case 'repeat-x': return { size: 'auto', repeat: 'repeat-x', position: 'top center' };
                case 'repeat-y': return { size: 'auto', repeat: 'repeat-y', position: 'center left' };
                default: return { size: 'cover', repeat: 'no-repeat', position: 'center' };
            }
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
            if (hasLink) { img = '<a class="em-rubrique__link em-rubrique__link--media" href="#" onclick="return false;">' + img + '</a>'; }
            if (iv.tape) {
                img = '<span class="em-rubrique__imgwrap"><span class="em-rubrique__tape em-rubrique__tape--left" aria-hidden="true"></span>' + img + '</span>';
            }
            return img;
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

        // Aperçu fidèle du SLIDER mayami : reprend EXACTEMENT le markup du
        // template-part front (cadre, scotch, flèches, bandeau titre, pastilles,
        // bouton son) afin que la CSS front lui donne le look du site.
        function sliderMayamiHtml(cfg) {
            var slides = (cfg.slides || []).filter(function (sl) { return sl && !sl.hidden; });
            var vars = '';
            if (cfg.frame_bg) { vars += '--em-slider-frame-bg:' + esc(cfg.frame_bg) + ';'; }
            if (cfg.border_color) { vars += '--em-slider-border-color:' + esc(cfg.border_color) + ';'; }
            if (cfg.shadow_color) { vars += '--em-slider-shadow-color:' + esc(cfg.shadow_color) + ';'; }
            if (cfg.footer_bg) { vars += '--em-slider-footer-bg:' + esc(cfg.footer_bg) + ';'; }
            if (cfg.footer_text) { vars += '--em-slider-footer-text:' + esc(cfg.footer_text) + ';'; }
            if (cfg.tapes_color) { vars += '--em-slider-tape-color:' + esc(cfg.tapes_color) + ';'; }
            var figs = '';
            var hasTikTokVideo = false;
            if (slides.length) {
                slides.forEach(function (sl, i) {
                    var inner;
                    if (sl.type === 'video') {
                        var info = videoProvider(sl.video_url || '');
                        inner = (info.p === 'youtube' && info.id)
                            ? '<iframe src="https://www.youtube.com/embed/' + esc(info.id) + '?enablejsapi=1&rel=0&modestbranding=1&playsinline=1&mute=1&autoplay=1" title="" loading="lazy" allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'
                            : '<span class="em-slider__ph">YouTube</span>';
                    } else if (sl.type === 'tiktok') {
                        if (sl.tiktok_video_url) {
                            hasTikTokVideo = true;
                            inner = '<div class="em-slider__video-wrap"><video class="em-slider__tiktok-video" src="' + esc(sl.tiktok_video_url) + '"' + (sl.image ? ' poster="' + esc(sl.image) + '"' : '') + ' playsinline preload="auto" muted></video></div>';
                        } else {
                            inner = sl.image ? '<img src="' + esc(sl.image) + '" alt="">' : '<span class="em-slider__ph">TikTok</span>';
                        }
                    } else {
                        inner = sl.image ? '<img src="' + esc(sl.image) + '" alt="">' : '<span class="em-slider__ph"></span>';
                    }
                    var delayMs = (parseInt(sl.duration, 10) || 5) * 1000;
                    figs += '<figure class="em-slider__slide' + (i === 0 ? ' is-active' : '') + '" data-type="' + esc(sl.type || 'image') + '" data-delay="' + delayMs + '">' + inner + '</figure>';
                });
            } else {
                figs = '<div class="em-slider__slide is-active em-slider__slide--empty"></div>';
            }
            var nav = slides.length > 1
                ? '<button class="em-slider__nav em-slider__nav--prev" type="button">\u276E</button><button class="em-slider__nav em-slider__nav--next" type="button">\u276F</button>'
                : '';
            var audio = hasTikTokVideo
                ? '<button type="button" class="em-slider__audio-btn is-muted" aria-pressed="false">' +
                    '<span class="em-slider__audio-icon em-slider__audio-icon-muted"><svg viewBox="0 0 24 24" focusable="false"><path d="M5 9h4l5-4v14l-5-4H5z" fill="currentColor"></path><path d="M17 10l4 4m0-4l-4 4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path></svg></span>' +
                    '<span class="em-slider__audio-icon em-slider__audio-icon-live"><svg viewBox="0 0 24 24" focusable="false"><path d="M5 9h4l5-4v14l-5-4H5z" fill="currentColor"></path><path d="M17 9a5 5 0 0 1 0 6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path><path d="M19.5 6.5a8.5 8.5 0 0 1 0 11" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path></svg></span>' +
                    '</button>'
                : '';
            var titleHtml = (!cfg.title_hidden && cfg.title) ? '<span class="em-slider__title">' + esc(cfg.title) + '</span>' : '';
            var dots = '';
            if (slides.length > 1) {
                var d = '';
                slides.forEach(function (sl, i) { d += '<button class="em-slider__dot' + (i === 0 ? ' is-active' : '') + '" type="button" data-slide-to="' + i + '"></button>'; });
                dots = '<div class="em-slider__dots">' + d + '</div>';
            }
            return '<div class="em-slider em-slider--mayami"' + (vars ? ' style="' + vars + '"' : '') + '>' +
                '<div class="em-slider__shell">' +
                (cfg.tapes_hidden ? '' : '<span class="em-slider__tape em-slider__tape--left" aria-hidden="true"></span><span class="em-slider__tape em-slider__tape--right" aria-hidden="true"></span>') +
                '<div class="em-slider__frame">' +
                '<div class="em-slider__media">' + figs + nav + audio + '</div>' +
                '<div class="em-slider__footer">' + titleHtml + '</div>' +
                '</div></div>' + dots + '</div>';
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
                var tiTextHtml = tiText ? '<p class="em-rubrique__field"' + (tiStyle ? ' style="' + tiStyle + '"' : '') + '>' + textLink(tiText, ti.link) + '</p>' : '';
                var tiImg = ti.image || {};
                var tiImgHtml = imageMarkup(item.imageUrl, tiImg, item.label, !!tiImg.link);
                if (!tiTextHtml && !tiImgHtml) { return '<span class="em-rubrique__field">[' + esc('texte + image') + ']</span>'; }
                return '<div class="em-rubrique__textimg">' + tiTextHtml + tiImgHtml + '</div>';
            }
            if (item.type === 'text_text') {
                var tt = {}; try { tt = JSON.parse(item.value || '{}'); } catch (e) { tt = {}; }
                var s1 = textStyleCss(tt.style), s2 = textStyleCss(tt.style2);
                var t1 = tt.text ? '<p class="em-rubrique__field"' + (s1 ? ' style="' + s1 + '"' : '') + '>' + textLink(tt.text, tt.link) + '</p>' : '';
                var t2 = tt.text2 ? '<p class="em-rubrique__field"' + (s2 ? ' style="' + s2 + '"' : '') + '>' + textLink(tt.text2, tt.link2) + '</p>' : '';
                if (!t1 && !t2) { return '<span class="em-rubrique__field">[' + esc('texte + texte') + ']</span>'; }
                return '<div class="em-rubrique__texttext">' + t1 + t2 + '</div>';
            }
            if (item.type === 'icon') {
                if (!item.icon) { return '<span class="em-rubrique__field">[' + esc(item.label || 'icon') + ']</span>'; }
                var ic = '<i class="em-rubrique__icon fa-brands ' + esc(item.icon) + '" title="' + esc(item.label) + '"></i>';
                return item.link ? '<a class="em-rubrique__link em-rubrique__link--media" href="#" onclick="return false;">' + ic + '</a>' : ic;
            }
            if (item.type === 'platform_block') { return platformCardHtml(item); }
            if (item.type === 'network_block') { return networkCardHtml(item); }
            if (item.type === 'button') {
                var btd = {}; try { btd = JSON.parse(item.value || '{}'); } catch (e) { btd = {}; }
                var blabel = item.label || '';
                if (!blabel) { return '<span class="em-rubrique__field">[' + esc('bouton') + ']</span>'; }
                var bst = '';
                var bbg = color(btd.bg), btx = color(btd.text);
                if (bbg) { bst += 'background:' + bbg + ';border-color:' + bbg + ';'; }
                if (btx) { bst += 'color:' + btx + ';'; }
                var bml = parseInt(btd.ml, 10) || 0, bmr = parseInt(btd.mr, 10) || 0;
                if (bml) { bst += 'margin-left:' + bml + 'px;'; }
                if (bmr) { bst += 'margin-right:' + bmr + 'px;'; }
                var btShp = (['pill', 'square', 'triangle'].indexOf(btd.shape) !== -1) ? btd.shape : 'pill';
                var btAnm = (['wiggle', 'pulse', 'bounce', 'none'].indexOf(btd.anim) !== -1) ? btd.anim : 'none';
                var btRad = parseInt(btd.radius, 10) || 0;
                var btCls = 'em-rubrique__button em-rubrique__button--shape-' + btShp;
                if (btAnm !== 'none') { btCls += ' em-rubrique__button--anim-' + btAnm; }
                if (btShp === 'square') { bst += '--em-rubrique-button-radius:' + btRad + 'px;'; }
                return '<a class="' + btCls + '" href="#" onclick="return false;"' + (bst ? ' style="' + bst + '"' : '') + '>' + esc(blabel) + '</a>';
            }
            if (item.type === 'animated_badge') {
                var bd = {}; try { bd = JSON.parse(item.value || '{}'); } catch (e) { bd = {}; }
                if (!bd.text) { return '<span class="em-rubrique__field">[' + esc('badge') + ']</span>'; }
                var bShape = (['pill', 'square', 'triangle'].indexOf(bd.shape) !== -1) ? bd.shape : 'pill';
                var bAnim = (['wiggle', 'pulse', 'bounce', 'none'].indexOf(bd.anim) !== -1) ? bd.anim : 'wiggle';
                var bRad = parseInt(bd.radius, 10) || 0;
                var bCls = 'em-rubrique__badge em-rubrique__badge--shape-' + bShape;
                if (bAnim !== 'none') { bCls += ' em-rubrique__badge--anim-' + bAnim; }
                var bast = '';
                var babg = color(bd.bg), baink = color(bd.ink);
                if (babg) { bast += '--em-rubrique-badge-bg:' + babg + ';'; }
                if (baink) { bast += '--em-rubrique-badge-ink:' + baink + ';'; }
                if (bShape === 'square') { bast += '--em-rubrique-badge-radius:' + bRad + 'px;'; }
                return '<span class="' + bCls + '"' + (bast ? ' style="' + bast + '"' : '') + '><span class="em-rubrique__badge-dot" aria-hidden="true"></span>' + esc(bd.text) + '</span>';
            }
            if (item.type === 'video_url') {
                var vd = {}; try { vd = JSON.parse(item.value || '{}'); } catch (e) { vd = {}; }
                var vurl = (typeof vd.url === 'string') ? vd.url : (item.url || '');
                var clickable = item.clickable !== undefined ? item.clickable : !!vd.clickable;
                var custom = item.thumbUrl || '';
                var poster = clickable ? (custom || autoThumb(vurl)) : custom;
                if (!poster) { return videoEmbed(vurl); }
                var facade = '<span class="em-rubrique__video-facade"><img class="em-rubrique__video-poster" src="' + esc(poster) + '" alt=""><span class="em-rubrique__video-play" aria-hidden="true"></span></span>';
                var frame = (clickable && vurl)
                    ? '<a class="em-rubrique__videourl em-rubrique__link--media" href="#" onclick="return false;">' + facade + '</a>'
                    : '<span class="em-rubrique__videourl">' + facade + '</span>';
                return '<span class="em-rubrique__videowrap">'
                    + '<span class="em-rubrique__videotape em-rubrique__videotape--left" aria-hidden="true"></span>'
                    + '<span class="em-rubrique__videotape em-rubrique__videotape--right" aria-hidden="true"></span>'
                    + frame + '</span>';
            }
            if (item.type === 'video_file') { return item.url ? '<video class="em-rubrique__video" controls preload="metadata" src="' + esc(item.url) + '"></video>' : '<span class="em-rubrique__field">[' + esc('vidéo') + ']</span>'; }
            if (item.type === 'audio_file') { return item.url ? '<audio class="em-rubrique__audio" controls preload="none" src="' + esc(item.url) + '"></audio>' : '<span class="em-rubrique__field">[' + esc('son') + ']</span>'; }
            if (item.type === 'audio_url') { return item.value ? '<audio class="em-rubrique__audio" controls preload="none" src="' + esc(item.value) + '"></audio>' : ''; }
            if (item.type === 'slider') {
                var cfg = {};
                try { cfg = JSON.parse(item.value || '{}') || {}; } catch (e) { cfg = {}; }
                return sliderMayamiHtml(cfg);
            }

            if (item.type === 'textarea') {
                var tv = item.value, tlnk = '';
                try {
                    var tp = JSON.parse(item.value || '{}');
                    if (tp && typeof tp === 'object' && 'text' in tp) {
                        tv = tp.text || '';
                        tlnk = tp.link || '';
                    }
                } catch (e) {}
                if (!tv) { return ''; }
                var rich = richTextHtml(tv);
                if (!rich) { return ''; }
                var richBody = '<div class="em-rubrique__field em-rubrique__field--rich">' + rich + '</div>';
                if (tlnk && !/[<][^>]+[>]/.test(String(tv || ''))) {
                    richBody = '<div class="em-rubrique__field em-rubrique__field--rich">' + textLink(String(tv), tlnk).replace(/\n/g, '<br>') + '</div>';
                }
                return richBody;
            }

            var v = item.value;
            if (v === '' || v == null) { return ''; }

            switch (item.type) {
                case 'url':
                case 'email':
                    return '<a class="em-rubrique__link" href="#" onclick="return false;">' + esc(item.label || v) + '</a>';
                default:
                    var ts = textStyleCss(item.style);
                    var tv = v, tlink = '';
                    try { var pd = JSON.parse(v); if (pd && typeof pd === 'object' && 'text' in pd) { tv = pd.text || ''; tlink = pd.link || ''; } } catch (e) {}
                    if (tv === '') { return ''; }
                    return '<p class="em-rubrique__field"' + (ts ? ' style="' + ts + '"' : '') + '>' + textLink(tv, tlink) + '</p>';
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

        // Carte « Bloc Réseau » (rendu identique à la section Social du site).
        function networkBrand(slug) {
            if (slug === 'tiktok') { return { bg: 'linear-gradient(135deg,#0f0f13 0%,#1a1a22 62%,#22152d 100%)', shadow: '#25f4ee' }; }
            if (slug === 'instagram') { return { bg: '#c13584', shadow: '#833ab4' }; }
            if (slug === 'youtube') { return { bg: '#ff0033', shadow: '#78000d' }; }
            return { bg: '#1a1a22', shadow: 'rgba(16,4,33,.55)' };
        }

        var NET_DEFAULT_ACCOUNT = { tiktok: '@ellenemasri', instagram: '@ellenemasri', youtube: '@ELLENEMASRI' };

        function networkCardHtml(item) {
            var pv = {}; try { pv = JSON.parse(item.value || '{}'); } catch (e) { pv = {}; }
            var badge = pv.label || '';
            if (!pv.platform && !badge) { return '<span class="em-rubrique__field">[' + esc('réseau') + ']</span>'; }
            var slug = (pv.platform && pv.platform.indexOf(':') !== -1) ? pv.platform.split(':')[1] : (pv.platform || '');
            var brand = networkBrand(slug);
            var account = pv.account || NET_DEFAULT_ACCOUNT[slug] || '';
            var badgeHtml = badge ? '<span class="em-rubrique__network-card-badge">' + esc(badge) + '</span>' : '';
            var iconHtml = item.icon ? '<i class="fa-brands ' + esc(item.icon) + '"></i>' : '';
            var nameHtml = item.name ? '<span>' + esc(item.name) + '</span>' : '';
            var accountHtml = account ? '<span class="em-rubrique__network-card-account">' + esc(account) + '</span>' : '';
            var style = 'background:' + brand.bg + ';box-shadow:8px 8px 0 ' + brand.shadow + ';';
            return '<a class="em-rubrique__network-card em-rubrique__network-card--' + esc(slug) + '" href="#" onclick="return false;" style="' + style + '">' +
                badgeHtml + '<span class="em-rubrique__network-card-label">' + iconHtml + nameHtml + '</span>' + accountHtml + '</a>';
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
                        if (it.row !== r || Math.min(columns, Math.max(1, it.col)) !== c) { return; }
                        rows += it.hidden ? ('<span class="em-rubrique__masked" title="' + esc(MASKED) + '"><span class="dashicons dashicons-hidden" aria-hidden="true"></span>' + esc(MASKED) + '</span>') : fieldHtml(it);
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
            if (colors && colors.bgTransparent) { style += '--em-rubrique-bg:transparent;'; }
            if (text) { style += '--em-rubrique-text:' + text + ';'; }
            if (link) { style += '--em-rubrique-link:' + link + ';'; }
            if (linkHover) { style += '--em-rubrique-link-hover:' + linkHover + ';'; }
            if (linkVisited) { style += '--em-rubrique-link-visited:' + linkVisited + ';'; }
            style += '--em-rubrique-underline:' + (colors && colors.underline ? 'underline' : 'none') + ';';
            if (colors && colors.font) { style += '--em-rubrique-font:' + colors.font + ';'; }
            if (colors && colors.bgImage) {
                var bp = bgPosCss(colors.bgPos);
                style += "--em-rubrique-bg-image:url('" + colors.bgImage.replace(/'/g, "%27") + "');";
                style += '--em-rubrique-bg-size:' + bp.size + ';--em-rubrique-bg-repeat:' + bp.repeat + ';--em-rubrique-bg-position:' + bp.position + ';';
                var op = (colors.bgOpacity === undefined || colors.bgOpacity === '') ? 100 : Math.max(0, Math.min(100, parseInt(colors.bgOpacity, 10) || 0));
                style += '--em-rubrique-bg-opacity:' + (op / 100) + ';';
                style += '--em-rubrique-bg-transform:' + (colors.bgMirror ? 'scaleX(-1)' : 'none') + ';';
            }
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
            initSliders(target);
        }

        // Élément de rubrique contenant l'aperçu lié à un bouton (œil/popout).
        function ownerItem(btn) { return btn.closest('.em-v4-item') || btn.closest('.em-v4-builder'); }

        // Ouvre/ferme l'aperçu pleine taille (sticky). Ouvre l'item si activé.
        function toggle(btn) {
            var item = ownerItem(btn);
            var body = item ? item.querySelector('.em-v4-livepreview') : null;
            if (!body) { return; }
            var wasOpen = !body.hidden;
            body.hidden = wasOpen;
            btn.setAttribute('aria-pressed', wasOpen ? 'false' : 'true');
            var i = btn.querySelector('.dashicons');
            if (i) { i.className = 'dashicons dashicons-' + (wasOpen ? 'visibility' : 'hidden'); }
            if (!wasOpen && item && item.tagName === 'DETAILS') { item.open = true; }
        }

        // Fenêtre détachée (onglet) — recopie les feuilles de style de la page.
        var winRef = null;

        function popoutStyles() {
            var css = '';
            document.querySelectorAll('link[rel="stylesheet"], style').forEach(function (node) { css += node.outerHTML; });
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
            if (winRef) { writeWindow(winRef, previewNode); winRef.focus(); }
        }

        // Rafraîchit la fenêtre détachée si elle est ouverte (sync temps réel).
        function syncWindow(previewNode) {
            if (!winRef || winRef.closed) { return; }
            var stage = winRef.document.querySelector('.em-rubrique-popout .em-v4-livepreview');
            if (stage) { stage.innerHTML = previewNode ? previewNode.innerHTML : ''; }
        }

        // Contrôles d'aperçu dans l'en-tête de l'item (hors builder) : délégation globale.
        document.addEventListener('click', function (e) {
            var tg = e.target.closest('.em-v4-preview__toggle'), po = e.target.closest('.em-v4-preview__popout');
            if (tg) { e.preventDefault(); e.stopPropagation(); toggle(tg); }
            else if (po) { e.preventDefault(); e.stopPropagation(); var it = ownerItem(po); openWindow(it ? it.querySelector('.em-v4-livepreview') : null); }
        });

        // Slider d'aperçu : comportement IDENTIQUE au front.
        //  - vidéos (TikTok fichier / YouTube) : lecture AUTO, pas de minuteur,
        //    on passe au suivant à la fin de la vidéo (event 'ended') ;
        //  - photos (et TikTok sans vidéo) : minuteur (durée du slide, def. 5 s).
        // Le minuteur est stocké sur la racine (root._emTimer) et se neutralise
        // tout seul si la racine a été re-rendue (document.body.contains test),
        // donc aucun timer fantôme ne s'accumule entre deux rendus d'aperçu.
        function sliderSlides(root) { return Array.prototype.slice.call(root.querySelectorAll('.em-slider__slide')); }
        function sliderActiveIndex(slides) { for (var i = 0; i < slides.length; i++) { if (slides[i].classList.contains('is-active')) { return i; } } return 0; }
        function sliderVideoOf(slide) { return slide ? slide.querySelector('video.em-slider__tiktok-video') : null; }
        function sliderSlideIsVideo(slide) {
            if (!slide) { return false; }
            var t = slide.getAttribute('data-type');
            if (t === 'video') { return true; }            // YouTube : auto, pas de minuteur
            if (t === 'tiktok') { return !!sliderVideoOf(slide); } // fichier : auto + avance à la fin
            return false;
        }
        function sliderDelay(slide) {
            var d = parseInt((slide && slide.getAttribute('data-delay')) || '', 10);
            return (isNaN(d) || d < 1000) ? 5000 : d;
        }
        function sliderAudioState(root, video) {
            var btn = root.querySelector('.em-slider__audio-btn');
            if (!btn) { return; }
            if (!video) { btn.classList.add('is-hidden'); return; }
            btn.classList.remove('is-hidden');
            btn.classList.toggle('is-muted', video.muted);
            btn.classList.toggle('is-live', !video.muted);
            btn.setAttribute('aria-pressed', video.muted ? 'false' : 'true');
        }
        function sliderSync(root, index) {
            var slides = sliderSlides(root);
            slides.forEach(function (s, i) {
                var v = sliderVideoOf(s);
                if (v) {
                    if (i === index) {
                        try { v.currentTime = 0; } catch (e) {}
                        v.muted = false;
                        var p = v.play();
                        if (p && p.catch) { p.catch(function () { v.muted = true; sliderAudioState(root, v); var r = v.play(); if (r && r.catch) { r.catch(function () {}); } }); }
                    } else {
                        v.pause();
                        try { v.currentTime = 0; } catch (e) {}
                        v.muted = true;
                    }
                }
                var f = s.querySelector('iframe');
                if (f && f.contentWindow) {
                    f.contentWindow.postMessage(JSON.stringify({ event: 'command', func: i === index ? 'playVideo' : 'pauseVideo', args: [] }), '*');
                }
            });
            sliderAudioState(root, sliderVideoOf(slides[index]));
        }
        function sliderSchedule(root) {
            var slides = sliderSlides(root);
            if (slides.length <= 1) { return; }
            var i = sliderActiveIndex(slides);
            if (sliderSlideIsVideo(slides[i])) { return; }
            root._emTimer = setTimeout(function () {
                if (!document.body.contains(root)) { return; }
                sliderGo(root, sliderActiveIndex(sliderSlides(root)) + 1);
            }, sliderDelay(slides[i]));
        }
        function sliderGo(root, index) {
            var slides = sliderSlides(root);
            if (!slides.length) { return; }
            if (root._emTimer) { clearTimeout(root._emTimer); root._emTimer = null; }
            index = (index % slides.length + slides.length) % slides.length;
            slides.forEach(function (s, i) { s.classList.toggle('is-active', i === index); });
            root.querySelectorAll('.em-slider__dot').forEach(function (d, i) { d.classList.toggle('is-active', i === index); });
            sliderSync(root, index);
            sliderSchedule(root);
        }
        function sliderInit(root) {
            var slides = sliderSlides(root);
            if (!slides.length) { return; }
            slides.forEach(function (s) {
                var v = sliderVideoOf(s);
                if (!v || v.dataset.emEnded === '1') { return; }
                v.dataset.emEnded = '1';
                v.addEventListener('ended', function () {
                    var list = sliderSlides(root);
                    if (list.indexOf(s) === sliderActiveIndex(list) && list.length > 1) {
                        sliderGo(root, sliderActiveIndex(list) + 1);
                    }
                });
            });
            sliderSync(root, sliderActiveIndex(slides));
            sliderSchedule(root);
        }
        function initSliders(scope) {
            (scope || document).querySelectorAll('.em-slider--mayami').forEach(sliderInit);
        }

        document.addEventListener('click', function (e) {
            var root = e.target.closest('.em-slider--mayami');
            if (!root) { return; }
            var slides = sliderSlides(root);
            if (e.target.closest('.em-slider__nav--prev')) { e.preventDefault(); sliderGo(root, sliderActiveIndex(slides) - 1); return; }
            if (e.target.closest('.em-slider__nav--next') || e.target.closest('.em-slider__play')) { e.preventDefault(); sliderGo(root, sliderActiveIndex(slides) + 1); return; }
            var dot = e.target.closest('.em-slider__dot');
            if (dot) { e.preventDefault(); var arr = Array.prototype.slice.call(root.querySelectorAll('.em-slider__dot')); sliderGo(root, arr.indexOf(dot)); return; }
            var ab = e.target.closest('.em-slider__audio-btn');
            if (ab) {
                e.preventDefault();
                var v = sliderVideoOf(slides[sliderActiveIndex(slides)]);
                if (!v) { return; }
                v.muted = !v.muted;
                sliderAudioState(root, v);
                if (!v.muted && v.paused) { var p = v.play(); if (p && p.catch) { p.catch(function () {}); } }
            }
        });

        return { render: render, toggle: toggle, openWindow: openWindow, syncWindow: syncWindow, initSliders: initSliders };
    })();
    </script>
    <?php
}
