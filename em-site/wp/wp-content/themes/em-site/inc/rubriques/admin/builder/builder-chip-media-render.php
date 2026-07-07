<?php
/**
 * Rendu serveur des contrôles de valeur pour les champs média (EM-SITE) :
 * vidéo (URL / fichier), son (URL / fichier), bloc réseau et slider.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rend le contrôle de valeur d'un champ média. @return bool true si pris en charge.
 */
function em_site_render_chip_media_value(string $type, string $value): bool
{
    switch ($type) {
        case 'video_url':
            em_site_render_video_url_value($value);
            return true;

        case 'audio_url':
            em_site_render_url_value($value, __('URL du fichier audio…', 'em-site'));
            return true;

        case 'video_file':
            em_site_render_av_value($value, 'video', __('Choisir une vidéo', 'em-site'));
            return true;

        case 'audio_file':
            em_site_render_av_value($value, 'audio', __('Choisir un son', 'em-site'));
            return true;

        case 'network_block':
            em_site_render_network_value($value);
            return true;

        case 'slider':
            em_site_render_slider_value($value);
            return true;
    }

    return false;
}

/**
 * Champ URL simple (vidéo/son).
 */
function em_site_render_url_value(string $value, string $placeholder): void
{
    ?>
    <input type="url" class="em-site-chip__value" value="<?php echo esc_url($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>">
    <?php
}

/**
 * Champ « Vidéo URL » : URL + miniature (média image) + lien cliquable.
 */
function em_site_render_video_url_value(string $value): void
{
    $v = em_site_rubrique_video_url_value($value);
    $thumb_url = $v['thumb'] ? (string) wp_get_attachment_image_url($v['thumb'], 'medium') : '';
    ?>
    <input type="url" class="em-site-chip__vurl" value="<?php echo esc_url($v['url']); ?>" placeholder="<?php esc_attr_e('URL YouTube ou TikTok…', 'em-site'); ?>">
    <span class="em-site-chip__media em-site-chip__media--av em-site-chip__vthumb" data-url="<?php echo esc_attr($thumb_url); ?>" data-mtype="image">
        <button type="button" class="button button-small em-site-chip__pick"><?php esc_html_e('Choisir une miniature', 'em-site'); ?></button>
        <span class="em-site-chip__medianame"><?php echo $thumb_url ? esc_html(wp_basename($thumb_url)) : ''; ?></span>
        <input type="hidden" class="em-site-chip__thumbid" value="<?php echo $v['thumb'] ? (int) $v['thumb'] : ''; ?>">
    </span>
    <label class="em-site-chip__check" title="<?php esc_attr_e('Coché : lien actif. Décoché : pas de lien.', 'em-site'); ?>">
        <input type="checkbox" class="em-site-chip__clickable" <?php checked($v['clickable']); ?>>
        <?php esc_html_e('Lien cliquable', 'em-site'); ?>
    </label>
    <?php em_site_render_scotchs_component([
        'hidden_class' => 'em-site-chip__vtapes-hidden',
        'hidden_checked' => !empty($v['tapes_hidden']),
        'hidden_title' => __('Masquer les scotchs décoratifs de la vidéo', 'em-site'),
        'color_class' => 'em-site-chip__vtapes-color',
        'color_value' => (string) ($v['tapes_color'] ?? ''),
        'color_prefix' => 'emv4vtp-',
    ]); ?>
    <?php
}

/**
 * Sélecteur de média audio/vidéo (médiathèque) : bouton + nom + id caché.
 */
function em_site_render_av_value(string $value, string $mtype, string $btn): void
{
    $id = em_site_rubrique_media_id_value($value);
    $url = $id > 0 ? wp_get_attachment_url($id) : '';
    $name = $url ? wp_basename($url) : '';
    ?>
    <span class="em-site-chip__media em-site-chip__media--av" data-url="<?php echo esc_attr((string) $url); ?>" data-mtype="<?php echo esc_attr($mtype); ?>">
        <button type="button" class="button button-small em-site-chip__pick"><?php echo esc_html($btn); ?></button>
        <span class="em-site-chip__medianame"><?php echo esc_html($name); ?></span>
        <input type="hidden" class="em-site-chip__value" value="<?php echo esc_attr($id > 0 ? (string) $id : ''); ?>">
    </span>
    <?php
}

/**
 * Bloc Réseau : sur-titre + réseau (TikTok/Insta/YouTube) + lien.
 */
function em_site_render_network_value(string $value): void
{
    $block = em_site_rubrique_platform_block_value($value);
    ?>
    <input type="text" class="em-site-chip__ptitle" value="<?php echo esc_attr($block['label']); ?>" placeholder="<?php esc_attr_e('Titre (ex. FOLLOW)', 'em-site'); ?>" title="<?php esc_attr_e('Sur-titre de la carte', 'em-site'); ?>">
    <?php em_site_render_network_select($block['platform']); ?>
    <input type="url" class="em-site-chip__url" value="<?php echo esc_url($block['url']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-site'); ?>">
    <input type="text" class="em-site-chip__paccount" value="<?php echo esc_attr($block['account']); ?>" placeholder="<?php esc_attr_e('Pseudo (ex. @ellenemasri)', 'em-site'); ?>" title="<?php esc_attr_e('Pseudo affiché sur la carte', 'em-site'); ?>">
    <?php
}

