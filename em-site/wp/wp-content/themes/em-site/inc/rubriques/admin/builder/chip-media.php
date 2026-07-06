<?php
/**
 * Rendu serveur des contrôles de valeur pour les champs média (V4) :
 * vidéo (URL / fichier), son (URL / fichier), bloc réseau et slider.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rend le contrôle de valeur d'un champ média. @return bool true si pris en charge.
 */
function em_wp_v4_render_chip_media_value(string $type, string $value): bool
{
    switch ($type) {
        case 'video_url':
            em_wp_v4_render_video_url_value($value);
            return true;

        case 'audio_url':
            em_wp_v4_render_url_value($value, __('URL du fichier audio…', 'em-wp'));
            return true;

        case 'video_file':
            em_wp_v4_render_av_value($value, 'video', __('Choisir une vidéo', 'em-wp'));
            return true;

        case 'audio_file':
            em_wp_v4_render_av_value($value, 'audio', __('Choisir un son', 'em-wp'));
            return true;

        case 'network_block':
            em_wp_v4_render_network_value($value);
            return true;

        case 'slider':
            em_wp_v4_render_slider_value($value);
            return true;
    }

    return false;
}

/**
 * Champ URL simple (vidéo/son).
 */
function em_wp_v4_render_url_value(string $value, string $placeholder): void
{
    ?>
    <input type="url" class="em-v4-chip__value" value="<?php echo esc_url($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>">
    <?php
}

/**
 * Champ « Vidéo URL » : URL + miniature (média image) + lien cliquable.
 */
function em_wp_v4_render_video_url_value(string $value): void
{
    $v = em_wp_rubrique_video_url_value($value);
    $thumb_url = $v['thumb'] ? (string) wp_get_attachment_image_url($v['thumb'], 'medium') : '';
    ?>
    <input type="url" class="em-v4-chip__vurl" value="<?php echo esc_url($v['url']); ?>" placeholder="<?php esc_attr_e('URL YouTube ou TikTok…', 'em-wp'); ?>">
    <span class="em-v4-chip__media em-v4-chip__media--av em-v4-chip__vthumb" data-url="<?php echo esc_attr($thumb_url); ?>" data-mtype="image">
        <button type="button" class="button button-small em-v4-chip__pick"><?php esc_html_e('Choisir une miniature', 'em-wp'); ?></button>
        <span class="em-v4-chip__medianame"><?php echo $thumb_url ? esc_html(wp_basename($thumb_url)) : ''; ?></span>
        <input type="hidden" class="em-v4-chip__thumbid" value="<?php echo $v['thumb'] ? (int) $v['thumb'] : ''; ?>">
    </span>
    <label class="em-v4-chip__check" title="<?php esc_attr_e('Coché : lien actif. Décoché : pas de lien.', 'em-wp'); ?>">
        <input type="checkbox" class="em-v4-chip__clickable" <?php checked($v['clickable']); ?>>
        <?php esc_html_e('Lien cliquable', 'em-wp'); ?>
    </label>
    <?php
}

/**
 * Sélecteur de média audio/vidéo (médiathèque) : bouton + nom + id caché.
 */
function em_wp_v4_render_av_value(string $value, string $mtype, string $btn): void
{
    $id = em_wp_rubrique_media_id_value($value);
    $url = $id > 0 ? wp_get_attachment_url($id) : '';
    $name = $url ? wp_basename($url) : '';
    ?>
    <span class="em-v4-chip__media em-v4-chip__media--av" data-url="<?php echo esc_attr((string) $url); ?>" data-mtype="<?php echo esc_attr($mtype); ?>">
        <button type="button" class="button button-small em-v4-chip__pick"><?php echo esc_html($btn); ?></button>
        <span class="em-v4-chip__medianame"><?php echo esc_html($name); ?></span>
        <input type="hidden" class="em-v4-chip__value" value="<?php echo esc_attr($id > 0 ? (string) $id : ''); ?>">
    </span>
    <?php
}

/**
 * Bloc Réseau : sur-titre + réseau (TikTok/Insta/YouTube) + lien.
 */
function em_wp_v4_render_network_value(string $value): void
{
    $block = em_wp_rubrique_platform_block_value($value);
    ?>
    <input type="text" class="em-v4-chip__ptitle" value="<?php echo esc_attr($block['label']); ?>" placeholder="<?php esc_attr_e('Titre (ex. FOLLOW)', 'em-wp'); ?>" title="<?php esc_attr_e('Sur-titre de la carte', 'em-wp'); ?>">
    <?php em_wp_v4_render_network_select($block['platform']); ?>
    <input type="url" class="em-v4-chip__url" value="<?php echo esc_url($block['url']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
    <input type="text" class="em-v4-chip__paccount" value="<?php echo esc_attr($block['account']); ?>" placeholder="<?php esc_attr_e('Pseudo (ex. @ellenemasri)', 'em-wp'); ?>" title="<?php esc_attr_e('Pseudo affiché sur la carte', 'em-wp'); ?>">
    <?php
}

