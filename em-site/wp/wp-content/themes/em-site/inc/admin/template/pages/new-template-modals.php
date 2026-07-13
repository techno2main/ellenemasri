<?php
/**
 * Modales « Nouveau template » — choix vierge ou duplication.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enfile les assets du lanceur « Nouveau template ».
 */
function em_site_admin_template_enqueue_new_template_launcher(): void
{
    wp_enqueue_style(
        'em-site-admin-new-template-modal',
        get_template_directory_uri() . '/assets/admin/css/template/new-template-modal.css',
        ['em-site-admin-hub-cards'],
        em_site_admin_asset_version('assets/admin/css/template/new-template-modal.css')
    );

    wp_enqueue_script(
        'em-site-admin-new-template-launcher',
        get_template_directory_uri() . '/assets/admin/js/template/new-template-launcher.js',
        ['jquery', 'wp-color-picker', 'em-site-admin-color-picker', 'em-site-admin-color-modal'],
        em_site_admin_asset_version('assets/admin/js/template/new-template-launcher.js'),
        true
    );

    $blank_url = function_exists('em_site_admin_template_create_workspace_admin_url')
        ? em_site_admin_template_create_workspace_admin_url()
        : '';

    wp_localize_script(
        'em-site-admin-new-template-launcher',
        'emWpNewTemplateLauncher',
        [
            'blankWizardUrl'          => $blank_url,
            'workspaceLaunchGrantKey' => 'em_site_wizard_workspace_launch',
            'i18n'                    => [
                'nameRequired'  => __('Le nom du template est requis.', 'em-site'),
                'colorRequired' => __('La couleur du template est requise.', 'em-site'),
                'sourceRequired'=> __('Choisissez le template à dupliquer.', 'em-site'),
            ],
        ]
    );
}

/**
 * Modale unique — panneau choix + panneau duplication.
 */
