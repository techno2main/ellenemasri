<?php
/**
 * Plan de la landing pour le sommaire admin (position des rubriques).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug module d'une zone du plan.
 */
function em_site_admin_landing_preview_zone_module_slug(string $zone): string
{
    foreach (em_site_admin_site_rubrique_all_definitions() as $module_slug => $definition) {
        if (($definition['preview_zone'] ?? '') === $zone) {
            return (string) $module_slug;
        }
    }

    if ($zone === 'header_hero' || $zone === 'header_slider') {
        return 'header';
    }

    return '';
}

/**
 * Zone preview d'un module.
 */
function em_site_admin_landing_preview_module_zone(string $module_slug): string
{
    $definitions = em_site_admin_site_rubrique_definitions();

    return (string) ($definitions[$module_slug]['preview_zone'] ?? '');
}

/**
 * Config HEADER pour le plan (template en édition).
 *
 * @return array{enabled:bool,hero_slug:string,slider_slug:string,layout:string}
 */
function em_site_admin_header_preview_config(): array
{
    if (!function_exists('em_site_header_get_options')) {
        return em_site_header_default_options();
    }

    return em_site_header_get_options();
}

/**
 * Sous-zones HEADER ordonnées pour le plan (hero / slider selon layout).
 *
 * @param array{enabled?:bool,hero_slug?:string,slider_slug?:string,layout?:string}|null $header
 * @return array<int, array{zone:string,part:string,slug:string,label:string}>
 */
function em_site_admin_header_preview_subzones(?array $header = null): array
{
    if ($header === null) {
        $header = em_site_admin_header_preview_config();
    }
    $hero_slug = sanitize_key((string) ($header['hero_slug'] ?? ($header['hero'] ?? '')));
    $slider_slug = sanitize_key((string) ($header['slider_slug'] ?? ($header['slider'] ?? '')));
    $layout = (string) ($header['layout'] ?? 'hero_left');
    $matrix = sanitize_key((string) ($header['matrix'] ?? ''));
    $hero_enabled = $matrix !== 'slider';
    $slider_enabled = $matrix !== 'hero';
    if ($matrix !== 'hero' && $matrix !== 'hero_slider' && $matrix !== 'slider') {
        // Compat legacy: si la matrice n'est pas fournie, on déduit via les slugs.
        $hero_enabled = $hero_slug !== '';
        $slider_enabled = $slider_slug !== '';
    }
    $parts = [];

    if ($hero_enabled && $hero_slug !== '') {
        $parts[] = ['zone' => 'header_hero', 'part' => 'hero', 'slug' => $hero_slug];
    }

    if ($slider_enabled && $slider_slug !== '') {
        $parts[] = ['zone' => 'header_slider', 'part' => 'slider', 'slug' => $slider_slug];
    }

    if ($layout === 'slider_left' && count($parts) === 2) {
        $parts = array_reverse($parts);
    }

    $labels = [
        'header_hero'   => __('Hero', 'em-site'),
        'header_slider' => __('Slider', 'em-site'),
    ];

    foreach ($parts as $index => $part) {
        $zone = (string) $part['zone'];
        $catalog_slug = (string) $part['slug'];
        $label = $labels[$zone] ?? $zone;

        if ($zone === 'header_hero' && function_exists('em_site_hero_catalog_entries')) {
            $entries = em_site_hero_catalog_entries();
            if (isset($entries[$catalog_slug]['label'])) {
                $label = (string) $entries[$catalog_slug]['label'];
            }
        }

        if ($zone === 'header_slider' && function_exists('em_site_slider_catalog_entries')) {
            $entries = em_site_slider_catalog_entries();
            if (isset($entries[$catalog_slug]['label'])) {
                $label = (string) $entries[$catalog_slug]['label'];
            }
        }

        $parts[$index]['label'] = $label;
    }

    return $parts;
}

/**
 * Libellé court d'une zone (infobulle).
 */
