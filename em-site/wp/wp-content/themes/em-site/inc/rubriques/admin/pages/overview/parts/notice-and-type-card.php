<?php
function em_site_overview_notice(): void
{
    $updated = sanitize_key((string) ($_GET['updated'] ?? ''));
    $error = sanitize_key((string) ($_GET['error'] ?? ''));

    $type_slug = sanitize_key((string) ($_GET['type'] ?? ''));
    $n = em_site_rubrique_type_nouns($type_slug !== '' && em_site_rubrique_type_exists($type_slug) ? $type_slug : '');
    $noun = ucfirst($n['singular'] !== '' ? $n['singular'] : __('élément', 'em-site'));
    $e = $n['e'];

    $messages = [
        'saved'      => sprintf(__('%1$s enregistré%2$s.', 'em-site'), $noun, $e),
        'created'    => sprintf(__('%1$s créé%2$s.', 'em-site'), $noun, $e),
        'deleted'    => sprintf(__('%1$s supprimé%2$s.', 'em-site'), $noun, $e),
        'duplicated' => sprintf(__('%1$s dupliqué%2$s.', 'em-site'), $noun, $e),
        'structure'  => __('Structure enregistrée.', 'em-site'),
        'type_created' => __('Rubrique créée.', 'em-site'),
        'type_deleted' => __('Rubrique supprimée.', 'em-site'),
    ];

    if (isset($messages[$updated])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$updated]) . '</p></div>';
    } elseif ($error === 'type_exists') {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Cette rubrique existe déjà.', 'em-site') . '</p></div>';
    } elseif ($error !== '') {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Action impossible (données invalides).', 'em-site') . '</p></div>';
    }
}

/**
 * Carte repliable d'une rubrique : ses footers.
 *
 * @param array<string, mixed> $type
 */
