<?php
/**
 * Rendu serveur d'une chip de champ du builder (V4).
 *
 * Une chip porte sa STRUCTURE (libellé) et son CONTENU (valeur). Le contrôle de
 * valeur dépend du type : texte, image (médiathèque) ou icône de plateforme.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Types de champ proposés dans le builder (palette).
 *
 * @return array<int, string>
 */
function em_wp_v4_builder_field_types(): array
{
    return ['text', 'textarea', 'url', 'email', 'image', 'text_image', 'text_text', 'icon', 'platform_block', 'button', 'video_url', 'video_file', 'audio_file', 'audio_url', 'network_block', 'slider', 'sep_line', 'sep_blank', 'arrow_up', 'arrow_down'];
}

/**
 * Icône (Dashicon) d'un type de champ, pour le badge d'une chip.
 */
function em_wp_v4_field_type_icon(string $type): string
{
    $def = em_wp_field_type_get($type);
    $icon = $def ? (string) ($def['icon'] ?? '') : '';

    return $icon !== '' ? $icon : 'dashicons-marker';
}

/**
 * Identifiant unique pour un champ couleur d'une chip.
 *
 * La modale couleur cible son input via getElementById : deux champs couleur ne
 * doivent jamais partager le même id (sinon l'édition de l'un écrit dans l'autre,
 * ex. deux flèches → une couleur perdue). On suffixe donc un compteur.
 */
function em_wp_v4_chip_color_id(string $prefix, string $key): string
{
    static $n = 0;

    return $prefix . sanitize_html_class($key) . '-' . (++$n);
}

/**
 * Une chip de champ : libellé (structure) + valeur (contenu) + drag + suppr.
 *
 * @param array<string, mixed> $field
 * @param array<string, mixed> $content
 */
function em_wp_v4_render_chip(array $field, array $content = []): void
{
    $key = (string) $field['key'];
    $type = (string) $field['type'];

    $value = (string) ($content[$key] ?? $field['default'] ?? '');
    $hidden = !empty($field['hidden']);

    if (em_wp_rubrique_field_is_decorative($type)) {
        em_wp_v4_render_decorative_chip($key, $type, $value, $hidden);
        return;
    }

    $type_def = em_wp_field_type_get($type);
    $type_label = $type_def ? (string) $type_def['label'] : $type;
    ?>
    <div class="em-v4-chip<?php echo $hidden ? ' is-hidden' : ''; ?>" draggable="true" data-key="<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($type); ?>" data-hidden="<?php echo $hidden ? '1' : '0'; ?>">
        <span class="em-v4-chip__drag dashicons dashicons-move" aria-hidden="true"></span>
        <span class="em-v4-chip__type"><span class="em-v4-chip__typeicon dashicons <?php echo esc_attr(em_wp_v4_field_type_icon($type)); ?>" aria-hidden="true"></span><?php echo esc_html($type_label); ?></span>
        <span class="em-v4-chip__fields">
            <?php if ($type === 'platform_block' || $type === 'network_block' || em_wp_rubrique_field_is_text_family($type)) : ?>
                <input type="hidden" class="em-v4-chip__label" value="<?php echo esc_attr((string) $field['label']); ?>">
            <?php else : ?>
                <input type="text" class="em-v4-chip__label" value="<?php echo esc_attr((string) $field['label']); ?>" placeholder="<?php esc_attr_e('Libellé', 'em-wp'); ?>">
            <?php endif; ?>
            <?php em_wp_v4_render_chip_value($type, $value, $key); ?>
            <?php if (em_wp_rubrique_field_supports_text_style($type)) : ?>
                <?php em_wp_v4_render_chip_textstyle($key, (array) ($field['options']['style'] ?? [])); ?>
            <?php endif; ?>
        </span>
        <span class="em-v4-chip__actions">
            <?php em_wp_v4_render_chip_toggle($hidden); ?>
            <button type="button" class="em-v4-chip__remove" data-label="<?php echo esc_attr((string) $field['label']); ?>" title="<?php esc_attr_e('Supprimer', 'em-wp'); ?>">&times;</button>
        </span>
    </div>
    <?php
}

