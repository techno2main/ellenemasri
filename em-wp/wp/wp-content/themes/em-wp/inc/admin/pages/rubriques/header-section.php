<?php
/**
 * Section HEADER du squelette (composite HERO + SLIDER).
 *
 * HEADER n'est PAS une rubrique V4 : c'est une « section » du squelette qui
 * compose une rubrique HERO et, optionnellement, une rubrique SLIDER (toutes
 * deux gérées comme des rubriques V4 indépendantes). Avant d'afficher les items
 * disponibles, l'admin choisit une MATRICE (HERO seul / HERO + SLIDER) et, si
 * les deux, leur POSITION. La configuration est persistée par template dans
 * l'option `em_wp_v4_header_<template>`.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug de la section HEADER dans le squelette.
 */
function em_wp_admin_header_section_slug(): string
{
    return 'header';
}

/**
 * Slug du type de rubrique V4 jouant le rôle « hero » ou « slider ».
 *
 * Détecté dynamiquement parmi les types enregistrés (l'admin a pu les nommer
 * « hero »/« heros »/« slider »…). Renvoie '' si aucun type ne correspond.
 */
function em_wp_admin_header_part_type_slug(string $keyword): string
{
    if (!function_exists('em_wp_rubrique_type_registry')) {
        return '';
    }

    $registry = em_wp_rubrique_type_registry();
    $slugs = array_keys($registry);

    // 1) Slug exact, 2) slug contenant le mot-clé.
    if (in_array($keyword, $slugs, true)) {
        return $keyword;
    }

    foreach ($slugs as $slug) {
        if (strpos((string) $slug, $keyword) !== false) {
            return (string) $slug;
        }
    }

    // 3) Libellé contenant le mot-clé : indispensable car renommer une rubrique
    // change le LIBELLÉ mais pas le SLUG (ex. HERO créé puis renommé garde slug
    // « header »). Le rôle HERO/SLIDER se reconnaît alors au libellé saisi.
    foreach ($registry as $slug => $def) {
        $label = strtolower((string) ($def['label'] ?? ''));

        if ($label !== '' && strpos($label, $keyword) !== false) {
            return (string) $slug;
        }
    }

    return '';
}

/**
 * Nom d'option de la config HEADER pour un template.
 */
function em_wp_admin_header_section_option_name(string $template): string
{
    return 'em_wp_v4_header_' . sanitize_key($template);
}

/**
 * Choix de ratio HERO / SLIDER (largeur des colonnes).
 *
 * @return array<string, string>
 */
function em_wp_admin_header_ratio_choices(): array
{
    return [
        '75-25' => '75 / 25',
        '70-30' => '70 / 30',
        '60-40' => '60 / 40',
        '50-50' => '50 / 50',
    ];
}

/**
 * Colonnes CSS (grid-template-columns) selon le ratio et la position.
 */
function em_wp_admin_header_ratio_columns(string $ratio, bool $slider_left): string
{
    $map = ['75-25' => [3, 1], '70-30' => [7, 3], '60-40' => [3, 2], '50-50' => [1, 1]];
    [$hero, $slider] = $map[$ratio] ?? [3, 1];

    return $slider_left
        ? $slider . 'fr ' . $hero . 'fr'
        : $hero . 'fr ' . $slider . 'fr';
}

/**
 * Apparence partagée du HEADER (valeurs par défaut).
 *
 * @return array<string, mixed>
 */
function em_wp_admin_header_appearance_defaults(): array
{
    return [
        'bg'               => '',
        'bg_image_id'      => 0,
        'bg_image_pos'     => 'cover',
        'bg_image_opacity' => 100,
        'bg_image_mirror'  => false,
        'pt'               => 0,
        'pb'               => 0,
        'pl'               => 0,
        'pr'               => 0,
    ];
}

/**
 * Normalise un tableau d'apparence HEADER.
 *
 * @param array<string, mixed> $raw
 * @return array<string, mixed>
 */
