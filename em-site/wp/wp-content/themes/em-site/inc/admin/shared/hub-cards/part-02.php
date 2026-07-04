<?php
function em_wp_admin_hub_render_catalog_card_description(string $item_name, string $rubrique_name, string $module_slug = ''): void
{
    $item_html = '<strong class="em-wp-hub__card-desc-item">' . esc_html(__('items', 'em-wp')) . '</strong>';
    $rubrique_html = '<strong class="em-wp-hub__card-desc-rubrique">' . esc_html(mb_strtoupper(trim($rubrique_name))) . '</strong>';

    $text = sprintf(
        /* translators: 1: literal "items", 2: rubrique name */
        __('Liste des %1$s disponibles pour la rubrique %2$s.', 'em-wp'),
        $item_html,
        $rubrique_html
    );
    ?>
    <p class="em-wp-hub__card-desc">
        <?php
        echo wp_kses(
            $text,
            [
                'strong' => ['class' => true],
            ]
        );
        ?>
    </p>
    <?php
}

/**
 * Rendu d'un bouton d'action pill (lien).
 */
function em_wp_admin_hub_render_action_link(
    string $url,
    string $label,
    string $icon_class,
    bool $compact = false,
    string $accessible_label = ''
): void {
    $icon_class = trim($icon_class);

    if ($icon_class !== '' && !str_contains($icon_class, 'dashicons ')) {
        $icon_class = 'dashicons ' . $icon_class;
    }

    $action_class = 'em-wp-hub__action';
    $visible_label = trim($label);
    $aria_label = trim($accessible_label !== '' ? $accessible_label : $label);

    if ($compact) {
        $action_class .= ' em-wp-hub__action--compact';
    }

    if ($visible_label === '' && $icon_class !== '') {
        $action_class .= ' em-wp-hub__action--icon-only';
    }
    ?>
    <a
        class="<?php echo esc_attr($action_class); ?>"
        href="<?php echo esc_url($url); ?>"
        <?php echo $aria_label !== '' ? 'aria-label="' . esc_attr($aria_label) . '"' : ''; ?>
        <?php echo $aria_label !== '' ? 'title="' . esc_attr($aria_label) . '"' : ''; ?>
    >
        <span class="em-wp-hub__action-inner">
            <?php if ($icon_class !== '') { ?>
                <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
            <?php } ?>
            <?php if ($visible_label !== '') { ?>
                <span class="em-wp-hub__action-label"><?php echo esc_html($visible_label); ?></span>
            <?php } ?>
        </span>
    </a>
    <?php
}

/**
 * Bouton renommer — à gauche du titre de carte catalogue.
 *
 * @param array<string, string> $attrs
 */
function em_wp_admin_hub_render_catalog_name_edit_button(
    string $button_id,
    string $accessible_label,
    array $attrs = []
): void {
    $button_id = sanitize_html_class($button_id);
    $accessible_label = trim($accessible_label);

    if ($button_id === '') {
        return;
    }

    $attr_html = '';

    foreach ($attrs as $key => $value) {
        $key = sanitize_key((string) $key);

        if ($key === '') {
            continue;
        }

        $attr_html .= sprintf(' %s="%s"', esc_attr($key), esc_attr((string) $value));
    }
    ?>
    <button
        type="button"
        class="em-wp-hub__card-name-edit"
        id="<?php echo esc_attr($button_id); ?>"
        title="<?php echo esc_attr($accessible_label); ?>"
        <?php echo $accessible_label !== '' ? 'aria-label="' . esc_attr($accessible_label) . '"' : ''; ?>
        <?php echo $attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    >
        <i class="fa-solid fa-pen" aria-hidden="true"></i>
    </button>
    <?php
}

/**
 * Action « Ouvrir le catalogue » — libellé visible + icône dossier.
 */