function em_site_admin_landing_preview_zone_label(string $zone): string
{
    $labels = [
        'top_bar'        => __('Barre du haut', 'em-site'),
        'header'         => __('Section Header', 'em-site'),
        'header_hero'    => __('Hero (catalogue)', 'em-site'),
        'header_slider'    => __('Slider (catalogue)', 'em-site'),
        'section_stream' => __('Section Stream', 'em-site'),
        'section_social' => __('Section Social', 'em-site'),
        'section_video'  => __('Section Videos', 'em-site'),
        'section_release'=> __('Section Releases', 'em-site'),
        'section_cta'    => __('Section CTA', 'em-site'),
        'section_footer' => __('Footer', 'em-site'),
    ];

    if (isset($labels[$zone])) {
        return $labels[$zone];
    }

    $module_slug = em_site_admin_landing_preview_zone_module_slug($zone);

    if ($module_slug !== '' && function_exists('em_site_admin_rubrique_skeleton_label')) {
        return em_site_admin_rubrique_skeleton_label($module_slug);
    }

    return $zone;
}

/**
 * Couleur d'accent d'une zone (Style de base enregistré, sinon défaut rubrique).
 */
function em_site_admin_landing_preview_zone_color(string $zone): string
{
    return em_site_admin_landing_preview_zone_style($zone)['background'];
}

/**
 * Couleurs catalogue hero/slider pour sous-zones HEADER.
 *
 * @return array{background:string,text:string}
 */
function em_site_admin_header_catalog_subzone_style(string $part, string $catalog_slug): array
{
    $catalog_slug = sanitize_key($catalog_slug);

    if ($part === 'hero' && function_exists('em_site_header_get_options')) {
        $options = em_site_header_get_options();
        $bg = trim((string) ($options['background_color'] ?? ''));
        $text = trim((string) ($options['text_color'] ?? ''));

        return [
            'background' => $bg !== '' ? (sanitize_hex_color($bg) ?: '#ff6f00') : '#ff6f00',
            'text'       => $text !== '' ? (sanitize_hex_color($text) ?: '#100421') : '#100421',
        ];
    }

    if ($part === 'slider' && $catalog_slug !== '' && function_exists('em_site_slider_get_options')) {
        $options = em_site_slider_get_options($catalog_slug);
        $bg = trim((string) ($options['frame_bg_color'] ?? ''));
        $text = trim((string) ($options['footer_text'] ?? ''));

        return [
            'background' => $bg !== '' ? (sanitize_hex_color($bg) ?: '#2563eb') : '#2563eb',
            'text'       => $text !== '' ? (sanitize_hex_color($text) ?: '#ffffff') : '#ffffff',
        ];
    }

    return [
        'background' => $part === 'hero' ? '#d94a2d' : '#2563eb',
        'text'       => '#ffffff',
    ];
}

/**
 * Couleurs d'une zone du plan (fond + texte depuis Style de base).
 *
 * @return array{background:string,text:string}
 */
function em_site_admin_landing_preview_zone_style(string $zone): array
{
    if ($zone === 'header_hero' || $zone === 'header_slider') {
        $header = em_site_admin_header_preview_config();
        $part = $zone === 'header_hero' ? 'hero' : 'slider';
        $slug = sanitize_key((string) ($header[$part . '_slug'] ?? ''));

        return em_site_admin_header_catalog_subzone_style($part, $slug);
    }

    $module_slug = em_site_admin_landing_preview_zone_module_slug($zone);

    if ($module_slug !== '' && function_exists('em_site_admin_module_style_colors_for_preview')) {
        return em_site_admin_module_style_colors_for_preview($module_slug);
    }

    return [
        'background' => em_site_admin_landing_preview_zone_fallback_color($zone),
        'text'       => '#ffffff',
    ];
}

/**
 * Couleur statique de repli (définition rubrique).
 */
function em_site_admin_landing_preview_zone_fallback_color(string $zone): string
{
    $definitions = em_site_admin_site_rubrique_definitions();

    foreach ($definitions as $definition) {
        if (($definition['preview_zone'] ?? '') === $zone) {
            return (string) ($definition['accent_color'] ?? '#100421');
        }
    }

    if ($zone === 'header_hero') {
        return '#d94a2d';
    }

    if ($zone === 'header_slider') {
        return '#2563eb';
    }

    return '#100421';
}

/**
 * Classe CSS is-active pour une zone du plan.
 */
