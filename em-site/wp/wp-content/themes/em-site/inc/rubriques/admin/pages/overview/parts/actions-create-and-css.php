<?php
function em_site_overview_render_delete_type_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.em-site-type-delete');
        if (!btn) { return; }
        e.preventDefault();
        e.stopPropagation();
        if (!window.EmWpAdminConfirm || !window.EmWpAdminConfirm.confirmDelete) { return; }

        var form = document.getElementById(btn.getAttribute('data-deleteform'));
        if (!form) { return; }

        var label = btn.getAttribute('data-label') || '';
        var message = '<?php echo esc_js(__('Supprimer définitivement la rubrique « ', 'em-site')); ?>'
            + label
            + '<?php echo esc_js(__(' » et toutes ses sections ?', 'em-site')); ?>';

        window.EmWpAdminConfirm.confirmDelete(function () { form.submit(); }, {
            title: btn.getAttribute('data-title') || '<?php echo esc_js(__('Supprimer', 'em-site')); ?>',
            message: message,
            acknowledgeLabel: btn.getAttribute('data-ack') || '<?php echo esc_js(__('Je confirme la suppression.', 'em-site')); ?>',
            confirmLabel: '<?php echo esc_js(__('Supprimer définitivement', 'em-site')); ?>'
        });
    });
    </script>
    <?php
}

/**
 * Bouton/formulaire « + Nouvelle Rubrique » (fin de liste).
 *
 * Permet de créer une rubrique personnalisée (nom + icône) sans code. La
 * rubrique démarre vide (apparence par défaut) et apparaît dans la liste.
 */
function em_site_overview_render_create_type(): void
{
    $icons = [
        'dashicons-screenoptions', 'dashicons-menu-alt3', 'dashicons-format-audio',
        'dashicons-share', 'dashicons-video-alt3', 'dashicons-album', 'dashicons-megaphone',
        'dashicons-star-filled', 'dashicons-heart', 'dashicons-images-alt2',
        'dashicons-list-view', 'dashicons-admin-links',
    ];
    ?>
    <details class="em-site-collapse em-site-create em-site-create--nochevron em-site-createtype">
        <summary class="em-site-collapse__summary">
            <span class="dashicons dashicons-plus-alt2"></span>
            <strong><?php esc_html_e('Nouvelle Rubrique', 'em-site'); ?></strong>
        </summary>
        <div class="em-site-collapse__body em-site-create__options">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-site-form em-site-create__row">
                <?php wp_nonce_field('em_site_create_type'); ?>
                <input type="hidden" name="action" value="em_site_create_type">
                <span class="em-site-create__label"><span class="dashicons dashicons-screenoptions" aria-hidden="true"></span> <?php esc_html_e('Nom de la rubrique', 'em-site'); ?></span>
                <input type="text" name="type_label" class="regular-text em-site-create__name" placeholder="<?php esc_attr_e('Ex. PARTENAIRES', 'em-site'); ?>" required>
                <span class="em-site-iconpick" role="radiogroup" aria-label="<?php esc_attr_e('Icône de la rubrique', 'em-site'); ?>">
                    <?php foreach ($icons as $i => $ic) : ?>
                        <label class="em-site-iconpick__opt" title="<?php echo esc_attr($ic); ?>">
                            <input type="radio" name="type_icon" value="<?php echo esc_attr($ic); ?>" <?php checked($i, 0); ?>>
                            <span class="dashicons <?php echo esc_attr($ic); ?>" aria-hidden="true"></span>
                        </label>
                    <?php endforeach; ?>
                </span>
                <button type="submit" class="button button-primary"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Créer la rubrique', 'em-site'); ?></button>
            </form>
        </div>
    </details>
    <?php
}

/**
 * Styles inline (autonome).
 */
function em_site_overview_render_styles(): void
{
    // Les styles de l'overview sont servis via wp_enqueue_style.
}


/**
 * CSS de PREVIEW ADMIN Rubriques lu depuis une source dédiée et explicitement
 * nommée "rubriques-preview", afin de séparer clairement les
 * responsabilités (admin preview vs styles des modules front).
 *
 * @return string CSS concaténé (sans balise <style>).
 */
function em_site_admin_rubriques_preview_css(): string
{
    static $css = null;

    if ($css !== null) {
        return $css;
    }

    $css = '';
    $base = get_template_directory() . '/assets/admin/css/rubriques-preview/';
    foreach (
        [
            'admin-preview-render-base.css',
            'admin-preview-render-media.css',
            'admin-preview-render-components.css',
            'admin-preview-render-header.css',
            'admin-preview-render-layout.css',
        ] as $file
    ) {
        $path = $base . $file;
        if (is_readable($path)) {
            $css .= (string) file_get_contents($path) . "\n";
        }
    }

    return $css;
}

/**
 * Compatibilité rétroactive: ancien nom conservé comme alias.
 *
 * @return string
 */
function em_site_rubriques_admin_render_css(): string
{
    return em_site_admin_rubriques_preview_css();
}

