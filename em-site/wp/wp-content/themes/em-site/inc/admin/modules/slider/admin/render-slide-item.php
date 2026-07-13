<?php
/**
 * Rendu des items de slide (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classes Font Awesome de l'icone d'un slide selon son type.
 */
function em_site_slider_slide_type_icon_classes(string $type): string
{
    switch (sanitize_key($type)) {
        case 'video':
            return 'fa-brands fa-youtube';
        case 'tiktok':
            return 'fa-brands fa-tiktok';
        case 'image':
        default:
            return 'fa-solid fa-image';
    }
}

/**
 * Rendu d'un item slide (liste dynamique).
 *
 * @param int|string $list_index Index dans slides[] ou __INDEX__ pour le template JS.
 * @param array<string, mixed> $context
 * @param array<string, mixed> $slide
 */
function em_site_slider_render_slide_item($list_index, array $context, array $slide, bool $is_template = false): void
{
    $index_token = $is_template ? '__INDEX__' : (string) $list_index;
    $option_name = $context['option_name'];
    $field_base = $option_name . '[slides][' . $index_token . ']';
    $uid = 'em-site-slider-slide-' . $index_token;

    $name_value = trim((string) ($slide['name'] ?? ''));
    $display_name = $name_value !== '' ? $name_value : __('Sans titre', 'em-site');
    $slide_type = sanitize_key((string) ($slide['type'] ?? 'image'));
    if (!in_array($slide_type, ['image', 'video', 'tiktok'], true)) {
        $slide_type = 'image';
    }

    $image_value = (string) ($slide['image'] ?? '');
    $video_url_value = (string) ($slide['video_url'] ?? '');
    $tiktok_url_value = (string) ($slide['tiktok_url'] ?? '');
    $tiktok_video_value = (string) ($slide['tiktok_video_url'] ?? '');
    $alt_text_value = (string) ($slide['alt_text'] ?? '');
    $duration_value = (string) ($slide['duration'] ?? '5');
    $is_hidden = !empty($slide['hidden']);

    $image_input_id = $uid . '-image';
    $image_preview_id = $image_input_id . '-preview';
    $tiktok_video_input_id = $uid . '-tiktok-video';
    $tiktok_video_preview_id = $tiktok_video_input_id . '-preview';
    ?>
    <details class="em-site-admin-nested-item em-site-slider-slide-item em-site-slider-item-panel" data-slide-item data-list-index="<?php echo esc_attr($index_token); ?>">
        <summary>
            <?php em_site_admin_render_panel_edit_trigger(); ?>
            <span class="em-site-slider-slide-item__label">
                <span class="em-site-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-hidden="true"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>"></i></span>
                <i class="em-site-slider-slide-item__type-icon <?php echo esc_attr(em_site_slider_slide_type_icon_classes($slide_type)); ?>" aria-hidden="true"></i>
                <span class="em-site-slider-slide-item__title"><?php echo esc_html($display_name); ?></span>
            </span>
        </summary>
        <div class="em-site-slider-slide-item__summary-actions">
            <span class="em-site-slider-slide-item__order">
                <button type="button" class="em-site-slider-slide-item__move em-site-slider-slide-item__move--up" aria-label="<?php esc_attr_e('Monter', 'em-site'); ?>" title="<?php esc_attr_e('Monter', 'em-site'); ?>"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i></button>
                <button type="button" class="em-site-slider-slide-item__move em-site-slider-slide-item__move--down" aria-label="<?php esc_attr_e('Descendre', 'em-site'); ?>" title="<?php esc_attr_e('Descendre', 'em-site'); ?>"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                <span class="em-site-slide-sortable__handle em-site-slider-slide-item__drag" role="button" tabindex="0" aria-label="<?php esc_attr_e('Glisser pour reordonner', 'em-site'); ?>" title="<?php esc_attr_e('Glisser pour reordonner', 'em-site'); ?>"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
            </span>
            <span class="em-site-slider-slide-item__sep" aria-hidden="true">|</span>
            <button type="button" class="em-site-slider-slide-item__delete" aria-label="<?php esc_attr_e('Supprimer ce slide', 'em-site'); ?>" title="<?php esc_attr_e('Supprimer ce slide', 'em-site'); ?>"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
        <div class="em-site-admin-nested-item__body em-site-slider-slide-item__body">
            <div class="em-site-admin-field-row em-site-slider-head-row">
                <span class="em-site-admin-field-group">
                    <span class="em-site-admin-field-group__label"><?php esc_html_e('Nom du slide', 'em-site'); ?></span>
                    <span class="em-site-slider-name-field">
                        <i class="fa-solid fa-pen-to-square em-site-slider-name-field__icon" aria-hidden="true"></i>
                        <input type="text" class="em-site-slider-text-input em-site-slider-name-field__input em-site-slider-slide-item__name-input" name="<?php echo esc_attr($field_base . '[name]'); ?>" value="<?php echo esc_attr($name_value); ?>">
                    </span>
                </span>
                <span class="em-site-admin-field-group">
                    <span class="em-site-admin-field-group__label"><?php esc_html_e('Type', 'em-site'); ?></span>
                    <select class="em-site-slider-select em-site-slider-item-panel__type" name="<?php echo esc_attr($field_base . '[type]'); ?>">
                        <option value="image" <?php selected($slide_type, 'image'); ?>><?php esc_html_e('Image', 'em-site'); ?></option>
                        <option value="video" <?php selected($slide_type, 'video'); ?>><?php esc_html_e('Video YouTube', 'em-site'); ?></option>
                        <option value="tiktok" <?php selected($slide_type, 'tiktok'); ?>><?php esc_html_e('Video TikTok', 'em-site'); ?></option>
                    </select>
                </span>
                <label class="em-site-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($field_base . '[hidden]'); ?>" value="1" <?php checked($is_hidden); ?>></label>
            </div>

            <div class="em-site-admin-field-row" data-slide-field="image">
                <span class="em-site-admin-field-group em-site-slider-field--grow">
                    <span class="em-site-admin-field-group__label"><?php esc_html_e('Image', 'em-site'); ?></span>
                    <input type="text" id="<?php echo esc_attr($image_input_id); ?>" name="<?php echo esc_attr($field_base . '[image]'); ?>" value="<?php echo esc_attr($image_value); ?>" class="em-site-slider-text-input">
                    <button type="button" class="button em-site-admin-media-button em-site-slider-media-button" data-target="<?php echo esc_attr($image_input_id); ?>" data-preview="<?php echo esc_attr($image_preview_id); ?>" data-modal-title="<?php echo esc_attr(sprintf(__('Choisir media pour %s', 'em-site'), $display_name)); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce media', 'em-site'); ?>"><?php esc_html_e('Modifier', 'em-site'); ?></button>
                </span>
            </div>
            <div id="<?php echo esc_attr($image_preview_id); ?>" class="em-site-admin-media-preview em-site-slider-preview<?php echo $image_value === '' ? ' is-empty' : ''; ?>"><?php if ($image_value !== '') { ?><img src="<?php echo esc_url($image_value); ?>" alt=""><?php } ?></div>

            <div class="em-site-admin-field-row" data-slide-field="video">
                <span class="em-site-admin-field-group em-site-slider-field--grow">
                    <span class="em-site-admin-field-group__label"><?php esc_html_e('URL YouTube', 'em-site'); ?></span>
                    <input type="url" class="em-site-slider-text-input" name="<?php echo esc_attr($field_base . '[video_url]'); ?>" value="<?php echo esc_attr($video_url_value); ?>" placeholder="https://www.youtube.com/watch?v=...">
                </span>
            </div>

            <div class="em-site-admin-field-row em-site-slider-inline-row" data-slide-field="tiktok">
                <span class="em-site-admin-field-group em-site-slider-field--grow">
                    <span class="em-site-admin-field-group__label"><?php esc_html_e('URL TikTok', 'em-site'); ?></span>
                    <input type="url" class="em-site-slider-text-input" name="<?php echo esc_attr($field_base . '[tiktok_url]'); ?>" value="<?php echo esc_attr($tiktok_url_value); ?>" placeholder="https://www.tiktok.com/@artist/video/...">
                </span>
                <span class="em-site-admin-field-group em-site-slider-field--grow">
                    <span class="em-site-admin-field-group__label"><?php esc_html_e('Video MP4', 'em-site'); ?></span>
                    <input type="text" id="<?php echo esc_attr($tiktok_video_input_id); ?>" name="<?php echo esc_attr($field_base . '[tiktok_video_url]'); ?>" value="<?php echo esc_attr($tiktok_video_value); ?>" class="em-site-slider-text-input">
                    <button type="button" class="button em-site-admin-media-button em-site-slider-media-button" data-target="<?php echo esc_attr($tiktok_video_input_id); ?>" data-preview="<?php echo esc_attr($tiktok_video_preview_id); ?>" data-modal-title="<?php echo esc_attr(sprintf(__('Choisir MP4 TikTok pour %s', 'em-site'), $display_name)); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce media', 'em-site'); ?>"><?php esc_html_e('Modifier', 'em-site'); ?></button>
                </span>
            </div>
            <div id="<?php echo esc_attr($tiktok_video_preview_id); ?>" class="em-site-admin-media-preview em-site-slider-preview<?php echo $tiktok_video_value === '' ? ' is-empty' : ''; ?>"><?php if ($tiktok_video_value !== '') { ?><video src="<?php echo esc_url($tiktok_video_value); ?>" controls muted preload="metadata"></video><?php } ?></div>

            <div class="em-site-admin-field-row" data-slide-field="alt">
                <span class="em-site-admin-field-group em-site-slider-field--grow">
                    <span class="em-site-admin-field-group__label"><?php esc_html_e('Texte Alt', 'em-site'); ?></span>
                    <input type="text" class="em-site-slider-text-input" name="<?php echo esc_attr($field_base . '[alt_text]'); ?>" value="<?php echo esc_attr($alt_text_value); ?>">
                </span>
            </div>

            <div class="em-site-admin-field-row" data-slide-field="duration">
                <span class="em-site-admin-field-group">
                    <span class="em-site-admin-field-group__label"><?php esc_html_e('Duree (secondes)', 'em-site'); ?></span>
                    <input type="number" min="1" step="1" class="em-site-slider-text-input em-site-slider-text-input--narrow" name="<?php echo esc_attr($field_base . '[duration]'); ?>" value="<?php echo esc_attr($duration_value); ?>">
                </span>
            </div>
        </div>
    </details>
    <?php
}