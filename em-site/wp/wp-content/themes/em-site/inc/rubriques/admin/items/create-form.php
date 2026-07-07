<?php
/**
 * Ligne « Nouvelle Section » d'une rubrique (V4).
 *
 * Deux actions mutuellement exclusives (une case radio choisit l'action) :
 * créer une nouvelle section vierge, ou dupliquer une section existante.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Formulaire « Nouvelle Section » (créer / dupliquer).
 */
function em_wp_v4_render_create_footer_form(string $type_slug): void
{
    $n = em_wp_rubrique_type_nouns($type_slug);
    $label_uc = (string) (em_wp_rubrique_type_get($type_slug)['label'] ?? mb_strtoupper($type_slug));
    $items = em_wp_v4_get_items($type_slug);
    $action = esc_url(admin_url('admin-post.php'));
    $create_id = 'em-v4-create-' . $type_slug;
    ?>
    <div id="<?php echo esc_attr($create_id); ?>" class="em-v4-collapse em-v4-create em-v4-create--nochevron" hidden>
        <div class="em-v4-collapse__body em-v4-create__options">
            <form method="post" action="<?php echo $action; ?>" class="em-v4-form em-v4-create__row">
                <?php wp_nonce_field('em_wp_v4_create_item'); ?>
                <input type="hidden" name="action" value="em_wp_v4_create_item">
                <input type="hidden" name="type" value="<?php echo esc_attr($type_slug); ?>">
                <label class="em-v4-create__label">
                    <input type="radio" class="em-v4-create__radio" name="em-v4-create-mode-<?php echo esc_attr($type_slug); ?>" value="create" checked>
                    <span class="dashicons dashicons-welcome-add-page" aria-hidden="true"></span> <?php esc_html_e('Créer nouvelle section', 'em-wp'); ?>
                </label>
                <input type="text" name="item_label" class="regular-text em-v4-create__name" placeholder="<?php echo esc_attr(sprintf(__('Ex. %s Default', 'em-wp'), $label_uc)); ?>" required>
                <button type="submit" class="button button-primary"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Ajouter', 'em-wp'); ?></button>
            </form>
            <?php if ($items !== []) : ?>
            <form method="post" action="<?php echo $action; ?>" class="em-v4-form em-v4-create__row">
                <?php wp_nonce_field('em_wp_v4_duplicate_item'); ?>
                <input type="hidden" name="action" value="em_wp_v4_duplicate_item">
                <input type="hidden" name="type" value="<?php echo esc_attr($type_slug); ?>">
                <label class="em-v4-create__label">
                    <input type="radio" class="em-v4-create__radio" name="em-v4-create-mode-<?php echo esc_attr($type_slug); ?>" value="duplicate">
                    <span class="dashicons dashicons-admin-page" aria-hidden="true"></span> <?php esc_html_e('Dupliquer section', 'em-wp'); ?>
                </label>
                <select name="item" class="em-v4-create__select">
                    <?php foreach ($items as $slug => $label) : ?>
                        <option value="<?php echo esc_attr((string) $slug); ?>"><?php echo esc_html((string) $label); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="item_label" class="regular-text em-v4-create__name" placeholder="<?php esc_attr_e('Nouveau nom', 'em-wp'); ?>" required>
                <button type="submit" class="button"><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e('Dupliquer', 'em-wp'); ?></button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
    em_wp_v4_render_create_mode_script();
}

/**
 * Script (une fois) : la case radio choisit l'action (créer ou dupliquer) ;
 * la ligne non sélectionnée est désactivée pour n'autoriser qu'une action.
 */
function em_wp_v4_render_create_mode_script(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    ?>
    <script>
    (function () {
        function sync(box) {
            box.querySelectorAll('.em-v4-create__row').forEach(function (row) {
                var radio = row.querySelector('.em-v4-create__radio');
                if (!radio) { return; } // lignes sans radio (ex. Nouvelle Rubrique) : toujours actives
                var on = radio.checked;
                row.classList.toggle('is-off', !on);
                row.querySelectorAll('input:not(.em-v4-create__radio), select, button').forEach(function (el) { el.disabled = !on; });
            });
        }
        document.addEventListener('change', function (e) {
            var radio = e.target.closest && e.target.closest('.em-v4-create__radio');
            if (!radio) { return; }
            var box = radio.closest('.em-v4-create__options');
            if (!box) { return; }
            box.querySelectorAll('.em-v4-create__radio').forEach(function (o) { if (o !== radio) { o.checked = false; } });
            sync(box);
        });
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.em-v4-create__options').forEach(sync);
        });
    })();
    </script>
    <?php
}
