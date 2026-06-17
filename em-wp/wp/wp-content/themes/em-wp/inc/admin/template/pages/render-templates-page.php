<?php

/**

 * Rendu page admin Templates (tableau enregistrés, réutilisable).

 *

 * @package em-wp

 */



if (!defined('ABSPATH')) {

    exit;

}



/**

 * Redirige l'ancienne page CRUD masquée vers Mes Templates.

 */

function em_wp_admin_render_templates_page(): void

{

    if (!current_user_can('manage_options')) {

        return;

    }



    $redirect = function_exists('em_wp_admin_templates_manage_admin_url')

        ? em_wp_admin_templates_manage_admin_url()

        : em_wp_admin_template_choice_admin_url();



    em_wp_admin_safe_redirect($redirect);

}



/**

 * Modale couleur template (une seule instance, hors tableau).

 */

function em_wp_admin_render_templates_color_modal(): void

{

    ?>

    <div

        id="em-wp-templates-admin-color-modal"

        class="em-wp-templates-admin-color-modal"

        hidden

        aria-hidden="true"

    >

        <div class="em-wp-templates-admin-color-modal__backdrop" data-em-wp-template-color-dismiss></div>

        <div

            class="em-wp-templates-admin-color-modal__dialog"

            role="dialog"

            aria-modal="true"

            aria-labelledby="em-wp-templates-admin-color-modal-title"

        >

            <header class="em-wp-templates-admin-color-modal__head">

                <h2 id="em-wp-templates-admin-color-modal-title" class="em-wp-templates-admin-color-modal__title">

                    <?php esc_html_e('Couleur du template', 'em-wp'); ?>

                </h2>

            </header>

            <div class="em-wp-templates-admin-color-modal__body">

                <div class="em-wp-templates-admin-color-modal__preview-wrap">

                    <span

                        id="em-wp-templates-admin-color-modal-preview"

                        class="em-wp-templates-admin-color-modal__preview"

                        aria-hidden="true"

                    ></span>

                    <p id="em-wp-templates-admin-color-modal-label" class="em-wp-templates-admin-color-modal__template-name"></p>

                </div>

                <div class="em-wp-admin-color-field-wrap em-wp-templates-admin-color-modal__picker-wrap">

                    <div class="em-wp-admin-color-control">

                        <label class="em-wp-admin-color-label" for="em-wp-templates-admin-color-modal-input">

                            <?php esc_html_e('Couleur', 'em-wp'); ?>

                        </label>

                        <input

                            type="text"

                            id="em-wp-templates-admin-color-modal-input"

                            class="em-wp-templates-admin-color-modal__input"

                            value=""

                            autocomplete="off"

                        >

                    </div>

                </div>

            </div>

            <footer class="em-wp-templates-admin-color-modal__actions">

                <button type="button" class="button button-secondary" data-em-wp-template-color-dismiss>

                    <?php esc_html_e('Annuler', 'em-wp'); ?>

                </button>

                <button type="button" class="button button-primary" id="em-wp-templates-admin-color-modal-save">

                    <?php esc_html_e('Enregistrer', 'em-wp'); ?>

                </button>

            </footer>

        </div>

    </div>

    <?php

}



/**

 * Tableau des templates enregistrés.

 *

 * @param array<string, array<string, mixed>> $registry

 */

