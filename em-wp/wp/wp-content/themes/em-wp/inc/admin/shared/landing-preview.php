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
 * Libellé court d'une zone (infobulle).
 */
function em_wp_admin_landing_preview_zone_label(string $zone): string
{
    $labels = [
        'top_bar'        => __('Barre du haut', 'em-wp'),
        'hero_content'   => __('Contenu hero', 'em-wp'),
        'hero_slider'    => __('Slider hero', 'em-wp'),
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
 * Couleur d'accent d'une zone.
 */
function em_wp_admin_landing_preview_zone_color(string $zone): string
{
    $definitions = em_wp_admin_site_rubrique_definitions();

    foreach ($definitions as $definition) {
        if (($definition['preview_zone'] ?? '') === $zone) {
            return (string) ($definition['accent_color'] ?? '#100421');
        }
    }

    return '#100421';
}

/**
 * Classe CSS is-active pour une zone du plan.
 */
function em_wp_admin_landing_zone_active_class(string $zone, string $active_zone): string
{
    return $zone === $active_zone ? ' is-active' : '';
}

/**
 * URL admin de la rubrique liée à une zone du plan.
 */
function em_wp_admin_landing_preview_zone_url(string $zone): string
{
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
 * Zone preview pour un module (hero → hero_content, slider → hero_slider, etc.).
 */
function em_wp_admin_landing_preview_zone_for_module(string $module_slug): string
{
    $zone = em_wp_admin_landing_preview_module_zone($module_slug);

    if ($zone !== '') {
        return $zone;
    }

    return match ($module_slug) {
        'hero'   => 'hero_content',
        'slider' => 'hero_slider',
        default  => '',
    };
}

/**
 * Affiche une zone cliquable du plan landing.
 */
function em_wp_admin_render_landing_map_zone(
    string $zone,
    string $active_zone,
    string $class_suffix,
    bool $is_hidden = false,
    bool $is_sortable = false
): void {
    $url = em_wp_admin_landing_preview_zone_url($zone);
    $label = em_wp_admin_landing_preview_zone_label($zone);
    $title = em_wp_admin_landing_preview_zone_title($zone);
    $color = em_wp_admin_landing_preview_zone_color($zone);
    $module_slug = em_wp_admin_landing_preview_zone_module_slug($zone);

    if ($module_slug === '' && $zone === 'hero_content') {
        $module_slug = 'hero';
    } elseif ($module_slug === '' && $zone === 'hero_slider') {
        $module_slug = 'slider';
    }

    if (!$is_sortable && $module_slug !== '' && em_wp_site_rubrique_is_reorderable($module_slug)) {
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

    if ($url === '') {
        ?>
        <span
            class="<?php echo esc_attr($classes); ?>"
            data-preview-zone="<?php echo esc_attr($zone); ?>"
            <?php if ($module_slug !== '') { ?>
                data-module-slug="<?php echo esc_attr($module_slug); ?>"
            <?php } ?>
            style="--em-zone-accent: <?php echo esc_attr($color); ?>"
            title="<?php echo esc_attr($label); ?>"
        ><?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        <?php
        return;
    }
    ?>
    <a
        class="<?php echo esc_attr($classes); ?>"
        href="<?php echo esc_url($url); ?>"
        data-preview-zone="<?php echo esc_attr($zone); ?>"
        <?php if ($module_slug !== '') { ?>
            data-module-slug="<?php echo esc_attr($module_slug); ?>"
        <?php } ?>
        style="--em-zone-accent: <?php echo esc_attr($color); ?>"
        title="<?php echo esc_attr($label); ?>"
        aria-label="<?php echo esc_attr($title); ?>"
    ><?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
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
                $zone = em_wp_admin_landing_preview_zone_for_module($module_slug);
                if ($zone === '') {
                    continue;
                }

                $class_suffix = match ($zone) {
                    'hero_content' => 'hero-content',
                    'hero_slider'  => 'hero-slider',
                    default        => 'section',
                };

                em_wp_admin_render_landing_map_zone($zone, $active_zone, $class_suffix, false, true);
            } ?>
        </div>

        <?php em_wp_admin_render_landing_map_zone('section_footer', $active_zone, 'section section-footer', $footer_hidden, false); ?>
    </div>
    <?php
}
