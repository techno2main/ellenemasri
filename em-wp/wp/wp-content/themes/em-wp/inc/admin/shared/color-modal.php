<?php
/**
 * Modale couleur admin partagée (wpColorPicker).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Titre par défaut de la modale couleur.
 */
function em_wp_admin_color_modal_default_title(): string
{
    return __('Couleur', 'em-wp');
}

/**
 * Détermine le mode d'aperçu d'un champ couleur (pastille ou texte).
 *
 * @param array{preview_type?:string,name?:string,input_class?:string} $args
 */
function em_wp_admin_color_field_preview_type(array $args, string $field_name = ''): string
{
    $preview_type = (string) ($args['preview_type'] ?? '');

    if ($preview_type === 'text' || $preview_type === 'swatch') {
        return $preview_type;
    }

    $name = $field_name !== '' ? $field_name : (string) ($args['name'] ?? '');

    if ($name !== '' && preg_match('/(^|\[)background_color(\]|$)/', $name)) {
        return 'swatch';
    }

    if ($name !== '' && preg_match('/(^|\[)text_color(\]|$)/', $name)) {
        return 'text';
    }

    $input_class = (string) ($args['input_class'] ?? '');

    if (str_ends_with($input_class, '__text') || str_ends_with($input_class, '-text')) {
        return 'text';
    }

    return 'swatch';
}

/**
 * Affiche la modale couleur (une seule instance par page).
 */
function em_wp_admin_render_color_modal(): void
{
    static $rendered = false;

    if ($rendered) {
        return;
    }

    $rendered = true;
    ?>
    <div
        id="em-wp-admin-color-modal"
        class="em-wp-admin-color-modal"
        hidden
        aria-hidden="true"
    >
        <div class="em-wp-admin-color-modal__backdrop" data-em-wp-color-modal-dismiss></div>
        <div
            class="em-wp-admin-color-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="em-wp-admin-color-modal-title"
        >
            <header class="em-wp-admin-color-modal__head">
                <h2 id="em-wp-admin-color-modal-title" class="em-wp-admin-color-modal__title">
                    <?php echo esc_html(em_wp_admin_color_modal_default_title()); ?>
                </h2>
            </header>
            <div class="em-wp-admin-color-modal__body">
                <div class="em-wp-admin-color-modal__preview-wrap">
                    <span
                        id="em-wp-admin-color-modal-preview-swatch"
                        class="em-wp-admin-color-modal__preview em-wp-admin-color-modal__preview--swatch"
                        aria-hidden="true"
                    ></span>
                    <span
                        id="em-wp-admin-color-modal-preview-text"
                        class="em-wp-admin-color-modal__preview-surface em-wp-admin-color-modal__preview--text"
                        hidden
                        aria-hidden="true"
                    >
                        <span class="em-wp-admin-color-modal__preview-text"><?php esc_html_e('Texte', 'em-wp'); ?></span>
                    </span>
                    <p id="em-wp-admin-color-modal-label" class="em-wp-admin-color-modal__context-label"></p>
                </div>
                <div class="em-wp-admin-color-field-wrap em-wp-admin-color-modal__picker-wrap">
                    <div class="em-wp-admin-color-control">
                        <label class="em-wp-admin-color-label" for="em-wp-admin-color-modal-input">
                            <?php esc_html_e('Couleur', 'em-wp'); ?>
                        </label>
                        <input
                            type="text"
                            id="em-wp-admin-color-modal-input"
                            class="em-wp-admin-color-field em-wp-admin-color-modal__input"
                            value=""
                            autocomplete="off"
                        >
                    </div>
                </div>
            </div>
            <footer class="em-wp-admin-color-modal__actions">
                <button type="button" class="button button-secondary" data-em-wp-color-modal-dismiss>
                    <?php esc_html_e('Annuler', 'em-wp'); ?>
                </button>
                <button type="button" class="button button-primary" id="em-wp-admin-color-modal-save">
                    <?php esc_html_e('Enregistrer', 'em-wp'); ?>
                </button>
            </footer>
        </div>
    </div>
    <?php
}

/**
 * Champ couleur avec déclencheur modale (pastille + hex + crayon).
 *
 * @param array{
 *     id:string,
 *     name?:string,
 *     value?:string,
 *     default?:string,
 *     field_label?:string,
 *     preview_label?:string,
 *     modal_title?:string,
 *     input_class?:string,
 *     wrap_class?:string,
 *     form_id?:string,
 *     form_value_name?:string,
 *     required?:bool,
 *     preview_type?:string,
 *     bg_target_id?:string,
 * } $args
 */
