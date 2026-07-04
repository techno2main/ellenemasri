<?php



/**

 * Textes onboarding wizard création template.

 *

 * @package em-wp

 */



if (!defined('ABSPATH')) {

    exit;

}



/**

 * Étapes résumées pour la page d'accueil du wizard.

 *

 * @return array<int, array{title: string, summary: string}>

 */

function em_wp_admin_template_wizard_onboarding_steps(): array

{

    return [

        [

            'title'   => __('Identité', 'em-wp'),

            'summary' => __('Nom et couleur du template — repères visuels dans l’admin et le bandeau d’édition.', 'em-wp'),

        ],

        [

            'title'   => __('Plan', 'em-wp'),

            'summary' => __('Compose le plan de page sur le wireframe : ajoute, réordonne ou retire des rubriques, puis valide le template.', 'em-wp'),

        ],

    ];

}



