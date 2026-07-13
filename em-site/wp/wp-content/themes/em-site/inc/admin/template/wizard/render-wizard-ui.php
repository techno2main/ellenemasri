<?php
/**
 * UI wizard création template (modale ou page pleine).
 *
 * @package em-site
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
function em_site_admin_template_wizard_render_ui(bool $page_mode = false, bool $compact_mode = false): void
{
    $classes = 'em-site-template-wizard';

    if ($page_mode) {
        $classes .= ' em-site-template-wizard--page';
    }

    if ($compact_mode) {
        $classes .= ' em-site-template-wizard--compact';
    }

    $dialog_attrs = $page_mode
        ? ''
        : ' role="dialog" aria-modal="true" aria-labelledby="em-site-template-wizard-title"';

    $initially_hidden = !$page_mode || $compact_mode;
    ?>
    <div
        id="em-site-template-create-wizard"
        class="<?php echo esc_attr($classes); ?>"
        <?php echo $initially_hidden ? 'hidden' : ''; ?>
        aria-hidden="<?php echo $initially_hidden ? 'true' : 'false'; ?>"
        <?php echo $page_mode ? ' data-wizard-page-mode="1"' : ''; ?>
    >
        <?php if (!$page_mode) { ?>
            <button
                type="button"
                class="em-site-template-wizard__backdrop"
                data-wizard-close
                tabindex="-1"
                aria-label="<?php esc_attr_e('Fermer', 'em-site'); ?>"
            ></button>
        <?php } ?>

        <div class="em-site-template-wizard__dialog"<?php echo $dialog_attrs !== '' ? ' ' . trim($dialog_attrs) : ''; ?>>
            <header class="em-site-template-wizard__head"<?php echo $compact_mode ? ' hidden' : ''; ?>>
                <h2 class="em-site-template-wizard__title" id="em-site-template-wizard-title">
                    <?php esc_html_e('Assistant de création', 'em-site'); ?>
                </h2>
                <ol class="em-site-template-wizard__steps">
                    <li class="em-site-template-wizard__step is-done" data-wizard-step-indicator="0">
                        <?php esc_html_e('Identité', 'em-site'); ?>
                    </li>
                    <li class="em-site-template-wizard__step" data-wizard-step-indicator="1">
                        <?php esc_html_e('Plan', 'em-site'); ?>
                    </li>
                </ol>
            </header>

            <div class="em-site-template-wizard__body">
                <div
                    class="em-site-template-wizard__panel em-site-template-wizard__panel--plan"
                    data-wizard-panel="1"
                    hidden
                >
                    <p class="em-site-template-wizard__helper">
                        <?php esc_html_e('Compose la structure de ta page directement sur le wireframe : réordonne les rubriques, retire celles dont tu n’as pas besoin, ou ajoute-en depuis la colonne de droite.', 'em-site'); ?>
                    </p>

                    <div class="em-site-template-wizard__identity-recap">
                        <span data-wizard-recap-name>—</span>
                        <span
                            class="em-site-template-wizard__recap-swatch"
                            data-wizard-recap-swatch
                            aria-hidden="true"
                        ></span>
                    </div>

                    <div class="em-site-template-wizard-plan">
                        <div class="em-site-template-wizard-plan__wireframe">
                            <div id="em-site-template-wizard-wireframe-host" class="em-site-template-wizard-wireframe-host"></div>
                        </div>
                        <aside class="em-site-template-wizard-plan__aside">
                            <div class="em-site-template-wizard-skeleton__add" data-wizard-plan-add>
                                <p class="em-site-template-wizard-skeleton__picker-heading" id="em-site-template-wizard-plan-picker-heading">
                                    <?php esc_html_e('Rubriques disponibles', 'em-site'); ?>
                                </p>
                                <p class="em-site-template-wizard-skeleton__picker-heading-hint" id="em-site-template-wizard-plan-picker-heading-hint">
                                    <?php esc_html_e('(Clique ou glisse une rubrique sur le wireframe)', 'em-site'); ?>
                                </p>
                                <div class="em-site-template-wizard-skeleton__picker-row">
                                    <ul
                                        class="em-site-template-wizard-skeleton__picker-list"
                                        id="em-site-template-wizard-plan-picker-list"
                                        data-wizard-plan-picker-list
                                        aria-labelledby="em-site-template-wizard-plan-picker-heading"
                                    ></ul>
                                </div>
                                <p class="em-site-template-wizard-skeleton__picker-empty" data-wizard-plan-picker-empty hidden>
                                    <?php esc_html_e('Toutes les rubriques disponibles sont déjà ajoutées.', 'em-site'); ?>
                                </p>
                            </div>
                        </aside>
                    </div>

                    <div class="em-site-template-wizard__panel-actions" data-wizard-wireframe-actions hidden>
                        <button
                            type="button"
                            class="em-site-hub__action em-site-hub__action--compact"
                            data-wizard-submit
                        >
                            <span class="em-site-hub__action-inner">
                                <i class="fa-solid fa-check" aria-hidden="true"></i>
                                <span class="em-site-hub__action-label"><?php esc_html_e('Valider le template', 'em-site'); ?></span>
                            </span>
                        </button>
                    </div>

                    <p class="em-site-template-wizard__error" id="em-site-template-wizard-plan-error" hidden></p>
                </div>
            </div>

            <footer class="em-site-template-wizard__actions">
                <button
                    type="button"
                    class="em-site-hub__action em-site-hub__action--compact em-site-hub__action--outline"
                    data-wizard-prev
                    hidden
                >
                    <span class="em-site-hub__action-inner">
                        <span class="em-site-hub__action-label"><?php esc_html_e('Retour', 'em-site'); ?></span>
                    </span>
                </button>
                <?php if (!$page_mode) { ?>
                    <button
                        type="button"
                        class="em-site-hub__action em-site-hub__action--compact em-site-hub__action--outline"
                        data-wizard-close
                    >
                        <span class="em-site-hub__action-inner">
                            <span class="em-site-hub__action-label"><?php esc_html_e('Annuler', 'em-site'); ?></span>
                        </span>
                    </button>
                <?php } ?>
                <button
                    type="button"
                    class="em-site-hub__action em-site-hub__action--compact"
                    data-wizard-next
                >
                    <span class="em-site-hub__action-inner">
                        <span class="em-site-hub__action-label"><?php esc_html_e('Suivant', 'em-site'); ?></span>
                    </span>
                </button>
            </footer>
        </div>
    </div>
    <?php
}
