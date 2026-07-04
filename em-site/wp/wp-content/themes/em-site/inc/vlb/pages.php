<?php

function mayami_render_visual_links_html_builder_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to access this page.', 'mayami'), 403);
    }

    $draft_id = isset($_GET['draft_id']) ? sanitize_text_field(wp_unslash($_GET['draft_id'])) : '';

    $builder_path = wp_parse_url(get_theme_file_uri('/visual-links-builder/admin/index.php'), PHP_URL_PATH);
    if (!is_string($builder_path) || $builder_path === '') {
        $builder_path = '/wp-content/themes/ellene-wp/visual-links-builder/admin/index.php';
    }

    $html_builder_url = add_query_arg(array(
        'wp_ajax_url' => admin_url('admin-ajax.php', 'relative'),
        'wp_nonce' => wp_create_nonce('mayami_visual_links_draft'),
        'visual_links_draft_id' => $draft_id,
    ), $builder_path);

    $selected_name = '';
    if ($draft_id !== '') {
        $store = mayami_get_visual_links_drafts_store();
        if (!empty($store[$draft_id]['name'])) {
            $selected_name = (string) $store[$draft_id]['name'];
        }
    }
    ?>
    <div class="wrap mayami-vlb-html-page">
        <h1>VISUAL LINKS BUILDER (VLB)</h1>
        <p>Utilisez ce builder pour ajouter des zones cliquables sur n'importe quel visuel.</p>
        <?php if ($selected_name !== '') : ?>
            <p><strong>Visuel ouvert :</strong> <?php echo esc_html($selected_name); ?></p>
        <?php endif; ?>
        <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;overflow:hidden;">
            <iframe
                src="<?php echo esc_url($html_builder_url); ?>"
                title="VISUAL LINKS BUILDER (VLB)"
                style="width:100%;height:calc(100vh - 210px);min-height:760px;border:0;display:block;"
            ></iframe>
        </div>
    </div>
    <?php
}

function mayami_render_visual_links_preview_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to access this page.', 'mayami'), 403);
    }

    $draft_id = isset($_GET['draft_id']) ? sanitize_text_field(wp_unslash($_GET['draft_id'])) : '';
    $builder_url = admin_url('admin.php?page=mayami_visual_links_builder' . ($draft_id ? '&draft_id=' . urlencode($draft_id) : ''));
    ?>
    <div class="wrap mayami-vlb-preview-page" style="padding:0;margin:0;">
        <div id="previewContainer" style="position:relative;width:100%;height:100vh;overflow:auto;"></div>
        <script>
        (function() {
            const previewHtml = sessionStorage.getItem('mayami_vlb_preview_html');
            const container = document.getElementById('previewContainer');

            if (!previewHtml) {
                container.innerHTML = '<div style="padding:40px;text-align:center;"><h2>Aucune preview disponible</h2><p><a href="<?php echo esc_url($builder_url); ?>" class="button button-primary">Retour au builder</a></p></div>';
                return;
            }

            sessionStorage.removeItem('mayami_vlb_preview_html');

            const iframe = document.createElement('iframe');
            iframe.style.cssText = 'width:100%;height:100%;border:0;display:block;';
            container.appendChild(iframe);

            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(previewHtml);
            iframe.contentWindow.document.close();
        })();
        </script>
    </div>
    <?php
}

