<?php
/**
 * Plan de la landing pour le sommaire admin (position des rubriques).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug module d'une zone du plan.
 */
function em_wp_admin_landing_preview_zone_module_slug(string $zone): string
{
    foreach (em_wp_admin_site_rubrique_definitions() as $module_slug => $definition) {
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
function em_wp_admin_landing_preview_module_zone(string $module_slug): string
{
    $definitions = em_wp_admin_site_rubrique_definitions();

    return (string) ($definitions[$module_slug]['preview_zone'] ?? '');
}

/**
 * Config HEADER pour le plan (template en édition).
 *
 * @return array{enabled:bool,hero_slug:string,slider_slug:string,layout:string}
 */
function em_wp_admin_header_preview_config(): array
{
    if (!function_exists('em_wp_header_get_options')) {
        return em_wp_header_default_options();
    }

    return em_wp_header_get_options();
}

/**
 * Sous-zones HEADER ordonnées pour le plan (hero / slider selon layout).
 *
 * @return array<int, array{zone:string,part:string,slug:string,label:string}>
 */
function em_wp_admin_header_preview_subzones(): array
{
    $header = em_wp_admin_header_preview_config();
    $hero_slug = sanitize_key((string) ($header['hero_slug'] ?? ''));
    $slider_slug = sanitize_key((string) ($header['slider_slug'] ?? ''));
    $layout = (string) ($header['layout'] ?? 'hero_left');
    $parts = [];

    if ($hero_slug !== '') {
        $parts[] = ['zone' => 'header_hero', 'part' => 'hero', 'slug' => $hero_slug];
    }

    if ($slider_slug !== '') {
        $parts[] = ['zone' => 'header_slider', 'part' => 'slider', 'slug' => $slider_slug];
    }

    if ($layout === 'slider_left' && count($parts) === 2) {
        $parts = array_reverse($parts);
    }

    $labels = [
        'header_hero'   => __('Hero', 'em-wp'),
        'header_slider' => __('Slider', 'em-wp'),
    ];

    foreach ($parts as $index => $part) {
        $zone = (string) $part['zone'];
        $catalog_slug = (string) $part['slug'];
        $label = $labels[$zone] ?? $zone;

        if ($zone === 'header_hero' && function_exists('em_wp_hero_catalog_entries')) {
            $entries = em_wp_hero_catalog_entries();
            if (isset($entries[$catalog_slug]['label'])) {
                $label = (string) $entries[$catalog_slug]['label'];
            }
        }

        if ($zone === 'header_slider' && function_exists('em_wp_slider_catalog_entries')) {
            $entries = em_wp_slider_catalog_entries();
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
function em_wp_admin_landing_preview_zone_label(string $zone): string
{
    $labels = [
        'top_bar'        => __('Barre du haut', 'em-wp'),
        'header'         => __('HEADER', 'em-wp'),
        'header_hero'    => __('Hero (catalogue)', 'em-wp'),
        'header_slider'  => __('Slider (catalogue)', 'em-wp'),
        'section_stream' => __('Section Stream', 'em-wp'),
        'section_social' => __('Section Social', 'em-wp'),
        'section_video'  => __('Section Videos', 'em-wp'),
        'section_release'=> __('Section Releases', 'em-wp'),
        'section_cta'    => __('Section CTA', 'em-wp'),
        'section_footer' => __('Footer', 'em-wp'),
    ];

    return $labels[$zone] ?? $zone;
}

/**
 * Couleur d'accent d'une zone (Style de base enregistré, sinon défaut rubrique).
 */
function em_wp_admin_landing_preview_zone_color(string $zone): string
{
    return em_wp_admin_landing_preview_zone_style($zone)['background'];
}

/**
 * Couleurs catalogue hero/slider pour sous-zones HEADER.
 *
 * @return array{background:string,text:string}
 */
function em_wp_admin_header_catalog_subzone_style(string $part, string $catalog_slug): array
{
    $catalog_slug = sanitize_key($catalog_slug);

    if ($part === 'hero' && $catalog_slug !== '' && function_exists('em_wp_hero_get_options')) {
        $options = em_wp_hero_get_options($catalog_slug);
        $bg = trim((string) ($options['style_bg_color'] ?? ''));
        $text = trim((string) ($options['style_text_color'] ?? ''));

        return [
            'background' => $bg !== '' ? (sanitize_hex_color($bg) ?: '#d94a2d') : '#d94a2d',
            'text'       => $text !== '' ? (sanitize_hex_color($text) ?: '#ffffff') : '#ffffff',
        ];
    }

    if ($part === 'slider' && $catalog_slug !== '' && function_exists('em_wp_slider_get_options')) {
        $options = em_wp_slider_get_options($catalog_slug);
        $bg = trim((string) ($options['style_bg_color'] ?? ''));
        $text = trim((string) ($options['style_text_color'] ?? ''));

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
function em_wp_admin_landing_preview_zone_style(string $zone): array
{
    if ($zone === 'header_hero' || $zone === 'header_slider') {
        $header = em_wp_admin_header_preview_config();
        $part = $zone === 'header_hero' ? 'hero' : 'slider';
        $slug = sanitize_key((string) ($header[$part . '_slug'] ?? ''));

        return em_wp_admin_header_catalog_subzone_style($part, $slug);
    }

    $module_slug = em_wp_admin_landing_preview_zone_module_slug($zone);

    if ($module_slug !== '' && function_exists('em_wp_admin_module_style_colors_for_preview')) {
        return em_wp_admin_module_style_colors_for_preview($module_slug);
    }

    return [
        'background' => em_wp_admin_landing_preview_zone_fallback_color($zone),
        'text'       => '#ffffff',
    ];
}

/**
 * Couleur statique de repli (définition rubrique).
 */
function em_wp_admin_landing_preview_zone_fallback_color(string $zone): string
{
    $definitions = em_wp_admin_site_rubrique_definitions();

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
function em_wp_admin_landing_zone_active_class(string $zone, string $active_zone): string
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
function em_wp_admin_catalog_entry_url(string $type, string $catalog_slug): string
{
    $catalog_slug = sanitize_key($catalog_slug);

    if ($catalog_slug === '') {
        return '';
    }

    $page_slug = '';

    if ($type === 'hero' && function_exists('em_wp_hero_catalog_edit_page_slug')) {
        $page_slug = em_wp_hero_catalog_edit_page_slug($catalog_slug);
    } elseif ($type === 'slider' && function_exists('em_wp_slider_catalog_edit_page_slug')) {
        $page_slug = em_wp_slider_catalog_edit_page_slug($catalog_slug);
    }

    if ($page_slug === '') {
        return '';
    }

    return add_query_arg(['page' => $page_slug], admin_url('admin.php'));
}

/**
 * URL admin de la rubrique liée à une zone du plan.
 */
function em_wp_admin_landing_preview_zone_url(string $zone): string
{
    if ($zone === 'header_hero' || $zone === 'header_slider') {
        $header = em_wp_admin_header_preview_config();
        $part = $zone === 'header_hero' ? 'hero' : 'slider';
        $slug = sanitize_key((string) ($header[$part . '_slug'] ?? ''));

        return em_wp_admin_catalog_entry_url($part, $slug);
    }

    $module_slug = em_wp_admin_landing_preview_zone_module_slug($zone);

    if ($module_slug !== '' && function_exists('em_wp_admin_site_rubrique_entry_url')) {
        return em_wp_admin_site_rubrique_entry_url($module_slug);
    }

    $definitions = em_wp_admin_site_rubrique_definitions();

    foreach ($definitions as $definition) {
        if (($definition['preview_zone'] ?? '') !== $zone) {
            continue;
        }

        $page_slug = (string) ($definition['page_slug'] ?? '');
        if ($page_slug === '') {
            break;
        }

        return add_query_arg(['page' => $page_slug], admin_url('admin.php'));
    }

    return '';
}

/**
 * Titre rubrique affiché sur le plan.
 */
function em_wp_admin_landing_preview_zone_title(string $zone): string
{
    if ($zone === 'header_hero' || $zone === 'header_slider') {
        foreach (em_wp_admin_header_preview_subzones() as $subzone) {
            if (($subzone['zone'] ?? '') === $zone) {
                return (string) ($subzone['label'] ?? em_wp_admin_landing_preview_zone_label($zone));
            }
        }
    }

    $definitions = em_wp_admin_site_rubrique_definitions();

    foreach ($definitions as $definition) {
        if (($definition['preview_zone'] ?? '') === $zone) {
            $title = (string) ($definition['label'] ?? '');
            if ($title !== '') {
                return $title;
            }
            break;
        }
    }

    return em_wp_admin_landing_preview_zone_label($zone);
}

/**
 * Zone preview pour un module.
 */
function em_wp_admin_landing_preview_zone_for_module(string $module_slug): string
{
    return em_wp_admin_landing_preview_module_zone($module_slug);
}

/**
 * Affiche une zone cliquable du plan landing.
 */
function em_wp_admin_render_landing_map_zone(
    string $zone,
    string $active_zone,
    string $class_suffix,
    bool $is_hidden = false,
    bool $is_sortable = false,
    string $module_slug = '',
    string $header_part = ''
): void {
    $url = em_wp_admin_landing_preview_zone_url($zone);
    $label = em_wp_admin_landing_preview_zone_label($zone);
    $title = em_wp_admin_landing_preview_zone_title($zone);
    $style = em_wp_admin_landing_preview_zone_style($zone);

    if ($module_slug === '') {
        $module_slug = em_wp_admin_landing_preview_zone_module_slug($zone);
    }

    if (!$is_sortable && $module_slug !== '' && $module_slug !== 'header' && em_wp_site_rubrique_is_reorderable($module_slug)) {
        $is_sortable = true;
    }

    $classes = 'em-wp-admin-landing-map__zone em-wp-admin-landing-map__' . $class_suffix
        . em_wp_admin_landing_zone_active_class($zone, $active_zone)
        . ($is_sortable ? ' is-sortable' : '')
        . ($is_hidden ? ' is-rubrique-hidden' : '');

    $sort_handle = $is_sortable
        ? '<span class="em-wp-rubriques-sortable__handle" aria-hidden="true"><i class="fa-solid fa-grip-vertical"></i></span>'
        : '';

    $hidden_badge = $is_hidden
        ? '<span class="em-wp-admin-landing-map__hidden-badge">' . esc_html__('Masqué', 'em-wp') . '</span>'
        : '';

    $inner = $sort_handle . $hidden_badge . '<span class="em-wp-admin-landing-map__zone-label">' . esc_html($title) . '</span>';

    $data_attrs = 'data-preview-zone="' . esc_attr($zone) . '"';

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
            title="<?php echo esc_attr($label); ?>"
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
        title="<?php echo esc_attr($label); ?>"
        aria-label="<?php echo esc_attr($title); ?>"
    ><?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
    <?php
}

/**
 * Encadrant HEADER sur le plan (hero / slider selon config template).
 */
function em_wp_admin_render_landing_map_header_group(string $active_zone): void
{
    $header = em_wp_admin_header_preview_config();
    $subzones = em_wp_admin_header_preview_subzones();
    $is_hidden = !em_wp_get_site_rubrique_visibility('header');
    $layout = (string) ($header['layout'] ?? 'hero_left');
    $can_swap = count($subzones) === 2;
    $group_classes = 'em-wp-admin-landing-map__header-group is-sortable'
        . em_wp_admin_landing_zone_active_class('header', $active_zone)
        . ($is_hidden ? ' is-rubrique-hidden' : '');
    ?>
    <div
        class="<?php echo esc_attr($group_classes); ?>"
        data-module-slug="header"
        data-header-layout="<?php echo esc_attr($layout); ?>"
        data-header-can-swap="<?php echo $can_swap ? '1' : '0'; ?>"
    >
        <div class="em-wp-admin-landing-map__header-group-toolbar">
            <span class="em-wp-rubriques-sortable__handle" aria-hidden="true"><i class="fa-solid fa-grip-vertical"></i></span>
            <span class="em-wp-admin-landing-map__header-group-title"><?php esc_html_e('HEADER', 'em-wp'); ?></span>
            <?php if ($can_swap) { ?>
                <button
                    type="button"
                    class="em-wp-admin-landing-map__swap-header"
                    aria-label="<?php esc_attr_e('Inverser Hero et Slider dans HEADER', 'em-wp'); ?>"
                >
                    <i class="fa-solid fa-right-left" aria-hidden="true"></i>
                </button>
            <?php } ?>
            <?php if ($is_hidden) { ?>
                <span class="em-wp-admin-landing-map__hidden-badge"><?php esc_html_e('Masqué', 'em-wp'); ?></span>
            <?php } ?>
        </div>
        <div class="em-wp-admin-landing-map__header-group-inner<?php echo count($subzones) === 1 ? ' is-single' : ''; ?>">
            <?php
            if ($subzones === []) {
                ?>
                <a
                    class="em-wp-admin-landing-map__zone em-wp-admin-landing-map__header-empty"
                    href="<?php echo esc_url(em_wp_admin_site_rubrique_entry_url('header')); ?>"
                    data-preview-zone="header"
                    data-module-slug="header"
                    title="<?php esc_attr_e('Configurer HEADER', 'em-wp'); ?>"
                >
                    <span class="em-wp-admin-landing-map__zone-label"><?php esc_html_e('HEADER (non configuré)', 'em-wp'); ?></span>
                </a>
                <?php
            } else {
                foreach ($subzones as $subzone) {
                    $zone = (string) ($subzone['zone'] ?? '');
                    $part = (string) ($subzone['part'] ?? '');
                    $class_suffix = $part === 'hero' ? 'header-hero' : 'header-slider';

                    em_wp_admin_render_landing_map_zone(
                        $zone,
                        $active_zone,
                        $class_suffix,
                        false,
                        false,
                        'header',
                        $part
                    );
                }
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Affiche le plan complet de la landing.
 */
function em_wp_admin_render_landing_map(string $active_zone = ''): void
{
    $top_hidden = !em_wp_get_site_rubrique_visibility('top-bar');
    $footer_hidden = !em_wp_get_site_rubrique_visibility('footer');
    ?>
    <div
        class="em-wp-admin-landing-map"
        id="em-wp-admin-landing-map"
        aria-label="<?php esc_attr_e('Plan du site', 'em-wp'); ?>"
    >
        <?php em_wp_admin_render_landing_map_zone('top_bar', $active_zone, 'top-bar', $top_hidden, false); ?>

        <div class="em-wp-admin-landing-map__body" id="em-wp-admin-landing-map-body">
            <?php foreach (em_wp_get_site_rubrique_middle_order() as $module_slug) {
                $module_slug = sanitize_key((string) $module_slug);

                if ($module_slug === 'header') {
                    em_wp_admin_render_landing_map_header_group($active_zone);
                    continue;
                }

                $zone = em_wp_admin_landing_preview_zone_for_module($module_slug);
                if ($zone === '') {
                    continue;
                }

                $is_hidden = !em_wp_get_site_rubrique_visibility($module_slug);

                em_wp_admin_render_landing_map_zone($zone, $active_zone, 'section', $is_hidden, true);
            } ?>
        </div>

        <?php em_wp_admin_render_landing_map_zone('section_footer', $active_zone, 'section section-footer', $footer_hidden, false); ?>
    </div>
    <?php
}
