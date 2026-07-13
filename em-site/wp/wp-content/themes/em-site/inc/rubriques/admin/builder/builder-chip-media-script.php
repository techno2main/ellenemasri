<?php
/**
 * Helpers JS des champs média du builder (EM-SITE), inclus DANS l'IIFE EmSiteChip :
 * réseaux, sélection vidéo/son (médiathèque) et slider d'images.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
    function networkSelectHtml() {
        var opts = '<option value="">' + esc(TXT.pick) + '</option>';
        Object.keys(NETWORKS).forEach(function (k) {
            var p = NETWORKS[k];
            opts += '<option value="' + esc(k) + '" data-icon="' + esc(p.icon) + '" data-color="' + esc(p.color || '') + '" data-label="' + esc(p.label) + '">' + esc(p.label) + '</option>';
        });
        return '<select class="em-site-chip__platform">' + opts + '</select>';
    }

    function avHtml(mtype, btn) {
        return '<span class="em-site-chip__media em-site-chip__media--av" data-url="" data-mtype="' + esc(mtype) + '">' +
            '<button type="button" class="button button-small em-site-chip__pick">' + esc(btn) + '</button>' +
            '<span class="em-site-chip__medianame"></span>' +
            '<input type="hidden" class="em-site-chip__value"></span>';
    }

    function slidesOptsHtml() {
        var borderId = colorId('em-site-sls-bdr-');
        var shadowId = colorId('em-site-sls-sh-');
        var footerBgId = colorId('em-site-sls-b-');
        var footerTextId = colorId('em-site-sls-t-');
        return '<span class="em-site-slides__section-title">' + esc(TXT.slStyle || 'Style du Slider') + '</span>' +
            '<span class="em-site-slides__opts em-site-slides__opts--row1">' +
            '<span class="em-site-slides__titlegroup">' +
            '<label class="em-site-slides__opt em-site-slides__opt--title"><span>' + esc(TXT.slTitleLabel || 'Titre') + '</span><input type="text" class="em-site-slides__title" placeholder="' + esc(TXT.slTitlePh) + '"></label>' +
            '<label class="em-site-slides__opt em-site-slides__opt--check"><input type="checkbox" class="em-site-slides__title-hidden"> ' + esc(TXT.slTitleHide) + '</label>' +
            '</span>' +
            '<input type="hidden" class="em-site-slides__frame" value="#12338f">' +
            colorField(footerBgId, 'em-site-slides__footerbg', TXT.slBand) +
            colorField(footerTextId, 'em-site-slides__footertext em-site-slides__footertext-text', TXT.slBandText, 'text', footerBgId) +
            colorField(borderId, 'em-site-slides__border-color', TXT.slBorder || 'Bordure') +
            colorField(shadowId, 'em-site-slides__shadow-color', TXT.slShadow || 'Ombre') +
            scotchsControlHtml({
                hiddenClass: 'em-site-slides__tapes-hidden',
                hiddenLabel: TXT.slTapeHide,
                hiddenWrapClass: 'em-site-slides__opt em-site-slides__opt--check em-site-slides__opt--check-tapes',
                colorClass: 'em-site-slides__tapes-color',
                colorLabel: TXT.slTape,
                colorWrapClass: 'em-site-slides__colorfield',
                colorIdPrefix: 'em-site-sls-'
            }) +
            '</span>';
    }

    // Miroir JS de em_site_slide_row_html (PHP) — nouvelle ligne vierge (image).
    function slideRowHtml() {
        return '<span class="em-site-slide" data-type="image">' +
            '<span class="em-site-slide__move"><button type="button" class="em-site-slide__up" title="' + esc(TXT.slUp) + '">&#9650;</button><button type="button" class="em-site-slide__down" title="' + esc(TXT.slDown) + '">&#9660;</button></span>' +
            '<select class="em-site-slide__type"><option value="image">' + esc(TXT.slImage) + '</option><option value="tiktok">TikTok</option><option value="video">' + esc(TXT.slVideo) + '</option></select>' +
            '<span class="em-site-slide__media em-site-slide__media--image"><img class="em-site-slide__thumb" alt="" hidden><button type="button" class="button button-small em-site-slide__pick" data-target="image">' + esc(TXT.slImage) + '</button><input type="hidden" class="em-site-slide__image"></span>' +
            '<input type="url" class="em-site-slide__videourl" placeholder="' + esc(TXT.slYoutube) + '">' +
            '<input type="url" class="em-site-slide__tiktokurl" placeholder="' + esc(TXT.slTiktok) + '">' +
            '<span class="em-site-slide__media em-site-slide__media--ttvid"><button type="button" class="button button-small em-site-slide__pick" data-target="ttvid">' + esc(TXT.slVideoFile) + '</button><span class="em-site-slide__medianame"></span><input type="hidden" class="em-site-slide__tiktokvideo"></span>' +
            '<input type="text" class="em-site-slide__name" placeholder="' + esc(TXT.slName) + '">' +
            '<input type="number" class="em-site-slide__duration" min="1" value="5" title="' + esc(TXT.slDuration) + '">' +
            '<button type="button" class="em-site-slide__eye" data-hidden="0"><span class="dashicons dashicons-visibility" aria-hidden="true"></span></button>' +
            '<button type="button" class="em-site-slide__del" title="' + esc(TXT.remove) + '">&times;</button>' +
            '</span>';
    }

    function sliderHtml() {
        return '<span class="em-site-slides">' + slidesOptsHtml() +
            '<span class="em-site-slides__group-label em-site-slides__slides-label">' + esc(TXT.slSlides || 'Slides') + '</span>' +
            '<span class="em-site-slides__list"></span>' +
            '<button type="button" class="button button-small em-site-slides__add">' + esc(TXT.slAdd) + '</button>' +
            '<input type="hidden" class="em-site-chip__value"></span>';
    }

    function openAv(chip, type, update) {
        var lib = type === 'video_file' ? 'video' : 'audio';
        var frame = window.wp.media({ title: lib === 'video' ? TXT.pickVideo : TXT.pickAudio, multiple: false, library: { type: lib } });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var media = chip.querySelector('.em-site-chip__media');
            var hidden = chip.querySelector('.em-site-chip__value');
            var name = chip.querySelector('.em-site-chip__medianame');
            hidden.value = att.id;
            if (media) { media.setAttribute('data-url', att.url || ''); }
            if (name) { name.textContent = att.filename || att.title || ''; }
            update();
        });
        frame.open();
    }

    // Sérialise un champ média lors de la collecte (script.php readChip).
    function readMedia(chip, type, item) {
        if (type === 'video_file' || type === 'audio_file') { var av = chip.querySelector('.em-site-chip__media'); item.url = av ? av.getAttribute('data-url') : ''; }
        else if (type === 'audio_url') { item.url = item.value; }
        else if (type === 'video_url') {
            var vu = chip.querySelector('.em-site-chip__vurl'), vt = chip.querySelector('.em-site-chip__vthumb'), vtid = chip.querySelector('.em-site-chip__thumbid'), vck = chip.querySelector('.em-site-chip__clickable'), vth = chip.querySelector('.em-site-chip__vtapes-hidden'), vtc = chip.querySelector('.em-site-chip__vtapes-color');
            item.url = vu ? vu.value : ''; item.thumbUrl = vt ? vt.getAttribute('data-url') : ''; item.clickable = vck ? !!vck.checked : false;
            item.tapesHidden = vth ? !!vth.checked : false;
            item.tapesColor = vtc ? vtc.value : '';
            item.value = JSON.stringify({
                url: item.url,
                thumb: vtid && vtid.value ? parseInt(vtid.value, 10) : 0,
                clickable: item.clickable ? 1 : 0,
                tapes_hidden: item.tapesHidden ? 1 : 0,
                tapes_color: item.tapesColor
            });
        }
        // type 'slider' : la valeur (config JSON complète) est déjà lue par readChip
        // via .em-site-chip__value ; EmSiteSlides la tient à jour.
    }

    function openThumb(chip, update) {
        var frame = window.wp.media({ title: TXT.pickThumb, multiple: false, library: { type: 'image' } });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var media = chip.querySelector('.em-site-chip__vthumb');
            var hidden = chip.querySelector('.em-site-chip__thumbid');
            var name = chip.querySelector('.em-site-chip__medianame');
            var sizes = att.sizes || {};
            if (hidden) { hidden.value = att.id; }
            if (media) { media.setAttribute('data-url', sizes.medium ? sizes.medium.url : att.url); }
            if (name) { name.textContent = att.filename || att.title || ''; }
            update();
        });
        frame.open();
    }

    function sliderIds(chip) {
        var v = chip.querySelector('.em-site-chip__slider .em-site-chip__value');
        try { var a = JSON.parse((v && v.value) || '[]'); return Array.isArray(a) ? a.map(function (x) { return parseInt(x, 10); }) : []; } catch (e) { return []; }
    }

    function slideHtml(id, thumb) {
        return '<span class="em-site-chip__slide" data-id="' + id + '"><img src="' + esc(thumb) + '" alt="">' +
            '<button type="button" class="em-site-chip__slide-del" title="' + esc(TXT.slideDel) + '">&times;</button></span>';
    }

    function openSlider(chip, update) {
        var frame = window.wp.media({ title: TXT.addImages, multiple: true, library: { type: 'image' } });
        frame.on('select', function () {
            var slides = chip.querySelector('.em-site-chip__slides');
            var hidden = chip.querySelector('.em-site-chip__slider .em-site-chip__value');
            var ids = sliderIds(chip);
            frame.state().get('selection').each(function (att) {
                var a = att.toJSON();
                if (ids.indexOf(a.id) !== -1) { return; }
                ids.push(a.id);
                var thumb = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
                if (slides) { slides.insertAdjacentHTML('beforeend', slideHtml(a.id, thumb)); }
            });
            if (hidden) { hidden.value = ids.length ? JSON.stringify(ids) : ''; }
            update();
        });
        frame.open();
    }

    // Retire une image du slider et recalcule la liste d'IDs.
    function removeSlide(btn, update) {
        var chip = btn.closest('.em-site-chip');
        var slide = btn.closest('.em-site-chip__slide');
        if (slide) { slide.remove(); }
        var ids = [];
        chip.querySelectorAll('.em-site-chip__slide').forEach(function (s) { ids.push(parseInt(s.getAttribute('data-id'), 10)); });
        var hidden = chip.querySelector('.em-site-chip__slider .em-site-chip__value');
        if (hidden) { hidden.value = ids.length ? JSON.stringify(ids) : ''; }
        update();
    }
