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
    }
    .ellene-pos-rect span + span {
        border-left: 1px solid #9a9a9a;
    }
    </style>
    <script>
    (function() {
        // Couleurs a modifier directement dans le code
        var ACTIVE_COLOR = '#16002f';
        var INACTIVE_COLOR = '#d5d5d5';

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

            colors.forEach(function(color) {
                var part = document.createElement('span');
                part.style.background = color;
                wrap.appendChild(part);
            });

            return wrap;
        }

        function reorderGroups() {
            var container = document.querySelector('.cmb2-id-top-bar-items .cmb-repeatable-group');
            if (!container) return;

            var groups = Array.prototype.slice.call(container.querySelectorAll('.cmb-repeatable-grouping'));
            if (!groups.length) return;

            groups.sort(function(a, b) {
                var aTitle = a.querySelector('.cmb-group-title') || a.querySelector('h3');
                var bTitle = b.querySelector('.cmb-group-title') || b.querySelector('h3');
                var aLabel = aTitle ? aTitle.textContent : '';
                var bLabel = bTitle ? bTitle.textContent : '';
                return getPriorityFromLabel(aLabel) - getPriorityFromLabel(bLabel);
            });

            groups.forEach(function(groupEl) {
                container.appendChild(groupEl);
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
            reorderGroups();

            var groups = document.querySelectorAll('.cmb2-id-top-bar-items .cmb-repeatable-grouping');
            Array.prototype.forEach.call(groups, function(groupEl) {
                renderGroup(groupEl);
            });
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
                setTimeout(renderAll, 30);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(renderAll, 30);
        });
    })();
    </script>
    <?php
}

add_action('admin_head', 'ellene_wp_top_bar_position_indicator_admin_head', 40);