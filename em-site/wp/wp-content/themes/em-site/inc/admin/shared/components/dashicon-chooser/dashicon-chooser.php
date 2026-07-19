<?php
/**
 * Chooser Dashicons partagé (EM-SITE).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, array<int, string>>
 */
function em_site_admin_dashicon_chooser_categories(): array
{
    return function_exists('em_site_dashicons_categories')
        ? em_site_dashicons_categories()
        : ['Divers' => function_exists('em_site_dashicons_all') ? em_site_dashicons_all() : ['dashicons-screenoptions']];
}

/**
 * Rend le chooser Dashicons.
 *
 * @param array<string, mixed> $args
 */
function em_site_admin_render_dashicon_chooser(array $args = []): void
{
    $value_name = isset($args['value_name']) ? (string) $args['value_name'] : '';
    $value_class = isset($args['value_class']) ? (string) $args['value_class'] : '';
    $selected = isset($args['selected']) ? trim((string) $args['selected']) : '';
    $default_icon = isset($args['default_icon']) && trim((string) $args['default_icon']) !== ''
        ? trim((string) $args['default_icon'])
        : 'dashicons-screenoptions';
    $placeholder = isset($args['placeholder']) && (string) $args['placeholder'] !== ''
        ? (string) $args['placeholder']
        : __('Choisir une icône', 'em-site');
    $compact = !empty($args['compact']);
    $categories = em_site_admin_dashicon_chooser_categories();

    $preview_icon = $selected !== '' ? $selected : $default_icon;
    $name_label = $selected !== '' ? preg_replace('/^dashicons-/', '', $selected) : $placeholder;
    ?>
    <div class="em-site-iconchooser<?php echo $compact ? ' em-site-iconchooser--compact' : ''; ?>" data-iconchooser>
        <input
            type="hidden"
            <?php echo $value_name !== '' ? 'name="' . esc_attr($value_name) . '"' : ''; ?>
            value="<?php echo esc_attr($selected); ?>"
            class="<?php echo esc_attr(trim($value_class)); ?>"
            data-iconchooser-value
            data-default-icon="<?php echo esc_attr($default_icon); ?>"
        >
        <button
            type="button"
            class="em-site-iconchooser__trigger"
            data-iconchooser-trigger
            aria-expanded="false"
            aria-haspopup="dialog"
            aria-label="<?php esc_attr_e('Choisir une icône', 'em-site'); ?>"
        >
            <span class="em-site-iconchooser__preview" data-iconchooser-preview>
                <span class="dashicons <?php echo esc_attr($preview_icon); ?>" aria-hidden="true"></span>
            </span>
            <span class="em-site-iconchooser__meta">
                <span class="em-site-iconchooser__meta-label"><?php esc_html_e('Icône', 'em-site'); ?></span>
                <span class="em-site-iconchooser__meta-name" data-iconchooser-name data-iconchooser-placeholder="<?php echo esc_attr($placeholder); ?>"><?php echo esc_html((string) $name_label); ?></span>
            </span>
            <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
        </button>

        <div class="em-site-iconchooser__panel" data-iconchooser-panel hidden>
            <div class="em-site-iconchooser__groups" data-iconchooser-groups>
                <?php foreach ($categories as $category_label => $category_icons) : ?>
                    <section class="em-site-iconchooser__group" data-iconchooser-group>
                        <h4 class="em-site-iconchooser__group-title"><?php echo esc_html($category_label); ?></h4>
                        <div class="em-site-iconchooser__items">
                            <?php foreach ($category_icons as $icon_name) : ?>
                                <?php $icon_display = preg_replace('/^dashicons-/', '', (string) $icon_name); ?>
                                <button
                                    type="button"
                                    class="em-site-iconchooser__item<?php echo $selected === $icon_name ? ' is-selected' : ''; ?>"
                                    data-icon-value="<?php echo esc_attr((string) $icon_name); ?>"
                                    aria-pressed="<?php echo $selected === $icon_name ? 'true' : 'false'; ?>"
                                    title="<?php echo esc_attr((string) $icon_name); ?>"
                                >
                                    <span class="dashicons <?php echo esc_attr((string) $icon_name); ?>" aria-hidden="true"></span>
                                    <span class="em-site-iconchooser__item-name"><?php echo esc_html((string) $icon_display); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Styles et JS du chooser (une seule fois).
 */
function em_site_admin_dashicon_chooser_assets(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <style>
    .em-site-iconchooser {
        position: relative;
        display: flex;
        flex: 1 1 320px;
        min-width: 220px;
        max-width: 760px;
    }
    .em-site-iconchooser__trigger {
        width: 100%;
        min-height: 52px;
        border: 1px solid #c7c9cc;
        border-radius: 10px;
        background: #fff;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        color: #1d2327;
        text-align: left;
    }
    .em-site-iconchooser__trigger:hover { border-color: #2271b1; }
    .em-site-iconchooser__trigger:focus-visible {
        outline: 0;
        border-color: #2271b1;
        box-shadow: 0 0 0 1px #2271b1;
    }
    .em-site-iconchooser__preview {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: 1px solid #d7dade;
        background: #f8fafc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #7f1d1d;
        flex: 0 0 auto;
    }
    .em-site-iconchooser__preview .dashicons {
        width: 24px;
        height: 24px;
        font-size: 24px;
    }
    .em-site-iconchooser__meta {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 2px;
        flex: 1 1 auto;
    }
    .em-site-iconchooser__meta-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6b7280;
        font-weight: 700;
    }
    .em-site-iconchooser__meta-name {
        font-size: 13px;
        color: #374151;
        font-family: Consolas, Menlo, Monaco, monospace;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .em-site-iconchooser__trigger > .dashicons {
        width: 18px;
        height: 18px;
        font-size: 18px;
        color: #6b7280;
        flex: 0 0 auto;
    }
    .em-site-iconchooser__panel {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        z-index: 80;
        border: 1px solid #c7c9cc;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 16px 34px -18px rgba(17,24,39,.35);
        padding: 12px;
        overflow: hidden;
    }
    .em-site-iconchooser.is-dropup .em-site-iconchooser__panel {
        top: auto;
        bottom: calc(100% + 8px);
    }
    .em-site-iconchooser__groups {
        max-height: 320px;
        overflow: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding-right: 2px;
    }
    .em-site-iconchooser__group {
        border: 1px solid #eceef0;
        border-radius: 8px;
        background: #fafbfc;
        padding: 8px;
    }
    .em-site-iconchooser__group-title {
        margin: 0 0 8px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #6b7280;
    }
    .em-site-iconchooser__items {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 6px;
    }
    .em-site-iconchooser__item {
        border: 1px solid #d7dade;
        border-radius: 8px;
        background: #fff;
        min-height: 48px;
        padding: 8px 9px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        text-align: left;
        color: #1f2937;
    }
    .em-site-iconchooser__item:hover { border-color: #2271b1; background: #f4f9ff; }
    .em-site-iconchooser__item.is-selected {
        border-color: #7f1d1d;
        box-shadow: 0 0 0 1px #7f1d1d;
        background: #fff7f7;
    }
    .em-site-iconchooser__item .dashicons {
        width: 24px;
        height: 24px;
        font-size: 24px;
        color: #7f1d1d;
        flex: 0 0 auto;
    }
    .em-site-iconchooser__item-name {
        font-size: 12px;
        line-height: 1.3;
        color: #4b5563;
        font-family: Consolas, Menlo, Monaco, monospace;
        word-break: break-word;
    }
    .em-site-iconchooser--compact {
        min-width: 180px;
        max-width: 280px;
        flex: 0 1 280px;
    }
    .em-site-iconchooser--compact .em-site-iconchooser__trigger {
        min-height: 40px;
        padding: 6px 10px;
        gap: 8px;
    }
    .em-site-iconchooser--compact .em-site-iconchooser__preview {
        width: 28px;
        height: 28px;
        border-radius: 6px;
    }
    .em-site-iconchooser--compact .em-site-iconchooser__preview .dashicons {
        width: 18px;
        height: 18px;
        font-size: 18px;
    }
    .em-site-iconchooser--compact .em-site-iconchooser__meta-label {
        font-size: 10px;
    }
    .em-site-iconchooser--compact .em-site-iconchooser__meta-name {
        font-size: 12px;
    }
    .em-site-iconchooser--compact .em-site-iconchooser__panel {
        min-width: 320px;
        right: auto;
    }
    </style>
    <script>
    window.EmSiteDashiconChooser = window.EmSiteDashiconChooser || (function () {
        function bindChooser(chooser) {
            if (!chooser || chooser.getAttribute('data-iconchooser-ready') === '1') {
                return;
            }

            var valueInput = chooser.querySelector('[data-iconchooser-value]');
            var trigger = chooser.querySelector('[data-iconchooser-trigger]');
            var preview = chooser.querySelector('[data-iconchooser-preview] .dashicons');
            var nameLabel = chooser.querySelector('[data-iconchooser-name]');
            var defaultIcon = valueInput ? (valueInput.getAttribute('data-default-icon') || 'dashicons-screenoptions') : 'dashicons-screenoptions';
            var placeholderText = nameLabel ? (nameLabel.getAttribute('data-iconchooser-placeholder') || 'Choisir une icône') : 'Choisir une icône';
            var panel = chooser.querySelector('[data-iconchooser-panel]');
            var items = Array.prototype.slice.call(chooser.querySelectorAll('[data-icon-value]'));

            if (!valueInput || !trigger || !preview || !nameLabel || !panel || !items.length) {
                return;
            }

            chooser.setAttribute('data-iconchooser-ready', '1');

            function isOpen() {
                return !panel.hidden;
            }

            function formatIconLabel(iconName) {
                return (iconName || '').replace(/^dashicons-/, '');
            }

            function setPanelPlacement() {
                if (!isOpen()) { return; }
                var rect = trigger.getBoundingClientRect();
                var viewportH = window.innerHeight || document.documentElement.clientHeight || 0;
                var viewportPadding = 24;
                var panelGap = 12;
                var spaceBelow = Math.max(0, viewportH - rect.bottom - viewportPadding - panelGap);
                var spaceAbove = Math.max(0, rect.top - viewportPadding - panelGap);
                var minPreferred = 280;
                var openUp = spaceBelow < minPreferred && spaceAbove > spaceBelow;
                chooser.classList.toggle('is-dropup', openUp);
            }

            function setOpen(open) {
                panel.hidden = !open;
                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                chooser.classList.toggle('is-open', open);
                if (open) {
                    setPanelPlacement();
                }
            }

            function applySelection(iconName) {
                var selectedIcon = (iconName || '').trim();
                valueInput.value = selectedIcon;
                nameLabel.textContent = selectedIcon ? formatIconLabel(selectedIcon) : placeholderText;
                if (!selectedIcon) {
                    selectedIcon = defaultIcon;
                }
                preview.className = 'dashicons ' + selectedIcon;
                items.forEach(function (item) {
                    var selected = valueInput.value !== '' && item.getAttribute('data-icon-value') === valueInput.value;
                    item.classList.toggle('is-selected', selected);
                    item.setAttribute('aria-pressed', selected ? 'true' : 'false');
                });
            }

            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                setOpen(!isOpen());
            });

            items.forEach(function (item) {
                item.addEventListener('click', function (event) {
                    event.preventDefault();
                    applySelection(item.getAttribute('data-icon-value') || '');
                    setOpen(false);
                    trigger.focus();
                });
            });

            document.addEventListener('click', function (event) {
                if (!isOpen()) { return; }
                if (chooser.contains(event.target)) { return; }
                setOpen(false);
            });

            document.addEventListener('keydown', function (event) {
                if (!isOpen() || event.key !== 'Escape') { return; }
                event.preventDefault();
                setOpen(false);
                trigger.focus();
            });

            applySelection(valueInput.value || '');
        }

        function init(root) {
            var scope = root && root.querySelectorAll ? root : document;
            scope.querySelectorAll('[data-iconchooser]').forEach(bindChooser);
        }

        document.addEventListener('DOMContentLoaded', function () { init(document); });

        return { init: init };
    })();
    </script>
    <?php
}