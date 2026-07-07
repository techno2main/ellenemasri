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
    var TEXT_STYLE = <?php echo wp_json_encode(em_wp_rubrique_text_style_types()); ?>;
    var FONTS = <?php echo wp_json_encode(em_wp_rubrique_font_choices()); ?>;
    var TYPE_LABELS = <?php
        $em_wp_tl = [];
        $em_wp_ti = [];
        foreach (em_wp_v4_builder_field_types() as $em_wp_ft) {
            $em_wp_d = em_wp_field_type_get($em_wp_ft);
            $em_wp_tl[$em_wp_ft] = $em_wp_d ? (string) $em_wp_d['label'] : $em_wp_ft;
            $em_wp_ti[$em_wp_ft] = em_wp_v4_field_type_icon($em_wp_ft);
        }
        echo wp_json_encode($em_wp_tl);
    ?>;
    var TYPE_ICONS = <?php echo wp_json_encode($em_wp_ti); ?>;
    var NETWORKS = <?php echo wp_json_encode(em_wp_rubrique_network_choices()); ?>;
    var TXT = {
        image: '<?php echo esc_js(__('Choisir une image', 'em-wp')); ?>',
        tape: '<?php echo esc_js(__('Scotch', 'em-wp')); ?>',
        pick: '<?php echo esc_js(__('— Choisir —', 'em-wp')); ?>',
        content: '<?php echo esc_js(__('Contenu…', 'em-wp')); ?>',
        link: '<?php echo esc_js(__('Lien (https://… ou #ancre)', 'em-wp')); ?>',
        focal: '<?php echo esc_js(__('Clique pour définir le point focal (recadrage)', 'em-wp')); ?>',
        sizeW: '<?php echo esc_js(__('Taille', 'em-wp')); ?>',
        sizeH: '<?php echo esc_js(__('Recadrer H px', 'em-wp')); ?>',
        anchor: '<?php echo esc_js(__('Ancre (#section) ou URL', 'em-wp')); ?>',
        label: '<?php echo esc_js(__('Libellé', 'em-wp')); ?>',
        ptitle: '<?php echo esc_js(__('Titre (ex. LISTEN ON)', 'em-wp')); ?>',
        color: '<?php echo esc_js(__('Modifier la couleur', 'em-wp')); ?>',
        tsize: '<?php echo esc_js(__('Taille du texte (px)', 'em-wp')); ?>',
        tfont: '<?php echo esc_js(__('Police du champ', 'em-wp')); ?>',
        tfontInherit: '<?php echo esc_js(__('Police héritée', 'em-wp')); ?>',
        tcolor: '<?php echo esc_js(__('Couleur du texte', 'em-wp')); ?>',
        px: '<?php echo esc_js(__('px', 'em-wp')); ?>',
        height: '<?php echo esc_js(__('Hauteur px', 'em-wp')); ?>',
        hide: '<?php echo esc_js(__('Visible — cliquer pour masquer', 'em-wp')); ?>',
        remove: '<?php echo esc_js(__('Supprimer', 'em-wp')); ?>',
        videoUrl: '<?php echo esc_js(__('URL YouTube ou TikTok…', 'em-wp')); ?>',
        audioUrl: '<?php echo esc_js(__('URL du fichier audio…', 'em-wp')); ?>',
        pickVideo: '<?php echo esc_js(__('Choisir une vidéo', 'em-wp')); ?>',
        pickAudio: '<?php echo esc_js(__('Choisir un son', 'em-wp')); ?>',
        addImages: '<?php echo esc_js(__('Ajouter des images', 'em-wp')); ?>',
        netTitle: '<?php echo esc_js(__('Titre (ex. FOLLOW)', 'em-wp')); ?>',
        netAccount: '<?php echo esc_js(__('Pseudo (ex. @ellenemasri)', 'em-wp')); ?>',
        slideDel: '<?php echo esc_js(__('Retirer', 'em-wp')); ?>',
        text1: '<?php echo esc_js(__('Texte 1…', 'em-wp')); ?>',
        text2: '<?php echo esc_js(__('Texte 2…', 'em-wp')); ?>',
        pickThumb: '<?php echo esc_js(__('Choisir une miniature', 'em-wp')); ?>',
        clickable: '<?php echo esc_js(__('Lien cliquable', 'em-wp')); ?>',
        btnBg: '<?php echo esc_js(__('Fond', 'em-wp')); ?>',
        btnText: '<?php echo esc_js(__('Texte', 'em-wp')); ?>',
        btnMargBefore: '<?php echo esc_js(__('Marge avant', 'em-wp')); ?>',
        btnMargAfter: '<?php echo esc_js(__('Marge après', 'em-wp')); ?>',
        badgeText: '<?php echo esc_js(__('Texte du badge…', 'em-wp')); ?>',
        badgeShape: '<?php echo esc_js(__('Forme', 'em-wp')); ?>',
        badgeAnim: '<?php echo esc_js(__('Animation', 'em-wp')); ?>',
        badgeRadius: '<?php echo esc_js(__('Arrondi', 'em-wp')); ?>',
        bsPill: '<?php echo esc_js(__('Pastille (arrondi total)', 'em-wp')); ?>',
        bsSquare: '<?php echo esc_js(__('Carré / rectangle', 'em-wp')); ?>',
        bsTriangle: '<?php echo esc_js(__('Triangle', 'em-wp')); ?>',
        baWiggle: '<?php echo esc_js(__('Balancement', 'em-wp')); ?>',
        baPulse: '<?php echo esc_js(__('Pulsation', 'em-wp')); ?>',
        baBounce: '<?php echo esc_js(__('Rebond', 'em-wp')); ?>',
        baNone: '<?php echo esc_js(__('Aucune', 'em-wp')); ?>',
        slTitle: '<?php echo esc_js(__('Titre du bandeau', 'em-wp')); ?>',
        slTitlePh: '<?php echo esc_js(__('Mayami, My Miami', 'em-wp')); ?>',
        slTitleHide: '<?php echo esc_js(__('Masquer le titre', 'em-wp')); ?>',
        slFrame: '<?php echo esc_js(__('Cadre', 'em-wp')); ?>',
        slBand: '<?php echo esc_js(__('Bandeau', 'em-wp')); ?>',
        slBandText: '<?php echo esc_js(__('Texte titre', 'em-wp')); ?>',
        slImage: '<?php echo esc_js(__('Image', 'em-wp')); ?>',
        slVideo: '<?php echo esc_js(__('Vidéo YouTube', 'em-wp')); ?>',
        slYoutube: '<?php echo esc_js(__('URL YouTube', 'em-wp')); ?>',
        slTiktok: '<?php echo esc_js(__('URL TikTok', 'em-wp')); ?>',
        slVideoFile: '<?php echo esc_js(__('Vidéo fichier', 'em-wp')); ?>',
        slName: '<?php echo esc_js(__('Nom', 'em-wp')); ?>',
        slDuration: '<?php echo esc_js(__('Durée (s)', 'em-wp')); ?>',
        slAdd: '<?php echo esc_js(__('+ Ajouter un slide', 'em-wp')); ?>',
        slUp: '<?php echo esc_js(__('Monter', 'em-wp')); ?>',
        slDown: '<?php echo esc_js(__('Descendre', 'em-wp')); ?>',
        richBold: '<?php echo esc_js(__('Gras', 'em-wp')); ?>',
        richItalic: '<?php echo esc_js(__('Italique', 'em-wp')); ?>',
        richUnderline: '<?php echo esc_js(__('Souligné', 'em-wp')); ?>',
        richList: '<?php echo esc_js(__('Liste', 'em-wp')); ?>',
        richAlignLeft: '<?php echo esc_js(__('Aligner à gauche', 'em-wp')); ?>',
        richAlignCenter: '<?php echo esc_js(__('Centrer', 'em-wp')); ?>',
        richAlignRight: '<?php echo esc_js(__('Aligner à droite', 'em-wp')); ?>',
        richAlignJustify: '<?php echo esc_js(__('Justifier', 'em-wp')); ?>',
        richInlineLink: '<?php echo esc_js(__('Ajouter un lien sur la sélection', 'em-wp')); ?>',
        richUnlink: '<?php echo esc_js(__('Retirer le lien', 'em-wp')); ?>',
        richAnchor: '<?php echo esc_js(__('Ajouter une ancre sur la sélection', 'em-wp')); ?>',
        richPromptLink: '<?php echo esc_js(__('URL du lien (https://... ou #ancre)', 'em-wp')); ?>',
        richPromptAnchor: '<?php echo esc_js(__('Nom de l\'ancre (sans #)', 'em-wp')); ?>',
        richPlaceholder: '<?php echo esc_js(__('Contenu enrichi…', 'em-wp')); ?>',
        richLink: '<?php echo esc_js(__('Lien global (optionnel)', 'em-wp')); ?>',
        talign: '<?php echo esc_js(__('Alignement du texte', 'em-wp')); ?>',
        talignInherit: '<?php echo esc_js(__('Alignement hérité', 'em-wp')); ?>',
        left: '<?php echo esc_js(__('Gauche', 'em-wp')); ?>',
        center: '<?php echo esc_js(__('Centre', 'em-wp')); ?>',
        right: '<?php echo esc_js(__('Droite', 'em-wp')); ?>',
        justify: '<?php echo esc_js(__('Justifié', 'em-wp')); ?>'
    };

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function isTextFamily(type) {
        return ['text', 'textarea', 'text_image', 'text_text'].indexOf(type) !== -1;
    }

    function toggleHtml() {
        return '<button type="button" class="em-v4-chip__toggle" aria-pressed="false" title="' + esc(TXT.hide) + '">' +
            '<span class="dashicons dashicons-visibility" aria-hidden="true"></span></button>';
    }

    function actionsHtml() {
        return '<span class="em-v4-chip__actions">' + toggleHtml() +
            '<button type="button" class="em-v4-chip__remove" title="' + esc(TXT.remove) + '">&times;</button></span>';
    }

    function platformSelectHtml() {
        var opts = '<option value="">' + esc(TXT.pick) + '</option>';
        Object.keys(PLATFORMS).forEach(function (k) {
            var p = PLATFORMS[k];
            opts += '<option value="' + esc(k) + '" data-icon="' + esc(p.icon) + '" data-color="' + esc(p.color || '') + '" data-label="' + esc(p.label) + '">' + esc(p.group + ' — ' + p.label) + '</option>';
        });
        return '<select class="em-v4-chip__platform">' + opts + '</select>';
    }

    var colorUid = 0;

    // id unique par champ couleur : la modale couleur cible via getElementById,
    // deux champs ne doivent jamais partager le même id (ex. 2 flèches).
    function colorId(prefix) {
        colorUid += 1;
        return prefix + colorUid;
    }

    function colorField(id, valueClass, title) {
        return '<div class="em-wp-admin-color-field-row">' +
            '<div class="em-wp-admin-color-trigger" data-em-wp-color-trigger-for="' + id + '">' +
            '<span class="em-wp-admin-color-trigger__swatch" style="--em-wp-color-swatch:#cccccc;" aria-hidden="true"></span>' +
            '<code class="em-wp-admin-color-trigger__hex"></code>' +
            '<button type="button" class="em-wp-catalog-sommaire__edit em-wp-admin-color-trigger__edit" data-em-wp-color-modal-open data-em-wp-color-modal-target="' + id + '" data-em-wp-color-modal-preview-type="swatch" title="' + esc(title) + '" aria-label="' + esc(title) + '"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>' +
            '<input type="hidden" id="' + id + '" value="" class="em-wp-admin-color-value ' + valueClass + '">' +
            '</div></div>';
    }

    function decorColorHtml(id) {
        return '<span class="em-v4-chip__color">' + colorField(id, 'em-v4-chip__value', TXT.color) + '</span>';
    }

    function textStyleHtml(key) {
        var fopts = '<option value="">' + esc(TXT.tfontInherit) + '</option>';
        Object.keys(FONTS).forEach(function (k) {
            fopts += '<option value="' + esc(k) + '" data-stack="' + esc(FONTS[k].stack) + '">' + esc(FONTS[k].label) + '</option>';
        });
        var aopts = '<option value="">' + esc(TXT.talignInherit) + '</option>'
            + '<option value="left">' + esc(TXT.left) + '</option>'
            + '<option value="center">' + esc(TXT.center) + '</option>'
            + '<option value="right">' + esc(TXT.right) + '</option>'
            + '<option value="justify">' + esc(TXT.justify) + '</option>';
        return '<span class="em-v4-chip__tstyle">' +
            '<input type="number" class="em-v4-chip__tsize" min="0" max="200" placeholder="' + esc(TXT.px) + '" title="' + esc(TXT.tsize) + '">' +
            '<select class="em-v4-chip__tfont" title="' + esc(TXT.tfont) + '">' + fopts + '</select>' +
            '<select class="em-v4-chip__talign" title="' + esc(TXT.talign) + '">' + aopts + '</select>' +
            colorField(colorId('emv4ts-'), 'em-v4-chip__tcolor', TXT.tcolor) +
            '</span>';
    }

    function imageHtml() {
        return '<span class="em-v4-chip__media" data-url="">' +
            '<img class="em-v4-chip__thumb" alt="" hidden>' +
            '<button type="button" class="button button-small em-v4-chip__pick">' + esc(TXT.image) + '</button>' +
            '<input type="hidden" class="em-v4-chip__value">' +
            '</span>' +
            '<span class="em-v4-chip__size">' +
            '<label class="em-v4-chip__sizelabel">' + esc(TXT.sizeW) +
            '<input type="range" class="em-v4-chip__w" min="0" max="600" step="5" value="0" oninput="this.nextElementSibling.textContent=(this.value>0?this.value+\'px\':\'auto\')">' +
            '<output class="em-v4-chip__wout">auto</output></label></span>' +
            '<input type="url" class="em-v4-chip__url" placeholder="' + esc(TXT.link) + '">' +
            '<label class="em-v4-chip__check"><input type="checkbox" class="em-v4-chip__itape"> ' + esc(TXT.tape) + '</label>';
    }

<?php require __DIR__ . '/chip-media-script.php'; ?>

    function valueHtml(type, key) {
        if (type === 'textarea') {
            return '<span class="em-v4-chip__rich">'
                + '<span class="em-v4-chip__richbar">'
                + '<button type="button" class="button button-small em-v4-richbtn" data-cmd="bold" title="' + esc(TXT.richBold) + '"><strong>B</strong></button>'
                + '<button type="button" class="button button-small em-v4-richbtn" data-cmd="italic" title="' + esc(TXT.richItalic) + '"><em>I</em></button>'
                + '<button type="button" class="button button-small em-v4-richbtn" data-cmd="underline" title="' + esc(TXT.richUnderline) + '"><span style="text-decoration:underline;">U</span></button>'
                + '<button type="button" class="button button-small em-v4-richbtn" data-cmd="insertUnorderedList" title="' + esc(TXT.richList) + '">•</button>'
                + '<button type="button" class="button button-small em-v4-richbtn" data-cmd="justifyLeft" title="' + esc(TXT.richAlignLeft) + '"><span class="dashicons dashicons-editor-alignleft" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-v4-richbtn" data-cmd="justifyCenter" title="' + esc(TXT.richAlignCenter) + '"><span class="dashicons dashicons-editor-aligncenter" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-v4-richbtn" data-cmd="justifyRight" title="' + esc(TXT.richAlignRight) + '"><span class="dashicons dashicons-editor-alignright" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-v4-richbtn" data-cmd="justifyFull" title="' + esc(TXT.richAlignJustify) + '"><span class="dashicons dashicons-editor-justify" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-v4-richbtn" data-action="link" title="' + esc(TXT.richInlineLink) + '"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-v4-richbtn" data-cmd="unlink" title="' + esc(TXT.richUnlink) + '"><span class="dashicons dashicons-editor-unlink" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-v4-richbtn" data-action="anchor" title="' + esc(TXT.richAnchor) + '">#</button>'
                + '<span class="em-v4-chip__richcolor" title="' + esc(TXT.tcolor) + '">' + colorField(colorId('emv4rc-'), 'em-v4-richcolor', TXT.tcolor) + '</span>'
                + '</span>'
                + '<div class="em-v4-chip__richedit" contenteditable="true" spellcheck="false" autocorrect="off" autocapitalize="off" data-gramm="false" data-placeholder="' + esc(TXT.richPlaceholder) + '"></div>'
                + '<input type="hidden" class="em-v4-chip__value">'
                + '</span>';
        }

        if (type === 'video_url') {
            return '<input type="url" class="em-v4-chip__vurl" placeholder="' + esc(TXT.videoUrl) + '">' +
                '<span class="em-v4-chip__media em-v4-chip__media--av em-v4-chip__vthumb" data-url="" data-mtype="image">' +
                '<button type="button" class="button button-small em-v4-chip__pick">' + esc(TXT.pickThumb) + '</button>' +
                '<span class="em-v4-chip__medianame"></span>' +
                '<input type="hidden" class="em-v4-chip__thumbid"></span>' +
                '<label class="em-v4-chip__check"><input type="checkbox" class="em-v4-chip__clickable"> ' + esc(TXT.clickable) + '</label>';
        }
        if (type === 'audio_url') { return '<input type="url" class="em-v4-chip__value" placeholder="' + esc(TXT.audioUrl) + '">'; }
        if (type === 'video_file') { return avHtml('video', TXT.pickVideo); }
        if (type === 'audio_file') { return avHtml('audio', TXT.pickAudio); }
        if (type === 'slider') { return sliderHtml(); }
        if (type === 'network_block') {
            return '<input type="text" class="em-v4-chip__ptitle" placeholder="' + esc(TXT.netTitle) + '">' +
                networkSelectHtml() +
                '<input type="url" class="em-v4-chip__url" placeholder="' + esc(TXT.link) + '">' +
                '<input type="text" class="em-v4-chip__paccount" placeholder="' + esc(TXT.netAccount) + '">';
        }
        if (type === 'text_image') {
            return '<input type="text" class="em-v4-chip__titext" placeholder="' + esc(TXT.content) + '">' +
                '<input type="url" class="em-v4-chip__tlink" placeholder="' + esc(TXT.link) + '">' +
                textStyleHtml(key) +
                '<span class="em-v4-chip__ti-image">' + imageHtml() + '</span>';
        }
        if (type === 'text_text') {
            return '<span class="em-v4-chip__tt-part"><input type="text" class="em-v4-chip__titext" placeholder="' + esc(TXT.text1) + '"><input type="url" class="em-v4-chip__tlink" placeholder="' + esc(TXT.link) + '">' + textStyleHtml(key) + '</span>' +
                '<span class="em-v4-chip__tt-part"><input type="text" class="em-v4-chip__titext2" placeholder="' + esc(TXT.text2) + '"><input type="url" class="em-v4-chip__tlink2" placeholder="' + esc(TXT.link) + '">' + textStyleHtml(key) + '</span>';
        }
        if (type === 'image') {
            return imageHtml();
        }
        if (type === 'platform_block') {
            return '<input type="text" class="em-v4-chip__ptitle" placeholder="' + esc(TXT.ptitle) + '">' +
                platformSelectHtml() +
                '<input type="url" class="em-v4-chip__url" placeholder="' + esc(TXT.link) + '">';
        }
        if (type === 'button') {
            var btShape = '<select class="em-v4-chip__btnshape">'
                + '<option value="pill">' + esc(TXT.bsPill) + '</option>'
                + '<option value="square">' + esc(TXT.bsSquare) + '</option>'
                + '<option value="triangle">' + esc(TXT.bsTriangle) + '</option></select>';
            var btAnim = '<select class="em-v4-chip__btnanim">'
                + '<option value="none">' + esc(TXT.baNone) + '</option>'
                + '<option value="wiggle">' + esc(TXT.baWiggle) + '</option>'
                + '<option value="pulse">' + esc(TXT.baPulse) + '</option>'
                + '<option value="bounce">' + esc(TXT.baBounce) + '</option></select>';
            return '<input type="url" class="em-v4-chip__url" placeholder="' + esc(TXT.link) + '">' +
                '<span class="em-v4-chip__btncolor"><span class="em-v4-chip__btncolor-label">' + esc(TXT.btnBg) + '</span>' + colorField(colorId('emv4bbg-'), 'em-v4-chip__btnbg', TXT.btnBg) + '</span>' +
                '<span class="em-v4-chip__btncolor"><span class="em-v4-chip__btncolor-label">' + esc(TXT.btnText) + '</span>' + colorField(colorId('emv4btx-'), 'em-v4-chip__btntext', TXT.btnText) + '</span>' +
                '<label class="em-v4-chip__btnmargin"><span>' + esc(TXT.btnMargBefore) + '</span><input type="number" class="em-v4-chip__btnml" min="0" max="200" value="0"></label>' +
                '<label class="em-v4-chip__btnmargin"><span>' + esc(TXT.btnMargAfter) + '</span><input type="number" class="em-v4-chip__btnmr" min="0" max="200" value="0"></label>' +
                '<label class="em-v4-chip__badgeopt"><span>' + esc(TXT.badgeShape) + '</span>' + btShape + '</label>' +
                '<label class="em-v4-chip__badgeopt"><span>' + esc(TXT.badgeAnim) + '</span>' + btAnim + '</label>' +
                '<label class="em-v4-chip__badgeopt"><span>' + esc(TXT.badgeRadius) + '</span><input type="number" class="em-v4-chip__btnradius" min="0" max="40" value="6"></label>';
        }
        if (type === 'animated_badge') {
            var bShape = '<select class="em-v4-chip__badgeshape">'
                + '<option value="pill">' + esc(TXT.bsPill) + '</option>'
                + '<option value="square">' + esc(TXT.bsSquare) + '</option>'
                + '<option value="triangle">' + esc(TXT.bsTriangle) + '</option></select>';
            var bAnim = '<select class="em-v4-chip__badgeanim">'
                + '<option value="wiggle">' + esc(TXT.baWiggle) + '</option>'
                + '<option value="pulse">' + esc(TXT.baPulse) + '</option>'
                + '<option value="bounce">' + esc(TXT.baBounce) + '</option>'
                + '<option value="none">' + esc(TXT.baNone) + '</option></select>';
            return '<input type="text" class="em-v4-chip__btext" placeholder="' + esc(TXT.badgeText) + '">' +
                '<span class="em-v4-chip__btncolor"><span class="em-v4-chip__btncolor-label">' + esc(TXT.btnBg) + '</span>' + colorField(colorId('emv4babg-'), 'em-v4-chip__badgebg', TXT.btnBg) + '</span>' +
                '<span class="em-v4-chip__btncolor"><span class="em-v4-chip__btncolor-label">' + esc(TXT.btnText) + '</span>' + colorField(colorId('emv4baink-'), 'em-v4-chip__badgeink', TXT.btnText) + '</span>' +
                '<label class="em-v4-chip__badgeopt"><span>' + esc(TXT.badgeShape) + '</span>' + bShape + '</label>' +
                '<label class="em-v4-chip__badgeopt"><span>' + esc(TXT.badgeAnim) + '</span>' + bAnim + '</label>' +
                '<label class="em-v4-chip__badgeopt"><span>' + esc(TXT.badgeRadius) + '</span><input type="number" class="em-v4-chip__badgeradius" min="0" max="40" value="6"></label>';
        }
        if (type === 'icon') {
            return platformSelectHtml() +
                '<input type="url" class="em-v4-chip__url" placeholder="' + esc(TXT.link) + '">';
        }
        var input = '<input type="text" class="em-v4-chip__value" placeholder="' + esc(TXT.content) + '">';
        if (type === 'text') {
            input += '<input type="url" class="em-v4-chip__tlink" placeholder="' + esc(TXT.link) + '">';
        }
        if (TEXT_STYLE.indexOf(type) !== -1) { input += textStyleHtml(key); }
        return input;
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
            var colorPart = DECOR_COLOR.indexOf(type) !== -1 ? decorColorHtml(colorId('emv4dec-')) : '';
            var urlPart = ARROW.indexOf(type) !== -1 ? '<input type="url" class="em-v4-chip__url" placeholder="' + esc(TXT.anchor) + '">' : '';
            var heightPart = type === 'sep_blank' ? '<input type="number" class="em-v4-chip__value em-v4-chip__height" min="0" max="400" placeholder="' + esc(TXT.height) + '">' : '';
            chip.innerHTML =
                '<span class="em-v4-chip__drag dashicons dashicons-move" aria-hidden="true"></span>' +
                '<span class="em-v4-chip__type"><span class="em-v4-chip__typeicon dashicons ' + esc(TYPE_ICONS[type] || 'dashicons-marker') + '" aria-hidden="true"></span>' + esc(DECOR[type]) + '</span>' +
                '<input type="hidden" class="em-v4-chip__label" value="">' +
                colorPart + urlPart + heightPart + actionsHtml();
            chip.querySelector('.em-v4-chip__remove').setAttribute('data-label', DECOR[type]);
            return chip;
        }

        var labelInput = (type === 'platform_block' || type === 'network_block' || type === 'animated_badge' || isTextFamily(type))
            ? '<input type="hidden" class="em-v4-chip__label">'
            : '<input type="text" class="em-v4-chip__label" placeholder="' + esc(TXT.label) + '">';
        chip.innerHTML =
            '<span class="em-v4-chip__drag dashicons dashicons-move" aria-hidden="true"></span>' +
            '<span class="em-v4-chip__type"><span class="em-v4-chip__typeicon dashicons ' + esc(TYPE_ICONS[type] || 'dashicons-marker') + '" aria-hidden="true"></span>' + esc(TYPE_LABELS[type] || type) + '</span>' +
            '<span class="em-v4-chip__fields">' +
                labelInput +
                valueHtml(type, key) +
            '</span>' +
            actionsHtml();
        chip.querySelector('.em-v4-chip__label').value = label;
        chip.querySelector('.em-v4-chip__remove').setAttribute('data-label', label);
        return chip;
    }

    function openMedia(btn, update) {
        if (!window.wp || !window.wp.media) { return; }
        var chip = btn.closest('.em-v4-chip');
        var type = chip.getAttribute('data-type');
        if (type === 'video_url') { return openThumb(chip, update); }
        if (type === 'video_file' || type === 'audio_file') { return openAv(chip, type, update); }
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

    // Lit le style de texte d'une chip (taille px, clé de police, couleur hex).
    function readStyle(chip) {
        var sz = chip.querySelector('.em-v4-chip__tsize');
        var fn = chip.querySelector('.em-v4-chip__tfont');
        var al = chip.querySelector('.em-v4-chip__talign');
        var cl = chip.querySelector('.em-v4-chip__tcolor');
        return { size: sz ? (parseInt(sz.value, 10) || 0) : 0, font: fn ? fn.value : '', align: al ? al.value : '', color: cl ? cl.value : '' };
    }

    return {
        build: build,
        openMedia: openMedia,
        removeSlide: removeSlide,
        slideRowHtml: slideRowHtml,
        readMedia: readMedia,
        setFocal: setFocal,
        readStyle: readStyle,
        fonts: FONTS,
        hasTextStyle: function (type) { return TEXT_STYLE.indexOf(type) !== -1; },
        decorative: function (type) { return DECOR[type] !== undefined; },
        labelOptional: function (type) {
            return DECOR[type] !== undefined || isTextFamily(type) || ['platform_block', 'network_block', 'animated_badge', 'video_url', 'video_file', 'audio_file', 'audio_url', 'slider'].indexOf(type) !== -1;
        }
    };
})();
</script>
<?php
