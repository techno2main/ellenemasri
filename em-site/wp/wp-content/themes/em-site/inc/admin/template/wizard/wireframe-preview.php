<?php

/**

 * Rendu wireframe global wizard depuis brouillon.

 *

 * @package em-wp

 */



if (!defined('ABSPATH')) {

    exit;

}



/**

 * Ordre milieu (sans top-bar / footer) depuis le payload wizard.

 *

 * @param array<int, string> $order

 * @return array<int, string>

 */

function em_wp_admin_template_wizard_middle_order(array $order): array

{

    return array_values(array_filter(array_map('sanitize_key', $order), static function ($slug): bool {

        return is_string($slug) && $slug !== '' && $slug !== 'top-bar' && $slug !== 'footer';

    }));

}



/**

 * Config HEADER depuis le catalogue brouillon.

 *

 * @param array<string, mixed> $catalog

 * @return array<string, mixed>

 */

function em_wp_admin_template_wizard_header_config(array $catalog): array

{

    $header_row = is_array($catalog['header'] ?? null) ? $catalog['header'] : [];

    $defaults = function_exists('em_wp_header_default_options') ? em_wp_header_default_options() : [];

    $layout = sanitize_key((string) ($header_row['layout'] ?? 'hero_left'));



    return wp_parse_args(

        [

            'hero_slug'   => sanitize_key((string) ($header_row['hero_slug'] ?? '')),

            'slider_slug' => sanitize_key((string) ($header_row['slider_slug'] ?? '')),

            'layout'      => in_array($layout, ['hero_left', 'slider_left'], true) ? $layout : 'hero_left',

        ],

        $defaults

    );

}



/**

 * Zone preview pour une rubrique du brouillon wizard (toutes définitions connues).

 */

function em_wp_admin_template_wizard_preview_zone_for_module(string $module_slug): string

{

    $module_slug = sanitize_key($module_slug);

    $definitions = em_wp_admin_site_rubrique_all_definitions();

    $definition = $definitions[$module_slug] ?? null;



    if (is_array($definition) && !empty($definition['preview_zone'])) {

        return sanitize_key((string) $definition['preview_zone']);

    }



    return function_exists('em_wp_admin_landing_preview_zone_for_module')

        ? em_wp_admin_landing_preview_zone_for_module($module_slug)

        : '';

}



/**

 * @param array<string, mixed> $payload

 */

function em_wp_admin_template_wizard_render_wireframe_html(array $payload): string

{

    $editable = !empty($payload['wireframeEditable']);

    $skeleton = is_array($payload['skeleton'] ?? null) ? $payload['skeleton'] : [];

    $order = is_array($skeleton['order'] ?? null) ? $skeleton['order'] : [];

    $catalog = is_array($payload['catalog'] ?? null) ? $payload['catalog'] : [];

    $template_label = sanitize_text_field((string) ($payload['label'] ?? ''));

    $middle_order = em_wp_admin_template_wizard_middle_order($order);

    $map_modifier = $editable ? 'em-wp-admin-landing-map--wizard-edit' : 'em-wp-admin-landing-map--wizard-preview';

    $header_args = [

        'show_hidden_badge'   => false,

        'header_config'       => em_wp_admin_template_wizard_header_config($catalog),

        'subzones_clickable'  => false,

        'subzone_display'     => 'placeholders',

        'disable_header_link' => true,

        'disable_swap'        => true,

        'is_hidden'           => false,

    ];



    if ($editable) {

        $header_args['interactive'] = true;

    } else {

        $header_args['interactive'] = false;

    }



    ob_start();

    ?>

    <div class="em-wp-template-wizard-global-wireframe">

        <p class="em-wp-template-wizard-global-wireframe__label">

            <?php

            if ($template_label !== '') {

                if ($editable) {

                    printf(

                        /* translators: %s: template label */

                        esc_html__('Plan du template %s', 'em-wp'),

                        esc_html(strtoupper($template_label))

                    );

                } else {

                    printf(

                        /* translators: %s: template label */

                        esc_html__('Prévisualisation du template %s', 'em-wp'),

                        esc_html(strtoupper($template_label))

                    );

                }

            } elseif ($editable) {

                esc_html_e('Plan du template', 'em-wp');

            } else {

                esc_html_e('Prévisualisation du template', 'em-wp');

            }

            ?>

        </p>

        <div class="em-wp-template-wizard-global-wireframe__map">

            <div

                class="em-wp-admin-landing-map <?php echo esc_attr($map_modifier); ?>"

                aria-label="<?php echo $editable ? esc_attr__('Plan du template', 'em-wp') : esc_attr__('Prévisualisation du template', 'em-wp'); ?>"

            >

                <?php

                if (in_array('top-bar', $order, true)) {

                    em_wp_admin_render_landing_map_zone(

                        'top_bar',

                        '',

                        'top-bar',

                        false,

                        false,

                        'top-bar',

                        '',

                        '',

                        '',

                        true,

                        true,

                        false

                    );

                }

                ?>

                <div class="em-wp-admin-landing-map__body" id="em-wp-template-wizard-map-body">

                    <?php

                    foreach ($middle_order as $module_slug) {

                        if ($module_slug === 'header') {

                            em_wp_admin_render_landing_map_header_group('', $header_args);

                            continue;

                        }



                        $zone = em_wp_admin_template_wizard_preview_zone_for_module($module_slug);



                        if ($zone === '') {

                            continue;

                        }



                        em_wp_admin_render_landing_map_zone(

                            $zone,

                            '',

                            'section',

                            false,

                            false,

                            $module_slug,

                            '',

                            '',

                            '',

                            true,

                            true,

                            false

                        );

                    }

                    ?>

                </div>

                <?php

                if (in_array('footer', $order, true)) {

                    em_wp_admin_render_landing_map_zone(

                        'section_footer',

                        '',

                        'section section-footer',

                        false,

                        false,

                        'footer',

                        '',

                        '',

                        '',

                        true,

                        true,

                        false

                    );

                }

                ?>

            </div>

        </div>

    </div>

    <?php



    return (string) ob_get_clean();

}

