<?php
/**
 * Contrôles de VALEUR d'une chip du builder (V4).
 *
 * Extrait de chip.php : rendu du contrôle d'édition selon le type de champ
 * (texte, média, plateforme, réseau, bouton…) + listes déroulantes plateforme /
 * réseaux. Séparé pour garder chaque fichier sous 300 lignes.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contrôle de valeur d'une chip selon le type (image = média, icône = liste).
 */
function em_wp_v4_render_chip_value(string $type, string $value, string $key = ''): void
{
    if (em_wp_v4_render_chip_media_value($type, $value)) {
        return;
    }

    if ($type === 'text') {
        $tv = em_wp_rubrique_text_value($value);
        ?>
        <input type="text" class="em-v4-chip__value" value="<?php echo esc_attr($tv['text']); ?>" placeholder="<?php esc_attr_e('Contenu…', 'em-wp'); ?>">
        <input type="url" class="em-v4-chip__tlink" value="<?php echo esc_url($tv['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-wp'); ?>">
        <?php
        return;
    }

    if ($type === 'textarea') {
        $tv = em_wp_rubrique_text_value($value);
        $editor_html = em_wp_rubrique_textarea_editor_html((string) $tv['text']);
        ?>
        <span class="em-v4-chip__rich">
            <span class="em-v4-chip__richbar">
                <button type="button" class="button button-small em-v4-richbtn" data-cmd="bold" title="<?php esc_attr_e('Gras', 'em-wp'); ?>"><strong>B</strong></button>
                <button type="button" class="button button-small em-v4-richbtn" data-cmd="italic" title="<?php esc_attr_e('Italique', 'em-wp'); ?>"><em>I</em></button>
                <button type="button" class="button button-small em-v4-richbtn" data-cmd="underline" title="<?php esc_attr_e('Souligné', 'em-wp'); ?>"><span style="text-decoration:underline;">U</span></button>
                <button type="button" class="button button-small em-v4-richbtn" data-cmd="insertUnorderedList" title="<?php esc_attr_e('Liste', 'em-wp'); ?>">•</button>
                <button type="button" class="button button-small em-v4-richbtn" data-cmd="justifyLeft" title="<?php esc_attr_e('Aligner à gauche', 'em-wp'); ?>"><span class="dashicons dashicons-editor-alignleft" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-v4-richbtn" data-cmd="justifyCenter" title="<?php esc_attr_e('Centrer', 'em-wp'); ?>"><span class="dashicons dashicons-editor-aligncenter" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-v4-richbtn" data-cmd="justifyRight" title="<?php esc_attr_e('Aligner à droite', 'em-wp'); ?>"><span class="dashicons dashicons-editor-alignright" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-v4-richbtn" data-cmd="justifyFull" title="<?php esc_attr_e('Justifier', 'em-wp'); ?>"><span class="dashicons dashicons-editor-justify" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-v4-richbtn" data-action="link" title="<?php esc_attr_e('Ajouter un lien sur la sélection', 'em-wp'); ?>"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-v4-richbtn" data-cmd="unlink" title="<?php esc_attr_e('Retirer le lien', 'em-wp'); ?>"><span class="dashicons dashicons-editor-unlink" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-v4-richbtn" data-action="anchor" title="<?php esc_attr_e('Ajouter une ancre sur la sélection', 'em-wp'); ?>">#</button>
                <span class="em-v4-chip__richcolor" title="<?php esc_attr_e('Couleur du texte', 'em-wp'); ?>">
                    <?php em_wp_admin_render_color_field([
                        'id'            => em_wp_v4_chip_color_id('emv4rc-', $key),
                        'value'         => '#000000',
                        'input_class'   => 'em-v4-richcolor',
                        'preview_label' => __('Couleur du texte', 'em-wp'),
                    ]); ?>
                </span>
            </span>
            <div class="em-v4-chip__richedit" contenteditable="true" spellcheck="false" autocorrect="off" autocapitalize="off" data-gramm="false" data-placeholder="<?php esc_attr_e('Contenu enrichi…', 'em-wp'); ?>"><?php echo $editor_html; ?></div>
            <input type="hidden" class="em-v4-chip__value" value="<?php echo esc_attr((string) $tv['text']); ?>">
        </span>
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
        <label class="em-v4-chip__check" title="<?php esc_attr_e('Ajoute un scotch bleu décoratif', 'em-wp'); ?>">
            <input type="checkbox" class="em-v4-chip__itape" <?php checked(!empty($img['tape'])); ?>>
            <?php esc_html_e('Scotch', 'em-wp'); ?>
        </label>
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
        <label class="em-v4-chip__btnmargin" title="<?php esc_attr_e('Marge à gauche du bouton (px)', 'em-wp'); ?>">
            <span><?php esc_html_e('Marge avant', 'em-wp'); ?></span>
            <input type="number" class="em-v4-chip__btnml" min="0" max="200" value="<?php echo (int) $btn['ml']; ?>">
        </label>
        <label class="em-v4-chip__btnmargin" title="<?php esc_attr_e('Marge à droite du bouton (px)', 'em-wp'); ?>">
            <span><?php esc_html_e('Marge après', 'em-wp'); ?></span>
            <input type="number" class="em-v4-chip__btnmr" min="0" max="200" value="<?php echo (int) $btn['mr']; ?>">
        </label>
        <label class="em-v4-chip__badgeopt" title="<?php esc_attr_e('Forme du bouton', 'em-wp'); ?>">
            <span><?php esc_html_e('Forme', 'em-wp'); ?></span>
            <select class="em-v4-chip__btnshape">
                <?php foreach (em_wp_rubrique_badge_shapes() as $slug => $label) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"<?php selected($btn['shape'], $slug); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="em-v4-chip__badgeopt" title="<?php esc_attr_e('Animation du bouton', 'em-wp'); ?>">
            <span><?php esc_html_e('Animation', 'em-wp'); ?></span>
            <select class="em-v4-chip__btnanim">
                <?php foreach (em_wp_rubrique_badge_anims() as $slug => $label) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"<?php selected($btn['anim'], $slug); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="em-v4-chip__badgeopt" title="<?php esc_attr_e('Arrondi des coins (forme carrée), en px', 'em-wp'); ?>">
            <span><?php esc_html_e('Arrondi', 'em-wp'); ?></span>
            <input type="number" class="em-v4-chip__btnradius" min="0" max="40" value="<?php echo (int) $btn['radius']; ?>">
        </label>
        <?php
        return;
    }

    if ($type === 'animated_badge') {
        $badge = em_wp_rubrique_animated_badge_value($value);
        ?>
        <input type="text" class="em-v4-chip__btext" value="<?php echo esc_attr($badge['text']); ?>" placeholder="<?php esc_attr_e('Texte du badge…', 'em-wp'); ?>">
        <span class="em-v4-chip__btncolor">
            <span class="em-v4-chip__btncolor-label"><?php esc_html_e('Fond', 'em-wp'); ?></span>
            <?php em_wp_admin_render_color_field([
                'id'            => em_wp_v4_chip_color_id('emv4babg-', $key),
                'value'         => $badge['bg'],
                'input_class'   => 'em-v4-chip__badgebg',
                'preview_label' => __('Fond du badge', 'em-wp'),
            ]); ?>
        </span>
        <span class="em-v4-chip__btncolor">
            <span class="em-v4-chip__btncolor-label"><?php esc_html_e('Texte', 'em-wp'); ?></span>
            <?php em_wp_admin_render_color_field([
                'id'            => em_wp_v4_chip_color_id('emv4baink-', $key),
                'value'         => $badge['ink'],
                'input_class'   => 'em-v4-chip__badgeink',
                'preview_label' => __('Texte du badge', 'em-wp'),
            ]); ?>
        </span>
        <label class="em-v4-chip__badgeopt" title="<?php esc_attr_e('Forme du badge', 'em-wp'); ?>">
            <span><?php esc_html_e('Forme', 'em-wp'); ?></span>
            <select class="em-v4-chip__badgeshape">
                <?php foreach (em_wp_rubrique_badge_shapes() as $slug => $label) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"<?php selected($badge['shape'], $slug); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="em-v4-chip__badgeopt" title="<?php esc_attr_e('Animation du badge', 'em-wp'); ?>">
            <span><?php esc_html_e('Animation', 'em-wp'); ?></span>
            <select class="em-v4-chip__badgeanim">
                <?php foreach (em_wp_rubrique_badge_anims() as $slug => $label) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"<?php selected($badge['anim'], $slug); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="em-v4-chip__badgeopt" title="<?php esc_attr_e('Arrondi des coins (forme carrée), en px', 'em-wp'); ?>">
            <span><?php esc_html_e('Arrondi', 'em-wp'); ?></span>
            <input type="number" class="em-v4-chip__badgeradius" min="0" max="40" value="<?php echo (int) $badge['radius']; ?>">
        </label>
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
