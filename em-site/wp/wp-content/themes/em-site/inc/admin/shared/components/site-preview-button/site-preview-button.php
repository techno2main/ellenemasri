<?php
/**
 * Bouton mutualise d'enregistrement avant passage par la preview.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL de preview site pour le template fourni (ou template en edition courant).
 */
function em_site_admin_site_preview_url(string $template_slug = ''): string
{
    if (!function_exists('em_site_template_preview_url')) {
        return '';
    }

    $slug = $template_slug !== ''
        ? $template_slug
        : (function_exists('em_site_get_editing_template_slug') ? em_site_get_editing_template_slug() : '');

    if ($slug === '') {
        return '';
    }

    return (string) em_site_template_preview_url($slug);
}

/**
 * Enqueue des assets partages du bouton d'enregistrement/preview.
 */
function em_site_admin_site_preview_button_enqueue_assets(string $hook_suffix): void
{
    unset($hook_suffix);

    if (!is_admin() || !function_exists('em_site_admin_is_em_site_screen') || !em_site_admin_is_em_site_screen()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'em-site-admin-site-preview-button',
        $theme_uri . '/assets/admin/shared/css/navigation/site-preview-button.css',
        [],
        em_site_admin_asset_version('assets/admin/shared/css/navigation/site-preview-button.css')
    );

    wp_enqueue_script(
        'em-site-admin-site-preview-button',
        $theme_uri . '/assets/admin/shared/js/navigation/site-preview-button.js',
        [],
        em_site_admin_asset_version('assets/admin/shared/js/navigation/site-preview-button.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_site_admin_site_preview_button_enqueue_assets', 40);

/**
 * Rendu du bouton d'enregistrement mutualise.
 *
 * @param array<string,mixed> $args Arguments d'affichage.
 */
function em_site_admin_render_site_preview_button(array $args = []): void
{
    $template_slug = isset($args['template_slug']) ? (string) $args['template_slug'] : '';
    if ($template_slug === '' && function_exists('em_site_get_editing_template_slug')) {
        $template_slug = (string) em_site_get_editing_template_slug();
    }
    $href = em_site_admin_site_preview_url($template_slug);

    if ($href === '') {
        return;
    }

    $has_pending_changes = !empty($args['initial_dirty']);

    $classes = isset($args['class']) ? trim((string) $args['class']) : '';
    $label = isset($args['label']) && (string) $args['label'] !== ''
        ? (string) $args['label']
        : __('ENREGISTRER LES MODIFICATIONS', 'em-site');
    $title = isset($args['title']) && (string) $args['title'] !== ''
        ? (string) $args['title']
        : __('Enregistrer les modifications puis ouvrir la prévisualisation dans un nouvel onglet', 'em-site');
    $icon_class = isset($args['icon_class']) && (string) $args['icon_class'] !== ''
        ? (string) $args['icon_class']
        : 'dashicons dashicons-external';

    $full_class = trim('em-site-site-preview-btn ' . ($has_pending_changes ? '' : 'is-disabled') . ' ' . $classes);
    ?>
    <a
        class="<?php echo esc_attr($full_class); ?>"
        href="<?php echo esc_url($href); ?>"
        target="_blank"
        title="<?php echo esc_attr($title); ?>"
        data-em-site-site-preview-btn="1"
        data-em-site-template-slug="<?php echo esc_attr($template_slug); ?>"
        data-em-site-initial-dirty="<?php echo $has_pending_changes ? '1' : '0'; ?>"
        aria-disabled="<?php echo $has_pending_changes ? 'false' : 'true'; ?>"
        <?php if (!$has_pending_changes) : ?>tabindex="-1"<?php endif; ?>
    >
        <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
        <span><?php echo esc_html($label); ?></span>
    </a>
    <?php
}
