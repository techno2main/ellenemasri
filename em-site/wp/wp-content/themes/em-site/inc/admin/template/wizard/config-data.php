<?php
/**
 * Données config wizard (localize script).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hub catalogue pour une rubrique (wizard, sans template existant).
 */
function em_wp_admin_template_wizard_catalog_hub_for_rubrique(string $rubrique_slug): string
{
    $rubrique_slug = sanitize_key($rubrique_slug);
    $map = [
        'top-bar' => 'top-bars',
        'stream'  => 'streams',
        'video'   => 'videos',
        'release' => 'releases',
        'social'  => 'socials',
        'cta'     => 'ctas',
        'footer'  => 'footers',
    ];

    if (isset($map[$rubrique_slug])) {
        return $map[$rubrique_slug];
    }

    $definition = em_wp_admin_site_rubrique_all_definitions()[$rubrique_slug] ?? [];

    return sanitize_key((string) ($definition['catalog_module'] ?? $rubrique_slug));
}

/**
 * Entrées catalogue formatées pour le wizard.
 *
 * @return array<int, array{slug:string,label:string}>
 */
function em_wp_admin_template_wizard_catalog_choices_for_hub(string $hub_slug): array
{
    $hub_slug = sanitize_key($hub_slug);
    $choices = [];

    if ($hub_slug === '') {
        return $choices;
    }

    $entries = function_exists('em_wp_catalog_hub_entries')
        ? em_wp_catalog_hub_entries($hub_slug)
        : [];

    if ($entries === [] && function_exists('em_wp_custom_catalog_entries')) {
        $entries = em_wp_custom_catalog_entries($hub_slug);
    }

    foreach ($entries as $slug => $entry) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '') {
            continue;
        }

        $choices[] = [
            'slug'  => $slug,
            'label' => sanitize_text_field((string) ($entry['label'] ?? $slug)),
        ];
    }

    return $choices;
}

/**
 * @return array<int, array{slug:string,label:string}>
 */
function em_wp_admin_template_wizard_simple_choices(array $choices_map): array
{
    $out = [];

    foreach ($choices_map as $slug => $label) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '') {
            continue;
        }

        $out[] = [
            'slug'  => $slug,
            'label' => sanitize_text_field((string) $label),
        ];
    }

    return $out;
}

/**
 * Rubriques disponibles pour le wizard (métadonnées).
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_admin_template_wizard_rubrique_definitions(): array
{
    $definitions = [];
    $pinned = function_exists('em_wp_template_skeleton_pinned_slugs')
        ? em_wp_template_skeleton_pinned_slugs()
        : ['top-bar', 'footer'];

    foreach (em_wp_admin_site_rubrique_all_definitions() as $slug => $definition) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '') {
            continue;
        }

        $label = function_exists('em_wp_admin_rubrique_skeleton_label')
            ? em_wp_admin_rubrique_skeleton_label($slug)
            : (string) ($definition['label'] ?? $slug);
        $hub = $slug === 'header' ? '' : em_wp_admin_template_wizard_catalog_hub_for_rubrique($slug);
        $style_defaults = function_exists('em_wp_admin_module_default_style_colors')
            ? em_wp_admin_module_default_style_colors($slug)
            : ['background' => '#100421', 'text' => '#ffffff'];
        $visibility_toggle = function_exists('em_wp_site_rubrique_is_visibility_toggle')
            && em_wp_site_rubrique_is_visibility_toggle($slug);

        $definitions[$slug] = [
            'slug'              => $slug,
            'label'             => $label,
            'comingSoon'        => !empty($definition['coming_soon']),
            'pinned'            => in_array($slug, $pinned, true) ? ($slug === 'top-bar' ? 'top' : 'bottom') : '',
            'required'          => ($slug === 'header'),
            'catalogHub'        => $hub,
            'pointerKey'        => $slug === 'header'
                ? ''
                : (function_exists('em_wp_admin_rubrique_catalog_pointer_key')
                    ? em_wp_admin_rubrique_catalog_pointer_key($slug)
                    : $slug . '_slug'),
            'needsCatalogPick'  => $slug !== 'header'
                && $hub !== ''
                && empty($definition['coming_soon']),
            'defaultBg'         => sanitize_hex_color((string) ($style_defaults['background'] ?? '#100421')) ?: '#100421',
            'defaultText'       => sanitize_hex_color((string) ($style_defaults['text'] ?? '#ffffff')) ?: '#ffffff',
            'defaultEnabled'    => !$visibility_toggle,
        ];
    }

    return $definitions;
}

/**
 * Config JS wizard.
 *
 * @return array<string, mixed>
 */