function em_wp_admin_header_appearance_normalize(array $raw): array
{
    $bg = sanitize_hex_color((string) ($raw['bg'] ?? ''));

    return [
        'bg'               => is_string($bg) ? $bg : '',
        'bg_image_id'      => max(0, (int) ($raw['bg_image_id'] ?? 0)),
        'bg_image_pos'     => sanitize_key((string) ($raw['bg_image_pos'] ?? 'cover')),
        'bg_image_opacity' => max(0, min(100, (int) ($raw['bg_image_opacity'] ?? 100))),
        'bg_image_mirror'  => !empty($raw['bg_image_mirror']),
        'pt'               => max(0, (int) ($raw['pt'] ?? 0)),
        'pb'               => max(0, (int) ($raw['pb'] ?? 0)),
        'pl'               => max(0, (int) ($raw['pl'] ?? 0)),
        'pr'               => max(0, (int) ($raw['pr'] ?? 0)),
    ];
}

/**
 * Config HEADER normalisée d'un template.
 *
 * @return array{matrix:string, position:string, hero:string, slider:string, ratio:string, appearance:array<string,mixed>}
 */
function em_wp_admin_header_section_get(string $template): array
{
    $raw = $template !== '' ? get_option(em_wp_admin_header_section_option_name($template), []) : [];

    if (!is_array($raw)) {
        $raw = [];
    }

    $ratio = (string) ($raw['ratio'] ?? '');
    if (!isset(em_wp_admin_header_ratio_choices()[$ratio])) {
        $ratio = '75-25';
    }

    return [
        'matrix'     => ($raw['matrix'] ?? '') === 'hero_slider' ? 'hero_slider' : 'hero',
        'position'   => ($raw['position'] ?? '') === 'slider_left' ? 'slider_left' : 'hero_left',
        'hero'       => sanitize_key((string) ($raw['hero'] ?? '')),
        'slider'     => sanitize_key((string) ($raw['slider'] ?? '')),
        'ratio'      => $ratio,
        'appearance' => em_wp_admin_header_appearance_normalize(is_array($raw['appearance'] ?? null) ? $raw['appearance'] : []),
    ];
}

/**
 * Persiste la config HEADER d'un template.
 *
 * @param array<string, mixed> $data
 */
function em_wp_admin_header_section_save(string $template, array $data): void
{
    if ($template === '') {
        return;
    }

    $ratio = (string) ($data['ratio'] ?? '');
    if (!isset(em_wp_admin_header_ratio_choices()[$ratio])) {
        $ratio = '75-25';
    }

    update_option(em_wp_admin_header_section_option_name($template), [
        'matrix'     => ($data['matrix'] ?? '') === 'hero_slider' ? 'hero_slider' : 'hero',
        'position'   => ($data['position'] ?? '') === 'slider_left' ? 'slider_left' : 'hero_left',
        'hero'       => sanitize_key((string) ($data['hero'] ?? '')),
        'slider'     => sanitize_key((string) ($data['slider'] ?? '')),
        'ratio'      => $ratio,
        'appearance' => em_wp_admin_header_appearance_normalize(is_array($data['appearance'] ?? null) ? $data['appearance'] : []),
    ], false);
}

/**
 * Item effectif (branché ou défaut) pour une partie HERO/SLIDER d'un template.
 */
function em_wp_admin_header_effective_item(string $template, string $part): string
{
    $type = em_wp_admin_header_part_type_slug($part);

    if ($type === '' || !function_exists('em_wp_v4_get_items')) {
        return '';
    }

    $cfg = em_wp_admin_header_section_get($template);
    $saved = (string) ($cfg[$part] ?? '');
    $items = em_wp_v4_get_items($type);

    if ($saved !== '' && isset($items[$saved])) {
        return $saved;
    }

    return function_exists('em_wp_rubrique_default_item_slug')
        ? em_wp_rubrique_default_item_slug($type)
        : '';
}

/**
 * URL de l'image de fond de l'item HERO branché (héritée au niveau HEADER).
 *
 * Le fond palmier vit aujourd'hui sur la rubrique HERO ; on le « remonte » au
 * HEADER (fond partagé) en lisant l'image de fond de l'item HERO effectif.
 */
