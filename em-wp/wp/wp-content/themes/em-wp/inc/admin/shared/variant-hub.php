<?php
/**
 * Hub admin pour modules multi-variantes (Videos, Releases, …).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre menu, settings et assets d'un module hub multi-variantes.
 *
 * @param array{
 *     module_slug:string,
 *     hub_menu_slug:string,
 *     menu_icon:string,
 *     menu_position:int|callable():int,
 *     hub_title:string,
 *     eyebrow:string,
 *     hub_description:string,
 *     sidebar_title:string,
 *     item_label_pattern:string,
 *     select_prompt:string,
 *     empty_variants_message:string,
 *     active_legend:string,
 *     active_submit:string,
 *     active_option:string,
 *     active_group:string,
 *     default_active_style:string,
 *     style_definitions:callable():array<string, array{label:string,menu_title:string,page_slug:string}>,
 *     render_style_setup?:callable(array, array):void,
 *     setup_coming_soon_message?:string,
 *     option_name:callable(string):string,
 *     group_name:callable(string):string,
 *     default_options?:callable(string):array,
 *     sanitize_options?:callable(array):array,
 *     get_options?:callable(string):array,
 * } $config
 */
function em_wp_admin_boot_variant_hub(array $config): void
{
    $module_slug = sanitize_key((string) ($config['module_slug'] ?? ''));
    if ($module_slug === '') {
        return;
    }

    add_action('admin_menu', static function () use ($config, $module_slug): void {
        em_wp_admin_variant_hub_register_menu($config);
    });

    add_action('admin_menu', static function () use ($config): void {
        remove_submenu_page(
            (string) $config['hub_menu_slug'],
            (string) $config['hub_menu_slug']
        );
    }, 999);

    add_action('admin_init', static function () use ($config): void {
        em_wp_admin_variant_hub_register_settings($config);
        em_wp_admin_variant_hub_register_active_save($config);
    });

    add_action('admin_enqueue_scripts', static function (string $hook_suffix) use ($config): void {
        unset($hook_suffix);
        em_wp_admin_variant_hub_enqueue($config);
    });
}

/**
 * @param array<string, mixed> $config
 */
function em_wp_admin_variant_hub_style_definitions(array $config): array
{
    $callback = $config['style_definitions'] ?? null;

    if (!is_callable($callback)) {
        return [];
    }

    $definitions = call_user_func($callback);

    return is_array($definitions) ? $definitions : [];
}

/**
 * @param array<string, mixed> $config
 * @return string[]
 */
function em_wp_admin_variant_hub_admin_page_slugs(array $config): array
{
    $definitions = em_wp_admin_variant_hub_style_definitions($config);

    return array_merge(
        [(string) ($config['hub_menu_slug'] ?? '')],
        wp_list_pluck($definitions, 'page_slug')
    );
}

/**
 * @param array<string, mixed> $config
 */
function em_wp_admin_variant_hub_register_menu(array $config): void
{
    $definitions = em_wp_admin_variant_hub_style_definitions($config);
    $parent_slug = (string) ($config['hub_menu_slug'] ?? '');
    $position = $config['menu_position'] ?? em_wp_admin_site_rubrique_menu_base();

    if (is_callable($position)) {
        $position = (int) call_user_func($position);
    }

    add_menu_page(
        (string) ($config['hub_title'] ?? ''),
        (string) ($config['hub_title'] ?? ''),
        'manage_options',
        $parent_slug,
        static function () use ($config): void {
            em_wp_admin_variant_hub_render_page($config);
        },
        (string) ($config['menu_icon'] ?? 'dashicons-admin-generic'),
        (int) $position
    );

    foreach ($definitions as $definition) {
        add_submenu_page(
            $parent_slug,
            (string) ($definition['menu_title'] ?? ''),
            (string) ($definition['menu_title'] ?? ''),
            'manage_options',
            (string) ($definition['page_slug'] ?? ''),
            static function () use ($config): void {
                em_wp_admin_variant_hub_render_page($config);
            }
        );
    }
}