function em_site_admin_landing_zone_active_class(string $zone, string $active_zone): string
{
    if ($zone === $active_zone) {
        return ' is-active';
    }

    if (($active_zone === 'header_hero' || $active_zone === 'header_slider') && $zone === 'header') {
        return ' is-context';
    }

    return '';
}

/**
 * URL admin d'une entrée catalogue hero/slider.
 */
function em_site_admin_catalog_entry_url(string $type, string $catalog_slug): string
{
    if (function_exists('em_site_admin_catalog_entry_edit_url')) {
        return em_site_admin_catalog_entry_edit_url($type, $catalog_slug);
    }

    return '';
}

/**
 * URL admin de la rubrique liée à une zone du plan.
 */
function em_site_admin_landing_preview_zone_url(string $zone): string
{
    if ($zone === 'header_hero' || $zone === 'header_slider') {
        $header = em_site_admin_header_preview_config();
        $part = $zone === 'header_hero' ? 'hero' : 'slider';
        $slug = sanitize_key((string) ($header[$part . '_slug'] ?? ''));

        return em_site_admin_catalog_entry_url($part, $slug);
    }

    $module_slug = em_site_admin_landing_preview_zone_module_slug($zone);

    // Rester sur le squelette : ouvrir la gestion EM-SITE de la rubrique en dessous.
    if ($module_slug !== '' && function_exists('em_site_admin_rubrique_open_url')) {
        return em_site_admin_rubrique_open_url($module_slug);
    }

    if ($module_slug !== '' && function_exists('em_site_admin_site_rubrique_entry_url')) {
        return em_site_admin_site_rubrique_entry_url($module_slug);
    }

    return '';
}

/**
 * Titre rubrique affiché sur le plan.
 */
function em_site_admin_landing_preview_zone_title(string $zone): string
{
    if ($zone === 'header_hero' || $zone === 'header_slider') {
        foreach (em_site_admin_header_preview_subzones() as $subzone) {
            if (($subzone['zone'] ?? '') === $zone) {
                return (string) ($subzone['label'] ?? em_site_admin_landing_preview_zone_label($zone));
            }
        }
    }

    $definitions = em_site_admin_site_rubrique_definitions();

    foreach ($definitions as $module_slug => $definition) {
        if (($definition['preview_zone'] ?? '') === $zone) {
            if (function_exists('em_site_admin_rubrique_skeleton_label_with_item')) {
                return em_site_admin_rubrique_skeleton_label_with_item((string) $module_slug);
            }

            if (function_exists('em_site_admin_rubrique_skeleton_label')) {
                return em_site_admin_rubrique_skeleton_label((string) $module_slug);
            }

            $title = (string) ($definition['label'] ?? '');
            if ($title !== '') {
                return $title;
            }
            break;
        }
    }

    return em_site_admin_landing_preview_zone_label($zone);
}

/**
 * Zone preview pour un module.
 */
function em_site_admin_landing_preview_zone_for_module(string $module_slug): string
{
    return em_site_admin_landing_preview_module_zone($module_slug);
}

/**
 * Affiche une zone cliquable du plan landing.
 */
