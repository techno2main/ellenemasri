<?php
/**
 * Contrôles de VALEUR d'une chip du builder (EM-SITE).
 *
 * Extrait de chip.php : rendu du contrôle d'édition selon le type de champ
 * (texte, média, plateforme, réseau, bouton…) + listes déroulantes plateforme /
 * réseaux. Séparé pour garder chaque fichier sous 300 lignes.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contrôle de valeur d'une chip selon le type (image = média, icône = liste).
 */
function em_site_render_chip_value(string $type, string $value, string $key = ''): void
{
    if (em_site_render_chip_media_value($type, $value)) {
        return;
    }

    if ($type === 'text') {
        $tv = em_site_rubrique_text_value($value);
        ?>
        <input type="text" class="em-site-chip__value" value="<?php echo esc_attr($tv['text']); ?>" placeholder="<?php esc_attr_e('Contenu…', 'em-site'); ?>">
        <input type="url" class="em-site-chip__tlink" value="<?php echo esc_url($tv['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-site'); ?>">
        <?php
        return;
    }

    if ($type === 'textarea') {
        $tv = em_site_rubrique_text_value($value);
        $editor_html = em_site_rubrique_textarea_editor_html((string) $tv['text']);
        ?>
        <span class="em-site-chip__rich">
            <span class="em-site-chip__richbar">
                <button type="button" class="button button-small em-site-richbtn" data-cmd="bold" title="<?php esc_attr_e('Gras', 'em-site'); ?>"><strong>B</strong></button>
                <button type="button" class="button button-small em-site-richbtn" data-cmd="italic" title="<?php esc_attr_e('Italique', 'em-site'); ?>"><em>I</em></button>
                <button type="button" class="button button-small em-site-richbtn" data-cmd="underline" title="<?php esc_attr_e('Souligné', 'em-site'); ?>"><span style="text-decoration:underline;">U</span></button>
                <button type="button" class="button button-small em-site-richbtn" data-cmd="insertUnorderedList" title="<?php esc_attr_e('Liste', 'em-site'); ?>">•</button>
                <button type="button" class="button button-small em-site-richbtn" data-cmd="justifyLeft" title="<?php esc_attr_e('Aligner à gauche', 'em-site'); ?>"><span class="dashicons dashicons-editor-alignleft" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-site-richbtn" data-cmd="justifyCenter" title="<?php esc_attr_e('Centrer', 'em-site'); ?>"><span class="dashicons dashicons-editor-aligncenter" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-site-richbtn" data-cmd="justifyRight" title="<?php esc_attr_e('Aligner à droite', 'em-site'); ?>"><span class="dashicons dashicons-editor-alignright" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-site-richbtn" data-cmd="justifyFull" title="<?php esc_attr_e('Justifier', 'em-site'); ?>"><span class="dashicons dashicons-editor-justify" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-site-richbtn" data-action="link" title="<?php esc_attr_e('Ajouter un lien sur la sélection', 'em-site'); ?>"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-site-richbtn" data-cmd="unlink" title="<?php esc_attr_e('Retirer le lien', 'em-site'); ?>"><span class="dashicons dashicons-editor-unlink" aria-hidden="true"></span></button>
                <button type="button" class="button button-small em-site-richbtn" data-action="anchor" title="<?php esc_attr_e('Ajouter une ancre sur la sélection', 'em-site'); ?>">#</button>
                <span class="em-site-chip__richcolor" title="<?php esc_attr_e('Couleur du texte', 'em-site'); ?>">
                    <?php em_site_admin_render_color_field([
                        'id'            => em_site_chip_color_id('em-site-rc-', $key),
                        'value'         => '#000000',
                        'input_class'   => 'em-site-richcolor',
                        'preview_label' => __('Couleur du texte', 'em-site'),
                    ]); ?>
                </span>
            </span>
            <div class="em-site-chip__richedit" contenteditable="true" spellcheck="false" autocorrect="off" autocapitalize="off" data-gramm="false" data-placeholder="<?php esc_attr_e('Contenu enrichi…', 'em-site'); ?>"><?php echo $editor_html; ?></div>
            <input type="hidden" class="em-site-chip__value" value="<?php echo esc_attr((string) $tv['text']); ?>">
        </span>
        <?php
        return;
    }

    if ($type === 'text_image') {
        $ti = em_site_rubrique_text_image_value($value);
        ?>
        <input type="text" class="em-site-chip__titext" value="<?php echo esc_attr($ti['text']); ?>" placeholder="<?php esc_attr_e('Contenu…', 'em-site'); ?>">
        <input type="url" class="em-site-chip__tlink" value="<?php echo esc_url($ti['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-site'); ?>">
        <?php em_site_render_chip_textstyle($key, $ti['style']); ?>
        <?php em_site_render_chip_value('image', (string) wp_json_encode($ti['image'])); ?>
        <?php
        return;
    }

    if ($type === 'text_icon' || $type === 'icon_text') {
        $ti = em_site_rubrique_text_icon_value($value);
        ?>
        <input type="text" class="em-site-chip__titext" value="<?php echo esc_attr($ti['text']); ?>" placeholder="<?php esc_attr_e('Contenu…', 'em-site'); ?>">
        <input type="url" class="em-site-chip__tlink" value="<?php echo esc_url($ti['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-site'); ?>">
        <?php if (function_exists('em_site_admin_render_dashicon_chooser')) {
            em_site_admin_render_dashicon_chooser([
                'value_class' => 'em-site-chip__dashicon',
                'selected' => $ti['icon'],
                'default_icon' => 'dashicons-screenoptions',
                'placeholder' => __('Choisir une icône BO', 'em-site'),
                'compact' => true,
            ]);
        } else {
            em_site_render_dashicon_select($ti['icon']);
        } ?>
        <?php
        return;
    }

    if ($type === 'text_text') {
        $tt = em_site_rubrique_text_text_value($value);
        ?>
        <span class="em-site-chip__tt-part">
            <input type="text" class="em-site-chip__titext" value="<?php echo esc_attr($tt['text']); ?>" placeholder="<?php esc_attr_e('Contenu 1…', 'em-site'); ?>">
            <input type="url" class="em-site-chip__tlink" value="<?php echo esc_url($tt['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-site'); ?>">
            <?php em_site_render_chip_textstyle($key, $tt['style']); ?>
        </span>
        <span class="em-site-chip__tt-part">
            <input type="text" class="em-site-chip__titext2" value="<?php echo esc_attr($tt['text2']); ?>" placeholder="<?php esc_attr_e('Contenu 2…', 'em-site'); ?>">
            <input type="url" class="em-site-chip__tlink2" value="<?php echo esc_url($tt['link2']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-site'); ?>">
            <?php em_site_render_chip_textstyle($key . '-2', $tt['style2']); ?>
        </span>
        <?php
        return;
    }

    if ($type === 'image') {
        $img = em_site_rubrique_image_value($value);
        $id = $img['id'];
        $thumb = $id ? wp_get_attachment_image_url($id, 'medium') : '';
        $full = $id ? wp_get_attachment_image_url($id, 'large') : '';
        ?>
        <span class="em-site-chip__media" data-url="<?php echo esc_attr((string) $full); ?>">
            <img class="em-site-chip__thumb" src="<?php echo esc_url((string) $thumb); ?>" alt="" <?php echo $thumb ? '' : 'hidden'; ?>>
            <button type="button" class="button button-small em-site-chip__pick"><?php esc_html_e('Choisir une image', 'em-site'); ?></button>
            <input type="hidden" class="em-site-chip__value" value="<?php echo esc_attr((string) $id); ?>">
        </span>
        <span class="em-site-chip__size">
            <label class="em-site-chip__sizelabel"><?php esc_html_e('Taille', 'em-site'); ?>
                <input type="range" class="em-site-chip__w" min="0" max="600" step="5" value="<?php echo (int) $img['w']; ?>" oninput="this.nextElementSibling.textContent=(this.value>0?this.value+'px':'auto')">
                <output class="em-site-chip__wout"><?php echo $img['w'] ? (int) $img['w'] . 'px' : esc_html__('auto', 'em-site'); ?></output>
            </label>
        </span>
        <input type="url" class="em-site-chip__url" value="<?php echo esc_url($img['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-site'); ?>">
        <?php em_site_render_scotchs_component([
            'hidden_class' => 'em-site-chip__itape-hidden',
            'hidden_checked' => empty($img['tape']),
            'hidden_title' => __('Masquer le scotch décoratif de l’image', 'em-site'),
            'color_class' => 'em-site-chip__itape-color',
            'color_value' => (string) ($img['tape_color'] ?? ''),
            'color_prefix' => 'em-site-itp-',
            'key' => $key,
        ]); ?>
        <?php
        return;
    }

    if ($type === 'button') {
        $btn = em_site_rubrique_button_value($value);
        ?>
        <input type="url" class="em-site-chip__url" value="<?php echo esc_url($btn['link']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-site'); ?>">
        <span class="em-site-chip__btncolor">
            <span class="em-site-chip__btncolor-label"><?php esc_html_e('Fond', 'em-site'); ?></span>
            <?php em_site_admin_render_color_field([
                'id'            => em_site_chip_color_id('em-site-bbg-', $key),
                'value'         => $btn['bg'],
                'input_class'   => 'em-site-chip__btnbg',
                'preview_label' => __('Fond du bouton', 'em-site'),
            ]); ?>
        </span>
        <span class="em-site-chip__btncolor">
            <span class="em-site-chip__btncolor-label"><?php esc_html_e('Texte', 'em-site'); ?></span>
            <?php em_site_admin_render_color_field([
                'id'            => em_site_chip_color_id('em-site-btx-', $key),
                'value'         => $btn['text'],
                'input_class'   => 'em-site-chip__btntext',
                'preview_label' => __('Texte du bouton', 'em-site'),
            ]); ?>
        </span>
        <label class="em-site-chip__btnmargin" title="<?php esc_attr_e('Marge à gauche du bouton (px)', 'em-site'); ?>">
            <span><?php esc_html_e('Marge avant', 'em-site'); ?></span>
            <input type="number" class="em-site-chip__btnml" min="0" max="200" value="<?php echo (int) $btn['ml']; ?>">
        </label>
        <label class="em-site-chip__btnmargin" title="<?php esc_attr_e('Marge à droite du bouton (px)', 'em-site'); ?>">
            <span><?php esc_html_e('Marge après', 'em-site'); ?></span>
            <input type="number" class="em-site-chip__btnmr" min="0" max="200" value="<?php echo (int) $btn['mr']; ?>">
        </label>
        <label class="em-site-chip__badgeopt" title="<?php esc_attr_e('Forme du bouton', 'em-site'); ?>">
            <span><?php esc_html_e('Forme', 'em-site'); ?></span>
            <select class="em-site-chip__btnshape">
                <?php foreach (em_site_rubrique_badge_shapes() as $slug => $label) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"<?php selected($btn['shape'], $slug); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="em-site-chip__badgeopt" title="<?php esc_attr_e('Animation du bouton', 'em-site'); ?>">
            <span><?php esc_html_e('Animation', 'em-site'); ?></span>
            <select class="em-site-chip__btnanim">
                <?php foreach (em_site_rubrique_badge_anims() as $slug => $label) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"<?php selected($btn['anim'], $slug); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="em-site-chip__badgeopt" title="<?php esc_attr_e('Arrondi des coins (forme carrée), en px', 'em-site'); ?>">
            <span><?php esc_html_e('Arrondi', 'em-site'); ?></span>
            <input type="number" class="em-site-chip__btnradius" min="0" max="40" value="<?php echo (int) $btn['radius']; ?>">
        </label>
        <?php
        return;
    }

    if ($type === 'animated_badge') {
        $badge = em_site_rubrique_animated_badge_value($value);
        ?>
        <input type="text" class="em-site-chip__btext" value="<?php echo esc_attr($badge['text']); ?>" placeholder="<?php esc_attr_e('Texte du badge…', 'em-site'); ?>">
        <span class="em-site-chip__btncolor">
            <span class="em-site-chip__btncolor-label"><?php esc_html_e('Fond', 'em-site'); ?></span>
            <?php em_site_admin_render_color_field([
                'id'            => em_site_chip_color_id('em-site-babg-', $key),
                'value'         => $badge['bg'],
                'input_class'   => 'em-site-chip__badgebg',
                'preview_label' => __('Fond du badge', 'em-site'),
            ]); ?>
        </span>
        <span class="em-site-chip__btncolor">
            <span class="em-site-chip__btncolor-label"><?php esc_html_e('Texte', 'em-site'); ?></span>
            <?php em_site_admin_render_color_field([
                'id'            => em_site_chip_color_id('em-site-baink-', $key),
                'value'         => $badge['ink'],
                'input_class'   => 'em-site-chip__badgeink',
                'preview_label' => __('Texte du badge', 'em-site'),
            ]); ?>
        </span>
        <label class="em-site-chip__badgeopt" title="<?php esc_attr_e('Forme du badge', 'em-site'); ?>">
            <span><?php esc_html_e('Forme', 'em-site'); ?></span>
            <select class="em-site-chip__badgeshape">
                <?php foreach (em_site_rubrique_badge_shapes() as $slug => $label) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"<?php selected($badge['shape'], $slug); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="em-site-chip__badgeopt" title="<?php esc_attr_e('Animation du badge', 'em-site'); ?>">
            <span><?php esc_html_e('Animation', 'em-site'); ?></span>
            <select class="em-site-chip__badgeanim">
                <?php foreach (em_site_rubrique_badge_anims() as $slug => $label) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"<?php selected($badge['anim'], $slug); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="em-site-chip__badgeopt" title="<?php esc_attr_e('Arrondi des coins (forme carrée), en px', 'em-site'); ?>">
            <span><?php esc_html_e('Arrondi', 'em-site'); ?></span>
            <input type="number" class="em-site-chip__badgeradius" min="0" max="40" value="<?php echo (int) $badge['radius']; ?>">
        </label>
        <?php
        return;
    }

    if ($type === 'platform_block') {
        $block = em_site_rubrique_platform_block_value($value);
        ?>
        <input type="text" class="em-site-chip__ptitle" value="<?php echo esc_attr($block['label']); ?>" placeholder="<?php esc_attr_e('Titre (ex. LISTEN ON)', 'em-site'); ?>" title="<?php esc_attr_e('Sur-titre de la carte', 'em-site'); ?>">
        <?php em_site_render_platform_select($block['platform']); ?>
        <?php em_site_render_stream_target_select((string) $block['platform'], (string) ($block['stream_item'] ?? ''), (string) $block['url']); ?>
        <input type="url" class="em-site-chip__url" value="<?php echo esc_url($block['url']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-site'); ?>">
        <?php
        return;
    }

    if ($type === 'icon') {
        $icon = em_site_rubrique_icon_value($value);
        em_site_render_platform_select($icon['platform']);
        em_site_render_stream_target_select((string) $icon['platform'], (string) ($icon['stream_item'] ?? ''), (string) $icon['url']);
        ?>
        <input type="url" class="em-site-chip__url" value="<?php echo esc_url($icon['url']); ?>" placeholder="<?php esc_attr_e('Lien (https://… ou #ancre)', 'em-site'); ?>">
        <?php
        return;
    }
    ?>
    <input type="text" class="em-site-chip__value" value="<?php echo esc_attr($value); ?>" placeholder="<?php esc_attr_e('Contenu…', 'em-site'); ?>">
    <?php
}

