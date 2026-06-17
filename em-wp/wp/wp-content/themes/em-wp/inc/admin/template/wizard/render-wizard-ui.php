<?php
/**
 * UI wizard création template (modale ou page pleine).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Affiche le wizard (inline page ou modale legacy).
 *
 * @param bool $page_mode    true = page dédiée sans overlay.
 * @param bool $compact_mode true = masque l'en-tête interne (reprise brouillon).
 */
function em_wp_admin_template_wizard_render_ui(bool $page_mode = false, bool $compact_mode = false): void
{
    $classes = 'em-wp-template-wizard';

    if ($page_mode) {
        $classes .= ' em-wp-template-wizard--page';
    }

    if ($compact_mode) {
        $classes .= ' em-wp-template-wizard--compact';
    }

    $dialog_attrs = $page_mode
        ? ''
        : ' role="dialog" aria-modal="true" aria-labelledby="em-wp-template-wizard-title"';

    $initially_hidden = !$page_mode || $compact_mode;
    ?>
    <div
        id="em-wp-template-create-wizard"
        class="<?php echo esc_attr($classes); ?>"
        <?php echo $initially_hidden ? 'hidden' : ''; ?>
        aria-hidden="<?php echo $initially_hidden ? 'true' : 'false'; ?>"
        <?php echo $page_mode ? ' data-wizard-page-mode="1"' : ''; ?>
    >
        <?php if (!$page_mode) { ?>
            <button
                type="button"
                class="em-wp-template-wizard__backdrop"
                data-wizard-close
                tabindex="-1"
                aria-label="<?php esc_attr_e('Fermer', 'em-wp'); ?>"
            ></button>
        <?php } ?>

        <div class="em-wp-template-wizard__dialog"<?php echo $dialog_attrs !== '' ? ' ' . trim($dialog_attrs) : ''; ?>>
            <header class="em-wp-template-wizard__head"<?php echo $compact_mode ? ' hidden' : ''; ?>>
                <h2 class="em-wp-template-wizard__title" id="em-wp-template-wizard-title">
                    <?php esc_html_e('Assistant de création', 'em-wp'); ?>
                </h2>
                <ol class="em-wp-template-wizard__steps">
                    <li class="em-wp-template-wizard__step is-done" data-wizard-step-indicator="0">
                        <?php esc_html_e('Identité', 'em-wp'); ?>
                    </li>
                    <li class="em-wp-template-wizard__step" data-wizard-step-indicator="1">
                        <?php esc_html_e('Plan', 'em-wp'); ?>
                    </li>
                </ol>
            </header>

            <div class="em-wp-template-wizard__body">
                <div
                    class="em-wp-template-wizard__panel em-wp-template-wizard__panel--plan"
                    data-wizard-panel="1"
                    hidden
                >
                    <p class="em-wp-template-wizard__helper">
                        <?php esc_html_e('Compose la structure de ta page directement sur le wireframe : réordonne les rubriques, retire celles dont tu n’as pas besoin, ou ajoute-en depuis la colonne de droite.', 'em-wp'); ?>
                    </p>

                    <div class="em-wp-template-wizard__identity-recap">
                        <span data-wizard-recap-name>—</span>
                        <span
                            class="em-wp-template-wizard__recap-swatch"
                            data-wizard-recap-swatch
                            aria-hidden="true"
                        ></span>
                    </div>

                    <div class="em-wp-template-wizard-plan">
                        <div class="em-wp-template-wizard-plan__wireframe">
                            <div id="em-wp-template-wizard-wireframe-host" class="em-wp-template-wizard-wireframe-host"></div>
                        </div>
                        <aside class="em-wp-template-wizard-plan__aside">
                            <div class="em-wp-template-wizard-skeleton__add" data-wizard-plan-add>
                                <p class="em-wp-template-wizard-skeleton__picker-heading" id="em-wp-template-wizard-plan-picker-heading">
                                    <?php esc_html_e('Rubriques disponibles', 'em-wp'); ?>
                                </p>
                                <p class="em-wp-template-wizard-skeleton__picker-heading-hint" id="em-wp-template-wizard-plan-picker-heading-hint">
                                    <?php esc_html_e('(Clique ou glisse une rubrique sur le wireframe)', 'em-wp'); ?>
                                </p>
                                <div class="em-wp-template-wizard-skeleton__picker-row">
                                    <ul
                                        class="em-wp-template-wizard-skeleton__picker-list"
                                        id="em-wp-template-wizard-plan-picker-list"
                                        data-wizard-plan-picker-list
                                        aria-labelledby="em-wp-template-wizard-plan-picker-heading"
                                    ></ul>
                                </div>
                                <p class="em-wp-template-wizard-skeleton__picker-empty" data-wizard-plan-picker-empty hidden>
                                    <?php esc_html_e('Toutes les rubriques disponibles sont déjà ajoutées.', 'em-wp'); ?>
                                </p>
                            </div>
                        </aside>
                    </div>

                    <div class="em-wp-template-wizard__panel-actions" data-wizard-wireframe-actions hidden>
                        <button
                            type="button"
                            class="em-wp-hub__action em-wp-hub__action--compact"
                            data-wizard-submit
                        >
                            <span class="em-wp-hub__action-inner">
                                <i class="fa-solid fa-check" aria-hidden="true"></i>
                                <span class="em-wp-hub__action-label"><?php esc_html_e('Valider le template', 'em-wp'); ?></span>
                            </span>
                        </button>
                    </div>

                    <p class="em-wp-template-wizard__error" id="em-wp-template-wizard-plan-error" hidden></p>
                </div>
            </div>

            <footer class="em-wp-template-wizard__actions">
                <button
                    type="button"
                    class="em-wp-hub__action em-wp-hub__action--compact em-wp-hub__action--outline"
                    data-wizard-prev
                    hidden
                >
                    <span class="em-wp-hub__action-inner">
                        <span class="em-wp-hub__action-label"><?php esc_html_e('Retour', 'em-wp'); ?></span>
                    </span>
                </button>
                <?php if (!$page_mode) { ?>
                    <button
                        type="button"
                        class="em-wp-hub__action em-wp-hub__action--compact em-wp-hub__action--outline"
                        data-wizard-close
                    >
                        <span class="em-wp-hub__action-inner">
                            <span class="em-wp-hub__action-label"><?php esc_html_e('Annuler', 'em-wp'); ?></span>
                        </span>
                    </button>
                <?php } ?>
                <button
                    type="button"
                    class="em-wp-hub__action em-wp-hub__action--compact"
                    data-wizard-next
                >
                    <span class="em-wp-hub__action-inner">
                        <span class="em-wp-hub__action-label"><?php esc_html_e('Suivant', 'em-wp'); ?></span>
                    </span>
                </button>
            </footer>
        </div>
    </div>
    <?php
}