function em_site_admin_render_landing_map_zone(
    string $zone,
    string $active_zone,
    string $class_suffix,
    bool $is_hidden = false,
    bool $is_sortable = false,
    string $module_slug = '',
    string $header_part = '',
    string $inner_html = '',
    string $zone_tooltip = '',
    bool $is_disabled = false,
    bool $disabled_visual = true,
    bool $omit_preview_zone = false,
    ?array $style_override = null
): void {
    $url = $is_disabled ? '' : em_site_admin_landing_preview_zone_url($zone);
    $label = em_site_admin_landing_preview_zone_label($zone);
    $title = em_site_admin_landing_preview_zone_title($zone);
    $tooltip = $zone_tooltip !== '' ? $zone_tooltip : ($inner_html !== '' ? $title : $label);
    $style = is_array($style_override)
        ? wp_parse_args($style_override, ['background' => '#100421', 'text' => '#ffffff'])
        : em_site_admin_landing_preview_zone_style($zone);
    $is_inert = $is_disabled && !$disabled_visual;

    if ($module_slug === '') {
        $module_slug = em_site_admin_landing_preview_zone_module_slug($zone);
    }

    if (!$is_sortable && $module_slug !== '' && $module_slug !== 'header' && em_site_site_rubrique_is_reorderable($module_slug)) {
        $is_sortable = true;
    }

    $classes = 'em-site-admin-landing-map__zone em-site-admin-landing-map__' . $class_suffix
        . em_site_admin_landing_zone_active_class($zone, $active_zone)
        . ($is_sortable ? ' is-sortable' : '')
        . ($is_hidden ? ' is-rubrique-hidden' : '')
        . ($inner_html !== '' ? ' has-structure' : '')
        . ($is_disabled && $disabled_visual ? ' is-disabled' : '')
        . ($is_inert ? ' is-inert' : '');

    $sort_handle = $is_sortable
        ? '<span class="em-site-rubriques-sortable__handle" aria-hidden="true"><i class="fa-solid fa-grip-vertical"></i></span>'
        : '';

    $hidden_badge = $is_hidden
        ? '<span class="em-site-admin-landing-map__hidden-badge">' . esc_html__('Masqué', 'em-site') . '</span>'
        : '';

    if ($inner_html !== '') {
        $inner = $sort_handle . $hidden_badge . $inner_html;
    } else {
        $inner = $sort_handle . $hidden_badge . '<span class="em-site-admin-landing-map__zone-label">' . esc_html($title) . '</span>';
    }

    $data_attrs = '';

    if (!$omit_preview_zone) {
        $data_attrs = 'data-preview-zone="' . esc_attr($zone) . '"';
    }

    if ($module_slug !== '') {
        $data_attrs .= ' data-module-slug="' . esc_attr($module_slug) . '"';
    }

    if ($header_part !== '') {
        $data_attrs .= ' data-header-part="' . esc_attr($header_part) . '"';
    }

    if ($url === '') {
        ?>
        <span
            class="<?php echo esc_attr($classes); ?>"
            <?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            style="--em-zone-accent: <?php echo esc_attr($style['background']); ?>; --em-zone-text: <?php echo esc_attr($style['text']); ?>"
            title="<?php echo esc_attr($tooltip); ?>"
        ><?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        <?php
        return;
    }
    ?>
    <a
        class="<?php echo esc_attr($classes); ?>"
        href="<?php echo esc_url($url); ?>"
        <?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        style="--em-zone-accent: <?php echo esc_attr($style['background']); ?>; --em-zone-text: <?php echo esc_attr($style['text']); ?>"
        title="<?php echo esc_attr($tooltip); ?>"
        aria-label="<?php echo esc_attr($title); ?>"
    ><?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
    <?php
}

/**
 * Encadrant HEADER sur le plan (hero / slider selon config template).
 *
 * @param array{interactive?:bool,show_hidden_badge?:bool,header_config?:array<string,mixed>,editing_part?:string,subzones_clickable?:bool,subzone_display?:string,disable_header_link?:bool,editing_entry_label?:string} $args
 */
