<?php



/**

 * Textes onboarding wizard création template.

 *

 * @package em-site

 */



if (!defined('ABSPATH')) {

    exit;

}



/**

 * Étapes résumées pour la page d'accueil du wizard.

 *

 * @return array<int, array{title: string, summary: string}>

 */

function em_site_admin_template_wizard_onboarding_steps(): array

{

    return [

        [

            'title'   => __('Identité', 'em-site'),

            'summary' => __('Nom et couleur du template — repères visuels dans l’admin et le bandeau d’édition.', 'em-site'),

        ],

        [

            'title'   => __('Plan', 'em-site'),

            'summary' => __('Compose le plan de page sur le wireframe : ajoute, réordonne ou retire des rubriques, puis valide le template.', 'em-site'),

        ],

    ];

}