function em_site_admin_render_new_template_modals(): void
{
    if (!em_site_admin_can_manage_templates()) {
        return;
    }

    $registry = em_site_template_registry();
    $can_duplicate = ($registry !== []);
    ?>
    <div
        id="em-site-new-template-modal"
        class="em-site-new-template-modal"
        hidden
        aria-hidden="true"
    >
        <div class="em-site-new-template-modal__backdrop" data-em-site-new-template-dismiss></div>

        <div
            class="em-site-new-template-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="em-site-new-template-modal-title"
        >
            <div class="em-site-new-template-modal__panel" data-new-template-panel="choice">
                <header class="em-site-new-template-modal__head">
                    <h2 id="em-site-new-template-modal-title" class="em-site-new-template-modal__title">
                        <?php esc_html_e('Nouveau template', 'em-site'); ?>
                    </h2>
                    <p class="em-site-new-template-modal__lead">
                        <?php esc_html_e('Choisis comment tu veux créer ton template.', 'em-site'); ?>
                    </p>
                </header>

                <div class="em-site-new-template-modal__choices" role="list">
                    <button
                        type="button"
                        class="em-site-new-template-modal__choice"
                        data-em-site-new-template-blank
                        role="listitem"
                    >
                        <span class="em-site-new-template-modal__choice-icon" aria-hidden="true">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </span>
                        <span class="em-site-new-template-modal__choice-body">
                            <strong class="em-site-new-template-modal__choice-title">
                                <?php esc_html_e('Modèle vierge', 'em-site'); ?>
                            </strong>
                            <span class="em-site-new-template-modal__choice-desc">
                                <?php esc_html_e('Lance l’assistant en 2 étapes : identité (nom, couleur) puis plan des rubriques. Idéal pour repartir de zéro.', 'em-site'); ?>
                            </span>
                        </span>
                    </button>

                    <?php if ($can_duplicate) { ?>
                        <button
                            type="button"
                            class="em-site-new-template-modal__choice"
                            data-em-site-new-template-show-duplicate
                            role="listitem"
                        >
                            <span class="em-site-new-template-modal__choice-icon" aria-hidden="true">
                                <i class="fa-solid fa-copy"></i>
                            </span>
                            <span class="em-site-new-template-modal__choice-body">
                                <strong class="em-site-new-template-modal__choice-title">
                                    <?php esc_html_e('Dupliquer un template', 'em-site'); ?>
                                </strong>
                                <span class="em-site-new-template-modal__choice-desc">
                                    <?php esc_html_e('Copie la structure et les réglages d’un template existant. Seuls le nom et la couleur changent.', 'em-site'); ?>
                                </span>
                            </span>
                        </button>
                    <?php } else { ?>
                        <p class="em-site-new-template-modal__choice-note">
                            <?php esc_html_e('La duplication sera disponible dès qu’au moins un template est enregistré.', 'em-site'); ?>
                        </p>
                    <?php } ?>
                </div>

                <footer class="em-site-new-template-modal__actions">
                    <button type="button" class="button button-secondary" data-em-site-new-template-dismiss>
                        <?php esc_html_e('Annuler', 'em-site'); ?>
                    </button>
                </footer>
            </div>

            <?php if ($can_duplicate) { ?>
                <div class="em-site-new-template-modal__panel" data-new-template-panel="duplicate" hidden>
                    <header class="em-site-new-template-modal__head">
                        <button
                            type="button"
                            class="em-site-new-template-modal__back"
                            data-em-site-new-template-show-choice
                        >
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            <?php esc_html_e('Retour', 'em-site'); ?>
                        </button>
                        <h2 class="em-site-new-template-modal__title">
                            <?php esc_html_e('Dupliquer un template', 'em-site'); ?>
                        </h2>
                    </header>

                    <div class="em-site-new-template-modal__notice">
                        <p>
                            <?php esc_html_e('Le template source sert de modèle : plan des rubriques, ordre des zones, réglages du catalogue (modules, options, visibilité) sont reproduits à l’identique.', 'em-site'); ?>
                        </p>
                        <p>
                            <?php esc_html_e('Seuls le nom d’affichage, la couleur et l’identifiant technique (généré automatiquement) seront différents.', 'em-site'); ?>
                        </p>
                    </div>

                    <form
                        method="post"
                        class="em-site-new-template-modal__form"
                        id="em-site-new-template-duplicate-form"
                    >
                        <?php wp_nonce_field('em_site_template_duplicate'); ?>
                        <input type="hidden" name="em_site_template_action" value="duplicate">

                        <p class="em-site-new-template-modal__field">
                            <label class="em-site-new-template-modal__label" for="em-site-new-template-source">
                                <?php esc_html_e('Template à dupliquer', 'em-site'); ?>
                            </label>
                            <select
                                id="em-site-new-template-source"
                                name="em_site_template_source_slug"
                                class="em-site-new-template-modal__select"
                                required
                            >
                                <option value=""><?php esc_html_e('— Choisir —', 'em-site'); ?></option>
                                <?php foreach ($registry as $slug => $definition) {
                                    $item_label = (string) ($definition['label'] ?? $slug);
                                    $item_color = em_site_get_template_color((string) $slug);
                                    ?>
                                    <option
                                        value="<?php echo esc_attr((string) $slug); ?>"
                                        data-template-color="<?php echo esc_attr($item_color); ?>"
                                    >
                                        <?php echo esc_html($item_label); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </p>

                        <p class="em-site-new-template-modal__field">
                            <label class="em-site-new-template-modal__label" for="em-site-new-template-label">
                                <?php esc_html_e('Nom du nouveau template', 'em-site'); ?>
                            </label>
                            <input
                                type="text"
                                id="em-site-new-template-label"
                                name="em_site_template_label"
                                class="regular-text em-site-new-template-modal__input"
                                required
                                autocomplete="off"
                                placeholder="<?php esc_attr_e('Ex. Variante été', 'em-site'); ?>"
                            >
                        </p>

                        <div class="em-site-new-template-modal__field em-site-new-template-modal__field--color">
                            <?php
                            em_site_admin_render_color_field([
                                'id'           => 'em-site-new-template-color',
                                'name'         => 'em_site_template_color',
                                'value'        => '',
                                'field_label'  => __('Couleur', 'em-site'),
                                'modal_title'  => __('Couleur du template', 'em-site'),
                                'input_class'  => 'em-site-new-template-modal__color-input',
                                'required'     => true,
                            ]);
                            ?>
                        </div>

                        <footer class="em-site-new-template-modal__actions">
                            <button type="button" class="button button-secondary" data-em-site-new-template-dismiss>
                                <?php esc_html_e('Annuler', 'em-site'); ?>
                            </button>
                            <button type="submit" class="button button-primary">
                                <?php esc_html_e('Créer la copie', 'em-site'); ?>
                            </button>
                        </footer>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
}
