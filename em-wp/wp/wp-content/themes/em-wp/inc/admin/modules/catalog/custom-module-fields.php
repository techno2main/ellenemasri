<?php
/**
 * Rendu des champs — entrées catalogue personnalisées.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<string, string|bool> $options
 * @param array<string, array{label:string,type:string}> $fields
 */
function em_wp_custom_catalog_render_entry_fields_panel(
    array $options,
    array $fields,
    string $option_name,
    string $module_slug = ''
): void {
    if ($fields === []) {
        return;
    }

    $module_slug = sanitize_key($module_slug);
    ?>
    <div class="em-wp-admin-module__panels em-wp-custom-catalog-form__panels">
        <?php
        em_wp_admin_render_module_panel(
            __('Coordonnées', 'em-wp'),
            'em-wp-custom-catalog-panel',
            static function () use ($options, $fields, $option_name, $module_slug): void {
                ?>
                <div class="em-wp-custom-catalog-fields em-wp-release-rows-list">
                    <?php foreach ($fields as $field_key => $definition) {
                        $field_key = sanitize_key((string) $field_key);
                        $label_key = em_wp_custom_catalog_field_label_key($field_key);
                        $hidden_key = em_wp_custom_catalog_field_hidden_key($field_key);
                        $type = sanitize_key((string) ($definition['type'] ?? 'text')) ?: 'text';
                        $input_type = in_array($type, ['email', 'url', 'tel'], true) ? $type : 'text';
                        $value = (string) ($options[$field_key] ?? '');
                        $label_value = em_wp_custom_catalog_field_display_label($module_slug, $field_key, $options);
                        $is_hidden = !empty($options[$hidden_key]);
                        ?>
                        <div class="em-wp-custom-catalog-field-row em-wp-release-row-item em-wp-admin-panel-body--row<?php echo $is_hidden ? ' is-row-hidden' : ''; ?>">
                            <label class="em-wp-admin-field--compact">
                                <span><?php esc_html_e('Label', 'em-wp'); ?></span>
                                <input
                                    type="text"
                                    class="regular-text"
                                    name="<?php echo esc_attr($option_name . '[' . $label_key . ']'); ?>"
                                    value="<?php echo esc_attr($label_value); ?>"
                                    autocomplete="off"
                                >
                            </label>
                            <label class="em-wp-admin-field--wide-inline">
                                <span><?php esc_html_e('Valeur', 'em-wp'); ?></span>
                                <input
                                    type="<?php echo esc_attr($input_type); ?>"
                                    class="regular-text"
                                    name="<?php echo esc_attr($option_name . '[' . $field_key . ']'); ?>"
                                    value="<?php echo esc_attr($value); ?>"
                                    autocomplete="off"
                                >
                            </label>
                            <div class="em-wp-release-row-item__actions">
                                <label class="em-wp-admin-inline-check">
                                    <span><?php esc_html_e('Masquer', 'em-wp'); ?></span>
                                    <input
                                        type="checkbox"
                                        class="em-wp-custom-catalog-field-hidden"
                                        name="<?php echo esc_attr($option_name . '[' . $hidden_key . ']'); ?>"
                                        value="1"
                                        <?php checked($is_hidden); ?>
                                    >
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
            },
            'em-wp-custom-catalog-panel__body'
        );
        ?>
    </div>
    <?php
}

function em_wp_custom_catalog_render_entry_form_actions(): void
{
    ?>
    <div class="em-wp-custom-catalog-form__actions">
        <?php
        submit_button(
            __('Enregistrer', 'em-wp'),
            'primary',
            'submit',
            true,
            [
                'disabled'      => 'disabled',
                'aria-disabled' => 'true',
            ]
        );
        ?>
    </div>
    <?php
}
