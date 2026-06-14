<?php
/**
 * Composants UI partagés — grilles de cartes sommaire (Accueil, Catalogues, Templates…).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

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
        ['em-wp-admin-module-common', 'dashicons'],
        em_wp_admin_asset_version('assets/admin/css/shared/hub-cards.css')
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
 * En-tête sommaire partagé (Hello + avatar + description + flèche).
 */
function em_wp_admin_hub_render_sommaire_header(string $description, string $icon_class = 'dashicons-dashboard', bool $description_allows_html = false): void
{
    $icon_class = trim($icon_class);

    if ($icon_class !== '' && !str_contains($icon_class, 'dashicons ')) {
        $icon_class = 'dashicons ' . $icon_class;
    }

    $greeting_name = em_wp_admin_hub_greeting_name();
    ?>
    <h1 class="em-wp-hub__greeting">
        <span class="<?php echo esc_attr($icon_class); ?> em-wp-hub__greeting-icon" aria-hidden="true"></span>
        <span class="em-wp-hub__greeting-text">
            <?php
            if ($greeting_name !== '') {
                printf(
                    /* translators: %s: admin first name */
                    esc_html__('Hello %s', 'em-wp'),
                    esc_html($greeting_name)
                );
            } else {
                esc_html_e('Hello', 'em-wp');
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

    <?php em_wp_admin_render_template_editing_banner(); ?>

    <div class="em-wp-hub__intro">
        <p class="description em-wp-hub__intro-text"><?php
            if ($description_allows_html) {
                echo wp_kses($description, ['strong' => ['class' => true]]);
            } else {
                echo esc_html($description);
            }
        ?></p>
        <span class="em-wp-hub__intro-arrow" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11 4v11.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M6 12.5 11 17.5 16 12.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
    </div>
    <?php
}

/**
 * Rendu du titre d'une carte (icône dashicons + libellé).
 */
function em_wp_admin_hub_render_card_title(string $title, string $icon_class): void
{
    $icon_class = trim($icon_class);

    if ($icon_class !== '' && !str_contains($icon_class, 'dashicons ')) {
        $icon_class = 'dashicons ' . $icon_class;
    }
    ?>
    <h2 class="em-wp-hub__card-title">
        <span class="<?php echo esc_attr($icon_class); ?> em-wp-hub__card-title-icon" aria-hidden="true"></span>
        <span class="em-wp-hub__card-title-label"><?php echo esc_html($title); ?></span>
    </h2>
    <?php
}

/**
 * Rendu d'un bouton d'action pill (lien).
 */
function em_wp_admin_hub_render_action_link(string $url, string $label, string $icon_class): void
{
    $icon_class = trim($icon_class);

    if ($icon_class !== '' && !str_contains($icon_class, 'dashicons ')) {
        $icon_class = 'dashicons ' . $icon_class;
    }
    ?>
    <a class="em-wp-hub__action" href="<?php echo esc_url($url); ?>">
        <span class="em-wp-hub__action-inner">
            <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
            <span class="em-wp-hub__action-label"><?php echo esc_html($label); ?></span>
        </span>
    </a>
    <?php
}

/**
 * Bouton secondaire désactivé (cartes « Nouveau … », prochaine étape).
 */
function em_wp_admin_hub_render_disabled_action(string $label, string $icon_class = 'dashicons dashicons-plus-alt2'): void
{
    ?>
    <button type="button" class="em-wp-hub__action em-wp-hub__action--secondary" disabled title="<?php esc_attr_e('Prochaine étape', 'em-wp'); ?>">
        <span class="em-wp-hub__action-inner">
            <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
            <span class="em-wp-hub__action-label"><?php echo esc_html($label); ?></span>
        </span>
    </button>
    <?php
}

/**
 * Pastille badge générique.
 */
function em_wp_admin_hub_render_status_badge(string $text, string $color, bool $in_card = false, bool $uppercase = false): void
{
    $classes = 'em-wp-hub__live';

    if ($uppercase) {
        $classes .= ' em-wp-hub__live--uppercase';
    }

    if ($in_card) {
        $classes .= ' em-wp-hub__live--in-card';
    }
    ?>
    <p
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
    </p>
    <?php
}

/**
 * Pastille « template actif sur le site ».
 */
function em_wp_admin_hub_render_live_template_badge(string $active_label, string $active_color, bool $in_card = false): void
{
    $classes = 'em-wp-hub__live em-wp-hub__live--uppercase';

    if ($in_card) {
        $classes .= ' em-wp-hub__live--in-card';
    }
    ?>
    <p
        class="<?php echo esc_attr($classes); ?>"
        role="status"
        style="--em-wp-live-color: <?php echo esc_attr($active_color); ?>;"
    >
        <span class="em-wp-hub__live-indicator" aria-hidden="true">
            <span class="em-wp-hub__live-dot"></span>
        </span>
        <span class="em-wp-hub__live-text">
            <?php esc_html_e('Ton site utilise actuellement le template :', 'em-wp'); ?>
            <strong class="em-wp-hub__live-template"><?php echo esc_html($active_label); ?></strong>
        </span>
    </p>
    <?php
}

/**
 * Pastille « template live » compacte (carte template individuelle).
 */
function em_wp_admin_hub_render_template_live_badge(string $label, string $color): void
{
    em_wp_admin_hub_render_status_badge(
        sprintf(
            /* translators: %s: template label */
            __('Live sur le site : %s', 'em-wp'),
            $label
        ),
        $color,
        true,
        true
    );
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
 * Bandeau template live + switches pour changer le template actif sur le site.
 */
function em_wp_admin_hub_render_live_template_switcher(string $redirect_page = ''): void
{
    if (!function_exists('em_wp_template_registry') || !function_exists('em_wp_get_active_template_slug')) {
        return;
    }

    $registry = em_wp_template_registry();
    $active_slug = em_wp_get_active_template_slug();
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    $active_color = em_wp_get_template_color($active_slug);

    if ($redirect_page === '' && function_exists('em_wp_admin_template_choice_page_slug')) {
        $redirect_page = em_wp_admin_template_choice_page_slug();
    }
    ?>
    <div
        class="em-wp-hub__live-bar"
        data-active-slug="<?php echo esc_attr($active_slug); ?>"
    >
        <?php em_wp_admin_hub_render_live_template_badge($active_label, $active_color, false); ?>

        <?php if (count($registry) > 1) { ?>
            <div
                class="em-wp-hub__live-switches"
                role="group"
                aria-label="<?php esc_attr_e('Template actif sur le site', 'em-wp'); ?>"
            >
                <?php foreach ($registry as $slug => $definition) {
                    $label = mb_strtoupper((string) ($definition['label'] ?? $slug));
                    $color = em_wp_get_template_color($slug);
                    $is_active = ($slug === $active_slug);
                    $switch_id = 'em-wp-hub-live-switch-' . sanitize_html_class($slug);
                    ?>
                    <label
                        class="em-wp-hub__live-switch"
                        for="<?php echo esc_attr($switch_id); ?>"
                        style="--em-wp-live-color: <?php echo esc_attr($color); ?>;"
                    >
                        <span class="em-wp-hub__live-switch-label"><?php echo esc_html($label); ?></span>
                        <input
                            type="checkbox"
                            class="em-wp-hub__live-switch-input"
                            id="<?php echo esc_attr($switch_id); ?>"
                            role="switch"
                            data-template-slug="<?php echo esc_attr($slug); ?>"
                            data-template-label="<?php echo esc_attr($label); ?>"
                            <?php checked($is_active); ?>
                            aria-checked="<?php echo $is_active ? 'true' : 'false'; ?>"
                        >
                        <span class="em-wp-hub__live-switch-ui" aria-hidden="true"></span>
                    </label>
                <?php } ?>
            </div>

            <form
                id="em-wp-hub-set-live-template-form"
                class="em-wp-hub__live-switch-form"
                method="post"
                action=""
            >
                <?php wp_nonce_field('em_wp_template_set_active'); ?>
                <input type="hidden" name="em_wp_template_action" value="set_active">
                <input type="hidden" name="em_wp_template_redirect_page" value="<?php echo esc_attr($redirect_page); ?>">
                <input type="hidden" name="em_wp_template_active_slug" value="<?php echo esc_attr($active_slug); ?>">
            </form>
        <?php } ?>
    </div>
    <?php
}
