<?php
/**
 * Builder d'un item (V4) — lay-out + structure + contenu (une seule étape).
 *
 * 1) Lay-out : on choisit le nombre de COLONNES (1 à 4) et l'ALIGNEMENT de chaque
 *    colonne. 2) Contenu : dans chaque colonne d'une ligne, on ajoute un champ via
 *    « + » (type + libellé) et on saisit sa valeur. Couleurs globales au-dessus.
 *    Aperçu temps réel. Tout est enregistré en un seul bouton.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Identifiant de formulaire d'un item (partagé entre l'en-tête et le builder).
 */
function em_wp_v4_item_form_id(string $type, string $item): string
{
    return 'emv4-item-' . sanitize_html_class($type . '-' . $item);
}

/**
 * Affiche le builder complet d'un item (lay-out + structure + contenu).
 */
function em_wp_v4_render_item_builder(string $type, string $item): void
{
    $data = em_wp_v4_get_item($type, $item);
    [$global_fields, $content_fields] = em_wp_rubrique_split_global_fields($data['fields']);
    $content = em_wp_v4_get_item_content($type, $item);
    $layout = $data['layout'];
    // Aucune ligne tant qu'il n'y a aucun champ de contenu (item vierge).
    $row_count = $content_fields === [] ? 0 : em_wp_rubrique_layout_row_count($layout);
    $form_id = em_wp_v4_item_form_id($type, $item);

    $grid = [];
    foreach ($content_fields as $field) {
        $grid[(int) $field['row']][(int) $field['col']][] = $field;
    }
    ?>
    <div class="em-v4-builder" data-form="<?php echo esc_attr($form_id); ?>">
        <div class="em-v4-sticky">
            <div class="em-v4-savebar" hidden>
                <span class="em-v4-savebar__msg"><?php esc_html_e('Modifications non enregistrées', 'em-wp'); ?></span>
                <button type="submit" form="<?php echo esc_attr($form_id); ?>" class="button button-primary em-v4-savebar__btn"><?php esc_html_e('Enregistrer', 'em-wp'); ?></button>
            </div>
            <div class="em-v4-preview">
                <div class="em-v4-preview__head">
                    <button type="button" class="em-v4-preview__toggle" aria-pressed="false" title="<?php esc_attr_e('Afficher / masquer l’aperçu', 'em-wp'); ?>">
                        <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                        <span class="em-v4-preview__label"><?php echo esc_html(sprintf(__('Aperçu (%s)', 'em-wp'), $data['label'])); ?></span>
                    </button>
                    <button type="button" class="em-v4-preview__popout" title="<?php esc_attr_e('Ouvrir l’aperçu dans une nouvelle fenêtre', 'em-wp'); ?>">
                        <span class="dashicons dashicons-external" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="em-v4-livepreview" hidden></div>
            </div>
        </div>

        <form id="<?php echo esc_attr($form_id); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('em_wp_v4_save_item'); ?>
            <input type="hidden" name="action" value="em_wp_v4_save_item">
            <input type="hidden" name="type" value="<?php echo esc_attr($type); ?>">
            <input type="hidden" name="item" value="<?php echo esc_attr($item); ?>">
            <input type="hidden" name="structure" id="<?php echo esc_attr($form_id); ?>-structure" value="">
            <input type="hidden" name="item_label" id="<?php echo esc_attr($form_id); ?>-label" value="<?php echo esc_attr($data['label']); ?>">

            <?php if ($global_fields !== []) : ?>
                <details class="em-v4-collapse em-v4-builder__section">
                    <summary class="em-v4-collapse__summary">
                        <span class="em-v4-collapse__chevron"></span>
                        <strong><?php esc_html_e('Apparence', 'em-wp'); ?></strong>
                    </summary>
                    <div class="em-v4-collapse__body">
                        <div class="em-v4-appearance">
                            <?php em_wp_v4_render_appearance_lines($type, $item, $global_fields, $form_id, $content); ?>
                        </div>
                    </div>
                </details>
            <?php endif; ?>
        </form>

        <details class="em-v4-collapse em-v4-builder__section">
            <summary class="em-v4-collapse__summary">
                <span class="em-v4-collapse__chevron"></span>
                <strong><?php esc_html_e('Contenu', 'em-wp'); ?></strong>
            </summary>
            <div class="em-v4-collapse__body">
                <div class="em-v4-rows">
                    <?php for ($row = 1; $row <= $row_count; $row++) : ?>
                        <?php em_wp_v4_render_row($row, $layout, $grid[$row] ?? [], $content); ?>
                    <?php endfor; ?>
                </div>

                <p class="em-v4-builder__actions">
                    <button type="button" class="button em-v4-addrow"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Ajouter une ligne', 'em-wp'); ?></button>
                </p>
            </div>
        </details>

        <p class="em-v4-builder__actions em-v4-builder__save">
            <button type="submit" form="<?php echo esc_attr($form_id); ?>" class="button button-primary"><?php esc_html_e('Enregistrer', 'em-wp'); ?></button>
        </p>

        <?php em_wp_v4_render_templates(); ?>
    </div>
    <?php
    em_wp_v4_builder_assets();
}