function em_site_admin_render_landing_map_header_group(string $active_zone = '', array $args = []): void
{
    $interactive = !array_key_exists('interactive', $args) || !empty($args['interactive']);
    $show_hidden_badge = !array_key_exists('show_hidden_badge', $args) || !empty($args['show_hidden_badge']);
    $editing_part = sanitize_key((string) ($args['editing_part'] ?? ''));
    $editing_entry_label = trim((string) ($args['editing_entry_label'] ?? ''));
    $subzone_display = sanitize_key((string) ($args['subzone_display'] ?? 'structure'));
    $layout_only = $subzone_display === 'placeholders';
    $subzones_clickable = array_key_exists('subzones_clickable', $args)
        ? !empty($args['subzones_clickable'])
        : ($editing_part !== '');
    $disable_header_link = !empty($args['disable_header_link']);
    $header_url = function_exists('em_site_admin_rubrique_open_url')
        ? em_site_admin_rubrique_open_url('header')
        : (function_exists('em_site_admin_site_rubrique_entry_url') ? em_site_admin_site_rubrique_entry_url('header') : '');
    $header = isset($args['header_config']) && is_array($args['header_config'])
        ? wp_parse_args($args['header_config'], em_site_header_default_options())
        : em_site_admin_header_preview_config();
    $header_entry_label = trim((string) ($args['header_entry_label'] ?? ''));
    $subzones = em_site_admin_header_preview_subzones($header);
    // Matrice EM-SITE (composite HERO/SLIDER) : si fournie, les deux sous-zones sont
    // toujours rendues mais SLIDER est masqué en mode « HERO seul » (permet la
    // bascule en direct depuis le sélecteur sans recharger).
    $is_hidden = array_key_exists('is_hidden', $args)
        ? !empty($args['is_hidden'])
        : !em_site_get_site_rubrique_visibility('header');
    $layout = (string) ($header['layout'] ?? 'hero_left');
    $can_swap = $interactive && count($subzones) === 2 && empty($args['disable_swap']);
    $header_style = is_array($args['zone_style'] ?? null)
        ? wp_parse_args($args['zone_style'], ['background' => '#100421', 'text' => '#ffffff'])
        : em_site_admin_landing_preview_zone_style('header');
    $group_classes = 'em-site-admin-landing-map__header-group'
        . ($interactive ? ' is-sortable' : ' is-static')
        . ($layout_only ? ' is-layout-mode' : '')
        . em_site_admin_landing_zone_active_class('header', $active_zone)
        . ($show_hidden_badge && $is_hidden ? ' is-rubrique-hidden' : '');
    $group_attrs = 'data-module-slug="header"'
        . ' data-header-layout="' . esc_attr($layout) . '"'
        . ' data-header-can-swap="' . ($can_swap ? '1' : '0') . '"';

    if ($layout_only) {
        $group_attrs .= ' data-preview-zone="header"'
            . ' style="--em-zone-accent: ' . esc_attr($header_style['background']) . '; --em-zone-text: ' . esc_attr($header_style['text']) . '"';
    }
    ?>
    <div
        class="<?php echo esc_attr($group_classes); ?>"
        <?php echo $group_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    >
        <div class="em-site-admin-landing-map__header-group-toolbar">
            <?php if ($interactive) { ?>
                <span class="em-site-rubriques-sortable__handle" aria-hidden="true"><i class="fa-solid fa-grip-vertical"></i></span>
            <?php } ?>
            <span class="em-site-admin-landing-map__header-group-title"><?php esc_html_e('HEADER', 'em-site'); ?></span>
            <?php if ($can_swap) { ?>
                <button
                    type="button"
                    class="em-site-admin-landing-map__swap-header"
                    aria-label="<?php esc_attr_e('Inverser Hero et Slider dans HEADER', 'em-site'); ?>"
                >
                    <i class="fa-solid fa-right-left" aria-hidden="true"></i>
                </button>
            <?php } ?>
            <?php if ($show_hidden_badge && $is_hidden) { ?>
                <span class="em-site-admin-landing-map__hidden-badge"><?php esc_html_e('Masqué', 'em-site'); ?></span>
            <?php } ?>
        </div>
        <?php if (!$subzones_clickable && !$disable_header_link && $header_url !== '' && $subzones !== []) { ?>
            <a
                class="em-site-admin-landing-map__header-group-link<?php echo $layout_only ? ' is-layout-only' : ''; ?>"
                href="<?php echo esc_url($header_url); ?>"
                <?php if (!$layout_only) { ?>
                data-preview-zone="header"
                style="--em-zone-accent: <?php echo esc_attr($header_style['background']); ?>; --em-zone-text: <?php echo esc_attr($header_style['text']); ?>"
                <?php } ?>
                data-module-slug="header"
                title="<?php echo esc_attr(em_site_admin_landing_preview_zone_label('header')); ?>"
            >
        <?php } ?>
        <div class="em-site-admin-landing-map__header-group-inner<?php echo ($layout_only || count($subzones) === 1) ? ' is-single' : ''; ?>">
            <?php
            if ($layout_only) {
                $placeholder = $header_entry_label !== ''
                    ? sprintf(
                        /* translators: %s: label d'item HEADER */
                        __('HEADER %s', 'em-site'),
                        $header_entry_label
                    )
                    : __('HEADER', 'em-site');
                ?>
                <span class="em-site-admin-landing-map__zone em-site-admin-landing-map__header-empty" style="grid-column:1 / -1; --em-zone-accent:#c7ccd4; --em-zone-text:#374151; background:#d1d5db; color:#374151;" aria-hidden="true">
                    <span class="em-site-admin-landing-map__zone-label"><?php echo esc_html($placeholder); ?></span>
                </span>
                <?php
            } elseif ($subzones === []) {
                ?>
                <a
                    class="em-site-admin-landing-map__zone em-site-admin-landing-map__header-empty"
                    href="<?php echo esc_url(function_exists('em_site_admin_rubrique_open_url') ? em_site_admin_rubrique_open_url('header') : em_site_admin_site_rubrique_entry_url('header')); ?>"
                    data-preview-zone="header"
                    data-module-slug="header"
                    style="--em-zone-accent:#c7ccd4; --em-zone-text:#374151; background:#d1d5db; color:#374151;"
                    title="<?php esc_attr_e('Configurer HEADER', 'em-site'); ?>"
                >
                    <span class="em-site-admin-landing-map__zone-label"><?php esc_html_e('HEADER (non configuré)', 'em-site'); ?></span>
                </a>
                <?php
            } else {
                foreach ($subzones as $subzone) {
                    $zone = (string) ($subzone['zone'] ?? '');
                    $part = (string) ($subzone['part'] ?? '');
                    $catalog_slug = sanitize_key((string) ($subzone['slug'] ?? ''));
                    $entry_label = (string) ($subzone['label'] ?? '');
                    $class_suffix = $part === 'hero' ? 'header-hero' : 'header-slider';

                    $inner_html = '';

                    if ($part === 'hero' && function_exists('em_site_admin_landing_hero_structure_html')) {
                        $inner_html = em_site_admin_landing_hero_structure_html($catalog_slug, $entry_label);
                    } elseif ($part === 'slider' && function_exists('em_site_admin_landing_slider_structure_html')) {
                        $inner_html = em_site_admin_landing_slider_structure_html($catalog_slug, $entry_label);
                    }

                    $is_disabled = $editing_part !== '' && $part !== '' && $part !== $editing_part;
                    $is_inert = !$subzones_clickable;
                    $no_link = $is_inert || $is_disabled;
                    $zone_tooltip = $entry_label !== '' ? $entry_label : em_site_admin_landing_preview_zone_title($zone);

                    em_site_admin_render_landing_map_zone(
                        $zone,
                        $active_zone,
                        $class_suffix,
                        false,
                        false,
                        'header',
                        $part,
                        $inner_html,
                        $zone_tooltip,
                        $no_link,
                        $is_disabled,
                        $is_inert
                    );
                }
            }
            ?>
        </div>
        <?php if (!$subzones_clickable && !$disable_header_link && $header_url !== '' && $subzones !== []) { ?>
            </a>
        <?php } ?>
    </div>
    <?php
}