/**
 * @param array<string, mixed> $config
 */
function em_wp_admin_variant_hub_register_settings(array $config): void
{
    $definitions = em_wp_admin_variant_hub_style_definitions($config);
    $sanitize_active = static function ($value) use ($config, $definitions): string {
        $slug = sanitize_key((string) $value);
        $default = sanitize_key((string) ($config['default_active_style'] ?? ''));

        if ($slug !== '' && isset($definitions[$slug])) {
            return $slug;
        }

        if ($default !== '' && isset($definitions[$default])) {
            return $default;
        }

        $keys = array_keys($definitions);

        return $keys[0] ?? '';
    };

    register_setting(
        (string) ($config['active_group'] ?? ''),
        (string) ($config['active_option'] ?? ''),
        [
            'type'              => 'string',
            'sanitize_callback' => $sanitize_active,
            'default'           => (string) ($config['default_active_style'] ?? ''),
        ]
    );

    $sanitize_options = $config['sanitize_options'] ?? null;
    $default_options = $config['default_options'] ?? null;
    $option_name = $config['option_name'] ?? null;
    $group_name = $config['group_name'] ?? null;

    if (!is_callable($sanitize_options) || !is_callable($default_options) || !is_callable($option_name) || !is_callable($group_name)) {
        return;
    }

    foreach (array_keys($definitions) as $style_slug) {
        register_setting(
            call_user_func($group_name, $style_slug),
            call_user_func($option_name, $style_slug),
            [
                'type'              => 'array',
                'sanitize_callback' => $sanitize_options,
                'default'           => call_user_func($default_options, $style_slug),
            ]
        );
    }
}

/**
 * Enregistre la sauvegarde « variante active » (hub sidebar).
 *
 * @param array<string, mixed> $config
 */
function em_wp_admin_variant_hub_register_active_save(array $config): void
{
    $module_slug = sanitize_key((string) ($config['module_slug'] ?? ''));
    if ($module_slug === '') {
        return;
    }

    $definitions = em_wp_admin_variant_hub_style_definitions($config);

    em_wp_admin_register_module_save($module_slug . '-active', [
        'type'          => 'active_style',
        'nonce_action'  => 'em_wp_' . $module_slug . '_active_save',
        'option_name'   => (string) ($config['active_option'] ?? ''),
        'value_field'   => (string) ($config['active_option'] ?? ''),
        'page_slug'     => 'referer',
        'fallback_page' => (string) ($config['hub_menu_slug'] ?? ''),
        'sanitize'      => static function ($value) use ($config, $definitions): string {
            $slug = sanitize_key((string) $value);
            $default = sanitize_key((string) ($config['default_active_style'] ?? ''));

            if ($slug !== '' && isset($definitions[$slug])) {
                return $slug;
            }

            if ($default !== '' && isset($definitions[$default])) {
                return $default;
            }

            $keys = array_keys($definitions);

            return $keys[0] ?? '';
        },
    ]);
}

/**
 * @param array<string, mixed> $config
 */
function em_wp_admin_variant_hub_enqueue(array $config): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (!in_array($page_slug, em_wp_admin_variant_hub_admin_page_slugs($config), true)) {
        return;
    }

    em_wp_admin_enqueue_shared_assets();
}

/**
 * @param array<string, mixed> $config
 * @return array{style_slug:string,label:string,page_slug:string,option_name:string,group:string}
 */
function em_wp_admin_variant_hub_get_context(array $config): array
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $hub_slug = (string) ($config['hub_menu_slug'] ?? '');

    if ($page_slug === $hub_slug || $page_slug === '') {
        return [
            'style_slug'  => '',
            'label'       => '',
            'page_slug'   => $hub_slug,
            'option_name' => '',
            'group'       => '',
        ];
    }

    foreach (em_wp_admin_variant_hub_style_definitions($config) as $style_slug => $definition) {
        if (($definition['page_slug'] ?? '') === $page_slug) {
            $option_name = $config['option_name'] ?? null;
            $group_name = $config['group_name'] ?? null;

            return [
                'style_slug'  => (string) $style_slug,
                'label'       => (string) ($definition['label'] ?? $style_slug),
                'page_slug'   => (string) ($definition['page_slug'] ?? ''),
                'option_name' => is_callable($option_name) ? call_user_func($option_name, $style_slug) : '',
                'group'       => is_callable($group_name) ? call_user_func($group_name, $style_slug) : '',
            ];
        }
    }

    return [
        'style_slug'  => '',
        'label'       => '',
        'page_slug'   => $hub_slug,
        'option_name' => '',
        'group'       => '',
    ];
}