function mayami_render_visual_links_drafts_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to access this page.', 'mayami'), 403);
    }

    $store = mayami_get_visual_links_drafts_store();
    uasort($store, function($a, $b) {
        return strcmp((string) ($b['updated_at'] ?? ''), (string) ($a['updated_at'] ?? ''));
    });
    ?>
    <div class="wrap mayami-vlb-drafts-page">
        <style>
            .mayami-delete-modal-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.58);
                z-index: 100000;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 16px;
            }
            .mayami-delete-modal-backdrop.is-open { display: flex; }
            .mayami-delete-modal {
                width: min(520px, 100%);
                background: #fff;
                border-radius: 12px;
                border: 1px solid #d0d7e2;
                box-shadow: 0 18px 42px rgba(15, 23, 42, 0.22);
                overflow: hidden;
            }
            .mayami-delete-modal__head {
                padding: 16px 18px 10px;
                font-size: 19px;
                font-weight: 700;
                color: #0f172a;
            }
            .mayami-delete-modal__body {
                padding: 0 18px 16px;
                color: #334155;
                font-size: 14px;
                line-height: 1.45;
            }
            .mayami-delete-modal__actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                padding: 14px 18px 16px;
                border-top: 1px solid #e2e8f0;
                background: #f8fafc;
            }
        </style>
        <h1>Liste des visuels</h1>
        <p>Ouvrez un visuel existant pour reprendre l'edition dans Visual Links Builder.</p>
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=mayami_visual_links_builder_new')); ?>" class="button button-primary">Nouveau visuel</a>
        </p>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Nom du visuel</th>
                    <th>Dernière mise à jour</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($store)) : ?>
                    <tr>
                        <td colspan="3">Aucun visuel enregistre pour le moment.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($store as $draft) : ?>
                        <tr>
                            <td><?php echo esc_html((string) ($draft['name'] ?? 'Sans nom')); ?></td>
                            <td><?php echo esc_html((string) ($draft['updated_at'] ?? '')); ?></td>
                            <td>
                                <a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=mayami_visual_links_builder&draft_id=' . rawurlencode((string) ($draft['id'] ?? '')))); ?>">
                                    Ouvrir
                                </a>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-left:8px;">
                                    <?php wp_nonce_field('mayami_delete_visual_links_draft'); ?>
                                    <input type="hidden" name="action" value="mayami_delete_visual_links_draft">
                                    <input type="hidden" name="draft_id" value="<?php echo esc_attr((string) ($draft['id'] ?? '')); ?>">
                                    <button
                                        type="submit"
                                        class="button button-link-delete mayami-delete-draft-btn"
                                        data-draft-name="<?php echo esc_attr((string) ($draft['name'] ?? 'ce visuel')); ?>"
                                    >
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mayami-delete-modal-backdrop" id="mayamiDeleteModalBackdrop" aria-hidden="true">
        <div class="mayami-delete-modal" role="dialog" aria-modal="true" aria-labelledby="mayamiDeleteModalTitle">
            <div class="mayami-delete-modal__head" id="mayamiDeleteModalTitle">Supprimer ce visuel ?</div>
            <div class="mayami-delete-modal__body" id="mayamiDeleteModalBody">
                Cette action est definitive et supprimera ce visuel de la liste.
            </div>
            <div class="mayami-delete-modal__actions">
                <button type="button" class="button" id="mayamiDeleteCancelBtn">Annuler</button>
                <button type="button" class="button button-primary" id="mayamiDeleteConfirmBtn">Supprimer</button>
            </div>
        </div>
    </div>
    <script>
    (function () {
        const modalBackdrop = document.getElementById('mayamiDeleteModalBackdrop');
        const modalBody = document.getElementById('mayamiDeleteModalBody');
        const cancelBtn = document.getElementById('mayamiDeleteCancelBtn');
        const confirmBtn = document.getElementById('mayamiDeleteConfirmBtn');
        const deleteButtons = Array.from(document.querySelectorAll('.mayami-delete-draft-btn'));
        let pendingForm = null;

        function closeModal() {
            pendingForm = null;
            modalBackdrop.classList.remove('is-open');
            modalBackdrop.setAttribute('aria-hidden', 'true');
        }

        function openModal(form, draftName) {
            pendingForm = form;
            modalBody.textContent = 'Supprimer definitivement "' + String(draftName || 'ce visuel') + '" ? Cette action est irreversible.';
            modalBackdrop.classList.add('is-open');
            modalBackdrop.setAttribute('aria-hidden', 'false');
            confirmBtn.focus();
        }

        deleteButtons.forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                const form = btn.closest('form');
                if (!form) { return; }
                openModal(form, btn.getAttribute('data-draft-name') || 'ce visuel');
            });
        });

        cancelBtn.addEventListener('click', closeModal);
        confirmBtn.addEventListener('click', () => {
            if (pendingForm) { pendingForm.submit(); }
        });
        modalBackdrop.addEventListener('click', (event) => {
            if (event.target === modalBackdrop) { closeModal(); }
        });
        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modalBackdrop.classList.contains('is-open')) { closeModal(); }
        });
    })();
    </script>
    <?php
}