/**
 * Contrôles de style propres à un champ texte : taille (px), police, couleur.
 *
 * @param array<string, mixed> $style
 */
function em_wp_v4_render_chip_textstyle(string $key, array $style): void
{
    $style = em_wp_rubrique_normalize_text_style($style);
    ?>
    <span class="em-v4-chip__tstyle">
        <input type="number" class="em-v4-chip__tsize" min="0" max="200" value="<?php echo $style['size'] ? (int) $style['size'] : ''; ?>" placeholder="<?php esc_attr_e('px', 'em-wp'); ?>" title="<?php esc_attr_e('Taille du texte (px)', 'em-wp'); ?>">
        <select class="em-v4-chip__tfont" title="<?php esc_attr_e('Police du champ', 'em-wp'); ?>">
            <option value=""><?php esc_html_e('Police héritée', 'em-wp'); ?></option>
            <?php foreach (em_wp_rubrique_font_choices() as $fkey => $choice) : ?>
                <option value="<?php echo esc_attr($fkey); ?>" data-stack="<?php echo esc_attr($choice['stack']); ?>" <?php selected($style['font'], $fkey); ?>><?php echo esc_html($choice['label']); ?></option>
            <?php endforeach; ?>
        </select>
        <?php em_wp_admin_render_color_field([
            'id'            => em_wp_v4_chip_color_id('emv4ts-', $key),
            'value'         => $style['color'],
            'input_class'   => 'em-v4-chip__tcolor',
            'preview_label' => __('Couleur du texte', 'em-wp'),
        ]); ?>
    </span>
    <?php
}

/**
 * Bouton Afficher/Masquer d'une chip (n'enlève pas le champ, ne décale rien).
 */
function em_wp_v4_render_chip_toggle(bool $hidden): void
{
    ?>
    <button type="button" class="em-v4-chip__toggle" aria-pressed="<?php echo $hidden ? 'true' : 'false'; ?>" title="<?php echo esc_attr($hidden ? __('Masqué — cliquer pour afficher', 'em-wp') : __('Visible — cliquer pour masquer', 'em-wp')); ?>">
        <span class="dashicons dashicons-<?php echo $hidden ? 'hidden' : 'visibility'; ?>" aria-hidden="true"></span>
    </button>
    <?php
}

/**
 * Chip décorative : pas de libellé. Filet/flèches portent un choix de couleur.
 */
function em_wp_v4_render_decorative_chip(string $key, string $type, string $value = '', bool $hidden = false): void
{
    $def = em_wp_field_type_get($type);
    $label = $def ? (string) $def['label'] : $type;
    $has_color = in_array($type, em_wp_rubrique_decorative_color_types(), true);
    $is_arrow = in_array($type, em_wp_rubrique_arrow_types(), true);
    $is_blank = ($type === 'sep_blank');

    $color = $value;
    $link = '';

    if ($is_arrow) {
        $arrow = em_wp_rubrique_arrow_value($value);
        $color = $arrow['color'];
        $link = $arrow['link'];
    }
    ?>
    <div class="em-v4-chip em-v4-chip--decor<?php echo $hidden ? ' is-hidden' : ''; ?>" draggable="true" data-key="<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($type); ?>" data-hidden="<?php echo $hidden ? '1' : '0'; ?>">
        <span class="em-v4-chip__drag dashicons dashicons-move" aria-hidden="true"></span>
        <span class="em-v4-chip__type"><span class="em-v4-chip__typeicon dashicons <?php echo esc_attr(em_wp_v4_field_type_icon($type)); ?>" aria-hidden="true"></span><?php echo esc_html($label); ?></span>
        <input type="hidden" class="em-v4-chip__label" value="">
        <?php if ($has_color) : ?>
            <span class="em-v4-chip__color">
                <?php em_wp_admin_render_color_field([
                    'id'            => em_wp_v4_chip_color_id('emv4dec-', $key),
                    'value'         => $color,
                    'input_class'   => 'em-v4-chip__value',
                    'preview_label' => $label,
                ]); ?>
            </span>
        <?php endif; ?>
        <?php if ($is_arrow) : ?>
            <input type="url" class="em-v4-chip__url" value="<?php echo esc_url($link); ?>" placeholder="<?php esc_attr_e('Ancre (#section) ou URL', 'em-wp'); ?>">
        <?php endif; ?>
        <?php if ($is_blank) : ?>
            <input type="number" class="em-v4-chip__value em-v4-chip__height" min="0" max="400" value="<?php echo $value !== '' ? (int) $value : ''; ?>" placeholder="<?php esc_attr_e('Hauteur px', 'em-wp'); ?>" title="<?php esc_attr_e('Hauteur du séparateur (px)', 'em-wp'); ?>">
        <?php endif; ?>
        <span class="em-v4-chip__actions">
            <?php em_wp_v4_render_chip_toggle($hidden); ?>
            <button type="button" class="em-v4-chip__remove" data-label="<?php echo esc_attr($label); ?>" title="<?php esc_attr_e('Supprimer', 'em-wp'); ?>">&times;</button>
        </span>
    </div>
    <?php
}