/**
 * @param array<string, mixed> $config
 */
function em_wp_admin_variant_hub_active_style_slug(array $config): string
{
    $saved = get_option((string) ($config['active_option'] ?? ''), (string) ($config['default_active_style'] ?? ''));
    $definitions = em_wp_admin_variant_hub_style_definitions($config);
    $slug = sanitize_key((string) $saved);

    if ($slug !== '' && isset($definitions[$slug])) {
        return $slug;
    }

    $default = sanitize_key((string) ($config['default_active_style'] ?? ''));
    if ($default !== '' && isset($definitions[$default])) {
        return $default;
    }

    $keys = array_keys($definitions);

    return $keys[0] ?? '';
}

/**
 * @param array<string, mixed> $config
 */
function em_wp_admin_variant_hub_render_page(array $config): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $context = em_wp_admin_variant_hub_get_context($config);
    $style_slug = (string) ($context['style_slug'] ?? '');
    $active_style = em_wp_admin_variant_hub_active_style_slug($config);
    $module_class = 'em-wp-' . sanitize_html_class((string) ($config['module_slug'] ?? 'module')) . '-admin';
    ?>
    <div class="wrap <?php echo esc_attr($module_class); ?> em-wp-admin-module">
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="<?php echo esc_attr($module_class . '__hero'); ?> em-wp-admin-module__hero">
            <div>
                <p class="em-wp-admin-module__eyebrow"><?php echo esc_html((string) ($config['eyebrow'] ?? '')); ?></p>
                <p class="em-wp-admin-module__description"><?php echo esc_html((string) ($config['hub_description'] ?? '')); ?></p>
            </div>
        </div>

        <div class="em-wp-admin-module-hub">
            <?php em_wp_admin_variant_hub_render_sidebar($config, $style_slug, $active_style); ?>

            <div class="em-wp-admin-module-hub__content">
                <?php if ($style_slug === '') { ?>
                    <div class="em-wp-admin-module-hub__empty">
                        <p><?php echo esc_html((string) ($config['select_prompt'] ?? '')); ?></p>
                    </div>
                <?php } else {
                    $get_options = $config['get_options'] ?? null;
                    $options = is_callable($get_options) ? call_user_func($get_options, $style_slug) : [];
                    $render_setup = $config['render_style_setup'] ?? null;

                    if (is_callable($render_setup)) {
                        call_user_func($render_setup, $context, $options);
                    } else {
                        em_wp_admin_variant_hub_render_coming_soon_setup($config, $context);
                    }
                } ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * @param array<string, mixed> $config
 */