/**
 * En-tête de ligne : nombre de colonnes + alignement de chaque colonne.
 *
 * Placé dans le résumé de la ligne ; les interactions n'ouvrent/ferment pas la
 * ligne (géré côté JS).
 *
 * @param array<string, mixed> $layout
 */
function em_wp_v4_render_row_layout(int $row, array $layout): void
{
    $columns = em_wp_rubrique_layout_columns_for($layout, $row);
    ?>
    <div class="em-v4-row__layout">
        <label class="em-v4-rowcols-label">
            <span><?php esc_html_e('Colonnes', 'em-wp'); ?></span>
            <select class="em-v4-rowcols">
                <?php for ($n = 1; $n <= em_wp_rubrique_max_columns(); $n++) : ?>
                    <option value="<?php echo (int) $n; ?>" <?php selected($columns, $n); ?>><?php echo (int) $n; ?></option>
                <?php endfor; ?>
            </select>
        </label>
        <div class="em-v4-row__aligns">
            <?php for ($i = 1; $i <= $columns; $i++) : ?>
                <?php em_wp_v4_render_align_select($i, em_wp_rubrique_layout_align_for($layout, $row, $i)); ?>
            <?php endfor; ?>
        </div>
    </div>
    <?php
}

/**
 * Sélecteur d'alignement d'une colonne : boutons-icônes (façon Word).
 *
 * Un input caché « em-v4-align__sel » porte la valeur (lue par le builder JS) ;
 * les boutons (gauche/centre/droite/justifié) basculent l'état actif.
 */
