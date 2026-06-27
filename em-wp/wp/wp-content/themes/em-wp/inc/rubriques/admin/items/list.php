<?php
/**
 * Liste des footers (items) d'une rubrique (V4).
 *
 * Chaque footer est édité en une seule étape (structure + contenu + couleurs +
 * aperçu temps réel) via le builder. Plus un formulaire « Ajouter un footer ».
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Affiche la section des footers d'un type.
 */
function em_wp_v4_render_items_section(string $type_slug): void
{
    $items = em_wp_v4_get_items($type_slug);
    $open_item = sanitize_key((string) ($_GET['item'] ?? ''));
    ?>
    <div class="em-v4-items">
        <?php if ($items === []) : ?>
            <p class="description"><?php esc_html_e('Aucun footer pour le moment. Créez-en un ci-dessous.', 'em-wp'); ?></p>
        <?php else : ?>
            <?php foreach ($items as $slug => $label) : ?>
                <?php em_wp_v4_render_footer_item($type_slug, (string) $slug, (string) $label, $open_item === $slug); ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php em_wp_v4_render_create_footer_form($type_slug); ?>
    </div>
    <?php
}

/**
 * Un footer repliable édité en une seule étape (structure + contenu).
 */
function em_wp_v4_render_footer_item(string $type_slug, string $item_slug, string $label, bool $open): void
{
    $is_default = ($item_slug === 'default');
    $type_label = (string) (em_wp_rubrique_type_get($type_slug)['label'] ?? mb_strtoupper($type_slug));
    $target = em_wp_v4_item_form_id($type_slug, $item_slug) . '-label';
    ?>
    <details class="em-v4-collapse em-v4-item" <?php echo $open ? 'open' : ''; ?>>
        <summary class="em-v4-collapse__summary">
            <span class="em-v4-collapse__chevron" aria-hidden="true"></span>
            <span class="dashicons dashicons-align-center"></span>
            <strong class="em-v4-item__title">
                <span class="em-v4-item__prefix"><?php echo esc_html($type_label); ?></span>
                <span class="em-v4-item__name"><?php echo esc_html($label); ?></span>
            </strong>
            <button type="button" class="em-v4-item__edit" data-target="<?php echo esc_attr($target); ?>" title="<?php esc_attr_e('Renommer', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Renommer', 'em-wp'); ?>">
                <span class="dashicons dashicons-edit"></span>
            </button>
            <input type="text" class="em-v4-item__nameinput" data-target="<?php echo esc_attr($target); ?>" value="<?php echo esc_attr($label); ?>" hidden>
            <?php if ($is_default) : ?>
                <span class="em-v4-badge em-v4-badge--default"><?php esc_html_e('Default', 'em-wp'); ?></span>
            <?php endif; ?>
        </summary>
        <div class="em-v4-collapse__body">
            <?php em_wp_v4_render_item_builder($type_slug, $item_slug); ?>
            <div class="em-v4-item__footeractions">
                <?php em_wp_v4_render_duplicate_footer($type_slug, $item_slug, $label); ?>
                <?php if (!$is_default) : ?>
                    <?php em_wp_v4_render_delete_footer($type_slug, $item_slug, $label); ?>
                <?php endif; ?>
            </div>
        </div>
    </details>
    <?php
    em_wp_v4_render_rename_script();
}

/**
 * Script (une fois) : édition inline du nom d'un footer depuis l'en-tête.
 *
 * Le crayon affiche un champ ; la saisie (forcée en MAJUSCULES) met à jour le
 * nom affiché et le champ caché du builder. L'enregistrement persiste le nom.
 */
function em_wp_v4_render_rename_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    (function () {
        function stop(e) { e.preventDefault(); e.stopPropagation(); }

        document.addEventListener('click', function (e) {
            var pen = e.target.closest('.em-v4-item__edit');
            if (!pen) { return; }
            stop(e);
            var summary = pen.closest('summary');
            var name = summary.querySelector('.em-v4-item__name');
            var input = summary.querySelector('.em-v4-item__nameinput');
            if (!input) { return; }
            input.hidden = false;
            if (name) { name.hidden = true; }
            input.focus();
            input.select();
        });

        document.addEventListener('input', function (e) {
            var input = e.target.closest('.em-v4-item__nameinput');
            if (!input) { return; }
            input.value = input.value.toUpperCase();
            var summary = input.closest('summary');
            var name = summary.querySelector('.em-v4-item__name');
            if (name) { name.textContent = input.value; }
            var target = document.getElementById(input.getAttribute('data-target'));
            if (target) { target.value = input.value; }
        });

        document.addEventListener('keydown', function (e) {
            var input = e.target.closest('.em-v4-item__nameinput');
            if (!input) { return; }
            if (e.key === 'Enter' || e.key === 'Escape') { stop(e); input.blur(); }
        });

        document.addEventListener('blur', function (e) {
            var input = e.target.closest ? e.target.closest('.em-v4-item__nameinput') : null;
            if (!input) { return; }
            input.hidden = true;
            var summary = input.closest('summary');
            var name = summary.querySelector('.em-v4-item__name');
            if (name) { name.hidden = false; }
        }, true);

        document.addEventListener('mousedown', function (e) {
            if (e.target.closest('.em-v4-item__nameinput')) { e.stopPropagation(); }
        });
        document.addEventListener('click', function (e) {
            if (e.target.closest('.em-v4-item__nameinput')) { e.preventDefault(); e.stopPropagation(); }
        });
    })();
    </script>
    <?php
}