function em_wp_admin_variant_hub_render_sidebar(array $config, string $selected_style_slug, string $active_style_slug): void
{
    $definitions = em_wp_admin_variant_hub_style_definitions($config);
    $module_slug = sanitize_key((string) ($config['module_slug'] ?? ''));
    $form_page_slug = $selected_style_slug !== '' && isset($definitions[$selected_style_slug])
        ? (string) ($definitions[$selected_style_slug]['page_slug'] ?? ($config['hub_menu_slug'] ?? ''))
        : (string) ($config['hub_menu_slug'] ?? '');
    ?>
    <aside class="em-wp-admin-module-hub__sidebar">
        <h2 class="em-wp-admin-module-hub__title"><?php echo esc_html((string) ($config['sidebar_title'] ?? '')); ?></h2>

        <?php if ($definitions === []) { ?>
            <p class="em-wp-admin-module-hub__empty-note"><?php echo esc_html((string) ($config['empty_variants_message'] ?? '')); ?></p>
        <?php } else { ?>
            <ul class="em-wp-admin-module-hub__list">
                <?php foreach ($definitions as $style_slug => $definition) {
                    $page_slug = (string) ($definition['page_slug'] ?? '');
                    $label = (string) ($definition['label'] ?? $style_slug);
                    $is_selected = $selected_style_slug === $style_slug;
                    $is_active = $active_style_slug === $style_slug;
                    $item_url = add_query_arg(['page' => $page_slug], admin_url('admin.php'));
                    $item_label = sprintf((string) ($config['item_label_pattern'] ?? '%s'), $label);
                    ?>
                    <li class="em-wp-admin-module-hub__list-item<?php echo $is_selected ? ' is-selected' : ''; ?><?php echo $is_active ? ' is-active' : ''; ?>">
                        <a class="em-wp-admin-module-hub__list-link" href="<?php echo esc_url($item_url); ?>">
                            <span class="em-wp-admin-module-hub__list-label"><?php echo esc_html($item_label); ?></span>
                            <?php if ($is_active) { ?>
                                <span class="em-wp-admin-module-hub__badge em-wp-admin-module-hub__badge--active"><?php esc_html_e('Actif', 'em-wp'); ?></span>
                            <?php } ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>

            <form class="em-wp-admin-module-hub__active-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action($form_page_slug)); ?>">
                <?php em_wp_admin_render_form_save_fields($module_slug . '-active', 'em_wp_' . $module_slug . '_active_save'); ?>
                <fieldset class="em-wp-admin-module-hub__active-fieldset">
                    <legend><?php echo esc_html((string) ($config['active_legend'] ?? '')); ?></legend>
                    <?php foreach ($definitions as $style_slug => $definition) {
                        $label = (string) ($definition['label'] ?? $style_slug);
                        ?>
                        <label class="em-wp-admin-module-hub__active-option">
                            <input type="radio" name="<?php echo esc_attr((string) ($config['active_option'] ?? '')); ?>" value="<?php echo esc_attr($style_slug); ?>" <?php checked($active_style_slug, $style_slug); ?>>
                            <span><?php echo esc_html($label); ?></span>
                        </label>
                    <?php } ?>
                </fieldset>
                <?php submit_button((string) ($config['active_submit'] ?? ''), 'secondary', 'submit', false); ?>
            </form>
        <?php } ?>
    </aside>
    <?php
}

/**
 * @param array<string, mixed> $config
 * @param array<string, mixed> $context
 */
function em_wp_admin_variant_hub_render_coming_soon_setup(array $config, array $context): void
{
    $variant_label = strtoupper((string) ($context['label'] ?? ''));
    $module_class = 'em-wp-' . sanitize_html_class((string) ($config['module_slug'] ?? 'module')) . '-admin';
    ?>
    <div class="<?php echo esc_attr($module_class . '__setup'); ?>">
        <div class="<?php echo esc_attr($module_class . '__setup-header'); ?> em-wp-admin-module__hero">
            <div>
                <p class="em-wp-admin-module__eyebrow"><?php echo esc_html((string) ($config['eyebrow'] ?? '')); ?></p>
                <h2 class="em-wp-admin-module__title">
                    <?php echo esc_html(sprintf('%s - %s', (string) ($config['hub_title'] ?? ''), $variant_label)); ?>
                </h2>
            </div>
        </div>

        <div class="em-wp-rubrique-soon__panel">
            <p class="em-wp-rubrique-soon__message">
                <?php echo esc_html((string) ($config['setup_coming_soon_message'] ?? __('Module en cours de développement. La configuration sera disponible prochainement.', 'em-wp'))); ?>
            </p>
            <p>
                <a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=' . em_wp_admin_rubriques_page_slug())); ?>">
                    <?php esc_html_e('← Retour au sommaire', 'em-wp'); ?>
                </a>
            </p>
        </div>
    </div>
    <?php
}
