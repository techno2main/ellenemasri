<?php
/**
 * Construction des chips côté client (EM-SITE) : valeur selon le type
 * (texte, image via médiathèque, icône plateforme), exposée via EmSiteChip.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
window.EmSiteChip = (function () {
    var PLATFORMS = <?php echo wp_json_encode(em_site_rubrique_platform_choices()); ?>;
    var DECOR = <?php
        $em_site_decor = [];
        foreach (em_site_rubrique_decorative_types() as $em_site_dt) {
            $em_site_def = em_site_field_type_get($em_site_dt);
            $em_site_decor[$em_site_dt] = $em_site_def ? (string) $em_site_def['label'] : $em_site_dt;
        }
        echo wp_json_encode($em_site_decor);
    ?>;
    var DECOR_COLOR = <?php echo wp_json_encode(em_site_rubrique_decorative_color_types()); ?>;
    var ARROW = <?php echo wp_json_encode(em_site_rubrique_arrow_types()); ?>;
    var TEXT_STYLE = <?php echo wp_json_encode(em_site_rubrique_text_style_types()); ?>;
    var FONTS = <?php echo wp_json_encode(em_site_rubrique_font_choices()); ?>;
    var TYPE_LABELS = <?php
        $em_site_tl = [];
        $em_site_ti = [];
        foreach (em_site_builder_field_types() as $em_site_ft) {
            $em_site_d = em_site_field_type_get($em_site_ft);
            $em_site_tl[$em_site_ft] = $em_site_d ? (string) $em_site_d['label'] : $em_site_ft;
            $em_site_ti[$em_site_ft] = em_site_field_type_icon($em_site_ft);
        }
        echo wp_json_encode($em_site_tl);
    ?>;
    var TYPE_ICONS = <?php echo wp_json_encode($em_site_ti); ?>;
    var NETWORKS = <?php echo wp_json_encode(em_site_rubrique_network_choices()); ?>;
    var DASHICONS = <?php echo wp_json_encode(function_exists('em_site_dashicons_all') ? em_site_dashicons_all() : ['dashicons-screenoptions']); ?>;
    var STREAM_LINKS = <?php
        $em_site_stream_links = [];
        if (function_exists('em_site_get_items')) {
            foreach (em_site_get_items('stream') as $em_site_stream_slug => $em_site_stream_item) {
                $em_site_stream_slug = sanitize_key((string) $em_site_stream_slug);
                if ($em_site_stream_slug === '') {
                    continue;
                }

                $em_site_stream_anchor = strpos($em_site_stream_slug, 'stream-') === 0
                    ? ('#' . $em_site_stream_slug)
                    : ('#stream-' . $em_site_stream_slug);

                $em_site_stream_label_raw = is_array($em_site_stream_item)
                    ? (string) ($em_site_stream_item['label'] ?? $em_site_stream_slug)
                    : $em_site_stream_slug;
                $em_site_stream_label_raw = trim($em_site_stream_label_raw);
                if ($em_site_stream_label_raw === '') {
                    $em_site_stream_label_raw = $em_site_stream_slug;
                }

                $em_site_stream_label_clean = preg_replace('/^stream\s*[-_:]?\s*/i', '', $em_site_stream_label_raw);
                $em_site_stream_label_clean = trim((string) $em_site_stream_label_clean);
                if ($em_site_stream_label_clean === '') {
                    $em_site_stream_label_clean = $em_site_stream_slug;
                }

                $em_site_stream_links[] = [
                    'slug' => $em_site_stream_slug,
                    'label' => 'Stream ' . ucwords(str_replace(['-', '_'], ' ', $em_site_stream_label_clean)),
                    'url' => $em_site_stream_anchor,
                    'platformUrls' => (function () use ($em_site_stream_slug) {
                        $map = [];
                        if (function_exists('em_site_get_item') && function_exists('em_site_stream_collect_platform_cards')) {
                            $item = em_site_get_item('stream', $em_site_stream_slug);
                            $content = is_array($item['content'] ?? null) ? $item['content'] : [];
                            foreach (em_site_stream_collect_platform_cards($content, $item) as $card) {
                                $platform_key = sanitize_key((string) ($card['platform_slug'] ?? ''));
                                $platform_url = (string) ($card['url'] ?? '');
                                if ($platform_key !== '' && $platform_url !== '') {
                                    $map[$platform_key] = $platform_url;
                                }
                            }
                        }
                        return $map;
                    })(),
                ];
            }
        }
        echo wp_json_encode($em_site_stream_links);
    ?>;
    var TXT = {
        image: '<?php echo esc_js(__('Choisir une image', 'em-site')); ?>',
        tape: '<?php echo esc_js(__('Scotch', 'em-site')); ?>',
        tapeHide: '<?php echo esc_js(__('Masquer les scotchs', 'em-site')); ?>',
        pick: '<?php echo esc_js(__('— Choisir —', 'em-site')); ?>',
        content: '<?php echo esc_js(__('Contenu…', 'em-site')); ?>',
        link: '<?php echo esc_js(__('Lien (https://… ou #ancre)', 'em-site')); ?>',
        focal: '<?php echo esc_js(__('Clique pour définir le point focal (recadrage)', 'em-site')); ?>',
        sizeW: '<?php echo esc_js(__('Taille', 'em-site')); ?>',
        sizeH: '<?php echo esc_js(__('Recadrer H px', 'em-site')); ?>',
        anchor: '<?php echo esc_js(__('Ancre (#section) ou URL', 'em-site')); ?>',
        label: '<?php echo esc_js(__('Libellé', 'em-site')); ?>',
        ptitle: '<?php echo esc_js(__('Titre (ex. LISTEN ON)', 'em-site')); ?>',
        color: '<?php echo esc_js(__('Modifier la couleur', 'em-site')); ?>',
        tsize: '<?php echo esc_js(__('Taille du texte (px)', 'em-site')); ?>',
        tfont: '<?php echo esc_js(__('Police du champ', 'em-site')); ?>',
        tfontInherit: '<?php echo esc_js(__('Police héritée', 'em-site')); ?>',
        tcolor: '<?php echo esc_js(__('Couleur du texte', 'em-site')); ?>',
        px: '<?php echo esc_js(__('px', 'em-site')); ?>',
        height: '<?php echo esc_js(__('Hauteur px', 'em-site')); ?>',
        hide: '<?php echo esc_js(__('Visible — cliquer pour masquer', 'em-site')); ?>',
        remove: '<?php echo esc_js(__('Supprimer', 'em-site')); ?>',
        videoUrl: '<?php echo esc_js(__('URL YouTube ou TikTok…', 'em-site')); ?>',
        audioUrl: '<?php echo esc_js(__('URL du fichier audio…', 'em-site')); ?>',
        pickVideo: '<?php echo esc_js(__('Choisir une vidéo', 'em-site')); ?>',
        pickAudio: '<?php echo esc_js(__('Choisir un son', 'em-site')); ?>',
        addImages: '<?php echo esc_js(__('Ajouter des images', 'em-site')); ?>',
        netTitle: '<?php echo esc_js(__('Titre (ex. FOLLOW)', 'em-site')); ?>',
        netAccount: '<?php echo esc_js(__('Pseudo (ex. @ellenemasri)', 'em-site')); ?>',
        slideDel: '<?php echo esc_js(__('Retirer', 'em-site')); ?>',
        text1: '<?php echo esc_js(__('Texte 1…', 'em-site')); ?>',
        text2: '<?php echo esc_js(__('Texte 2…', 'em-site')); ?>',
        pickThumb: '<?php echo esc_js(__('Choisir une miniature', 'em-site')); ?>',
        clickable: '<?php echo esc_js(__('Lien cliquable', 'em-site')); ?>',
        btnBg: '<?php echo esc_js(__('Fond', 'em-site')); ?>',
        btnText: '<?php echo esc_js(__('Texte', 'em-site')); ?>',
        btnMargBefore: '<?php echo esc_js(__('Marge avant', 'em-site')); ?>',
        btnMargAfter: '<?php echo esc_js(__('Marge après', 'em-site')); ?>',
        badgeText: '<?php echo esc_js(__('Texte du badge…', 'em-site')); ?>',
        badgeShape: '<?php echo esc_js(__('Forme', 'em-site')); ?>',
        badgeAnim: '<?php echo esc_js(__('Animation', 'em-site')); ?>',
        badgeRadius: '<?php echo esc_js(__('Arrondi', 'em-site')); ?>',
        bsPill: '<?php echo esc_js(__('Pastille (arrondi total)', 'em-site')); ?>',
        bsSquare: '<?php echo esc_js(__('Carré / rectangle', 'em-site')); ?>',
        bsTriangle: '<?php echo esc_js(__('Triangle', 'em-site')); ?>',
        baWiggle: '<?php echo esc_js(__('Balancement', 'em-site')); ?>',
        baPulse: '<?php echo esc_js(__('Pulsation', 'em-site')); ?>',
        baBounce: '<?php echo esc_js(__('Rebond', 'em-site')); ?>',
        baNone: '<?php echo esc_js(__('Aucune', 'em-site')); ?>',
        slTitle: '<?php echo esc_js(__('Titre du bandeau', 'em-site')); ?>',
        slTitlePh: '<?php echo esc_js(__('Mayami, My Miami', 'em-site')); ?>',
        slTitleHide: '<?php echo esc_js(__('Masquer le bandeau', 'em-site')); ?>',
        slBorder: '<?php echo esc_js(__('Bordure', 'em-site')); ?>',
        slShadow: '<?php echo esc_js(__('Ombre', 'em-site')); ?>',
        slBand: '<?php echo esc_js(__('Bandeau', 'em-site')); ?>',
        slBandText: '<?php echo esc_js(__('Texte titre', 'em-site')); ?>',
        slTapeHide: '<?php echo esc_js(__('Masquer les scotchs', 'em-site')); ?>',
        slTape: '<?php echo esc_js(__('Scotch', 'em-site')); ?>',
        slImage: '<?php echo esc_js(__('Image', 'em-site')); ?>',
        slVideo: '<?php echo esc_js(__('Vidéo YouTube', 'em-site')); ?>',
        slYoutube: '<?php echo esc_js(__('URL YouTube', 'em-site')); ?>',
        slTiktok: '<?php echo esc_js(__('URL TikTok', 'em-site')); ?>',
        slVideoFile: '<?php echo esc_js(__('Vidéo fichier', 'em-site')); ?>',
        slName: '<?php echo esc_js(__('Nom', 'em-site')); ?>',
        slDuration: '<?php echo esc_js(__('Durée (s)', 'em-site')); ?>',
        slAdd: '<?php echo esc_js(__('+ Ajouter un slide', 'em-site')); ?>',
        slUp: '<?php echo esc_js(__('Monter', 'em-site')); ?>',
        slDown: '<?php echo esc_js(__('Descendre', 'em-site')); ?>',
        richBold: '<?php echo esc_js(__('Gras', 'em-site')); ?>',
        richItalic: '<?php echo esc_js(__('Italique', 'em-site')); ?>',
        richUnderline: '<?php echo esc_js(__('Souligné', 'em-site')); ?>',
        richList: '<?php echo esc_js(__('Liste', 'em-site')); ?>',
        richAlignLeft: '<?php echo esc_js(__('Aligner à gauche', 'em-site')); ?>',
        richAlignCenter: '<?php echo esc_js(__('Centrer', 'em-site')); ?>',
        richAlignRight: '<?php echo esc_js(__('Aligner à droite', 'em-site')); ?>',
        richAlignJustify: '<?php echo esc_js(__('Justifier', 'em-site')); ?>',
        richInlineLink: '<?php echo esc_js(__('Ajouter un lien sur la sélection', 'em-site')); ?>',
        richUnlink: '<?php echo esc_js(__('Retirer le lien', 'em-site')); ?>',
        richAnchor: '<?php echo esc_js(__('Ajouter une ancre sur la sélection', 'em-site')); ?>',
        richPromptLink: '<?php echo esc_js(__('URL du lien (https://... ou #ancre)', 'em-site')); ?>',
        richPromptAnchor: '<?php echo esc_js(__('Nom de l\'ancre (sans #)', 'em-site')); ?>',
        richPlaceholder: '<?php echo esc_js(__('Contenu enrichi…', 'em-site')); ?>',
        richLink: '<?php echo esc_js(__('Lien global (optionnel)', 'em-site')); ?>',
        talign: '<?php echo esc_js(__('Alignement du texte', 'em-site')); ?>',
        talignInherit: '<?php echo esc_js(__('Alignement hérité', 'em-site')); ?>',
        left: '<?php echo esc_js(__('Gauche', 'em-site')); ?>',
        center: '<?php echo esc_js(__('Centre', 'em-site')); ?>',
        right: '<?php echo esc_js(__('Droite', 'em-site')); ?>',
        justify: '<?php echo esc_js(__('Justifié', 'em-site')); ?>'
    };

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function isTextFamily(type) {
        return ['text', 'textarea', 'text_image', 'text_icon', 'icon_text', 'text_text'].indexOf(type) !== -1;
    }

    function toggleHtml() {
        return '<button type="button" class="em-site-chip__toggle" aria-pressed="false" title="' + esc(TXT.hide) + '">' +
            '<span class="dashicons dashicons-visibility" aria-hidden="true"></span></button>';
    }

    function actionsHtml() {
        return '<span class="em-site-chip__actions">' + toggleHtml() +
            '<button type="button" class="em-site-chip__remove" title="' + esc(TXT.remove) + '">&times;</button></span>';
    }

    function platformSelectHtml() {
        var opts = '<option value="">' + esc(TXT.pick) + '</option>';
        Object.keys(PLATFORMS).forEach(function (k) {
            var p = PLATFORMS[k];
            opts += '<option value="' + esc(k) + '" data-icon="' + esc(p.icon) + '" data-color="' + esc(p.color || '') + '" data-label="' + esc(p.label) + '">' + esc(p.group + ' — ' + p.label) + '</option>';
        });
        return '<select class="em-site-chip__platform">' + opts + '</select>';
    }

    function dashiconSelectHtml() {
        var items = '';
        DASHICONS.forEach(function (icon) {
            var label = String(icon || '').replace(/^dashicons-/, '');
            items += '<button type="button" class="em-site-iconchooser__item" data-icon-value="' + esc(icon) + '" aria-pressed="false" title="' + esc(icon) + '">' +
                '<span class="dashicons ' + esc(icon) + '" aria-hidden="true"></span>' +
                '<span class="em-site-iconchooser__item-name">' + esc(label) + '</span>' +
                '</button>';
        });

        return '<div class="em-site-iconchooser em-site-iconchooser--compact" data-iconchooser>' +
            '<input type="hidden" value="" class="em-site-chip__dashicon" data-iconchooser-value data-default-icon="dashicons-screenoptions">' +
            '<button type="button" class="em-site-iconchooser__trigger" data-iconchooser-trigger aria-expanded="false" aria-haspopup="dialog" aria-label="' + esc('<?php echo esc_js(__('Choisir une icône', 'em-site')); ?>') + '">' +
                '<span class="em-site-iconchooser__preview" data-iconchooser-preview><span class="dashicons dashicons-screenoptions" aria-hidden="true"></span></span>' +
                '<span class="em-site-iconchooser__meta"><span class="em-site-iconchooser__meta-label"><?php echo esc_js(__('Icône', 'em-site')); ?></span><span class="em-site-iconchooser__meta-name" data-iconchooser-name data-iconchooser-placeholder="<?php echo esc_js(__('Choisir une icône BO', 'em-site')); ?>"><?php echo esc_js(__('Choisir une icône BO', 'em-site')); ?></span></span>' +
                '<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>' +
            '</button>' +
            '<div class="em-site-iconchooser__panel" data-iconchooser-panel hidden><div class="em-site-iconchooser__groups" data-iconchooser-groups><section class="em-site-iconchooser__group" data-iconchooser-group><h4 class="em-site-iconchooser__group-title"><?php echo esc_js(__('Icônes BO', 'em-site')); ?></h4><div class="em-site-iconchooser__items">' + items + '</div></section></div></div>' +
        '</div>';
    }

    function streamTargetSelectHtml() {
        if (!STREAM_LINKS.length) {
            return '';
        }

        var opts = '<option value="#stream">' + esc('<?php echo esc_js(__('Stream Global', 'em-site')); ?>') + ' (#stream)</option>';
        STREAM_LINKS.forEach(function (item) {
            opts += '<option value="' + esc(item.url || '') + '" data-stream-slug="' + esc(item.slug || '') + '">' + esc((item.label || item.slug || 'stream') + ' (' + (item.url || '') + ')') + '</option>';
        });

        return '<select class="em-site-chip__streamtarget" title="' + esc('<?php echo esc_js(__('Cible stream inline', 'em-site')); ?>') + '">' + opts + '</select>';
    }

    var colorUid = 0;

    // id unique par champ couleur : la modale couleur cible via getElementById,
    // deux champs ne doivent jamais partager le même id (ex. 2 flèches).
    function colorId(prefix) {
        colorUid += 1;
        return prefix + colorUid;
    }

    function colorField(id, valueClass, title, previewType, bgTargetId) {
        var previewAttr = previewType ? ' data-em-site-color-modal-preview-type="' + esc(previewType) + '"' : ' data-em-site-color-modal-preview-type="swatch"';
        var bgAttr = bgTargetId ? ' data-em-site-color-modal-bg-target="' + esc(bgTargetId) + '"' : '';
        return '<div class="em-site-admin-color-field-row">' +
            '<div class="em-site-admin-color-trigger" data-em-site-color-trigger-for="' + id + '">' +
            '<span class="em-site-admin-color-trigger__swatch" style="--em-site-color-swatch:#cccccc;" aria-hidden="true"></span>' +
            '<button type="button" class="em-site-catalog-sommaire__edit em-site-admin-color-trigger__edit" data-em-site-color-modal-open data-em-site-color-modal-target="' + id + '"' + previewAttr + bgAttr + ' title="' + esc(title) + '" aria-label="' + esc(title) + '"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>' +
            '<input type="hidden" id="' + id + '" value="" class="em-site-admin-color-value ' + valueClass + '">' +
            '</div></div>';
    }

    function decorColorHtml(id) {
        return '<span class="em-site-chip__color">' + colorField(id, 'em-site-chip__value', TXT.color) + '</span>';
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
        return '<span class="em-site-chip__tstyle">' +
            '<input type="number" class="em-site-chip__tsize" min="0" max="200" placeholder="' + esc(TXT.px) + '" title="' + esc(TXT.tsize) + '">' +
            '<select class="em-site-chip__tfont" title="' + esc(TXT.tfont) + '">' + fopts + '</select>' +
            '<select class="em-site-chip__talign" title="' + esc(TXT.talign) + '">' + aopts + '</select>' +
            colorField(colorId('em-site-ts-'), 'em-site-chip__tcolor', TXT.tcolor) +
            '</span>';
    }

<?php require __DIR__ . '/../../../admin/shared/components/scotchs-control/scotchs-control-script.php'; ?>

    function imageHtml() {
        return '<span class="em-site-chip__media" data-url="">' +
            '<img class="em-site-chip__thumb" alt="" hidden>' +
            '<button type="button" class="button button-small em-site-chip__pick">' + esc(TXT.image) + '</button>' +
            '<input type="hidden" class="em-site-chip__value">' +
            '</span>' +
            '<span class="em-site-chip__size">' +
            '<label class="em-site-chip__sizelabel">' + esc(TXT.sizeW) +
            '<input type="range" class="em-site-chip__w" min="0" max="600" step="5" value="0" oninput="this.nextElementSibling.textContent=(this.value>0?this.value+\'px\':\'auto\')">' +
            '<output class="em-site-chip__wout">auto</output></label></span>' +
            '<input type="url" class="em-site-chip__url" placeholder="' + esc(TXT.link) + '">' +
            scotchsControlHtml({
                hiddenClass: 'em-site-chip__itape-hidden',
                hiddenChecked: true,
                hiddenLabel: TXT.tapeHide,
                colorClass: 'em-site-chip__itape-color',
                colorLabel: TXT.tape,
                colorIdPrefix: 'em-site-itp-'
            });
    }

<?php require __DIR__ . '/builder-chip-media-script.php'; ?>

    function valueHtml(type, key) {
        if (type === 'textarea') {
            return '<span class="em-site-chip__rich">'
                + '<span class="em-site-chip__richbar">'
                + '<button type="button" class="button button-small em-site-richbtn" data-cmd="bold" title="' + esc(TXT.richBold) + '"><strong>B</strong></button>'
                + '<button type="button" class="button button-small em-site-richbtn" data-cmd="italic" title="' + esc(TXT.richItalic) + '"><em>I</em></button>'
                + '<button type="button" class="button button-small em-site-richbtn" data-cmd="underline" title="' + esc(TXT.richUnderline) + '"><span style="text-decoration:underline;">U</span></button>'
                + '<button type="button" class="button button-small em-site-richbtn" data-cmd="insertUnorderedList" title="' + esc(TXT.richList) + '">•</button>'
                + '<button type="button" class="button button-small em-site-richbtn" data-cmd="justifyLeft" title="' + esc(TXT.richAlignLeft) + '"><span class="dashicons dashicons-editor-alignleft" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-site-richbtn" data-cmd="justifyCenter" title="' + esc(TXT.richAlignCenter) + '"><span class="dashicons dashicons-editor-aligncenter" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-site-richbtn" data-cmd="justifyRight" title="' + esc(TXT.richAlignRight) + '"><span class="dashicons dashicons-editor-alignright" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-site-richbtn" data-cmd="justifyFull" title="' + esc(TXT.richAlignJustify) + '"><span class="dashicons dashicons-editor-justify" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-site-richbtn" data-action="link" title="' + esc(TXT.richInlineLink) + '"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-site-richbtn" data-cmd="unlink" title="' + esc(TXT.richUnlink) + '"><span class="dashicons dashicons-editor-unlink" aria-hidden="true"></span></button>'
                + '<button type="button" class="button button-small em-site-richbtn" data-action="anchor" title="' + esc(TXT.richAnchor) + '">#</button>'
                + '<span class="em-site-chip__richcolor" title="' + esc(TXT.tcolor) + '">' + colorField(colorId('em-site-rc-'), 'em-site-richcolor', TXT.tcolor) + '</span>'
                + '</span>'
                + '<div class="em-site-chip__richedit" contenteditable="true" spellcheck="false" autocorrect="off" autocapitalize="off" data-gramm="false" data-placeholder="' + esc(TXT.richPlaceholder) + '"></div>'

            return '<input type="text" class="em-site-chip__ptitle" placeholder="' + esc(TXT.netTitle) + '">' +
                networkSelectHtml() +
                '<input type="url" class="em-site-chip__url" placeholder="' + esc(TXT.link) + '">' +
                '<input type="text" class="em-site-chip__paccount" placeholder="' + esc(TXT.netAccount) + '">';
        }
        if (type === 'text_image') {
            return '<input type="text" class="em-site-chip__titext" placeholder="' + esc(TXT.content) + '">' +
                '<input type="url" class="em-site-chip__tlink" placeholder="' + esc(TXT.link) + '">' +
                textStyleHtml(key) +
                '<span class="em-site-chip__ti-image">' + imageHtml() + '</span>';
        }
        if (type === 'text_icon' || type === 'icon_text') {
            return '<input type="text" class="em-site-chip__titext" placeholder="' + esc(TXT.content) + '">' +
                '<input type="url" class="em-site-chip__tlink" placeholder="' + esc(TXT.link) + '">' +
            dashiconSelectHtml();
        }
        if (type === 'text_text') {
            return '<span class="em-site-chip__tt-part"><input type="text" class="em-site-chip__titext" placeholder="' + esc(TXT.text1) + '"><input type="url" class="em-site-chip__tlink" placeholder="' + esc(TXT.link) + '">' + textStyleHtml(key) + '</span>' +
                '<span class="em-site-chip__tt-part"><input type="text" class="em-site-chip__titext2" placeholder="' + esc(TXT.text2) + '"><input type="url" class="em-site-chip__tlink2" placeholder="' + esc(TXT.link) + '">' + textStyleHtml(key) + '</span>';
        }
        if (type === 'image') {
            return imageHtml();
        }
        if (type === 'platform_block') {
            return '<input type="text" class="em-site-chip__ptitle" placeholder="' + esc(TXT.ptitle) + '">' +
                platformSelectHtml() +
                streamTargetSelectHtml() +
                '<input type="url" class="em-site-chip__url" placeholder="' + esc(TXT.link) + '">';
        }
        if (type === 'button') {
            var btShape = '<select class="em-site-chip__btnshape">'
                + '<option value="pill">' + esc(TXT.bsPill) + '</option>'
                + '<option value="square">' + esc(TXT.bsSquare) + '</option>'
                + '<option value="triangle">' + esc(TXT.bsTriangle) + '</option></select>';
            var btAnim = '<select class="em-site-chip__btnanim">'
                + '<option value="none">' + esc(TXT.baNone) + '</option>'
                + '<option value="wiggle">' + esc(TXT.baWiggle) + '</option>'
                + '<option value="pulse">' + esc(TXT.baPulse) + '</option>'
                + '<option value="bounce">' + esc(TXT.baBounce) + '</option></select>';
            return '<input type="url" class="em-site-chip__url" placeholder="' + esc(TXT.link) + '">' +
                '<span class="em-site-chip__btncolor"><span class="em-site-chip__btncolor-label">' + esc(TXT.btnBg) + '</span>' + colorField(colorId('em-site-bbg-'), 'em-site-chip__btnbg', TXT.btnBg) + '</span>' +
                '<span class="em-site-chip__btncolor"><span class="em-site-chip__btncolor-label">' + esc(TXT.btnText) + '</span>' + colorField(colorId('em-site-btx-'), 'em-site-chip__btntext', TXT.btnText) + '</span>' +
                '<label class="em-site-chip__btnmargin"><span>' + esc(TXT.btnMargBefore) + '</span><input type="number" class="em-site-chip__btnml" min="0" max="200" value="0"></label>' +
                '<label class="em-site-chip__btnmargin"><span>' + esc(TXT.btnMargAfter) + '</span><input type="number" class="em-site-chip__btnmr" min="0" max="200" value="0"></label>' +
                '<label class="em-site-chip__badgeopt"><span>' + esc(TXT.badgeShape) + '</span>' + btShape + '</label>' +
                '<label class="em-site-chip__badgeopt"><span>' + esc(TXT.badgeAnim) + '</span>' + btAnim + '</label>' +
                '<label class="em-site-chip__badgeopt"><span>' + esc(TXT.badgeRadius) + '</span><input type="number" class="em-site-chip__btnradius" min="0" max="40" value="6"></label>';
        }
        if (type === 'animated_badge') {
            var bShape = '<select class="em-site-chip__badgeshape">'
                + '<option value="pill">' + esc(TXT.bsPill) + '</option>'
                + '<option value="square">' + esc(TXT.bsSquare) + '</option>'
                + '<option value="triangle">' + esc(TXT.bsTriangle) + '</option></select>';
            var bAnim = '<select class="em-site-chip__badgeanim">'
                + '<option value="wiggle">' + esc(TXT.baWiggle) + '</option>'
                + '<option value="pulse">' + esc(TXT.baPulse) + '</option>'
                + '<option value="bounce">' + esc(TXT.baBounce) + '</option>'
                + '<option value="none">' + esc(TXT.baNone) + '</option></select>';
            return '<input type="text" class="em-site-chip__btext" placeholder="' + esc(TXT.badgeText) + '">' +
                '<span class="em-site-chip__btncolor"><span class="em-site-chip__btncolor-label">' + esc(TXT.btnBg) + '</span>' + colorField(colorId('em-site-babg-'), 'em-site-chip__badgebg', TXT.btnBg) + '</span>' +
                '<span class="em-site-chip__btncolor"><span class="em-site-chip__btncolor-label">' + esc(TXT.btnText) + '</span>' + colorField(colorId('em-site-baink-'), 'em-site-chip__badgeink', TXT.btnText) + '</span>' +
                '<label class="em-site-chip__badgeopt"><span>' + esc(TXT.badgeShape) + '</span>' + bShape + '</label>' +
                '<label class="em-site-chip__badgeopt"><span>' + esc(TXT.badgeAnim) + '</span>' + bAnim + '</label>' +
                '<label class="em-site-chip__badgeopt"><span>' + esc(TXT.badgeRadius) + '</span><input type="number" class="em-site-chip__badgeradius" min="0" max="40" value="6"></label>';
        }
        if (type === 'icon') {
            return platformSelectHtml() +
                streamTargetSelectHtml() +
                '<input type="url" class="em-site-chip__url" placeholder="' + esc(TXT.link) + '">';
        }
        var input = '<input type="text" class="em-site-chip__value" placeholder="' + esc(TXT.content) + '">';
        if (type === 'text') {
            input += '<input type="url" class="em-site-chip__tlink" placeholder="' + esc(TXT.link) + '">';
        }
        if (TEXT_STYLE.indexOf(type) !== -1) { input += textStyleHtml(key); }
        return input;
    }

    function build(type, label) {
        var key = 'f' + Math.random().toString(36).slice(2, 9);
        var chip = document.createElement('div');
        chip.className = 'em-site-chip';
        chip.setAttribute('draggable', 'true');
        chip.setAttribute('data-key', key);
        chip.setAttribute('data-type', type);
        chip.setAttribute('data-hidden', '0');

        if (DECOR[type] !== undefined) {
            chip.classList.add('em-site-chip--decor');
            var colorPart = DECOR_COLOR.indexOf(type) !== -1 ? decorColorHtml(colorId('em-site-dec-')) : '';
            var urlPart = ARROW.indexOf(type) !== -1 ? '<input type="url" class="em-site-chip__url" placeholder="' + esc(TXT.anchor) + '">' : '';
            var heightPart = type === 'sep_blank' ? '<input type="number" class="em-site-chip__value em-site-chip__height" min="0" max="400" placeholder="' + esc(TXT.height) + '">' : '';
            chip.innerHTML =
                '<span class="em-site-chip__drag dashicons dashicons-move" aria-hidden="true"></span>' +
                '<span class="em-site-chip__type"><span class="em-site-chip__typeicon dashicons ' + esc(TYPE_ICONS[type] || 'dashicons-marker') + '" aria-hidden="true"></span>' + esc(DECOR[type]) + '</span>' +
                '<input type="hidden" class="em-site-chip__label" value="">' +
                colorPart + urlPart + heightPart + actionsHtml();
            chip.querySelector('.em-site-chip__remove').setAttribute('data-label', DECOR[type]);
            return chip;
        }

        var labelInput = (type === 'platform_block' || type === 'network_block' || type === 'animated_badge' || type === 'slider' || isTextFamily(type))
            ? '<input type="hidden" class="em-site-chip__label">'
            : '<input type="text" class="em-site-chip__label" placeholder="' + esc(TXT.label) + '">';
        chip.innerHTML =
            '<span class="em-site-chip__drag dashicons dashicons-move" aria-hidden="true"></span>' +
            '<span class="em-site-chip__type"><span class="em-site-chip__typeicon dashicons ' + esc(TYPE_ICONS[type] || 'dashicons-marker') + '" aria-hidden="true"></span>' + esc(TYPE_LABELS[type] || type) + '</span>' +
            '<span class="em-site-chip__fields">' +
                labelInput +
                valueHtml(type, key) +
            '</span>' +
            actionsHtml();
        chip.querySelector('.em-site-chip__label').value = label;
        chip.querySelector('.em-site-chip__remove').setAttribute('data-label', label);
        if (window.EmSiteDashiconChooser && typeof window.EmSiteDashiconChooser.init === 'function') {
            window.EmSiteDashiconChooser.init(chip);
        }
        return chip;
    }

    function openMedia(btn, update) {
        if (!window.wp || !window.wp.media) { return; }
        var chip = btn.closest('.em-site-chip');
        var type = chip.getAttribute('data-type');
        if (type === 'video_url') { return openThumb(chip, update); }
        if (type === 'video_file' || type === 'audio_file') { return openAv(chip, type, update); }
        var frame = window.wp.media({ title: TXT.image, multiple: false, library: { type: 'image' } });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var media = chip.querySelector('.em-site-chip__media');
            var hidden = chip.querySelector('.em-site-chip__value');
            var thumb = chip.querySelector('.em-site-chip__thumb');
            var sizes = att.sizes || {};
            hidden.value = att.id;
            media.setAttribute('data-url', (sizes.large ? sizes.large.url : att.url));
            if (thumb) { thumb.src = (sizes.medium ? sizes.medium.url : att.url); thumb.hidden = false; }
            var dot = chip.querySelector('.em-site-chip__focaldot');
            if (dot) { dot.hidden = false; }
            update();
        });
        frame.open();
    }

    // Point focal : positionne fx/fy (%) selon le clic sur la vignette.
    function setFocal(focal, e, update) {
        var img = focal.querySelector('.em-site-chip__thumb');
        if (!img || img.hidden) { return; }
        var r = img.getBoundingClientRect();
        if (!r.width || !r.height) { return; }
        var fx = Math.max(0, Math.min(100, Math.round((e.clientX - r.left) / r.width * 100)));
        var fy = Math.max(0, Math.min(100, Math.round((e.clientY - r.top) / r.height * 100)));
        var chip = focal.closest('.em-site-chip');
        var ix = chip.querySelector('.em-site-chip__fx'), iy = chip.querySelector('.em-site-chip__fy');
        var dot = focal.querySelector('.em-site-chip__focaldot');
        if (ix) { ix.value = fx; } if (iy) { iy.value = fy; }
        if (dot) { dot.hidden = false; dot.style.left = fx + '%'; dot.style.top = fy + '%'; }
        update();
    }

    // Lit le style de texte d'une chip (taille px, clé de police, couleur hex).
    function readStyle(chip) {
        var sz = chip.querySelector('.em-site-chip__tsize');
        var fn = chip.querySelector('.em-site-chip__tfont');
        var al = chip.querySelector('.em-site-chip__talign');
        var cl = chip.querySelector('.em-site-chip__tcolor');
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
        streamLinks: STREAM_LINKS,
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
