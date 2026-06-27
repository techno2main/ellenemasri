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
    return ['text', 'textarea', 'url', 'email', 'image', 'icon', 'sep_line', 'sep_blank', 'arrow_up', 'arrow_down'];
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

    ?>
    <div class="em-v4-chip<?php echo $hidden ? ' is-hidden' : ''; ?>" draggable="true" data-key="<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($type); ?>" data-hidden="<?php echo $hidden ? '1' : '0'; ?>">
        <span class="em-v4-chip__drag dashicons dashicons-move" aria-hidden="true"></span>
        <span class="em-v4-chip__type"><?php echo esc_html($type); ?></span>
        <span class="em-v4-chip__fields">
            <input type="text" class="em-v4-chip__label" value="<?php echo esc_attr((string) $field['label']); ?>" placeholder="<?php esc_attr_e('Libellé', 'em-wp'); ?>">
            <?php em_wp_v4_render_chip_value($type, $value); ?>
        </span>
        <?php em_wp_v4_render_chip_toggle($hidden); ?>
        <button type="button" class="em-v4-chip__remove" data-label="<?php echo esc_attr((string) $field['label']); ?>" title="<?php esc_attr_e('Supprimer', 'em-wp'); ?>">&times;</button>
    </div>
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
        <span class="em-v4-chip__type"><?php echo esc_html($label); ?></span>
        <input type="hidden" class="em-v4-chip__label" value="">
        <?php if ($has_color) : ?>
            <span class="em-v4-chip__color">
                <?php em_wp_admin_render_color_field([
                    'id'            => 'emv4dec-' . sanitize_html_class($key),
                    'value'         => $color,
                    'input_class'   => 'em-v4-chip__value',
                    'preview_label' => $label,
                ]); ?>
            </span>
        <?php endif; ?>
        <?php if ($is_arrow) : ?>
            <input type="url" class="em-v4-chip__url" value="<?php echo esc_url($link); ?>" placeholder="<?php esc_attr_e('Ancre (#section) ou URL', 'em-wp'); ?>">
        <?php endif; ?>
        <?php em_wp_v4_render_chip_toggle($hidden); ?>
        <button type="button" class="em-v4-chip__remove" data-label="<?php echo esc_attr($label); ?>" title="<?php esc_attr_e('Supprimer', 'em-wp'); ?>">&times;</button>
    </div>
    <?php
}

/**
 * Contrôle de valeur d'une chip selon le type (image = média, icône = liste).
 */
function em_wp_v4_render_chip_value(string $type, string $value): void
{
    if ($type === 'image') {
        $img = em_wp_rubrique_image_value($value);
        $id = $img['id'];
        $thumb = $id ? wp_get_attachment_image_url($id, 'medium') : '';
        $full = $id ? wp_get_attachment_image_url($id, 'large') : '';
        ?>
        <span class="em-v4-chip__media" data-url="<?php echo esc_attr((string) $full); ?>">
            <span class="em-v4-chip__focal" title="<?php esc_attr_e('Cliquez pour définir le point focal (recadrage)', 'em-wp'); ?>">
                <img class="em-v4-chip__thumb" src="<?php echo esc_url((string) $thumb); ?>" alt="" <?php echo $thumb ? '' : 'hidden'; ?>>
                <span class="em-v4-chip__focaldot" style="left:<?php echo (int) $img['fx']; ?>%;top:<?php echo (int) $img['fy']; ?>%" <?php echo $thumb ? '' : 'hidden'; ?>></span>
            </span>
            <button type="button" class="button button-small em-v4-chip__pick"><?php esc_html_e('Choisir une image', 'em-wp'); ?></button>
            <input type="hidden" class="em-v4-chip__value" value="<?php echo esc_attr((string) $id); ?>">
            <input type="hidden" class="em-v4-chip__fx" value="<?php echo (int) $img['fx']; ?>">
            <input type="hidden" class="em-v4-chip__fy" value="<?php echo (int) $img['fy']; ?>">
        </span>
        <span class="em-v4-chip__size">
            <label class="em-v4-chip__sizelabel"><?php esc_html_e('Taille', 'em-wp'); ?>
                <input type="range" class="em-v4-chip__w" min="0" max="600" step="5" value="<?php echo (int) $img['w']; ?>" oninput="this.nextElementSibling.textContent=(this.value>0?this.value+'px':'auto')">
                <output class="em-v4-chip__wout"><?php echo $img['w'] ? (int) $img['w'] . 'px' : esc_html__('auto', 'em-wp'); ?></output>
            </label>
            <input type="number" min="0" class="em-v4-chip__h" value="<?php echo $img['h'] ? (int) $img['h'] : ''; ?>" placeholder="<?php esc_attr_e('Recadrer H px', 'em-wp'); ?>" title="<?php esc_attr_e('Hauteur de recadrage (optionnel)', 'em-wp'); ?>">
        </span>
        <input type="url" class="em-v4-chip__url" value="<?php echo esc_url($img['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
        <?php
        return;
    }

    if ($type === 'icon') {
        $icon = em_wp_rubrique_icon_value($value);
        ?>
        <select class="em-v4-chip__platform">
            <option value=""><?php esc_html_e('— Choisir —', 'em-wp'); ?></option>
            <?php foreach (em_wp_rubrique_platform_choices() as $pkey => $choice) : ?>
                <option value="<?php echo esc_attr($pkey); ?>" data-icon="<?php echo esc_attr($choice['icon']); ?>" <?php selected($icon['platform'], $pkey); ?>><?php echo esc_html($choice['group'] . ' — ' . $choice['label']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="url" class="em-v4-chip__url" value="<?php echo esc_url($icon['url']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
        <?php
        return;
    }
    ?>
    <input type="text" class="em-v4-chip__value" value="<?php echo esc_attr($value); ?>" placeholder="<?php esc_attr_e('Contenu…', 'em-wp'); ?>">
    <?php
}
