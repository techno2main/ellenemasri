<?php
/**
 * Modales « Nouveau template » — choix vierge ou duplication.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enfile les assets du lanceur « Nouveau template ».
 */
function em_wp_admin_template_enqueue_new_template_launcher(): void
{
    wp_enqueue_style(
        'em-wp-admin-new-template-modal',
        get_template_directory_uri() . '/assets/admin/css/template/new-template-modal.css',
        ['em-wp-admin-hub-cards'],
        em_wp_admin_asset_version('assets/admin/css/template/new-template-modal.css')
    );

    wp_enqueue_script(
        'em-wp-admin-new-template-launcher',
        get_template_directory_uri() . '/assets/admin/js/template/new-template-launcher.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker'],
        em_wp_admin_asset_version('assets/admin/js/template/new-template-launcher.js'),
        true
    );

    $blank_url = function_exists('em_wp_admin_template_create_workspace_admin_url')
        ? em_wp_admin_template_create_workspace_admin_url()
        : '';

    wp_localize_script(
        'em-wp-admin-new-template-launcher',
        'emWpNewTemplateLauncher',
        [
            'blankWizardUrl'          => $blank_url,
            'workspaceLaunchGrantKey' => 'em_wp_wizard_workspace_launch',
            'i18n'                    => [
                'nameRequired'  => __('Le nom du template est requis.', 'em-wp'),
                'colorRequired' => __('La couleur du template est requise.', 'em-wp'),
                'sourceRequired'=> __('Choisissez le template à dupliquer.', 'em-wp'),
            ],
        ]
    );
}

/**
 * Modale unique — panneau choix + panneau duplication.
 */
