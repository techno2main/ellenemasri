<?php
/**
 * Composants admin partagés — panneau "Style de base".
 *
 * Pattern unique pour Hero, Top Bar, Slider et futurs modules :
 * - section.em-wp-admin-module__panel (+ classe module optionnelle)
 * - button.em-wp-admin-module__panel-header
 * - div.em-wp-admin-module__panel-body (unique, replié si pas .is-open)
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classes du bouton d'en-tête de panneau accordion.
 */
function em_wp_admin_panel_header_class(string $panel_class = ''): string
{
    $classes = ['em-wp-admin-module__panel-header'];

    if ($panel_class !== '') {
        $classes[] = sanitize_html_class($panel_class . '__header');
    }

    return implode(' ', $classes);
}

/**
 * Rendu des champs couleur (sans wrapper panel-body).
 *
 * @param array<int, array{name:string,label:string,value:string,placeholder?:string}> $fields
 */
function em_wp_admin_render_color_fields_wrap(array $fields, string $option_name): void
{
    if ($fields === []) {
        return;
    }
    ?>
    <div class="em-wp-admin-color-field-wrap">
        <?php foreach ($fields as $field) {
            $name = sanitize_key((string) ($field['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $label = (string) ($field['label'] ?? '');
            $value = (string) ($field['value'] ?? '');
            $placeholder = (string) ($field['placeholder'] ?? '');
            ?>
            <div class="em-wp-admin-color-control">
                <span class="em-wp-admin-color-label"><?php echo esc_html($label); ?></span>
                <input
                    type="text"
                    class="regular-text em-wp-admin-color-field"
                    name="<?php echo esc_attr($option_name . '[' . $name . ']'); ?>"
                    value="<?php echo esc_attr($value); ?>"
                    <?php if ($placeholder !== '') { ?>placeholder="<?php echo esc_attr($placeholder); ?>"<?php } ?>
                >
            </div>
        <?php } ?>
    </div>
    <?php
}

/**
 * Rendu du panneau accordion "Style de base" avec champs couleur.
 *
 * @param array<int, array{name:string,label:string,value:string,placeholder?:string}> $color_fields
 * @param string $panel_class Classe module (ex. em-wp-hero-panel) pour ciblage CSS/JS optionnel.
 * @param callable|null $extra_body Contenu additionnel dans le même panel-body (ex. image de fond Top Bar).
 */
function em_wp_admin_render_base_style_panel(
    string $title,
    array $color_fields,
    string $option_name,
    string $panel_class = '',
    ?callable $extra_body = null
): void {
    $panel_classes = trim('em-wp-admin-module__panel ' . $panel_class);
    ?>
    <section class="<?php echo esc_attr($panel_classes); ?>">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class($panel_class)); ?>" type="button" aria-expanded="false">
            <span><?php echo esc_html($title); ?></span>
        </button>
        <div class="em-wp-admin-module__panel-body">
            <?php em_wp_admin_render_color_fields_wrap($color_fields, $option_name); ?>
            <?php
            if (is_callable($extra_body)) {
                call_user_func($extra_body);
            }
            ?>
        </div>
    </section>
    <?php
}
