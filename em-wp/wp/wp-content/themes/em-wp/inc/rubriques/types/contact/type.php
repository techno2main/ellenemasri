<?php
/**
 * Type de rubrique CONTACTS (V4).
 *
 * Rubrique configurable via le builder ; les items restent stockes en base.
 *
 * @package em-wp
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
function em_wp_rubrique_type_contacts(array $types): array
{
    $types['contacts'] = [
        'label'        => __('CONTACT', 'em-wp'),
        'label_plural' => __('CONTACTS', 'em-wp'),
        'noun'         => __('Contact', 'em-wp'),
        'icon'         => 'dashicons-screenoptions',
        'layout'       => ['columns' => 1, 'align' => [1 => 'left']],
        'starter'      => em_wp_rubrique_default_appearance_fields(),
    ];

    return $types;
}
add_filter('em_wp_rubrique_types', 'em_wp_rubrique_type_contacts');