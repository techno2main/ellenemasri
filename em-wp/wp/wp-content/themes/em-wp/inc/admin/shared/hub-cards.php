<?php
/**
 * Composants UI partagés — grilles de cartes sommaire (Accueil, Catalogues, Templates…).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/hub-breadcrumb.php';

/**
 * Enqueue CSS/JS communs aux pages sommaire à cartes.
 */
function em_wp_admin_hub_cards_enqueue_assets(): void
{
    em_wp_admin_enqueue_shared_assets();

    wp_enqueue_style('dashicons');

    wp_enqueue_style(
        'em-wp-admin-hub-cards',
        get_template_directory_uri() . '/assets/admin/css/shared/hub-cards.css',
        ['em-wp-admin-module-common', 'em-wp-admin-live-badge', 'dashicons'],
        em_wp_admin_asset_version('assets/admin/css/shared/hub-cards.css')
    );

    wp_enqueue_script(
        'em-wp-admin-hub-sommaire-preview',
        get_template_directory_uri() . '/assets/admin/js/shared/hub-sommaire-preview.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/hub-sommaire-preview.js'),
        true
    );

}

/**
 * Prénom (ou repli) de l'admin connecté pour les en-têtes sommaire.
 */
function em_wp_admin_hub_greeting_name(): string
{
    $user = wp_get_current_user();

    if (!$user instanceof WP_User || $user->ID <= 0) {
        return '';
    }

    $first_name = trim((string) $user->first_name);

    if ($first_name !== '') {
        return $first_name;
    }

    $display_name = trim((string) $user->display_name);

    if ($display_name !== '') {
        return $display_name;
    }

    return (string) $user->user_login;
}

/**
 * Balises HTML autorisées dans l'intro sommaire (fil d'Ariane, nom template…).
 *
 * @return array<string, array<string, bool>>
 */
function em_wp_admin_hub_intro_html_allowed_tags(): array
{
    return [
        'nav'    => [
            'class'      => true,
            'aria-label' => true,
        ],
        'div'    => [
            'class' => true,
        ],
        'a'      => [
            'class' => true,
            'href'  => true,
        ],
        'span'   => [
            'class'        => true,
            'aria-hidden'  => true,
            'aria-current' => true,
        ],
        'strong' => [
            'class'        => true,
            'aria-current' => true,
        ],
    ];
}

/**
 * Ouvre la zone sticky (fil d'Ariane + onglets).
 */
function em_wp_admin_hub_sticky_head_open(): void
{
    echo '<div class="em-wp-hub__sticky-head">';
}

/**
 * Ferme la zone sticky hub.
 */
function em_wp_admin_hub_sticky_head_close(): void
{
    echo '</div>';
}

/**
 * En-tête sommaire partagé (avatar + description + flèche).
 */
function em_wp_admin_hub_render_sommaire_header(
    string $description = '',
    string $icon_class = 'dashicons-dashboard',
    bool $description_allows_html = false,
    bool $show_template_banner = true,
    ?callable $context_banner_renderer = null,
    ?array $breadcrumb = null,
    bool $sticky_head = false
): void {
    if ($sticky_head) {
        em_wp_admin_hub_sticky_head_open();
    }

    $icon_class = trim($icon_class);

    if ($icon_class !== '' && !str_contains($icon_class, 'dashicons ')) {
        $icon_class = 'dashicons ' . $icon_class;
    }

    $greeting_name = em_wp_admin_hub_greeting_name();

    $breadcrumb_html = '';

    if ($breadcrumb !== null && $breadcrumb !== []) {
        $breadcrumb_html = em_wp_admin_hub_breadcrumb_html($breadcrumb);
    } elseif ($breadcrumb === null) {
        $auto_crumbs = em_wp_admin_hub_resolve_breadcrumb_crumbs();
        if ($auto_crumbs !== []) {
            $breadcrumb_html = em_wp_admin_hub_breadcrumb_html($auto_crumbs);
        }
    }
    ?>
    <h1 class="em-wp-hub__greeting">
        <span class="<?php echo esc_attr($icon_class); ?> em-wp-hub__greeting-icon" aria-hidden="true"></span>
        <span class="em-wp-hub__greeting-text">
            <?php
            if ($greeting_name !== '') {
                printf(
                    /* translators: %s: admin first name */
                    esc_html__('%s', 'em-wp'),
                    esc_html($greeting_name)
                );
            } else {
                esc_html_e('em-wp');
            }
            ?>
        </span>
        <?php
        echo get_avatar(
            get_current_user_id(),
            40,
            '',
            $greeting_name !== '' ? sprintf(__('Avatar de %s', 'em-wp'), $greeting_name) : __('Avatar', 'em-wp'),
            ['class' => 'em-wp-hub__greeting-avatar']
        );
        ?>
    </h1>

    <?php if ($breadcrumb_html !== '') { ?>
        <div class="em-wp-hub__breadcrumb"><?php echo wp_kses($breadcrumb_html, em_wp_admin_hub_intro_html_allowed_tags()); ?></div>
    <?php } elseif ($description !== '') { ?>
        <p class="description em-wp-hub__intro-text em-wp-hub__breadcrumb"><?php
            if ($description_allows_html) {
                echo wp_kses($description, em_wp_admin_hub_intro_html_allowed_tags());
            } else {
                echo esc_html($description);
            }
        ?></p>
    <?php } ?>

    <?php
    if (is_callable($context_banner_renderer)) {
        $context_banner_renderer();
    } elseif ($show_template_banner) {
        em_wp_admin_render_template_editing_banner();
    }
    ?>
    <?php
}

