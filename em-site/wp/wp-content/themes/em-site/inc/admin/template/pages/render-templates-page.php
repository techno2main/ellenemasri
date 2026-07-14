<?php

/**

 * Rendu page admin Templates (tableau enregistrés, réutilisable).

 *

 * @package em-site

 */



if (!defined('ABSPATH')) {

    exit;

}



/**

 * Redirige l'ancienne page CRUD masquée vers Mes Templates.

 */

function em_site_admin_render_templates_page(): void

{

    if (!current_user_can('manage_options')) {

        return;

    }



    $redirect = function_exists('em_site_admin_templates_manage_admin_url')

        ? em_site_admin_templates_manage_admin_url()

        : em_site_admin_template_choice_admin_url();



    em_site_admin_safe_redirect($redirect);

}



/**

 * Tableau des templates enregistrés.

 *

 * @param array<string, array<string, mixed>> $registry

 */

function em_site_admin_render_templates_registered_table(

    array $registry,

    string $active_slug,

    bool $can_manage,

    string $registered_title_id

): void {

    $template_icon = function_exists('em_site_site_icon') ? em_site_site_icon('template', 'dashicons-layout') : 'dashicons-layout';

    ?>

    <section class="em-site-catalog-sommaire__section" aria-labelledby="<?php echo esc_attr($registered_title_id); ?>">

        <header class="em-site-catalog-sommaire__section-header">

            <div id="<?php echo esc_attr($registered_title_id); ?>" class="em-site-catalog-sommaire__section-title">

                <?php em_site_admin_hub_render_card_title(__('TEMPLATES ENREGISTRÉS', 'em-site'), $template_icon); ?>

            </div>

        </header>



        <div class="em-site-catalog-sommaire__section-body">

            <table class="widefat striped em-site-catalog-sommaire__table em-site-templates-admin__table">

                <thead>

                    <tr>

                        <th scope="col"><?php esc_html_e('Nom', 'em-site'); ?></th>

                        <th scope="col"><?php esc_html_e('Couleur', 'em-site'); ?></th>

                        <th scope="col"><?php esc_html_e('Identifiant', 'em-site'); ?></th>

                        <th scope="col"><?php esc_html_e('Actif sur le site', 'em-site'); ?></th>

                        <?php if ($can_manage) { ?>

                            <th scope="col" class="em-site-catalog-sommaire__actions-col">

                                <?php esc_html_e('Actions', 'em-site'); ?>

                            </th>

                        <?php } ?>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($registry as $slug => $definition) {

                        em_site_admin_render_templates_registered_row(

                            (string) $slug,

                            $definition,

                            $active_slug,

                            $can_manage,

                            count($registry)

                        );

                    } ?>

                </tbody>

            </table>

        </div>



    </section>

    <?php

}



/**

 * @param array<string, mixed> $definition

 */

