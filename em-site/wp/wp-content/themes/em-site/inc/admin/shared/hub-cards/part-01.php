<?php
/**
 * Composants UI partagés — grilles de cartes sommaire (Accueil, Catalogues, Templates…).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__DIR__) . '/hub-breadcrumb.php';

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