/**
 * Rendu du titre d'une carte (icône dashicons + libellé).
 */
function em_wp_admin_hub_render_card_title(string $title, string $icon_class, ?callable $after_icon = null, string $icon_color = ''): void
{
    $icon_class = trim($icon_class);
    $icon_style = $icon_color !== '' ? ' style="--em-wp-card-accent: ' . esc_attr($icon_color) . ';"' : '';

    if ($icon_class !== '' && !str_contains($icon_class, 'dashicons ')) {
        $icon_class = 'dashicons ' . $icon_class;
    }
    ?>
    <h2 class="em-wp-hub__card-title">
        <span class="<?php echo esc_attr($icon_class); ?> em-wp-hub__card-title-icon"<?php echo $icon_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-hidden="true"></span>
        <?php
        if ($after_icon !== null) {
            $after_icon();
        }
        ?>
        <span class="em-wp-hub__card-title-label"><?php echo esc_html($title); ?></span>
    </h2>
    <?php
}

/**
 * Pastille compacte — nombre d'entrées (cartes sommaire).
 */
function em_wp_admin_hub_render_count_badge(int $count): void
{
    $count = max(0, $count);
    ?>
    <span
        class="em-wp-hub__count-badge"
        aria-label="<?php echo esc_attr(sprintf(
            /* translators: %d: number of catalog items */
            _n('%d item', '%d items', $count, 'em-wp'),
            $count
        )); ?>"
    ><?php echo esc_html((string) $count); ?></span>
    <?php
}

/**
 * Texte description carte catalogue (nom d'item en majuscules).
 */
function em_wp_admin_hub_catalog_card_description_text(string $item_name, string $rubrique_name, string $module_slug = ''): string
{
    $rubrique = mb_strtoupper(trim($rubrique_name));

    return (string) sprintf(
        /* translators: 1: literal "items", 2: rubrique name */
        __('Liste des %1$s disponibles pour la rubrique %2$s.', 'em-wp'),
        __('items', 'em-wp'),
        $rubrique
    );
}

/**
 * Description carte catalogue — nom d'item en gras / majuscules.
 */
function em_wp_admin_hub_render_catalog_card_description(string $item_name, string $rubrique_name, string $module_slug = ''): void
{
    $item_html = '<strong class="em-wp-hub__card-desc-item">' . esc_html(__('items', 'em-wp')) . '</strong>';
    $rubrique_html = '<strong class="em-wp-hub__card-desc-rubrique">' . esc_html(mb_strtoupper(trim($rubrique_name))) . '</strong>';

    $text = sprintf(
        /* translators: 1: literal "items", 2: rubrique name */
        __('Liste des %1$s disponibles pour la rubrique %2$s.', 'em-wp'),
        $item_html,
        $rubrique_html
    );
    ?>
    <p class="em-wp-hub__card-desc">
        <?php
        echo wp_kses(
            $text,
            [
                'strong' => ['class' => true],
            ]
        );
        ?>
    </p>
    <?php
}