/**
 * Aperçu HEADER seul (wireframe hero / slider), sans le reste du plan.
 *
 * @param array{interactive?:bool,show_hidden_badge?:bool,header_config?:array<string,mixed>,editing_part?:string,editing_entry_label?:string} $args
 */
function em_site_admin_render_header_structure_preview(string $active_zone = '', array $args = []): void
{
    $preview_args = array_merge(
        [
            'interactive'        => false,
            'show_hidden_badge'  => false,
            'subzones_clickable' => false,
            'disable_header_link' => true,
            'subzone_display'    => 'placeholders',
        ],
        $args
    );
    $editing_part = sanitize_key((string) ($preview_args['editing_part'] ?? ''));
    $map_classes = 'em-site-admin-landing-map em-site-admin-landing-map--header-only';

    if ($editing_part !== '') {
        $map_classes .= ' em-site-admin-landing-map--edit-' . $editing_part;
    }
    ?>
    <div
        class="<?php echo esc_attr($map_classes); ?>"
        aria-label="<?php esc_attr_e('Aperçu HEADER', 'em-site'); ?>"
    >
        <?php em_site_admin_render_landing_map_header_group($active_zone, $preview_args); ?>
    </div>
    <?php
}

/**
 * Affiche le plan complet de la landing.
 */