function em_site_admin_render_templates_registered_row(

    string $slug,

    array $definition,

    string $active_slug,

    bool $can_manage,

    int $registry_count

): void {

    $label = (string) ($definition['label'] ?? $slug);

    $color = em_site_get_template_color($slug);

    $is_active = ($slug === $active_slug);

    $preview_url = function_exists('em_site_template_preview_url')

        ? em_site_template_preview_url($slug)

        : '';

    $entry_url = add_query_arg(

        ['page' => em_site_admin_template_entry_page_slug($slug)],

        admin_url('admin.php')

    );

    $rename_form_id = 'em-site-template-rename-' . $slug;

    $color_form_id = 'em-site-template-color-' . $slug;

    $color_value_id = 'em-site-template-color-value-' . $slug;

    ?>

    <tr>

        <td class="em-site-catalog-sommaire__name">

            <?php if ($can_manage && !em_site_template_is_default($slug)) { ?>

                <div class="em-site-templates-admin__inline-field" data-em-site-template-inline-field="name">

                    <button

                        type="button"

                        class="em-site-catalog-sommaire__edit em-site-templates-admin__inline-edit"

                        data-em-site-template-inline-edit="name"

                        data-em-site-template-form="<?php echo esc_attr($rename_form_id); ?>"

                        title="<?php esc_attr_e('Modifier le nom', 'em-site'); ?>"

                        aria-label="<?php echo esc_attr(sprintf(__('Modifier le nom de %s', 'em-site'), $label)); ?>"

                    >

                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>

                    </button>

                    <span class="em-site-templates-admin__inline-value"><?php echo esc_html($label); ?></span>

                    <form

                        id="<?php echo esc_attr($rename_form_id); ?>"

                        method="post"

                        class="em-site-templates-admin__rename-form"

                        hidden

                    >

                        <?php wp_nonce_field('em_site_template_rename'); ?>

                        <input type="hidden" name="em_site_template_action" value="rename">

                        <input type="hidden" name="em_site_template_slug" value="<?php echo esc_attr($slug); ?>">

                        <input

                            type="text"

                            name="em_site_template_label"

                            value="<?php echo esc_attr($label); ?>"

                            class="regular-text em-site-catalog-sommaire__label-input em-site-templates-admin__inline-input"

                            required

                            autocomplete="off"

                        >

                        <button

                            type="submit"

                            class="em-site-catalog-sommaire__save em-site-templates-admin__inline-save"

                            title="<?php esc_attr_e('Enregistrer le nom', 'em-site'); ?>"

                            aria-label="<?php echo esc_attr(sprintf(__('Enregistrer le nom de %s', 'em-site'), $label)); ?>"

                        >

                            <i class="fa-solid fa-check" aria-hidden="true"></i>

                        </button>

                        <button

                            type="button"

                            class="em-site-templates-admin__inline-cancel"

                            data-em-site-template-inline-cancel="name"

                            title="<?php esc_attr_e('Annuler', 'em-site'); ?>"

                            aria-label="<?php esc_attr_e('Annuler la modification du nom', 'em-site'); ?>"

                        >

                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>

                        </button>

                    </form>

                </div>

            <?php } else { ?>

                <div class="em-site-templates-admin__inline-field">

                    <span class="em-site-templates-admin__edit-placeholder" aria-hidden="true"></span>

                    <span class="em-site-templates-admin__inline-value"><?php echo esc_html($label); ?></span>

                </div>

            <?php } ?>

        </td>

        <td>

            <?php if ($can_manage) { ?>

                <div class="em-site-templates-admin__inline-field" data-em-site-template-inline-field="color">

                    <span

                        class="em-site-templates-admin__color-swatch"

                        style="--em-template-swatch: <?php echo esc_attr($color); ?>;"

                        aria-hidden="true"

                    ></span>

                    <code class="em-site-templates-admin__color-hex"><?php echo esc_html($color); ?></code>

                    <button

                        type="button"

                        class="em-site-catalog-sommaire__edit em-site-templates-admin__inline-edit"

                        data-em-site-color-modal-open

                        data-em-site-color-modal-target="<?php echo esc_attr($color_value_id); ?>"

                        data-em-site-color-modal-form="<?php echo esc_attr($color_form_id); ?>"

                        data-em-site-color-modal-value-name="em_site_template_color"

                        data-em-site-color-modal-label="<?php echo esc_attr($label); ?>"

                        data-em-site-color-modal-title="<?php echo esc_attr(__('Couleur du template', 'em-site')); ?>"

                        data-em-site-color-modal-default="<?php echo esc_attr(em_site_template_default_color_for_slug($slug)); ?>"

                        title="<?php esc_attr_e('Modifier la couleur', 'em-site'); ?>"

                        aria-label="<?php echo esc_attr(sprintf(__('Modifier la couleur de %s', 'em-site'), $label)); ?>"

                    >

                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>

                    </button>

                    <form

                        id="<?php echo esc_attr($color_form_id); ?>"

                        method="post"

                        class="em-site-templates-admin__color-form"

                        hidden

                    >

                        <?php wp_nonce_field('em_site_template_set_color'); ?>

                        <input type="hidden" name="em_site_template_action" value="set_color">

                        <input type="hidden" name="em_site_template_slug" value="<?php echo esc_attr($slug); ?>">

                        <input type="hidden" name="em_site_template_color" id="<?php echo esc_attr($color_value_id); ?>" value="<?php echo esc_attr($color); ?>">

                    </form>

                </div>

            <?php } else { ?>

                <span class="em-site-templates-admin__color-swatch" style="--em-template-swatch: <?php echo esc_attr($color); ?>;" title="<?php echo esc_attr($color); ?>"></span>

            <?php } ?>

        </td>

        <td class="em-site-templates-admin__slug"><code><?php echo esc_html($slug); ?></code></td>

        <td class="em-site-templates-admin__live-col">

            <?php if ($is_active) { ?>

                <?php em_site_admin_hub_render_template_active_pill($label, $slug); ?>

            <?php } elseif ($can_manage) { ?>

                <?php em_site_admin_hub_render_template_activate_badge($slug, $label, $color, true, true); ?>

            <?php } else { ?>

                <span class="em-site-templates-admin__status-empty" aria-hidden="true">—</span>

            <?php } ?>

        </td>

        <?php if ($can_manage) { ?>

            <td class="em-site-catalog-sommaire__actions">

                <div class="em-site-templates-admin__row-actions">

                    <?php if ($preview_url !== '') { ?>

                        <button

                            type="button"

                            class="em-site-catalog-sommaire__edit em-site-templates-admin__template-preview"

                            data-em-site-template-preview-url="<?php echo esc_url($preview_url); ?>"

                            data-em-site-template-preview-label="<?php echo esc_attr($label); ?>"

                            title="<?php esc_attr_e('Prévisualiser le template', 'em-site'); ?>"

                            aria-label="<?php echo esc_attr(sprintf(__('Prévisualiser %s', 'em-site'), $label)); ?>"

                        >

                            <i class="fa-solid fa-eye" aria-hidden="true"></i>

                        </button>

                    <?php } ?>

                    <a

                        class="em-site-catalog-sommaire__edit em-site-templates-admin__template-edit"

                        href="<?php echo esc_url($entry_url); ?>"

                        title="<?php esc_attr_e('Éditer le template', 'em-site'); ?>"

                        aria-label="<?php echo esc_attr(sprintf(__('Éditer %s', 'em-site'), $label)); ?>"

                    >

                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>

                    </a>

                    <?php if (!$is_active && $registry_count > 1 && !em_site_template_is_default($slug)) { ?>

                        <form method="post" class="em-site-templates-admin__delete-form" data-delete-label="<?php echo esc_attr($label); ?>">

                            <?php wp_nonce_field('em_site_template_delete'); ?>

                            <input type="hidden" name="em_site_template_action" value="delete">

                            <input type="hidden" name="em_site_template_slug" value="<?php echo esc_attr($slug); ?>">

                            <button type="submit" class="em-site-catalog-sommaire__delete" title="<?php esc_attr_e('Supprimer', 'em-site'); ?>" aria-label="<?php echo esc_attr(sprintf(__('Supprimer %s', 'em-site'), $label)); ?>">

                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>

                            </button>

                        </form>

                    <?php } ?>

                </div>

            </td>

        <?php } ?>

    </tr>

    <?php

}