/**
 * Rendu d'un bouton d'action pill (lien).
 */
function em_wp_admin_hub_render_action_link(
    string $url,
    string $label,
    string $icon_class,
    bool $compact = false,
    string $accessible_label = ''
): void {
    $icon_class = trim($icon_class);

    if ($icon_class !== '' && !str_contains($icon_class, 'dashicons ')) {
        $icon_class = 'dashicons ' . $icon_class;
    }

    $action_class = 'em-wp-hub__action';
    $visible_label = trim($label);
    $aria_label = trim($accessible_label !== '' ? $accessible_label : $label);

    if ($compact) {
        $action_class .= ' em-wp-hub__action--compact';
    }

    if ($visible_label === '' && $icon_class !== '') {
        $action_class .= ' em-wp-hub__action--icon-only';
    }
    ?>
    <a
        class="<?php echo esc_attr($action_class); ?>"
        href="<?php echo esc_url($url); ?>"
        <?php echo $aria_label !== '' ? 'aria-label="' . esc_attr($aria_label) . '"' : ''; ?>
        <?php echo $aria_label !== '' ? 'title="' . esc_attr($aria_label) . '"' : ''; ?>
    >
        <span class="em-wp-hub__action-inner">
            <?php if ($icon_class !== '') { ?>
                <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
            <?php } ?>
            <?php if ($visible_label !== '') { ?>
                <span class="em-wp-hub__action-label"><?php echo esc_html($visible_label); ?></span>
            <?php } ?>
        </span>
    </a>
    <?php
}

/**
 * Bouton renommer — à gauche du titre de carte catalogue.
 *
 * @param array<string, string> $attrs
 */
function em_wp_admin_hub_render_catalog_name_edit_button(
    string $button_id,
    string $accessible_label,
    array $attrs = []
): void {
    $button_id = sanitize_html_class($button_id);
    $accessible_label = trim($accessible_label);

    if ($button_id === '') {
        return;
    }

    $attr_html = '';

    foreach ($attrs as $key => $value) {
        $key = sanitize_key((string) $key);

        if ($key === '') {
            continue;
        }

        $attr_html .= sprintf(' %s="%s"', esc_attr($key), esc_attr((string) $value));
    }
    ?>
    <button
        type="button"
        class="em-wp-hub__card-name-edit"
        id="<?php echo esc_attr($button_id); ?>"
        title="<?php echo esc_attr($accessible_label); ?>"
        <?php echo $accessible_label !== '' ? 'aria-label="' . esc_attr($accessible_label) . '"' : ''; ?>
        <?php echo $attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    >
        <i class="fa-solid fa-pen" aria-hidden="true"></i>
    </button>
    <?php
}

/**
 * Action « Ouvrir le catalogue » — libellé visible + icône dossier.
 */
function em_wp_admin_hub_render_catalog_open_action(string $url, string $catalog_label = ''): void
{
    $url = trim($url);
    $catalog_label = trim($catalog_label);

    if ($url === '') {
        return;
    }

    $accessible_label = $catalog_label !== ''
        ? sprintf(
            /* translators: %s: catalog name */
            __('Ouvrir le catalogue %s', 'em-wp'),
            $catalog_label
        )
        : __('Ouvrir le catalogue', 'em-wp');
    ?>
    <a
        class="em-wp-hub__action em-wp-hub__action--compact em-wp-hub__action--catalog-open em-wp-hub__action--fa"
        href="<?php echo esc_url($url); ?>"
        aria-label="<?php echo esc_attr($accessible_label); ?>"
        title="<?php echo esc_attr($accessible_label); ?>"
    >
        <span class="em-wp-hub__action-inner">
            <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
            <span class="em-wp-hub__action-label"><?php esc_html_e('Ouvrir', 'em-wp'); ?></span>
        </span>
    </a>
    <?php
}

/**
 * Lien icône Font Awesome compact (cartes hub — voir, éditer…).
 */
