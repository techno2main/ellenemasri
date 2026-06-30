<?php
/**
 * Rendu front — rubrique CONTACT (catalogue personnalisé).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu HTML de la section contact sans template-part legacy.
 *
 * @param array<string, mixed> $contact
 */
function em_wp_render_contact_section(array $contact, string $module_slug): void
{
    $module_slug = sanitize_key($module_slug);
    $field_definitions = function_exists('em_wp_custom_catalog_module_field_definitions')
        ? em_wp_custom_catalog_module_field_definitions($module_slug)
        : [];

    $bg = trim((string) ($contact['background_color'] ?? ''));
    $text = trim((string) ($contact['text_color'] ?? ''));
    $inline_style = '';

    if ($bg !== '') {
        $inline_style .= '--em-contact-bg:' . esc_attr($bg) . ';background:' . esc_attr($bg) . ';';
    }

    if ($text !== '') {
        $inline_style .= '--em-contact-text:' . esc_attr($text) . ';color:' . esc_attr($text) . ';';
    }

    $items = [];

    foreach ($field_definitions as $field_key => $definition) {
        $field_key = sanitize_key((string) $field_key);
        $hidden_key = function_exists('em_wp_custom_catalog_field_hidden_key')
            ? em_wp_custom_catalog_field_hidden_key($field_key)
            : $field_key . '_hidden';
        $value = trim((string) ($contact[$field_key] ?? ''));

        if ($field_key === '' || !empty($contact[$hidden_key]) || $value === '') {
            continue;
        }

        $items[] = [
            'label' => function_exists('em_wp_custom_catalog_field_display_label')
                ? em_wp_custom_catalog_field_display_label($module_slug, $field_key, $contact)
                : trim((string) ($definition['label'] ?? $field_key)),
            'value' => $value,
            'type'  => sanitize_key((string) ($definition['type'] ?? 'text')) ?: 'text',
        ];
    }

    if ($items === []) {
        return;
    }

    $section_nav = function_exists('em_wp_landing_get_section_nav_hrefs')
        ? em_wp_landing_get_section_nav_hrefs($module_slug)
        : ['prev' => '#cta', 'next' => '#footer'];
    ?>
    <section id="contact" class="em-contact em-contact--<?php echo esc_attr(sanitize_html_class($module_slug)); ?>"<?php echo $inline_style !== '' ? ' style="' . esc_attr($inline_style) . '"' : ''; ?>>
        <div class="em-contact__inner">
            <?php if (($section_nav['prev'] ?? '') !== '' || ($section_nav['next'] ?? '') !== '') { ?>
            <div class="em-contact__nav">
                <?php if (($section_nav['next'] ?? '') !== '') { ?>
                    <a href="<?php echo esc_attr((string) $section_nav['next']); ?>" class="em-contact__nav-link" aria-label="<?php esc_attr_e('Section suivante', 'em-wp'); ?>">↓</a>
                <?php } ?>
                <?php if (($section_nav['prev'] ?? '') !== '') { ?>
                    <a href="<?php echo esc_attr((string) $section_nav['prev']); ?>" class="em-contact__nav-link" aria-label="<?php esc_attr_e('Section précédente', 'em-wp'); ?>">↑</a>
                <?php } ?>
            </div>
            <?php } ?>

            <h2 class="em-contact__title screen-reader-text"><?php esc_html_e('CONTACT', 'em-wp'); ?></h2>

            <dl class="em-contact__list">
                <?php foreach ($items as $item) {
                    $label = (string) ($item['label'] ?? '');
                    $value = (string) ($item['value'] ?? '');
                    $type = (string) ($item['type'] ?? 'text');
                    ?>
                    <div class="em-contact__item">
                        <dt class="em-contact__term"><?php echo esc_html($label); ?></dt>
                        <dd class="em-contact__value">
                            <?php if ($type === 'email') { ?>
                                <a href="<?php echo esc_url('mailto:' . antispambot($value)); ?>"><?php echo esc_html($value); ?></a>
                            <?php } elseif ($type === 'url') { ?>
                                <a href="<?php echo esc_url($value); ?>" target="_blank" rel="noreferrer"><?php echo esc_html($value); ?></a>
                            <?php } elseif ($type === 'tel') { ?>
                                <a href="<?php echo esc_url('tel:' . preg_replace('/\s+/', '', $value)); ?>"><?php echo esc_html($value); ?></a>
                            <?php } else { ?>
                                <?php echo esc_html($value); ?>
                            <?php } ?>
                        </dd>
                    </div>
                <?php } ?>
            </dl>
        </div>
    </section>
    <?php
}

function em_wp_contacts_enqueue_front_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');
    $theme_uri = get_template_directory_uri();
    $css_path = 'assets/front/css/modules/contacts/contact.css';

    wp_enqueue_style(
        'em-wp-landing-ui',
        $theme_uri . '/assets/front/css/landing-ui.css',
        ['em-wp-theme'],
        file_exists(get_template_directory() . '/assets/front/css/landing-ui.css')
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/assets/front/css/landing-ui.css')
            : $theme_version
    );

    wp_enqueue_style(
        'em-wp-contacts',
        $theme_uri . '/' . $css_path,
        ['em-wp-landing-ui'],
        file_exists(get_template_directory() . '/' . $css_path)
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/' . $css_path)
            : $theme_version
    );
}

/**
 * Rendu d'une rubrique liée à un catalogue personnalisé (CONTACTS, …).
 */
function em_wp_render_custom_catalog_rubrique(string $module_slug): void
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '' || !function_exists('em_wp_custom_catalog_is_module') || !em_wp_custom_catalog_is_module($module_slug)) {
        return;
    }

    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility($module_slug)) {
        return;
    }

    $options = function_exists('em_wp_custom_catalog_rubrique_get_options_for_front')
        ? em_wp_custom_catalog_rubrique_get_options_for_front($module_slug)
        : [];

    if (empty($options['enabled'])) {
        return;
    }

    em_wp_contacts_enqueue_front_assets();

    em_wp_render_contact_section($options, $module_slug);
}

function em_wp_render_contacts(): void
{
    em_wp_render_custom_catalog_rubrique('contacts');
}