function em_wp_admin_template_wizard_get_config(): array
{
    $default_order = ['top-bar', 'header', 'footer'];
    $catalogs = [
        'heroes'   => em_wp_admin_template_wizard_simple_choices(
            function_exists('em_wp_hero_catalog_choices') ? em_wp_hero_catalog_choices() : []
        ),
        'sliders'  => em_wp_admin_template_wizard_simple_choices(
            function_exists('em_wp_slider_catalog_choices') ? em_wp_slider_catalog_choices() : []
        ),
        'top-bars' => em_wp_admin_template_wizard_catalog_choices_for_hub('top-bars'),
        'streams'  => em_wp_admin_template_wizard_catalog_choices_for_hub('streams'),
        'videos'   => em_wp_admin_template_wizard_catalog_choices_for_hub('videos'),
        'releases' => em_wp_admin_template_wizard_catalog_choices_for_hub('releases'),
        'socials'  => em_wp_admin_template_wizard_catalog_choices_for_hub('socials'),
        'ctas'     => em_wp_admin_template_wizard_catalog_choices_for_hub('ctas'),
        'footers'  => em_wp_admin_template_wizard_catalog_choices_for_hub('footers'),
        'contacts' => em_wp_admin_template_wizard_catalog_choices_for_hub('contacts'),
    ];

    if (function_exists('em_wp_custom_catalog_modules')) {
        foreach (em_wp_custom_catalog_modules() as $module_slug => $module) {
            $module_slug = sanitize_key((string) $module_slug);
            if ($module_slug !== '') {
                $catalogs[$module_slug] = em_wp_admin_template_wizard_catalog_choices_for_hub($module_slug);
            }
        }
    }

    return [
        'defaultSkeletonOrder' => $default_order,
        'rubriques'            => em_wp_admin_template_wizard_rubrique_definitions(),
        'catalogs'             => $catalogs,
        'headerLayouts' => [
            ['value' => 'hero_left', 'label' => __('Hero à gauche', 'em-wp')],
            ['value' => 'slider_left', 'label' => __('Slider à gauche', 'em-wp')],
        ],
        'ajaxUrl'       => admin_url('admin-ajax.php'),
        'wireframeNonce' => wp_create_nonce('em_wp_template_wizard_wireframe'),
        'draftNonce'    => wp_create_nonce('em_wp_template_wizard_draft'),
        'serverDrafts'  => em_wp_template_wizard_drafts_get_all(),
        'pageMode'      => true,
        'cancelUrl'     => function_exists('em_wp_admin_template_create_admin_url')
            ? em_wp_admin_template_create_admin_url()
            : admin_url('admin.php'),
        'createHubUrl'  => function_exists('em_wp_admin_template_create_admin_url')
            ? em_wp_admin_template_create_admin_url()
            : admin_url('admin.php'),
        'createNewUrl'  => function_exists('em_wp_admin_template_create_new_admin_url')
            ? em_wp_admin_template_create_new_admin_url()
            : admin_url('admin.php'),
        'createWorkspaceUrl' => function_exists('em_wp_admin_template_create_workspace_admin_url')
            ? em_wp_admin_template_create_workspace_admin_url()
            : admin_url('admin.php'),
        'templatesListUrl' => function_exists('em_wp_admin_template_choice_admin_url')
            ? em_wp_admin_template_choice_admin_url()
            : admin_url('admin.php'),
        'draftStorageKey' => 'em_wp_template_wizard_drafts_' . get_current_user_id(),
        'existingTemplateSlugs' => array_values(array_keys(em_wp_template_registry())),
        'onboarding'    => [
            'stepLabels' => array_map(
                static fn(array $step): string => (string) ($step['title'] ?? ''),
                em_wp_admin_template_wizard_onboarding_steps()
            ),
            'guides' => [
                0 => __('Commence par saisir le nom de ton template, puis choisis sa couleur. Valide chaque action avant de continuer.', 'em-wp'),
                1 => __('Compose le plan sur le wireframe : glisse les rubriques pour les réordonner, retire celles dont tu n’as pas besoin, ou ajoute-en depuis la colonne de droite.', 'em-wp'),
            ],
            'identityGuides' => [
                'empty'        => __('Définis le nom du template, puis choisis sa couleur.', 'em-wp'),
                'nameFilled'   => __('Valide le nom, puis choisis la couleur du template.', 'em-wp'),
                'colorFilled'  => __('Valide la couleur, ou définis d’abord le nom du template.', 'em-wp'),
                'bothFilled'   => __('Nom et couleur validés. Passe à l’étape 2 pour composer le plan du template.', 'em-wp'),
            ],
            'progress' => [
                'stepHeading'        => __('%1$s — %2$s', 'em-wp'),
                'actionsLead'        => __('Actions', 'em-wp'),
                'actionsLeadInitialOne' => __('Pour l\'étape %1$s/%2$s, tu as %3$s action', 'em-wp'),
                'actionsLeadInitialMany' => __('Pour l\'étape %1$s/%2$s, tu as %3$s actions', 'em-wp'),
                'actionsLeadRemainingOne' => __('Pour l\'étape %1$s/%2$s, il te reste %3$s action', 'em-wp'),
                'actionsLeadRemainingMany' => __('Pour l\'étape %1$s/%2$s, il te reste %3$s actions', 'em-wp'),
                'actionsStepComplete' => __('Tu as terminé l\'étape %1$s. Tu peux passer à l\'étape suivante : %2$s', 'em-wp'),
                'actionsStepCompleteLast' => __('Tu as terminé l\'étape %1$s.', 'em-wp'),
                'actionsLeadByStep' => [
                    0 => [
                        'initialOne'   => __('Tu dois choisir un nom et une couleur pour le nouveau template', 'em-wp'),
                        'initialMany'  => __('Tu dois choisir un nom et une couleur pour le nouveau template', 'em-wp'),
                        'remainingOne' => __('Pour définir l\'identité, il te reste %s action à valider', 'em-wp'),
                        'remainingMany'=> __('Pour définir l\'identité, il te reste %s actions à valider', 'em-wp'),
                    ],
                    1 => [
                        'lead' => __('Compose le plan du template sur le wireframe, puis valide-le.', 'em-wp'),
                    ],
                ],
                'actionCodePrefix' => __('Action', 'em-wp'),
                'actionsProgressCounter' => __('Action %1$s/%2$s', 'em-wp'),
                'statusDone'         => __('OK', 'em-wp'),
                'statusPending'      => __('à choisir', 'em-wp'),
                'statusPendingInput' => __('à saisir', 'em-wp'),
                'statusPendingVerify'=> __('à vérifier', 'em-wp'),
                'statusPendingValidate'=> __('à valider', 'em-wp'),
                'steps' => [
                    0 => [
                        [
                            'id'      => 'a',
                            'key'     => 'label',
                            'label'   => __('Nom', 'em-wp'),
                            'summary' => __('Choisis un nom', 'em-wp'),
                        ],
                        [
                            'id'      => 'b',
                            'key'     => 'color',
                            'label'   => __('Couleur', 'em-wp'),
                            'summary' => __('Choisis une couleur', 'em-wp'),
                        ],
                    ],
                    1 => [],
                ],
            ],
            'draftContextMeta' => __('Reprise à l’étape %1$s sur %2$s — %3$s', 'em-wp'),
            'draftContextProgress' => __('Progression : %s', 'em-wp'),
            'draftContextNamePlaceholder' => __('Nom à définir', 'em-wp'),
            'draftContextKicker' => __('Création d\'un nouveau template — brouillon en cours — étape %1$s/%2$s', 'em-wp'),
            'focus' => [
                0 => '#em-wp-template-new-label',
                1 => '[data-wizard-plan-picker-list]',
            ],
        ],
        'i18n'          => [
            'quitDraft'       => __('Quitter sans enregistrer ? Les modifications du wizard seront perdues.', 'em-wp'),
            'removeRubrique'  => __('Retirer cette rubrique du squelette ?', 'em-wp'),
            'removeRubriqueLabel' => __('Retirer %s', 'em-wp'),
            'addRubrique'     => __('Ajouter %s à la liste', 'em-wp'),
            'createConfirm'   => __('Valider le template « %s » avec cette configuration ?', 'em-wp'),
            'deleteTemplate'  => __('Supprimer ce template ?', 'em-wp'),
            'headerRequired'  => __('Le squelette doit contenir la rubrique HEADER.', 'em-wp'),
            'catalogRequired' => __('Sélectionne une entrée catalogue pour chaque rubrique requise.', 'em-wp'),
            'colorRequired'   => __('Choisis une couleur pour le template.', 'em-wp'),
            'nameRequired'    => __('Le nom du template est requis.', 'em-wp'),
            'labelPlaceholder' => __('Nom à définir', 'em-wp'),
            'comingSoonHint'  => __('Configuration détaillée bientôt disponible.', 'em-wp'),
            'wireframeError'  => __('Impossible de charger l’aperçu du plan.', 'em-wp'),
            'draftSaved'      => __('Brouillon enregistré.', 'em-wp'),
            'draftProgressSaved' => __('Progression enregistrée.', 'em-wp'),
            'draftNameRequired' => __('Saisis au minimum un nom pour enregistrer le brouillon.', 'em-wp'),
            'draftSaveError'  => __('Impossible d’enregistrer le brouillon.', 'em-wp'),
            'draftResume'     => __('Reprendre', 'em-wp'),
            'draftDelete'     => __('Supprimer', 'em-wp'),
            'draftDeleteConfirm' => __('Supprimer le brouillon « %s » ?', 'em-wp'),
            'draftDeleteError' => __('Impossible de supprimer le brouillon.', 'em-wp'),
            'draftSwitchConfirm' => __('Changer de brouillon sans enregistrer les modifications en cours ?', 'em-wp'),
            'draftSwitchConfirmYes' => __('Changer', 'em-wp'),
            'draftNewConfirm' => __('Démarrer un nouveau brouillon sans enregistrer les modifications en cours ?', 'em-wp'),
            'draftLeaveTitle' => __('Quitter l’assistant', 'em-wp'),
            'draftLeaveConfirm' => __('Enregistrer l’avancement en cours avant de quitter ?', 'em-wp'),
            'draftLeaveSave' => __('Enregistrer', 'em-wp'),
            'draftLeaveCancel' => __('Annuler', 'em-wp'),
            'draftLeaveWithoutSave' => __('Aucun nom saisi — quitter sans enregistrer ?', 'em-wp'),
            'draftLeaveQuit' => __('Quitter sans enregistrer', 'em-wp'),
            'draftStepFallback' => __('Étape %s', 'em-wp'),
            'draftStepProgress' => __('Étape %1$s — %2$s', 'em-wp'),
            'guideStep'       => __('Étape %1$s sur %2$s — %3$s', 'em-wp'),
            'progressNextStep'=> __('Étape %1$s/%2$s', 'em-wp'),
            'validateAction'  => __('Valider', 'em-wp'),
            'validateRubriques' => __('Valider le choix des rubriques', 'em-wp'),
            'validatePositions' => __('Valider l’ordre des rubriques', 'em-wp'),
            'skeletonBadgeDefault' => __('(Rubrique de base)', 'em-wp'),
            'skeletonBadgeAdded'   => __('Rubrique ajoutée', 'em-wp'),
            'dragRubrique'         => __('Glisser pour réordonner', 'em-wp'),
            'wizardResetConfirm' => __('Réinitialiser l’assistant ? Les saisies non enregistrées seront perdues.', 'em-wp'),
            'wizardResetTitle' => __('Reset', 'em-wp'),
        ],
    ];
}
