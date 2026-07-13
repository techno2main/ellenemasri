<?php
/**
 * Page admin « Nouveau template » (hub brouillons + workspace création).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug page admin création template.
 */
function em_site_admin_template_create_page_slug(): string
{
    return 'em-template-create';
}

/**
 * URL page hub (étapes + brouillons).
 */
function em_site_admin_template_create_admin_url(): string
{
    return admin_url('admin.php?page=' . em_site_admin_template_create_page_slug());
}

/**
 * URL workspace — nouveau template (sans liste des brouillons).
 */
function em_site_admin_template_create_new_admin_url(): string
{
    return em_site_admin_template_create_workspace_admin_url();
}

/**
 * URL workspace — assistant création (mode progression, sans brouillon).
 */
function em_site_admin_template_create_workspace_admin_url(): string
{
    return add_query_arg('em_site_mode', 'edit', em_site_admin_template_create_admin_url());
}

/**
 * URL workspace — reprendre un brouillon.
 */
function em_site_admin_template_create_edit_admin_url(string $draft_id): string
{
    return add_query_arg(
        [
            'em_site_mode'  => 'edit',
            'em_site_draft' => sanitize_key($draft_id),
        ],
        em_site_admin_template_create_admin_url()
    );
}

/**
 * Mode courant de la page création.
 *
 * @return 'hub'|'new'|'edit'
 */
function em_site_admin_template_create_view_mode(): string
{
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $mode = sanitize_key((string) ($_GET['em_site_mode'] ?? ''));

    if ($mode === 'new') {
        return 'new';
    }

    if ($mode === 'edit') {
        return 'edit';
    }

    return 'hub';
}

/**
 * En-tête contexte brouillon (mode reprise).
 */
