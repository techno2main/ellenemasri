<?php
/**
 * Rendu serveur d'une chip de champ du builder (EM-SITE).
 *
 * Une chip porte sa STRUCTURE (libellé) et son CONTENU (valeur). Le contrôle de
 * valeur dépend du type : texte, image (médiathèque) ou icône de plateforme.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Types de champ proposés dans le builder (palette).
 *
 * @return array<int, string>
 */
function em_site_builder_field_types(): array
{
    return ['text', 'textarea', 'email', 'image', 'text_image', 'text_icon', 'icon_text', 'text_text', 'icon', 'platform_block', 'button', 'animated_badge', 'video_url', 'video_file', 'audio_file', 'audio_url', 'network_block', 'slider', 'sep_line', 'sep_blank', 'arrow_up', 'arrow_down'];
}

/**
 * Groupes de types pour le select "Ajouter un champ".
 *
 * @return array<string, array<int, string>>
 */
function em_site_builder_field_groups(): array
{
    return [
        __('Texte et contenu', 'em-site') => ['text', 'textarea', 'url', 'email', 'text_image', 'text_icon', 'icon_text', 'text_text', 'button', 'animated_badge'],
        __('Media', 'em-site') => ['image', 'video_url', 'video_file', 'audio_file', 'audio_url', 'slider'],
        __('Plateformes et reseaux', 'em-site') => ['icon', 'platform_block', 'network_block'],
        __('Structure et navigation', 'em-site') => ['sep_line', 'sep_blank', 'arrow_up', 'arrow_down'],
    ];
}

/**
 * Libelle lisible d'un type pour le picker.
 */
function em_site_field_type_picker_label(string $type): string
{
    $def = em_site_field_type_get($type);
    return $def ? (string) ($def['label'] ?? $type) : $type;
}

/**
 * Icône (Dashicon) d'un type de champ, pour le badge d'une chip.
 */
