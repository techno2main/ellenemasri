<?php
/**
 * Type de rubrique CONTACTS (EM-SITE).
 *
 * Rubrique configurable via le builder ; les items restent stockes en base.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enregistre le type CONTACTS.
 *
 * @param array<string, array<string, mixed>> $types
 * @return array<string, array<string, mixed>>
 */
function em_site_rubrique_type_contacts(array $types): array
{
    $types['contacts'] = [
        'label'        => __('CONTACT', 'em-site'),
        'label_plural' => __('CONTACT', 'em-site'),
        'noun'         => __('Contact', 'em-site'),
        'icon'         => 'dashicons-email-alt',
        'layout'       => ['columns' => 1, 'align' => [1 => 'left']],
        'starter'      => em_site_rubrique_default_appearance_fields(),
    ];

    return $types;
}
add_filter('em_site_rubrique_types', 'em_site_rubrique_type_contacts');