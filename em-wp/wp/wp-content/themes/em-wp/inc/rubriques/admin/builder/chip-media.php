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
 * Slider : bouton d'ajout d'images + vignettes + IDs cachés (JSON).
 */
function em_wp_v4_render_slider_value(string $value): void
{
    $ids = em_wp_rubrique_slider_value($value);
    ?>
    <span class="em-v4-chip__slider">
        <button type="button" class="button button-small em-v4-chip__pick"><?php esc_html_e('Ajouter des images', 'em-wp'); ?></button>
        <span class="em-v4-chip__slides">
            <?php foreach ($ids as $id) : ?>
                <?php $thumb = wp_get_attachment_image_url((int) $id, 'thumbnail'); ?>
                <?php if ($thumb) : ?>
                    <span class="em-v4-chip__slide" data-id="<?php echo (int) $id; ?>">
                        <img src="<?php echo esc_url($thumb); ?>" alt="">
                        <button type="button" class="em-v4-chip__slide-del" title="<?php esc_attr_e('Retirer', 'em-wp'); ?>">&times;</button>
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
        </span>
        <input type="hidden" class="em-v4-chip__value" value="<?php echo esc_attr($ids === [] ? '' : (string) wp_json_encode($ids)); ?>">
    </span>
    <?php
}