function em_site_field_type_icon(string $type): string
{
    $def = em_site_field_type_get($type);
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
function em_site_chip_color_id(string $prefix, string $key): string
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
function em_site_render_chip(array $field, array $content = []): void
{
    $key = (string) $field['key'];
    $type = (string) $field['type'];

    $value = (string) ($content[$key] ?? $field['default'] ?? '');
    $hidden = !empty($field['hidden']);

    if (em_site_rubrique_field_is_decorative($type)) {
        em_site_render_decorative_chip($key, $type, $value, $hidden);
        return;
    }

    $type_def = em_site_field_type_get($type);
    $type_label = $type_def ? (string) $type_def['label'] : $type;
    ?>
    <div class="em-site-chip<?php echo $hidden ? ' is-hidden' : ''; ?>" draggable="true" data-key="<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($type); ?>" data-hidden="<?php echo $hidden ? '1' : '0'; ?>">
        <span class="em-site-chip__drag dashicons dashicons-move" aria-hidden="true"></span>
        <span class="em-site-chip__type"><span class="em-site-chip__typeicon dashicons <?php echo esc_attr(em_site_field_type_icon($type)); ?>" aria-hidden="true"></span><?php echo esc_html($type_label); ?></span>
        <span class="em-site-chip__fields">
                <?php if ($type === 'platform_block' || $type === 'network_block' || $type === 'animated_badge' || $type === 'slider' || em_site_rubrique_field_is_text_family($type)) : ?>
                <input type="hidden" class="em-site-chip__label" value="<?php echo esc_attr((string) $field['label']); ?>">
            <?php else : ?>
                <input type="text" class="em-site-chip__label" value="<?php echo esc_attr((string) $field['label']); ?>" placeholder="<?php esc_attr_e('Libellé', 'em-site'); ?>">
            <?php endif; ?>
            <?php em_site_render_chip_value($type, $value, $key); ?>
            <?php if (em_site_rubrique_field_supports_text_style($type)) : ?>
                <?php em_site_render_chip_textstyle($key, (array) ($field['options']['style'] ?? [])); ?>
            <?php endif; ?>
        </span>
        <span class="em-site-chip__actions">
            <?php em_site_render_chip_toggle($hidden); ?>
            <button type="button" class="em-site-chip__remove" data-label="<?php echo esc_attr((string) $field['label']); ?>" title="<?php esc_attr_e('Supprimer', 'em-site'); ?>">&times;</button>
        </span>
    </div>
    <?php
}

/**
 * Contrôles de style propres à un champ texte : taille (px), police, couleur.
 *
 * @param array<string, mixed> $style
 */
function em_site_render_chip_textstyle(string $key, array $style): void
{
    $style = em_site_rubrique_normalize_text_style($style);
    ?>
    <span class="em-site-chip__tstyle">
        <input type="number" class="em-site-chip__tsize" min="0" max="200" value="<?php echo $style['size'] ? (int) $style['size'] : ''; ?>" placeholder="<?php esc_attr_e('px', 'em-site'); ?>" title="<?php esc_attr_e('Taille du texte (px)', 'em-site'); ?>">
        <select class="em-site-chip__tfont" title="<?php esc_attr_e('Police du champ', 'em-site'); ?>">
            <option value=""><?php esc_html_e('Police héritée', 'em-site'); ?></option>
            <?php foreach (em_site_rubrique_font_choices() as $fkey => $choice) : ?>
                <option value="<?php echo esc_attr($fkey); ?>" data-stack="<?php echo esc_attr($choice['stack']); ?>" <?php selected($style['font'], $fkey); ?>><?php echo esc_html($choice['label']); ?></option>
            <?php endforeach; ?>
        </select>
        <select class="em-site-chip__talign" title="<?php esc_attr_e('Alignement du texte', 'em-site'); ?>">
            <option value=""><?php esc_html_e('Alignement hérité', 'em-site'); ?></option>
            <option value="left" <?php selected($style['align'], 'left'); ?>><?php esc_html_e('Gauche', 'em-site'); ?></option>
            <option value="center" <?php selected($style['align'], 'center'); ?>><?php esc_html_e('Centre', 'em-site'); ?></option>
            <option value="right" <?php selected($style['align'], 'right'); ?>><?php esc_html_e('Droite', 'em-site'); ?></option>
            <option value="justify" <?php selected($style['align'], 'justify'); ?>><?php esc_html_e('Justifié', 'em-site'); ?></option>
        </select>
        <?php em_site_admin_render_color_field([
            'id'            => em_site_chip_color_id('em-site-ts-', $key),
            'value'         => $style['color'],
            'input_class'   => 'em-site-chip__tcolor',
            'preview_label' => __('Couleur du texte', 'em-site'),
        ]); ?>
    </span>
    <?php
}

/**
 * Bouton Afficher/Masquer d'une chip (n'enlève pas le champ, ne décale rien).
 */
function em_site_render_chip_toggle(bool $hidden): void
{
    ?>
    <button type="button" class="em-site-chip__toggle" aria-pressed="<?php echo $hidden ? 'true' : 'false'; ?>" title="<?php echo esc_attr($hidden ? __('Masqué — cliquer pour afficher', 'em-site') : __('Visible — cliquer pour masquer', 'em-site')); ?>">
        <span class="dashicons dashicons-<?php echo $hidden ? 'hidden' : 'visibility'; ?>" aria-hidden="true"></span>
    </button>
    <?php
}

/**
 * Chip décorative : pas de libellé. Filet/flèches portent un choix de couleur.
 */
function em_site_render_decorative_chip(string $key, string $type, string $value = '', bool $hidden = false): void
{
    $def = em_site_field_type_get($type);
    $label = $def ? (string) $def['label'] : $type;
    $has_color = in_array($type, em_site_rubrique_decorative_color_types(), true);
    $is_arrow = in_array($type, em_site_rubrique_arrow_types(), true);
    $is_blank = ($type === 'sep_blank');

    $color = $value;
    $link = '';

    if ($is_arrow) {
        $arrow = em_site_rubrique_arrow_value($value);
        $color = $arrow['color'];
        $link = $arrow['link'];
    }
    ?>
    <div class="em-site-chip em-site-chip--decor<?php echo $hidden ? ' is-hidden' : ''; ?>" draggable="true" data-key="<?php echo esc_attr($key); ?>" data-type="<?php echo esc_attr($type); ?>" data-hidden="<?php echo $hidden ? '1' : '0'; ?>">
        <span class="em-site-chip__drag dashicons dashicons-move" aria-hidden="true"></span>
        <span class="em-site-chip__type"><span class="em-site-chip__typeicon dashicons <?php echo esc_attr(em_site_field_type_icon($type)); ?>" aria-hidden="true"></span><?php echo esc_html($label); ?></span>
        <input type="hidden" class="em-site-chip__label" value="">
        <?php if ($has_color) : ?>
            <span class="em-site-chip__color">
                <?php em_site_admin_render_color_field([
                    'id'            => em_site_chip_color_id('em-site-dec-', $key),
                    'value'         => $color,
                    'input_class'   => 'em-site-chip__value',
                    'preview_label' => $label,
                ]); ?>
            </span>
        <?php endif; ?>
        <?php if ($is_arrow) : ?>
            <input type="url" class="em-site-chip__url" value="<?php echo esc_url($link); ?>" placeholder="<?php esc_attr_e('Ancre (#section) ou URL', 'em-site'); ?>">
        <?php endif; ?>
        <?php if ($is_blank) : ?>
            <input type="number" class="em-site-chip__value em-site-chip__height" min="0" max="400" value="<?php echo $value !== '' ? (int) $value : ''; ?>" placeholder="<?php esc_attr_e('Hauteur px', 'em-site'); ?>" title="<?php esc_attr_e('Hauteur du séparateur (px)', 'em-site'); ?>">
        <?php endif; ?>
        <span class="em-site-chip__actions">
            <?php em_site_render_chip_toggle($hidden); ?>
            <button type="button" class="em-site-chip__remove" data-label="<?php echo esc_attr($label); ?>" title="<?php esc_attr_e('Supprimer', 'em-site'); ?>">&times;</button>
        </span>
    </div>
    <?php
}

// Contrôles de valeur des chips + sélecteurs plateforme/réseau : voir
// chip-value.php (extraits pour garder ce fichier sous 300 lignes).
