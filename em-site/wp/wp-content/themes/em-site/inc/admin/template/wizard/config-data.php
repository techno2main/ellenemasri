<?php
/**
 * Données config wizard (localize script).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hub catalogue pour une rubrique (wizard, sans template existant).
 */
function em_site_admin_template_wizard_catalog_hub_for_rubrique(string $rubrique_slug): string
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

    $definition = em_site_admin_site_rubrique_all_definitions()[$rubrique_slug] ?? [];

    return sanitize_key((string) ($definition['catalog_module'] ?? $rubrique_slug));
}

/**
 * Entrées catalogue formatées pour le wizard.
 *
 * @return array<int, array{slug:string,label:string}>
 */
function em_site_admin_template_wizard_catalog_choices_for_hub(string $hub_slug): array
{
    $hub_slug = sanitize_key($hub_slug);
    $choices = [];

    if ($hub_slug === '') {
        return $choices;
    }

    $entries = function_exists('em_site_catalog_hub_entries')
        ? em_site_catalog_hub_entries($hub_slug)
        : [];

    if ($entries === [] && function_exists('em_site_custom_catalog_entries')) {
        $entries = em_site_custom_catalog_entries($hub_slug);
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
function em_site_admin_template_wizard_simple_choices(array $choices_map): array
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
function em_site_admin_template_wizard_rubrique_definitions(): array
{
    $definitions = [];
    $pinned = function_exists('em_site_template_skeleton_pinned_slugs')
        ? em_site_template_skeleton_pinned_slugs()
        : ['top-bar', 'footer'];

    foreach (em_site_admin_site_rubrique_all_definitions() as $slug => $definition) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '') {
            continue;
        }

        $label = function_exists('em_site_admin_rubrique_skeleton_label')
            ? em_site_admin_rubrique_skeleton_label($slug)
            : (string) ($definition['label'] ?? $slug);
        $hub = $slug === 'header' ? '' : em_site_admin_template_wizard_catalog_hub_for_rubrique($slug);
        $style_defaults = function_exists('em_site_admin_module_default_style_colors')
            ? em_site_admin_module_default_style_colors($slug)
            : ['background' => '#100421', 'text' => '#ffffff'];
        $visibility_toggle = function_exists('em_site_site_rubrique_is_visibility_toggle')
            && em_site_site_rubrique_is_visibility_toggle($slug);

        $definitions[$slug] = [
            'slug'              => $slug,
            'label'             => $label,
            'comingSoon'        => !empty($definition['coming_soon']),
            'pinned'            => in_array($slug, $pinned, true) ? ($slug === 'top-bar' ? 'top' : 'bottom') : '',
            'required'          => ($slug === 'header'),
            'catalogHub'        => $hub,
            'pointerKey'        => $slug === 'header'
                ? ''
                : (function_exists('em_site_admin_rubrique_catalog_pointer_key')
                    ? em_site_admin_rubrique_catalog_pointer_key($slug)
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
function em_site_admin_template_wizard_get_config(): array
{
    $default_order = ['top-bar', 'header', 'footer'];
    $catalogs = [
        'heroes'   => em_site_admin_template_wizard_simple_choices(
            function_exists('em_site_hero_catalog_choices') ? em_site_hero_catalog_choices() : []
        ),
        'sliders'  => em_site_admin_template_wizard_simple_choices(
            function_exists('em_site_slider_catalog_choices') ? em_site_slider_catalog_choices() : []
        ),
        'top-bars' => em_site_admin_template_wizard_catalog_choices_for_hub('top-bars'),
        'streams'  => em_site_admin_template_wizard_catalog_choices_for_hub('streams'),
        'videos'   => em_site_admin_template_wizard_catalog_choices_for_hub('videos'),
        'releases' => em_site_admin_template_wizard_catalog_choices_for_hub('releases'),
        'socials'  => em_site_admin_template_wizard_catalog_choices_for_hub('socials'),
        'ctas'     => em_site_admin_template_wizard_catalog_choices_for_hub('ctas'),
        'footers'  => em_site_admin_template_wizard_catalog_choices_for_hub('footers'),
        'contacts' => em_site_admin_template_wizard_catalog_choices_for_hub('contacts'),
    ];

    if (function_exists('em_site_custom_catalog_modules')) {
        foreach (em_site_custom_catalog_modules() as $module_slug => $module) {
            $module_slug = sanitize_key((string) $module_slug);
            if ($module_slug !== '') {
                $catalogs[$module_slug] = em_site_admin_template_wizard_catalog_choices_for_hub($module_slug);
            }
        }
    }

    return [
        'defaultSkeletonOrder' => $default_order,
        'rubriques'            => em_site_admin_template_wizard_rubrique_definitions(),
        'catalogs'             => $catalogs,
        'headerLayouts' => [
            ['value' => 'hero_left', 'label' => __('Hero à gauche', 'em-site')],
            ['value' => 'slider_left', 'label' => __('Slider à gauche', 'em-site')],
        ],
        'ajaxUrl'       => admin_url('admin-ajax.php'),
        'wireframeNonce' => wp_create_nonce('em_site_template_wizard_wireframe'),
        'draftNonce'    => wp_create_nonce('em_site_template_wizard_draft'),
        'serverDrafts'  => em_site_template_wizard_drafts_get_all(),
        'pageMode'      => true,
        'cancelUrl'     => function_exists('em_site_admin_template_create_admin_url')
            ? em_site_admin_template_create_admin_url()
            : admin_url('admin.php'),
        'createHubUrl'  => function_exists('em_site_admin_template_create_admin_url')
            ? em_site_admin_template_create_admin_url()
            : admin_url('admin.php'),
        'createNewUrl'  => function_exists('em_site_admin_template_create_new_admin_url')
            ? em_site_admin_template_create_new_admin_url()
            : admin_url('admin.php'),
        'createWorkspaceUrl' => function_exists('em_site_admin_template_create_workspace_admin_url')
            ? em_site_admin_template_create_workspace_admin_url()
            : admin_url('admin.php'),
        'templatesListUrl' => function_exists('em_site_admin_template_choice_admin_url')
            ? em_site_admin_template_choice_admin_url()
            : admin_url('admin.php'),
        'draftStorageKey' => 'em_site_template_wizard_drafts_' . get_current_user_id(),
        'existingTemplateSlugs' => array_values(array_keys(em_site_template_registry())),
        'onboarding'    => [
            'stepLabels' => array_map(
                static fn(array $step): string => (string) ($step['title'] ?? ''),
                em_site_admin_template_wizard_onboarding_steps()
            ),
            'guides' => [
                0 => __('Commence par saisir le nom de ton template, puis choisis sa couleur. Valide chaque action avant de continuer.', 'em-site'),
                1 => __('Compose le plan sur le wireframe : glisse les rubriques pour les réordonner, retire celles dont tu n’as pas besoin, ou ajoute-en depuis la colonne de droite.', 'em-site'),
            ],
            'identityGuides' => [
                'empty'        => __('Définis le nom du template, puis choisis sa couleur.', 'em-site'),
                'nameFilled'   => __('Valide le nom, puis choisis la couleur du template.', 'em-site'),
                'colorFilled'  => __('Valide la couleur, ou définis d’abord le nom du template.', 'em-site'),
                'bothFilled'   => __('Nom et couleur validés. Passe à l’étape 2 pour composer le plan du template.', 'em-site'),
            ],
            'progress' => [
                'stepHeading'        => __('%1$s — %2$s', 'em-site'),
                'actionsLead'        => __('Actions', 'em-site'),
                'actionsLeadInitialOne' => __('Pour l\'étape %1$s/%2$s, tu as %3$s action', 'em-site'),
                'actionsLeadInitialMany' => __('Pour l\'étape %1$s/%2$s, tu as %3$s actions', 'em-site'),
                'actionsLeadRemainingOne' => __('Pour l\'étape %1$s/%2$s, il te reste %3$s action', 'em-site'),
                'actionsLeadRemainingMany' => __('Pour l\'étape %1$s/%2$s, il te reste %3$s actions', 'em-site'),
                'actionsStepComplete' => __('Tu as terminé l\'étape %1$s. Tu peux passer à l\'étape suivante : %2$s', 'em-site'),
                'actionsStepCompleteLast' => __('Tu as terminé l\'étape %1$s.', 'em-site'),
                'actionsLeadByStep' => [
                    0 => [
                        'initialOne'   => __('Tu dois choisir un nom et une couleur pour le nouveau template', 'em-site'),
                        'initialMany'  => __('Tu dois choisir un nom et une couleur pour le nouveau template', 'em-site'),
                        'remainingOne' => __('Pour définir l\'identité, il te reste %s action à valider', 'em-site'),
                        'remainingMany'=> __('Pour définir l\'identité, il te reste %s actions à valider', 'em-site'),
                    ],
                    1 => [
                        'lead' => __('Compose le plan du template sur le wireframe, puis valide-le.', 'em-site'),
                    ],
                ],
                'actionCodePrefix' => __('Action', 'em-site'),
                'actionsProgressCounter' => __('Action %1$s/%2$s', 'em-site'),
                'statusDone'         => __('OK', 'em-site'),
                'statusPending'      => __('à choisir', 'em-site'),
                'statusPendingInput' => __('à saisir', 'em-site'),
                'statusPendingVerify'=> __('à vérifier', 'em-site'),
                'statusPendingValidate'=> __('à valider', 'em-site'),
                'steps' => [
                    0 => [
                        [
                            'id'      => 'a',
                            'key'     => 'label',
                            'label'   => __('Nom', 'em-site'),
                            'summary' => __('Choisis un nom', 'em-site'),
                        ],
                        [
                            'id'      => 'b',
                            'key'     => 'color',
                            'label'   => __('Couleur', 'em-site'),
                            'summary' => __('Choisis une couleur', 'em-site'),
                        ],
                    ],
                    1 => [],
                ],
            ],
            'draftContextMeta' => __('Reprise à l’étape %1$s sur %2$s — %3$s', 'em-site'),
            'draftContextProgress' => __('Progression : %s', 'em-site'),
            'draftContextNamePlaceholder' => __('Nom à définir', 'em-site'),
            'draftContextKicker' => __('Création d\'un nouveau template — brouillon en cours — étape %1$s/%2$s', 'em-site'),
            'focus' => [
                0 => '#em-site-template-new-label',
                1 => '[data-wizard-plan-picker-list]',
            ],
        ],
        'i18n'          => [
            'quitDraft'       => __('Quitter sans enregistrer ? Les modifications du wizard seront perdues.', 'em-site'),
            'removeRubrique'  => __('Retirer cette rubrique du squelette ?', 'em-site'),
            'removeRubriqueLabel' => __('Retirer %s', 'em-site'),
            'addRubrique'     => __('Ajouter %s à la liste', 'em-site'),
            'createConfirm'   => __('Valider le template « %s » avec cette configuration ?', 'em-site'),
            'deleteTemplate'  => __('Supprimer ce template ?', 'em-site'),
            'headerRequired'  => __('Le squelette doit contenir la rubrique HEADER.', 'em-site'),
            'catalogRequired' => __('Sélectionne une entrée catalogue pour chaque rubrique requise.', 'em-site'),
            'colorRequired'   => __('Choisis une couleur pour le template.', 'em-site'),
            'nameRequired'    => __('Le nom du template est requis.', 'em-site'),
            'labelPlaceholder' => __('Nom à définir', 'em-site'),
            'comingSoonHint'  => __('Configuration détaillée bientôt disponible.', 'em-site'),
            'wireframeError'  => __('Impossible de charger l’aperçu du plan.', 'em-site'),
            'draftSaved'      => __('Brouillon enregistré.', 'em-site'),
            'draftProgressSaved' => __('Progression enregistrée.', 'em-site'),
            'draftNameRequired' => __('Saisis au minimum un nom pour enregistrer le brouillon.', 'em-site'),
            'draftSaveError'  => __('Impossible d’enregistrer le brouillon.', 'em-site'),
            'draftResume'     => __('Reprendre', 'em-site'),
            'draftDelete'     => __('Supprimer', 'em-site'),
            'draftDeleteConfirm' => __('Supprimer le brouillon « %s » ?', 'em-site'),
            'draftDeleteError' => __('Impossible de supprimer le brouillon.', 'em-site'),
            'draftSwitchConfirm' => __('Changer de brouillon sans enregistrer les modifications en cours ?', 'em-site'),
            'draftSwitchConfirmYes' => __('Changer', 'em-site'),
            'draftNewConfirm' => __('Démarrer un nouveau brouillon sans enregistrer les modifications en cours ?', 'em-site'),
            'draftLeaveTitle' => __('Quitter l’assistant', 'em-site'),
            'draftLeaveConfirm' => __('Enregistrer l’avancement en cours avant de quitter ?', 'em-site'),
            'draftLeaveSave' => __('Enregistrer', 'em-site'),
            'draftLeaveCancel' => __('Annuler', 'em-site'),
            'draftLeaveWithoutSave' => __('Aucun nom saisi — quitter sans enregistrer ?', 'em-site'),
            'draftLeaveQuit' => __('Quitter sans enregistrer', 'em-site'),
            'draftStepFallback' => __('Étape %s', 'em-site'),
            'draftStepProgress' => __('Étape %1$s — %2$s', 'em-site'),
            'guideStep'       => __('Étape %1$s sur %2$s — %3$s', 'em-site'),
            'progressNextStep'=> __('Étape %1$s/%2$s', 'em-site'),
            'validateAction'  => __('Valider', 'em-site'),
            'validateRubriques' => __('Valider le choix des rubriques', 'em-site'),
            'validatePositions' => __('Valider l’ordre des rubriques', 'em-site'),
            'skeletonBadgeDefault' => __('(Rubrique de base)', 'em-site'),
            'skeletonBadgeAdded'   => __('Rubrique ajoutée', 'em-site'),
            'dragRubrique'         => __('Glisser pour réordonner', 'em-site'),
            'wizardResetConfirm' => __('Réinitialiser l’assistant ? Les saisies non enregistrées seront perdues.', 'em-site'),
            'wizardResetTitle' => __('Reset', 'em-site'),
        ],
    ];
}
