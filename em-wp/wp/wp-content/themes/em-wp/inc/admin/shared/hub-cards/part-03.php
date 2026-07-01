<?php
function em_wp_admin_hub_render_catalog_entry_links_badge(
    array $entries,
    string $color = '#751820',
    string $prefix = '',
    bool $uppercase = false,
    int $max_visible = 0,
    string $see_all_url = '',
    string $see_all_label = '',
    bool $blink_puce = false,
    bool $always_see_all = false
): void {
    if ($entries === []) {
        return;
    }

    $total_count = count($entries);
    $see_all_url = trim($see_all_url);
    $see_all_label = trim($see_all_label);
    $should_trim = $max_visible > 0 && $total_count > $max_visible && $see_all_url !== '';
    $show_see_all = $should_trim || ($always_see_all && $see_all_url !== '');

    if ($should_trim) {
        $entries = array_slice($entries, 0, $max_visible);
    }

    $classes = 'em-wp-hub__live em-wp-hub__live--in-card em-wp-hub__live--entry-links';

    if ($uppercase) {
        $classes .= ' em-wp-hub__live--uppercase';
    }

    if ($show_see_all) {
        $classes .= ' em-wp-hub__live--entry-links-trimmed';
    }

    if ($blink_puce) {
        $classes .= ' em-wp-hub__live--blink-puce';
    }
    ?>
    <p
        class="<?php echo esc_attr($classes); ?>"
        style="--em-wp-live-color: <?php echo esc_attr($color); ?>;"
    >
        <span class="em-wp-hub__catalog-entry-arrow" aria-hidden="true">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.5 6h5.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M6.25 3.25 9.5 6l-3.25 2.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="em-wp-hub__live-text">
            <?php if ($prefix !== '') { ?>
                <span class="em-wp-hub__entry-links-prefix"><?php echo esc_html($prefix); ?></span>
            <?php } ?>
            <?php foreach ($entries as $index => $entry) {
                if ($index > 0) {
                    echo '<span class="em-wp-hub__catalog-entry-sep" aria-hidden="true"></span>';
                }
                ?>
                <a
                    class="em-wp-hub__catalog-entry-link<?php echo !empty($entry['live']) ? ' is-live' : ''; ?>"
                    href="<?php echo esc_url((string) ($entry['url'] ?? '')); ?>"
                ><?php echo esc_html((string) ($entry['label'] ?? '')); ?></a>
                <?php if (!empty($entry['live'])) {
                    $entry_live_color = (string) ($entry['live_color'] ?? '');
                    ?>
                    <span
                        class="em-wp-hub__catalog-entry-live-dot"
                        aria-hidden="true"
                        title="<?php esc_attr_e('Actif sur le template live', 'em-wp'); ?>"
                        <?php if ($entry_live_color !== '') { ?>style="--em-live-color: <?php echo esc_attr($entry_live_color); ?>;"<?php } ?>
                    ></span>
                <?php } ?>
            <?php } ?>
            <?php if ($show_see_all) { ?>
                <span class="em-wp-hub__catalog-entry-sep" aria-hidden="true"></span>
                <a
                    class="em-wp-hub__catalog-entry-link em-wp-hub__catalog-entry-link--see-all"
                    href="<?php echo esc_url($see_all_url); ?>"
                ><?php echo esc_html($see_all_label !== '' ? $see_all_label : __('Voir tout', 'em-wp')); ?></a>
            <?php } ?>
        </span>
    </p>
    <?php
}

/**
 * Pastille actions Â« Nouveau template Â» (DUPLIQUER + WIZARD).
 */
function em_wp_admin_hub_render_template_create_actions_badge(bool $can_duplicate = true): void
{
    $entries = [];

    if ($can_duplicate) {
        $entries[] = [
            'label'   => __('DUPLIQUER', 'em-wp'),
            'trigger' => 'duplicate',
        ];
    }

    $entries[] = [
        'label'   => __('WIZARD', 'em-wp'),
        'trigger' => 'wizard',
    ];

    $classes = 'em-wp-hub__live em-wp-hub__live--in-card em-wp-hub__live--entry-links em-wp-hub__live--uppercase';
    ?>
    <p
        class="<?php echo esc_attr($classes); ?>"
        style="--em-wp-live-color: #751820;"
    >
        <span class="em-wp-hub__catalog-entry-arrow" aria-hidden="true">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.5 6h5.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M6.25 3.25 9.5 6l-3.25 2.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="em-wp-hub__live-text">
            <?php foreach ($entries as $index => $entry) {
                if ($index > 0) {
                    echo '<span class="em-wp-hub__catalog-entry-sep" aria-hidden="true"></span>';
                }

                $trigger = (string) ($entry['trigger'] ?? '');
                $attr = $trigger === 'duplicate'
                    ? 'data-em-wp-new-template-duplicate'
                    : 'data-em-wp-new-template-wizard';
                ?>
                <button
                    type="button"
                    class="em-wp-hub__catalog-entry-link"
                    <?php echo $attr; ?>
                ><?php echo esc_html((string) ($entry['label'] ?? '')); ?></button>
            <?php } ?>
        </span>
    </p>
    <?php
}