function em_wp_admin_render_color_field(array $args): void
{
    $id = sanitize_html_class((string) ($args['id'] ?? ''));

    if ($id === '') {
        return;
    }

    $name = (string) ($args['name'] ?? '');
    $value = sanitize_hex_color((string) ($args['value'] ?? '')) ?: '';
    $default = sanitize_hex_color((string) ($args['default'] ?? '')) ?: '';
    $display = $value !== '' ? $value : $default;
    $field_label = (string) ($args['field_label'] ?? '');
    $preview_label = (string) ($args['preview_label'] ?? '');
    $modal_title = (string) ($args['modal_title'] ?? '');
    $input_class = trim((string) ($args['input_class'] ?? ''));
    $wrap_class = trim((string) ($args['wrap_class'] ?? ''));
    $form_id = sanitize_html_class((string) ($args['form_id'] ?? ''));
    $form_value_name = (string) ($args['form_value_name'] ?? '');
    $required = !empty($args['required']);
    $preview_type = em_wp_admin_color_field_preview_type($args, $name);
    $bg_target_id = sanitize_html_class((string) ($args['bg_target_id'] ?? ''));
    $is_text_preview = $preview_type === 'text';
    $trigger_classes = trim('em-wp-admin-color-trigger' . ($is_text_preview ? ' em-wp-admin-color-trigger--text-preview' : ''));

    $wrap_classes = trim('em-wp-admin-color-field-row ' . $wrap_class);
    ?>
    <div class="<?php echo esc_attr($wrap_classes); ?>">
        <?php if ($field_label !== '') { ?>
            <label class="em-wp-admin-color-field-row__label" for="<?php echo esc_attr($id); ?>">
                <?php echo esc_html($field_label); ?>
            </label>
        <?php } ?>
        <div class="<?php echo esc_attr($trigger_classes); ?>" data-em-wp-color-trigger-for="<?php echo esc_attr($id); ?>">
            <?php if ($is_text_preview) { ?>
                <span
                    class="em-wp-admin-color-trigger__text-preview"
                    data-em-wp-color-text-preview
                    aria-hidden="true"
                >
                    <span class="em-wp-admin-color-trigger__text-preview-label"><?php esc_html_e('Texte', 'em-wp'); ?></span>
                </span>
            <?php } else { ?>
                <span
                    class="em-wp-admin-color-trigger__swatch"
                    style="--em-wp-color-swatch: <?php echo esc_attr($display !== '' ? $display : '#cccccc'); ?>;"
                    aria-hidden="true"
                ></span>
            <?php } ?>
            <code class="em-wp-admin-color-trigger__hex"><?php echo esc_html($display); ?></code>
            <button
                type="button"
                class="em-wp-catalog-sommaire__edit em-wp-admin-color-trigger__edit"
                data-em-wp-color-modal-open
                data-em-wp-color-modal-target="<?php echo esc_attr($id); ?>"
                data-em-wp-color-modal-preview-type="<?php echo esc_attr($preview_type); ?>"
                <?php if ($bg_target_id !== '') { ?>
                    data-em-wp-color-modal-bg-target="<?php echo esc_attr($bg_target_id); ?>"
                <?php } ?>
                <?php if ($preview_label !== '') { ?>
                    data-em-wp-color-modal-label="<?php echo esc_attr($preview_label); ?>"
                <?php } ?>
                <?php if ($modal_title !== '') { ?>
                    data-em-wp-color-modal-title="<?php echo esc_attr($modal_title); ?>"
                <?php } ?>
                <?php if ($default !== '') { ?>
                    data-em-wp-color-modal-default="<?php echo esc_attr($default); ?>"
                <?php } ?>
                <?php if ($form_id !== '') { ?>
                    data-em-wp-color-modal-form="<?php echo esc_attr($form_id); ?>"
                <?php } ?>
                <?php if ($form_value_name !== '') { ?>
                    data-em-wp-color-modal-value-name="<?php echo esc_attr($form_value_name); ?>"
                <?php } ?>
                title="<?php esc_attr_e('Modifier la couleur', 'em-wp'); ?>"
                aria-label="<?php esc_attr_e('Modifier la couleur', 'em-wp'); ?>"
            >
                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
            </button>
            <input
                type="hidden"
                id="<?php echo esc_attr($id); ?>"
                <?php if ($name !== '') { ?>
                    name="<?php echo esc_attr($name); ?>"
                <?php } ?>
                value="<?php echo esc_attr($value); ?>"
                class="em-wp-admin-color-value<?php echo $input_class !== '' ? ' ' . esc_attr($input_class) : ''; ?>"
                <?php if ($default !== '') { ?>
                    data-default-color="<?php echo esc_attr($default); ?>"
                <?php } ?>
                <?php echo $required ? ' required' : ''; ?>
            >
        </div>
    </div>
    <?php
}

/**
 * Injecte la modale couleur en pied de page admin em-wp.
 */
function em_wp_admin_boot_color_modal(): void
{
    if (!function_exists('em_wp_admin_is_em_wp_screen') || !em_wp_admin_is_em_wp_screen()) {
        return;
    }

    if (!wp_script_is('em-wp-admin-color-modal', 'enqueued')) {
        return;
    }

    em_wp_admin_render_color_modal();
}

add_action('admin_footer', 'em_wp_admin_boot_color_modal', 20);
