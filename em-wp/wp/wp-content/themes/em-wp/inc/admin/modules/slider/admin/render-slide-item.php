<?php
/**
 * Rendu des items de slide (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classes Font Awesome de l'icone d'un slide selon son type.
 */
function em_wp_slider_slide_type_icon_classes(string $type): string
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
function em_wp_slider_render_slide_item($list_index, array $context, array $slide, bool $is_template = false): void
{
    $index_token = $is_template ? '__INDEX__' : (string) $list_index;
    $option_name = $context['option_name'];
    $field_base = $option_name . '[slides][' . $index_token . ']';
    $uid = 'em-wp-slider-slide-' . $index_token;

    $name_value = trim((string) ($slide['name'] ?? ''));
    $display_name = $name_value !== '' ? $name_value : __('Sans titre', 'em-wp');
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
    <details class="em-wp-admin-nested-item em-wp-slider-slide-item em-wp-slider-item-panel" data-slide-item data-list-index="<?php echo esc_attr($index_token); ?>">
        <summary>
            <?php em_wp_admin_render_panel_edit_trigger(); ?>
            <span class="em-wp-slider-slide-item__label">
                <span class="em-wp-slider-slide-item__order">
                    <button type="button" class="em-wp-slider-slide-item__move em-wp-slider-slide-item__move--up" aria-label="<?php esc_attr_e('Monter', 'em-wp'); ?>" title="<?php esc_attr_e('Monter', 'em-wp'); ?>"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i></button>
                    <button type="button" class="em-wp-slider-slide-item__move em-wp-slider-slide-item__move--down" aria-label="<?php esc_attr_e('Descendre', 'em-wp'); ?>" title="<?php esc_attr_e('Descendre', 'em-wp'); ?>"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                    <span class="em-wp-slide-sortable__handle em-wp-slider-slide-item__drag" role="button" tabindex="0" aria-label="<?php esc_attr_e('Glisser pour reordonner', 'em-wp'); ?>" title="<?php esc_attr_e('Glisser pour reordonner', 'em-wp'); ?>"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
                </span>
                <span class="em-wp-slider-slide-item__sep" aria-hidden="true">|</span>
                <span class="em-wp-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-hidden="true"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>"></i></span>
                <span class="em-wp-slider-slide-item__sep" aria-hidden="true">|</span>
                <button type="button" class="em-wp-slider-slide-item__delete" aria-label="<?php esc_attr_e('Supprimer ce slide', 'em-wp'); ?>" title="<?php esc_attr_e('Supprimer ce slide', 'em-wp'); ?>"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                <span class="em-wp-slider-slide-item__sep" aria-hidden="true">|</span>
                <i class="em-wp-slider-slide-item__type-icon <?php echo esc_attr(em_wp_slider_slide_type_icon_classes($slide_type)); ?>" aria-hidden="true"></i>
                <span class="em-wp-slider-slide-item__title"><?php echo esc_html($display_name); ?></span>
            </span>
        </summary>
        <div class="em-wp-admin-nested-item__body em-wp-slider-slide-item__body">
            <div class="em-wp-admin-field-row em-wp-slider-head-row">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Nom du slide', 'em-wp'); ?></span>
                    <span class="em-wp-slider-name-field">
                        <i class="fa-solid fa-pen-to-square em-wp-slider-name-field__icon" aria-hidden="true"></i>
                        <input type="text" class="em-wp-slider-text-input em-wp-slider-name-field__input em-wp-slider-slide-item__name-input" name="<?php echo esc_attr($field_base . '[name]'); ?>" value="<?php echo esc_attr($name_value); ?>">
                    </span>
                </span>
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Type', 'em-wp'); ?></span>
                    <select class="em-wp-slider-select em-wp-slider-item-panel__type" name="<?php echo esc_attr($field_base . '[type]'); ?>">
                        <option value="image" <?php selected($slide_type, 'image'); ?>><?php esc_html_e('Image', 'em-wp'); ?></option>
                        <option value="video" <?php selected($slide_type, 'video'); ?>><?php esc_html_e('Video YouTube', 'em-wp'); ?></option>
                        <option value="tiktok" <?php selected($slide_type, 'tiktok'); ?>><?php esc_html_e('Video TikTok', 'em-wp'); ?></option>
                    </select>
                </span>
                <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($field_base . '[hidden]'); ?>" value="1" <?php checked($is_hidden); ?>></label>
            </div>

            <div class="em-wp-admin-field-row" data-slide-field="image">
                <span class="em-wp-admin-field-group em-wp-slider-field--grow">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Image', 'em-wp'); ?></span>
                    <input type="text" id="<?php echo esc_attr($image_input_id); ?>" name="<?php echo esc_attr($field_base . '[image]'); ?>" value="<?php echo esc_attr($image_value); ?>" class="em-wp-slider-text-input">
                    <button type="button" class="button em-wp-admin-media-button em-wp-slider-media-button" data-target="<?php echo esc_attr($image_input_id); ?>" data-preview="<?php echo esc_attr($image_preview_id); ?>" data-modal-title="<?php echo esc_attr(sprintf(__('Choisir media pour %s', 'em-wp'), $display_name)); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce media', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                </span>
            </div>
            <div id="<?php echo esc_attr($image_preview_id); ?>" class="em-wp-admin-media-preview em-wp-slider-preview<?php echo $image_value === '' ? ' is-empty' : ''; ?>"><?php if ($image_value !== '') { ?><img src="<?php echo esc_url($image_value); ?>" alt=""><?php } ?></div>

            <div class="em-wp-admin-field-row" data-slide-field="video">
                <span class="em-wp-admin-field-group em-wp-slider-field--grow">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('URL YouTube', 'em-wp'); ?></span>
                    <input type="url" class="em-wp-slider-text-input" name="<?php echo esc_attr($field_base . '[video_url]'); ?>" value="<?php echo esc_attr($video_url_value); ?>" placeholder="https://www.youtube.com/watch?v=...">
                </span>
            </div>

            <div class="em-wp-admin-field-row em-wp-slider-inline-row" data-slide-field="tiktok">
                <span class="em-wp-admin-field-group em-wp-slider-field--grow">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('URL TikTok', 'em-wp'); ?></span>
                    <input type="url" class="em-wp-slider-text-input" name="<?php echo esc_attr($field_base . '[tiktok_url]'); ?>" value="<?php echo esc_attr($tiktok_url_value); ?>" placeholder="https://www.tiktok.com/@artist/video/...">
                </span>
                <span class="em-wp-admin-field-group em-wp-slider-field--grow">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Video MP4', 'em-wp'); ?></span>
                    <input type="text" id="<?php echo esc_attr($tiktok_video_input_id); ?>" name="<?php echo esc_attr($field_base . '[tiktok_video_url]'); ?>" value="<?php echo esc_attr($tiktok_video_value); ?>" class="em-wp-slider-text-input">
                    <button type="button" class="button em-wp-admin-media-button em-wp-slider-media-button" data-target="<?php echo esc_attr($tiktok_video_input_id); ?>" data-preview="<?php echo esc_attr($tiktok_video_preview_id); ?>" data-modal-title="<?php echo esc_attr(sprintf(__('Choisir MP4 TikTok pour %s', 'em-wp'), $display_name)); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce media', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                </span>
            </div>
            <div id="<?php echo esc_attr($tiktok_video_preview_id); ?>" class="em-wp-admin-media-preview em-wp-slider-preview<?php echo $tiktok_video_value === '' ? ' is-empty' : ''; ?>"><?php if ($tiktok_video_value !== '') { ?><video src="<?php echo esc_url($tiktok_video_value); ?>" controls muted preload="metadata"></video><?php } ?></div>

            <div class="em-wp-admin-field-row" data-slide-field="alt">
                <span class="em-wp-admin-field-group em-wp-slider-field--grow">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Texte Alt', 'em-wp'); ?></span>
                    <input type="text" class="em-wp-slider-text-input" name="<?php echo esc_attr($field_base . '[alt_text]'); ?>" value="<?php echo esc_attr($alt_text_value); ?>">
                </span>
            </div>

            <div class="em-wp-admin-field-row" data-slide-field="duration">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Duree (secondes)', 'em-wp'); ?></span>
                    <input type="number" min="1" step="1" class="em-wp-slider-text-input em-wp-slider-text-input--narrow" name="<?php echo esc_attr($field_base . '[duration]'); ?>" value="<?php echo esc_attr($duration_value); ?>">
                </span>
            </div>
        </div>
    </details>
    <?php
}