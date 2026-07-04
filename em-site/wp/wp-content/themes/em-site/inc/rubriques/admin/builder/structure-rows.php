<?php
/**
 * Rendu des LIGNES / COLONNES du builder (V4).
 *
 * En-tête de ligne (indicateur colonnes + sélecteur), onglets de colonnes
 * (libellé + alignement), panneaux de colonne (zone de dépôt + ajout) et
 * gabarits JS. Extrait de structure.php pour respecter la limite de taille.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pictos représentant le nombre de colonnes : un picto = une colonne.
 */
function em_wp_v4_render_col_pips(int $columns): void
{
    $columns = max(1, $columns);
    ?>
    <span class="em-v4-colpips" aria-hidden="true">
        <?php for ($i = 0; $i < $columns; $i++) : ?>
            <span class="em-v4-colpip"></span>
        <?php endfor; ?>
    </span>
    <?php
}

/**
 * Libelle de synthese du nombre de colonnes (ex: "1 colonne", "3 colonnes").
 */
function em_wp_v4_row_cols_text(int $columns): string
{
    $columns = max(1, $columns);

    return sprintf(
        /* translators: %d: number of columns */
        _n('%d colonne', '%d colonnes', $columns, 'em-wp'),
        $columns
    );
}

/**
 * Bloc info colonnes: nombre + pictos (meme rendu ouvert/ferme).
 */
function em_wp_v4_render_row_cols_meta(int $columns): void
{
    ?>
    <span class="em-v4-rowcols-label">
        <span class="em-v4-row__colsnum"><?php echo esc_html(em_wp_v4_row_cols_text($columns)); ?></span>
        <?php em_wp_v4_render_col_pips($columns); ?>
    </span>
    <?php
}

/**
 * Indicateur discret du nombre de colonnes (visible quand la ligne est fermée).
 */
function em_wp_v4_render_row_colcount(int $columns): void
{
    ?>
    <span class="em-v4-row__colcount" title="<?php esc_attr_e('Nombre de colonnes', 'em-wp'); ?>">
        <?php em_wp_v4_render_row_cols_meta($columns); ?>
    </span>
    <?php
}

/**
 * Noms de colonnes affiches dans le resume de ligne, cliquables.
 *
 * @param array<string, mixed> $layout
 * @param array<int, array<int, array<string,mixed>>> $row_grid
 */
function em_wp_v4_render_row_colnames(int $row, int $columns, array $layout, array $row_grid): void
{
    ?>
    <span class="em-v4-row__colnames" aria-label="<?php esc_attr_e('Colonnes de la ligne', 'em-wp'); ?>">
        <?php for ($c = 1; $c <= $columns; $c++) : ?>
            <button type="button" class="em-v4-row__colname" data-col="<?php echo (int) $c; ?>"><?php echo esc_html(em_wp_v4_col_name_for($layout, $row, $c, $row_grid[$c] ?? [])); ?></button>
        <?php endfor; ?>
    </span>
    <?php
}

/**
 * En-tête de ligne (ligne ouverte) : nombre de colonnes (indicatif, sans menu).
 *
 * L'ajout/suppression de colonnes se fait via les onglets (« + » et croix). Le
 * nombre est purement informatif et se met à jour côté JS.
 *
 * @param array<string, mixed> $layout
 */
function em_wp_v4_render_row_layout(int $row, array $layout): void
{
    $columns = em_wp_rubrique_layout_columns_for($layout, $row);
    ?>
    <div class="em-v4-row__layout">
        <?php em_wp_v4_render_row_cols_meta($columns); ?>
    </div>
    <?php
}

/**
 * Nom d'une colonne: premier libelle de champ non vide, sinon "Vide".
 *
 * @param array<int, array<string,mixed>> $fields
 */
function em_wp_v4_col_name_from_fields(array $fields): string
{
    foreach ($fields as $field) {
        $label = trim((string) ($field['label'] ?? ''));
        if ($label !== '') {
            return $label;
        }
    }

    return __('Vide', 'em-wp');
}

/**
 * Nom affiche d'une colonne: titre personnalise si defini, sinon auto.
 *
 * @param array<string, mixed> $layout
 * @param array<int, array<string,mixed>> $fields
 */
function em_wp_v4_col_name_for(array $layout, int $row, int $col, array $fields): string
{
    $custom = trim(em_wp_rubrique_layout_col_title_for($layout, $row, $col));

    return $custom !== '' ? $custom : em_wp_v4_col_name_from_fields($fields);
}