function em_wp_admin_header_hero_bg_image_url(string $template): string
{
    $hero_type = em_wp_admin_header_part_type_slug('hero');
    $hero_item = em_wp_admin_header_effective_item($template, 'hero');

    if ($hero_type === '' || $hero_item === '' || !function_exists('em_wp_v4_get_item')) {
        return '';
    }

    $item = em_wp_v4_get_item($hero_type, $hero_item);
    $fields = $item['fields'] ?? [];

    if ($fields === []) {
        return '';
    }

    $content = function_exists('em_wp_v4_get_item_content')
        ? em_wp_v4_get_item_content($hero_type, $hero_item)
        : [];

    foreach ($fields as $field) {
        if (($field['type'] ?? '') !== 'image' || ($field['options']['role'] ?? '') !== 'background_image') {
            continue;
        }

        $value = $content[$field['key']] ?? ($field['default'] ?? '');
        $img = em_wp_rubrique_image_value($value);

        if ((int) $img['id'] > 0) {
            $url = (string) wp_get_attachment_image_url((int) $img['id'], 'full');
            if ($url !== '') {
                return $url;
            }
        }
    }

    return '';
}

/**
 * Style inline (variables CSS) du conteneur HEADER : fond partagé (couleur +
 * image héritée du HERO + opacité + miroir + position) et marges.
 *
 * @param array<string, mixed> $appearance
 */
function em_wp_admin_header_shell_style(string $template, array $appearance): string
{
    $style = '';
    $bg = sanitize_hex_color((string) ($appearance['bg'] ?? ''));

    if (is_string($bg) && $bg !== '') {
        $style .= '--em-rubrique-bg:' . $bg . ';';
    }

    $pads = ['pt' => '--em-rubrique-pt', 'pb' => '--em-rubrique-pb', 'pl' => '--em-rubrique-pl', 'pr' => '--em-rubrique-pr'];
    foreach ($pads as $key => $var) {
        $style .= $var . ':' . max(0, (int) ($appearance[$key] ?? 0)) . 'px;';
    }

    // Image de fond : celle choisie explicitement au niveau HEADER prime ;
    // sinon repli sur l'image de fond de l'item HERO branché (héritage).
    $bg_image_id = (int) ($appearance['bg_image_id'] ?? 0);
    $url = $bg_image_id > 0 ? (string) wp_get_attachment_image_url($bg_image_id, 'full') : '';
    if ($url === '') {
        $url = em_wp_admin_header_hero_bg_image_url($template);
    }

    if ($url !== '') {
        $bp = em_wp_rubrique_bg_position_css((string) ($appearance['bg_image_pos'] ?? 'cover'));
        $op = max(0, min(100, (int) ($appearance['bg_image_opacity'] ?? 100)));
        $style .= "--em-rubrique-bg-image:url('" . str_replace("'", '%27', esc_url($url)) . "');";
        $style .= '--em-rubrique-bg-size:' . $bp['size'] . ';--em-rubrique-bg-repeat:' . $bp['repeat'] . ';--em-rubrique-bg-position:' . $bp['position'] . ';';
        $style .= '--em-rubrique-bg-opacity:' . round($op / 100, 2) . ';';
        $style .= '--em-rubrique-bg-transform:' . (!empty($appearance['bg_image_mirror']) ? 'scaleX(-1)' : 'none') . ';';
    }

    return $style;
}

/**
 * HTML composite du HEADER : un conteneur « shell » qui porte le FOND PARTAGÉ,
 * sur lequel HERO (et SLIDER) sont posés en colonnes, rendus SANS fond propre
 * (transparents) — reproduit le rendu du site (un seul fond, deux colonnes).
 */
function em_wp_admin_header_composite_html(string $template): string
{
    if (!function_exists('em_wp_rubrique_render')) {
        return '';
    }

    $cfg = em_wp_admin_header_section_get($template);
    $hero_type = em_wp_admin_header_part_type_slug('hero');
    $hero_item = em_wp_admin_header_effective_item($template, 'hero');
    $hero_html = ($hero_type !== '' && $hero_item !== '')
        ? em_wp_rubrique_render($hero_type, ['item' => $hero_item])
        : '';
    $hero_col = '<div class="em-header-shell__col em-header-shell__col--hero">' . $hero_html . '</div>';

    $slider_left = $cfg['position'] === 'slider_left';
    $cols = '1fr';
    $inner = $hero_col;
    $is_pair = false;

    if ($cfg['matrix'] === 'hero_slider') {
        $slider_type = em_wp_admin_header_part_type_slug('slider');
        $slider_item = em_wp_admin_header_effective_item($template, 'slider');
        $slider_html = ($slider_type !== '' && $slider_item !== '')
            ? em_wp_rubrique_render($slider_type, ['item' => $slider_item])
            : '';

        if ($slider_html !== '') {
            $is_pair = true;
            $cols = em_wp_admin_header_ratio_columns($cfg['ratio'], $slider_left);
            $slider_col = '<div class="em-header-shell__col em-header-shell__col--slider">' . $slider_html . '</div>';
            $inner = $slider_left ? ($slider_col . $hero_col) : ($hero_col . $slider_col);
        }
    }

    // Le SHELL porte le fond partagé pleine largeur ; la grille HERO/SLIDER est
    // dans un conteneur centré (comme .em-landing-hero-row__inner du front :
    // max 1100px, gap, padding vertical, colonnes alignées en haut).
    $shell_style = em_wp_admin_header_shell_style($template, $cfg['appearance']);
    $inner_style = 'grid-template-columns:' . $cols . ';';
    $inner_class = 'em-header-shell__inner' . ($slider_left ? ' is-slider-first' : '') . ($is_pair ? ' is-pair' : ' is-single');

    return '<div class="em-rubrique em-header-shell" style="' . esc_attr($shell_style) . '">'
        . '<div class="' . esc_attr($inner_class) . '" style="' . esc_attr($inner_style) . '">' . $inner . '</div>'
        . '</div>';
}