function em_wp_admin_hub_render_card_fa_action_link(
    string $url,
    string $fa_class,
    string $accessible_label
): void {
    $fa_class = trim($fa_class);
    $accessible_label = trim($accessible_label);

    if ($url === '' || $fa_class === '') {
        return;
    }
    ?>
    <a
        class="em-wp-hub__action em-wp-hub__action--compact em-wp-hub__action--icon-only em-wp-hub__action--fa"
        href="<?php echo esc_url($url); ?>"
        <?php echo $accessible_label !== '' ? 'aria-label="' . esc_attr($accessible_label) . '"' : ''; ?>
    >
        <span class="em-wp-hub__action-inner">
            <i class="<?php echo esc_attr($fa_class); ?>" aria-hidden="true"></i>
        </span>
    </a>
    <?php
}

/**
 * Bouton icône Font Awesome compact (panneau inline, toggle…).
 *
 * @param array<string, string> $attrs
 */
function em_wp_admin_hub_render_card_fa_action_button(
    string $button_id,
    string $fa_class,
    string $accessible_label,
    array $attrs = []
): void {
    $fa_class = trim($fa_class);
    $button_id = sanitize_html_class($button_id);
    $accessible_label = trim($accessible_label);

    if ($fa_class === '' || $button_id === '') {
        return;
    }

    $attr_html = '';

    foreach ($attrs as $key => $value) {
        $key = sanitize_key((string) $key);

        if ($key === '') {
            continue;
        }

        $attr_html .= sprintf(' %s="%s"', esc_attr($key), esc_attr((string) $value));
    }
    ?>
    <button
        type="button"
        class="em-wp-hub__action em-wp-hub__action--compact em-wp-hub__action--icon-only em-wp-hub__action--fa"
        id="<?php echo esc_attr($button_id); ?>"
        <?php echo $accessible_label !== '' ? 'aria-label="' . esc_attr($accessible_label) . '"' : ''; ?>
        <?php echo $attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    >
        <span class="em-wp-hub__action-inner">
            <i class="<?php echo esc_attr($fa_class); ?>" aria-hidden="true"></i>
        </span>
    </button>
    <?php
}

/**
 * Bouton secondaire désactivé (cartes « Nouveau … », prochaine étape).
 */
function em_wp_admin_hub_render_disabled_action(string $label, string $icon_class = 'dashicons dashicons-plus-alt2', bool $compact = false): void
{
    $action_class = 'em-wp-hub__action em-wp-hub__action--secondary';
    $visible_label = trim($label);

    if ($compact) {
        $action_class .= ' em-wp-hub__action--compact';
    }

    if ($visible_label === '' && $icon_class !== '') {
        $action_class .= ' em-wp-hub__action--icon-only';
    }
    ?>
    <button type="button" class="<?php echo esc_attr($action_class); ?>" disabled title="<?php esc_attr_e('Prochaine étape', 'em-wp'); ?>">
        <span class="em-wp-hub__action-inner">
            <?php if ($icon_class !== '') { ?>
                <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
            <?php } ?>
            <?php if ($visible_label !== '') { ?>
                <span class="em-wp-hub__action-label"><?php echo esc_html($visible_label); ?></span>
            <?php } ?>
        </span>
    </button>
    <?php
}

/**
 * Pastille liste des entrées catalogue cliquables (cartes hub CATALOGUES).
 *
 * @param array<int, array{label:string,url:string}> $entries
 */
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
 * Pastille actions « Nouveau template » (DUPLIQUER + WIZARD).
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
 * Carte « Nouveau template » (sommaire Templates + Accueil).
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
 * Pastille badge générique.
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
 * Pastille « template actif sur le site ».
 */
function em_wp_admin_hub_render_live_template_badge(string $active_label, string $active_slug, bool $in_card = false): void
{
    $active_slug = sanitize_key($active_slug);

    if ($in_card) {
        ?>
        <div class="em-wp-hub__card-live-status">
            <p class="em-wp-hub__card-live-status-prefix">
                <?php esc_html_e('Ton site utilise actuellement le template :', 'em-wp'); ?>
            </p>
            <?php em_wp_admin_hub_render_template_active_pill($active_label, $active_slug); ?>
        </div>
        <?php
        return;
    }

    em_wp_admin_hub_render_template_active_pill($active_label, $active_slug);
}

/**
 * Pastille « template live » compacte (carte template individuelle).
 */
function em_wp_admin_hub_render_template_live_badge(string $label, string $template_slug): void
{
    em_wp_admin_hub_render_template_active_pill($label, $template_slug);
}