/**
 * Onglet d'une colonne (ligne ouverte) : libellé « Colonne N » + alignement +
 * croix de suppression de la colonne.
 */
function em_wp_v4_render_col_tab(int $index, string $align, bool $active, int $row = 1, string $name = ''): void
{
    $row = max(1, $row);
    $name = trim($name);
    if ($name === '') {
        $name = __('Vide', 'em-wp');
    }
    $tab_label = sprintf('L%dC%d - %s', $row, $index, $name);
    ?>
    <div class="em-v4-col-tab<?php echo $active ? ' is-active' : ''; ?>" data-col="<?php echo (int) $index; ?>" data-editing="0" role="tab">
        <span class="em-v4-col-tab__name"><?php echo esc_html($tab_label); ?></span>
        <input type="text" class="em-v4-col-tab__titleinput em-v4-row__titleinput" value="<?php echo esc_attr($name); ?>" placeholder="<?php esc_attr_e('Nom de colonne', 'em-wp'); ?>" hidden>
        <button type="button" class="em-v4-col-tab__titleedit em-v4-row__titleedit" title="<?php esc_attr_e('Modifier le nom de la colonne', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Modifier le nom de la colonne', 'em-wp'); ?>"><span class="dashicons dashicons-edit" aria-hidden="true"></span></button>
        <span class="em-v4-col-tab__move-group" aria-hidden="false">
            <button type="button" class="em-v4-col-tab__move em-v4-col-tab__move--left" data-dir="-1" title="<?php esc_attr_e('Déplacer la colonne vers la gauche', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Déplacer la colonne vers la gauche', 'em-wp'); ?>"><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span></button>
            <button type="button" class="em-v4-col-tab__move em-v4-col-tab__move--right" data-dir="1" title="<?php esc_attr_e('Déplacer la colonne vers la droite', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Déplacer la colonne vers la droite', 'em-wp'); ?>"><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></button>
        </span>
        <?php em_wp_v4_render_align_select($index, $align); ?>
        <button type="button" class="em-v4-col-tab__del" title="<?php esc_attr_e('Supprimer la colonne', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Supprimer la colonne', 'em-wp'); ?>">&times;</button>
    </div>
    <?php
}

/**
 * Bouton « + » de fin d'onglets : ajoute une colonne à la ligne.
 */
