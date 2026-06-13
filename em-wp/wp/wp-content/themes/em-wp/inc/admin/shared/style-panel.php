<?php
/**
 * Composants admin partagés — panneau "Style de base".
 *
 * Pattern unique pour toutes les rubriques em-wp (OBLIGATOIRE pour chaque nouvelle rubrique) :
 * - em_wp_admin_render_base_style_panel() + preview bloc titre (data-em-admin-style)
 * - em_wp_admin_render_module_panel() / em_wp_admin_module_panel_classes() (fermés par défaut)
 * - em_wp_admin_module_style_data_attributes() + em_wp_admin_module_style_inline_vars()
 * - Champs empilés : em-wp-admin-panel-body--stack | ligne : em-wp-admin-panel-body--row
 * - button.em-wp-admin-module__panel-header
 * - div.em-wp-admin-module__panel-body (replié si pas .is-open)
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classes du bouton d'en-tête de panneau accordion.
 */
function em_wp_admin_panel_header_class(string $panel_class = ''): string
{
    $classes = ['em-wp-admin-module__panel-header'];

    if ($panel_class !== '') {
        $classes[] = sanitize_html_class($panel_class . '__header');
    }

    return implode(' ', $classes);
}

/**
 * Indique si les panneaux accordion admin sont ouverts au chargement.
 */
function em_wp_admin_module_panel_is_open_by_default(): bool
{
    return false;
}

/**
 * Classes d'un panneau accordion module (fermé par défaut).
 */
function em_wp_admin_module_panel_classes(string $panel_class = ''): string
{
    $classes = ['em-wp-admin-module__panel'];

    if ($panel_class !== '') {
        $classes[] = sanitize_html_class($panel_class);
    }

    if (em_wp_admin_module_panel_is_open_by_default()) {
        $classes[] = 'is-open';
    }

    return implode(' ', $classes);
}

/**
 * Valeur aria-expanded d'un panneau accordion module.
 */
function em_wp_admin_module_panel_aria_expanded(): string
{
    return em_wp_admin_module_panel_is_open_by_default() ? 'true' : 'false';
}

/**
 * Rendu d'un panneau accordion module (pattern mutualisé, fermé par défaut).
 *
 * @param callable $body_callback Contenu du panel-body.
 */
function em_wp_admin_render_module_panel(
    string $title,
    string $panel_class,
    callable $body_callback,
    string $body_classes = '',
    bool $open_by_default = false
): void {
    $body_class_attr = trim('em-wp-admin-module__panel-body ' . $body_classes);
    $panel_classes = em_wp_admin_module_panel_classes($panel_class);
    if ($open_by_default) {
        $panel_classes .= ' is-open';
    }
    ?>
    <section class="<?php echo esc_attr($panel_classes); ?>"<?php echo $open_by_default ? ' data-default-open' : ''; ?>>
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class($panel_class)); ?>" type="button" aria-expanded="<?php echo esc_attr($open_by_default ? 'true' : em_wp_admin_module_panel_aria_expanded()); ?>">
            <?php if ($title !== '') { ?>
                <span><?php echo esc_html($title); ?></span>
            <?php } ?>
        </button>
        <div class="<?php echo esc_attr($body_class_attr); ?>">
            <?php call_user_func($body_callback); ?>
        </div>
    </section>
    <?php
}

/**
 * Titre de section « Items de {rubrique} » sous Style de base.
 *
 * @param string      $module_slug  Slug rubrique (stream, cta, hero…).
 * @param string      $items_label  Libellé avant « de » (Items, Slides…).
 * @param string|null $module_label Libellé dans la pastille (défaut = label rubrique).
 */
function em_wp_admin_render_module_items_section_title(
    string $module_slug,
    string $items_label = '',
    ?string $module_label = null
): void {
    if ($items_label === '') {
        $items_label = __('Items', 'em-wp');
    }

    $definitions = function_exists('em_wp_admin_site_rubrique_definitions')
        ? em_wp_admin_site_rubrique_definitions()
        : [];

    if ($module_label === null) {
        $module_label = (string) ($definitions[$module_slug]['label'] ?? strtoupper($module_slug));
    }

    ?>
    <div class="em-wp-admin-module__section-title">
        <span><?php echo esc_html($items_label); ?></span>
        <span class="em-wp-admin-module__section-title-sep"><?php esc_html_e('de', 'em-wp'); ?></span>
        <span class="em-wp-admin-module__section-module-pill"><?php echo esc_html($module_label); ?></span>
    </div>
    <?php
}

/**
 * Rendu des champs couleur (sans wrapper panel-body).
 *
 * @param array<int, array{name:string,label:string,value:string,placeholder?:string}> $fields
 */
