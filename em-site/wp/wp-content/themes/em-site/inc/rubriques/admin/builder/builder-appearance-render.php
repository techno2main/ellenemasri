<?php
/**
 * Bandeau « Apparence » du builder (EM-SITE) : réglages globaux du bloc.
 *
 * Réglages globaux = ne se positionnent pas en lignes/colonnes : couleur de fond,
 * couleur du texte, couleur des liens, soulignement des liens. Plus une pastille
 * « Aperçu » mise à jour en temps réel par le script du builder.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trie les champs globaux dans l'ordre d'affichage (fond, texte, liens, souligné).
 *
 * @param array<int, array<string, mixed>> $fields
 * @return array<int, array<string, mixed>>
 */
function em_site_sort_global_fields(array $fields): array
{
    $order = ['background' => 0, 'background_transparent' => 0.5, 'text' => 1, 'link' => 2, 'link_hover' => 3, 'link_visited' => 4, 'link_underline' => 5, 'space_top' => 6, 'space_bottom' => 7, 'space_left' => 8, 'space_right' => 9, 'font' => 10, 'background_image' => 11, 'background_pos' => 12, 'background_opacity' => 13, 'background_mirror' => 14];

    usort($fields, static function (array $a, array $b) use ($order): int {
        $ra = $order[(string) ($a['options']['role'] ?? '')] ?? 9;
        $rb = $order[(string) ($b['options']['role'] ?? '')] ?? 9;

        return $ra <=> $rb;
    });

    return $fields;
}

/**
 * Bandeau Apparence sur 4 lignes : Couleurs, Espacements, Typos, Image de fond.
 *
 * @param array<int, array<string, mixed>> $global_fields
 * @param array<string, mixed>             $content
 */
function em_site_render_appearance_lines(string $type, string $item, array $global_fields, string $form_id, array $content): void
{
    $sorted = em_site_sort_global_fields($global_fields);
    $role_of = static fn(array $f): string => (string) ($f['options']['role'] ?? '');
    $space_roles = ['space_top', 'space_bottom', 'space_left', 'space_right'];
    $colors = array_filter($sorted, static fn(array $f): bool => (string) $f['type'] === 'color' || ((string) $f['type'] === 'toggle' && in_array($role_of($f), ['link_underline', 'background_transparent'], true)));
    $spaces = array_filter($sorted, static fn(array $f): bool => (string) $f['type'] === 'number' && in_array($role_of($f), $space_roles, true));
    $fonts = array_filter($sorted, static fn(array $f): bool => (string) $f['type'] === 'select' && $role_of($f) === 'font');
    $bg_images = array_filter($sorted, static fn(array $f): bool => (string) $f['type'] === 'image' && $role_of($f) === 'background_image');
    $bg_positions = array_filter($sorted, static fn(array $f): bool => (string) $f['type'] === 'select' && $role_of($f) === 'background_pos');
    $bg_opacities = array_filter($sorted, static fn(array $f): bool => (string) $f['type'] === 'number' && $role_of($f) === 'background_opacity');
    $bg_mirrors = array_filter($sorted, static fn(array $f): bool => (string) $f['type'] === 'toggle' && $role_of($f) === 'background_mirror');
    ?>
    <div class="em-site-appearance__line em-site-appearance__line--colors">
        <span class="em-site-appearance__title"><?php esc_html_e('Couleurs', 'em-site'); ?></span>
        <?php foreach ($colors as $field) : ?>
            <?php em_site_render_appearance_field($type, $item, $field, $form_id, $content); ?>
        <?php endforeach; ?>
    </div>
    <?php if ($spaces !== []) : ?>
        <div class="em-site-appearance__line em-site-appearance__line--spaces">
            <span class="em-site-appearance__title"><?php esc_html_e('Espacements', 'em-site'); ?></span>
            <?php em_site_render_spacing_pairs($type, $item, $spaces, $form_id, $content); ?>
        </div>
    <?php endif; ?>
    <?php if ($fonts !== []) : ?>
        <div class="em-site-appearance__line em-site-appearance__line--fonts">
            <span class="em-site-appearance__title"><?php esc_html_e('Typos', 'em-site'); ?></span>
            <?php foreach ($fonts as $field) : ?>
                <?php em_site_render_appearance_field($type, $item, $field, $form_id, $content); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($bg_images !== [] || $bg_positions !== []) : ?>
        <div class="em-site-appearance__line em-site-appearance__line--bgimage">
            <span class="em-site-appearance__title"><?php esc_html_e('Image de fond', 'em-site'); ?></span>
            <?php foreach ($bg_images as $field) : ?>
                <?php em_site_render_appearance_bg_image($field, $content); ?>
            <?php endforeach; ?>
            <?php foreach ($bg_positions as $field) : ?>
                <?php em_site_render_appearance_select($field, $content); ?>
            <?php endforeach; ?>
            <?php foreach ($bg_opacities as $field) : ?>
                <?php em_site_render_appearance_bg_opacity($field, $content); ?>
            <?php endforeach; ?>
            <?php foreach ($bg_mirrors as $field) : ?>
                <?php em_site_render_appearance_toggle($field, $content); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php
}