/**
 * Pastille template actif (fond couleur template, typo blanche, chip LIVE clignotante).
 */
function em_wp_admin_hub_render_template_active_pill(string $label, string $template_slug): void
{
    $label = trim($label);
    $template_slug = sanitize_key($template_slug);
    $style_attr = function_exists('em_wp_admin_template_tab_style_attr')
        ? em_wp_admin_template_tab_style_attr($template_slug)
        : '';
    ?>
    <div
        class="em-wp-hub__template-live-pill"
        role="status"
        <?php if ($style_attr !== '') { ?>
            style="<?php echo esc_attr($style_attr); ?>"
        <?php } ?>
    >
        <span class="em-wp-hub__template-live-pill-name"><?php echo esc_html(mb_strtoupper($label)); ?></span>
        <span class="em-wp-hub__template-live-pill-live">
            <span class="em-wp-hub__live-indicator" aria-hidden="true">
                <span class="em-wp-hub__live-dot"></span>
            </span>
            <?php esc_html_e('Live', 'em-wp'); ?>
        </span>
    </div>
    <?php
}

/**
 * Pastille « Activer » (fond couleur template, chip Activer).
 *
 * @param array{compact?:bool,table?:bool,block?:bool,id?:string,class?:string} $args
 */
function em_wp_admin_hub_render_template_activate_pill(string $slug, string $display_label, array $args = []): void
{
    $slug = sanitize_key($slug);
    $label_upper = mb_strtoupper(trim($display_label));
    $compact = !empty($args['compact']);
    $table = !empty($args['table']);
    $block = !empty($args['block']);
    $id = sanitize_html_class((string) ($args['id'] ?? ''));
    $extra_class = trim((string) ($args['class'] ?? ''));

    $classes = trim(
        'em-wp-hub__template-live-pill em-wp-hub__template-live-pill--activate em-wp-templates-sommaire__activate-live '
        . $extra_class
    );

    if ($compact || $table) {
        $classes .= ' em-wp-hub__template-live-pill--compact';
    }

    if ($block) {
        $classes .= ' em-wp-hub__template-live-pill--block';
    }

    $style_attr = function_exists('em_wp_admin_template_tab_style_attr')
        ? em_wp_admin_template_tab_style_attr($slug)
        : '';
    ?>
    <button
        type="button"
        class="<?php echo esc_attr($classes); ?>"
        <?php if ($id !== '') { ?>
            id="<?php echo esc_attr($id); ?>"
        <?php } ?>
        <?php if ($style_attr !== '') { ?>
            style="<?php echo esc_attr($style_attr); ?>"
        <?php } ?>
        data-template-slug="<?php echo esc_attr($slug); ?>"
        data-template-label="<?php echo esc_attr($label_upper); ?>"
        title="<?php esc_attr_e('Activer sur le site public', 'em-wp'); ?>"
    >
        <span class="em-wp-hub__template-live-pill-name"><?php echo esc_html($label_upper); ?></span>
        <span class="em-wp-hub__template-live-pill-live em-wp-hub__template-live-pill-action">
            <?php echo esc_html(mb_strtoupper(__('Activer', 'em-wp'))); ?>
        </span>
    </button>
    <?php
}

/**
 * Badge « Activer sur le site » (carte ou tableau).
 */
function em_wp_admin_hub_render_template_activate_badge(
    string $slug,
    string $display_label,
    string $color,
    bool $compact = false,
    bool $table = false
): void {
    unset($color);

    em_wp_admin_hub_render_template_activate_pill($slug, $display_label, [
        'compact' => $compact,
        'table'   => $table,
        'block'   => !$table,
    ]);
}

/**
 * Pied de carte template : badge live ou action d’activation.
 */
function em_wp_admin_hub_render_template_card_live_footer(
    string $slug,
    string $display_label,
    string $color,
    bool $is_live,
    bool $can_manage
): void {
    if ($is_live) {
        em_wp_admin_hub_render_template_live_badge($display_label, $slug);
        return;
    }

    if (!$can_manage) {
        return;
    }

    em_wp_admin_hub_render_template_activate_badge($slug, $display_label, $color, false);
}

/**
 * Formulaire POST — bascule template live (sommaire Templates).
 */
