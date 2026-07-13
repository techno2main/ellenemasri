<?php
/**
 * Rendu HTML d'un champ de contenu selon son type (EM-SITE).
 *
 * Sépare la logique « un champ → HTML » du moteur de grille (item.php).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Style CSS d'une image (redimension + recadrage non destructif via object-fit).
 */
function em_site_rubrique_image_style(int $w, int $h, int $fx, int $fy): string
{
    $style = '';

    if ($w > 0) {
        $style .= 'width:' . $w . 'px;';
    }

    if ($h > 0) {
        $style .= 'height:' . $h . 'px;';
    }

    if ($w > 0 && $h > 0) {
        $style .= 'object-fit:cover;object-position:' . $fx . '% ' . $fy . '%;';
    }

    return $style;
}

/**
 * HTML d'une image (redimension/recadrage + lien éventuel). '' si aucune image.
 *
 * @param array{id:int,link:string,w:int,h:int,fx:int,fy:int} $img_data
 */
function em_site_rubrique_image_html(array $img_data, string $alt): string
{
    $url = $img_data['id'] ? wp_get_attachment_image_url((int) $img_data['id'], 'large') : '';

    if ($url === '') {
        return '';
    }

    $img_style = em_site_rubrique_image_style((int) $img_data['w'], (int) $img_data['h'], (int) $img_data['fx'], (int) $img_data['fy']);
    $img = '<img class="em-rubrique__image" src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '"' . ($img_style !== '' ? ' style="' . esc_attr($img_style) . '"' : '') . '>';

    if ($img_data['link'] !== '') {
        $img_target = strpos($img_data['link'], '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';
        $img = '<a class="em-rubrique__link em-rubrique__link--media" href="' . esc_url($img_data['link']) . '"' . $img_target . '>' . $img . '</a>';
    }

    if (!empty($img_data['tape'])) {
        $tape_style = '';
        if (!empty($img_data['tape_color'])) {
            $tape_style = ' style="background:' . esc_attr((string) $img_data['tape_color']) . ';"';
        }
        return '<span class="em-rubrique__imgwrap">'
            . '<span class="em-rubrique__tape em-rubrique__tape--left em-slider__tape em-slider__tape--left" aria-hidden="true"' . $tape_style . '></span>'
            . $img . '</span>';
    }

    return $img;
}

/**
 * Rend un champ de contenu selon son type.
 *
 * @param array<string, mixed> $field
 * @param mixed                $value
 */
function em_site_rubrique_item_field_html(array $field, $value): string
{
    $type = (string) $field['type'];
    $label = (string) $field['label'];
    $key = (string) $field['key'];

    switch ($type) {
        case 'sep_line':
            $sep_color = em_site_field_sanitize_color((string) $value);
            return '<hr class="em-rubrique__sep"' . ($sep_color !== '' ? ' style="border-color:' . esc_attr($sep_color) . '"' : '') . '>';

        case 'sep_blank':
            $blank_h = em_site_rubrique_sep_blank_height($value);
            return '<span class="em-rubrique__spacer" aria-hidden="true"' . ($blank_h > 0 ? ' style="display:block;height:' . $blank_h . 'px;"' : '') . '></span>';

        case 'arrow_up':
            return em_site_rubrique_arrow_html($value, 'up', '&uarr;');

        case 'arrow_down':
            return em_site_rubrique_arrow_html($value, 'down', '&darr;');

        case 'text':
            $tv = em_site_rubrique_text_value($value);
            if ($tv['text'] === '') {
                // Repli « valeur || libellé » (identique à l'aperçu builder JS) :
                // les anciens champs texte stockaient leur contenu dans le libellé
                // (avant le champ « Contenu » unique). Sans ce repli, ces textes
                // — titres « 01 / LISTEN », « AVAILABLE EVERYWHERE »… — disparaissent
                // du rendu PHP (front + aperçu squelette) alors qu'ils s'affichent
                // dans le builder. Les champs neufs ont un libellé vide : pas d'effet.
                $tv['text'] = $label;
            }
            if ($tv['text'] === '') {
                return '';
            }
            $text_style = em_site_rubrique_text_style_css((array) ($field['options']['style'] ?? []));
            $text_inner = em_site_rubrique_text_link_wrap($tv['text'], $tv['link']);
            return '<p class="em-rubrique__field em-rubrique__field--' . esc_attr($key) . '"' . ($text_style !== '' ? ' style="' . esc_attr($text_style) . '"' : '') . '>' . $text_inner . '</p>';

        case 'textarea':
            $tv = em_site_rubrique_text_value($value);
            if ($tv['text'] === '') {
                return '';
            }
            $text_style = em_site_rubrique_text_style_css((array) ($field['options']['style'] ?? []));
            $rich_html = em_site_rubrique_textarea_render_html((string) $tv['text']);
            if ($rich_html === '') {
                return '';
            }
            return '<div class="em-rubrique__field em-rubrique__field--rich em-rubrique__field--' . esc_attr($key) . '"' . ($text_style !== '' ? ' style="' . esc_attr($text_style) . '"' : '') . '>' . $rich_html . '</div>';

        case 'url':
            if ($value === '') {
                return '';
            }
            // Lien externe → nouvel onglet ; ancre interne (#) → même page.
            $target = strpos((string) $value, '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';
            return '<a class="em-rubrique__link" href="' . esc_url((string) $value) . '"' . $target . '>' . esc_html($label !== '' ? $label : (string) $value) . '</a>';

        case 'email':
            return $value === '' ? '' : '<a class="em-rubrique__link" href="' . esc_attr('mailto:' . sanitize_email((string) $value)) . '">' . esc_html((string) $value) . '</a>';

        case 'image':
            return em_site_rubrique_image_html(em_site_rubrique_image_value($value), $label);

        case 'text_image':
            $ti = em_site_rubrique_text_image_value($value);
            $ti_text = (string) $ti['text'];
            $ti_style = em_site_rubrique_text_style_css($ti['style']);
            $ti_text_html = $ti_text !== ''
                ? '<p class="em-rubrique__field"' . ($ti_style !== '' ? ' style="' . esc_attr($ti_style) . '"' : '') . '>' . em_site_rubrique_text_link_wrap($ti_text, (string) $ti['link']) . '</p>'
                : '';
            $ti_img_html = em_site_rubrique_image_html($ti['image'], $label);
            if ($ti_text_html === '' && $ti_img_html === '') {
                return '';
            }
            return '<div class="em-rubrique__textimg">' . $ti_text_html . $ti_img_html . '</div>';

        case 'text_text':
            $tt = em_site_rubrique_text_text_value($value);
            $tt1 = (string) $tt['text'];
            $tt2 = (string) $tt['text2'];
            $tt1_style = em_site_rubrique_text_style_css($tt['style']);
            $tt2_style = em_site_rubrique_text_style_css($tt['style2']);
            $tt1_html = $tt1 !== '' ? '<p class="em-rubrique__field"' . ($tt1_style !== '' ? ' style="' . esc_attr($tt1_style) . '"' : '') . '>' . em_site_rubrique_text_link_wrap($tt1, (string) $tt['link']) . '</p>' : '';
            $tt2_html = $tt2 !== '' ? '<p class="em-rubrique__field"' . ($tt2_style !== '' ? ' style="' . esc_attr($tt2_style) . '"' : '') . '>' . em_site_rubrique_text_link_wrap($tt2, (string) $tt['link2']) . '</p>' : '';
            if ($tt1_html === '' && $tt2_html === '') {
                return '';
            }
            return '<div class="em-rubrique__texttext">' . $tt1_html . $tt2_html . '</div>';

        case 'icon':
            $icon_data = em_site_rubrique_icon_value($value);
            $icon = $icon_data['platform'] !== '' ? em_site_rubrique_platform_icon($icon_data['platform']) : '';
            if ($icon === '') {
                return '';
            }
            $glyph = '<i class="em-rubrique__icon fa-brands ' . esc_attr($icon) . '" title="' . esc_attr($label) . '" aria-hidden="true"></i>';
            if ($icon_data['url'] === '') {
                return $glyph;
            }
            // Icône d'une plateforme STREAM (top-bar / footer) : système du site réel
            // — scroll vers #stream puis ouverture du player (stream.js). Sinon, lien
            // externe classique (réseaux sociaux, liens divers).
            if (strpos($icon_data['platform'], 'stream:') === 0) {
                $open_slug = function_exists('em_site_platform_stream_slug')
                    ? em_site_platform_stream_slug($icon_data['platform'])
                    : '';
                if ($open_slug !== '') {
                    return '<a class="em-rubrique__link em-rubrique__link--media top-bar-platform-link" href="' . esc_url($icon_data['url']) . '" data-open-platform="' . esc_attr($open_slug) . '">' . $glyph . '</a>';
                }
            }
            return '<a class="em-rubrique__link em-rubrique__link--media" href="' . esc_url($icon_data['url']) . '" target="_blank" rel="noopener noreferrer">' . $glyph . '</a>';

        case 'platform_block':
            return em_site_rubrique_platform_card_html(em_site_rubrique_platform_block_value($value));

        case 'network_block':
            return em_site_rubrique_network_card_html(em_site_rubrique_platform_block_value($value));

        case 'button':
            if ($label === '') {
                return '';
            }
            $btn = em_site_rubrique_button_value($value);
            $btn_style = '';
            if ($btn['bg'] !== '') {
                $btn_style .= 'background:' . $btn['bg'] . ';border-color:' . $btn['bg'] . ';';
            }
            if ($btn['text'] !== '') {
                $btn_style .= 'color:' . $btn['text'] . ';';
            }
            if ($btn['ml'] > 0) {
                $btn_style .= 'margin-left:' . $btn['ml'] . 'px;';
            }
            if ($btn['mr'] > 0) {
                $btn_style .= 'margin-right:' . $btn['mr'] . 'px;';
            }
            if ($btn['shape'] === 'square') {
                $btn_style .= '--em-rubrique-button-radius:' . $btn['radius'] . 'px;';
            }
            $btn_classes = ['em-rubrique__button', 'em-rubrique__button--shape-' . $btn['shape']];
            if ($btn['anim'] !== 'none') {
                $btn_classes[] = 'em-rubrique__button--anim-' . $btn['anim'];
            }
            $btn_href = $btn['link'] !== '' ? $btn['link'] : '#';
            $btn_target = ($btn['link'] !== '' && strpos($btn['link'], '#') !== 0) ? ' target="_blank" rel="noopener noreferrer"' : '';
            return '<a class="' . esc_attr(implode(' ', $btn_classes)) . '" href="' . esc_url($btn_href) . '"' . $btn_target . ($btn_style !== '' ? ' style="' . esc_attr($btn_style) . '"' : '') . '>' . esc_html($label) . '</a>';

        case 'animated_badge':
            return em_site_rubrique_animated_badge_html(em_site_rubrique_animated_badge_value($value));

        case 'video_url':
            $video_data = em_site_rubrique_video_url_value($value);
            $video_html = em_site_rubrique_video_url_html($video_data);
            if ($video_html === '') {
                return '';
            }
            $tape_style = '';
            if (!empty($video_data['tapes_color'])) {
                $tape_style = ' style="background:' . esc_attr((string) $video_data['tapes_color']) . ';"';
            }
            $left_tape = !empty($video_data['tapes_hidden']) ? '' : '<span class="em-rubrique__tape em-rubrique__tape--left em-slider__tape em-slider__tape--left" aria-hidden="true"' . $tape_style . '></span>';
            $right_tape = !empty($video_data['tapes_hidden']) ? '' : '<span class="em-rubrique__tape em-rubrique__tape--right em-slider__tape em-slider__tape--right" aria-hidden="true"' . $tape_style . '></span>';
            return '<span class="em-rubrique__videowrap">'
                . $left_tape
                . $right_tape
                . $video_html . '</span>';

        case 'video_file':
            return em_site_rubrique_video_file_html(em_site_rubrique_media_id_value($value));

        case 'audio_file':
            $audio_id = em_site_rubrique_media_id_value($value);
            return em_site_rubrique_audio_html($audio_id > 0 ? (string) wp_get_attachment_url($audio_id) : '');

        case 'audio_url':
            return em_site_rubrique_audio_html((string) $value);

        case 'slider':
            return em_site_rubrique_slides_front_html(em_site_rubrique_slides_config($value));

        case 'toggle':
            return '<span class="em-rubrique__chip">' . esc_html($label) . ' : ' . ($value ? esc_html__('oui', 'em-site') : esc_html__('non', 'em-site')) . '</span>';

        case 'color':
            return $value === '' ? '' : '<span class="em-rubrique__swatch" style="background:' . esc_attr((string) $value) . '" title="' . esc_attr($label) . '"></span>';

        case 'number':
        case 'select':
        default:
            return $value === '' ? '' : '<span class="em-rubrique__field">' . esc_html((string) $value) . '</span>';
    }
}

/**
 * HTML d'une flèche de navigation : glyphe coloré, ancre/URL optionnelle.
 *
 * @param mixed $value
 */
function em_site_rubrique_arrow_html($value, string $dir, string $glyph): string
{
    $arrow = em_site_rubrique_arrow_value($value);
    $style = $arrow['color'] !== '' ? ' style="color:' . esc_attr($arrow['color']) . '"' : '';
    $span = '<span class="em-rubrique__arrow em-rubrique__arrow--' . esc_attr($dir) . '" aria-hidden="true"' . $style . '>' . $glyph . '</span>';

    if ($arrow['link'] === '') {
        return $span;
    }

    // Ancre interne (#section) → même page ; lien externe → nouvel onglet.
    $target = strpos($arrow['link'], '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';

    return '<a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="' . esc_url($arrow['link']) . '"' . $target . $style . '>' . $span . '</a>';
}