function em_wp_v4_render_align_select(int $index, string $value): void
{
    $value = em_wp_rubrique_valid_align($value);
    $icons = [
        'left'    => 'dashicons-editor-alignleft',
        'center'  => 'dashicons-editor-aligncenter',
        'right'   => 'dashicons-editor-alignright',
        'justify' => 'dashicons-editor-justify',
    ];
    ?>
    <div class="em-v4-align">
        <span class="em-v4-align__label"><?php printf(esc_html__('Colonne %d', 'em-wp'), $index); ?></span>
        <div class="em-v4-align__group" role="group">
            <input type="hidden" class="em-v4-align__sel" data-col="<?php echo (int) $index; ?>" value="<?php echo esc_attr($value); ?>">
            <?php foreach (em_wp_rubrique_alignments() as $key => $label) : ?>
                <button type="button" class="em-v4-align__btn<?php echo $value === $key ? ' is-active' : ''; ?>" data-align="<?php echo esc_attr($key); ?>" title="<?php echo esc_attr($label); ?>" aria-label="<?php echo esc_attr($label); ?>">
                    <span class="dashicons <?php echo esc_attr($icons[$key] ?? 'dashicons-editor-alignleft'); ?>"></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Une ligne : en-tête (colonnes + alignement) + N colonnes.
 *
 * @param array<string, mixed> $layout
 * @param array<int, array<int, array<string,mixed>>> $row_grid
 * @param array<string, mixed> $content
 */
function em_wp_v4_render_row(int $row, array $layout, array $row_grid, array $content = []): void
{
    $columns = em_wp_rubrique_layout_columns_for($layout, $row);
    ?>
    <details class="em-v4-row">
        <summary class="em-v4-row__summary">
            <span class="em-v4-row__drag dashicons dashicons-menu" title="<?php esc_attr_e('Glisser pour déplacer la ligne', 'em-wp'); ?>" aria-hidden="true"></span>
            <span class="em-v4-collapse__chevron"></span>
            <span class="em-v4-row__label" aria-hidden="true"></span>
            <?php em_wp_v4_render_row_layout($row, $layout); ?>
            <button type="button" class="em-v4-row__add" title="<?php esc_attr_e('Insérer une ligne en dessous', 'em-wp'); ?>"><span class="dashicons dashicons-plus-alt2"></span></button>
            <button type="button" class="em-v4-row__remove" title="<?php esc_attr_e('Supprimer la ligne', 'em-wp'); ?>">&times;</button>
        </summary>
        <div class="em-v4-row__cols">
            <?php for ($c = 1; $c <= $columns; $c++) : ?>
                <?php em_wp_v4_render_col($c, $row_grid[$c] ?? [], $content); ?>
            <?php endfor; ?>
        </div>
    </details>
    <?php
}

/**
 * Une colonne (zone de dépôt + bouton « + »).
 *
 * @param array<int, array<string,mixed>> $fields
 * @param array<string, mixed> $content
 */
function em_wp_v4_render_col(int $index, array $fields, array $content = []): void
{
    ?>
    <div class="em-v4-col" data-col="<?php echo (int) $index; ?>">
        <div class="em-v4-col__head"><?php printf(esc_html__('Colonne %d', 'em-wp'), $index); ?></div>
        <div class="em-v4-col__drop">
            <?php foreach ($fields as $field) : ?>
                <?php em_wp_v4_render_chip($field, $content); ?>
            <?php endforeach; ?>
        </div>
        <?php em_wp_v4_render_cell_add(); ?>
    </div>
    <?php
}

/**
 * Contrôle « + Ajouter » d'une colonne (type + libellé inline).
 */
function em_wp_v4_render_cell_add(): void
{
    ?>
    <div class="em-v4-celladd">
        <button type="button" class="em-v4-celladd__btn"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Ajouter un champ', 'em-wp'); ?></button>
        <div class="em-v4-celladd__form" hidden>
            <input type="text" class="em-v4-celladd__label" placeholder="<?php esc_attr_e('Libellé', 'em-wp'); ?>">
            <select class="em-v4-celladd__type">
                <?php foreach (em_wp_field_type_registry() as $ft_key => $ft) : ?>
                    <?php if (!in_array($ft_key, em_wp_v4_builder_field_types(), true)) { continue; } ?>
                    <option value="<?php echo esc_attr($ft_key); ?>"><?php echo esc_html((string) $ft['label']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="button button-small em-v4-celladd__confirm"><?php esc_html_e('OK', 'em-wp'); ?></button>
            <button type="button" class="em-v4-celladd__cancel" title="<?php esc_attr_e('Annuler', 'em-wp'); ?>">&times;</button>
        </div>
    </div>
    <?php
}

/**
 * Gabarits JS : une ligne vide (sans colonnes) + une colonne vide.
 */
function em_wp_v4_render_templates(): void
{
    ?>
    <template class="em-v4-row-template">
        <details class="em-v4-row" open>
            <summary class="em-v4-row__summary">
                <span class="em-v4-row__drag dashicons dashicons-menu" title="<?php esc_attr_e('Glisser pour déplacer la ligne', 'em-wp'); ?>" aria-hidden="true"></span>
                <span class="em-v4-collapse__chevron"></span>
                <span class="em-v4-row__label" aria-hidden="true"></span>
                <?php em_wp_v4_render_row_layout(1, ['rows' => [['columns' => 1, 'align' => [1 => 'center']]]]); ?>
                <button type="button" class="em-v4-row__add" title="<?php esc_attr_e('Insérer une ligne en dessous', 'em-wp'); ?>"><span class="dashicons dashicons-plus-alt2"></span></button>
                <button type="button" class="em-v4-row__remove" title="<?php esc_attr_e('Supprimer la ligne', 'em-wp'); ?>">&times;</button>
            </summary>
            <div class="em-v4-row__cols"></div>
        </details>
    </template>
    <template class="em-v4-cell-template"><?php em_wp_v4_render_col(1, []); ?></template>
    <template class="em-v4-align-template"><?php em_wp_v4_render_align_select(1, 'center'); ?></template>
    <?php
}

/**
 * Charge les scripts du builder (aperçu + interactions), une seule fois.
 */
function em_wp_v4_builder_assets(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    em_wp_v4_render_preview_script();
    require __DIR__ . '/appearance-script.php';
    require __DIR__ . '/align-script.php';
    require __DIR__ . '/script.php';
}