function em_site_admin_render_landing_map(string $active_zone = ''): void
{
    $top_hidden = !em_site_get_site_rubrique_visibility('top-bar');
    $footer_hidden = !em_site_get_site_rubrique_visibility('footer');
    ?>
    <div
        class="em-site-admin-landing-map"
        id="em-site-admin-landing-map"
        aria-label="<?php esc_attr_e('Squelette du template', 'em-site'); ?>"
    >
        <?php em_site_admin_render_landing_map_zone('top_bar', $active_zone, 'top-bar', $top_hidden, false); ?>

        <div class="em-site-admin-landing-map__body" id="em-site-admin-landing-map-body">
            <?php
            $middle_order_fn = function_exists('em_site_get_rubrique_middle_order_for_template')
                ? 'em_site_get_rubrique_middle_order_for_template'
                : 'em_site_get_site_rubrique_middle_order';

            foreach ($middle_order_fn() as $module_slug) {
                $module_slug = sanitize_key((string) $module_slug);

                if ($module_slug === 'header') {
                    $header_active = in_array($active_zone, ['header', 'header_hero', 'header_slider'], true);
                    $header_args = [
                        'interactive'        => true,
                        // Le switch gauche/droite n'est plus affiché dans le wireframe
                        // de la page Rubriques (reliquat historique).
                        'disable_swap'       => true,
                        'subzones_clickable' => false,
                        // Wireframe global: placeholder par défaut. La vraie structure
                        // n'est affichée que lorsque la zone HEADER est active.
                        'subzone_display'    => $header_active ? 'structure' : 'placeholders',
                    ];

                    // Reflète la composition HEADER EM-SITE (matrice + position) si dispo.
                    if ($header_active && function_exists('em_site_admin_header_section_get') && function_exists('em_site_get_editing_template_slug')) {
                        $tpl = (string) em_site_get_editing_template_slug();

                        if ($tpl !== '') {
                            $cfg = em_site_admin_header_section_get($tpl);
                            $header_items = function_exists('em_site_get_items') ? em_site_get_items('headers') : [];
                            $preview_header_item = sanitize_key((string) ($cfg['header_item'] ?? ''));
                            if (($cfg['display_mode'] ?? 'single') === 'multi') {
                                $candidate_first = sanitize_key((string) ($cfg['first_item'] ?? ''));
                                if ($candidate_first !== '' && isset($header_items[$candidate_first])) {
                                    $preview_header_item = $candidate_first;
                                }
                            }
                            if ($preview_header_item !== '' && isset($header_items[$preview_header_item])) {
                                $header_args['header_entry_label'] = (string) $header_items[$preview_header_item];
                                if (function_exists('em_site_admin_header_item_config_get')) {
                                    $header_item_cfg = em_site_admin_header_item_config_get($preview_header_item);
                                    // On alimente le wireframe directement avec la composition
                                    // de l'item HEADER sélectionné, sans dépendre du contexte.
                                    $header_args['header_config'] = [
                                        'enabled'     => true,
                                        'matrix'      => (string) ($header_item_cfg['matrix'] ?? 'hero'),
                                        'hero_slug'   => (string) ($header_item_cfg['hero'] ?? ''),
                                        'slider_slug' => (string) ($header_item_cfg['slider'] ?? ''),
                                        'layout'      => (string) ($header_item_cfg['position'] ?? 'hero_left'),
                                    ];
                                }
                            }
                        }
                    }

                    em_site_admin_render_landing_map_header_group($active_zone, $header_args);
                    continue;
                }

                $zone = em_site_admin_landing_preview_zone_for_module($module_slug);
                if ($zone === '') {
                    continue;
                }

                $is_hidden = !em_site_get_site_rubrique_visibility($module_slug);

                em_site_admin_render_landing_map_zone($zone, $active_zone, 'section', $is_hidden, true, $module_slug);
            } ?>
        </div>

        <?php em_site_admin_render_landing_map_zone('section_footer', $active_zone, 'section section-footer', $footer_hidden, false); ?>
    </div>
    <?php
}
