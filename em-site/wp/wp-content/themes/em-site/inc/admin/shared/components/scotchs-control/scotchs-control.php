<?php
/**
 * Composant scotchs mutualise du builder (PHP).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu unique du controle scotchs (color picker + checkbox masquage).
 *
 * @param array<string,mixed> $args
 */
function em_wp_v4_render_scotchs_component(array $args): void
{
    $hidden_class = isset($args['hidden_class']) ? (string) $args['hidden_class'] : '';
    $hidden_checked = !empty($args['hidden_checked']);
    $hidden_label = isset($args['hidden_label']) ? (string) $args['hidden_label'] : __('Masquer les scotchs', 'em-wp');
    $hidden_title = isset($args['hidden_title']) ? (string) $args['hidden_title'] : '';
    $hidden_wrap_class = isset($args['hidden_wrap_class']) ? (string) $args['hidden_wrap_class'] : 'em-v4-slides__opt em-v4-slides__opt--check em-v4-slides__opt--check-tapes';
    $hidden_wrap_class = trim($hidden_wrap_class . ' em-v4-scotchs-control__check');

    $color_class = isset($args['color_class']) ? (string) $args['color_class'] : 'em-v4-chip__tapes-color';
    $color_value = isset($args['color_value']) ? (string) $args['color_value'] : '';
    $color_label = isset($args['color_label']) ? (string) $args['color_label'] : __('Scotch', 'em-wp');
    $color_wrap_class = isset($args['color_wrap_class']) ? (string) $args['color_wrap_class'] : 'em-v4-slides__colorfield';
    $color_wrap_class = trim($color_wrap_class . ' em-v4-scotchs-control__color');
    $color_prefix = isset($args['color_prefix']) ? (string) $args['color_prefix'] : 'emv4tp-';
    $key = isset($args['key']) ? (string) $args['key'] : '';

    $color_id = function_exists('em_wp_v4_chip_color_id')
        ? em_wp_v4_chip_color_id($color_prefix, $key)
        : ($color_prefix . wp_unique_id());
    ?>
    <?php em_wp_admin_render_color_field([
        'id'          => $color_id,
        'value'       => $color_value,
        'field_label' => $color_label,
        'input_class' => $color_class,
        'wrap_class'  => $color_wrap_class,
    ]); ?>
    <?php if ($hidden_class !== '') : ?>
        <label class="<?php echo esc_attr($hidden_wrap_class); ?>"<?php echo $hidden_title !== '' ? ' title="' . esc_attr($hidden_title) . '"' : ''; ?>>
            <input type="checkbox" class="<?php echo esc_attr($hidden_class); ?>" <?php checked($hidden_checked); ?>>
            <?php echo esc_html($hidden_label); ?>
        </label>
    <?php endif; ?>
    <?php
}
