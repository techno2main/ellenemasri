<?php
/**
 * Partial : item plateforme social (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<string, mixed> $item
 * @param array<string, array{label:string,icon:string,default_account:string}> $definitions
 */
function em_site_social_render_platform_item(int $list_index, array $item, array $definitions, ?string $field = null): void
{
    $slug = sanitize_key((string) ($item['slug'] ?? ''));
    $platform = $definitions[$slug] ?? null;

    if (!is_array($platform)) {
        return;
    }

    $field_base = ($field ?? em_site_social_form_option_key()) . '[platforms][' . $list_index . ']';
    $is_active = !empty($item['active']);
    ?>
    <details class="em-site-admin-nested-item em-site-top-bar-platform-item">
        <summary>
            <?php em_site_admin_render_panel_edit_trigger(); ?>
            <span class="em-site-top-bar-platform-item__label">
                <span class="em-site-top-bar-panel__visibility em-site-admin-module__item-visibility<?php echo $is_active ? '' : ' is-hidden'; ?>"><i class="fa-solid <?php echo $is_active ? 'fa-eye' : 'fa-eye-slash'; ?>" aria-hidden="true"></i></span>
                <i class="fa-brands <?php echo esc_attr($platform['icon']); ?>" aria-hidden="true"></i>
                <span><?php echo esc_html($platform['label']); ?></span>
            </span>
        </summary>
        <div class="em-site-admin-nested-item__body em-site-admin-panel-body--stack">
            <input type="hidden" name="<?php echo esc_attr($field_base . '[slug]'); ?>" value="<?php echo esc_attr($slug); ?>">
            <label><span><?php esc_html_e('Lien', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[link]'); ?>" value="<?php echo esc_attr((string) ($item['link'] ?? '')); ?>"></label>
            <label><span><?php esc_html_e('Label', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[label]'); ?>" value="<?php echo esc_attr((string) ($item['label'] ?? '')); ?>"></label>
            <label><span><?php esc_html_e('Badge', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[badge]'); ?>" value="<?php echo esc_attr((string) ($item['badge'] ?? '')); ?>"></label>
            <label><span><?php esc_html_e('Compte', 'em-site'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[account]'); ?>" value="<?php echo esc_attr((string) ($item['account'] ?? '')); ?>"></label>
            <label class="em-site-admin-inline-check"><span><?php esc_html_e('Actif', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($field_base . '[active]'); ?>" value="1" <?php checked($is_active); ?>></label>
        </div>
    </details>
    <?php
}