function em_wp_admin_hub_render_template_set_live_form(string $redirect_page = ''): void
{
    if ($redirect_page === '' && function_exists('em_wp_admin_template_choice_page_slug')) {
        $redirect_page = em_wp_admin_template_choice_page_slug();
    }

    $active_slug = em_wp_get_active_template_slug();
    ?>
    <form
        id="em-wp-hub-set-live-template-form"
        class="em-wp-hub__live-switch-form"
        method="post"
        action=""
        hidden
    >
        <?php wp_nonce_field('em_wp_template_set_active'); ?>
        <input type="hidden" name="em_wp_template_action" value="set_active">
        <input type="hidden" name="em_wp_template_redirect_page" value="<?php echo esc_attr($redirect_page); ?>">
        <input type="hidden" name="em_wp_template_active_slug" value="<?php echo esc_attr($active_slug); ?>">
    </form>
    <?php
}

/**
 * Enqueue JS du switch template live (sommaire Templates).
 */
function em_wp_admin_hub_enqueue_template_live_switcher(): void
{
    wp_enqueue_script(
        'em-wp-admin-template-live-switch',
        get_template_directory_uri() . '/assets/admin/js/pages/template-live-switch.js',
        ['em-wp-admin-confirm-modal'],
        em_wp_admin_asset_version('assets/admin/js/pages/template-live-switch.js'),
        true
    );

    wp_localize_script(
        'em-wp-admin-template-live-switch',
        'emWpTemplateLiveSwitch',
        [
            'i18n' => [
                'confirm' => __('Activer le template %s sur le site public ?', 'em-wp'),
                'confirmLabel' => __('Activer', 'em-wp'),
                'cancelLabel' => __('Annuler', 'em-wp'),
            ],
        ]
    );
}

/**
 * Bandeau template actif sur le site public.
 */
function em_wp_admin_hub_render_live_template_switcher(string $redirect_page = ''): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_get_active_template_slug')) {
        return;
    }

    $registry = em_wp_template_registry();
    $active_slug = em_wp_get_active_template_slug();
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    ?>
    <div
        class="em-wp-hub__live-bar"
        data-active-slug="<?php echo esc_attr($active_slug); ?>"
    >
        <?php em_wp_admin_hub_render_live_template_badge($active_label, $active_slug, false); ?>
    </div>
    <?php
}

/**
 * Libellé court pour toggle catalogue (MAYAMI, ELLENE…).
 */
function em_wp_admin_catalog_choice_switch_label(string $catalog_slug, string $label): string
{
    $catalog_slug = sanitize_key($catalog_slug);

    foreach (['mayami', 'ellene'] as $token) {
        if ($catalog_slug !== '' && str_contains($catalog_slug, $token)) {
            return mb_strtoupper($token);
        }
    }

    $label = trim($label);

    return $label !== '' ? mb_strtoupper($label) : mb_strtoupper($catalog_slug);
}

/**
 * Couleur d'accent admin partagée (catalogues, toggles rubrique template…).
 */
function em_wp_admin_hub_brand_accent_color(): string
{
    return '#751820';
}

/**
 * Couleur d'accent pour un toggle catalogue (hero/slider…) en édition template.
 * Toujours la couleur catalogues — pas de couleur par entrée.
 */
function em_wp_admin_catalog_choice_switch_color(string $catalog_slug): string
{
    unset($catalog_slug);

    return em_wp_admin_hub_brand_accent_color();
}

/**
 * Module catalogue associé à un identifiant rubrique (hero, slider, video…).
 */
function em_wp_admin_catalog_part_to_module_slug(string $catalog_part): string
{
    $catalog_part = sanitize_key($catalog_part);

    if ($catalog_part !== ''
        && function_exists('em_wp_custom_catalog_is_module')
        && em_wp_custom_catalog_is_module($catalog_part)) {
        return $catalog_part;
    }

    static $map = [
        'hero'    => 'heros',
        'slider'  => 'sliders',
        'video'   => 'videos',
        'stream'  => 'streams',
        'social'  => 'socials',
        'top-bar' => 'top-bars',
        'release' => 'releases',
        'cta'     => 'ctas',
        'footer'  => 'footers',
    ];

    return (string) ($map[$catalog_part] ?? '');
}

