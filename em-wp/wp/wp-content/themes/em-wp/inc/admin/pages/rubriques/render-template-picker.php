<?php
/**
 * Écran « Choix du template » (sans contexte d'édition).
 *
 * Réutilisé par la page Templates (list.php).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu du sélecteur de template (avant contexte explicite).
 */
function em_wp_admin_render_rubriques_template_picker(): void
{
    $registry = em_wp_template_registry();
    $active_slug = em_wp_get_active_template_slug();
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    $active_color = em_wp_get_template_color($active_slug);
    ?>
    <div class="wrap em-wp-rubriques-admin em-wp-rubriques-picker em-wp-admin-module">
        <p class="em-wp-rubriques-picker__back">
            <a href="<?php echo esc_url(em_wp_admin_dashboard_admin_url()); ?>">&larr; <?php esc_html_e('Retour à l’accueil', 'em-wp'); ?></a>
        </p>

        <h1><?php esc_html_e('Choix du Template', 'em-wp'); ?></h1>

        <p class="description em-wp-rubriques-picker__intro">
            <?php esc_html_e('Choisis le template que tu veux éditer. Les rubriques (TOP-BAR, HEADER, STREAM…) seront disponibles une fois ta sélection confirmée.', 'em-wp'); ?>
        </p>

        <p class="em-wp-rubriques-picker__live" role="status">
            <span class="em-wp-rubriques-picker__live-dot" style="background: <?php echo esc_attr($active_color); ?>;" aria-hidden="true"></span>
            <?php
            printf(
                esc_html__('Rappel — template actif sur le site : %s', 'em-wp'),
                '<strong>' . esc_html($active_label) . '</strong>'
            );
            ?>
        </p>

        <form class="em-wp-rubriques-picker__form" method="post" action="">
            <?php wp_nonce_field('em_wp_template_set_editing'); ?>
            <input type="hidden" name="em_wp_template_action" value="set_editing">
            <input type="hidden" name="em_wp_template_redirect_page" value="<?php echo esc_attr(em_wp_admin_rubriques_page_slug()); ?>">

            <p>
                <label for="em-wp-rubriques-editing-select" class="em-wp-rubriques-picker__label">
                    <?php esc_html_e('Template à éditer', 'em-wp'); ?>
                </label>
            </p>

            <select id="em-wp-rubriques-editing-select" name="em_wp_template_editing_slug" class="em-wp-rubriques-picker__select" required>
                <option value=""><?php esc_html_e('— Choisir un template —', 'em-wp'); ?></option>
                <?php foreach ($registry as $slug => $definition) { ?>
                    <option value="<?php echo esc_attr($slug); ?>" <?php selected($active_slug, $slug); ?>>
                        <?php echo esc_html((string) ($definition['label'] ?? $slug)); ?>
                    </option>
                <?php } ?>
            </select>

            <p class="em-wp-rubriques-picker__actions">
                <button type="submit" class="button button-primary button-hero">
                    <?php esc_html_e('Commencer l’édition', 'em-wp'); ?>
                </button>
            </p>
        </form>
    </div>
    <?php
}