function em_wp_admin_render_templates_registered_table(

    array $registry,

    string $active_slug,

    bool $can_manage,

    string $registered_title_id

): void {

    ?>

    <section class="em-wp-catalog-sommaire__section" aria-labelledby="<?php echo esc_attr($registered_title_id); ?>">

        <header class="em-wp-catalog-sommaire__section-header">

            <div id="<?php echo esc_attr($registered_title_id); ?>" class="em-wp-catalog-sommaire__section-title">

                <?php em_wp_admin_hub_render_card_title(__('TEMPLATES ENREGISTRÉS', 'em-wp'), 'dashicons-layout'); ?>

            </div>

        </header>



        <div class="em-wp-catalog-sommaire__section-body">

            <table class="widefat striped em-wp-catalog-sommaire__table em-wp-templates-admin__table">

                <thead>

                    <tr>

                        <th scope="col"><?php esc_html_e('Nom', 'em-wp'); ?></th>

                        <th scope="col"><?php esc_html_e('Couleur', 'em-wp'); ?></th>

                        <th scope="col"><?php esc_html_e('Identifiant', 'em-wp'); ?></th>

                        <th scope="col"><?php esc_html_e('Actif sur le site', 'em-wp'); ?></th>

                        <?php if ($can_manage) { ?>

                            <th scope="col" class="em-wp-templates-admin__edit-col">

                                <span class="screen-reader-text"><?php esc_html_e('Édition', 'em-wp'); ?></span>

                            </th>

                            <th scope="col" class="em-wp-catalog-sommaire__actions-col">

                                <span class="screen-reader-text"><?php esc_html_e('Actions', 'em-wp'); ?></span>

                            </th>

                        <?php } ?>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($registry as $slug => $definition) {

                        em_wp_admin_render_templates_registered_row(

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



        <?php if ($can_manage) {

            em_wp_admin_render_templates_color_modal();

        } ?>

    </section>

    <?php

}



/**

 * @param array<string, mixed> $definition

 */

function em_wp_admin_render_templates_registered_row(

    string $slug,

    array $definition,

    string $active_slug,

    bool $can_manage,

    int $registry_count

): void {

    $label = (string) ($definition['label'] ?? $slug);

    $color = em_wp_get_template_color($slug);

    $is_active = ($slug === $active_slug);

    $entry_url = add_query_arg(

        ['page' => em_wp_admin_template_entry_page_slug($slug)],

        admin_url('admin.php')

    );

    $rename_form_id = 'em-wp-template-rename-' . $slug;

    $color_form_id = 'em-wp-template-color-' . $slug;

    ?>

    <tr>

        <td class="em-wp-catalog-sommaire__name">

            <?php if ($can_manage) { ?>

                <div class="em-wp-templates-admin__inline-field" data-em-wp-template-inline-field="name">

                    <button

                        type="button"

                        class="em-wp-catalog-sommaire__edit em-wp-templates-admin__inline-edit"

                        data-em-wp-template-inline-edit="name"

                        data-em-wp-template-form="<?php echo esc_attr($rename_form_id); ?>"

                        title="<?php esc_attr_e('Modifier le nom', 'em-wp'); ?>"

                        aria-label="<?php echo esc_attr(sprintf(__('Modifier le nom de %s', 'em-wp'), $label)); ?>"

                    >

                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>

                    </button>

                    <span class="em-wp-templates-admin__inline-value"><?php echo esc_html($label); ?></span>

                    <form

                        id="<?php echo esc_attr($rename_form_id); ?>"

                        method="post"

                        class="em-wp-templates-admin__rename-form"

                        hidden

                    >

                        <?php wp_nonce_field('em_wp_template_rename'); ?>

                        <input type="hidden" name="em_wp_template_action" value="rename">

                        <input type="hidden" name="em_wp_template_slug" value="<?php echo esc_attr($slug); ?>">

                        <input

                            type="text"

                            name="em_wp_template_label"

                            value="<?php echo esc_attr($label); ?>"

                            class="regular-text em-wp-catalog-sommaire__label-input em-wp-templates-admin__inline-input"

                            required

                            autocomplete="off"

                        >

                        <button

                            type="submit"

                            class="em-wp-catalog-sommaire__save em-wp-templates-admin__inline-save"

                            title="<?php esc_attr_e('Enregistrer le nom', 'em-wp'); ?>"

                            aria-label="<?php echo esc_attr(sprintf(__('Enregistrer le nom de %s', 'em-wp'), $label)); ?>"

                        >

                            <i class="fa-solid fa-check" aria-hidden="true"></i>

                        </button>

                        <button

                            type="button"

                            class="em-wp-templates-admin__inline-cancel"

                            data-em-wp-template-inline-cancel="name"

                            title="<?php esc_attr_e('Annuler', 'em-wp'); ?>"

                            aria-label="<?php esc_attr_e('Annuler la modification du nom', 'em-wp'); ?>"

                        >

                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>

                        </button>

                    </form>

                </div>

            <?php } else { ?>

                <strong><?php echo esc_html($label); ?></strong>

            <?php } ?>

        </td>

        <td>

            <?php if ($can_manage) { ?>

                <div class="em-wp-templates-admin__inline-field" data-em-wp-template-inline-field="color">

                    <span

                        class="em-wp-templates-admin__color-swatch"

                        style="--em-template-swatch: <?php echo esc_attr($color); ?>;"

                        aria-hidden="true"

                    ></span>

                    <code class="em-wp-templates-admin__color-hex"><?php echo esc_html($color); ?></code>

                    <button

                        type="button"

                        class="em-wp-catalog-sommaire__edit em-wp-templates-admin__inline-edit"

                        data-em-wp-template-inline-edit="color"

                        data-em-wp-template-form="<?php echo esc_attr($color_form_id); ?>"

                        data-em-wp-template-label="<?php echo esc_attr($label); ?>"

                        data-em-wp-template-color="<?php echo esc_attr($color); ?>"

                        data-em-wp-template-default-color="<?php echo esc_attr(em_wp_template_default_color_for_slug($slug)); ?>"

                        title="<?php esc_attr_e('Modifier la couleur', 'em-wp'); ?>"

                        aria-label="<?php echo esc_attr(sprintf(__('Modifier la couleur de %s', 'em-wp'), $label)); ?>"

                    >

                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>

                    </button>

                    <form

                        id="<?php echo esc_attr($color_form_id); ?>"

                        method="post"

                        class="em-wp-templates-admin__color-form"

                        hidden

                    >

                        <?php wp_nonce_field('em_wp_template_set_color'); ?>

                        <input type="hidden" name="em_wp_template_action" value="set_color">

                        <input type="hidden" name="em_wp_template_slug" value="<?php echo esc_attr($slug); ?>">

                        <input type="hidden" name="em_wp_template_color" value="<?php echo esc_attr($color); ?>">

                    </form>

                </div>

            <?php } else { ?>

                <span class="em-wp-templates-admin__color-swatch" style="--em-template-swatch: <?php echo esc_attr($color); ?>;" title="<?php echo esc_attr($color); ?>"></span>

            <?php } ?>

        </td>

        <td class="em-wp-templates-admin__slug"><code><?php echo esc_html($slug); ?></code></td>

        <td class="em-wp-templates-admin__live-col">

            <?php if ($is_active) { ?>

                <span
                    class="em-wp-templates-admin__badge em-wp-templates-admin__badge--live"
                    style="--em-template-accent: <?php echo esc_attr($color); ?>;"
                ><?php esc_html_e('LIVE', 'em-wp'); ?></span>

            <?php } elseif ($can_manage) { ?>

                <?php em_wp_admin_hub_render_template_activate_badge($slug, $label, $color, true, true); ?>

            <?php } else { ?>

                <span class="em-wp-templates-admin__status-empty" aria-hidden="true">—</span>

            <?php } ?>

        </td>

        <?php if ($can_manage) { ?>

            <td class="em-wp-templates-admin__edit-col">

                <a

                    class="em-wp-catalog-sommaire__edit em-wp-templates-admin__template-edit"

                    href="<?php echo esc_url($entry_url); ?>"

                    title="<?php esc_attr_e('Éditer le template', 'em-wp'); ?>"

                    aria-label="<?php echo esc_attr(sprintf(__('Éditer %s', 'em-wp'), $label)); ?>"

                >

                    <i class="fa-solid fa-gear" aria-hidden="true"></i>

                </a>

            </td>

            <td class="em-wp-catalog-sommaire__actions">

                <div class="em-wp-templates-admin__row-actions">

                    <?php if (!$is_active && $registry_count > 1) { ?>

                        <form method="post" class="em-wp-templates-admin__delete-form" data-delete-label="<?php echo esc_attr($label); ?>">

                            <?php wp_nonce_field('em_wp_template_delete'); ?>

                            <input type="hidden" name="em_wp_template_action" value="delete">

                            <input type="hidden" name="em_wp_template_slug" value="<?php echo esc_attr($slug); ?>">

                            <button type="submit" class="em-wp-catalog-sommaire__delete" title="<?php esc_attr_e('Supprimer', 'em-wp'); ?>" aria-label="<?php echo esc_attr(sprintf(__('Supprimer %s', 'em-wp'), $label)); ?>">

                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>

                            </button>

                        </form>

                    <?php } else { ?>

                        <span class="em-wp-templates-admin__status-empty" aria-hidden="true">—</span>

                    <?php } ?>

                </div>

            </td>

        <?php } ?>

    </tr>

    <?php

}