/**
 * URL admin d'édition d'une entrée catalogue (depuis une rubrique template).
 */
function em_wp_admin_catalog_entry_edit_url(string $catalog_part, string $catalog_slug): string
{
    $catalog_slug = sanitize_key($catalog_slug);

    if ($catalog_slug === '') {
        return '';
    }

    $module_slug = em_wp_admin_catalog_part_to_module_slug($catalog_part);

    if ($module_slug === '' || !function_exists('em_wp_catalog_hub_edit_page_slug_fn')) {
        return '';
    }

    $slug_fn = em_wp_catalog_hub_edit_page_slug_fn($module_slug);

    if ($slug_fn === null) {
        return '';
    }

    $page_slug = $slug_fn($catalog_slug);

    if ($page_slug === '') {
        return '';
    }

    return add_query_arg(['page' => $page_slug], admin_url('admin.php'));
}

/**
 * Sélecteur catalogue hero/slider (toggles exclusifs, style template live).
 *
 * @param array<string, string> $choices slug => label
 */
function em_wp_admin_render_catalog_slug_switcher(
    string $input_name,
    string $selected_slug,
    array $choices,
    string $group_label = '',
    string $catalog_part = ''
): void {
    $selected_slug = sanitize_key($selected_slug);
    $switch_group_id = wp_unique_id('em-wp-catalog-slug-switches-');
    $catalog_part = sanitize_key($catalog_part);
    $field_attrs = 'class="em-wp-header-admin__field em-wp-header-admin__field--catalog"';

    if ($catalog_part !== '') {
        $field_attrs .= ' data-catalog-part="' . esc_attr($catalog_part) . '"';
    }
    ?>
    <div <?php echo $field_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
        <?php if ($group_label !== '') { ?>
            <span class="em-wp-header-admin__catalog-switcher-label"><?php echo esc_html($group_label); ?></span>
        <?php } else { ?>
            <span class="em-wp-header-admin__catalog-switcher-label" aria-hidden="true"></span>
        <?php } ?>

        <div class="em-wp-header-admin__catalog-switcher-control">
        <?php if ($choices === []) { ?>
            <p class="description"><?php esc_html_e('Aucune entrée catalogue disponible.', 'em-wp'); ?></p>
            <input type="hidden" name="<?php echo esc_attr($input_name); ?>" value="">
        <?php } else {
            $catalog_accent = em_wp_admin_catalog_choice_switch_color('');
            ?>
            <div
                id="<?php echo esc_attr($switch_group_id); ?>"
                class="em-wp-hub__live-switches em-wp-admin-catalog-slug-switches"
                role="group"
                aria-label="<?php echo esc_attr($group_label !== '' ? $group_label : __('Sélection catalogue', 'em-wp')); ?>"
                style="--em-wp-live-color: <?php echo esc_attr($catalog_accent); ?>;"
            >
                <?php foreach ($choices as $slug => $label) {
                    $slug = sanitize_key((string) $slug);
                    if ($slug === '') {
                        continue;
                    }

                    $display_label = trim((string) $label);
                    if ($display_label === '') {
                        $display_label = $slug;
                    }

                    $wireframe_label = em_wp_admin_catalog_choice_switch_label($slug, $display_label);

                    $is_selected = ($slug === $selected_slug);
                    $switch_id = $switch_group_id . '-' . sanitize_html_class($slug);
                    $entry_url = $catalog_part !== ''
                        ? em_wp_admin_catalog_entry_edit_url($catalog_part, $slug)
                        : '';
                    $entry_open_label = sprintf(
                        /* translators: %s: catalog entry label */
                        __('Ouvrir %s dans le catalogue', 'em-wp'),
                        $display_label
                    );
                    ?>
                    <div class="em-wp-hub__live-switch">
                        <?php if ($entry_url !== '') { ?>
                            <a
                                href="<?php echo esc_url($entry_url); ?>"
                                class="em-wp-catalog-entry-open"
                                aria-label="<?php echo esc_attr($entry_open_label); ?>"
                                title="<?php echo esc_attr($entry_open_label); ?>"
                                data-entry-label="<?php echo esc_attr($display_label); ?>"
                            >
                                <span class="dashicons dashicons-external" aria-hidden="true"></span>
                            </a>
                        <?php } ?>
                        <label class="em-wp-hub__live-switch-control" for="<?php echo esc_attr($switch_id); ?>">
                            <span class="em-wp-hub__live-switch-label"><?php echo esc_html($display_label); ?></span>
                            <input
                                type="checkbox"
                                class="em-wp-hub__live-switch-input em-wp-admin-catalog-slug-switch"
                                id="<?php echo esc_attr($switch_id); ?>"
                                role="switch"
                                data-choice-slug="<?php echo esc_attr($slug); ?>"
                                data-choice-label="<?php echo esc_attr($display_label); ?>"
                                data-choice-wireframe-label="<?php echo esc_attr($wireframe_label); ?>"
                                <?php checked($is_selected); ?>
                                aria-checked="<?php echo $is_selected ? 'true' : 'false'; ?>"
                            >
                            <span class="em-wp-hub__live-switch-ui" aria-hidden="true"></span>
                        </label>
                    </div>
                <?php } ?>
            </div>

            <input
                type="hidden"
                class="em-wp-admin-catalog-slug-input"
                name="<?php echo esc_attr($input_name); ?>"
                value="<?php echo esc_attr($selected_slug); ?>"
            >
        <?php } ?>
        </div>
    </div>
    <?php
}