function em_wp_v4_render_col_addbtn(): void
{
    ?>
    <button type="button" class="em-v4-col-tab__add" title="<?php esc_attr_e('Ajouter une colonne', 'em-wp'); ?>"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><?php esc_html_e('Ajouter une colonne', 'em-wp'); ?></button>
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
 * Une ligne : en-tête (indicateur + colonnes) + onglets de colonnes + panneaux.
 *
 * @param array<string, mixed> $layout
 * @param array<int, array<int, array<string,mixed>>> $row_grid
 * @param array<string, mixed> $content
 */
function em_wp_v4_render_row(int $row, array $layout, array $row_grid, array $content = []): void
{
    $columns = em_wp_rubrique_layout_columns_for($layout, $row);
    $title = em_wp_rubrique_layout_title_for($layout, $row);
    $title_placeholder = __('Titre de ligne', 'em-wp');
    $title_display = $title !== '' ? $title : $title_placeholder;
    ?>
    <details class="em-v4-row">
        <summary class="em-v4-row__summary">
            <span class="em-v4-row__drag dashicons dashicons-menu" title="<?php esc_attr_e('Glisser pour déplacer la ligne', 'em-wp'); ?>" aria-hidden="true"></span>
            <span class="em-v4-collapse__chevron"></span>
            <span class="em-v4-row__left">
                <span class="em-v4-row__label" aria-hidden="true"></span>
            </span>
        </summary>
        <div class="em-v4-row__summary-actions">
            <span class="em-v4-row__title" data-editing="0" title="<?php esc_attr_e('Titre de la ligne', 'em-wp'); ?>">
                <span class="em-v4-row__titleprefix" aria-hidden="true"></span>
                <span class="em-v4-row__titletxt" data-empty="<?php echo $title !== '' ? '0' : '1'; ?>"><?php echo esc_html($title_display); ?></span>
                <input type="text" class="em-v4-row__titleinput" value="<?php echo esc_attr($title); ?>" placeholder="<?php esc_attr($title_placeholder); ?>" hidden>
                <button type="button" class="em-v4-row__titleedit" title="<?php esc_attr_e('Modifier le titre de la ligne', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Modifier le titre de la ligne', 'em-wp'); ?>"><span class="dashicons dashicons-edit" aria-hidden="true"></span></button>
            </span>
            <span class="em-v4-row__right">
                <?php em_wp_v4_render_row_colcount($columns); ?>
                <?php em_wp_v4_render_row_colnames($row, $columns, $layout, $row_grid); ?>
            </span>
            <button type="button" class="em-v4-row__add" title="<?php esc_attr_e('Insérer une ligne en dessous', 'em-wp'); ?>"><span class="dashicons dashicons-plus-alt2"></span></button>
            <button type="button" class="em-v4-row__remove" title="<?php esc_attr_e('Supprimer la ligne', 'em-wp'); ?>">&times;</button>
        </div>
        <div class="em-v4-row__body">
            <div class="em-v4-col-tabs" role="tablist">
                <?php for ($c = 1; $c <= $columns; $c++) : ?>
                    <?php em_wp_v4_render_col_tab($c, em_wp_rubrique_layout_align_for($layout, $row, $c), $c === 1, $row, em_wp_v4_col_name_for($layout, $row, $c, $row_grid[$c] ?? [])); ?>
                <?php endfor; ?>
                <?php em_wp_v4_render_col_addbtn(); ?>
            </div>
            <div class="em-v4-col-panels">
                <?php for ($c = 1; $c <= $columns; $c++) : ?>
                    <?php em_wp_v4_render_col($c, $row_grid[$c] ?? [], $content, $c === 1); ?>
                <?php endfor; ?>
            </div>
        </div>
    </details>
    <?php
}

/**
 * Une colonne (panneau d'onglet) : zone de dépôt + bouton « + ».
 * Seul le panneau actif est visible (pleine largeur).
 *
 * @param array<int, array<string,mixed>> $fields
 * @param array<string, mixed> $content
 */
function em_wp_v4_render_col(int $index, array $fields, array $content = [], bool $active = false): void
{
    ?>
    <div class="em-v4-col<?php echo $active ? ' is-active' : ''; ?>" data-col="<?php echo (int) $index; ?>">
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
    $registry = em_wp_field_type_registry();
    $allowed = em_wp_v4_builder_field_types();
    $first_type = 'text';
    if (!in_array($first_type, $allowed, true)) {
        $first_type = isset($allowed[0]) ? (string) $allowed[0] : '';
    }
    $first_label = em_wp_v4_field_type_picker_label($first_type);
    $first_icon = em_wp_v4_field_type_icon($first_type);
    ?>
    <div class="em-v4-celladd">
        <button type="button" class="em-v4-celladd__btn"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Ajouter un champ', 'em-wp'); ?></button>
        <div class="em-v4-celladd__form" hidden>
            <div class="em-v4-celladd__picker" data-open="0">
                <button type="button" class="em-v4-celladd__pickbtn" aria-expanded="false" title="<?php esc_attr_e('Choisir un type de champ', 'em-wp'); ?>">
                    <span class="em-v4-celladd__pickicon dashicons <?php echo esc_attr($first_icon); ?>" aria-hidden="true"></span>
                    <span class="em-v4-celladd__picklabel"><?php echo esc_html($first_label); ?></span>
                    <span class="em-v4-celladd__pickarrow dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                </button>
                <div class="em-v4-celladd__menu" hidden>
                    <?php foreach (em_wp_v4_builder_field_groups() as $group_label => $group_types) : ?>
                        <?php
                        $rendered = false;
                        ob_start();
                        foreach ($group_types as $ft_key) {
                            if (!in_array($ft_key, $allowed, true) || !isset($registry[$ft_key])) {
                                continue;
                            }
                            $rendered = true;
                            $ft_label = em_wp_v4_field_type_picker_label($ft_key);
                            $ft_icon = em_wp_v4_field_type_icon($ft_key);
                            echo '<button type="button" class="em-v4-celladd__opt" data-value="' . esc_attr($ft_key) . '" data-label="' . esc_attr($ft_label) . '" data-icon="' . esc_attr($ft_icon) . '">';
                            echo '<span class="em-v4-celladd__opticon dashicons ' . esc_attr($ft_icon) . '" aria-hidden="true"></span>';
                            echo '<span class="em-v4-celladd__optlabel">' . esc_html($ft_label) . '</span>';
                            echo '</button>';
                        }
                        $group_options = ob_get_clean();
                        if (!$rendered || $group_options === '') {
                            continue;
                        }
                        ?>
                        <div class="em-v4-celladd__group">
                            <div class="em-v4-celladd__gtitle"><?php echo esc_html($group_label); ?></div>
                            <div class="em-v4-celladd__glist"><?php echo $group_options; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <select class="em-v4-celladd__type" hidden>
                <?php foreach (em_wp_v4_builder_field_groups() as $group_label => $group_types) : ?>
                    <?php
                    $rendered = false;
                    ob_start();
                    foreach ($group_types as $ft_key) {
                        if (!in_array($ft_key, $allowed, true) || !isset($registry[$ft_key])) {
                            continue;
                        }
                        $rendered = true;
                        echo '<option value="' . esc_attr($ft_key) . '"' . selected($ft_key, $first_type, false) . '>' . esc_html(em_wp_v4_field_type_picker_label($ft_key)) . '</option>';
                    }
                    $group_options = ob_get_clean();
                    if (!$rendered || $group_options === '') {
                        continue;
                    }
                    ?>
                    <optgroup label="<?php echo esc_attr($group_label); ?>"><?php echo $group_options; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></optgroup>
                <?php endforeach; ?>
            </select>
            <button type="button" class="button button-small em-v4-celladd__confirm"><?php esc_html_e('OK', 'em-wp'); ?></button>
            <button type="button" class="em-v4-celladd__cancel" title="<?php esc_attr_e('Annuler', 'em-wp'); ?>">&times;</button>
        </div>
    </div>
    <?php
}

/**
 * Gabarits JS : une ligne vide (corps onglets/panneaux) + une colonne + un onglet.
 */
function em_wp_v4_render_templates(): void
{
    ?>
    <template class="em-v4-row-template">
        <details class="em-v4-row" open>
            <summary class="em-v4-row__summary">
                <span class="em-v4-row__drag dashicons dashicons-menu" title="<?php esc_attr_e('Glisser pour déplacer la ligne', 'em-wp'); ?>" aria-hidden="true"></span>
                <span class="em-v4-collapse__chevron"></span>
                <span class="em-v4-row__left">
                    <span class="em-v4-row__label" aria-hidden="true"></span>
                </span>
            </summary>
            <div class="em-v4-row__summary-actions">
                <span class="em-v4-row__title" data-editing="0" title="<?php esc_attr_e('Titre de la ligne', 'em-wp'); ?>">
                    <span class="em-v4-row__titleprefix" aria-hidden="true"></span>
                    <span class="em-v4-row__titletxt" data-empty="1"><?php esc_html_e('Titre de ligne', 'em-wp'); ?></span>
                    <input type="text" class="em-v4-row__titleinput" value="" placeholder="<?php esc_attr_e('Titre de ligne', 'em-wp'); ?>" hidden>
                    <button type="button" class="em-v4-row__titleedit" title="<?php esc_attr_e('Modifier le titre de la ligne', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Modifier le titre de la ligne', 'em-wp'); ?>"><span class="dashicons dashicons-edit" aria-hidden="true"></span></button>
                </span>
                <span class="em-v4-row__right">
                    <?php em_wp_v4_render_row_colcount(1); ?>
                    <?php em_wp_v4_render_row_colnames(1, 1, ['rows' => [['columns' => 1, 'align' => [1 => 'center'], 'col_titles' => [1 => '']]]], [1 => []]); ?>
                </span>
                <button type="button" class="em-v4-row__add" title="<?php esc_attr_e('Insérer une ligne en dessous', 'em-wp'); ?>"><span class="dashicons dashicons-plus-alt2"></span></button>
                <button type="button" class="em-v4-row__remove" title="<?php esc_attr_e('Supprimer la ligne', 'em-wp'); ?>">&times;</button>
            </div>
            <div class="em-v4-row__body">
                <div class="em-v4-col-tabs" role="tablist"><?php em_wp_v4_render_col_addbtn(); ?></div>
                <div class="em-v4-col-panels"></div>
            </div>
        </details>
    </template>
    <template class="em-v4-cell-template"><?php em_wp_v4_render_col(1, [], [], true); ?></template>
    <template class="em-v4-coltab-template"><?php em_wp_v4_render_col_tab(1, 'center', true); ?></template>
    <?php
}
