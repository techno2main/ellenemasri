<?php
/**
 * Defaults et identifiants admin Top Bar.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_top_bar_item_definitions(): array
{
    return [
        'line_1_center' => __('URL', 'em-wp'),
        'line_1_right'  => __('Texte', 'em-wp'),
        'baseline'      => __('Baseline', 'em-wp'),
        'cta'           => __('CTA', 'em-wp'),
    ];
}

/**
 * Items top-bar disposant d'un style éditable (couleur + typo).
 *
 * @return string[]
 */
function em_wp_top_bar_styleable_item_keys(): array
{
    return ['line_1_right'];
}

function em_wp_top_bar_item_supports_style(string $key): bool
{
    return in_array($key, em_wp_top_bar_styleable_item_keys(), true);
}

/**
 * Typographies proposées pour les items stylables.
 *
 * @return array<string, array{label: string, stack: string}>
 */
function em_wp_top_bar_font_choices(): array
{
    return [
        ''          => ['label' => __('Par défaut', 'em-wp'), 'stack' => ''],
        'script'    => ['label' => __('Script (manuscrit)', 'em-wp'), 'stack' => '"Brush Script MT", "Segoe Script", cursive'],
        'archivo'   => ['label' => __('Archivo Black', 'em-wp'), 'stack' => '"Archivo Black", system-ui, sans-serif'],
        'trebuchet' => ['label' => __('Trebuchet', 'em-wp'), 'stack' => '"Trebuchet MS", Verdana, sans-serif'],
        'serif'     => ['label' => __('Serif', 'em-wp'), 'stack' => 'Georgia, "Times New Roman", serif'],
        'sans'      => ['label' => __('Sans-serif', 'em-wp'), 'stack' => 'Arial, Helvetica, sans-serif'],
        'mono'      => ['label' => __('Monospace', 'em-wp'), 'stack' => '"Courier New", Courier, monospace'],
    ];
}

/**
 * Retourne la pile CSS d'une typo top-bar (vide si inconnue/défaut).
 */
function em_wp_top_bar_font_stack(string $slug): string
{
    $choices = em_wp_top_bar_font_choices();

    return (string) ($choices[$slug]['stack'] ?? '');
}

function em_wp_top_bar_page_slug(): string
{
    return 'em-wp-top-bar';
}

function em_wp_top_bar_form_option_key(): string
{
    return em_wp_top_bar_option_name(em_wp_top_bar_admin_template_slug());
}

function em_wp_top_bar_rubrique_default_options(): array
{
    return [
        'enabled'          => true,
        'top_bar_slug'     => '',
        'background_color' => '',
        'text_color'       => '',
    ];
}

function em_wp_top_bar_catalog_default_options(): array
{
    $items = [];
    foreach (em_wp_top_bar_item_definitions() as $key => $title) {
        unset($title);
        $items[$key] = [
            'label'  => '',
            'href'   => '',
            'hidden' => false,
        ];

        if (em_wp_top_bar_item_supports_style($key)) {
            $items[$key]['text_color'] = '';
            $items[$key]['font']       = '';
        }
    }

    $items['cta']['label'] = __('Stream & Share', 'em-wp');
    $items['cta']['href'] = '#stream';
    $items['baseline']['label'] = __('Join the Journey!', 'em-wp');
    $items['baseline']['href'] = '#social';

    return [
        'logo_url'                 => '',
        'logo_hidden'              => false,
        'background_image_enabled' => false,
        'background_image_url'     => '',
        'background_image_hidden'  => false,
        'items'                    => $items,
        'stream_icons_hidden'      => false,
    ];
}
