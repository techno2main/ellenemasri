<?php
function em_site_admin_hub_render_catalog_entry_links_badge(
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

    $classes = 'em-site-hub__live em-site-hub__live--in-card em-site-hub__live--entry-links';

    if ($uppercase) {
        $classes .= ' em-site-hub__live--uppercase';
    }

    if ($show_see_all) {
        $classes .= ' em-site-hub__live--entry-links-trimmed';
    }

    if ($blink_puce) {
        $classes .= ' em-site-hub__live--blink-puce';
    }
    ?>
    <p
        class="<?php echo esc_attr($classes); ?>"
        style="--em-site-live-color: <?php echo esc_attr($color); ?>;"
    >
        <span class="em-site-hub__catalog-entry-arrow" aria-hidden="true">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.5 6h5.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M6.25 3.25 9.5 6l-3.25 2.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="em-site-hub__live-text">
            <?php if ($prefix !== '') { ?>
                <span class="em-site-hub__entry-links-prefix"><?php echo esc_html($prefix); ?></span>
            <?php } ?>
            <?php foreach ($entries as $index => $entry) {
                if ($index > 0) {
                    echo '<span class="em-site-hub__catalog-entry-sep" aria-hidden="true"></span>';
                }
                ?>
                <a
                    class="em-site-hub__catalog-entry-link<?php echo !empty($entry['live']) ? ' is-live' : ''; ?>"
                    href="<?php echo esc_url((string) ($entry['url'] ?? '')); ?>"
                ><?php echo esc_html((string) ($entry['label'] ?? '')); ?></a>
                <?php if (!empty($entry['live'])) {
                    $entry_live_color = (string) ($entry['live_color'] ?? '');
                    ?>
                    <span
                        class="em-site-hub__catalog-entry-live-dot"
                        aria-hidden="true"
                        title="<?php esc_attr_e('Actif sur le template live', 'em-site'); ?>"
                        <?php if ($entry_live_color !== '') { ?>style="--em-live-color: <?php echo esc_attr($entry_live_color); ?>;"<?php } ?>
                    ></span>
                <?php } ?>
            <?php } ?>
            <?php if ($show_see_all) { ?>
                <span class="em-site-hub__catalog-entry-sep" aria-hidden="true"></span>
                <a
                    class="em-site-hub__catalog-entry-link em-site-hub__catalog-entry-link--see-all"
                    href="<?php echo esc_url($see_all_url); ?>"
                ><?php echo esc_html($see_all_label !== '' ? $see_all_label : __('Voir tout', 'em-site')); ?></a>
            <?php } ?>
        </span>
    </p>
    <?php
}

/**
 * Pastille actions « Nouveau template » (DUPLIQUER + WIZARD).
 */
function em_site_admin_hub_render_template_create_actions_badge(bool $can_duplicate = true): void
{
    $entries = [];

    if ($can_duplicate) {
        $entries[] = [
            'label'   => __('DUPLIQUER', 'em-site'),
            'trigger' => 'duplicate',
        ];
    }

    $entries[] = [
        'label'   => __('WIZARD', 'em-site'),
        'trigger' => 'wizard',
    ];

    $classes = 'em-site-hub__live em-site-hub__live--in-card em-site-hub__live--entry-links em-site-hub__live--uppercase';
    ?>
    <p
        class="<?php echo esc_attr($classes); ?>"
        style="--em-site-live-color: #751820;"
    >
        <span class="em-site-hub__catalog-entry-arrow" aria-hidden="true">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.5 6h5.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M6.25 3.25 9.5 6l-3.25 2.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="em-site-hub__live-text">
            <?php foreach ($entries as $index => $entry) {
                if ($index > 0) {
                    echo '<span class="em-site-hub__catalog-entry-sep" aria-hidden="true"></span>';
                }

                $trigger = (string) ($entry['trigger'] ?? '');
                $attr = $trigger === 'duplicate'
                    ? 'data-em-site-new-template-duplicate'
                    : 'data-em-site-new-template-wizard';
                ?>
                <button
                    type="button"
                    class="em-site-hub__catalog-entry-link"
                    <?php echo $attr; ?>
                ><?php echo esc_html((string) ($entry['label'] ?? '')); ?></button>
            <?php } ?>
        </span>
    </p>
    <?php
}