function em_wp_admin_hub_render_catalog_open_action(string $url, string $catalog_label = ''): void
{
    $url = trim($url);
    $catalog_label = trim($catalog_label);

    if ($url === '') {
        return;
    }

    $accessible_label = $catalog_label !== ''
        ? sprintf(
            /* translators: %s: catalog name */
            __('Ouvrir le catalogue %s', 'em-wp'),
            $catalog_label
        )
        : __('Ouvrir le catalogue', 'em-wp');
    ?>
    <a
        class="em-wp-hub__action em-wp-hub__action--compact em-wp-hub__action--catalog-open em-wp-hub__action--fa"
        href="<?php echo esc_url($url); ?>"
        aria-label="<?php echo esc_attr($accessible_label); ?>"
        title="<?php echo esc_attr($accessible_label); ?>"
    >
        <span class="em-wp-hub__action-inner">
            <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
            <span class="em-wp-hub__action-label"><?php esc_html_e('Ouvrir', 'em-wp'); ?></span>
        </span>
    </a>
    <?php
}

/**
 * Lien icône Font Awesome compact (cartes hub — voir, éditer…).
 */
function em_wp_admin_hub_render_card_fa_action_link(
    string $url,
    string $fa_class,
    string $accessible_label
): void {
    $fa_class = trim($fa_class);
    $accessible_label = trim($accessible_label);

    if ($url === '' || $fa_class === '') {
        return;
    }
    ?>
    <a
        class="em-wp-hub__action em-wp-hub__action--compact em-wp-hub__action--icon-only em-wp-hub__action--fa"
        href="<?php echo esc_url($url); ?>"
        <?php echo $accessible_label !== '' ? 'aria-label="' . esc_attr($accessible_label) . '"' : ''; ?>
    >
        <span class="em-wp-hub__action-inner">
            <i class="<?php echo esc_attr($fa_class); ?>" aria-hidden="true"></i>
        </span>
    </a>
    <?php
}

/**
 * Bouton icône Font Awesome compact (panneau inline, toggle…).
 *
 * @param array<string, string> $attrs
 */
function em_wp_admin_hub_render_card_fa_action_button(
    string $button_id,
    string $fa_class,
    string $accessible_label,
    array $attrs = []
): void {
    $fa_class = trim($fa_class);
    $button_id = sanitize_html_class($button_id);
    $accessible_label = trim($accessible_label);

    if ($fa_class === '' || $button_id === '') {
        return;
    }

    $attr_html = '';

    foreach ($attrs as $key => $value) {
        $key = sanitize_key((string) $key);

        if ($key === '') {
            continue;
        }

        $attr_html .= sprintf(' %s="%s"', esc_attr($key), esc_attr((string) $value));
    }
    ?>
    <button
        type="button"
        class="em-wp-hub__action em-wp-hub__action--compact em-wp-hub__action--icon-only em-wp-hub__action--fa"
        id="<?php echo esc_attr($button_id); ?>"
        <?php echo $accessible_label !== '' ? 'aria-label="' . esc_attr($accessible_label) . '"' : ''; ?>
        <?php echo $attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    >
        <span class="em-wp-hub__action-inner">
            <i class="<?php echo esc_attr($fa_class); ?>" aria-hidden="true"></i>
        </span>
    </button>
    <?php
}

/**
 * Bouton secondaire désactivé (cartes « Nouveau … », prochaine étape).
 */
function em_wp_admin_hub_render_disabled_action(string $label, string $icon_class = 'dashicons dashicons-plus-alt2', bool $compact = false): void
{
    $action_class = 'em-wp-hub__action em-wp-hub__action--secondary';
    $visible_label = trim($label);

    if ($compact) {
        $action_class .= ' em-wp-hub__action--compact';
    }

    if ($visible_label === '' && $icon_class !== '') {
        $action_class .= ' em-wp-hub__action--icon-only';
    }
    ?>
    <button type="button" class="<?php echo esc_attr($action_class); ?>" disabled title="<?php esc_attr_e('Prochaine étape', 'em-wp'); ?>">
        <span class="em-wp-hub__action-inner">
            <?php if ($icon_class !== '') { ?>
                <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
            <?php } ?>
            <?php if ($visible_label !== '') { ?>
                <span class="em-wp-hub__action-label"><?php echo esc_html($visible_label); ?></span>
            <?php } ?>
        </span>
    </button>
    <?php
}

/**
 * Pastille liste des entrées catalogue cliquables (cartes hub CATALOGUES).
 *
 * @param array<int, array{label:string,url:string}> $entries
 */