/**
 * Formulaire « Dupliquer ce footer » (copie + nouveau nom).
 */
function em_wp_v4_render_duplicate_footer(string $type_slug, string $item_slug, string $label): void
{
    ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-v4-dupform">
        <?php wp_nonce_field('em_wp_v4_duplicate_item'); ?>
        <input type="hidden" name="action" value="em_wp_v4_duplicate_item">
        <input type="hidden" name="type" value="<?php echo esc_attr($type_slug); ?>">
        <input type="hidden" name="item" value="<?php echo esc_attr($item_slug); ?>">
        <input type="text" name="item_label" class="em-v4-dupform__name" value="<?php echo esc_attr($label . ' COPIE'); ?>" placeholder="<?php esc_attr_e('Nouveau nom', 'em-wp'); ?>" required>
        <button type="submit" class="button">
            <span class="dashicons dashicons-admin-page"></span> <?php esc_html_e('Dupliquer', 'em-wp'); ?>
        </button>
    </form>
    <?php
}

/**
 * Bouton « Supprimer ce footer » (double confirmation mutualisée).
 */
function em_wp_v4_render_delete_footer(string $type_slug, string $item_slug, string $label): void
{
    ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-v4-deleteform">
        <?php wp_nonce_field('em_wp_v4_delete_item'); ?>
        <input type="hidden" name="action" value="em_wp_v4_delete_item">
        <input type="hidden" name="type" value="<?php echo esc_attr($type_slug); ?>">
        <input type="hidden" name="item" value="<?php echo esc_attr($item_slug); ?>">
        <button type="button" class="button-link-delete em-v4-delete" data-label="<?php echo esc_attr($label); ?>">
            <span class="dashicons dashicons-trash"></span> <?php esc_html_e('Supprimer ce footer', 'em-wp'); ?>
        </button>
    </form>
    <?php
    em_wp_v4_render_delete_script();
}

/**
 * Script (une fois) : confirme la suppression d'un footer puis soumet.
 */
function em_wp_v4_render_delete_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.em-v4-delete');
        if (!btn || !window.EmWpAdminConfirm) { return; }
        e.preventDefault();
        var form = btn.closest('form');
        window.EmWpAdminConfirm.confirmDelete(function () { form.submit(); }, {
            title: '<?php echo esc_js(__('Supprimer le footer', 'em-wp')); ?>',
            message: '<?php echo esc_js(__('Supprimer définitivement « ', 'em-wp')); ?>' + (btn.getAttribute('data-label') || '') + ' » ?',
            acknowledgeLabel: '<?php echo esc_js(__('Je confirme la suppression de ce footer.', 'em-wp')); ?>',
            confirmLabel: '<?php echo esc_js(__('Supprimer définitivement', 'em-wp')); ?>'
        });
    });
    </script>
    <?php
}

/**
 * Formulaire « Ajouter un footer ».
 */
function em_wp_v4_render_create_footer_form(string $type_slug): void
{
    ?>
    <details class="em-v4-collapse em-v4-create">
        <summary class="em-v4-collapse__summary">
            <span class="em-v4-collapse__chevron" aria-hidden="true"></span>
            <span class="dashicons dashicons-plus-alt2"></span>
            <strong><?php esc_html_e('Ajouter un footer', 'em-wp'); ?></strong>
        </summary>
        <div class="em-v4-collapse__body">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-v4-form">
                <?php wp_nonce_field('em_wp_v4_create_item'); ?>
                <input type="hidden" name="action" value="em_wp_v4_create_item">
                <input type="hidden" name="type" value="<?php echo esc_attr($type_slug); ?>">
                <p>
                    <label><?php esc_html_e('Nom du footer', 'em-wp'); ?><br>
                        <input type="text" name="item_label" class="regular-text" placeholder="<?php esc_attr_e('Ex. Footer Default', 'em-wp'); ?>" required>
                    </label>
                </p>
                <p><button type="submit" class="button"><?php esc_html_e('Créer le footer', 'em-wp'); ?></button></p>
            </form>
        </div>
    </details>
    <?php
}