/**
 * Carte « Nouveau template » (sommaire Templates + Accueil).
 *
 * @param array{
 *     enabled?: bool,
 *     can_duplicate?: bool,
 *     section_attr?: string,
 *     section_value?: string,
 * } $args
 */
function em_site_admin_hub_render_template_create_card(array $args = []): void
{
    $enabled = (bool) ($args['enabled'] ?? true);
    $can_duplicate = (bool) ($args['can_duplicate'] ?? true);
    $section_attr = sanitize_key((string) ($args['section_attr'] ?? 'data-template-section'));
    $section_value = sanitize_key((string) ($args['section_value'] ?? 'create'));

    if ($section_attr === '') {
        $section_attr = 'data-template-section';
    }

    $card_classes = 'em-site-hub__card em-site-hub__card--template-create';

    if (!$enabled) {
        $card_classes .= ' em-site-hub__card--disabled';
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
        style="--em-site-template-accent: #751820; --em-site-template-text: #ffffff;"
    >
        <header class="em-site-hub__card-header">
            <div class="em-site-hub__card-heading">
                <?php
                em_site_admin_hub_render_card_title(
                    mb_strtoupper(__('Nouveau template', 'em-site')),
                    'dashicons-layout'
                );
                ?>
            </div>
            <?php if ($enabled) { ?>
                <button
                    type="button"
                    class="em-site-hub__card-create-icon"
                    data-em-site-new-template-open
                    aria-label="<?php esc_attr_e('Création d\'un nouveau template', 'em-site'); ?>"
                >
                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                </button>
            <?php } else { ?>
                <?php em_site_admin_hub_render_disabled_action('', 'dashicons dashicons-plus-alt2', true); ?>
            <?php } ?>
        </header>
        <div class="em-site-hub__card-desc em-site-templates-sommaire__card-desc">
            <p class="em-site-templates-sommaire__card-desc-label">
                <?php esc_html_e('Création d\'un nouveau Template', 'em-site'); ?>
            </p>
            <p class="em-site-templates-sommaire__card-desc-list">
                <?php esc_html_e('Duplique un template existant ou utilise le Wizard de création', 'em-site'); ?>
            </p>
        </div>
        <?php if ($enabled) { ?>
            <div class="em-site-templates-sommaire__card-live-footer">
                <?php em_site_admin_hub_render_template_create_actions_badge($can_duplicate); ?>
            </div>
        <?php } ?>
    </section>
    <?php
}

/**
 * Pastille badge générique.
 */
function em_site_admin_hub_render_status_badge(string $text, string $color, bool $in_card = false, bool $uppercase = false, bool $compact = false): void
{
    $classes = 'em-site-hub__live';

    if ($uppercase) {
        $classes .= ' em-site-hub__live--uppercase';
    }

    if ($in_card) {
        $classes .= ' em-site-hub__live--in-card';
    }

    if ($compact) {
        $classes .= ' em-site-hub__live--compact-in-card';
    }
    ?>
    <div
        class="<?php echo esc_attr($classes); ?>"
        <?php echo $in_card ? 'role="status"' : ''; ?>
        style="--em-site-live-color: <?php echo esc_attr($color); ?>;"
    >
        <span class="em-site-hub__live-indicator" aria-hidden="true">
            <span class="em-site-hub__live-dot"></span>
        </span>
        <span class="em-site-hub__live-text">
            <strong class="em-site-hub__live-template"><?php echo esc_html($text); ?></strong>
        </span>
    </div>
    <?php
}

/**
 * Pastille « template actif sur le site ».
 */

