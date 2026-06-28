<?php
/**
 * Helpers JS des champs média du builder (V4), inclus DANS l'IIFE EmWpV4Chip :
 * réseaux, sélection vidéo/son (médiathèque) et slider d'images.
 *
 * @package em-wp
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
        return '<select class="em-v4-chip__platform">' + opts + '</select>';
    }

    function avHtml(mtype, btn) {
        return '<span class="em-v4-chip__media em-v4-chip__media--av" data-url="" data-mtype="' + esc(mtype) + '">' +
            '<button type="button" class="button button-small em-v4-chip__pick">' + esc(btn) + '</button>' +
            '<span class="em-v4-chip__medianame"></span>' +
            '<input type="hidden" class="em-v4-chip__value"></span>';
    }

    function slidesOptsHtml() {
        return '<span class="em-v4-slides__opts">' +
            '<label class="em-v4-slides__opt"><span>' + esc(TXT.slTitle) + '</span><input type="text" class="em-v4-slides__title" placeholder="' + esc(TXT.slTitlePh) + '"></label>' +
            '<label class="em-v4-slides__opt em-v4-slides__opt--check"><input type="checkbox" class="em-v4-slides__title-hidden"> ' + esc(TXT.slTitleHide) + '</label>' +
            '<label class="em-v4-slides__opt"><span>' + esc(TXT.slFrame) + '</span><input type="color" class="em-v4-slides__frame" value="#12338f"></label>' +
            '<label class="em-v4-slides__opt"><span>' + esc(TXT.slBand) + '</span><input type="color" class="em-v4-slides__footerbg" value="#f2ebd1"></label>' +
            '<label class="em-v4-slides__opt"><span>' + esc(TXT.slBandText) + '</span><input type="color" class="em-v4-slides__footertext" value="#100421"></label>' +
            '</span>';
    }

    // Miroir JS de em_wp_v4_slide_row_html (PHP) — nouvelle ligne vierge (image).
    function slideRowHtml() {
        return '<span class="em-v4-slide" data-type="image">' +
            '<span class="em-v4-slide__move"><button type="button" class="em-v4-slide__up" title="' + esc(TXT.slUp) + '">&#9650;</button><button type="button" class="em-v4-slide__down" title="' + esc(TXT.slDown) + '">&#9660;</button></span>' +
            '<select class="em-v4-slide__type"><option value="image">' + esc(TXT.slImage) + '</option><option value="tiktok">TikTok</option><option value="video">' + esc(TXT.slVideo) + '</option></select>' +
            '<span class="em-v4-slide__media em-v4-slide__media--image"><img class="em-v4-slide__thumb" alt="" hidden><button type="button" class="button button-small em-v4-slide__pick" data-target="image">' + esc(TXT.slImage) + '</button><input type="hidden" class="em-v4-slide__image"></span>' +
            '<input type="url" class="em-v4-slide__videourl" placeholder="' + esc(TXT.slYoutube) + '">' +
            '<input type="url" class="em-v4-slide__tiktokurl" placeholder="' + esc(TXT.slTiktok) + '">' +
            '<span class="em-v4-slide__media em-v4-slide__media--ttvid"><button type="button" class="button button-small em-v4-slide__pick" data-target="ttvid">' + esc(TXT.slVideoFile) + '</button><span class="em-v4-slide__medianame"></span><input type="hidden" class="em-v4-slide__tiktokvideo"></span>' +
            '<input type="text" class="em-v4-slide__name" placeholder="' + esc(TXT.slName) + '">' +
            '<input type="number" class="em-v4-slide__duration" min="1" value="5" title="' + esc(TXT.slDuration) + '">' +
            '<button type="button" class="em-v4-slide__eye" data-hidden="0"><span class="dashicons dashicons-visibility" aria-hidden="true"></span></button>' +
            '<button type="button" class="em-v4-slide__del" title="' + esc(TXT.remove) + '">&times;</button>' +
            '</span>';
    }

    function sliderHtml() {
        return '<span class="em-v4-slides">' + slidesOptsHtml() +
            '<span class="em-v4-slides__list"></span>' +
            '<button type="button" class="button button-small em-v4-slides__add">' + esc(TXT.slAdd) + '</button>' +
            '<input type="hidden" class="em-v4-chip__value"></span>';
    }

    function openAv(chip, type, update) {
        var lib = type === 'video_file' ? 'video' : 'audio';
        var frame = window.wp.media({ title: lib === 'video' ? TXT.pickVideo : TXT.pickAudio, multiple: false, library: { type: lib } });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var media = chip.querySelector('.em-v4-chip__media');
            var hidden = chip.querySelector('.em-v4-chip__value');
            var name = chip.querySelector('.em-v4-chip__medianame');
            hidden.value = att.id;
            if (media) { media.setAttribute('data-url', att.url || ''); }
            if (name) { name.textContent = att.filename || att.title || ''; }
            update();
        });
        frame.open();
    }

    // Sérialise un champ média lors de la collecte (script.php readChip).
    function readMedia(chip, type, item) {
        if (type === 'video_file' || type === 'audio_file') { var av = chip.querySelector('.em-v4-chip__media'); item.url = av ? av.getAttribute('data-url') : ''; }
        else if (type === 'audio_url') { item.url = item.value; }
        else if (type === 'video_url') {
            var vu = chip.querySelector('.em-v4-chip__vurl'), vt = chip.querySelector('.em-v4-chip__vthumb'), vtid = chip.querySelector('.em-v4-chip__thumbid'), vck = chip.querySelector('.em-v4-chip__clickable');
            item.url = vu ? vu.value : ''; item.thumbUrl = vt ? vt.getAttribute('data-url') : ''; item.clickable = vck ? !!vck.checked : false;
            item.value = JSON.stringify({ url: item.url, thumb: vtid && vtid.value ? parseInt(vtid.value, 10) : 0, clickable: item.clickable ? 1 : 0 });
        }
        // type 'slider' : la valeur (config JSON complète) est déjà lue par readChip
        // via .em-v4-chip__value ; EmWpV4Slides la tient à jour.
    }

    function openThumb(chip, update) {
        var frame = window.wp.media({ title: TXT.pickThumb, multiple: false, library: { type: 'image' } });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var media = chip.querySelector('.em-v4-chip__vthumb');
            var hidden = chip.querySelector('.em-v4-chip__thumbid');
            var name = chip.querySelector('.em-v4-chip__medianame');
            var sizes = att.sizes || {};
            if (hidden) { hidden.value = att.id; }
            if (media) { media.setAttribute('data-url', sizes.medium ? sizes.medium.url : att.url); }
            if (name) { name.textContent = att.filename || att.title || ''; }
            update();
        });
        frame.open();
    }

    function sliderIds(chip) {
        var v = chip.querySelector('.em-v4-chip__slider .em-v4-chip__value');
        try { var a = JSON.parse((v && v.value) || '[]'); return Array.isArray(a) ? a.map(function (x) { return parseInt(x, 10); }) : []; } catch (e) { return []; }
    }

    function slideHtml(id, thumb) {
        return '<span class="em-v4-chip__slide" data-id="' + id + '"><img src="' + esc(thumb) + '" alt="">' +
            '<button type="button" class="em-v4-chip__slide-del" title="' + esc(TXT.slideDel) + '">&times;</button></span>';
    }

    function openSlider(chip, update) {
        var frame = window.wp.media({ title: TXT.addImages, multiple: true, library: { type: 'image' } });
        frame.on('select', function () {
            var slides = chip.querySelector('.em-v4-chip__slides');
            var hidden = chip.querySelector('.em-v4-chip__slider .em-v4-chip__value');
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
        var chip = btn.closest('.em-v4-chip');
        var slide = btn.closest('.em-v4-chip__slide');
        if (slide) { slide.remove(); }
        var ids = [];
        chip.querySelectorAll('.em-v4-chip__slide').forEach(function (s) { ids.push(parseInt(s.getAttribute('data-id'), 10)); });
        var hidden = chip.querySelector('.em-v4-chip__slider .em-v4-chip__value');
        if (hidden) { hidden.value = ids.length ? JSON.stringify(ids) : ''; }
        update();
    }