/**
 * Liste déroulante des réseaux sociaux (icône + libellé).
 */
function em_site_render_network_select(string $selected): void
{
    ?>
    <select class="em-site-chip__platform">
        <option value=""><?php esc_html_e('— Choisir —', 'em-site'); ?></option>
        <?php foreach (em_site_rubrique_network_choices() as $pkey => $choice) : ?>
            <option value="<?php echo esc_attr($pkey); ?>" data-icon="<?php echo esc_attr($choice['icon']); ?>" data-color="<?php echo esc_attr((string) ($choice['color'] ?? '')); ?>" data-label="<?php echo esc_attr($choice['label']); ?>" <?php selected($selected, $pkey); ?>><?php echo esc_html($choice['label']); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Slider EM-SITE : bandeau titre (texte + couleurs) + liste de slides riches
 * (image / TikTok / vidéo YouTube). La valeur complète est sérialisée en JSON
 * dans l'input caché par EmSiteSlides.
 */
function em_site_render_slider_value(string $value): void
{
    $cfg = em_site_rubrique_slides_config($value);
    $frame_id = 'em-site-slides-frame-' . wp_unique_id();
    $border_id = 'em-site-slides-border-' . wp_unique_id();
    $shadow_id = 'em-site-slides-shadow-' . wp_unique_id();
    $footer_bg_id = 'em-site-slides-footerbg-' . wp_unique_id();
    $footer_text_id = 'em-site-slides-footertext-' . wp_unique_id();
    $frame = $cfg['frame_bg'] !== '' ? $cfg['frame_bg'] : '#12338f';
    $border_color = $cfg['border_color'] !== '' ? $cfg['border_color'] : '#100421';
    $shadow_color = $cfg['shadow_color'] !== '' ? $cfg['shadow_color'] : '#100421';
    $footer_bg = $cfg['footer_bg'] !== '' ? $cfg['footer_bg'] : '#f2ebd1';
    $footer_text = $cfg['footer_text'] !== '' ? $cfg['footer_text'] : '#100421';
    $tapes_color = $cfg['tapes_color'] !== '' ? $cfg['tapes_color'] : '#39c7ca';
    ?>
    <span class="em-site-slides">
        <span class="em-site-slides__section-title"><?php esc_html_e('Style du Slider', 'em-site'); ?></span>
        <span class="em-site-slides__opts em-site-slides__opts--row1">
            <span class="em-site-slides__titlegroup">
                <label class="em-site-slides__opt em-site-slides__opt--title">
                    <span><?php esc_html_e('Titre', 'em-site'); ?></span>
                    <input type="text" class="em-site-slides__title" value="<?php echo esc_attr($cfg['title']); ?>" placeholder="<?php esc_attr_e('Mayami, My Miami', 'em-site'); ?>">
                </label>
                <label class="em-site-slides__opt em-site-slides__opt--check">
                    <input type="checkbox" class="em-site-slides__title-hidden" <?php checked($cfg['title_hidden']); ?>>
                    <?php esc_html_e('Masquer le bandeau', 'em-site'); ?>
                </label>
            </span>
            <?php em_site_admin_render_color_field([
                'id'          => $footer_bg_id,
                'value'       => $footer_bg,
                'field_label' => __('Bandeau', 'em-site'),
                'wrap_class'  => 'em-site-slides__colorfield',
                'input_class' => 'em-site-slides__footerbg',
            ]); ?>
            <?php em_site_admin_render_color_field([
                'id'           => $footer_text_id,
                'value'        => $footer_text,
                'field_label'  => __('Texte titre', 'em-site'),
                'wrap_class'   => 'em-site-slides__colorfield',
                'input_class'  => 'em-site-slides__footertext em-site-slides__footertext-text',
                'preview_type' => 'text',
                'bg_target_id' => $footer_bg_id,
            ]); ?>
            <input type="hidden" class="em-site-slides__frame" value="<?php echo esc_attr($frame); ?>">
            <?php em_site_admin_render_color_field([
                'id'          => $border_id,
                'value'       => $border_color,
                'field_label' => __('Bordure', 'em-site'),
                'wrap_class'  => 'em-site-slides__colorfield',
                'input_class' => 'em-site-slides__border-color',
            ]); ?>
            <?php em_site_admin_render_color_field([
                'id'          => $shadow_id,
                'value'       => $shadow_color,
                'field_label' => __('Ombre', 'em-site'),
                'wrap_class'  => 'em-site-slides__colorfield',
                'input_class' => 'em-site-slides__shadow-color',
            ]); ?>
            <?php em_site_render_scotchs_component([
                'hidden_class' => 'em-site-slides__tapes-hidden',
                'hidden_checked' => !empty($cfg['tapes_hidden']),
                'hidden_wrap_class' => 'em-site-slides__opt em-site-slides__opt--check em-site-slides__opt--check-tapes',
                'color_class' => 'em-site-slides__tapes-color',
                'color_value' => $tapes_color,
                'color_wrap_class' => 'em-site-slides__colorfield',
                'color_prefix' => 'emv4sls-',
            ]); ?>
        </span>
        <span class="em-site-slides__group-label em-site-slides__slides-label"><?php esc_html_e('Slides', 'em-site'); ?></span>
        <span class="em-site-slides__list">
            <?php foreach ($cfg['slides'] as $slide) {
                echo em_site_slide_row_html($slide); // déjà échappé
            } ?>
        </span>
        <button type="button" class="button button-small em-site-slides__add"><?php esc_html_e('+ Ajouter un slide', 'em-site'); ?></button>
        <input type="hidden" class="em-site-chip__value" value="<?php echo esc_attr($value); ?>">
    </span>
    <?php
}

/**
 * Une ligne de slide dans l'éditeur (miroir HTML de EmSiteChip.slideRowHtml).
 *
 * @param array<string, mixed> $slide
 */
function em_site_slide_row_html(array $slide): string
{
    $slide = em_site_rubrique_slide_normalize($slide);
    $type = $slide['type'];
    $thumb = $slide['image'];
    $ttvid_name = $slide['tiktok_video_url'] !== '' ? wp_basename($slide['tiktok_video_url']) : '';

    ob_start();
    ?>
    <span class="em-site-slide<?php echo $slide['hidden'] ? ' is-hidden' : ''; ?>" data-type="<?php echo esc_attr($type); ?>">
        <span class="em-site-slide__move">
            <button type="button" class="em-site-slide__up" title="<?php esc_attr_e('Monter', 'em-site'); ?>">&#9650;</button>
            <button type="button" class="em-site-slide__down" title="<?php esc_attr_e('Descendre', 'em-site'); ?>">&#9660;</button>
        </span>
        <select class="em-site-slide__type">
            <option value="image" <?php selected($type, 'image'); ?>><?php esc_html_e('Image', 'em-site'); ?></option>
            <option value="tiktok" <?php selected($type, 'tiktok'); ?>>TikTok</option>
            <option value="video" <?php selected($type, 'video'); ?>><?php esc_html_e('Vidéo YouTube', 'em-site'); ?></option>
        </select>
        <span class="em-site-slide__media em-site-slide__media--image">
            <img class="em-site-slide__thumb" src="<?php echo esc_url($thumb); ?>" alt="" <?php echo $thumb === '' ? 'hidden' : ''; ?>>
            <button type="button" class="button button-small em-site-slide__pick" data-target="image"><?php esc_html_e('Image', 'em-site'); ?></button>
            <input type="hidden" class="em-site-slide__image" value="<?php echo esc_url($slide['image']); ?>">
        </span>
        <input type="url" class="em-site-slide__videourl" value="<?php echo esc_url($slide['video_url']); ?>" placeholder="<?php esc_attr_e('URL YouTube', 'em-site'); ?>">
        <input type="url" class="em-site-slide__tiktokurl" value="<?php echo esc_url($slide['tiktok_url']); ?>" placeholder="<?php esc_attr_e('URL TikTok', 'em-site'); ?>">
        <span class="em-site-slide__media em-site-slide__media--ttvid">
            <button type="button" class="button button-small em-site-slide__pick" data-target="ttvid"><?php esc_html_e('Vidéo fichier', 'em-site'); ?></button>
            <span class="em-site-slide__medianame"><?php echo esc_html($ttvid_name); ?></span>
            <input type="hidden" class="em-site-slide__tiktokvideo" value="<?php echo esc_url($slide['tiktok_video_url']); ?>">
        </span>
        <input type="text" class="em-site-slide__name" value="<?php echo esc_attr($slide['name']); ?>" placeholder="<?php esc_attr_e('Nom', 'em-site'); ?>">
        <input type="number" class="em-site-slide__duration" min="1" value="<?php echo (int) $slide['duration']; ?>" title="<?php esc_attr_e('Durée (s)', 'em-site'); ?>">
        <button type="button" class="em-site-slide__eye" data-hidden="<?php echo $slide['hidden'] ? '1' : '0'; ?>" title="<?php esc_attr_e('Afficher / masquer le slide', 'em-site'); ?>">
            <span class="dashicons dashicons-<?php echo $slide['hidden'] ? 'hidden' : 'visibility'; ?>" aria-hidden="true"></span>
        </button>
        <button type="button" class="em-site-slide__del" title="<?php esc_attr_e('Supprimer le slide', 'em-site'); ?>">&times;</button>
    </span>
    <?php
    return (string) ob_get_clean();
}