/**
 * Espacements par paires liables : haut/bas et gauche/droite, séparés par une
 * icône « chaîne » qui synchronise les deux valeurs quand elle est active.
 *
 * @param array<int, array<string, mixed>> $spaces
 * @param array<string, mixed>             $content
 */
function em_site_render_spacing_pairs(string $type, string $item, array $spaces, string $form_id, array $content): void
{
    $by_role = [];
    foreach ($spaces as $f) {
        $by_role[(string) ($f['options']['role'] ?? '')] = $f;
    }

    $pairs = [
        'vertical'   => ['space_top', 'space_bottom', __('Lier haut et bas', 'em-site')],
        'horizontal' => ['space_left', 'space_right', __('Lier gauche et droite', 'em-site')],
    ];

    foreach ($pairs as $axis => $pair) {
        [$a, $b, $tip] = $pair;
        if (!isset($by_role[$a], $by_role[$b])) {
            continue;
        }
        ?>
        <span class="em-site-appearance__group" data-axis="<?php echo esc_attr($axis); ?>">
            <?php em_site_render_appearance_field($type, $item, $by_role[$a], $form_id, $content); ?>
            <button type="button" class="em-site-appearance__chain" aria-pressed="false" title="<?php echo esc_attr($tip); ?>" aria-label="<?php echo esc_attr($tip); ?>">
                <span class="dashicons dashicons-editor-unlink" aria-hidden="true"></span>
            </button>
            <?php em_site_render_appearance_field($type, $item, $by_role[$b], $form_id, $content); ?>
        </span>
        <?php
        unset($by_role[$a], $by_role[$b]);
    }

    foreach ($by_role as $field) {
        em_site_render_appearance_field($type, $item, $field, $form_id, $content);
    }
}

/**
 * Champ global d'apparence : couleur (picker), bascule (souligné) ou nombre (espace).
 *
 * @param array<string, mixed> $field
 * @param array<string, mixed> $content
 */
function em_site_render_appearance_field(string $type, string $item, array $field, string $form_id, array $content): void
{
    if ((string) $field['type'] === 'toggle') {
        em_site_render_appearance_toggle($field, $content);
        return;
    }

    if ((string) $field['type'] === 'number') {
        em_site_render_appearance_number($field, $content);
        return;
    }

    if ((string) $field['type'] === 'select') {
        em_site_render_appearance_select($field, $content);
        return;
    }

    $key = (string) $field['key'];
    $role = (string) ($field['options']['role'] ?? 'content');
    $value = (string) ($content[$key] ?? $field['default'] ?? '');
    $args = [
        'id'            => 'em-site-c-' . sanitize_html_class($type . '-' . $item . '-' . $key),
        'name'          => 'fields[' . $key . ']',
        'value'         => $value,
        'default'       => (string) ($field['default'] ?? ''),
        'preview_label' => (string) $field['label'],
        'preview_type'  => $role === 'text' ? 'text' : 'swatch',
    ];
    // La pastille « Texte » s'affiche sur le fond choisi (couleur de fond du bloc).
    if ($role === 'text') {
        $args['bg_target_id'] = 'em-site-c-' . sanitize_html_class($type . '-' . $item . '-bg_color');
    }
    ?>
    <div class="em-site-appearance__item" data-role="<?php echo esc_attr($role); ?>">
        <span class="em-site-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
        <?php em_site_admin_render_color_field($args); ?>
    </div>
    <?php
}

/**
 * Bascule globale (ex. souligner les liens).
 *
 * @param array<string, mixed> $field
 * @param array<string, mixed> $content
 */
function em_site_render_appearance_toggle(array $field, array $content): void
{
    $key = (string) $field['key'];
    $role = (string) ($field['options']['role'] ?? 'content');
    $checked = !empty($content[$key] ?? $field['default'] ?? false);
    ?>
    <div class="em-site-appearance__item" data-role="<?php echo esc_attr($role); ?>">
        <label class="em-site-appearance__toggle">
            <input type="hidden" name="fields[<?php echo esc_attr($key); ?>]" value="0">
            <input type="checkbox" class="em-site-appearance__toggle-input" name="fields[<?php echo esc_attr($key); ?>]" value="1" <?php checked($checked); ?>>
            <span class="em-site-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
        </label>
    </div>
    <?php
}

/**
 * Champ nombre global (ex. espace haut/bas en px).
 *
 * @param array<string, mixed> $field
 * @param array<string, mixed> $content
 */