/**
 * Liste déroulante des réseaux sociaux (icône + libellé).
 */
function em_wp_v4_render_network_select(string $selected): void
{
    ?>
    <select class="em-v4-chip__platform">
        <option value=""><?php esc_html_e('— Choisir —', 'em-wp'); ?></option>
        <?php foreach (em_wp_rubrique_network_choices() as $pkey => $choice) : ?>
            <option value="<?php echo esc_attr($pkey); ?>" data-icon="<?php echo esc_attr($choice['icon']); ?>" data-color="<?php echo esc_attr((string) ($choice['color'] ?? '')); ?>" data-label="<?php echo esc_attr($choice['label']); ?>" <?php selected($selected, $pkey); ?>><?php echo esc_html($choice['label']); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Slider V4 : bandeau titre (texte + couleurs) + liste de slides riches
 * (image / TikTok / vidéo YouTube). La valeur complète est sérialisée en JSON
 * dans l'input caché par EmWpV4Slides.
 */
function em_wp_v4_render_slider_value(string $value): void
{
    $cfg = em_wp_rubrique_slides_config($value);
    $frame_id = 'em-v4-slides-frame-' . wp_unique_id();
    $border_id = 'em-v4-slides-border-' . wp_unique_id();
    $shadow_id = 'em-v4-slides-shadow-' . wp_unique_id();
    $footer_bg_id = 'em-v4-slides-footerbg-' . wp_unique_id();
    $footer_text_id = 'em-v4-slides-footertext-' . wp_unique_id();
    $tapes_id = 'em-v4-slides-tapes-' . wp_unique_id();
    $frame = $cfg['frame_bg'] !== '' ? $cfg['frame_bg'] : '#12338f';
    $border_color = $cfg['border_color'] !== '' ? $cfg['border_color'] : '#100421';
    $shadow_color = $cfg['shadow_color'] !== '' ? $cfg['shadow_color'] : '#100421';
    $footer_bg = $cfg['footer_bg'] !== '' ? $cfg['footer_bg'] : '#f2ebd1';
    $footer_text = $cfg['footer_text'] !== '' ? $cfg['footer_text'] : '#100421';
    $tapes_color = $cfg['tapes_color'] !== '' ? $cfg['tapes_color'] : '#39c7ca';
    ?>
    <span class="em-v4-slides">
        <span class="em-v4-slides__section-title"><?php esc_html_e('Style du Slider', 'em-wp'); ?></span>
        <span class="em-v4-slides__opts em-v4-slides__opts--row1">
            <span class="em-v4-slides__titlegroup">
                <label class="em-v4-slides__opt em-v4-slides__opt--title">
                    <span><?php esc_html_e('Titre', 'em-wp'); ?></span>
                    <input type="text" class="em-v4-slides__title" value="<?php echo esc_attr($cfg['title']); ?>" placeholder="<?php esc_attr_e('Mayami, My Miami', 'em-wp'); ?>">
                </label>
                <label class="em-v4-slides__opt em-v4-slides__opt--check">
                    <input type="checkbox" class="em-v4-slides__title-hidden" <?php checked($cfg['title_hidden']); ?>>
                    <?php esc_html_e('Masquer le titre', 'em-wp'); ?>
                </label>
            </span>
            <?php em_wp_admin_render_color_field([
                'id'          => $footer_bg_id,
                'value'       => $footer_bg,
                'field_label' => __('Bandeau', 'em-wp'),
                'wrap_class'  => 'em-v4-slides__colorfield',
                'input_class' => 'em-v4-slides__footerbg',
            ]); ?>
            <?php em_wp_admin_render_color_field([
                'id'           => $footer_text_id,
                'value'        => $footer_text,
                'field_label'  => __('Texte titre', 'em-wp'),
                'wrap_class'   => 'em-v4-slides__colorfield',
                'input_class'  => 'em-v4-slides__footertext em-v4-slides__footertext-text',
                'preview_type' => 'text',
                'bg_target_id' => $footer_bg_id,
            ]); ?>
            <input type="hidden" class="em-v4-slides__frame" value="<?php echo esc_attr($frame); ?>">
            <?php em_wp_admin_render_color_field([
                'id'          => $border_id,
                'value'       => $border_color,
                'field_label' => __('Bordure', 'em-wp'),
                'wrap_class'  => 'em-v4-slides__colorfield',
                'input_class' => 'em-v4-slides__border-color',
            ]); ?>
            <?php em_wp_admin_render_color_field([
                'id'          => $shadow_id,
                'value'       => $shadow_color,
                'field_label' => __('Ombre', 'em-wp'),
                'wrap_class'  => 'em-v4-slides__colorfield',
                'input_class' => 'em-v4-slides__shadow-color',
            ]); ?>
            <?php em_wp_admin_render_color_field([
                'id'          => $tapes_id,
                'value'       => $tapes_color,
                'field_label' => __('Scotch', 'em-wp'),
                'wrap_class'  => 'em-v4-slides__colorfield',
                'input_class' => 'em-v4-slides__tapes-color',
            ]); ?>
            <label class="em-v4-slides__opt em-v4-slides__opt--check em-v4-slides__opt--check-tapes">
                <input type="checkbox" class="em-v4-slides__tapes-hidden" <?php checked($cfg['tapes_hidden']); ?>>
                <?php esc_html_e('Masquer les scotchs', 'em-wp'); ?>
            </label>
        </span>
        <span class="em-v4-slides__group-label em-v4-slides__slides-label"><?php esc_html_e('Slides', 'em-wp'); ?></span>
        <span class="em-v4-slides__list">
            <?php foreach ($cfg['slides'] as $slide) {
                echo em_wp_v4_slide_row_html($slide); // déjà échappé
            } ?>
        </span>
        <button type="button" class="button button-small em-v4-slides__add"><?php esc_html_e('+ Ajouter un slide', 'em-wp'); ?></button>
        <input type="hidden" class="em-v4-chip__value" value="<?php echo esc_attr($value); ?>">
    </span>
    <?php
}

/**
 * Une ligne de slide dans l'éditeur (miroir HTML de EmWpV4Chip.slideRowHtml).
 *
 * @param array<string, mixed> $slide
 */
function em_wp_v4_slide_row_html(array $slide): string
{
    $slide = em_wp_rubrique_slide_normalize($slide);
    $type = $slide['type'];
    $thumb = $slide['image'];
    $ttvid_name = $slide['tiktok_video_url'] !== '' ? wp_basename($slide['tiktok_video_url']) : '';

    ob_start();
    ?>
    <span class="em-v4-slide<?php echo $slide['hidden'] ? ' is-hidden' : ''; ?>" data-type="<?php echo esc_attr($type); ?>">
        <span class="em-v4-slide__move">
            <button type="button" class="em-v4-slide__up" title="<?php esc_attr_e('Monter', 'em-wp'); ?>">&#9650;</button>
            <button type="button" class="em-v4-slide__down" title="<?php esc_attr_e('Descendre', 'em-wp'); ?>">&#9660;</button>
        </span>
        <select class="em-v4-slide__type">
            <option value="image" <?php selected($type, 'image'); ?>><?php esc_html_e('Image', 'em-wp'); ?></option>
            <option value="tiktok" <?php selected($type, 'tiktok'); ?>>TikTok</option>
            <option value="video" <?php selected($type, 'video'); ?>><?php esc_html_e('Vidéo YouTube', 'em-wp'); ?></option>
        </select>
        <span class="em-v4-slide__media em-v4-slide__media--image">
            <img class="em-v4-slide__thumb" src="<?php echo esc_url($thumb); ?>" alt="" <?php echo $thumb === '' ? 'hidden' : ''; ?>>
            <button type="button" class="button button-small em-v4-slide__pick" data-target="image"><?php esc_html_e('Image', 'em-wp'); ?></button>
            <input type="hidden" class="em-v4-slide__image" value="<?php echo esc_url($slide['image']); ?>">
        </span>
        <input type="url" class="em-v4-slide__videourl" value="<?php echo esc_url($slide['video_url']); ?>" placeholder="<?php esc_attr_e('URL YouTube', 'em-wp'); ?>">
        <input type="url" class="em-v4-slide__tiktokurl" value="<?php echo esc_url($slide['tiktok_url']); ?>" placeholder="<?php esc_attr_e('URL TikTok', 'em-wp'); ?>">
        <span class="em-v4-slide__media em-v4-slide__media--ttvid">
            <button type="button" class="button button-small em-v4-slide__pick" data-target="ttvid"><?php esc_html_e('Vidéo fichier', 'em-wp'); ?></button>
            <span class="em-v4-slide__medianame"><?php echo esc_html($ttvid_name); ?></span>
            <input type="hidden" class="em-v4-slide__tiktokvideo" value="<?php echo esc_url($slide['tiktok_video_url']); ?>">
        </span>
        <input type="text" class="em-v4-slide__name" value="<?php echo esc_attr($slide['name']); ?>" placeholder="<?php esc_attr_e('Nom', 'em-wp'); ?>">
        <input type="number" class="em-v4-slide__duration" min="1" value="<?php echo (int) $slide['duration']; ?>" title="<?php esc_attr_e('Durée (s)', 'em-wp'); ?>">
        <button type="button" class="em-v4-slide__eye" data-hidden="<?php echo $slide['hidden'] ? '1' : '0'; ?>" title="<?php esc_attr_e('Afficher / masquer le slide', 'em-wp'); ?>">
            <span class="dashicons dashicons-<?php echo $slide['hidden'] ? 'hidden' : 'visibility'; ?>" aria-hidden="true"></span>
        </button>
        <button type="button" class="em-v4-slide__del" title="<?php esc_attr_e('Supprimer le slide', 'em-wp'); ?>">&times;</button>
    </span>
    <?php
    return (string) ob_get_clean();
}