/**
 * Carte Â« Nouveau template Â» (sommaire Templates + Accueil).
 *
 * @param array{
 *     enabled?: bool,
 *     can_duplicate?: bool,
 *     section_attr?: string,
 *     section_value?: string,
 * } $args
 */
function em_wp_admin_hub_render_template_create_card(array $args = []): void
{
    $enabled = (bool) ($args['enabled'] ?? true);
    $can_duplicate = (bool) ($args['can_duplicate'] ?? true);
    $section_attr = sanitize_key((string) ($args['section_attr'] ?? 'data-template-section'));
    $section_value = sanitize_key((string) ($args['section_value'] ?? 'create'));

    if ($section_attr === '') {
        $section_attr = 'data-template-section';
    }

    $card_classes = 'em-wp-hub__card em-wp-hub__card--template-create';

    if (!$enabled) {
        $card_classes .= ' em-wp-hub__card--disabled';
    }

    $section_attr_html = sprintf(
        '%s="%s"',
        esc_attr($section_attr),
        esc_attr($section_value)
    );
    ?>
    <section
        class="<?php echo esc_attr($card_classes); ?>"
        <?php echo $section_attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        style="--em-wp-template-accent: #751820; --em-wp-template-text: #ffffff;"
    >
        <header class="em-wp-hub__card-header">
            <div class="em-wp-hub__card-heading">
                <?php
                em_wp_admin_hub_render_card_title(
                    mb_strtoupper(__('Nouveau template', 'em-wp')),
                    'dashicons-layout'
                );
                ?>
            </div>
            <?php if ($enabled) { ?>
                <button
                    type="button"
                    class="em-wp-hub__card-create-icon"
                    data-em-wp-new-template-open
                    aria-label="<?php esc_attr_e('Création d\'un nouveau template', 'em-wp'); ?>"
                >
                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                </button>
            <?php } else { ?>
                <?php em_wp_admin_hub_render_disabled_action('', 'dashicons dashicons-plus-alt2', true); ?>
            <?php } ?>
        </header>
        <div class="em-wp-hub__card-desc em-wp-templates-sommaire__card-desc">
            <p class="em-wp-templates-sommaire__card-desc-label">
                <?php esc_html_e('Création d\'un nouveau Template', 'em-wp'); ?>
            </p>
            <p class="em-wp-templates-sommaire__card-desc-list">
                <?php esc_html_e('Duplique un template existant ou utilise le Wizard de création', 'em-wp'); ?>
            </p>
        </div>
        <?php if ($enabled) { ?>
            <div class="em-wp-templates-sommaire__card-live-footer">
                <?php em_wp_admin_hub_render_template_create_actions_badge($can_duplicate); ?>
            </div>
        <?php } ?>
    </section>
    <?php
}

/**
 * Pastille badge gÃ©nÃ©rique.
 */
function em_wp_admin_hub_render_status_badge(string $text, string $color, bool $in_card = false, bool $uppercase = false, bool $compact = false): void
{
    $classes = 'em-wp-hub__live';

    if ($uppercase) {
        $classes .= ' em-wp-hub__live--uppercase';
    }

    if ($in_card) {
        $classes .= ' em-wp-hub__live--in-card';
    }

    if ($compact) {
        $classes .= ' em-wp-hub__live--compact-in-card';
    }
    ?>
    <div
        class="<?php echo esc_attr($classes); ?>"
        <?php echo $in_card ? 'role="status"' : ''; ?>
        style="--em-wp-live-color: <?php echo esc_attr($color); ?>;"
    >
        <span class="em-wp-hub__live-indicator" aria-hidden="true">
            <span class="em-wp-hub__live-dot"></span>
        </span>
        <span class="em-wp-hub__live-text">
            <strong class="em-wp-hub__live-template"><?php echo esc_html($text); ?></strong>
        </span>
    </div>
    <?php
}

/**
 * Pastille Â« template actif sur le site Â».
 */

