<?php
/**
 * Construction des chips côté client (V4) : valeur selon le type
 * (texte, image via médiathèque, icône plateforme), exposée via EmWpV4Chip.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
window.EmWpV4Chip = (function () {
    var PLATFORMS = <?php echo wp_json_encode(em_wp_rubrique_platform_choices()); ?>;
    var DECOR = <?php
        $em_wp_decor = [];
        foreach (em_wp_rubrique_decorative_types() as $em_wp_dt) {
            $em_wp_def = em_wp_field_type_get($em_wp_dt);
            $em_wp_decor[$em_wp_dt] = $em_wp_def ? (string) $em_wp_def['label'] : $em_wp_dt;
        }
        echo wp_json_encode($em_wp_decor);
    ?>;
    var DECOR_COLOR = <?php echo wp_json_encode(em_wp_rubrique_decorative_color_types()); ?>;
    var ARROW = <?php echo wp_json_encode(em_wp_rubrique_arrow_types()); ?>;
    var TXT = {
        image: '<?php echo esc_js(__('Choisir une image', 'em-wp')); ?>',
        pick: '<?php echo esc_js(__('— Choisir —', 'em-wp')); ?>',
        content: '<?php echo esc_js(__('Contenu…', 'em-wp')); ?>',
        link: '<?php echo esc_js(__('Lien (https://… ou #ancre)', 'em-wp')); ?>',
        focal: '<?php echo esc_js(__('Cliquez pour définir le point focal (recadrage)', 'em-wp')); ?>',
        sizeW: '<?php echo esc_js(__('Taille', 'em-wp')); ?>',
        sizeH: '<?php echo esc_js(__('Recadrer H px', 'em-wp')); ?>',
        anchor: '<?php echo esc_js(__('Ancre (#section) ou URL', 'em-wp')); ?>',
        label: '<?php echo esc_js(__('Libellé', 'em-wp')); ?>',
        color: '<?php echo esc_js(__('Modifier la couleur', 'em-wp')); ?>',
        hide: '<?php echo esc_js(__('Visible — cliquer pour masquer', 'em-wp')); ?>',
        remove: '<?php echo esc_js(__('Supprimer', 'em-wp')); ?>'
    };

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function toggleHtml() {
        return '<button type="button" class="em-v4-chip__toggle" aria-pressed="false" title="' + esc(TXT.hide) + '">' +
            '<span class="dashicons dashicons-visibility" aria-hidden="true"></span></button>';
    }

    function decorColorHtml(id) {
        return '<span class="em-v4-chip__color"><div class="em-wp-admin-color-field-row">' +
            '<div class="em-wp-admin-color-trigger" data-em-wp-color-trigger-for="' + id + '">' +
            '<span class="em-wp-admin-color-trigger__swatch" style="--em-wp-color-swatch:#cccccc;" aria-hidden="true"></span>' +
            '<code class="em-wp-admin-color-trigger__hex"></code>' +
            '<button type="button" class="em-wp-catalog-sommaire__edit em-wp-admin-color-trigger__edit" data-em-wp-color-modal-open data-em-wp-color-modal-target="' + id + '" data-em-wp-color-modal-preview-type="swatch" title="' + esc(TXT.color) + '" aria-label="' + esc(TXT.color) + '"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>' +
            '<input type="hidden" id="' + id + '" value="" class="em-wp-admin-color-value em-v4-chip__value">' +
            '</div></div></span>';
    }

    function valueHtml(type) {
        if (type === 'image') {
            return '<span class="em-v4-chip__media" data-url="">' +
                '<span class="em-v4-chip__focal" title="' + esc(TXT.focal) + '">' +
                '<img class="em-v4-chip__thumb" alt="" hidden>' +
                '<span class="em-v4-chip__focaldot" style="left:50%;top:50%" hidden></span></span>' +
                '<button type="button" class="button button-small em-v4-chip__pick">' + esc(TXT.image) + '</button>' +
                '<input type="hidden" class="em-v4-chip__value">' +
                '<input type="hidden" class="em-v4-chip__fx" value="50">' +
                '<input type="hidden" class="em-v4-chip__fy" value="50">' +
                '</span>' +
                '<span class="em-v4-chip__size">' +
                '<label class="em-v4-chip__sizelabel">' + esc(TXT.sizeW) +
                '<input type="range" class="em-v4-chip__w" min="0" max="600" step="5" value="0" oninput="this.nextElementSibling.textContent=(this.value>0?this.value+\'px\':\'auto\')">' +
                '<output class="em-v4-chip__wout">auto</output></label>' +
                '<input type="number" min="0" class="em-v4-chip__h" placeholder="' + esc(TXT.sizeH) + '"></span>' +
                '<input type="url" class="em-v4-chip__url" placeholder="' + esc(TXT.link) + '">';
        }
        if (type === 'icon') {
            var opts = '<option value="">' + esc(TXT.pick) + '</option>';
            Object.keys(PLATFORMS).forEach(function (k) {
                var p = PLATFORMS[k];
                opts += '<option value="' + esc(k) + '" data-icon="' + esc(p.icon) + '">' + esc(p.group + ' — ' + p.label) + '</option>';
            });
            return '<select class="em-v4-chip__platform">' + opts + '</select>' +
                '<input type="url" class="em-v4-chip__url" placeholder="' + esc(TXT.link) + '">';
        }
        return '<input type="text" class="em-v4-chip__value" placeholder="' + esc(TXT.content) + '">';
    }

    function build(type, label) {
        var key = 'f' + Math.random().toString(36).slice(2, 9);
        var chip = document.createElement('div');
        chip.className = 'em-v4-chip';
        chip.setAttribute('draggable', 'true');
        chip.setAttribute('data-key', key);
        chip.setAttribute('data-type', type);
        chip.setAttribute('data-hidden', '0');

        if (DECOR[type] !== undefined) {
            chip.classList.add('em-v4-chip--decor');
            var colorPart = DECOR_COLOR.indexOf(type) !== -1 ? decorColorHtml('emv4dec-' + key) : '';
            var urlPart = ARROW.indexOf(type) !== -1 ? '<input type="url" class="em-v4-chip__url" placeholder="' + esc(TXT.anchor) + '">' : '';
            chip.innerHTML =
                '<span class="em-v4-chip__drag dashicons dashicons-move" aria-hidden="true"></span>' +
                '<span class="em-v4-chip__type">' + esc(DECOR[type]) + '</span>' +
                '<input type="hidden" class="em-v4-chip__label" value="">' +
                colorPart + urlPart + toggleHtml() +
                '<button type="button" class="em-v4-chip__remove" data-label="' + esc(DECOR[type]) + '" title="' + esc(TXT.remove) + '">&times;</button>';
            return chip;
        }

        chip.innerHTML =
            '<span class="em-v4-chip__drag dashicons dashicons-move" aria-hidden="true"></span>' +
            '<span class="em-v4-chip__type"></span>' +
            '<span class="em-v4-chip__fields">' +
                '<input type="text" class="em-v4-chip__label" placeholder="' + esc(TXT.label) + '">' +
                valueHtml(type) +
            '</span>' +
            toggleHtml() +
            '<button type="button" class="em-v4-chip__remove" title="' + esc(TXT.remove) + '">&times;</button>';
        chip.querySelector('.em-v4-chip__type').textContent = type;
        chip.querySelector('.em-v4-chip__label').value = label;
        chip.querySelector('.em-v4-chip__remove').setAttribute('data-label', label);
        return chip;
    }

    function openMedia(btn, update) {
        if (!window.wp || !window.wp.media) { return; }
        var chip = btn.closest('.em-v4-chip');
        var frame = window.wp.media({ title: TXT.image, multiple: false, library: { type: 'image' } });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var media = chip.querySelector('.em-v4-chip__media');
            var hidden = chip.querySelector('.em-v4-chip__value');
            var thumb = chip.querySelector('.em-v4-chip__thumb');
            var sizes = att.sizes || {};
            hidden.value = att.id;
            media.setAttribute('data-url', (sizes.large ? sizes.large.url : att.url));
            if (thumb) { thumb.src = (sizes.medium ? sizes.medium.url : att.url); thumb.hidden = false; }
            var dot = chip.querySelector('.em-v4-chip__focaldot');
            if (dot) { dot.hidden = false; }
            update();
        });
        frame.open();
    }

    // Point focal : positionne fx/fy (%) selon le clic sur la vignette.
    function setFocal(focal, e, update) {
        var img = focal.querySelector('.em-v4-chip__thumb');
        if (!img || img.hidden) { return; }
        var r = img.getBoundingClientRect();
        if (!r.width || !r.height) { return; }
        var fx = Math.max(0, Math.min(100, Math.round((e.clientX - r.left) / r.width * 100)));
        var fy = Math.max(0, Math.min(100, Math.round((e.clientY - r.top) / r.height * 100)));
        var chip = focal.closest('.em-v4-chip');
        var ix = chip.querySelector('.em-v4-chip__fx'), iy = chip.querySelector('.em-v4-chip__fy');
        var dot = focal.querySelector('.em-v4-chip__focaldot');
        if (ix) { ix.value = fx; } if (iy) { iy.value = fy; }
        if (dot) { dot.hidden = false; dot.style.left = fx + '%'; dot.style.top = fy + '%'; }
        update();
    }

    return { build: build, openMedia: openMedia, setFocal: setFocal, decorative: function (type) { return DECOR[type] !== undefined; } };
})();
</script>
<?php