/**
 * Enqueue JS des toggles catalogue (hero/slider HEADER…).
 */
function em_wp_admin_enqueue_catalog_slug_switch_assets(): void
{
    em_wp_admin_hub_cards_enqueue_assets();

    if (!wp_script_is('em-wp-admin-confirm-modal', 'registered')) {
        wp_register_script(
            'em-wp-admin-confirm-modal',
            get_template_directory_uri() . '/assets/admin/js/shared/confirm-modal.js',
            [],
            em_wp_admin_asset_version('assets/admin/js/shared/confirm-modal.js'),
            true
        );
    }

    if (!wp_script_is('em-wp-admin-module-form-dirty', 'registered')) {
        wp_register_script(
            'em-wp-admin-module-form-dirty',
            get_template_directory_uri() . '/assets/admin/js/shared/module-form-dirty.js',
            ['em-wp-admin-confirm-modal'],
            em_wp_admin_asset_version('assets/admin/js/shared/module-form-dirty.js'),
            true
        );
    }

    wp_enqueue_script('em-wp-admin-confirm-modal');
    wp_enqueue_script('em-wp-admin-module-form-dirty');

    wp_enqueue_script(
        'em-wp-admin-catalog-slug-switch',
        get_template_directory_uri() . '/assets/admin/js/shared/catalog-slug-switch.js',
        ['em-wp-admin-confirm-modal', 'em-wp-admin-module-form-dirty'],
        em_wp_admin_asset_version('assets/admin/js/shared/catalog-slug-switch.js'),
        true
    );

    $template_label = function_exists('em_wp_get_editing_template_label')
        ? (string) em_wp_get_editing_template_label()
        : '';
    $has_template_context = function_exists('em_wp_admin_has_template_context') && em_wp_admin_has_template_context();

    wp_localize_script(
        'em-wp-admin-catalog-slug-switch',
        'EmWpCatalogEntryOpen',
        [
            'hasTemplateContext' => $has_template_context,
            'quitEndpoint'       => admin_url('admin.php'),
            'quitNonce'          => wp_create_nonce('em_wp_quit_editing_nav'),
            'templateLabel'      => $template_label,
            'strings'            => [
                'openConfirm'         => __('Tu vas quitter l\'édition en cours pour ouvrir « %s » dans le catalogue.', 'em-wp'),
                'openConfirmTemplate' => __('Tu vas quitter l\'édition du template « %1$s » pour ouvrir « %2$s » dans le catalogue.', 'em-wp'),
                'confirmOpen'         => __('Ouvrir le catalogue', 'em-wp'),
                'confirmSaveOpen'     => __('Enregistrer & Ouvrir', 'em-wp'),
                'stay'                => __('Rester', 'em-wp'),
                'saveConfirm'         => __('Enregistrer la configuration actuelle et continuer ?', 'em-wp'),
                'saveLabel'           => __('Enregistrer', 'em-wp'),
                'saveCancel'          => __('Annuler', 'em-wp'),
            ],
        ]
    );
}
