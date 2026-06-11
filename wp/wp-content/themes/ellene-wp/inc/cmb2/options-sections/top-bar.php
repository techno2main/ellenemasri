<?php

/**
 * Top bar fields for the CMB2 options page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_register_cmb2_top_bar_section($cmb) {
    // ========== SECTION: TOP-BAR ==========

    $cmb->add_field(array(
        'name' => 'Top-Bar',
        'type' => 'title',
        'id'   => 'section_top_bar_title',
    ));

    $cmb->add_field(array(
        'name' => 'Logo TOP-BAR',
        'id'   => 'top_bar_logo_png',
        'type' => 'file',
        'text' => array(
            'add_upload_file_text' => 'Modifier',
        ),
    ));

    $cmb->add_field(array(
        'name' => 'Masquer',
        'id'   => 'top_bar_logo_hidden',
        'type' => 'checkbox',
    ));

    $top_bar_group = $cmb->add_field(array(
        'id'      => 'top_bar_items',
        'type'    => 'group',
        'options' => array(
            'group_title'   => 'Item {#}',
            'add_button'    => '+ Ajouter',
            'remove_button' => 'Supprimer',
            'sortable'      => true,
        ),
    ));

    $cmb->add_group_field($top_bar_group, array(
        'name' => 'Label',
        'id'   => 'label',
        'type' => 'text',
    ));

    $cmb->add_group_field($top_bar_group, array(
        'name' => 'Lien',
        'id'   => 'href',
        'type' => 'text_url',
    ));

    $cmb->add_group_field($top_bar_group, array(
        'name' => 'Masquer',
        'id'   => 'is_hidden',
        'type' => 'checkbox',
    ));

}

function ellene_wp_top_bar_position_indicator_admin_head() {
    $screen = get_current_screen();

    if (!$screen || $screen->id !== 'toplevel_page_ellene-wp_landing_options') {
        return;
    }
    ?>
    <style>
    .ellene-pos-rect {
        display: inline-flex;
        margin: 0 12px 0 0;
        vertical-align: middle;
        border: 1px solid #9a9a9a;
        border-radius: 2px;
        line-height: 0;
        overflow: hidden;
    }
    .ellene-pos-rect span {
        display: inline-block;
        width: 22px;
        height: 14px;
        position: relative;
    }
    .ellene-pos-rect span + span {
        border-left: 1px solid #9a9a9a;
    }
    .ellene-pos-rect span::after {
        content: attr(data-label);
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 10px;
        line-height: 1;
        font-weight: 700;
        color: inherit;
        font-family: Arial, sans-serif;
    }

    /* TOP-BAR: force one-line item layout (arrows + Label + Lien + Masquer). */
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-nested {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
        gap: 12px !important;
        white-space: nowrap;
        overflow-x: auto;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"],
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"],
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-is-hidden"] {
        display: flex !important;
        clear: none !important;
        float: none !important;
        width: auto !important;
        flex: 0 0 auto !important;
        align-items: center !important;
        vertical-align: middle;
        gap: 8px !important;
        padding-top: 4px !important;
        padding-bottom: 4px !important;
        margin-top: 0 !important;
        border-bottom: 0 !important;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] {
        margin-right: 10px !important;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] {
        margin-right: 10px !important;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-is-hidden"] {
        margin-right: 0 !important;
        gap: 6px !important;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-th,
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-th {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px;
        width: auto !important;
        min-width: 70px !important;
        float: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-td,
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-td,
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-is-hidden"] .cmb-td {
        width: auto !important;
        float: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-td,
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-td {
        flex: 0 0 auto;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-th label,
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-th label,
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-is-hidden"] .cmb-th label {
        display: inline-block !important;
        margin: 0 !important;
        white-space: nowrap;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-th label {
        visibility: visible !important;
        opacity: 1 !important;
        color: inherit !important;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .ellene-topbar-native-label {
        display: none !important;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-th label {
        display: none !important;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .ellene-topbar-label-text {
        display: inline-block !important;
        margin: 0 !important;
        font-weight: 600;
        white-space: nowrap;
        color: #1d2327;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-td input[type="text"],
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-td input[type="url"],
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-td input[type="text"] {
        width: 170px !important;
        min-width: 170px !important;
        max-width: 170px !important;
        height: 36px !important;
        margin: 0 !important;
        padding: 6px 8px !important;
    }

    .ellene-topbar-shift-slot {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-right: 10px;
        flex: 0 0 auto;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] > .cmb-th {
        order: 1;
    }

    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] > .cmb-td {
        order: 2;
    }

    .ellene-topbar-shift-slot .cmb-shift-rows {
        position: static !important;
        top: auto !important;
        left: auto !important;
        margin: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
        min-width: 0 !important;
        min-height: 0 !important;
        width: auto !important;
        height: auto !important;
        line-height: 1 !important;
        float: none !important;
    }

    .ellene-topbar-shift-slot .cmb-shift-rows .dashicons {
        color: #6b21a8 !important;
        font-size: 18px;
        width: 18px;
        height: 18px;
        line-height: 18px;
    }

    .cmb2-id-top-bar-items .cmb-shift-rows[aria-disabled="true"],
    .cmb2-id-top-bar-items .cmb-shift-rows:disabled {
        opacity: 0.35;
        pointer-events: none;
        cursor: default;
    }

    /* Respect native postbox accordion state. */
    .cmb2-id-top-bar-items .cmb-repeatable-grouping.closed > .inside {
        display: none !important;
    }

    /* Requested: remove reorder arrows in TOP-BAR items. */
    .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-shift-rows {
        display: none !important;
    }

    /* Mobile only: each field row on its own line for usability. */
    @media (max-width: 900px) {
        .cmb2-id-top-bar-logo-png .cmb-attach-list {
            display: none !important;
        }

        .cmb2-id-top-bar-logo-png .cmb-td {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            flex-wrap: wrap !important;
            justify-content: flex-start !important;
        }

        .cmb2-id-top-bar-logo-png .cmb-td input[type="text"],
        .cmb2-id-top-bar-logo-png .cmb-td input[type="url"] {
            display: none !important;
        }

        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-upload-button {
            order: 2;
            margin: 0 !important;
            width: 42px !important;
            min-width: 42px !important;
            height: 40px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0 !important;
            line-height: 1 !important;
        }

        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-upload-button::before {
            content: "\f464";
            font-family: "dashicons";
            font-size: 18px;
            line-height: 1;
            color: #3858e9 !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .cmb2-id-top-bar-logo-png .cmb-td .ellene-wp-inline-hide-toggle {
            order: 2;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin: 0 0 0 6px !important;
            white-space: nowrap;
            width: auto !important;
        }

        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-media-status,
        .cmb2-id-top-bar-logo-png .cmb-td .img-status,
        .cmb2-id-top-bar-logo-png .cmb-td .embed-status,
        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-remove-file-button {
            display: inline-block;
        }

        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-remove-wrapper {
            order: 5;
            width: 100% !important;
            display: block !important;
            height: auto !important;
            margin: 8px 0 0 0 !important;
            overflow: visible !important;
        }

        /* Mobile: prevent duplicated red remove controls in preview. */
        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-media-status .cmb2-remove-file-button,
        .cmb2-id-top-bar-logo-png .cmb-td .img-status .cmb2-remove-file-button,
        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-media-status .cmb2-remove-file-list,
        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-media-status .cmb2-remove-file {
            display: none !important;
        }

        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-remove-file-button {
            display: none !important;
        }

        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-remove-wrap .cmb2-remove-file-button:first-of-type,
        .cmb2-id-top-bar-logo-png .cmb-td .cmb2-remove-wrapper .cmb2-remove-file-button:first-of-type,
        .cmb2-id-top-bar-logo-png .cmb-td > .cmb2-remove-file-button:first-of-type {
            display: inline-block !important;
            position: static !important;
            top: auto !important;
            left: auto !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-nested {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            white-space: normal;
            overflow: visible;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"],
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"],
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-is-hidden"] {
            display: grid !important;
            width: 100% !important;
            margin-right: 0 !important;
            margin-bottom: 10px;
            gap: 8px !important;
            align-items: center !important;
            justify-content: start !important;
            text-align: left !important;
            clear: none !important;
            float: none !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-th,
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-th,
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-td,
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-td {
            display: inline-flex !important;
            align-items: center !important;
            align-self: center !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-th + .cmb-td,
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-th + .cmb-td {
            float: none !important;
            width: auto !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"],
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] {
            grid-template-columns: 68px minmax(0, 1fr) !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-is-hidden"] {
            grid-template-columns: auto auto !important;
            width: auto !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-th,
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-th {
            align-items: center !important;
            justify-content: flex-start !important;
            min-width: 68px !important;
            width: 68px !important;
            float: none !important;
            padding: 0 !important;
            margin: 0 !important;
            text-align: left !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-td,
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-td {
            display: inline-flex !important;
            width: auto !important;
            max-width: none !important;
            float: none !important;
            padding: 0 !important;
            margin: 0 !important;
            justify-content: flex-start !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-is-hidden"] .cmb-th,
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-is-hidden"] .cmb-td {
            width: auto !important;
            float: none !important;
            margin: 0 !important;
            padding: 0 !important;
            text-align: left !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-td input[type="text"],
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-td input[type="url"],
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-td input[type="text"] {
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
            margin: 0 !important;
            display: block !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .cmb-th label,
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-href"] .cmb-th label,
        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-is-hidden"] .cmb-th label {
            text-align: left !important;
            margin: 0 !important;
        }

        .cmb2-id-top-bar-items .cmb-repeatable-grouping .cmb-row[class*="-label"] .ellene-topbar-label-text {
            text-align: left !important;
            margin: 0 !important;
        }
    }
    </style>
    <script>
    (function() {
        // Couleurs a modifier directement dans le code
        var ACTIVE_COLOR = '#16002f';
        var INACTIVE_COLOR = '#d5d5d5';
        var FIXED_ITEM_TITLES = ['Baseline', 'CTA', 'Titre Single'];

        function normalizeLabel(text) {
            return String(text || '').toLowerCase().replace(/\s+/g, ' ').trim();
        }

        function getPriorityFromLabel(label) {
            var value = normalizeLabel(label);
            if (value.indexOf('baselin') !== -1) return 0;
            if (value.indexOf('cta') !== -1) return 1;
            if (value.indexOf('titre') !== -1) return 2;
            return 99;
        }

        function getPositionFromLabel(label) {
            var value = normalizeLabel(label);
            if (value.indexOf('baseline') !== -1) return 'gauche';
            if (value.indexOf('cta') !== -1) return 'centre';
            if (value.indexOf('titre single') !== -1) return 'droite';
            return '';
        }

        function getColors(position) {
            if (position === 'gauche') return [ACTIVE_COLOR, INACTIVE_COLOR, INACTIVE_COLOR];
            if (position === 'centre') return [INACTIVE_COLOR, ACTIVE_COLOR, INACTIVE_COLOR];
            if (position === 'droite') return [INACTIVE_COLOR, INACTIVE_COLOR, ACTIVE_COLOR];
            return [INACTIVE_COLOR, INACTIVE_COLOR, INACTIVE_COLOR];
        }

        function buildRect(colors) {
            var wrap = document.createElement('span');
            wrap.className = 'ellene-pos-rect';
            var labels = ['1', '2', '3'];

            colors.forEach(function(color, index) {
                var part = document.createElement('span');
                part.style.background = color;
                part.style.color = (color === ACTIVE_COLOR) ? '#ffffff' : '#111111';
                part.setAttribute('data-label', labels[index] || '');
                wrap.appendChild(part);
            });

            return wrap;
        }

        function moveShiftArrowsToLabelRow(groupEl) {
            var labelRow = groupEl.querySelector('.cmb-row[class*="-label"]');
            if (!labelRow) return;

            var existingShiftSlot = labelRow.querySelector('.ellene-topbar-shift-slot');
            if (existingShiftSlot) {
                existingShiftSlot.remove();
            }

            var labelTh = labelRow.querySelector('.cmb-th');
            if (labelTh) {
                var nativeLabel = labelTh.querySelector('label');
                if (nativeLabel) {
                    nativeLabel.classList.add('ellene-topbar-native-label');
                    nativeLabel.style.display = 'none';
                }

                var labelText = labelTh.querySelector('.ellene-topbar-label-text');
                if (!labelText) {
                    labelText = document.createElement('span');
                    labelText.className = 'ellene-topbar-label-text';
                    labelText.textContent = 'Label';
                    labelTh.appendChild(labelText);
                }
            }
        }

        function isShiftUpControl(control) {
            if (!control) return false;

            var classText = control.className || '';
            if (/cmb-shift-row-up/i.test(classText)) return true;

            var dash = control.querySelector('.dashicons');
            if (dash && /arrow-up/i.test(dash.className || '')) return true;

            var label = String(control.getAttribute('aria-label') || control.textContent || '').toLowerCase();
            return /haut|up|top|monter/.test(label);
        }

        function isShiftDownControl(control) {
            if (!control) return false;

            var classText = control.className || '';
            if (/cmb-shift-row-down/i.test(classText)) return true;

            var dash = control.querySelector('.dashicons');
            if (dash && /arrow-down/i.test(dash.className || '')) return true;

            var label = String(control.getAttribute('aria-label') || control.textContent || '').toLowerCase();
            return /bas|down|bottom|descendre/.test(label);
        }

        function setShiftControlDisabled(control, shouldDisable) {
            if (!control) return;

            if (control.tagName === 'BUTTON') {
                control.disabled = !!shouldDisable;
            }

            if (shouldDisable) {
                control.setAttribute('aria-disabled', 'true');
                control.setAttribute('tabindex', '-1');
            } else {
                control.removeAttribute('aria-disabled');
                control.removeAttribute('tabindex');
            }
        }

        function updateShiftControlsState() {
            var groups = Array.prototype.slice.call(document.querySelectorAll('.cmb2-id-top-bar-items .cmb-repeatable-grouping'));
            if (!groups.length) return;

            groups.forEach(function(groupEl, index) {
                var controls = Array.prototype.slice.call(groupEl.querySelectorAll('.cmb-shift-rows'));
                if (!controls.length) return;

                controls.forEach(function(control) {
                    if (isShiftUpControl(control)) {
                        setShiftControlDisabled(control, index === 0);
                    } else if (isShiftDownControl(control)) {
                        setShiftControlDisabled(control, index === groups.length - 1);
                    } else {
                        setShiftControlDisabled(control, false);
                    }
                });
            });
        }

        function shiftTopBarGroup(control) {
            if (!(window.jQuery && window.CMB2)) {
                return false;
            }

            var $ = window.jQuery;
            var cmb = window.CMB2;
            var $control = $(control);
            var moveUp = $control.hasClass('move-up');
            var $from = $control.parents('.cmb-repeatable-grouping').first();
            var $goto = $from[moveUp ? 'prev' : 'next']('.cmb-repeatable-grouping');

            if (!$goto.length) {
                return false;
            }

            var fromIterator = $from.attr('data-iterator');
            var toIterator = $goto.attr('data-iterator');

            $from.find(cmb.repeatEls).each(function() {
                cmb.updateNameAttr($(this), fromIterator, toIterator);
            });

            $goto.find(cmb.repeatEls).each(function() {
                cmb.updateNameAttr($(this), toIterator, fromIterator);
            });

            var groupTitle = $control.parents('.cmb-repeatable-group').find('[data-grouptitle]').data('grouptitle');
            if (groupTitle && typeof cmb.resetGroupTitles === 'function') {
                cmb.resetGroupTitles($from, toIterator, groupTitle);
                cmb.resetGroupTitles($goto, fromIterator, groupTitle);
            }

            $from.data('iterator', toIterator).attr('data-iterator', toIterator);
            $goto.data('iterator', fromIterator).attr('data-iterator', fromIterator);

            $goto[moveUp ? 'before' : 'after']($from);

            return true;
        }

        function bindTopBarShiftClicks() {
            return;
        }

        function applyFixedTitles() {
            var groups = document.querySelectorAll('.cmb2-id-top-bar-items .cmb-repeatable-grouping');

            Array.prototype.forEach.call(groups, function(groupEl, index) {
                var titleEl = groupEl.querySelector('.cmb-group-title') || groupEl.querySelector('h3');
                if (!titleEl) return;

                var wantedTitle = FIXED_ITEM_TITLES[index] || ('Item ' + (index + 1));
                var titleSpan = titleEl.querySelector('span');

                if (titleSpan) {
                    titleSpan.textContent = wantedTitle;
                    return;
                }

                var textNode = Array.prototype.find.call(titleEl.childNodes, function(node) {
                    return node.nodeType === Node.TEXT_NODE;
                });

                if (textNode) {
                    textNode.textContent = ' ' + wantedTitle + ' ';
                }
            });
        }

        function renderGroup(groupEl) {
            var titleEl = groupEl.querySelector('.cmb-group-title') || groupEl.querySelector('h3');
            if (!titleEl) return;

            var selectEl = groupEl.querySelector('select[id*="top_bar_items"][id*="_position"]');
            var label = titleEl.textContent || '';

            if (/^\s*cta\s+central\s*$/i.test(label)) {
                var labelNode = Array.prototype.find.call(titleEl.childNodes, function(node) {
                    return node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '';
                });

                if (labelNode) {
                    labelNode.textContent = ' CTA';
                    label = 'CTA';
                }
            }

            var position = (selectEl && selectEl.value) ? selectEl.value : getPositionFromLabel(label);

            var oldRect = titleEl.querySelector('.ellene-pos-rect');
            if (oldRect) {
                oldRect.remove();
            }

            titleEl.insertBefore(buildRect(getColors(position)), titleEl.firstChild);
        }

        function renderAll() {
            applyFixedTitles();

            var groups = document.querySelectorAll('.cmb2-id-top-bar-items .cmb-repeatable-grouping');
            Array.prototype.forEach.call(groups, function(groupEl) {
                moveShiftArrowsToLabelRow(groupEl);
                renderGroup(groupEl);
            });

            updateShiftControlsState();
        }

        function scheduleRenderAll() {
            [0, 70, 160, 320].forEach(function(delay) {
                setTimeout(renderAll, delay);
            });
        }

        function normalizeMobileLogoRemoveButtons() {
            if (!window.matchMedia || !window.matchMedia('(max-width: 900px)').matches) {
                return;
            }

            var logoTd = document.querySelector('.cmb2-id-top-bar-logo-png .cmb-td');
            if (!logoTd) {
                return;
            }

            var buttons = Array.prototype.slice.call(logoTd.querySelectorAll('.cmb2-remove-file-button'));
            buttons.forEach(function(btn) {
                btn.style.display = 'none';
            });

            if (buttons.length) {
                buttons[0].style.display = 'inline-block';
                buttons[0].style.position = 'static';
                buttons[0].style.top = 'auto';
                buttons[0].style.left = 'auto';
            }

            var wrappers = Array.prototype.slice.call(logoTd.querySelectorAll('.cmb2-remove-wrapper'));
            wrappers.forEach(function(wrap, index) {
                wrap.style.display = (index === 0) ? 'block' : 'none';
                wrap.style.height = (index === 0) ? 'auto' : '0';
                wrap.style.margin = (index === 0) ? '8px 0 0 0' : '0';
            }
        }

        document.addEventListener('change', function(event) {
            var target = event.target;
            if (!target || !target.matches('select[id*="top_bar_items"][id*="_position"]')) {
                return;
            }

            var groupEl = target.closest('.cmb-repeatable-grouping');
            if (groupEl) {
                renderGroup(groupEl);
            }
        });

        document.addEventListener('click', function(event) {
            var target = event.target;
            if (!target) {
                return;
            }

            if (target.matches('.add-group-row, .cmb-add-group-row')) {
                scheduleRenderAll();
            }

            if (target.closest('.cmb2-id-top-bar-logo-png .cmb2-upload-button, .cmb2-id-top-bar-logo-png .cmb2-remove-file-button')) {
                setTimeout(normalizeMobileLogoRemoveButtons, 50);
                setTimeout(normalizeMobileLogoRemoveButtons, 250);
            }
        });

        var topBarGroupRoot = document.querySelector('.cmb2-id-top-bar-items .cmb-repeatable-group');
        if (topBarGroupRoot && window.MutationObserver) {
            var renderQueued = false;
            var observer = new MutationObserver(function() {
                if (renderQueued) return;
                renderQueued = true;
                setTimeout(function() {
                    renderQueued = false;
                    scheduleRenderAll();
                }, 60);
            });

            observer.observe(topBarGroupRoot, { childList: true, subtree: true });
        }

        document.addEventListener('DOMContentLoaded', function() {
            bindTopBarShiftClicks();
            scheduleRenderAll();
            setTimeout(normalizeMobileLogoRemoveButtons, 50);
            setTimeout(normalizeMobileLogoRemoveButtons, 250);
        });
    })();
    </script>
    <?php
}

add_action('admin_head', 'ellene_wp_top_bar_position_indicator_admin_head', 40);