/**
 * Rendu de la liste d'items d'une partie (HERO ou SLIDER) + sources d'aperçu.
 */
function em_wp_admin_render_header_part_items(string $template, string $part, string $type): void
{
    $part_label = $part === 'slider' ? __('SLIDER', 'em-wp') : __('HERO', 'em-wp');
    ?>
    <div class="em-wp-header-picker__part" data-part="<?php echo esc_attr($part); ?>">
        <p class="em-wp-header-picker__subhead">
            <?php
            /* translators: %s: HERO ou SLIDER. */
            echo esc_html(sprintf(__('Items disponibles pour %s', 'em-wp'), $part_label));
            ?>
        </p>
        <?php
        $items = $type !== '' && function_exists('em_wp_v4_get_items') ? em_wp_v4_get_items($type) : [];

        if ($type === '' || $items === []) {
            ?>
            <p class="em-wp-rubriques-admin__picker-empty">
                <?php
                /* translators: %s: HERO ou SLIDER. */
                echo esc_html(sprintf(__('Crée d’abord une rubrique %s pour pouvoir la brancher.', 'em-wp'), $part_label));
                ?>
            </p>
            </div>
            <?php
            return;
        }

        $effective = em_wp_admin_header_effective_item($template, $part);

        if ($effective !== '' && isset($items[$effective])) {
            $items = [$effective => $items[$effective]] + $items;
        }
        ?>
        <ul
            class="em-wp-instance-picker em-wp-header-picker__items"
            data-part="<?php echo esc_attr($part); ?>"
            data-type="<?php echo esc_attr($type); ?>"
            data-current="<?php echo esc_attr($effective); ?>"
        >
            <?php foreach ($items as $slug => $item_label) :
                $slug = (string) $slug;
                $radio_id = 'em-wp-header-' . sanitize_html_class($part . '-' . $slug);
                ?>
                <li class="em-wp-instance-picker__row">
                    <label class="em-wp-instance-picker__label" for="<?php echo esc_attr($radio_id); ?>">
                        <input
                            type="radio"
                            id="<?php echo esc_attr($radio_id); ?>"
                            name="em-wp-header-<?php echo esc_attr($part); ?>"
                            value="<?php echo esc_attr($slug); ?>"
                            <?php checked($slug === $effective); ?>
                        >
                        <span class="em-wp-instance-picker__name"><?php echo esc_html($part_label . ' ' . $item_label); ?></span>
                        <?php if ($slug === $effective) : ?>
                            <span class="em-wp-instance-picker__badge"><?php esc_html_e('Utilisée', 'em-wp'); ?></span>
                        <?php endif; ?>
                    </label>
                    <span class="em-wp-instance-picker__actions">
                        <button type="button" class="em-wp-instance-picker__eye" data-part="<?php echo esc_attr($part); ?>" data-item="<?php echo esc_attr($slug); ?>" aria-pressed="false" title="<?php esc_attr_e('Aperçu de la section', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Aperçu de la section', 'em-wp'); ?>">
                            <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                        </button>
                        <a class="em-wp-instance-picker__edit" href="<?php echo esc_url(em_wp_admin_rubrique_v4_edit_url($type, $slug)); ?>" title="<?php esc_attr_e('Éditer dans RUBRIQUES', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Éditer dans RUBRIQUES', 'em-wp'); ?>">
                            <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                        </a>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="em-wp-instance-picker__previews em-wp-header-picker__previews" data-part="<?php echo esc_attr($part); ?>">
            <?php foreach ($items as $slug => $item_label) :
                $slug = (string) $slug;
                ?>
                <div class="em-wp-instance-picker__preview" data-part="<?php echo esc_attr($part); ?>" data-item="<?php echo esc_attr($slug); ?>" hidden>
                    <div class="em-wp-instance-picker__stage">
                        <?php echo em_wp_rubrique_render($type, ['item' => $slug]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Apparence partagée du HEADER (fond, opacité/miroir image héritée, marges) + ratio.
 *
 * @param array<string, mixed> $appearance
 */
function em_wp_admin_render_header_appearance(array $appearance, string $ratio): void
{
    $bg = (string) ($appearance['bg'] ?? '');
    $op = max(0, min(100, (int) ($appearance['bg_image_opacity'] ?? 100)));
    $pos = (string) ($appearance['bg_image_pos'] ?? 'cover');
    $bg_image_id = (int) ($appearance['bg_image_id'] ?? 0);
    $bg_thumb = $bg_image_id > 0 ? (string) wp_get_attachment_image_url($bg_image_id, 'medium') : '';
    ?>
    <div class="em-wp-header-picker__appearance">
        <p class="em-wp-header-picker__subhead"><?php esc_html_e('Apparence du HEADER (fond partagé)', 'em-wp'); ?></p>
        <div class="em-wp-header-appr">
            <label class="em-wp-header-appr__field">
                <span><?php esc_html_e('Fond', 'em-wp'); ?></span>
                <input type="color" class="em-wp-header-appr__bg" value="<?php echo esc_attr($bg !== '' ? $bg : '#100421'); ?>">
            </label>
            <span class="em-wp-header-appr__field em-wp-header-appr__field--image">
                <span><?php esc_html_e('Image de fond', 'em-wp'); ?></span>
                <span class="em-wp-header-appr__media" data-id="<?php echo esc_attr((string) $bg_image_id); ?>">
                    <img class="em-wp-header-appr__thumb" src="<?php echo esc_url($bg_thumb); ?>" alt=""<?php echo $bg_thumb === '' ? ' hidden' : ''; ?>>
                    <button type="button" class="button button-small em-wp-header-appr__pick"><?php esc_html_e('Choisir', 'em-wp'); ?></button>
                    <button type="button" class="em-wp-header-appr__clear" title="<?php esc_attr_e('Retirer l’image (revenir à celle du HERO)', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Retirer l’image', 'em-wp'); ?>"<?php echo $bg_image_id > 0 ? '' : ' hidden'; ?>>&times;</button>
                </span>
            </span>
            <label class="em-wp-header-appr__field">
                <span><?php esc_html_e('Position image', 'em-wp'); ?></span>
                <select class="em-wp-header-appr__pos">
                    <?php foreach (em_wp_rubrique_bg_position_choices() as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($pos, $key); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="em-wp-header-appr__field em-wp-header-appr__field--range">
                <span><?php esc_html_e('Opacité', 'em-wp'); ?></span>
                <input type="range" class="em-wp-header-appr__op" min="0" max="100" step="1" value="<?php echo esc_attr((string) $op); ?>" oninput="this.nextElementSibling.textContent=this.value+'%'">
                <output><?php echo esc_html($op . '%'); ?></output>
            </label>
            <label class="em-wp-header-appr__field em-wp-header-appr__field--check">
                <input type="checkbox" class="em-wp-header-appr__mirror" <?php checked(!empty($appearance['bg_image_mirror'])); ?>>
                <span><?php esc_html_e('Miroir', 'em-wp'); ?></span>
            </label>
            <label class="em-wp-header-appr__field">
                <span><?php esc_html_e('Ratio HERO/SLIDER', 'em-wp'); ?></span>
                <select class="em-wp-header-appr__ratio">
                    <?php foreach (em_wp_admin_header_ratio_choices() as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($ratio, $key); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <span class="em-wp-header-appr__pads">
                <span class="em-wp-header-appr__padlabel"><?php esc_html_e('Marges', 'em-wp'); ?></span>
                <input type="number" class="em-wp-header-appr__pt" min="0" value="<?php echo esc_attr((string) (int) ($appearance['pt'] ?? 0)); ?>" title="<?php esc_attr_e('Haut', 'em-wp'); ?>">
                <input type="number" class="em-wp-header-appr__pb" min="0" value="<?php echo esc_attr((string) (int) ($appearance['pb'] ?? 0)); ?>" title="<?php esc_attr_e('Bas', 'em-wp'); ?>">
                <input type="number" class="em-wp-header-appr__pl" min="0" value="<?php echo esc_attr((string) (int) ($appearance['pl'] ?? 0)); ?>" title="<?php esc_attr_e('Gauche', 'em-wp'); ?>">
                <input type="number" class="em-wp-header-appr__pr" min="0" value="<?php echo esc_attr((string) (int) ($appearance['pr'] ?? 0)); ?>" title="<?php esc_attr_e('Droite', 'em-wp'); ?>">
            </span>
        </div>
    </div>
    <?php
}

/**
 * Rendu du sélecteur HEADER : matrice (HERO / HERO+SLIDER), position, items.
 */
function em_wp_admin_render_header_section_picker(string $template): void
{
    $cfg = em_wp_admin_header_section_get($template);
    $hero_type = em_wp_admin_header_part_type_slug('hero');
    $slider_type = em_wp_admin_header_part_type_slug('slider');
    $is_live = $template !== ''
        && function_exists('em_wp_get_active_template_slug')
        && em_wp_get_active_template_slug() === $template;
    $template_label = function_exists('em_wp_get_editing_template_label')
        ? (string) em_wp_get_editing_template_label()
        : '';
    $both = $cfg['matrix'] === 'hero_slider';
    ?>
    <div
        class="em-wp-header-picker"
        data-template="<?php echo esc_attr($template); ?>"
        data-template-label="<?php echo esc_attr($template_label); ?>"
        data-live="<?php echo $is_live ? '1' : '0'; ?>"
        data-matrix="<?php echo esc_attr($cfg['matrix']); ?>"
        data-position="<?php echo esc_attr($cfg['position']); ?>"
        data-config="<?php echo esc_attr((string) wp_json_encode($cfg)); ?>"
    >
        <p class="em-wp-rubriques-admin__picker-head"><?php esc_html_e('Composition du HEADER', 'em-wp'); ?></p>

        <div class="em-wp-header-picker__matrix" role="radiogroup">
            <label class="em-wp-header-picker__opt">
                <input type="radio" name="em-wp-header-matrix" value="hero" <?php checked(!$both); ?>>
                <span><?php esc_html_e('HERO seul', 'em-wp'); ?></span>
            </label>
            <label class="em-wp-header-picker__opt">
                <input type="radio" name="em-wp-header-matrix" value="hero_slider" <?php checked($both); ?>>
                <span><?php esc_html_e('HERO + SLIDER', 'em-wp'); ?></span>
            </label>
        </div>

        <div class="em-wp-header-picker__position"<?php echo $both ? '' : ' hidden'; ?>>
            <span class="em-wp-header-picker__poslabel"><?php esc_html_e('Position', 'em-wp'); ?></span>
            <label class="em-wp-header-picker__opt">
                <input type="radio" name="em-wp-header-position" value="hero_left" <?php checked($cfg['position'] !== 'slider_left'); ?>>
                <span><?php esc_html_e('HERO à gauche', 'em-wp'); ?></span>
            </label>
            <label class="em-wp-header-picker__opt">
                <input type="radio" name="em-wp-header-position" value="slider_left" <?php checked($cfg['position'] === 'slider_left'); ?>>
                <span><?php esc_html_e('SLIDER à gauche', 'em-wp'); ?></span>
            </label>
        </div>

        <?php em_wp_admin_render_header_part_items($template, 'hero', $hero_type); ?>

        <div class="em-wp-header-picker__slider-wrap"<?php echo $both ? '' : ' hidden'; ?>>
            <?php em_wp_admin_render_header_part_items($template, 'slider', $slider_type); ?>
        </div>

        <?php em_wp_admin_render_header_appearance($cfg['appearance'], $cfg['ratio']); ?>

        <p class="em-wp-instance-picker__status" aria-live="polite" hidden></p>
    </div>
    <?php
}