function em_site_overview_render_type(string $slug, array $type, bool $open): void
{
    $count = count(em_site_get_items($slug));
    $label = function_exists('em_site_overview_type_label')
        ? em_site_overview_type_label((string) $slug, $type)
        : (string) ($type['label_plural'] ?? $type['label']);
    $label_singular = (string) ($type['label'] ?? $label);
    $add_label = sprintf(__('Ajouter un item %s', 'em-site'), $label_singular);
    $is_special_fixed = function_exists('em_site_is_fixed_single_item_type')
        && em_site_is_fixed_single_item_type($slug);
    $is_header_container = ($slug === 'headers');
    $is_reorderable = !$is_special_fixed && !$is_header_container;
    $can_add_items = !$is_special_fixed;
    $can_delete_type = !$is_special_fixed && !$is_header_container;
    $delete_form_id = 'em-site-delete-type-' . $slug;
    $name_input_id = 'em-site-card-name-' . sanitize_html_class($slug);
    $delete_title = __('Supprimer la rubrique', 'em-site');
    $delete_tip = __('Supprimer cette rubrique', 'em-site');
    $delete_ack = __('Je confirme la suppression définitive de cette rubrique et de ses sections.', 'em-site');
    $label_for_match = strtolower(remove_accents($label));
    $is_header_linked = in_array($slug, ['hero', 'heros', 'heroes', 'slider', 'sliders'], true)
        || strpos($slug, 'hero') !== false
        || strpos($slug, 'slider') !== false
        || strpos($label_for_match, 'hero') !== false
        || strpos($label_for_match, 'slider') !== false;
    $card_classes = 'em-site-collapse em-site-card'
        . ($is_special_fixed ? ' em-site-card--fixed-single' : '')
        . (!$is_reorderable ? ' em-site-card--not-reorderable' : '')
        . ($is_header_linked ? ' em-site-card--header-linked' : '');
    ?>
    <details class="<?php echo esc_attr($card_classes); ?>" id="em-site-card-<?php echo esc_attr($slug); ?>" data-slug="<?php echo esc_attr($slug); ?>" data-item-count="<?php echo esc_attr((string) $count); ?>" data-reorderable="<?php echo $is_reorderable ? '1' : '0'; ?>" data-header-linked="<?php echo $is_header_linked ? '1' : '0'; ?>" <?php echo $open ? 'open' : ''; ?>>
        <summary class="em-site-collapse__summary em-site-card__head">
            <span class="em-site-card__drag dashicons dashicons-menu" title="<?php echo esc_attr($is_reorderable ? __('Glisser pour réordonner', 'em-site') : __('Ordre verrouillé', 'em-site')); ?>" aria-hidden="true"></span>
            <span class="em-site-collapse__chevron" aria-hidden="true"></span>
            <?php
            $icon = function_exists('em_site_overview_type_icon')
                ? em_site_overview_type_icon((string) $slug, $type)
                : (string) ($type['icon'] ?? 'dashicons-screenoptions');
            ?>
            <span class="em-site-card__icon dashicons <?php echo esc_attr($icon); ?>"></span>
            <strong class="em-site-card__name"><?php echo esc_html($label); ?></strong>
            <span class="em-site-card__count" title="<?php echo esc_attr(sprintf(_n('%d item', '%d items', $count, 'em-site'), $count)); ?>"><?php echo esc_html((string) $count); ?></span>
        </summary>
        <div class="em-site-collapse__body">
            <div class="em-site-card__toolbar">
                <span class="em-site-card__toolbar-spacer" aria-hidden="true"></span>
                <span class="em-site-card__toolbar-spacer" aria-hidden="true"></span>
                <span class="em-site-card__toolbar-spacer" aria-hidden="true"></span>
                <button type="button" class="em-site-card__edit" title="<?php esc_attr_e('Renommer la rubrique', 'em-site'); ?>" aria-label="<?php esc_attr_e('Renommer la rubrique', 'em-site'); ?>">
                    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                </button>
                <input id="<?php echo esc_attr($name_input_id); ?>" type="text" class="em-site-card__nameinput" data-slug="<?php echo esc_attr($slug); ?>" data-original="<?php echo esc_attr($label); ?>" value="<?php echo esc_attr($label); ?>" hidden>
                <button type="button" class="em-site-card__confirm" title="<?php esc_attr_e('Valider', 'em-site'); ?>" aria-label="<?php esc_attr_e('Valider', 'em-site'); ?>" hidden>
                    <span class="dashicons dashicons-yes" aria-hidden="true"></span>
                </button>
                <button type="button" class="em-site-card__cancel" title="<?php esc_attr_e('Annuler', 'em-site'); ?>" aria-label="<?php esc_attr_e('Annuler', 'em-site'); ?>" hidden>
                    <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                </button>
                <span class="em-site-card__count" title="<?php echo esc_attr(sprintf(_n('%d item', '%d items', $count, 'em-site'), $count)); ?>"><?php echo esc_html((string) $count); ?></span>
                <?php if ($can_add_items) { ?>
                    <button type="button" class="em-site-card__additem" data-create-target="em-site-create-<?php echo esc_attr($slug); ?>" title="<?php echo esc_attr($add_label); ?>" aria-label="<?php echo esc_attr($add_label); ?>" aria-expanded="false">
                        <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                        <span><?php echo esc_html($add_label); ?></span>
                    </button>
                <?php } ?>
                <?php if ($can_delete_type) { ?>
                    <button type="button" class="em-site-card__delete em-site-type-delete" data-deleteform="<?php echo esc_attr($delete_form_id); ?>" data-label="<?php echo esc_attr($label); ?>" data-title="<?php echo esc_attr($delete_title); ?>" data-ack="<?php echo esc_attr($delete_ack); ?>" title="<?php echo esc_attr($delete_tip); ?>" aria-label="<?php echo esc_attr($delete_tip); ?>">
                        <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                    </button>
                <?php } ?>
            </div>
            <?php em_site_render_items_section($slug); ?>
        </div>
        <?php if ($can_delete_type) { ?>
            <form id="<?php echo esc_attr($delete_form_id); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-site-deleteform" hidden>
                <?php wp_nonce_field('em_site_delete_type'); ?>
                <input type="hidden" name="action" value="em_site_delete_type">
                <input type="hidden" name="type" value="<?php echo esc_attr($slug); ?>">
            </form>
        <?php } ?>
    </details>
    <?php
}

/**
 * Confirmation modale de suppression d'une rubrique (cartes overview).
 */