/**
 * Liste déroulante des plateformes (icône + couleur + libellé), mutualisée par
 * les champs « icône » et « Bloc Plateforme ».
 */
function em_site_render_platform_select(string $selected): void
{
    ?>
    <select class="em-site-chip__platform">
        <option value=""><?php esc_html_e('— Choisir —', 'em-site'); ?></option>
        <?php foreach (em_site_rubrique_platform_choices() as $pkey => $choice) : ?>
            <option value="<?php echo esc_attr($pkey); ?>" data-icon="<?php echo esc_attr($choice['icon']); ?>" data-color="<?php echo esc_attr((string) ($choice['color'] ?? '')); ?>" data-label="<?php echo esc_attr($choice['label']); ?>" <?php selected($selected, $pkey); ?>><?php echo esc_html($choice['group'] . ' — ' . $choice['label']); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Liste déroulante des Dashicons BO mutualisée.
 */
function em_site_render_dashicon_select(string $selected): void
{
    ?>
    <select class="em-site-chip__dashicon">
        <option value=""><?php esc_html_e('— Choisir —', 'em-site'); ?></option>
        <?php foreach ((function_exists('em_site_dashicons_all') ? em_site_dashicons_all() : ['dashicons-screenoptions']) as $icon) : ?>
            <option value="<?php echo esc_attr($icon); ?>"<?php selected($selected, $icon); ?>><?php echo esc_html($icon); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Sélecteur de cible Stream inline (global + items existants).
 */
function em_site_render_stream_target_select(string $platform, string $selected_stream_item, string $selected_url): void
{
    $selected_stream_item = sanitize_key($selected_stream_item);
    $selected_url = trim($selected_url);
    $is_stream_platform = str_starts_with($platform, 'stream:');

    if ($selected_stream_item === '' && str_starts_with($selected_url, '#stream-')) {
        $selected_stream_item = sanitize_key(ltrim($selected_url, '#'));
    }

    $links = [];
    if (function_exists('em_site_get_items')) {
        foreach (em_site_get_items('stream') as $stream_slug => $stream_item) {
            $stream_slug = sanitize_key((string) $stream_slug);
            if ($stream_slug === '') {
                continue;
            }

            $anchor = strpos($stream_slug, 'stream-') === 0
                ? ('#' . $stream_slug)
                : ('#stream-' . $stream_slug);

            $label_raw = is_array($stream_item)
                ? (string) ($stream_item['label'] ?? $stream_slug)
                : $stream_slug;
            $label_raw = trim($label_raw);
            if ($label_raw === '') {
                $label_raw = $stream_slug;
            }

            $label_clean = preg_replace('/^stream\s*[-_:]?\s*/i', '', $label_raw);
            $label_clean = trim((string) $label_clean);
            if ($label_clean === '') {
                $label_clean = $stream_slug;
            }

            $links[] = [
                'url' => $anchor,
                'label' => 'Stream ' . ucwords(str_replace(['-', '_'], ' ', $label_clean)),
            ];
        }
    }
    ?>
    <select class="em-site-chip__streamtarget" title="<?php esc_attr_e('Cible stream inline', 'em-site'); ?>"<?php echo $is_stream_platform ? '' : ' hidden disabled'; ?>>
        <option value="#stream"<?php selected($selected_stream_item === '' ? '#stream' : '', '#stream'); ?>><?php esc_html_e('Stream Global', 'em-site'); ?> (#stream)</option>
        <?php foreach ($links as $link) : ?>
            <?php $stream_slug = sanitize_key(ltrim((string) $link['url'], '#')); ?>
            <option value="<?php echo esc_attr((string) $link['url']); ?>" data-stream-slug="<?php echo esc_attr($stream_slug); ?>"<?php selected($selected_stream_item, $stream_slug); ?>><?php echo esc_html((string) $link['label']); ?> (<?php echo esc_html((string) $link['url']); ?>)</option>
        <?php endforeach; ?>
    </select>
    <?php
}
