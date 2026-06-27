<?php
/**
 * Bandeau « Apparence » du builder (V4) : réglages globaux du bloc.
 *
 * Réglages globaux = ne se positionnent pas en lignes/colonnes : couleur de fond,
 * couleur du texte, couleur des liens, soulignement des liens. Plus une pastille
 * « Aperçu » mise à jour en temps réel par le script du builder.
 *
 * @package em-wp
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
function em_wp_v4_sort_global_fields(array $fields): array
{
    $order = ['background' => 0, 'text' => 1, 'link' => 2, 'link_hover' => 3, 'link_visited' => 4, 'link_underline' => 5, 'space_top' => 6, 'space_bottom' => 7, 'space_left' => 8, 'space_right' => 9];

    usort($fields, static function (array $a, array $b) use ($order): int {
        $ra = $order[(string) ($a['options']['role'] ?? '')] ?? 9;
        $rb = $order[(string) ($b['options']['role'] ?? '')] ?? 9;

        return $ra <=> $rb;
    });

    return $fields;
}

/**
 * Bandeau Apparence sur deux lignes : couleurs/liens, puis espacements.
 *
 * @param array<int, array<string, mixed>> $global_fields
 * @param array<string, mixed>             $content
 */
function em_wp_v4_render_appearance_lines(string $type, string $item, array $global_fields, string $form_id, array $content): void
{
    $sorted = em_wp_v4_sort_global_fields($global_fields);
    $colors = array_filter($sorted, static fn(array $f): bool => !in_array((string) $f['type'], ['number', 'select'], true));
    $spaces = array_filter($sorted, static fn(array $f): bool => (string) $f['type'] === 'number');
    $fonts = array_filter($sorted, static fn(array $f): bool => (string) $f['type'] === 'select');
    ?>
    <div class="em-v4-appearance__line">
        <span class="em-v4-appearance__title"><?php esc_html_e('Couleurs', 'em-wp'); ?></span>
        <?php foreach ($colors as $field) : ?>
            <?php em_wp_v4_render_appearance_field($type, $item, $field, $form_id, $content); ?>
        <?php endforeach; ?>
        <?php em_wp_v4_render_appearance_preview(); ?>
    </div>
    <?php if ($spaces !== [] || $fonts !== []) : ?>
        <div class="em-v4-appearance__line">
            <?php if ($spaces !== []) : ?>
                <span class="em-v4-appearance__title"><?php esc_html_e('Espacements', 'em-wp'); ?></span>
                <?php foreach ($spaces as $field) : ?>
                    <?php em_wp_v4_render_appearance_field($type, $item, $field, $form_id, $content); ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($fonts !== []) : ?>
                <span class="em-v4-appearance__title"><?php esc_html_e('Typos', 'em-wp'); ?></span>
                <?php foreach ($fonts as $field) : ?>
                    <?php em_wp_v4_render_appearance_field($type, $item, $field, $form_id, $content); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php
}

/**
 * Champ global d'apparence : couleur (picker), bascule (souligné) ou nombre (espace).
 *
 * @param array<string, mixed> $field
 * @param array<string, mixed> $content
 */
function em_wp_v4_render_appearance_field(string $type, string $item, array $field, string $form_id, array $content): void
{
    if ((string) $field['type'] === 'toggle') {
        em_wp_v4_render_appearance_toggle($field, $content);
        return;
    }

    if ((string) $field['type'] === 'number') {
        em_wp_v4_render_appearance_number($field, $content);
        return;
    }

    if ((string) $field['type'] === 'select') {
        em_wp_v4_render_appearance_select($field, $content);
        return;
    }

    $key = (string) $field['key'];
    $role = (string) ($field['options']['role'] ?? 'content');
    $value = (string) ($content[$key] ?? $field['default'] ?? '');
    ?>
    <div class="em-v4-appearance__item" data-role="<?php echo esc_attr($role); ?>">
        <span class="em-v4-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
        <?php em_wp_admin_render_color_field([
            'id'            => 'emv4c-' . sanitize_html_class($type . '-' . $item . '-' . $key),
            'name'          => 'fields[' . $key . ']',
            'value'         => $value,
            'default'       => (string) ($field['default'] ?? ''),
            'preview_label' => (string) $field['label'],
            'preview_type'  => $role === 'text' ? 'text' : 'swatch',
        ]); ?>
    </div>
    <?php
}

/**
 * Bascule globale (ex. souligner les liens).
 *
 * @param array<string, mixed> $field
 * @param array<string, mixed> $content
 */
function em_wp_v4_render_appearance_toggle(array $field, array $content): void
{
    $key = (string) $field['key'];
    $role = (string) ($field['options']['role'] ?? 'content');
    $checked = !empty($content[$key] ?? $field['default'] ?? false);
    ?>
    <div class="em-v4-appearance__item" data-role="<?php echo esc_attr($role); ?>">
        <label class="em-v4-appearance__toggle">
            <input type="hidden" name="fields[<?php echo esc_attr($key); ?>]" value="0">
            <input type="checkbox" class="em-v4-appearance__toggle-input" name="fields[<?php echo esc_attr($key); ?>]" value="1" <?php checked($checked); ?>>
            <span class="em-v4-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
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
function em_wp_v4_render_appearance_number(array $field, array $content): void
{
    $key = (string) $field['key'];
    $role = (string) ($field['options']['role'] ?? 'content');
    $value = (int) ($content[$key] ?? $field['default'] ?? 0);
    ?>
    <div class="em-v4-appearance__item" data-role="<?php echo esc_attr($role); ?>">
        <label class="em-v4-appearance__num">
            <span class="em-v4-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
            <input type="number" class="em-v4-appearance__num-input" name="fields[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) $value); ?>" min="0" max="200" step="2">
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
function em_wp_v4_render_appearance_select(array $field, array $content): void
{
    $key = (string) $field['key'];
    $role = (string) ($field['options']['role'] ?? 'content');
    $value = (string) ($content[$key] ?? $field['default'] ?? '');
    $choices = $role === 'font' ? em_wp_rubrique_font_choices() : [];
    ?>
    <div class="em-v4-appearance__item" data-role="<?php echo esc_attr($role); ?>">
        <label class="em-v4-appearance__font">
            <span class="em-v4-appearance__label"><?php echo esc_html((string) $field['label']); ?></span>
            <select class="em-v4-appearance__font-input" name="fields[<?php echo esc_attr($key); ?>]">
                <?php foreach ($choices as $ckey => $choice) : ?>
                    <option value="<?php echo esc_attr($ckey); ?>" data-stack="<?php echo esc_attr($choice['stack']); ?>" style="font-family:<?php echo esc_attr($choice['stack']); ?>" <?php selected($value, $ckey); ?>><?php echo esc_html($choice['label']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <?php
}

/**
 * Pastille « Aperçu » : fond + texte + lien (mise à jour live par le script).
 */
function em_wp_v4_render_appearance_preview(): void
{
    ?>
    <span class="em-v4-appearance__preview">
        <span class="em-v4-appearance__preview-label"><?php esc_html_e('Aperçu', 'em-wp'); ?></span>
        <span class="em-v4-appearance__preview-box">
            <span class="ap-text"><?php esc_html_e('Texte', 'em-wp'); ?></span>
            <a class="ap-link" href="#" onclick="return false;" title="<?php esc_attr_e('Clique pour tester l’état visité', 'em-wp'); ?>"><?php esc_html_e('lien', 'em-wp'); ?></a>
        </span>
    </span>
    <?php
}