function em_site_render_appearance_number(array $field, array $content): void
{
    $key = (string) $field['key'];
    $role = (string) ($field['options']['role'] ?? 'content');
    $value = (int) ($content[$key] ?? $field['default'] ?? 0);
    ?>
    <div class="em-site-appearance__item" data-role="<?php echo esc_attr($role); ?>">
        <label class="em-site-appearance__num">
            <span class="em-site-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
            <input type="number" class="em-site-appearance__num-input" name="fields[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) $value); ?>" min="0" max="200" step="2">
        </label>
    </div>
    <?php
}

/**
 * Liste déroulante globale (ex. police de la rubrique).
 *
 * @param array<string, mixed> $field
 * @param array<string, mixed> $content
 */
function em_site_render_appearance_select(array $field, array $content): void
{
    $key = (string) $field['key'];
    $role = (string) ($field['options']['role'] ?? 'content');
    $value = (string) ($content[$key] ?? $field['default'] ?? '');

    if ($role === 'background_pos') {
        $choices = em_site_rubrique_bg_position_choices();
        ?>
        <div class="em-site-appearance__item" data-role="<?php echo esc_attr($role); ?>">
            <label class="em-site-appearance__font">
                <span class="em-site-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
                <select class="em-site-appearance__bgpos-input" name="fields[<?php echo esc_attr($key); ?>]">
                    <?php foreach ($choices as $ckey => $clabel) : ?>
                        <option value="<?php echo esc_attr($ckey); ?>" <?php selected($value, $ckey); ?>><?php echo esc_html($clabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <?php
        return;
    }

    $choices = $role === 'font' ? em_site_rubrique_font_choices() : [];
    ?>
    <div class="em-site-appearance__item" data-role="<?php echo esc_attr($role); ?>">
        <label class="em-site-appearance__font">
            <span class="em-site-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
            <select class="em-site-appearance__font-input" name="fields[<?php echo esc_attr($key); ?>]">
                <?php foreach ($choices as $ckey => $choice) : ?>
                    <option value="<?php echo esc_attr($ckey); ?>" data-stack="<?php echo esc_attr($choice['stack']); ?>" style="font-family:<?php echo esc_attr($choice['stack']); ?>" <?php selected($value, $ckey); ?>><?php echo esc_html($choice['label']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <?php
}

/**
 * Champ « Image de fond » du bloc : sélecteur média (bibliothèque WP) + vignette.
 *
 * Stocke uniquement l'ID du média (le sanitizer image encode ensuite en JSON).
 *
 * @param array<string, mixed> $field
 * @param array<string, mixed> $content
 */
function em_site_render_appearance_bg_image(array $field, array $content): void
{
    $key = (string) $field['key'];
    $value = em_site_rubrique_image_value($content[$key] ?? $field['default'] ?? '');
    $id = (int) $value['id'];
    $thumb = $id > 0 ? (string) wp_get_attachment_image_url($id, 'medium') : '';
    $full = $id > 0 ? (string) wp_get_attachment_image_url($id, 'full') : '';
    ?>
    <div class="em-site-appearance__item em-site-appearance__bgimg" data-role="background_image">
        <span class="em-site-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
        <span class="em-site-appearance__bgmedia" data-url="<?php echo esc_url($full); ?>">
            <img class="em-site-appearance__bgthumb" src="<?php echo esc_url($thumb); ?>" alt=""<?php echo $thumb === '' ? ' hidden' : ''; ?>>
            <button type="button" class="button button-small em-site-appearance__bgpick"><?php esc_html_e('Choisir', 'em-site'); ?></button>
            <button type="button" class="em-site-appearance__bgclear" title="<?php esc_attr_e('Retirer l’image', 'em-site'); ?>" aria-label="<?php esc_attr_e('Retirer l’image', 'em-site'); ?>"<?php echo $id > 0 ? '' : ' hidden'; ?>>&times;</button>
            <input type="hidden" class="em-site-appearance__bgid" name="fields[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($id > 0 ? (string) $id : ''); ?>">
        </span>
    </div>
    <?php
}

/**
 * Opacité de l'image de fond (0–100 %), via curseur.
 *
 * @param array<string, mixed> $field
 * @param array<string, mixed> $content
 */
function em_site_render_appearance_bg_opacity(array $field, array $content): void
{
    $key = (string) $field['key'];
    $value = max(0, min(100, (int) ($content[$key] ?? $field['default'] ?? 100)));
    ?>
    <div class="em-site-appearance__item em-site-appearance__bgopacity" data-role="background_opacity">
        <label class="em-site-appearance__num">
            <span class="em-site-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
            <input type="range" class="em-site-appearance__bgopacity-input" name="fields[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) $value); ?>" min="0" max="100" step="1" oninput="this.nextElementSibling.textContent=this.value+'%'">
            <output class="em-site-appearance__bgopacity-out"><?php echo esc_html($value . '%'); ?></output>
        </label>
    </div>
    <?php
}