function em_wp_admin_render_color_fields_wrap(array $fields, string $option_name): void
{
    if ($fields === []) {
        return;
    }
    ?>
    <div class="em-wp-admin-color-field-wrap">
        <?php foreach ($fields as $field) {
            $name = sanitize_key((string) ($field['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $label = (string) ($field['label'] ?? '');
            $value = (string) ($field['value'] ?? '');
            $placeholder = (string) ($field['placeholder'] ?? '');
            ?>
            <div class="em-wp-admin-color-control">
                <span class="em-wp-admin-color-label"><?php echo esc_html($label); ?></span>
                <input
                    type="text"
                    class="regular-text em-wp-admin-color-field"
                    name="<?php echo esc_attr($option_name . '[' . $name . ']'); ?>"
                    value="<?php echo esc_attr($value); ?>"
                    <?php if ($placeholder !== '') { ?>placeholder="<?php echo esc_attr($placeholder); ?>"<?php } ?>
                >
            </div>
        <?php } ?>
    </div>
    <?php
}

/**
 * Rendu du panneau accordion "Style de base" avec champs couleur.
 *
 * @param array<int, array{name:string,label:string,value:string,placeholder?:string}> $color_fields
 * @param string $panel_class Classe module (ex. em-wp-hero-panel) pour ciblage CSS/JS optionnel.
 * @param callable|null $extra_body Contenu additionnel dans le même panel-body (ex. image de fond Top Bar).
 */
function em_wp_admin_render_base_style_panel(
    string $title,
    array $color_fields,
    string $option_name,
    string $panel_class = '',
    ?callable $extra_body = null
): void {
    $panel_classes = em_wp_admin_module_panel_classes($panel_class);
    ?>
    <section class="<?php echo esc_attr($panel_classes); ?>">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class($panel_class)); ?>" type="button" aria-expanded="<?php echo esc_attr(em_wp_admin_module_panel_aria_expanded()); ?>">
            <span><?php echo esc_html($title); ?></span>
        </button>
        <div class="em-wp-admin-module__panel-body">
            <?php em_wp_admin_render_color_fields_wrap($color_fields, $option_name); ?>
            <?php
            if (is_callable($extra_body)) {
                call_user_func($extra_body);
            }
            ?>
        </div>
    </section>
    <?php
}

/**
 * Attributs data pour le preview live Style de base → bloc titre.
 *
 * @param array{background?:string,text?:string} $defaults
 * @param array{bg?:string,text?:string}|null $field_keys
 */
function em_wp_admin_module_style_data_attributes(string $option_name, array $defaults = [], ?array $field_keys = null): string
{
    $bg_default = (string) ($defaults['background'] ?? '#100421');
    $text_default = (string) ($defaults['text'] ?? '#ffffff');
    $bg_key = (string) ($field_keys['bg'] ?? 'background_color');
    $text_key = (string) ($field_keys['text'] ?? 'text_color');
    $bg_field = $option_name !== '' ? $option_name . '[' . $bg_key . ']' : '[' . $bg_key . ']';
    $text_field = $option_name !== '' ? $option_name . '[' . $text_key . ']' : '[' . $text_key . ']';

    return sprintf(
        'data-em-admin-style data-em-admin-bg-field="%s" data-em-admin-text-field="%s" data-em-admin-bg-default="%s" data-em-admin-text-default="%s"',
        esc_attr($bg_field),
        esc_attr($text_field),
        esc_attr($bg_default),
        esc_attr($text_default)
    );
}

/**
 * Attributs preview Style de base mappés par slug rubrique (hero, slider, stream…).
 *
 * @param array<string, mixed> $options
 */
function em_wp_admin_module_style_data_attributes_for_module(string $module_slug, string $option_name, array $options = []): string
{
    $defaults = em_wp_admin_module_default_style_colors($module_slug);
    $field_map = em_wp_admin_module_style_color_fields($module_slug);

    return em_wp_admin_module_style_data_attributes($option_name, $defaults, $field_map);
}

/**
 * Variables CSS inline initiales pour le bloc titre admin.
 *
 * @param array<string, mixed> $options
 * @param array{background?:string,text?:string} $defaults
 * @param array{bg?:string,text?:string}|null $field_keys
 */
function em_wp_admin_module_style_inline_vars(array $options, array $defaults = [], ?array $field_keys = null): string
{
    $bg_key = (string) ($field_keys['bg'] ?? 'background_color');
    $text_key = (string) ($field_keys['text'] ?? 'text_color');
    $bg = trim((string) ($options[$bg_key] ?? ''));
    $text = trim((string) ($options[$text_key] ?? ''));

    if ($bg === '') {
        $bg = (string) ($defaults['background'] ?? '#100421');
    }
    if ($text === '') {
        $text = (string) ($defaults['text'] ?? '#ffffff');
    }

    return '--em-module-admin-bg:' . $bg . ';--em-module-admin-text:' . $text . ';';
}

/**
 * Variables CSS inline mappées par slug rubrique.
 *
 * @param array<string, mixed> $options
 */
function em_wp_admin_module_style_inline_vars_for_module(string $module_slug, array $options): string
{
    $defaults = em_wp_admin_module_default_style_colors($module_slug);
    $field_map = em_wp_admin_module_style_color_fields($module_slug);

    return em_wp_admin_module_style_inline_vars($options, $defaults, $field_map);
}

/**
 * Couleurs par défaut du Style de base pour une rubrique.
 *
 * @return array{background:string,text:string}
 */
function em_wp_admin_module_default_style_colors(string $module_slug): array
{
    $map = [
        'top-bar' => ['background' => '#13061f', 'text' => '#ffffff'],
        'hero'    => ['background' => '#13061f', 'text' => '#ffffff'],
        'slider'  => ['background' => '#12338f', 'text' => '#100421'],
        'stream'  => ['background' => '#6a1b78', 'text' => '#fff6ea'],
        'social'  => ['background' => '#db2777', 'text' => '#100421'],
        'video'   => ['background' => '#f5e06a', 'text' => '#100421'],
        'release' => ['background' => '#fff6ea', 'text' => '#100421'],
        'cta'     => ['background' => '#0d9488', 'text' => '#100421'],
        'footer'  => ['background' => '#100421', 'text' => '#fff6ea'],
    ];

    return $map[$module_slug] ?? ['background' => '#100421', 'text' => '#ffffff'];
}

/**
 * Mapping des champs Style de base par rubrique (plan / sommaire).
 *
 * @return array{bg:string,text:string}|null
 */
function em_wp_admin_module_style_color_fields(string $module_slug): ?array
{
    $maps = [
        'top-bar' => ['bg' => 'background_color', 'text' => 'text_color'],
        'hero'    => ['bg' => 'background_color', 'text' => 'text_color'],
        'stream'  => ['bg' => 'background_color', 'text' => 'text_color'],
        'slider'  => ['bg' => 'frame_bg_color', 'text' => 'footer_text'],
        'social'  => ['bg' => 'background_color', 'text' => 'text_color'],
        'video'   => ['bg' => 'background_color', 'text' => 'text_color'],
        'release' => ['bg' => 'background_color', 'text' => 'text_color'],
        'cta'     => ['bg' => 'background_color', 'text' => 'text_color'],
        'footer'  => ['bg' => 'background_color', 'text' => 'text_color'],
    ];

    return $maps[$module_slug] ?? null;
}

/**
 * Options enregistrées d'une rubrique (style actif pour hero/slider).
 *
 * @return array<string, mixed>
 */
function em_wp_admin_get_module_options_for_preview(string $module_slug): array
{
    switch ($module_slug) {
        case 'top-bar':
            return function_exists('em_wp_top_bar_get_options') ? em_wp_top_bar_get_options() : [];
        case 'stream':
            return function_exists('em_wp_stream_get_options') ? em_wp_stream_get_options() : [];
        case 'social':
            return function_exists('em_wp_social_get_options') ? em_wp_social_get_options() : [];
        case 'video':
            return function_exists('em_wp_video_get_options') ? em_wp_video_get_options() : [];
        case 'release':
            return function_exists('em_wp_release_get_options') ? em_wp_release_get_options() : [];
        case 'cta':
            return function_exists('em_wp_cta_get_options') ? em_wp_cta_get_options() : [];
        case 'footer':
            return function_exists('em_wp_footer_get_options') ? em_wp_footer_get_options() : [];
        case 'hero':
            if (function_exists('em_wp_hero_get_options') && function_exists('em_wp_hero_active_style_slug')) {
                return em_wp_hero_get_options(em_wp_hero_active_style_slug());
            }
            break;
        case 'slider':
            if (function_exists('em_wp_slider_get_options') && function_exists('em_wp_slider_active_style_slug')) {
                return em_wp_slider_get_options(em_wp_slider_active_style_slug());
            }
            break;
    }

    return [];
}

/**
 * Couleurs Style de base réelles pour le plan du site / sommaire.
 *
 * @return array{background:string,text:string}
 */
function em_wp_admin_module_style_colors_for_preview(string $module_slug): array
{
    $definitions = function_exists('em_wp_admin_site_rubrique_definitions')
        ? em_wp_admin_site_rubrique_definitions()
        : [];
    $fallback_accent = (string) ($definitions[$module_slug]['accent_color'] ?? '#100421');
    $defaults = em_wp_admin_module_default_style_colors($module_slug);
    $default_bg = (string) ($defaults['background'] ?? $fallback_accent);
    $default_text = (string) ($defaults['text'] ?? '#ffffff');
    $field_map = em_wp_admin_module_style_color_fields($module_slug);

    if ($field_map === null) {
        return [
            'background' => $fallback_accent,
            'text'       => $default_text,
        ];
    }

    $options = em_wp_admin_get_module_options_for_preview($module_slug);
    $bg = trim((string) ($options[$field_map['bg']] ?? ''));
    $text = trim((string) ($options[$field_map['text']] ?? ''));

    return [
        'background' => $bg !== '' ? (sanitize_hex_color($bg) ?: $default_bg) : $default_bg,
        'text'       => $text !== '' ? (sanitize_hex_color($text) ?: $default_text) : $default_text,
    ];
}
