    function scotchsControlHtml(opts) {
        opts = opts || {};
        var hiddenClass = opts.hiddenClass || '';
        var hiddenLabel = opts.hiddenLabel || TXT.tapeHide;
        var hiddenWrapClass = (opts.hiddenWrapClass || 'em-v4-slides__opt em-v4-slides__opt--check em-v4-slides__opt--check-tapes') + ' em-v4-scotchs-control__check';
        var hiddenChecked = !!opts.hiddenChecked;
        var colorClass = opts.colorClass || 'em-v4-chip__tapes-color';
        var colorLabel = opts.colorLabel || TXT.tape;
        var colorWrapClass = (opts.colorWrapClass || 'em-v4-slides__colorfield') + ' em-v4-scotchs-control__color';
        var colorLabelClass = opts.colorLabelClass || 'em-wp-admin-color-field-row__label';
        var colorIdPrefix = opts.colorIdPrefix || 'emv4tp-';
        var html = '';

        html += '<span class="' + esc(colorWrapClass) + '"><span class="' + esc(colorLabelClass) + '">' + esc(colorLabel) + '</span>'
            + colorField(colorId(colorIdPrefix), colorClass, colorLabel)
            + '</span>';

        if (hiddenClass) {
            html += '<label class="' + esc(hiddenWrapClass) + '"' + (opts.hiddenTitle ? ' title="' + esc(opts.hiddenTitle) + '"' : '') + '><input type="checkbox" class="' + esc(hiddenClass) + '"' + (hiddenChecked ? ' checked' : '') + '> ' + esc(hiddenLabel) + '</label>';
        }

        return html;
    }
