<?php
/**
 * Composants UI partagés — grilles de cartes sommaire (Accueil, Catalogues, Templates…).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__DIR__, 2) . '/hub-breadcrumb/hub-breadcrumb.php';

/**
 * Enqueue CSS/JS communs aux pages sommaire à cartes.
 */
function em_site_admin_hub_cards_enqueue_assets(): void
{
    em_site_admin_enqueue_shared_assets();

    wp_enqueue_style('dashicons');

    wp_enqueue_style(
        'em-site-admin-hub-cards',
        get_template_directory_uri() . '/assets/admin/shared/css/hub-cards.css',
        ['em-site-admin-module-common', 'em-site-admin-live-badge', 'dashicons'],
        em_site_admin_asset_version('assets/admin/shared/css/hub-cards.css')
    );

    wp_enqueue_script(
        'em-site-admin-hub-sommaire-preview',
        get_template_directory_uri() . '/assets/admin/shared/js/preview/hub-sommaire-preview.js',
        [],
        em_site_admin_asset_version('assets/admin/shared/js/preview/hub-sommaire-preview.js'),
        true
    );

}

/**
 * Prénom (ou repli) de l'admin connecté pour les en-têtes sommaire.
 */
function em_site_admin_hub_greeting_name(): string
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
function em_site_admin_hub_intro_html_allowed_tags(): array
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
function em_site_admin_hub_sticky_head_open(): void
{
    echo '<div class="em-site-hub__sticky-head">';
}

/**
 * Ferme la zone sticky hub.
 */
function em_site_admin_hub_sticky_head_close(): void
{
    echo '</div>';
}

/**
 * En-tête sommaire partagé (avatar + description + flèche).
 */
function em_site_admin_hub_render_sommaire_header(
    string $description = '',
    string $icon_class = 'dashicons-dashboard',
    bool $description_allows_html = false,
    bool $show_template_banner = true,
    ?callable $context_banner_renderer = null,
    ?array $breadcrumb = null,
    bool $sticky_head = false
): void {
    if ($sticky_head) {
        em_site_admin_hub_sticky_head_open();
    }

    $icon_class = trim($icon_class);

    if ($icon_class !== '' && !str_contains($icon_class, 'dashicons ')) {
        $icon_class = 'dashicons ' . $icon_class;
    }

    $greeting_name = em_site_admin_hub_greeting_name();

    $breadcrumb_html = '';

    if ($breadcrumb !== null && $breadcrumb !== []) {
        $breadcrumb_html = em_site_admin_hub_breadcrumb_html($breadcrumb);
    } elseif ($breadcrumb === null) {
        $auto_crumbs = em_site_admin_hub_resolve_breadcrumb_crumbs();
        if ($auto_crumbs !== []) {
            $breadcrumb_html = em_site_admin_hub_breadcrumb_html($auto_crumbs);
        }
    }
    $initial_dirty = false;
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $admin_page = sanitize_key((string) ($_GET['page'] ?? ''));
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $updated = sanitize_key((string) ($_GET['updated'] ?? ''));
    if ($admin_page === 'em-rubriques-overview' && $updated === 'saved') {
        $initial_dirty = true;
    }
    ?>
    <h1 class="em-site-hub__greeting">
        <span class="<?php echo esc_attr($icon_class); ?> em-site-hub__greeting-icon" aria-hidden="true"></span>
        <span class="em-site-hub__greeting-text">
            <?php
            if ($greeting_name !== '') {
                printf(
                    /* translators: %s: admin first name */
                    esc_html__('%s', 'em-site'),
                    esc_html($greeting_name)
                );
            } else {
                esc_html_e('em-site');
            }
            ?>
        </span>
        <?php
        if (function_exists('em_site_admin_render_site_preview_button')) {
            em_site_admin_render_site_preview_button([
                'class' => 'em-site-site-preview-btn--top',
                'icon_class' => 'dashicons dashicons-external',
                'title' => __('Enregistrer les modifications puis ouvrir la prévisualisation dans un nouvel onglet', 'em-site'),
                'label' => __('ENREGISTRER LES MODIFICATIONS', 'em-site'),
                'initial_dirty' => $initial_dirty,
            ]);
        }
        ?>
        <?php
        echo get_avatar(
            get_current_user_id(),
            40,
            '',
            $greeting_name !== '' ? sprintf(__('Avatar de %s', 'em-site'), $greeting_name) : __('Avatar', 'em-site'),
            ['class' => 'em-site-hub__greeting-avatar']
        );
        ?>
    </h1>

    <?php if ($breadcrumb_html !== '') { ?>
        <div class="em-site-hub__breadcrumb"><?php echo wp_kses($breadcrumb_html, em_site_admin_hub_intro_html_allowed_tags()); ?></div>
    <?php } elseif ($description !== '') { ?>
        <p class="description em-site-hub__intro-text em-site-hub__breadcrumb"><?php
            if ($description_allows_html) {
                echo wp_kses($description, em_site_admin_hub_intro_html_allowed_tags());
            } else {
                echo esc_html($description);
            }
        ?></p>
    <?php } ?>

    <?php
    if (is_callable($context_banner_renderer)) {
        $context_banner_renderer();
    } elseif ($show_template_banner) {
        em_site_admin_render_template_editing_banner();
    }
    ?>
    <?php
}

/**
 * Rendu du titre d'une carte (icône dashicons + libellé).
 */
function em_site_admin_hub_render_card_title(string $title, string $icon_class, ?callable $after_icon = null, string $icon_color = ''): void
{
    $icon_class = trim($icon_class);
    $icon_style = $icon_color !== '' ? ' style="--em-site-card-accent: ' . esc_attr($icon_color) . ';"' : '';

    if ($icon_class !== '' && !str_contains($icon_class, 'dashicons ')) {
        $icon_class = 'dashicons ' . $icon_class;
    }
    ?>
    <h2 class="em-site-hub__card-title">
        <span class="<?php echo esc_attr($icon_class); ?> em-site-hub__card-title-icon"<?php echo $icon_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-hidden="true"></span>
        <?php
        if ($after_icon !== null) {
            $after_icon();
        }
        ?>
        <span class="em-site-hub__card-title-label"><?php echo esc_html($title); ?></span>
    </h2>
    <?php
}

/**
 * Pastille compacte — nombre d'entrées (cartes sommaire).
 */
function em_site_admin_hub_render_count_badge(int $count): void
{
    $count = max(0, $count);
    ?>
    <span
        class="em-site-hub__count-badge"
        aria-label="<?php echo esc_attr(sprintf(
            /* translators: %d: number of catalog items */
            _n('%d item', '%d items', $count, 'em-site'),
            $count
        )); ?>"
    ><?php echo esc_html((string) $count); ?></span>
    <?php
}

/**
 * Texte description carte catalogue (nom d'item en majuscules).
 */
function em_site_admin_hub_catalog_card_description_text(string $item_name, string $rubrique_name, string $module_slug = ''): string
{
    $rubrique = mb_strtoupper(trim($rubrique_name));

    return (string) sprintf(
        /* translators: 1: literal "items", 2: rubrique name */
        __('Liste des %1$s disponibles pour la rubrique %2$s.', 'em-site'),
        __('items', 'em-site'),
        $rubrique
    );
}

/**
 * Description carte catalogue — nom d'item en gras / majuscules.
 */