function em_wp_admin_render_new_template_modals(): void
{
    if (!em_wp_admin_can_manage_templates()) {
        return;
    }

    $registry = em_wp_template_registry();
    $can_duplicate = ($registry !== []);
    ?>
    <div
        id="em-wp-new-template-modal"
        class="em-wp-new-template-modal"
        hidden
        aria-hidden="true"
    >
        <div class="em-wp-new-template-modal__backdrop" data-em-wp-new-template-dismiss></div>

        <div
            class="em-wp-new-template-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="em-wp-new-template-modal-title"
        >
            <div class="em-wp-new-template-modal__panel" data-new-template-panel="choice">
                <header class="em-wp-new-template-modal__head">
                    <h2 id="em-wp-new-template-modal-title" class="em-wp-new-template-modal__title">
                        <?php esc_html_e('Nouveau template', 'em-wp'); ?>
                    </h2>
                    <p class="em-wp-new-template-modal__lead">
                        <?php esc_html_e('Choisissez comment créer votre template. Vous pourrez ensuite le personnaliser rubrique par rubrique.', 'em-wp'); ?>
                    </p>
                </header>

                <div class="em-wp-new-template-modal__choices" role="list">
                    <button
                        type="button"
                        class="em-wp-new-template-modal__choice"
                        data-em-wp-new-template-blank
                        role="listitem"
                    >
                        <span class="em-wp-new-template-modal__choice-icon" aria-hidden="true">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </span>
                        <span class="em-wp-new-template-modal__choice-body">
                            <strong class="em-wp-new-template-modal__choice-title">
                                <?php esc_html_e('Modèle vierge', 'em-wp'); ?>
                            </strong>
                            <span class="em-wp-new-template-modal__choice-desc">
                                <?php esc_html_e('Lance l’assistant en 2 étapes : identité (nom, couleur) puis plan des rubriques. Idéal pour repartir de zéro.', 'em-wp'); ?>
                            </span>
                        </span>
                    </button>

                    <?php if ($can_duplicate) { ?>
                        <button
                            type="button"
                            class="em-wp-new-template-modal__choice"
                            data-em-wp-new-template-show-duplicate
                            role="listitem"
                        >
                            <span class="em-wp-new-template-modal__choice-icon" aria-hidden="true">
                                <i class="fa-solid fa-copy"></i>
                            </span>
                            <span class="em-wp-new-template-modal__choice-body">
                                <strong class="em-wp-new-template-modal__choice-title">
                                    <?php esc_html_e('Dupliquer un template', 'em-wp'); ?>
                                </strong>
                                <span class="em-wp-new-template-modal__choice-desc">
                                    <?php esc_html_e('Copie la structure et les réglages d’un template existant. Seuls le nom et la couleur changent.', 'em-wp'); ?>
                                </span>
                            </span>
                        </button>
                    <?php } else { ?>
                        <p class="em-wp-new-template-modal__choice-note">
                            <?php esc_html_e('La duplication sera disponible dès qu’au moins un template est enregistré.', 'em-wp'); ?>
                        </p>
                    <?php } ?>
                </div>

                <footer class="em-wp-new-template-modal__actions">
                    <button type="button" class="button button-secondary" data-em-wp-new-template-dismiss>
                        <?php esc_html_e('Annuler', 'em-wp'); ?>
                    </button>
                </footer>
            </div>

            <?php if ($can_duplicate) { ?>
                <div class="em-wp-new-template-modal__panel" data-new-template-panel="duplicate" hidden>
                    <header class="em-wp-new-template-modal__head">
                        <button
                            type="button"
                            class="em-wp-new-template-modal__back"
                            data-em-wp-new-template-show-choice
                        >
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            <?php esc_html_e('Retour', 'em-wp'); ?>
                        </button>
                        <h2 class="em-wp-new-template-modal__title">
                            <?php esc_html_e('Dupliquer un template', 'em-wp'); ?>
                        </h2>
                    </header>

                    <div class="em-wp-new-template-modal__notice">
                        <p>
                            <?php esc_html_e('Le template source sert de modèle : plan des rubriques, ordre des zones, réglages du catalogue (modules, options, visibilité) sont reproduits à l’identique.', 'em-wp'); ?>
                        </p>
                        <p>
                            <?php esc_html_e('Seuls le nom d’affichage, la couleur et l’identifiant technique (généré automatiquement) seront différents.', 'em-wp'); ?>
                        </p>
                    </div>

                    <form
                        method="post"
                        class="em-wp-new-template-modal__form"
                        id="em-wp-new-template-duplicate-form"
                    >
                        <?php wp_nonce_field('em_wp_template_duplicate'); ?>
                        <input type="hidden" name="em_wp_template_action" value="duplicate">

                        <p class="em-wp-new-template-modal__field">
                            <label class="em-wp-new-template-modal__label" for="em-wp-new-template-source">
                                <?php esc_html_e('Template à dupliquer', 'em-wp'); ?>
                            </label>
                            <select
                                id="em-wp-new-template-source"
                                name="em_wp_template_source_slug"
                                class="em-wp-new-template-modal__select"
                                required
                            >
                                <option value=""><?php esc_html_e('— Choisir —', 'em-wp'); ?></option>
                                <?php foreach ($registry as $slug => $definition) {
                                    $item_label = (string) ($definition['label'] ?? $slug);
                                    $item_color = em_wp_get_template_color((string) $slug);
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

                        <p class="em-wp-new-template-modal__field">
                            <label class="em-wp-new-template-modal__label" for="em-wp-new-template-label">
                                <?php esc_html_e('Nom du nouveau template', 'em-wp'); ?>
                            </label>
                            <input
                                type="text"
                                id="em-wp-new-template-label"
                                name="em_wp_template_label"
                                class="regular-text em-wp-new-template-modal__input"
                                required
                                autocomplete="off"
                                placeholder="<?php esc_attr_e('Ex. Variante été', 'em-wp'); ?>"
                            >
                        </p>

                        <div class="em-wp-new-template-modal__field em-wp-new-template-modal__field--color">
                            <div class="em-wp-admin-color-field-wrap">
                                <label class="em-wp-admin-color-label" for="em-wp-new-template-color">
                                    <?php esc_html_e('Couleur', 'em-wp'); ?>
                                </label>
                                <input
                                    type="text"
                                    id="em-wp-new-template-color"
                                    name="em_wp_template_color"
                                    class="em-wp-admin-color-field em-wp-new-template-modal__color-input"
                                    value=""
                                    required
                                    data-default-color=""
                                >
                            </div>
                        </div>

                        <footer class="em-wp-new-template-modal__actions">
                            <button type="button" class="button button-secondary" data-em-wp-new-template-dismiss>
                                <?php esc_html_e('Annuler', 'em-wp'); ?>
                            </button>
                            <button type="submit" class="button button-primary">
                                <?php esc_html_e('Créer la copie', 'em-wp'); ?>
                            </button>
                        </footer>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
}