/**
 * Contrôle de valeur d'une chip selon le type (image = média, icône = liste).
 */
function em_wp_v4_render_chip_value(string $type, string $value, string $key = ''): void
{
    if (em_wp_v4_render_chip_media_value($type, $value)) {
        return;
    }

    if ($type === 'text' || $type === 'textarea') {
        $tv = em_wp_rubrique_text_value($value);
        ?>
        <input type="text" class="em-v4-chip__value" value="<?php echo esc_attr($tv['text']); ?>" placeholder="<?php esc_attr_e('Contenu…', 'em-wp'); ?>">
        <input type="url" class="em-v4-chip__tlink" value="<?php echo esc_url($tv['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
        <?php
        return;
    }

    if ($type === 'text_image') {
        $ti = em_wp_rubrique_text_image_value($value);
        ?>
        <input type="text" class="em-v4-chip__titext" value="<?php echo esc_attr($ti['text']); ?>" placeholder="<?php esc_attr_e('Contenu…', 'em-wp'); ?>">
        <input type="url" class="em-v4-chip__tlink" value="<?php echo esc_url($ti['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
        <?php em_wp_v4_render_chip_textstyle($key, $ti['style']); ?>
        <?php em_wp_v4_render_chip_value('image', (string) wp_json_encode($ti['image'])); ?>
        <?php
        return;
    }

    if ($type === 'text_text') {
        $tt = em_wp_rubrique_text_text_value($value);
        ?>
        <span class="em-v4-chip__tt-part">
            <input type="text" class="em-v4-chip__titext" value="<?php echo esc_attr($tt['text']); ?>" placeholder="<?php esc_attr_e('Contenu 1…', 'em-wp'); ?>">
            <input type="url" class="em-v4-chip__tlink" value="<?php echo esc_url($tt['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
            <?php em_wp_v4_render_chip_textstyle($key, $tt['style']); ?>
        </span>
        <span class="em-v4-chip__tt-part">
            <input type="text" class="em-v4-chip__titext2" value="<?php echo esc_attr($tt['text2']); ?>" placeholder="<?php esc_attr_e('Contenu 2…', 'em-wp'); ?>">
            <input type="url" class="em-v4-chip__tlink2" value="<?php echo esc_url($tt['link2']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
            <?php em_wp_v4_render_chip_textstyle($key . '-2', $tt['style2']); ?>
        </span>
        <?php
        return;
    }

    if ($type === 'image') {
        $img = em_wp_rubrique_image_value($value);
        $id = $img['id'];
        $thumb = $id ? wp_get_attachment_image_url($id, 'medium') : '';
        $full = $id ? wp_get_attachment_image_url($id, 'large') : '';
        ?>
        <span class="em-v4-chip__media" data-url="<?php echo esc_attr((string) $full); ?>">
            <img class="em-v4-chip__thumb" src="<?php echo esc_url((string) $thumb); ?>" alt="" <?php echo $thumb ? '' : 'hidden'; ?>>
            <button type="button" class="button button-small em-v4-chip__pick"><?php esc_html_e('Choisir une image', 'em-wp'); ?></button>
            <input type="hidden" class="em-v4-chip__value" value="<?php echo esc_attr((string) $id); ?>">
        </span>
        <span class="em-v4-chip__size">
            <label class="em-v4-chip__sizelabel"><?php esc_html_e('Taille', 'em-wp'); ?>
                <input type="range" class="em-v4-chip__w" min="0" max="600" step="5" value="<?php echo (int) $img['w']; ?>" oninput="this.nextElementSibling.textContent=(this.value>0?this.value+'px':'auto')">
                <output class="em-v4-chip__wout"><?php echo $img['w'] ? (int) $img['w'] . 'px' : esc_html__('auto', 'em-wp'); ?></output>
            </label>
        </span>
        <input type="url" class="em-v4-chip__url" value="<?php echo esc_url($img['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
        <?php
        return;
    }

    if ($type === 'button') {
        $btn = em_wp_rubrique_button_value($value);
        ?>
        <input type="url" class="em-v4-chip__url" value="<?php echo esc_url($btn['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
        <span class="em-v4-chip__btncolor">
            <span class="em-v4-chip__btncolor-label"><?php esc_html_e('Fond', 'em-wp'); ?></span>
            <?php em_wp_admin_render_color_field([
                'id'            => em_wp_v4_chip_color_id('emv4bbg-', $key),
                'value'         => $btn['bg'],
                'input_class'   => 'em-v4-chip__btnbg',
                'preview_label' => __('Fond du bouton', 'em-wp'),
            ]); ?>
        </span>
        <span class="em-v4-chip__btncolor">
            <span class="em-v4-chip__btncolor-label"><?php esc_html_e('Texte', 'em-wp'); ?></span>
            <?php em_wp_admin_render_color_field([
                'id'            => em_wp_v4_chip_color_id('emv4btx-', $key),
                'value'         => $btn['text'],
                'input_class'   => 'em-v4-chip__btntext',
                'preview_label' => __('Texte du bouton', 'em-wp'),
            ]); ?>
        </span>
        <?php
        return;
    }

    if ($type === 'platform_block') {
        $block = em_wp_rubrique_platform_block_value($value);
        ?>
        <input type="text" class="em-v4-chip__ptitle" value="<?php echo esc_attr($block['label']); ?>" placeholder="<?php esc_attr_e('Titre (ex. LISTEN ON)', 'em-wp'); ?>" title="<?php esc_attr_e('Sur-titre de la carte', 'em-wp'); ?>">
        <?php em_wp_v4_render_platform_select($block['platform']); ?>
        <input type="url" class="em-v4-chip__url" value="<?php echo esc_url($block['url']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
        <?php
        return;
    }

    if ($type === 'icon') {
        $icon = em_wp_rubrique_icon_value($value);
        em_wp_v4_render_platform_select($icon['platform']);
        ?>
        <input type="url" class="em-v4-chip__url" value="<?php echo esc_url($icon['url']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
        <?php
        return;
    }
    ?>
    <input type="text" class="em-v4-chip__value" value="<?php echo esc_attr($value); ?>" placeholder="<?php esc_attr_e('Contenu…', 'em-wp'); ?>">
    <?php
}

/**
 * Liste déroulante des plateformes (icône + couleur + libellé), mutualisée par
 * les champs « icône » et « Bloc Plateforme ».
 */
function em_wp_v4_render_platform_select(string $selected): void
{
    ?>
    <select class="em-v4-chip__platform">
        <option value=""><?php esc_html_e('— Choisir —', 'em-wp'); ?></option>
        <?php foreach (em_wp_rubrique_platform_choices() as $pkey => $choice) : ?>
            <option value="<?php echo esc_attr($pkey); ?>" data-icon="<?php echo esc_attr($choice['icon']); ?>" data-color="<?php echo esc_attr((string) ($choice['color'] ?? '')); ?>" data-label="<?php echo esc_attr($choice['label']); ?>" <?php selected($selected, $pkey); ?>><?php echo esc_html($choice['group'] . ' — ' . $choice['label']); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}