function em_site_admin_template_create_render_draft_context(): void
{
    ?>
    <div class="em-site-template-wizard-context" id="em-site-template-wizard-context" data-wizard-draft-context hidden>
        <p class="em-site-template-wizard-context__kicker" data-wizard-draft-kicker>
            <?php
            printf(
                /* translators: 1: current step number, 2: total steps */
                esc_html__('Création d\'un nouveau template — brouillon en cours — étape %1$s/%2$s', 'em-site'),
                '1',
                '2'
            );
            ?>
        </p>
        <div class="em-site-template-wizard-context__head">
            <div class="em-site-template-wizard-context__title-group">
                <div class="em-site-template-wizard-context__name-row">
                    <h2 class="em-site-template-wizard-context__name is-placeholder" id="em-site-template-wizard-context-name" data-wizard-draft-name>
                        <?php esc_html_e('Nom à définir', 'em-site'); ?>
                    </h2>
                    <span class="em-site-template-wizard-context__id" data-wizard-draft-id hidden>
                        <?php esc_html_e('Identifiant :', 'em-site'); ?>
                        <code class="em-site-template-wizard-context__slug" data-wizard-draft-slug></code>
                    </span>
                </div>
            </div>
            <div class="em-site-template-wizard-context__toolbar">
                <p class="em-site-template-wizard-context__progress" data-wizard-draft-progress>
                    <?php esc_html_e('Progression : 0 %', 'em-site'); ?>
                </p>
                <button
                    type="button"
                    class="em-site-template-wizard-context__reset-btn"
                    data-wizard-reset-workspace
                >
                    <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                    <?php esc_html_e('Reset', 'em-site'); ?>
                </button>
                <button
                    type="button"
                    class="em-site-template-wizard-context__save-btn"
                    data-wizard-save-draft
                    data-wizard-save-stay="1"
                >
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    <?php esc_html_e('Enregistrer la progression', 'em-site'); ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Onglets navigation workspace création (Mes Templates / Mes Brouillons).
 *
 * @param string $view_mode hub|new|edit
 * @param bool   $close_sticky_head Fermer la zone sticky après les onglets.
 */
function em_site_admin_template_create_render_nav_tabs(string $view_mode, bool $close_sticky_head = true): void
{
    $templates_url = em_site_admin_template_choice_admin_url();
    $drafts_url = em_site_admin_template_create_admin_url();
    $is_drafts_active = ($view_mode === 'hub');
    ?>
    <nav class="em-site-catalog-edit__nav em-site-template-create__nav" aria-label="<?php echo esc_attr__('Navigation assistant template', 'em-site'); ?>">
        <ul class="em-site-catalog-edit__nav-list">
            <li class="em-site-catalog-edit__nav-item">
                <a
                    class="em-site-catalog-edit__nav-link"
                    href="<?php echo esc_url($templates_url); ?>"
                    data-wizard-nav-tab="templates"
                >
                    <?php esc_html_e('Mes Templates', 'em-site'); ?>
                </a>
            </li>
            <li class="em-site-catalog-edit__nav-item<?php echo $is_drafts_active ? ' is-active' : ''; ?>">
                <a
                    class="em-site-catalog-edit__nav-link"
                    href="<?php echo esc_url($drafts_url); ?>"
                    data-wizard-nav-tab="drafts"
                    <?php echo $is_drafts_active ? 'aria-current="page"' : ''; ?>
                >
                    <?php esc_html_e('Mes Brouillons', 'em-site'); ?>
                </a>
            </li>
        </ul>
    </nav>
    <?php

    if ($close_sticky_head && function_exists('em_site_admin_hub_sticky_head_close')) {
        em_site_admin_hub_sticky_head_close();
    }
}

/**
 * Header de progression (mode reprise brouillon).
 */
function em_site_admin_template_create_render_progress_header(): void
{
    ?>
    <div
        class="em-site-template-wizard-progress"
        id="em-site-template-wizard-progress"
        role="status"
        aria-live="polite"
        data-wizard-progress
    >
        <div class="em-site-template-wizard-progress__inner">
            <span class="em-site-template-wizard-progress__badge" data-wizard-progress-badge><?php esc_html_e('ÉTAPE 1/2 - IDENTITÉ', 'em-site'); ?></span>
            <div class="em-site-template-wizard-progress__body">
                <p class="em-site-template-wizard-progress__title" data-wizard-progress-title></p>
                <p class="em-site-template-wizard-progress__checklist" data-wizard-progress-checklist hidden></p>
            </div>
            <span
                class="em-site-template-wizard-progress__step-complete"
                data-wizard-progress-step-complete
                hidden
                aria-hidden="true"
            >
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            </span>
        </div>
        <div class="em-site-template-wizard-progress__workspace" data-wizard-progress-workspace hidden>
            <div class="em-site-template-wizard-progress__fields" data-wizard-progress-fields>
                <div
                    class="em-site-template-wizard-progress__color-preview"
                    data-wizard-progress-color-preview
                    hidden
                    aria-hidden="true"
                >
                    <span class="em-site-template-wizard-progress__color-preview-label"><?php esc_html_e('Couleur', 'em-site'); ?></span>
                    <span class="em-site-template-wizard-progress__color-preview-swatch" data-wizard-progress-color-swatch aria-hidden="true"></span>
                </div>
                <button
                    type="button"
                    class="em-site-hub__action em-site-hub__action--compact em-site-template-wizard-progress__continue"
                    id="em-site-template-wizard-open"
                    data-wizard-progress-continue
                    hidden
                >
                    <span class="em-site-hub__action-inner">
                        <span class="em-site-hub__action-label"><?php echo esc_html(sprintf(__('Étape %1$s/%2$s', 'em-site'), '2', '2')); ?></span>
                    </span>
                </button>
                <button
                    type="button"
                    class="em-site-hub__action em-site-hub__action--compact em-site-template-wizard-progress__continue"
                    data-wizard-progress-advance
                    hidden
                >
                    <span class="em-site-hub__action-inner">
                        <span class="em-site-hub__action-label"><?php echo esc_html(sprintf(__('Étape %1$s/%2$s', 'em-site'), '2', '2')); ?></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Affiche le récapitulatif des étapes du wizard (hub ou nouveau template).
 *
 * @param array<int, array{title: string, summary: string}> $steps
 */
function em_site_admin_template_create_render_overview(array $steps, bool $is_hub): void
{
    $has_drafts = function_exists('em_site_template_wizard_drafts_get_all')
        && em_site_template_wizard_drafts_get_all() !== [];

    ?>
    <div class="em-site-templates-create-page__overview" id="em-site-template-wizard-overview">
        <p class="em-site-templates-create-page__overview-lead">
            <?php
            printf(
                /* translators: %d: number of wizard steps */
                esc_html__('Cet assistant comporte %d étapes. À la fin, ton template est créé et prêt à être personnalisé rubrique par rubrique.', 'em-site'),
                count($steps)
            );
            ?>
        </p>
        <?php if ($steps !== []) { ?>
            <ol class="em-site-templates-create-page__overview-steps">
                <?php foreach ($steps as $index => $step) { ?>
                    <li class="em-site-templates-create-page__overview-step">
                        <span class="em-site-templates-create-page__overview-step-num"><?php echo esc_html((string) ($index + 1)); ?></span>
                        <span class="em-site-templates-create-page__overview-step-body">
                            <strong><?php echo esc_html((string) ($step['title'] ?? '')); ?></strong>
                            <?php echo esc_html((string) ($step['summary'] ?? '')); ?>
                        </span>
                    </li>
                <?php } ?>
            </ol>
        <?php } ?>
        <?php if ($is_hub) { ?>
            <p class="em-site-templates-create-page__overview-note">
                <?php esc_html_e('Reprends un brouillon ci-dessous ou démarre un nouveau template.', 'em-site'); ?>
            </p>
            <p class="em-site-templates-create-page__overview-actions">
                <button
                    type="button"
                    class="em-site-hub__action em-site-hub__action--compact"
                    data-em-site-new-template-open
                >
                    <span class="em-site-hub__action-inner">
                        <span class="em-site-hub__action-label"><?php esc_html_e('Nouveau Template', 'em-site'); ?></span>
                    </span>
                </button>
            </p>
        <?php } else { ?>
            <p class="em-site-templates-create-page__overview-note">
                <?php esc_html_e('Enregistre un brouillon à tout moment pour le retrouver sur la page d’accueil de l’assistant.', 'em-site'); ?>
            </p>
            <?php if ($has_drafts) { ?>
            <p class="em-site-templates-create-page__overview-actions">
                <a class="em-site-hub__action em-site-hub__action--compact em-site-hub__action--outline" href="<?php echo esc_url(em_site_admin_template_create_admin_url()); ?>">
                    <span class="em-site-hub__action-inner">
                        <span class="em-site-hub__action-label"><?php esc_html_e('Retour aux brouillons', 'em-site'); ?></span>
                    </span>
                </a>
            </p>
            <?php } ?>
        <?php } ?>
    </div>
    <?php
}

/**
 * Rendu page création template.
 */
function em_site_admin_render_template_create_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (function_exists('em_site_template_unique_mode_enabled') && em_site_template_unique_mode_enabled()) {
        em_site_admin_template_redirect_with_notice(
            em_site_admin_template_choice_admin_url(),
            'warning',
            __('Mode template unique actif : page assistant indisponible.', 'em-site')
        );
    }

    if (!em_site_admin_can_manage_templates()) {
        em_site_admin_safe_redirect(em_site_admin_template_choice_admin_url());
        return;
    }

    $view_mode = em_site_admin_template_create_view_mode();

    if ($view_mode === 'new') {
        em_site_admin_safe_redirect(em_site_admin_template_create_workspace_admin_url());
        return;
    }

    $is_hub = ($view_mode === 'hub');
    $is_edit = ($view_mode === 'edit');
    $is_new = ($view_mode === 'new');
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $resume_draft_id = !$is_hub ? sanitize_key((string) ($_GET['em_site_draft'] ?? '')) : '';

    $steps = function_exists('em_site_admin_template_wizard_onboarding_steps')
        ? em_site_admin_template_wizard_onboarding_steps()
        : [];

    $page_class = 'em-site-templates-create-page em-site-templates-create-page--' . $view_mode;
    ?>
    <div
        class="wrap em-site-admin-module em-site-hub-sommaire <?php echo esc_attr($page_class); ?>"
        data-wizard-view="<?php echo esc_attr($view_mode); ?>"
        <?php if ($resume_draft_id !== '') { ?>
            data-wizard-resume-id="<?php echo esc_attr($resume_draft_id); ?>"
        <?php } ?>
    >
        <?php
        em_site_admin_hub_render_sommaire_header('', 'dashicons-layout', false, true, null, null, true);

        em_site_admin_template_create_render_nav_tabs($view_mode, !$is_edit);

        if ($is_edit) {
            em_site_admin_template_create_render_draft_context();
            em_site_admin_template_create_render_progress_header();
            if (function_exists('em_site_admin_hub_sticky_head_close')) {
                em_site_admin_hub_sticky_head_close();
            }
        }
        ?>

        <section
            class="em-site-catalog-sommaire__section"
            aria-labelledby="<?php echo $is_edit ? 'em-site-template-wizard-context-name' : 'em-site-template-create-page-title'; ?>"
            <?php echo $is_edit ? ' hidden' : ''; ?>
        >
            <?php if (!$is_edit) { ?>
            <header class="em-site-catalog-sommaire__section-header">
                <div id="em-site-template-create-page-title" class="em-site-catalog-sommaire__section-title">
                    <?php
                    if ($is_hub) {
                        em_site_admin_hub_render_card_title(__('Assistant de création de Template', 'em-site'), 'dashicons-plus-alt2');
                    } else {
                        em_site_admin_hub_render_card_title(__('Assistant de création d\'un nouveau Template', 'em-site'), 'dashicons-plus-alt2');
                    }
                    ?>
                </div>
            </header>
            <?php } ?>
            <div class="em-site-catalog-sommaire__section-body">
                <?php if ($is_hub) {
                    em_site_admin_template_create_render_overview($steps, true);
                } elseif (!$is_edit) {
                    em_site_admin_template_create_render_overview($steps, false);
                } ?>

                <?php if ($is_hub) { ?>
                    <section
                        class="em-site-template-wizard-drafts"
                        id="em-site-template-wizard-drafts"
                        aria-labelledby="em-site-template-wizard-drafts-title"
                    >
                        <header class="em-site-template-wizard-drafts__header">
                            <h3 class="em-site-template-wizard-drafts__title" id="em-site-template-wizard-drafts-title">
                                <?php esc_html_e('Brouillons en cours', 'em-site'); ?>
                            </h3>
                        </header>
                        <p class="em-site-template-wizard-drafts__empty" data-wizard-drafts-empty>
                            <?php esc_html_e('Aucun brouillon pour le moment.', 'em-site'); ?>
                        </p>
                        <ul class="em-site-template-wizard-drafts__list" data-wizard-drafts-list hidden></ul>
                    </section>
                <?php } else { ?>
                    <?php if ($is_new) { ?>
                    <div
                        class="em-site-template-wizard-guide"
                        id="em-site-template-wizard-guide"
                        role="status"
                        aria-live="polite"
                        data-wizard-guide
                    >
                        <span class="em-site-template-wizard-guide__step" data-wizard-guide-step></span>
                        <p class="em-site-template-wizard-guide__text" data-wizard-guide-text></p>
                    </div>
                    <?php } ?>

                    <form method="post" class="em-site-templates-admin__create-form" id="em-site-template-create-form">
                        <?php wp_nonce_field('em_site_template_create_wizard'); ?>
                        <input type="hidden" name="em_site_template_action" id="em-site-template-wizard-action" value="">
                        <input type="hidden" name="em_site_template_wizard_payload" id="em-site-template-wizard-payload" value="">

                        <div id="em-site-template-identity-stash" hidden aria-hidden="true"></div>

                        <div class="em-site-templates-admin__create-row" data-wizard-step="0" id="em-site-template-wizard-identity">
                            <div class="em-site-templates-admin__create-field" data-wizard-guide-target="label">
                                <label class="em-site-templates-admin__create-label" for="em-site-template-new-label"><?php esc_html_e('Nom du template', 'em-site'); ?></label>
                                <input type="text" id="em-site-template-new-label" name="em_site_template_label" class="regular-text em-site-templates-admin__create-input" required autocomplete="off" placeholder="<?php esc_attr_e('Nom à définir', 'em-site'); ?>">
                            </div>
                            <div class="em-site-templates-admin__create-field" data-wizard-guide-target="color">
                                <?php
                                em_site_admin_render_color_field([
                                    'id'           => 'em-site-template-new-color',
                                    'name'         => 'em_site_template_color',
                                    'value'        => '',
                                    'field_label'  => __('Couleur', 'em-site'),
                                    'modal_title'  => __('Couleur du template', 'em-site'),
                                    'input_class'  => 'em-site-templates-admin__create-color',
                                    'required'     => true,
                                ]);
                                ?>
                            </div>
                            <div class="em-site-templates-admin__create-actions">
                                <?php if (!$is_edit) { ?>
                                <button type="button" class="em-site-hub__action em-site-hub__action--compact em-site-hub__action--outline" data-wizard-save-draft>
                                    <span class="em-site-hub__action-inner">
                                        <span class="em-site-hub__action-label"><?php esc_html_e('Enregistrer le brouillon', 'em-site'); ?></span>
                                    </span>
                                </button>
                                <button type="button" class="em-site-hub__action em-site-hub__action--compact" id="em-site-template-wizard-open">
                                    <span class="em-site-hub__action-inner">
                                        <span class="em-site-hub__action-label"><?php esc_html_e('Continuer', 'em-site'); ?></span>
                                    </span>
                                </button>
                                <?php } ?>
                            </div>
                        </div>

                        <?php em_site_admin_template_wizard_render_ui(true, $is_edit); ?>
                    </form>
                <?php } ?>
            </div>
        </section>
    </div>
    <?php
    if ($is_hub && function_exists('em_site_admin_render_new_template_modals')) {
        em_site_admin_render_new_template_modals();
    }
}

