<?php
/**
 * Modale couleur admin partagée (wpColorPicker).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Titre par défaut de la modale couleur.
 */
function em_site_admin_color_modal_default_title(): string
{
    return __('Couleur', 'em-site');
}

/**
 * Détermine le mode d'aperçu d'un champ couleur (pastille ou texte).
 *
 * @param array{preview_type?:string,name?:string,input_class?:string} $args
 */
function em_site_admin_color_field_preview_type(array $args, string $field_name = ''): string
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
function em_site_admin_render_color_modal(): void
{
    static $rendered = false;

    if ($rendered) {
        return;
    }

    $rendered = true;
    ?>
    <div
        id="em-site-admin-color-modal"
        class="em-site-admin-color-modal"
        hidden
        aria-hidden="true"
    >
        <div class="em-site-admin-color-modal__backdrop" data-em-site-color-modal-dismiss></div>
        <div
            class="em-site-admin-color-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="em-site-admin-color-modal-title"
        >
            <header class="em-site-admin-color-modal__head">
                <h2 id="em-site-admin-color-modal-title" class="em-site-admin-color-modal__title">
                    <?php echo esc_html(em_site_admin_color_modal_default_title()); ?>
                </h2>
            </header>
            <div class="em-site-admin-color-modal__body">
                <div class="em-site-admin-color-modal__preview-wrap">
                    <span
                        id="em-site-admin-color-modal-preview-swatch"
                        class="em-site-admin-color-modal__preview em-site-admin-color-modal__preview--swatch"
                        aria-hidden="true"
                    ></span>
                    <span
                        id="em-site-admin-color-modal-preview-text"
                        class="em-site-admin-color-modal__preview-surface em-site-admin-color-modal__preview--text"
                        hidden
                        aria-hidden="true"
                    >
                        <span class="em-site-admin-color-modal__preview-text"><?php esc_html_e('Texte', 'em-site'); ?></span>
                    </span>
                    <p id="em-site-admin-color-modal-label" class="em-site-admin-color-modal__context-label"></p>
                </div>
                <div class="em-site-admin-color-field-wrap em-site-admin-color-modal__picker-wrap">
                    <div class="em-site-admin-color-control">
                        <label class="em-site-admin-color-label" for="em-site-admin-color-modal-input">
                            <?php esc_html_e('Couleur', 'em-site'); ?>
                        </label>
                        <input
                            type="text"
                            id="em-site-admin-color-modal-input"
                            class="em-site-admin-color-field em-site-admin-color-modal__input"
                            value=""
                            autocomplete="off"
                        >
                    </div>
                </div>
            </div>
            <footer class="em-site-admin-color-modal__actions">
                <button type="button" class="button button-secondary" data-em-site-color-modal-dismiss>
                    <?php esc_html_e('Annuler', 'em-site'); ?>
                </button>
                <button type="button" class="button button-primary" id="em-site-admin-color-modal-save">
                    <?php esc_html_e('Enregistrer', 'em-site'); ?>
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
function em_site_admin_render_color_field(array $args): void
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
    $preview_type = em_site_admin_color_field_preview_type($args, $name);
    $bg_target_id = sanitize_html_class((string) ($args['bg_target_id'] ?? ''));
    $is_text_preview = $preview_type === 'text';
    $trigger_classes = trim('em-site-admin-color-trigger' . ($is_text_preview ? ' em-site-admin-color-trigger--text-preview' : ''));

    $wrap_classes = trim('em-site-admin-color-field-row ' . $wrap_class);
    ?>
    <div class="<?php echo esc_attr($wrap_classes); ?>">
        <?php if ($field_label !== '') { ?>
            <?php // Pas de <label for> : le contrôle réel est un déclencheur custom
            // + un <input type="hidden"> (non « labelable »), ce qui déclenche le
            // warning Chrome « Incorrect use of <label for=FORM_ELEMENT> ». ?>
            <span class="em-site-admin-color-field-row__label">
                <?php echo esc_html($field_label); ?>
            </span>
        <?php } ?>
        <div class="<?php echo esc_attr($trigger_classes); ?>" data-em-site-color-trigger-for="<?php echo esc_attr($id); ?>">
            <?php if ($is_text_preview) { ?>
                <span
                    class="em-site-admin-color-trigger__text-preview"
                    data-em-site-color-text-preview
                    aria-hidden="true"
                >
                    <span class="em-site-admin-color-trigger__text-preview-label"><?php esc_html_e('Texte', 'em-site'); ?></span>
                </span>
            <?php } else { ?>
                <span
                    class="em-site-admin-color-trigger__swatch"
                    style="--em-site-color-swatch: <?php echo esc_attr($display !== '' ? $display : '#cccccc'); ?>;"
                    aria-hidden="true"
                ></span>
            <?php } ?>
            <button
                type="button"
                class="em-site-catalog-sommaire__edit em-site-admin-color-trigger__edit"
                data-em-site-color-modal-open
                data-em-site-color-modal-target="<?php echo esc_attr($id); ?>"
                data-em-site-color-modal-preview-type="<?php echo esc_attr($preview_type); ?>"
                <?php if ($bg_target_id !== '') { ?>
                    data-em-site-color-modal-bg-target="<?php echo esc_attr($bg_target_id); ?>"
                <?php } ?>
                <?php if ($preview_label !== '') { ?>
                    data-em-site-color-modal-label="<?php echo esc_attr($preview_label); ?>"
                <?php } ?>
                <?php if ($modal_title !== '') { ?>
                    data-em-site-color-modal-title="<?php echo esc_attr($modal_title); ?>"
                <?php } ?>
                <?php if ($default !== '') { ?>
                    data-em-site-color-modal-default="<?php echo esc_attr($default); ?>"
                <?php } ?>
                <?php if ($form_id !== '') { ?>
                    data-em-site-color-modal-form="<?php echo esc_attr($form_id); ?>"
                <?php } ?>
                <?php if ($form_value_name !== '') { ?>
                    data-em-site-color-modal-value-name="<?php echo esc_attr($form_value_name); ?>"
                <?php } ?>
                title="<?php esc_attr_e('Modifier la couleur', 'em-site'); ?>"
                aria-label="<?php esc_attr_e('Modifier la couleur', 'em-site'); ?>"
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
                class="em-site-admin-color-value<?php echo $input_class !== '' ? ' ' . esc_attr($input_class) : ''; ?>"
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
 * Injecte la modale couleur en pied de page admin em-site.
 */
function em_site_admin_boot_color_modal(): void
{
    if (!function_exists('em_site_admin_is_em_site_screen') || !em_site_admin_is_em_site_screen()) {
        return;
    }

    if (!wp_script_is('em-site-admin-color-modal', 'enqueued')) {
        return;
    }

    em_site_admin_render_color_modal();
}

add_action('admin_footer', 'em_site_admin_boot_color_modal', 20);
